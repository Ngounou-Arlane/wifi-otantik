<?php

/**
 * Reseller accounts: wallet, zones, commission, portal, accounting.
 */
class OtantikReseller
{
    const SEED = 500;

    public static function admin()
    {
        global $admin, $routes;
        _admin();
        Tenant::require_can('sales', $admin);
        Tenant::ensure_schema();
        $act = (string)($routes[2] ?? '');
        $id = (int)($routes[3] ?? 0);
        if ($act === 'new') {
            self::admin_form($admin, 0);
            return;
        }
        if ($act === 'edit' && $id > 0) {
            self::admin_form($admin, $id);
            return;
        }
        if ($act === 'view' && $id > 0) {
            self::admin_view($admin, $id);
            return;
        }
        if ($act === 'topup' && $id > 0) {
            self::admin_topup($admin, $id);
            return;
        }
        if ($act === 'toggle' && $id > 0) {
            self::admin_toggle($admin, $id);
            return;
        }
        if ($act === 'delete' && $id > 0) {
            self::admin_delete($admin, $id);
            return;
        }
        if ($act === 'export') {
            $what = (string)($routes[3] ?? 'list');
            if ($what === 'view') {
                self::admin_export($admin, 'view', (int)($routes[4] ?? 0), (string)($routes[5] ?? 'sales'), (string)($routes[6] ?? 'csv'));
            } else {
                self::admin_export($admin, $what, 0, '', (string)($routes[4] ?? 'csv'));
            }
            return;
        }
        self::admin_list($admin);
    }

    public static function portal()
    {
        global $routes, $ui, $config;
        Tenant::ensure_schema();
        if (class_exists('Tenant') && (Tenant::is_wifi_host() || Tenant::is_portal_host())) {
            header('Location: ' . self::url());
            exit;
        }
        $act = (string)($routes[2] ?? '');
        if ($act === 'logout') {
            unset($_SESSION['wo_reseller_id']);
            r2(self::url(), 's', 'Déconnecté');
        }
        $rs = self::session();
        if ($rs) {
            self::complete_pending($rs);
        }
        if (!$rs) {
            self::portal_login();
            return;
        }
        if ($act === 'sell') {
            self::portal_sell($rs);
            return;
        }
        if ($act === 'history') {
            self::portal_history($rs);
            return;
        }
        if ($act === 'export') {
            self::portal_export($rs, (string)($routes[3] ?? ''), (string)($routes[4] ?? 'csv'));
            return;
        }
        if ($act === 'recharge') {
            self::portal_recharge($rs);
            return;
        }
        if ($act === 'profile') {
            self::portal_profile($rs);
            return;
        }
        self::portal_home($rs);
    }

    public static function url($path = '')
    {
        $p = '/revendeur';
        if ($path !== '') {
            $p .= '/' . ltrim($path, '/');
        }
        return Tenant::url_business($p);
    }

    public static function admin_url($path = '')
    {
        $base = 'plugin/wifi_otantik_resellers';
        if ($path !== '') {
            $base .= '/' . ltrim($path, '/');
        }
        return getUrl($base);
    }

    public static function session()
    {
        $id = (int)($_SESSION['wo_reseller_id'] ?? 0);
        if ($id < 1) {
            return null;
        }
        try {
            $row = ORM::for_table('tbl_otantik_resellers')->find_one($id);
        } catch (Throwable $e) {
            return null;
        }
        if (!$row || (string)$row['status'] !== 'active') {
            unset($_SESSION['wo_reseller_id']);
            return null;
        }
        return $row;
    }

    public static function require_owned($id, $admin)
    {
        $row = ORM::for_table('tbl_otantik_resellers')->find_one((int)$id);
        if (!$row || (!Tenant::is_platform($admin) && (int)$row['owner_id'] !== Tenant::id($admin))) {
            r2(self::admin_url(), 'e', 'Revendeur introuvable');
        }
        return $row;
    }

    public static function defaults_permissions()
    {
        return [
            'sell_hotspot' => true,
            'sell_pppoe' => true,
            'recharge' => true,
        ];
    }

