<?php

/**
 * ERP / Comptabilité premium Wifi-Otantik.
 */
class OtantikErp
{
    public static function has_access($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (!$admin) {
            return false;
        }
        if (Tenant::is_platform($admin)) {
            return true;
        }
        $oid = Tenant::id($admin);
        if ($oid < 1) {
            return false;
        }
        try {
            $t = Tenant::tenant_row($oid);
            if (!$t || (int)($t['erp_enabled'] ?? 0) !== 1) {
                return false;
            }
            $until = trim((string)($t['erp_until'] ?? ''));
            if ($until !== '' && strtotime($until) !== false && strtotime($until) < time()) {
                return false;
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function guard($admin = null)
    {
        if (self::has_access($admin)) {
            return true;
        }
        wifi_otantik_ui('plugin/otantik_erp_locked.tpl', 'ERP Premium', [
            'erp_locked' => true,
        ]);
        exit;
    }

    public static function owner_id($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        return Tenant::is_platform($admin) ? 0 : Tenant::id($admin);
    }

    public static function month_start($offset = 0)
    {
        return date('Y-m-01 00:00:00', strtotime(($offset >= 0 ? '+' : '') . $offset . ' month'));
    }

    public static function sync_payment($trx)
    {
        if (!$trx || (int)$trx['status'] !== 2) {
            return;
        }
        $router = ORM::for_table('tbl_routers')->find_one((int)$trx['routers_id']);
        if (!$router && !empty($trx['routers'])) {
            $router = ORM::for_table('tbl_routers')->where('name', $trx['routers'])->find_one();
        }
        if (!$router) {
            return;
        }
        $ownerId = (int)$router['owner_id'];
        if ($ownerId < 1) {
            return;
        }
        $exists = ORM::for_table('tbl_otantik_erp_flow')
            ->where('owner_id', $ownerId)
            ->where('ref_type', 'payment')
            ->where('ref_id', (int)$trx['id'])
            ->find_one();
        if ($exists) {
            return;
        }
        $no = function_exists('wifi_otantik_receipt_ensure') ? wifi_otantik_receipt_ensure($trx) : ('WO-' . (int)$trx['id']);
        $row = ORM::for_table('tbl_otantik_erp_flow')->create();
        $row->owner_id = $ownerId;
        $row->direction = 'in';
        $row->source = 'wifi_sale';
        $row->amount = (float)$trx['price'];
        $row->label = trim((string)($trx['plan_name'] ?? 'Vente Wi-Fi'));
        $row->category = 'ventes';
        $row->method = (string)($trx['payment_method'] ?? '');
        $row->ref_type = 'payment';
        $row->ref_id = (int)$trx['id'];
        $row->meta = json_encode([
            'receipt' => $no,
            'zone' => (string)$trx['routers'],
            'phone' => (string)$trx['username'],
        ], JSON_UNESCAPED_UNICODE);
        $row->occurred_at = (string)($trx['paid_date'] ?: date('Y-m-d H:i:s'));
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    }

    public static function sync_withdrawal($wd)
    {
        if (!$wd || strtoupper((string)$wd['status']) !== 'SUCCESS') {
            return;
        }
        $ownerId = (int)$wd['owner_id'];
        if ($ownerId < 1) {
            return;
        }
        $expense = self::ensure_withdrawal_expense($wd);
        $exists = ORM::for_table('tbl_otantik_erp_flow')
            ->where('owner_id', $ownerId)
            ->where('ref_type', 'withdrawal')
            ->where('ref_id', (int)$wd['id'])
            ->find_one();
        if ($exists) {
            if ((int)$exists['expense_id'] < 1 && $expense) {
                $exists->expense_id = (int)$expense->id();
                $exists->save();
            }
            return;
        }
        $reason = trim((string)($wd['reason'] ?? ''));
        $row = ORM::for_table('tbl_otantik_erp_flow')->create();
        $row->owner_id = $ownerId;
        $row->direction = 'out';
        $row->source = 'withdrawal';
        $row->amount = (float)$wd['amount'];
        $row->label = $reason !== '' ? $reason : 'Retrait wallet';
        $row->category = 'retraits';
        $row->method = (string)($wd['service'] ?? '');
        $row->ref_type = 'withdrawal';
        $row->ref_id = (int)$wd['id'];
        $row->expense_id = $expense ? (int)$expense->id() : 0;
        $row->meta = json_encode(['phone' => (string)$wd['receiver']], JSON_UNESCAPED_UNICODE);
        $row->occurred_at = (string)($wd['created_at'] ?: date('Y-m-d H:i:s'));
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    }

    public static function next_expense_number($ownerId)
    {
        $prefix = 'DEP-' . date('Y') . '-';
        $last = ORM::for_table('tbl_otantik_erp_expenses')
            ->where('owner_id', (int)$ownerId)
            ->where_like('number', $prefix . '%')
            ->order_by_desc('id')
            ->find_one();
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', (string)$last['number'], $m)) {
            $seq = (int)$m[1] + 1;
        }
        return $prefix . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }

    public static function ensure_withdrawal_expense($wd)
    {
        $ownerId = (int)$wd['owner_id'];
        $withdrawalId = (int)$wd['id'];
        $expenseId = (int)($wd['expense_id'] ?? 0);
        $expense = $expenseId > 0
            ? ORM::for_table('tbl_otantik_erp_expenses')->find_one($expenseId)
            : null;
        if (!$expense) {
            $expense = ORM::for_table('tbl_otantik_erp_expenses')
                ->where('owner_id', $ownerId)
                ->where('source_type', 'withdrawal')
                ->where('source_id', $withdrawalId)
                ->find_one();
        }
        if (!$expense) {
            $when = (string)($wd['created_at'] ?: date('Y-m-d H:i:s'));
            $reason = trim((string)($wd['reason'] ?? ''));
            $expense = ORM::for_table('tbl_otantik_erp_expenses')->create();
            $expense->owner_id = $ownerId;
            $expense->number = self::next_expense_number($ownerId);
            $expense->title = $reason !== '' ? $reason : 'Retrait wallet';
            $expense->category = 'retraits';
            $expense->amount = (float)$wd['amount'];
            $expense->vendor = trim((string)($wd['receiver'] ?? ''));
            $expense->payment_method = trim((string)($wd['service'] ?? ''));
            $expense->note = 'Sortie créée depuis le retrait #' . $withdrawalId;
            $expense->status = 'paid';
            $expense->recurring_id = 0;
            $expense->source_type = 'withdrawal';
            $expense->source_id = $withdrawalId;
            $expense->expense_date = date('Y-m-d', strtotime($when));
            $expense->paid_at = $when;
            $expense->created_at = date('Y-m-d H:i:s');
            $expense->save();
        }
        if ((int)($wd['expense_id'] ?? 0) !== (int)$expense->id()) {
            $wd->expense_id = (int)$expense->id();
            $wd->save();
        }
        return $expense;
    }

    public static function dashboard($admin, $range = 'month')
    {
        $oid = Tenant::is_platform($admin) ? null : Tenant::id($admin);
        $from = self::range_start($range);
        // Une seule source de vérité : le journal de flux (pas de double comptage dépenses).
        $flowIn = self::sum_flow($oid, 'in', $from);
        $flowOut = self::sum_flow($oid, 'out', $from);
        $wifiIn = self::sum_income_wifi($oid, $from);
        $otherIn = self::sum_income_other($oid, $from);
        $expOut = self::sum_flow_source($oid, 'out', ['expense', 'recurring'], $from);
        $wdOut = self::sum_flow_source($oid, 'out', ['withdrawal'], $from);
        $net = $flowIn - $flowOut;
        $series = self::chart_series($oid, $range);
        $recent = self::recent_flow($oid, 12);
        $recurring = [];
        $pendingQuotes = 0;
        if ($oid) {
            $recurring = ORM::for_table('tbl_otantik_erp_recurring')
                ->where('owner_id', $oid)->where('active', 1)->order_by_asc('day_of_month')->find_many();
            $pendingQuotes = (int)ORM::for_table('tbl_otantik_erp_docs')
                ->where('owner_id', $oid)->where_in('status', ['draft', 'sent'])->where('kind', 'quote')->count();
        } elseif (Tenant::is_platform($admin)) {
            $pendingQuotes = (int)ORM::for_table('tbl_otantik_erp_docs')
                ->where_in('status', ['draft', 'sent'])->where('kind', 'quote')->count();
        }
        return [
            'range' => $range,
            'in_total' => $flowIn,
            'out_total' => $flowOut,
            'wifi_in' => $wifiIn,
            'other_in' => $otherIn,
            'net' => $net,
            'expenses' => $expOut,
            'withdrawals' => $wdOut,
            'series' => $series,
            'recent' => $recent,
            'recurring' => $recurring,
            'pending_quotes' => $pendingQuotes,
            'margin_pct' => $flowIn > 0 ? round(100 * $net / $flowIn) : 0,
        ];
    }

    private static function sum_flow_source($ownerId, $direction, array $sources, $from)
    {
        try {
            $q = self::flow_query($ownerId, $direction, $from);
            if ($sources) {
                $q->where_in('source', $sources);
            }
            return (float)$q->sum('amount');
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    public static function parse_lines()
    {
        $labels = (array)($_POST['line_label'] ?? []);
        $qtys = (array)($_POST['line_qty'] ?? []);
        $prices = (array)($_POST['line_price'] ?? []);
        $periods = (array)($_POST['line_period'] ?? []);
        if (!$labels && trim((string)(_post('line_label') ?? '')) !== '') {
            $labels = [_post('line_label')];
            $qtys = [_post('line_qty')];
            $prices = [_post('line_price')];
        }
        $lines = [];
        $n = max(count($labels), count($qtys), count($prices));
        for ($i = 0; $i < $n; $i++) {
            $label = trim((string)($labels[$i] ?? ''));
            if ($label === '') {
                continue;
            }
            $qty = max(1, (int)($qtys[$i] ?? 1));
            $price = (float)($prices[$i] ?? 0);
            $lines[] = [
                'label' => $label,
                'period' => trim((string)($periods[$i] ?? '')),
                'qty' => $qty,
                'price' => $price,
                'total' => $qty * $price,
            ];
        }
        return $lines;
    }

    public static function lines_total(array $lines)
    {
        $sub = 0.0;
        foreach ($lines as $ln) {
            $sub += (float)($ln['total'] ?? ((float)($ln['qty'] ?? 0) * (float)($ln['price'] ?? 0)));
        }
        return $sub;
    }

    public static function doc_owned($docId, $admin)
    {
        $doc = ORM::for_table('tbl_otantik_erp_docs')->find_one((int)$docId);
        if (!$doc) {
            return null;
        }
        if (Tenant::is_platform($admin)) {
            return $doc;
        }
        if ((int)$doc['owner_id'] !== Tenant::id($admin)) {
            return null;
        }
        return $doc;
    }

    public static function expense_owned($id, $admin)
    {
        $row = ORM::for_table('tbl_otantik_erp_expenses')->find_one((int)$id);
        if (!$row) {
            return null;
        }
        if (!Tenant::is_platform($admin) && (int)$row['owner_id'] !== Tenant::id($admin)) {
            return null;
        }
        return $row;
    }

    public static function expense_payload($expense)
    {
        $company = self::company_for((int)$expense['owner_id']);
        $date = (string)($expense['expense_date'] ?? '');
        if ($date === '') {
            $date = substr((string)$expense['paid_at'], 0, 10);
        }
        return [
            'id' => (int)$expense['id'],
            'number' => (string)($expense['number'] ?: ('DEP-' . (int)$expense['id'])),
            'title' => (string)$expense['title'],
            'category' => (string)$expense['category'],
            'amount' => (float)$expense['amount'],
            'vendor' => (string)$expense['vendor'],
            'payment_method' => (string)($expense['payment_method'] ?? ''),
            'note' => (string)$expense['note'],
            'status' => (string)$expense['status'],
            'date' => $date,
            'source_type' => (string)($expense['source_type'] ?? 'manual'),
            'company' => $company['name'],
            'company_niu' => $company['niu'],
            'company_phone' => $company['phone'],
            'files' => self::files_for('expense', (int)$expense['id']),
        ];
    }

    public static function company_for($ownerId)
    {
        $name = 'Wifi-Otantik';
        $niu = '';
        $phone = '';
        try {
            $t = Tenant::tenant_row((int)$ownerId);
            if ($t) {
                $bn = trim((string)($t['business_name'] ?? ''));
                if ($bn !== '') {
                    $name = $bn;
                }
                $niu = trim((string)($t['business_niu'] ?? ''));
                $phone = trim((string)($t['phone'] ?? ''));
            }
        } catch (Throwable $e) {
        }
        return ['name' => $name, 'niu' => $niu, 'phone' => $phone];
    }

    public static function doc_payload($doc)
    {
        $lines = json_decode((string)($doc['lines_json'] ?? '[]'), true);
        if (!is_array($lines)) {
            $lines = [];
        }
        foreach ($lines as &$ln) {
            $ln['qty'] = max(1, (int)($ln['qty'] ?? 1));
            $ln['price'] = (float)($ln['price'] ?? 0);
            $ln['total'] = (float)($ln['total'] ?? ($ln['qty'] * $ln['price']));
            $ln['label'] = (string)($ln['label'] ?? '');
            $ln['period'] = (string)($ln['period'] ?? '');
        }
        unset($ln);
        $company = self::company_for((int)$doc['owner_id']);
        $status = (string)$doc['status'];
        $statusLabel = [
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'paid' => 'Payé',
            'cancelled' => 'Annulé',
        ][$status] ?? $status;
        return [
            'id' => (int)$doc['id'],
            'kind' => (string)$doc['kind'],
            'kind_label' => $doc['kind'] === 'invoice' ? 'Facture' : 'Devis',
            'number' => (string)$doc['number'],
            'client_name' => (string)$doc['client_name'],
            'client_phone' => (string)$doc['client_phone'],
            'client_email' => (string)$doc['client_email'],
            'subject' => (string)$doc['subject'],
            'lines' => $lines,
            'subtotal' => (float)$doc['subtotal'],
            'tax' => (float)$doc['tax'],
            'total' => (float)$doc['total'],
            'status' => $status,
            'status_label' => $statusLabel,
            'issue_date' => (string)($doc['issue_date'] ?? '') !== ''
                ? (string)$doc['issue_date']
                : substr((string)$doc['created_at'], 0, 10),
            'valid_until' => (string)($doc['valid_until'] ?? ''),
            'paid_at' => (string)($doc['paid_at'] ?? ''),
            'created_at' => (string)$doc['created_at'],
            'company' => $company['name'],
            'company_niu' => $company['niu'],
            'company_phone' => $company['phone'],
        ];
    }

    public static function sync_invoice_paid($doc)
    {
        if (!$doc || (string)$doc['status'] !== 'paid') {
            return;
        }
        $exists = ORM::for_table('tbl_otantik_erp_flow')
            ->where('owner_id', (int)$doc['owner_id'])
            ->where('ref_type', 'invoice')
            ->where('ref_id', (int)$doc['id'])
            ->find_one();
        if ($exists) {
            return;
        }
        $row = ORM::for_table('tbl_otantik_erp_flow')->create();
        $row->owner_id = (int)$doc['owner_id'];
        $row->direction = 'in';
        $row->source = 'invoice';
        $row->amount = (float)$doc['total'];
        $row->label = trim((string)$doc['subject']) !== '' ? (string)$doc['subject'] : ('Facture ' . $doc['number']);
        $row->category = 'services';
        $row->method = '';
        $row->ref_type = 'invoice';
        $row->ref_id = (int)$doc['id'];
        $row->meta = json_encode([
            'number' => (string)$doc['number'],
            'client' => (string)$doc['client_name'],
        ], JSON_UNESCAPED_UNICODE);
        $row->occurred_at = (string)($doc['paid_at'] ?: date('Y-m-d H:i:s'));
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
    }

    /** Sources ERP = revenus hors connexion Wi‑Fi (facture, service manuel). */
    public static function other_income_sources()
    {
        return ['invoice', 'other_service'];
    }

    public static function add_other_income($ownerId, array $data)
    {
        $ownerId = (int)$ownerId;
        $amount = (float)($data['amount'] ?? 0);
        $label = trim((string)($data['label'] ?? ''));
        if ($ownerId < 1 || $amount <= 0 || $label === '') {
            return [false, 'Montant ou libellé invalide', null];
        }
        $dateRaw = trim((string)($data['date'] ?? ''));
        $ts = $dateRaw !== '' ? strtotime($dateRaw) : time();
        if ($ts === false) {
            return [false, 'Date invalide', null];
        }
        $when = date('Y-m-d', $ts) . ' ' . date('H:i:s');
        $category = trim((string)($data['category'] ?? 'services')) ?: 'services';
        $method = trim((string)($data['method'] ?? 'Espèces'));
        $note = trim((string)($data['note'] ?? ''));
        $row = ORM::for_table('tbl_otantik_erp_flow')->create();
        $row->owner_id = $ownerId;
        $row->direction = 'in';
        $row->source = 'other_service';
        $row->amount = $amount;
        $row->label = $label;
        $row->category = $category;
        $row->method = $method;
        $row->ref_type = 'manual';
        $row->ref_id = 0;
        $row->meta = json_encode(['note' => $note], JSON_UNESCAPED_UNICODE);
        $row->occurred_at = $when;
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
        return [true, 'Revenu enregistré', $row];
    }

    public static function sum_income_wifi($ownerId, $from)
    {
        try {
            $q = self::flow_query($ownerId, 'in', $from)->where('source', 'wifi_sale');
            return (float)$q->sum('amount');
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    public static function sum_income_other($ownerId, $from)
    {
        try {
            $q = self::flow_query($ownerId, 'in', $from)->where_in('source', self::other_income_sources());
            return (float)$q->sum('amount');
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    /**
     * CA réel Pro : Wi‑Fi + autres services sur une période.
     * @return array{wifi:float,other:float,total:float}
     */
    public static function revenue_split($admin, $range = 'month')
    {
        $oid = Tenant::is_platform($admin) ? null : Tenant::id($admin);
        $from = self::range_start($range);
        $wifi = self::sum_income_wifi($oid, $from);
        $other = self::sum_income_other($oid, $from);
        // Fallback si backfill Wi‑Fi pas encore fait : ventes gateway
        if ($wifi <= 0 && $oid !== null && function_exists('wifi_otantik_sales_query')) {
            try {
                $wifi = (float)wifi_otantik_sales_query($admin)->where('status', 2)->where_gte('paid_date', $from)->sum('price');
            } catch (Throwable $e) {
            }
        }
        return [
            'wifi' => $wifi,
            'other' => $other,
            'total' => $wifi + $other,
            'from' => $from,
            'range' => $range,
        ];
    }

    /** Série barres : connexion Wi‑Fi vs autres services. */
    public static function revenue_bar_series($admin, $range = 'week')
    {
        $ownerId = Tenant::is_platform($admin) ? null : Tenant::id($admin);
        $points = [];
        $n = $range === 'today' ? 1 : ($range === 'week' ? 7 : ($range === 'year' ? 12 : 30));
        for ($i = $n - 1; $i >= 0; $i--) {
            if ($range === 'year') {
                $a = date('Y-m-01 00:00:00', strtotime('-' . $i . ' month'));
                $b = date('Y-m-01 00:00:00', strtotime('+1 month', strtotime($a)));
                $label = date('M', strtotime($a));
            } else {
                $a = date('Y-m-d 00:00:00', strtotime('-' . $i . ' day'));
                $b = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($a)));
                $label = $range === 'week' ? date('D', strtotime($a)) : date('d/m', strtotime($a));
            }
            $wifi = 0.0;
            $other = 0.0;
            try {
                $qw = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in')->where('source', 'wifi_sale')
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                $qo = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in')
                    ->where_in('source', self::other_income_sources())
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                if ($ownerId !== null) {
                    $qw->where('owner_id', (int)$ownerId);
                    $qo->where('owner_id', (int)$ownerId);
                }
                $wifi = (float)$qw->sum('amount');
                $other = (float)$qo->sum('amount');
            } catch (Throwable $e) {
            }
            // Fallback Wi‑Fi depuis payment_gateway si pas de flux ERP
            if ($wifi <= 0 && $ownerId !== null && function_exists('wifi_otantik_sales_query') && $admin) {
                try {
                    $wifi = (float)wifi_otantik_sales_query($admin)->where('status', 2)
                        ->where_gte('paid_date', $a)->where_lt('paid_date', $b)->sum('price');
                } catch (Throwable $e) {
                }
            }
            $points[] = [
                'label' => $label,
                'wifi' => $wifi,
                'other' => $other,
                'total' => $wifi + $other,
            ];
        }
        return $points;
    }

    public static function storage_dir($ownerId)
    {
        $root = rtrim(str_replace('\\', '/', dirname(__DIR__, 2)), '/') . '/system/storage/erp/' . (int)$ownerId;
        if (!is_dir($root)) {
            @mkdir($root, 0775, true);
        }
        return $root;
    }

    public static function save_upload($ownerId, $targetType, $targetId, $file)
    {
        if (empty($file['tmp_name']) || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Aucun fichier reçu';
        }
        if ((int)($file['size'] ?? 0) > 8 * 1024 * 1024) {
            return 'Fichier trop volumineux (max 8 Mo)';
        }
        $orig = basename((string)($file['name'] ?? 'piece'));
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            return 'Formats acceptés : PDF, JPG, PNG, WEBP';
        }
        $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', pathinfo($orig, PATHINFO_FILENAME));
        $name = date('YmdHis') . '_' . substr($safe, 0, 40) . '.' . $ext;
        $dir = self::storage_dir($ownerId);
        $dest = $dir . '/' . $name;
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return 'Enregistrement du justificatif impossible';
        }
        $row = ORM::for_table('tbl_otantik_erp_files')->create();
        $row->owner_id = (int)$ownerId;
        $row->target_type = (string)$targetType;
        $row->target_id = (int)$targetId;
        $row->filename = $name;
        $row->original_name = $orig;
        $mime = '';
        if (function_exists('finfo_open')) {
            $fi = @finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $mime = (string)@finfo_file($fi, $dest);
                @finfo_close($fi);
            }
        }
        $row->mime = $mime !== '' ? $mime : (string)($file['type'] ?? '');
        $row->size = (int)($file['size'] ?? 0);
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
        return '';
    }

    public static function save_uploads($ownerId, $targetType, $targetId, $files)
    {
        if (!isset($files['name'])) {
            return [];
        }
        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        $errors = [];
        foreach ($names as $i => $name) {
            $file = [
                'name' => $name,
                'type' => is_array($files['type'] ?? null) ? ($files['type'][$i] ?? '') : ($files['type'] ?? ''),
                'tmp_name' => is_array($files['tmp_name'] ?? null) ? ($files['tmp_name'][$i] ?? '') : ($files['tmp_name'] ?? ''),
                'error' => is_array($files['error'] ?? null) ? ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) : ($files['error'] ?? UPLOAD_ERR_NO_FILE),
                'size' => is_array($files['size'] ?? null) ? ($files['size'][$i] ?? 0) : ($files['size'] ?? 0),
            ];
            if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $error = self::save_upload($ownerId, $targetType, $targetId, $file);
            if ($error !== '') {
                $errors[] = $name . ' : ' . $error;
            }
        }
        return $errors;
    }

    public static function files_for($targetType, $targetId)
    {
        try {
            return ORM::for_table('tbl_otantik_erp_files')
                ->where('target_type', (string)$targetType)
                ->where('target_id', (int)$targetId)
                ->order_by_desc('id')
                ->find_many();
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function file_path($fileRow)
    {
        return self::storage_dir((int)$fileRow['owner_id']) . '/' . (string)$fileRow['filename'];
    }

    private static function range_start($range)
    {
        if ($range === 'today') {
            return date('Y-m-d 00:00:00');
        }
        if ($range === 'week') {
            return date('Y-m-d 00:00:00', strtotime('-6 days'));
        }
        if ($range === 'year') {
            return date('Y-01-01 00:00:00');
        }
        return self::month_start(0);
    }

    private static function flow_query($ownerId, $direction, $from)
    {
        $q = ORM::for_table('tbl_otantik_erp_flow')->where('direction', $direction);
        if ($ownerId !== null) {
            $q->where('owner_id', (int)$ownerId);
        }
        if ($from !== '') {
            $q->where_gte('occurred_at', $from);
        }
        return $q;
    }

    private static function sum_flow($ownerId, $direction, $from)
    {
        try {
            return (float)self::flow_query($ownerId, $direction, $from)->sum('amount');
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    private static function sum_expenses($ownerId, $from)
    {
        try {
            $q = ORM::for_table('tbl_otantik_erp_expenses');
            if ($ownerId !== null) {
                $q->where('owner_id', (int)$ownerId);
            }
            if ($from !== '') {
                $q->where_gte('paid_at', $from);
            }
            return (float)$q->sum('amount');
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    private static function chart_series($ownerId, $range)
    {
        $points = [];
        $n = $range === 'week' ? 7 : ($range === 'today' ? 1 : 30);
        if ($range === 'year') {
            $n = 12;
        }
        for ($i = $n - 1; $i >= 0; $i--) {
            if ($range === 'year') {
                $a = date('Y-m-01 00:00:00', strtotime('-' . $i . ' month'));
                $b = date('Y-m-01 00:00:00', strtotime('-' . ($i - 1) . ' month'));
                $label = date('M', strtotime($a));
            } elseif ($range === 'week' || $range === 'today') {
                $a = date('Y-m-d 00:00:00', strtotime('-' . $i . ' day'));
                $b = date('Y-m-d 00:00:00', strtotime('-' . ($i - 1) . ' day'));
                $label = date('D', strtotime($a));
            } else {
                $a = date('Y-m-d 00:00:00', strtotime('-' . $i . ' day'));
                $b = date('Y-m-d 00:00:00', strtotime('-' . ($i - 1) . ' day'));
                $label = date('d', strtotime($a));
            }
            $in = 0.0;
            $out = 0.0;
            $wifi = 0.0;
            $other = 0.0;
            try {
                $qi = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in')
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                $qo = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'out')
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                $qw = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in')->where('source', 'wifi_sale')
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                $qoth = ORM::for_table('tbl_otantik_erp_flow')->where('direction', 'in')
                    ->where_in('source', self::other_income_sources())
                    ->where_gte('occurred_at', $a)->where_lt('occurred_at', $b);
                if ($ownerId !== null) {
                    $qi->where('owner_id', (int)$ownerId);
                    $qo->where('owner_id', (int)$ownerId);
                    $qw->where('owner_id', (int)$ownerId);
                    $qoth->where('owner_id', (int)$ownerId);
                }
                $in = (float)$qi->sum('amount');
                $out = (float)$qo->sum('amount');
                $wifi = (float)$qw->sum('amount');
                $other = (float)$qoth->sum('amount');
            } catch (Throwable $e) {
            }
            $points[] = [
                'label' => $label,
                'in' => $in,
                'out' => $out,
                'net' => $in - $out,
                'wifi' => $wifi,
                'other' => $other,
            ];
        }
        return $points;
    }

    private static function recent_flow($ownerId, $limit = 10)
    {
        try {
            $q = ORM::for_table('tbl_otantik_erp_flow')->order_by_desc('occurred_at')->limit((int)$limit);
            if ($ownerId !== null) {
                $q->where('owner_id', (int)$ownerId);
            }
            $rows = [];
            foreach ($q->find_many() as $r) {
                $rows[] = [
                    'id' => (int)$r['id'],
                    'direction' => (string)$r['direction'],
                    'label' => (string)$r['label'],
                    'amount' => (float)$r['amount'],
                    'method' => (string)$r['method'],
                    'category' => (string)$r['category'],
                    'when' => (string)$r['occurred_at'],
                    'source' => (string)$r['source'],
                    'ref_type' => (string)$r['ref_type'],
                    'ref_id' => (int)$r['ref_id'],
                    'expense_id' => (int)$r['expense_id'],
                ];
            }
            return $rows;
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function backfill($admin)
    {
        if (!self::has_access($admin)) {
            return 0;
        }
        $names = Tenant::router_names($admin);
        if (!$names && !Tenant::is_platform($admin)) {
            return 0;
        }
        $n = 0;
        $q = ORM::for_table('tbl_payment_gateway')
            ->where_in('gateway', wifi_otantik_gateways())
            ->where('status', 2)
            ->order_by_desc('id')
            ->limit(500);
        if (!Tenant::is_platform($admin)) {
            $q->where_in('routers', $names ?: ['__none__']);
        }
        foreach ($q->find_many() as $trx) {
            self::sync_payment($trx);
            $n++;
        }
        $oid = Tenant::id($admin);
        $withdrawals = ORM::for_table('tbl_otantik_withdrawals')
            ->where('status', 'SUCCESS')
            ->order_by_desc('id')
            ->limit(Tenant::is_platform($admin) ? 1000 : 200);
        if (!Tenant::is_platform($admin)) {
            $withdrawals->where('owner_id', $oid);
        }
        foreach ($withdrawals->find_many() as $wd) {
            self::sync_withdrawal($wd);
            $n++;
        }
        return $n;
    }

    public static function next_doc_number($ownerId, $kind)
    {
        $prefix = $kind === 'invoice' ? 'FAC' : ($kind === 'quote' ? 'DEV' : 'DOC');
        $year = date('Y');
        $like = $prefix . '-' . $year . '-%';
        $last = ORM::for_table('tbl_otantik_erp_docs')
            ->where('owner_id', (int)$ownerId)
            ->where_like('number', $like)
            ->order_by_desc('id')
            ->find_one();
        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', (string)$last['number'], $m)) {
            $seq = (int)$m[1] + 1;
        }
        return $prefix . '-' . $year . '-' . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }

    public static function run_recurring($ownerId)
    {
        $today = (int)date('j');
        $ym = date('Y-m');
        $n = 0;
        foreach (ORM::for_table('tbl_otantik_erp_recurring')
            ->where('owner_id', (int)$ownerId)->where('active', 1)->find_many() as $rec) {
            $day = max(1, min(28, (int)$rec['day_of_month']));
            if ($today < $day) {
                continue;
            }
            if ((string)$rec['last_run'] !== '' && substr((string)$rec['last_run'], 0, 7) === $ym) {
                continue;
            }
            $exp = ORM::for_table('tbl_otantik_erp_expenses')->create();
            $exp->owner_id = (int)$ownerId;
            $exp->number = self::next_expense_number((int)$ownerId);
            $exp->title = (string)$rec['title'];
            $exp->category = (string)$rec['category'];
            $exp->amount = (float)$rec['amount'];
            $exp->vendor = '';
            $exp->payment_method = '';
            $exp->note = 'Charge récurrente auto';
            $exp->status = 'paid';
            $exp->recurring_id = (int)$rec['id'];
            $exp->source_type = 'recurring';
            $exp->source_id = (int)$rec['id'];
            $exp->expense_date = date('Y-m-d');
            $exp->paid_at = date('Y-m-d H:i:s');
            $exp->created_at = date('Y-m-d H:i:s');
            $exp->save();
            $flow = ORM::for_table('tbl_otantik_erp_flow')->create();
            $flow->owner_id = (int)$ownerId;
            $flow->direction = 'out';
            $flow->source = 'recurring';
            $flow->amount = (float)$rec['amount'];
            $flow->label = (string)$rec['title'];
            $flow->category = (string)$rec['category'];
            $flow->ref_type = 'expense';
            $flow->ref_id = (int)$exp['id'];
            $flow->expense_id = (int)$exp['id'];
            $flow->occurred_at = date('Y-m-d H:i:s');
            $flow->created_at = date('Y-m-d H:i:s');
            $flow->save();
            $rec->last_run = date('Y-m-d');
            $rec->save();
            $n++;
        }
        return $n;
    }

    public static function chart_svg(array $series, $w = 640, $h = 200)
    {
        if (!$series) {
            return '';
        }
        $max = 1.0;
        foreach ($series as $p) {
            $max = max($max, (float)$p['in'], (float)$p['out'], abs((float)$p['net']));
        }
        $pad = 24;
        $iw = $w - $pad * 2;
        $ih = $h - $pad * 2;
        $n = count($series);
        $step = $n > 1 ? $iw / ($n - 1) : $iw;
        $inPts = [];
        $outPts = [];
        foreach ($series as $i => $p) {
            $x = $pad + $i * $step;
            $yin = $pad + $ih - ((float)$p['in'] / $max) * $ih;
            $yout = $pad + $ih - ((float)$p['out'] / $max) * $ih;
            $inPts[] = round($x, 1) . ',' . round($yin, 1);
            $outPts[] = round($x, 1) . ',' . round($yout, 1);
        }
        $inLine = implode(' ', $inPts);
        $outLine = implode(' ', $outPts);
        $fillIn = $pad . ',' . ($pad + $ih) . ' ' . $inLine . ' ' . ($pad + ($n - 1) * $step) . ',' . ($pad + $ih);
        return '<svg viewBox="0 0 ' . $w . ' ' . $h . '" class="wo-erp-chart-svg" aria-hidden="true">'
            . '<defs><linearGradient id="woErpIn" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity=".35"/><stop offset="100%" stop-color="#10b981" stop-opacity="0"/></linearGradient></defs>'
            . '<polygon points="' . $fillIn . '" fill="url(#woErpIn)"/>'
            . '<polyline points="' . $inLine . '" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round"/>'
            . '<polyline points="' . $outLine . '" fill="none" stroke="#f05018" stroke-width="2" stroke-dasharray="6 4" stroke-linecap="round"/>'
            . '</svg>';
    }
}