    public static function permissions($row)
    {
        $def = self::defaults_permissions();
        $raw = trim((string)($row['permissions'] ?? ''));
        $arr = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($arr)) {
            return $def;
        }
        return [
            'sell_hotspot' => !empty($arr['sell_hotspot']),
            'sell_pppoe' => !empty($arr['sell_pppoe']),
            'recharge' => array_key_exists('recharge', $arr) ? !empty($arr['recharge']) : true,
        ];
    }

    public static function zones($reseller)
    {
        $ids = [];
        try {
            foreach (ORM::for_table('tbl_otantik_reseller_zones')->where('reseller_id', (int)$reseller['id'])->find_many() as $z) {
                $ids[] = (int)$z['router_id'];
            }
        } catch (Throwable $e) {
        }
        if (!$ids) {
            return [];
        }
        return ORM::for_table('tbl_routers')->where_in('id', $ids)->where('enabled', 1)->order_by_asc('name')->find_many();
    }

    public static function zone_ids($reseller)
    {
        $out = [];
        foreach (self::zones($reseller) as $z) {
            $out[] = (int)$z['id'];
        }
        return $out;
    }

    public static function set_zones($resellerId, $routerIds)
    {
        $resellerId = (int)$resellerId;
        ORM::for_table('tbl_otantik_reseller_zones')->where('reseller_id', $resellerId)->delete_many();
        foreach ($routerIds as $rid) {
            $rid = (int)$rid;
            if ($rid < 1) {
                continue;
            }
            $row = ORM::for_table('tbl_otantik_reseller_zones')->create();
            $row->reseller_id = $resellerId;
            $row->router_id = $rid;
            $row->save();
        }
    }

    public static function book($ownerId, $reseller, $kind, $amount, $opts = [])
    {
        $walletAfter = (float)($reseller['wallet'] ?? 0);
        try {
            $b = ORM::for_table('tbl_otantik_books')->create();
            $b->owner_id = (int)$ownerId;
            $b->reseller_id = (int)($reseller['id'] ?? 0);
            $b->kind = (string)$kind;
            $b->amount = round((float)$amount, 2);
            $b->commission = round((float)($opts['commission'] ?? 0), 2);
            $b->client_amount = round((float)($opts['client_amount'] ?? 0), 2);
            $b->wallet_after = $walletAfter;
            $b->trx_id = (int)($opts['trx_id'] ?? 0);
            $b->router_id = (int)($opts['router_id'] ?? 0);
            $b->note = mb_substr((string)($opts['note'] ?? ''), 0, 255);
            $b->created_at = date('Y-m-d H:i:s');
            $b->save();
        } catch (Throwable $e) {
        }
        if (empty($opts['ledger'])) {
            return;
        }
        try {
            $led = ORM::for_table('tbl_otantik_ledger')->create();
            $led->owner_id = (int)$ownerId;
            $led->trx_id = (int)($opts['trx_id'] ?? 0);
            $led->gross = abs((float)$amount);
            $led->rate = (float)($opts['rate'] ?? 0);
            $led->fee = round((float)($opts['commission'] ?? 0), 2);
            $led->net = round((float)($opts['client_amount'] ?? $amount), 2);
            $led->created_at = date('Y-m-d H:i:s');
            if (self::ledger_has('reseller_id')) {
                $led->reseller_id = (int)($reseller['id'] ?? 0);
            }
            if (self::ledger_has('kind')) {
                $led->kind = (string)$kind;
            }
            if (self::ledger_has('note')) {
                $led->note = mb_substr((string)($opts['note'] ?? ''), 0, 255);
            }
            $led->save();
        } catch (Throwable $e) {
        }
    }

    private static function ledger_has($col)
    {
        static $cols = null;
        if ($cols === null) {
            $cols = [];
            try {
                foreach (ORM::get_db()->query('SHOW COLUMNS FROM `tbl_otantik_ledger`')->fetchAll(PDO::FETCH_ASSOC) as $c) {
                    $cols[strtolower((string)$c['Field'])] = true;
                }
            } catch (Throwable $e) {
            }
        }
        return isset($cols[strtolower((string)$col)]);
    }

    public static function credit($reseller, $amount, $kind, $opts = [])
    {
        $amount = round((float)$amount, 2);
        if ($amount == 0.0) {
            return $reseller;
        }
        $reseller->wallet = round((float)$reseller['wallet'] + $amount, 2);
        $reseller->save();
        $opts['client_amount'] = $opts['client_amount'] ?? ($amount > 0 ? $amount : 0);
        self::book((int)$reseller['owner_id'], $reseller, $kind, $amount, $opts);
        return $reseller;
    }

    public static function split_price($price, $commission)
    {
        $price = (int)round((float)$price);
        $pct = (float)$commission;
        if ($pct < 0) {
            $pct = 0;
        }
        if ($pct > 90) {
            $pct = 90;
        }
        $profit = (int)round($price * $pct / 100);
        $wholesale = max(0, $price - $profit);
        return ['price' => $price, 'commission' => $pct, 'profit' => $profit, 'wholesale' => $wholesale];
    }

    private static function admin_list($admin)
    {
        $oid = Tenant::id($admin);
        $all = Tenant::is_platform($admin);
        $qStr = function_exists('wifi_otantik_q') ? wifi_otantik_q() : trim((string)_get('q'));
        $makeRs = function () use ($oid, $all, $qStr) {
            $q = ORM::for_table('tbl_otantik_resellers');
            if (!$all) {
                $q->where('owner_id', $oid);
            }
            if (function_exists('wifi_otantik_where_search')) {
                wifi_otantik_where_search($q, ['name', 'phone'], $qStr);
            }
            return $q;
        };
        $pager = wifi_otantik_pager((int)$makeRs()->count(), null, 100);
        $rows = wifi_otantik_paged($makeRs()->order_by_desc('id'), $pager)->find_many();
        $owners = [];
        if ($all) {
            try {
                foreach (ORM::for_table('tbl_users')->select('id')->select('fullname')->select('username')->find_many() as $u) {
                    $owners[(int)$u['id']] = trim((string)(($u['fullname'] ?? '') !== '' ? $u['fullname'] : $u['username']));
                }
            } catch (Throwable $e) {
            }
        }
        $list = [];
        foreach ($rows as $r) {
            $zones = self::zones($r);
            $names = [];
            foreach ($zones as $z) {
                $names[] = (string)$z['name'];
            }
            $list[] = [
                'id' => (int)$r['id'],
                'name' => (string)$r['name'],
                'phone' => (string)$r['phone'],
                'commission' => (float)$r['commission'],
                'wallet' => (float)$r['wallet'],
                'profit' => (float)$r['profit'],
                'sales_total' => (float)$r['sales_total'],
                'tickets_count' => (int)$r['tickets_count'],
                'status' => (string)$r['status'],
                'zones' => $names,
                'created_at' => (string)$r['created_at'],
                'owner_id' => (int)$r['owner_id'],
                'owner_name' => $owners[(int)$r['owner_id']] ?? '',
            ];
        }
        $bq = wifi_otantik_q('bq');
        $makeBooks = function () use ($oid, $all, $bq) {
            $q = ORM::for_table('tbl_otantik_books');
            if (!$all) {
                $q->where('owner_id', $oid);
            }
            wifi_otantik_where_search($q, ['kind', 'note', 'amount'], $bq);
            return $q;
        };
        $pagerBooks = wifi_otantik_pager((int)$makeBooks()->count(), null, 20, '', 'bp');
        $books = [];
        try {
            $books = wifi_otantik_paged($makeBooks()->order_by_desc('id'), $pagerBooks)->find_many();
        } catch (Throwable $e) {
        }
        $seeded = 0.0;
        $topups = 0.0;
        $wholesale = 0.0;
        $comm = 0.0;
        try {
            $bqSeed = ORM::for_table('tbl_otantik_books')->where('kind', 'seed');
            $bqTop = ORM::for_table('tbl_otantik_books')->where_in('kind', ['topup', 'self_topup']);
            $bqSale = ORM::for_table('tbl_otantik_books')->where('kind', 'sale');
            $bqSale2 = ORM::for_table('tbl_otantik_books')->where('kind', 'sale');
            if (!$all) {
                $bqSeed->where('owner_id', $oid);
                $bqTop->where('owner_id', $oid);
                $bqSale->where('owner_id', $oid);
                $bqSale2->where('owner_id', $oid);
            }
            $seeded = (float)$bqSeed->sum('amount');
            $topups = (float)$bqTop->sum('amount');
            $wholesale = (float)$bqSale->sum('client_amount');
            $comm = (float)$bqSale2->sum('commission');
        } catch (Throwable $e) {
        }
        $wallets = 0.0;
        foreach ($list as $r) {
            $wallets += (float)$r['wallet'];
        }
        wifi_otantik_ui('plugin/otantik_resellers.tpl', 'Revendeurs', [
            'rows' => $list,
            'books' => $books,
            'filter_q' => $qStr,
            'filter_bq' => $bq,
            'pager' => $pager,
            'pager_books' => $pagerBooks,
            'sum_seed' => $seeded,
            'sum_topup' => $topups,
            'sum_wholesale' => $wholesale,
            'sum_commission' => $comm,
            'sum_wallets' => $wallets,
            'show_owner' => $all,
            'portal_url' => self::url(),
            'export_list_csv' => self::admin_url('export/list/csv'),
            'export_list_pdf' => self::admin_url('export/list/pdf'),
            'export_books_csv' => self::admin_url('export/books/csv'),
            'export_books_pdf' => self::admin_url('export/books/pdf'),
        ]);
    }

    private static function posted_permissions()
    {
        return json_encode([
            'sell_hotspot' => (bool)_post('perm_hotspot'),
            'sell_pppoe' => (bool)_post('perm_pppoe'),
            'recharge' => (bool)_post('perm_recharge'),
        ], JSON_UNESCAPED_UNICODE);
    }

    private static function posted_zone_ids($admin)
    {
        $allowed = [];
        foreach (Tenant::routers_query($admin)->find_many() as $r) {
            $allowed[(int)$r['id']] = true;
        }
        $raw = $_POST['zone_ids'] ?? [];
        if (!is_array($raw)) {
            $raw = $raw !== '' ? [(int)$raw] : [];
        }
        $ids = [];
        foreach ($raw as $id) {
            $id = (int)$id;
            if (isset($allowed[$id])) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if (count($allowed) === 1 && !$ids) {
            $ids = array_keys($allowed);
        }
        return $ids;
    }

    private static function admin_form($admin, $id)
    {
        $oid = Tenant::id($admin);
        $row = $id ? self::require_owned($id, $admin) : null;
        $routers = Tenant::routers_query($admin)->find_many();
        if (_post('save_reseller')) {
            $name = mb_substr(trim((string)_post('name')), 0, 128);
            $phone = wifi_otantik_phone(_post('phone'));
            $email = strtolower(trim((string)_post('email')));
            $commission = (float)str_replace(',', '.', (string)_post('commission'));
            if ($name === '' || strlen($phone) < 9) {
                r2(self::admin_url($id ? 'edit/' . $id : 'new'), 'e', 'Nom et téléphone requis');
            }
            if ($commission < 0 || $commission > 90) {
                r2(self::admin_url($id ? 'edit/' . $id : 'new'), 'e', 'Commission entre 0 et 90 %');
            }
            $ownerKey = $row ? (int)$row['owner_id'] : $oid;
            $q = ORM::for_table('tbl_otantik_resellers')->where('owner_id', $ownerKey)->where('phone', $phone);
            if ($id) {
                $q->where_not_equal('id', $id);
            }
            if ($q->find_one()) {
                r2(self::admin_url($id ? 'edit/' . $id : 'new'), 'e', 'Ce numéro a déjà un compte revendeur');
            }
            $zoneIds = self::posted_zone_ids($admin);
            if (!$zoneIds) {
                r2(self::admin_url($id ? 'edit/' . $id : 'new'), 'e', 'Choisissez au moins une Wifi Zone');
            }
            $pass = (string)_post('password');
            if (!$row && strlen($pass) < 6) {
                r2(self::admin_url('new'), 'e', 'Mot de passe : 6 caractères minimum');
            }
            if (!$row) {
                $row = ORM::for_table('tbl_otantik_resellers')->create();
                $row->owner_id = $oid;
                $row->wallet = 0;
                $row->profit = 0;
                $row->sales_total = 0;
                $row->tickets_count = 0;
                $row->created_at = date('Y-m-d H:i:s');
            }
            $row->name = $name;
            $row->phone = $phone;
            $row->email = $email;
            $row->commission = round($commission, 2);
            $row->status = _post('status') === 'disabled' ? 'disabled' : 'active';
            $row->permissions = self::posted_permissions();
            if ($pass !== '') {
                if (strlen($pass) < 6) {
                    r2(self::admin_url($id ? 'edit/' . $id : 'new'), 'e', 'Mot de passe : 6 caractères minimum');
                }
                $row->password = Password::_crypt($pass);
            }
            $row->save();
            self::set_zones((int)$row['id'], $zoneIds);
            if (!$id) {
                self::credit($row, self::SEED, 'seed', [
                    'client_amount' => 0,
                    'note' => 'Crédit initial automatique ' . self::SEED . ' FCFA',
                ]);
            }
            r2(self::admin_url('view/' . (int)$row['id']), 's', $id ? 'Revendeur mis à jour' : 'Compte revendeur créé avec ' . self::SEED . ' FCFA');
        }
        $sel = $row ? self::zone_ids($row) : [];
        if (!$row && count($routers) === 1) {
            $sel = [(int)$routers[0]['id']];
        }
        $perm = $row ? self::permissions($row) : self::defaults_permissions();
        wifi_otantik_ui('plugin/otantik_reseller_form.tpl', $row ? 'Modifier le revendeur' : 'Nouveau revendeur', [
            'row' => $row,
            'routers' => $routers,
            'sel_zones' => $sel,
            'perm' => $perm,
            'auto_zone' => count($routers) === 1,
            'solo_zone' => count($routers) === 1 ? $routers[0] : null,
            'seed' => self::SEED,
        ]);
    }

    private static function admin_view($admin, $id)
    {
        $row = self::require_owned($id, $admin);
        $zones = self::zones($row);
        $qSales = wifi_otantik_q();
        $qBooks = wifi_otantik_q('bq');
        $makeSales = function () use ($row, $qSales) {
            $q = ORM::for_table('tbl_payment_gateway')
                ->where('gateway', 'reseller')
                ->where_like('pg_url_payment', 'reseller:' . (int)$row['id'] . '%');
            wifi_otantik_where_search($q, ['routers', 'plan_name', 'payment_channel'], $qSales);
            return $q;
        };
        $makeBooks = function () use ($row, $qBooks) {
            $q = ORM::for_table('tbl_otantik_books')->where('reseller_id', (int)$row['id']);
            wifi_otantik_where_search($q, ['kind', 'note', 'amount'], $qBooks);
            return $q;
        };
        $pager = wifi_otantik_pager((int)$makeSales()->count());
        $pagerBooks = wifi_otantik_pager((int)$makeBooks()->count(), null, 20, '', 'bp');
        $books = wifi_otantik_paged($makeBooks()->order_by_desc('id'), $pagerBooks)->find_many();
        $sales = [];
        try {
            $sales = wifi_otantik_paged($makeSales()->order_by_desc('id'), $pager)->find_many();
        } catch (Throwable $e) {
        }
        wifi_otantik_ui('plugin/otantik_reseller_view.tpl', (string)$row['name'], [
            'row' => $row,
            'zones' => $zones,
            'perm' => self::permissions($row),
            'books' => $books,
            'sales' => $sales,
            'filter_q' => $qSales,
            'filter_bq' => $qBooks,
            'pager' => $pager,
            'pager_books' => $pagerBooks,
            'split' => self::split_price(1000, $row['commission']),
            'export_sales_csv' => self::admin_url('export/view/' . (int)$row['id'] . '/sales/csv'),
            'export_sales_pdf' => self::admin_url('export/view/' . (int)$row['id'] . '/sales/pdf'),
            'export_books_csv' => self::admin_url('export/view/' . (int)$row['id'] . '/books/csv'),
            'export_books_pdf' => self::admin_url('export/view/' . (int)$row['id'] . '/books/pdf'),
        ]);
    }

    private static function admin_topup($admin, $id)
    {
        $row = self::require_owned($id, $admin);
        $amount = (int)round((float)str_replace(',', '.', (string)_post('amount')));
        $fromWallet = (int)_post('from_wallet') === 1;
        if ($amount < 100) {
            r2(self::admin_url('view/' . $id), 'e', 'Montant trop bas (min. 100 FCFA)');
        }
        if ($fromWallet) {
            $tenant = Tenant::tenant_row(Tenant::id($admin));
            if ((float)$tenant['wallet'] < $amount) {
                r2(self::admin_url('view/' . $id), 'e', 'Solde client insuffisant');
            }
            $tenant->wallet = (float)$tenant['wallet'] - $amount;
            $tenant->save();
        }
        self::credit($row, $amount, 'topup', [
            'client_amount' => $fromWallet ? -$amount : $amount,
            'note' => $fromWallet ? 'Recharge depuis le portefeuille client' : 'Recharge manuelle client',
        ]);
        r2(self::admin_url('view/' . $id), 's', $amount . ' FCFA crédités');
    }

    private static function admin_toggle($admin, $id)
    {
        $row = self::require_owned($id, $admin);
        $row->status = (string)$row['status'] === 'active' ? 'disabled' : 'active';
        $row->save();
        r2(self::admin_url(), 's', $row->status === 'active' ? 'Compte activé' : 'Compte désactivé');
    }

    private static function admin_delete($admin, $id)
    {
        $row = self::require_owned($id, $admin);
        if (!empty($_POST['confirm_delete']) || _get('ok') === '1') {
            ORM::for_table('tbl_otantik_reseller_zones')->where('reseller_id', (int)$row['id'])->delete_many();
            $row->delete();
            r2(self::admin_url(), 's', 'Compte revendeur supprimé');
        }
        r2(self::admin_url('view/' . $id), 'e', 'Suppression non confirmée');
    }

    private static function admin_export($admin, $what, $id, $kind, $fmt)
    {
        $fmt = OtantikExport::fmt($fmt);
        if ($what === 'view' && $id > 0) {
            $row = self::require_owned($id, $admin);
            if ($kind === 'books') {
                OtantikExport::send($fmt, 'revendeur-' . $id . '-caisse', 'Caisse — ' . $row['name'], [
                    'Date', 'Type', 'Caisse', 'Votre part', 'Commission', 'Solde après', 'Note',
                ], self::book_rows((int)$row['id'], true));
                return;
            }
            OtantikExport::send($fmt, 'revendeur-' . $id . '-ventes', 'Ventes — ' . $row['name'], [
                'Date', 'Zone', 'Forfait', 'Code', 'Prix',
            ], self::sale_rows((int)$row['id']));
            return;
        }
        if ($what === 'books') {
            $oid = Tenant::id($admin);
            $headers = ['Date', 'Type', 'Montant caisse', 'Votre part', 'Commission', 'Note'];
            $rows = [];
            foreach (ORM::for_table('tbl_otantik_books')->where('owner_id', $oid)->order_by_desc('id')->limit(5000)->find_many() as $b) {
                $rows[] = [
                    (string)$b['created_at'],
                    (string)$b['kind'],
                    (string)$b['amount'],
                    (string)$b['client_amount'],
                    (string)$b['commission'],
                    (string)$b['note'],
                ];
            }
            OtantikExport::send($fmt, 'revendeurs-journal', 'Journal comptable revendeurs', $headers, $rows);
            return;
        }
        $oid = Tenant::id($admin);
        $headers = ['Revendeur', 'Téléphone', 'Zones', 'Commission %', 'Caisse', 'Ventes', 'Tickets', 'Gain', 'Statut'];
        $out = [];
        foreach (ORM::for_table('tbl_otantik_resellers')->where('owner_id', $oid)->order_by_desc('id')->find_many() as $r) {
            $names = [];
            foreach (self::zones($r) as $z) {
                $names[] = (string)$z['name'];
            }
            $out[] = [
                (string)$r['name'],
                (string)$r['phone'],
                implode(', ', $names),
                (string)round((float)$r['commission']),
                (string)round((float)$r['wallet']),
                (string)round((float)$r['sales_total']),
                (string)(int)$r['tickets_count'],
                (string)round((float)$r['profit']),
                (string)$r['status'] === 'active' ? 'Actif' : 'Inactif',
            ];
        }
        OtantikExport::send($fmt, 'revendeurs', 'Revendeurs', $headers, $out);
    }

    private static function sale_rows($resellerId)
    {
        $rows = [];
        try {
            $sales = ORM::for_table('tbl_payment_gateway')
                ->where('gateway', 'reseller')
                ->where_like('pg_url_payment', 'reseller:' . (int)$resellerId . '%')
                ->order_by_desc('id')
                ->limit(5000)
                ->find_many();
            foreach ($sales as $s) {
                $rows[] = [
                    (string)($s['paid_date'] ?: $s['created_date']),
                    (string)$s['routers'],
                    (string)$s['plan_name'],
                    (string)$s['payment_channel'],
                    (string)$s['price'],
                ];
            }
        } catch (Throwable $e) {
        }
        return $rows;
    }

    private static function book_rows($resellerId, $withClient = false)
    {
        $rows = [];
        $q = ORM::for_table('tbl_otantik_books')->where('reseller_id', (int)$resellerId)->order_by_desc('id')->limit(5000);
        foreach ($q->find_many() as $b) {
            $line = [
                (string)$b['created_at'],
                (string)$b['kind'],
                (string)$b['amount'],
            ];
            if ($withClient) {
                $line[] = (string)$b['client_amount'];
            }
            $line[] = (string)$b['commission'];
            $line[] = (string)$b['wallet_after'];
            $line[] = (string)$b['note'];
            $rows[] = $line;
        }
        return $rows;
    }

    private static function portal_export($rs, $what, $fmt)
    {
        $fmt = OtantikExport::fmt($fmt);
        $rid = (int)$rs['id'];
        if ($what === 'books') {
            OtantikExport::send($fmt, 'caisse-' . $rid, 'Caisse et recharges — ' . $rs['name'], [
                'Date', 'Type', 'Caisse', 'Commission', 'Solde', 'Note',
            ], self::book_rows($rid, false));
            return;
        }
        OtantikExport::send($fmt, 'ventes-' . $rid, 'Ventes — ' . $rs['name'], [
            'Date', 'Zone', 'Forfait', 'Code', 'Prix',
        ], self::sale_rows($rid));
    }

    private static function complete_pending($rs)
    {
        try {
            $rows = ORM::for_table('tbl_payment_gateway')
                ->where('status', 1)
                ->where('pg_url_payment', 'reseller-topup:' . (int)$rs['id'])
                ->order_by_desc('id')
                ->limit(8)
                ->find_many();
            foreach ($rows as $trx) {
                $ref = trim((string)$trx['gateway_trx_id']);
                if ($ref === '') {
                    continue;
                }
                $st = OtantikPay::status($ref);
                if (!empty($st['ok'])) {
                    wifi_otantik_pay_resume($trx, false);
                } elseif (empty($st['pending'])) {
                    $trx->status = 3;
                    $trx->pg_paid_response = json_encode($st);
                    $trx->save();
                }
            }
        } catch (Throwable $e) {
        }
    }

    private static function portal_login()
    {
        global $ui, $config;
        if (_post('login') && _post('password')) {
            $phone = wifi_otantik_phone(_post('login'));
            $email = strtolower(trim((string)_post('login')));
            $pass = (string)_post('password');
            $q = ORM::for_table('tbl_otantik_resellers')->where('status', 'active');
            if (strlen($phone) >= 9) {
                $row = $q->where('phone', $phone)->find_one();
            } else {
                $row = $q->where('email', $email)->find_one();
            }
            if (!$row || !Password::_verify($pass, $row['password'])) {
                r2(self::url(), 'e', 'Téléphone ou mot de passe incorrect');
            }
            $_SESSION['wo_reseller_id'] = (int)$row['id'];
            r2(self::url(), 's', 'Bienvenue ' . $row['name']);
        }
        $ui->assign('_title', 'Espace revendeur');
        $ui->assign('app_url', defined('APP_URL') ? APP_URL : Tenant::url_business(''));
        $ui->assign('_c', $config);
        $ui->assign('rs_action', self::url());
        $ui->assign('url_client', Tenant::url_business('/connexion'));
        $ui->display('otantik_rs_login.tpl');
    }

    private static function portal_assign($rs, $title, $page, $extra = [])
    {
        global $ui, $config;
        $zones = self::zones($rs);
        $ui->assign('_title', $title);
        $ui->assign('app_url', defined('APP_URL') ? APP_URL : Tenant::url_business(''));
        $ui->assign('_c', $config);
        $ui->assign('rs', $rs);
        $ui->assign('rs_page', $page);
        $ui->assign('rs_zones', $zones);
        $ui->assign('rs_perm', self::permissions($rs));
        $ui->assign('rs_home', self::url());
        $ui->assign('rs_sell', self::url('sell'));
        $ui->assign('rs_history', self::url('history'));
        $ui->assign('rs_recharge', self::url('recharge'));
        $ui->assign('rs_profile', self::url('profile'));
        $ui->assign('rs_logout', self::url('logout'));
        $ui->assign('rs_split', self::split_price(1000, $rs['commission']));
        $ui->assign('rs_solo', count($zones) === 1 ? $zones[0] : null);
        $ui->assign('rs_avatar', self::avatar_url($rs));
        foreach ($extra as $k => $v) {
            $ui->assign($k, $v);
        }
        $ui->display('otantik_rs.tpl');
    }

    public static function avatar_path($id)
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'avatars'
            . DIRECTORY_SEPARATOR . 'rs-' . (int)$id . '.jpg';
    }

    public static function avatar_url($rs)
    {
        $id = (int)($rs['id'] ?? 0);
        $path = self::avatar_path($id);
        $base = defined('APP_URL') ? APP_URL : Tenant::url_business('');
        if ($id > 0 && is_file($path)) {
            return rtrim($base, '/') . '/ui/ui_custom/images/avatars/rs-' . $id . '.jpg?t=' . filemtime($path);
        }
        $fallback = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'avatar-default.png';
        if (is_file($fallback)) {
            return rtrim($base, '/') . '/ui/ui_custom/images/avatar-default.png';
        }
        return '';
    }

    public static function save_avatar($rs, $file)
    {
        $id = (int)($rs['id'] ?? 0);
        if ($id < 1) {
            return 'Session expirée';
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Téléversement impossible';
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return 'Photo trop lourde (max. 2 Mo)';
        }
        $info = @getimagesize($file['tmp_name']);
        $okTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (defined('IMAGETYPE_WEBP')) {
            $okTypes[] = IMAGETYPE_WEBP;
        }
        if (!$info || !in_array((int)$info[2], $okTypes, true)) {
            return 'Utilisez une image JPG, PNG ou WEBP';
        }
        $dest = self::avatar_path($id);
        $dir = dirname($dest);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return 'Dossier photo inaccessible';
        }
        $src = null;
        if (function_exists('imagecreatetruecolor')) {
            if ((int)$info[2] === IMAGETYPE_JPEG) {
                $src = @imagecreatefromjpeg($file['tmp_name']);
            } elseif ((int)$info[2] === IMAGETYPE_PNG) {
                $src = @imagecreatefrompng($file['tmp_name']);
            } elseif ((int)$info[2] === IMAGETYPE_GIF) {
                $src = @imagecreatefromgif($file['tmp_name']);
            } elseif (defined('IMAGETYPE_WEBP') && (int)$info[2] === IMAGETYPE_WEBP && function_exists('imagecreatefromwebp')) {
                $src = @imagecreatefromwebp($file['tmp_name']);
            }
        }
        if ($src) {
            $w = imagesx($src);
            $h = imagesy($src);
            $side = min($w, $h);
            $sx = (int)(($w - $side) / 2);
            $sy = (int)(($h - $side) / 2);
            $size = 400;
            $dst = imagecreatetruecolor($size, $size);
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $side, $side);
            imagejpeg($dst, $dest, 86);
            imagedestroy($dst);
            imagedestroy($src);
        } elseif (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return 'Enregistrement de la photo impossible';
        }
        @chmod($dest, 0644);
        return '';
    }

    private static function portal_home($rs)
    {
        $books = ORM::for_table('tbl_otantik_books')->where('reseller_id', (int)$rs['id'])->order_by_desc('id')->limit(12)->find_many();
        $plans = self::available_plans($rs);
        self::portal_assign($rs, 'Tableau de bord', 'home', [
            'books' => $books,
            'rs_plans' => $plans,
        ]);
    }

    private static function available_plans($rs)
    {
        $zones = self::zones($rs);
        $perm = self::permissions($rs);
        $names = [];
        foreach ($zones as $z) {
            $names[] = (string)$z['name'];
        }
        if (!$names) {
            return [];
        }
        $types = [];
        if (!empty($perm['sell_hotspot'])) {
            $types[] = 'Hotspot';
        }
        if (!empty($perm['sell_pppoe'])) {
            $types[] = 'PPPOE';
        }
        if (!$types) {
            return [];
        }
        return ORM::for_table('tbl_plans')->where_in('type', $types)->where_in('routers', $names)->where('enabled', 1)->order_by_asc('price')->find_many();
    }

    private static function portal_sell($rs)
    {
        $zones = self::zones($rs);
        $perm = self::permissions($rs);
        if (_post('sell_ticket')) {
            $back = self::url('sell');
            $routerId = (int)_post('router_id');
            if (count($zones) === 1) {
                $routerId = (int)$zones[0]['id'];
            }
            $okIds = self::zone_ids($rs);
            if (!in_array($routerId, $okIds, true)) {
                r2($back, 'e', 'Cette zone ne vous est pas attribuée');
            }
            $router = ORM::for_table('tbl_routers')->find_one($routerId);
            $plan = ORM::for_table('tbl_plans')->find_one((int)_post('plan_id'));
            if (!$router || !$plan || (string)$plan['routers'] !== (string)$router['name']) {
                r2($back, 'e', 'Forfait ou zone invalide');
            }
            $kind = wifi_otantik_plan_kind($plan);
            if ($kind === 'PPPOE' && empty($perm['sell_pppoe'])) {
                r2($back, 'e', 'PPPoE non autorisé pour votre compte');
            }
            if ($kind !== 'PPPOE' && empty($perm['sell_hotspot'])) {
                r2($back, 'e', 'Hotspot non autorisé pour votre compte');
            }
            $split = self::split_price($plan['price'], $rs['commission']);
            if ((float)$rs['wallet'] < $split['wholesale']) {
                r2($back, 'e', 'Solde insuffisant. Il faut ' . $split['wholesale'] . ' FCFA (prix ' . $split['price'] . ' − commission ' . (int)$split['commission'] . ' %).');
            }
            $rawPhone = trim((string)_post('phone'));
            $phone = wifi_otantik_phone($rawPhone);
            if ($rawPhone !== '' && !wifi_otantik_phone_ok($phone)) {
                r2($back, 'e', 'Numéro camerounais invalide (6XX XXX XXX)');
            }
            $user = wifi_otantik_manual_login($kind, $phone, _post('username'));
            $pass = $kind === 'PPPOE'
                ? (strlen(trim((string)_post('password'))) >= 4 ? trim((string)_post('password')) : substr(preg_replace('/[^A-Za-z0-9]/', '', ($phone ?: 'Ot') . 'Ot'), 0, 8))
                : $user;
            $cust = wifi_otantik_upsert_wifi_customer($user, $pass, $phone, $kind, $router);
            $trx = ORM::for_table('tbl_payment_gateway')->create();
            $trx->username = $phone !== '' ? $phone : $user;
            $trx->user_id = (int)$cust['id'];
            $trx->gateway = 'reseller';
            $trx->plan_id = (int)$plan['id'];
            $trx->plan_name = $plan['name_plan'];
            $trx->routers_id = (int)$router['id'];
            $trx->routers = $router['name'];
            $trx->price = (string)$split['price'];
            $trx->payment_method = 'RESELLER';
            $trx->payment_channel = substr($user . ':' . $pass, 0, 128);
            $trx->pg_url_payment = 'reseller:' . (int)$rs['id'];
            $trx->created_date = date('Y-m-d H:i:s');
            $trx->expired_date = date('Y-m-d H:i:s', time() + 86400 * 30);
            $trx->status = 2;
            $trx->paid_date = date('Y-m-d H:i:s');
            $trx->gateway_trx_id = 'RS-' . (int)$rs['id'] . '-' . date('YmdHis');
            $trx->save();
            $rs->wallet = round((float)$rs['wallet'] - $split['wholesale'], 2);
            $rs->profit = round((float)$rs['profit'] + $split['profit'], 2);
            $rs->sales_total = round((float)$rs['sales_total'] + $split['price'], 2);
            $rs->tickets_count = (int)$rs['tickets_count'] + 1;
            $rs->save();
            self::book((int)$rs['owner_id'], $rs, 'sale', -$split['wholesale'], [
                'commission' => $split['profit'],
                'client_amount' => 0,
                'trx_id' => (int)$trx->id(),
                'router_id' => (int)$router['id'],
                'rate' => $split['commission'],
                'note' => $plan['name_plan'] . ' · ' . $router['name'],
            ]);
            $pushError = '';
            try {
                wifi_otantik_push_user_mikrotik($router, $plan, $user, $pass, 'otantik reseller ' . ($phone ?: $user));
                wifi_otantik_record_recharge($cust, $plan, $router, $user, 'Revendeur');
                if (function_exists('wifi_otantik_wa_ticket')) {
                    wifi_otantik_wa_ticket($cust, $plan, $router, $user, $pass, $kind);
                }
            } catch (Throwable $e) {
                $pushError = $e->getMessage();
            }
            $_SESSION['wo_rs_issued'] = [
                'kind' => $kind,
                'user' => $user,
                'pass' => $pass,
                'plan' => (string)$plan['name_plan'],
                'zone' => (string)$router['name'],
                'price' => $split['price'],
                'wholesale' => $split['wholesale'],
                'profit' => $split['profit'],
                'push_error' => $pushError,
            ];
            r2($back, $pushError !== '' ? 'w' : 's', $pushError !== '' ? ('Ticket créé. MikroTik : ' . $pushError) : 'Ticket généré');
        }
        $issued = $_SESSION['wo_rs_issued'] ?? null;
        if (isset($_SESSION['wo_rs_issued'])) {
            unset($_SESSION['wo_rs_issued']);
        }
        $plans = self::available_plans($rs);
        $routerIds = [];
        foreach ($zones as $z) {
            $routerIds[(string)$z['name']] = (int)$z['id'];
        }
        $planJs = [];
        foreach ($plans as $p) {
            $zoneName = trim((string)$p['routers']);
            $planJs[] = [
                'id' => (int)$p['id'],
                'name' => (string)$p['name_plan'],
                'type' => wifi_otantik_plan_kind($p),
                'routers' => $zoneName,
                'router_id' => $routerIds[$zoneName] ?? 0,
                'price' => (int)round((float)$p['price']),
            ];
        }
        self::portal_assign($rs, 'Vendre un ticket', 'sell', [
            'plans_json' => json_encode($planJs),
            'issued' => $issued,
            'auto_zone' => count($zones) === 1,
        ]);
    }

    private static function portal_history($rs)
    {
        $qSales = wifi_otantik_q();
        $qBooks = wifi_otantik_q('bq');
        $base = self::url('history');
        $makeBooks = function () use ($rs, $qBooks) {
            $q = ORM::for_table('tbl_otantik_books')->where('reseller_id', (int)$rs['id']);
            wifi_otantik_where_search($q, ['kind', 'note'], $qBooks);
            return $q;
        };
        $makeSales = function () use ($rs, $qSales) {
            $q = ORM::for_table('tbl_payment_gateway')
                ->where('gateway', 'reseller')
                ->where_like('pg_url_payment', 'reseller:' . (int)$rs['id'] . '%');
            wifi_otantik_where_search($q, ['routers', 'plan_name', 'payment_channel'], $qSales);
            return $q;
        };
        $pager = wifi_otantik_pager((int)$makeSales()->count(), null, 20, $base);
        $pagerBooks = wifi_otantik_pager((int)$makeBooks()->count(), null, 20, $base, 'bp');
        $books = wifi_otantik_paged($makeBooks()->order_by_desc('id'), $pagerBooks)->find_many();
        $sales = [];
        try {
            $sales = wifi_otantik_paged($makeSales()->order_by_desc('id'), $pager)->find_many();
        } catch (Throwable $e) {
        }
        $pager['route'] = '';
        $pagerBooks['route'] = '';
        self::portal_assign($rs, 'Historique', 'history', [
            'books' => $books,
            'sales' => $sales,
            'filter_q' => $qSales,
            'filter_bq' => $qBooks,
            'pager' => $pager,
            'pager_books' => $pagerBooks,
            'export_sales_csv' => self::url('export/sales/csv'),
            'export_sales_pdf' => self::url('export/sales/pdf'),
            'export_books_csv' => self::url('export/books/csv'),
            'export_books_pdf' => self::url('export/books/pdf'),
        ]);
    }

    private static function portal_recharge($rs)
    {
        $perm = self::permissions($rs);
        if (empty($perm['recharge'])) {
            r2(self::url(), 'e', 'Recharge non autorisée');
        }
        if (_post('amount')) {
            $back = self::url('recharge');
            $amount = (int)round((float)_post('amount'));
            $phone = wifi_otantik_phone(_post('phone') ?: $rs['phone']);
            $service = wifi_otantik_detect_service($phone);
            if ($amount < 500 || strlen($phone) < 9) {
                r2($back, 'e', 'Montant (min. 500) ou numéro invalide');
            }
            if ($service === '') {
                r2($back, 'e', 'Numéro MTN MoMo ou Orange Money requis');
            }
            $trx = ORM::for_table('tbl_payment_gateway')->create();
            $trx->username = $phone;
            $trx->user_id = 0;
            $trx->gateway = OtantikPay::active();
            $trx->plan_id = 0;
            $trx->plan_name = 'Recharge revendeur';
            $trx->routers_id = 0;
            $trx->routers = '';
            $trx->price = (string)$amount;
            $trx->payment_method = $service;
            $trx->payment_channel = 'reseller:' . (int)$rs['id'];
            $trx->pg_url_payment = 'reseller-topup:' . (int)$rs['id'];
            $trx->created_date = date('Y-m-d H:i:s');
            $trx->expired_date = date('Y-m-d H:i:s', time() + 3600);
            $trx->status = 1;
            $trx->save();
            try {
                $resp = OtantikPay::collect($amount, $phone, $service, 'RS-' . $trx->id(), [
                    'first_name' => (string)$rs['name'],
                    'last_name' => 'Revendeur',
                    'user_id' => 'rs' . (int)$rs['id'],
                ]);
                $trx->pg_paid_response = json_encode($resp);
                $trx->gateway_trx_id = $resp['reference'] ?: '';
                $trx->save();
                if (!empty($resp['pending'])) {
                    r2(self::url('recharge'), 's', 'Validez le paiement sur votre téléphone, puis revenez sur cette page.');
                }
                if (empty($resp['ok'])) {
                    $trx->status = 3;
                    $trx->save();
                    r2($back, 'e', wifi_otantik_user_msg($resp['message'] ?? '', 'pay'));
                }
                wifi_otantik_pay_resume($trx, false);
                r2(self::url(), 's', $amount . ' FCFA ajoutés à votre caisse');
            } catch (Exception $e) {
                $trx->status = 3;
                $trx->pg_paid_response = $e->getMessage();
                $trx->save();
                r2($back, 'e', wifi_otantik_user_msg($e->getMessage(), 'pay'));
            }
        }
        self::portal_assign($rs, 'Recharger la caisse', 'recharge', []);
    }

    private static function portal_profile($rs)
    {
        $back = self::url('profile');
        $hasAvatar = !empty($_FILES['avatar']['name']) && (int)($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
        if ($hasAvatar) {
            $err = self::save_avatar($rs, $_FILES['avatar']);
            if ($err !== '') {
                r2($back, 'e', $err);
            }
            if (!_post('save_profile') && !_post('save_password')) {
                r2($back, 's', 'Photo de profil mise à jour');
            }
        }
        if (_post('email_code')) {
            $pending = strtolower(trim((string)($_SESSION['wo_rs_email_pending'] ?? '')));
            if ($pending === '' || !filter_var($pending, FILTER_VALIDATE_EMAIL)) {
                r2($back, 'e', 'Demandez d’abord un code pour cette adresse.');
            }
            list($ok, $payload, $msg) = OtantikAuth::consume($pending, 'email', _post('email_code'));
            if (!$ok) {
                r2($back . (strpos($back, '?') !== false ? '&' : '?') . 'verify=1', 'e', $msg);
            }
            $rs->email = strtolower(trim((string)($payload['email'] ?? $pending)));
            $rs->save();
            unset($_SESSION['wo_rs_email_pending']);
            r2($back, 's', 'Adresse email confirmée');
        }
        if (_post('save_profile')) {
            $name = mb_substr(trim((string)_post('name')), 0, 128);
            $phone = wifi_otantik_phone(_post('phone'));
            $email = strtolower(trim((string)_post('email')));
            if ($name === '' || strlen($phone) < 9) {
                r2($back, 'e', 'Nom et téléphone requis');
            }
            $dup = ORM::for_table('tbl_otantik_resellers')->where('phone', $phone)->where_not_equal('id', (int)$rs['id'])->find_one();
            if ($dup) {
                r2($back, 'e', 'Ce numéro a déjà un compte revendeur');
            }
            if ($email !== '') {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    r2($back, 'e', 'Email invalide');
                }
                $edup = ORM::for_table('tbl_otantik_resellers')->where('email', $email)->where_not_equal('id', (int)$rs['id'])->find_one();
                if ($edup) {
                    r2($back, 'e', 'Cet email est déjà utilisé');
                }
            }
            $rs->name = $name;
            $rs->phone = $phone;
            $oldMail = strtolower(trim((string)($rs['email'] ?? '')));
            if ($email !== $oldMail) {
                if ($email === '') {
                    $rs->email = '';
                    $rs->save();
                    r2($back, 's', 'Profil mis à jour');
                }
                $rs->save();
                list($sent, $hint) = OtantikAuth::issue($email, 'email', [
                    'rid' => (int)$rs['id'],
                    'email' => $email,
                ]);
                if (!$sent) {
                    r2($back, 'e', $hint);
                }
                $_SESSION['wo_rs_email_pending'] = $email;
                r2($back . (strpos($back, '?') !== false ? '&' : '?') . 'verify=1', 's', 'Un code a été envoyé à ' . $hint);
            }
            $rs->email = $email;
            $rs->save();
            r2($back, 's', 'Profil mis à jour');
        }
        if (_post('save_password')) {
            $cur = (string)_post('current_password');
            $new = (string)_post('new_password');
            $new2 = (string)_post('new_password2');
            if (!Password::_verify($cur, $rs['password'])) {
                r2($back, 'e', 'Mot de passe actuel incorrect');
            }
            if (strlen($new) < 6 || $new !== $new2) {
                r2($back, 'e', 'Nouveau mot de passe invalide ou non confirmé (6 caractères min.)');
            }
            $rs->password = Password::_crypt($new);
            $rs->save();
            r2($back, 's', 'Mot de passe modifié');
        }
        self::portal_assign($rs, 'Mon profil', 'profile', [
            'phone_fmt' => function_exists('wifi_otantik_phone_fmt') ? wifi_otantik_phone_fmt($rs['phone']) : (string)$rs['phone'],
            'email_verify' => !empty($_SESSION['wo_rs_email_pending']),
            'email_pending_hint' => OtantikAuth::hint((string)($_SESSION['wo_rs_email_pending'] ?? '')),
        ]);
    }

    public static function kind_label($kind)
    {
        $map = [
            'seed' => 'Crédit initial',
            'topup' => 'Recharge client',
            'self_topup' => 'Recharge Mobile Money',
            'sale' => 'Vente ticket',
            'refund' => 'Remboursement',
            'adjust' => 'Ajustement',
        ];
        return $map[(string)$kind] ?? (string)$kind;
    }
}
