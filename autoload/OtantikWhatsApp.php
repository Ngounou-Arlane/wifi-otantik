<?php

/**
 * WhatsApp Cloud API templates and sends for Wifi-Otantik.
 */
class OtantikWhatsApp
{
    public static $lastError = '';

    public static function ready()
    {
        global $config;
        if ((string)($config['otantik_wa_enabled'] ?? '1') !== '1') {
            return false;
        }
        $tok = trim((string)($config['otantik_wa_token'] ?? ''));
        return $tok !== '';
    }

    public static function premium_has_access($admin = null)
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
        // Mirrors OtantikErp::has_access()'s staff-of-platform-owner bypass:
        // Tenant::is_platform() only looks at the logged-in row's own
        // user_type, so a staff sub-account resolves to its owner first.
        if ($oid !== (int)$admin['id']) {
            try {
                $owner = ORM::for_table('tbl_users')->find_one($oid);
                if ($owner && Tenant::is_platform($owner->as_array())) {
                    return true;
                }
            } catch (Throwable $e) {
            }
        }
        try {
            $t = Tenant::tenant_row($oid);
            if (!$t || (int)($t['wa_enabled'] ?? 0) !== 1) {
                return false;
            }
            $until = trim((string)($t['wa_until'] ?? ''));
            if ($until !== '' && strtotime($until) !== false && strtotime($until) < time()) {
                return false;
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function portal_root()
    {
        return rtrim(class_exists('Tenant') ? Tenant::url_portal('') : '', '/');
    }

    public static function hook_url()
    {
        return self::portal_root() . '/w/hook';
    }

    public static function e164($phone)
    {
        $p = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : preg_replace('/\D+/', '', (string)$phone);
        if (strlen($p) === 9 && isset($p[0]) && $p[0] === '6') {
            return '237' . $p;
        }
        if (strlen($p) === 12 && strpos($p, '237') === 0) {
            return $p;
        }
        return '';
    }

    /** Templates to create / accept in Meta Business Manager (WhatsApp). */
    public static function catalog()
    {
        $base = self::portal_root() . '/w';
        return [
            'wo_hello' => [
                'title' => 'Démarrer la conversation',
                'category' => 'MARKETING',
                'need' => 'Menu : acheter, voir le code, voir le statut',
                'body' => "Bonjour {{1}}, c’est {{2}}.\n\nBesoin de Wi-Fi, de votre code, ou de l’état de votre forfait ? Touchez un bouton, on s’occupe du reste.",
                'vars' => ['prénom ou « client »', 'nom de la zone'],
                'buttons' => [
                    ['type' => 'QUICK_REPLY', 'text' => 'Acheter un forfait'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir mon code'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir le statut'],
                ],
            ],
            'wo_buy' => [
                'title' => 'Acheter un forfait (bouton)',
                'category' => 'UTILITY',
                'need' => 'Lien d’achat direct',
                'body' => "Achetez votre forfait {{1}} en un toucher.\n\nPaiement MTN MoMo ou Orange Money. Aucun ticket n’est créé tant que le paiement n’est pas confirmé.",
                'vars' => ['nom de la zone'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Acheter maintenant', 'url' => $base . '/b/{{1}}'],
                ],
                'url_example' => ['centre-ville'],
            ],
            'wo_code' => [
                'title' => 'Voir le code',
                'category' => 'UTILITY',
                'need' => 'Code Wi-Fi / identifiant',
                'body' => "{{1}} — votre code : {{2}}\nValable jusqu’au {{3}}\n\nTouchez le bouton pour l’afficher à l’écran.",
                'vars' => ['zone', 'code', 'date/heure'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Voir mon code', 'url' => $base . '/c/{{1}}'],
                    ['type' => 'URL', 'text' => 'Acheter un forfait', 'url' => $base . '/b/{{2}}'],
                ],
                'url_example' => ['12.a1b2c3d4e5', 'centre-ville'],
            ],
            'wo_status' => [
                'title' => 'Voir le statut',
                'category' => 'UTILITY',
                'need' => 'Temps restant',
                'body' => "{{1}}\nForfait : {{2}}\nReste : {{3}}\nFin : {{4}}",
                'vars' => ['zone', 'forfait', 'temps restant', 'date/heure'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Voir le statut', 'url' => $base . '/s/{{1}}'],
                    ['type' => 'URL', 'text' => 'Renouveler', 'url' => $base . '/b/{{2}}'],
                ],
                'url_example' => ['12.a1b2c3d4e5', 'centre-ville'],
            ],
            'wo_ticket_ready' => [
                'title' => 'Ticket acheté',
                'category' => 'UTILITY',
                'need' => 'Après paiement Wi-Fi réussi',
                'body' => "{{1}} — votre Wi-Fi est prêt.\n\nForfait : {{2}}\nCode : {{3}}\nValable jusqu’au {{4}}\n\nOuvrez le Wi-Fi de la zone et saisissez ce code, ou touchez un bouton.",
                'vars' => ['zone', 'forfait', 'code', 'date/heure'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Voir mon code', 'url' => $base . '/c/{{1}}'],
                    ['type' => 'URL', 'text' => 'Voir le statut', 'url' => $base . '/s/{{1}}'],
                    ['type' => 'URL', 'text' => 'Acheter un forfait', 'url' => $base . '/b/{{2}}'],
                ],
                'url_example' => ['12.a1b2c3d4e5', 'centre-ville'],
            ],
            'wo_pay_help' => [
                'title' => 'Achat non abouti (relance)',
                'category' => 'UTILITY',
                'need' => 'Convaincre de réessayer après un échec MoMo',
                'body' => "Bonjour, vous avez essayé d’acheter un forfait {{1}} mais ça n’a pas abouti.\n\nAucun ticket n’a été créé. On est là pour vous aider : solde insuffisant, confirmation *126# (MTN) ou #150*50# (Orange), ou simplement un nouvel essai.\n\nComment pouvons-nous vous aider ?",
                'vars' => ['nom de la zone'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Réessayer l’achat', 'url' => $base . '/b/{{1}}'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Besoin d’aide'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir mon code'],
                ],
                'url_example' => ['centre-ville'],
            ],
            'wo_ending_soon' => [
                'title' => 'Rappel 10% / 24 h avant',
                'category' => 'UTILITY',
                'need' => 'Quand il reste ~10 % du forfait (24 h pour un forfait 3 jours)',
                'body' => "Votre forfait {{1}} sur {{2}} se termine bientôt ({{3}} restant).\n\nRenouvelez maintenant pour rester connecté sans coupure.",
                'vars' => ['forfait', 'zone', 'temps restant'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Renouveler', 'url' => $base . '/b/{{1}}'],
                    ['type' => 'URL', 'text' => 'Voir le statut', 'url' => $base . '/s/{{2}}'],
                ],
                'url_example' => ['centre-ville', '12.a1b2c3d4e5'],
            ],
            'wo_ending_now' => [
                'title' => '5 minutes avant la fin',
                'category' => 'UTILITY',
                'need' => 'Annoncer que c’est fini',
                'body' => "Votre forfait {{1}} sur {{2}} est terminé (ou se coupe dans quelques minutes).\n\nReconnectez-vous en achetant un nouveau forfait.",
                'vars' => ['forfait', 'zone'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Acheter un forfait', 'url' => $base . '/b/{{1}}'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir mon code'],
                ],
                'url_example' => ['centre-ville'],
            ],
            'wo_contact_ack' => [
                'title' => 'Message portail reçu',
                'category' => 'UTILITY',
                'need' => 'Accusé de réception contact',
                'body' => "Nous avons bien reçu votre message pour {{1}}.\n\nNous vous répondons ici, par appel ou WhatsApp. En attendant, vous pouvez acheter un forfait ou revoir votre code.",
                'vars' => ['zone'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Acheter un forfait', 'url' => $base . '/b/{{1}}'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir mon code'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Voir le statut'],
                ],
                'url_example' => ['centre-ville'],
            ],
            'wo_pppoe_ready' => [
                'title' => 'Ligne internet maison créée',
                'category' => 'UTILITY',
                'need' => 'Identifiants PPPoE',
                'body' => "Votre internet maison {{1}} est prêt.\n\nIdentifiant : {{2}}\nMot de passe : {{3}}\nService : pppoe-otantik\n\nCréez une connexion PPPoE sur le routeur / ONU avec ces identifiants.",
                'vars' => ['zone', 'identifiant', 'mot de passe'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Recharger', 'url' => $base . '/b/{{1}}'],
                    ['type' => 'URL', 'text' => 'Voir le statut', 'url' => $base . '/s/{{2}}'],
                ],
                'url_example' => ['quartier-nord', '12.a1b2c3d4e5'],
            ],
            'wo_pppoe_recharged' => [
                'title' => 'Ligne rechargée',
                'category' => 'UTILITY',
                'need' => 'Après recharge PPPoE',
                'body' => "Ligne {{1}} rechargée.\nForfait : {{2}} — {{3}} FCFA\nValable jusqu’au {{4}}",
                'vars' => ['identifiant', 'forfait', 'montant', 'date/heure'],
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Voir le statut', 'url' => $base . '/s/{{1}}'],
                    ['type' => 'URL', 'text' => 'Recharger', 'url' => $base . '/b/{{2}}'],
                ],
                'url_example' => ['1a2b3c4d', 'quartier-nord'],
            ],
            'wo_withdraw' => [
                'title' => 'Retrait envoyé / échoué',
                'category' => 'UTILITY',
                'need' => 'Exploitant, après virement',
                'body' => "Retrait {{1}} FCFA vers {{2}} : {{3}}.\nRéf : {{4}}",
                'vars' => ['montant', 'numéro', 'statut', 'référence'],
            ],
            'wo_zone_down' => [
                'title' => 'Zone hors ligne',
                'category' => 'UTILITY',
                'need' => 'Alerte exploitant — prioritaire',
                'body' => "Alerte : la zone {{1}} ne répond plus.\n\nVérifiez le routeur, l’électricité et le tunnel. Les clients ne peuvent plus se connecter.",
                'vars' => ['nom de la zone'],
                'buttons' => [
                    ['type' => 'QUICK_REPLY', 'text' => 'C’est réglé'],
                ],
            ],
        ];
    }

    public static function token($kind, $id)
    {
        $id = (int)$id;
        $kind = preg_replace('/[^a-z]/', '', (string)$kind);
        $sig = substr(hash_hmac('sha256', $kind . '|' . $id, self::secret()), 0, 10);
        return $id . '.' . $sig;
    }

    public static function parse_token($kind, $raw)
    {
        $raw = trim((string)$raw);
        $kind = preg_replace('/[^a-z]/', '', (string)$kind);
        if (!preg_match('/^(\d+)\.([a-f0-9]{10})$/', $raw, $m)) {
            return [false, 0];
        }
        $expect = substr(hash_hmac('sha256', $kind . '|' . $m[1], self::secret()), 0, 10);
        if (!hash_equals($expect, $m[2])) {
            return [false, 0];
        }
        return [true, (int)$m[1]];
    }

    private static function secret()
    {
        global $config;
        $t = trim((string)($config['otantik_wa_token'] ?? ''));
        if ($t !== '') {
            return $t;
        }
        return 'wo-wa-' . (string)($config['otantik_wa_phone_id'] ?? 'otantik');
    }

    public static function land_buy($slug)
    {
        $slug = preg_replace('/[^a-z0-9_-]/i', '', (string)$slug);
        if ($slug === '') {
            return self::portal_root() . '/';
        }
        if (function_exists('wifi_otantik_checkout_url')) {
            return wifi_otantik_checkout_url($slug, 0, false);
        }
        return self::portal_root() . '/wifi/' . rawurlencode($slug) . '/buy';
    }

    public static function land_code($rechargeId)
    {
        return self::portal_root() . '/w/c/' . rawurlencode(self::token('c', $rechargeId));
    }

    public static function land_status($rechargeId)
    {
        return self::portal_root() . '/w/s/' . rawurlencode(self::token('s', $rechargeId));
    }

    public static function notify_ticket($cust, $plan, $router, $user, $pass, $kind = 'Hotspot')
    {
        $phone = self::phone_of($cust, '');
        if ($phone === '' || !$router) {
            return false;
        }
        $rech = self::latest_recharge($user, $router);
        $rid = $rech ? (int)$rech['id'] : 0;
        $zone = self::zone_title($router);
        $planName = (string)($plan['name_plan'] ?? 'Forfait');
        $exp = $rech ? self::end_label($rech) : '';
        $slug = (string)($router['slug'] ?? '');
        $kind = strtoupper((string)$kind) === 'PPPOE' ? 'PPPOE' : 'Hotspot';
        if ($kind === 'PPPOE') {
            $prev = 0;
            try {
                $prev = (int)ORM::for_table('tbl_user_recharges')
                    ->where('username', $user)
                    ->where('routers', $router['name'])
                    ->count();
            } catch (Throwable $e) {
            }
            if ($prev > 1) {
                $amount = (string)(int)round((float)($plan['price'] ?? 0));
                return self::send_tpl($phone, 'wo_pppoe_recharged', [
                    $user ?: '—',
                    $planName,
                    $amount,
                    $exp !== '' ? $exp : '—',
                ], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'recharge', $rid);
            }
            return self::send_tpl($phone, 'wo_pppoe_ready', [
                $zone,
                $user ?: '—',
                $pass !== '' ? $pass : $user,
            ], [
                ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
            ], 'recharge', $rid);
        }
        $codeTok = $rid ? self::token('c', $rid) : $slug;
        return self::send_tpl($phone, 'wo_ticket_ready', [
            $zone,
            $planName,
            (string)$user,
            $exp !== '' ? $exp : '—',
        ], [
            ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $codeTok]]],
            ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => $rid ? self::token('s', $rid) : $codeTok]]],
            ['type' => 'button', 'sub_type' => 'url', 'index' => '2', 'parameters' => [['type' => 'text', 'text' => $slug]]],
        ], 'recharge', $rid);
    }

    public static function notify_pay_help($phone, $router, $why = 'unknown', $kind = 'Hotspot')
    {
        $phone = self::e164($phone) ? (function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone) : '';
        $why = preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$why));
        if (!in_array($why, ['refused', 'cancelled', 'expired', 'busy', 'network', 'unknown'], true)) {
            return false;
        }
        if ($phone === '' || !$router) {
            return false;
        }
        $zone = self::zone_title($router);
        $slug = (string)($router['slug'] ?? '');
        return self::send_tpl($phone, 'wo_pay_help', [$zone], [
            ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
        ], 'pay_help', (int)($router['id'] ?? 0), 1800);
    }

    public static function notify_contact($phone, $router)
    {
        $phone = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
        if ($phone === '' || !$router) {
            return false;
        }
        $slug = (string)($router['slug'] ?? '');
        return self::send_tpl($phone, 'wo_contact_ack', [self::zone_title($router)], [
            ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
        ], 'contact', (int)($router['id'] ?? 0), 600);
    }

    /** Templates an admin can manually send to a given subscriber (tbl_customers row). */
    public static function customer_catalog()
    {
        return [
            'wo_hello' => 'Démarrer la conversation',
            'wo_code' => 'Voir le code',
            'wo_status' => 'Voir le statut',
            'wo_ticket_ready' => 'Ticket acheté',
            'wo_contact_ack' => 'Message portail reçu',
            'wo_ending_soon' => 'Rappel avant expiration',
            'wo_ending_now' => 'Forfait terminé',
        ];
    }

    public static function send_customer_tpl($cust, $tpl)
    {
        if (!$cust) {
            return false;
        }
        $phone = self::phone_of($cust, '');
        if ($phone === '') {
            self::$lastError = 'numéro introuvable pour cet abonné';
            return false;
        }
        $cid = (int)($cust['id'] ?? 0);
        $user = (string)($cust['username'] ?? '');
        $rech = null;
        try {
            if ($user !== '') {
                $rech = ORM::for_table('tbl_user_recharges')->where('username', $user)->order_by_desc('id')->find_one();
            }
        } catch (Throwable $e) {
        }
        $router = $rech ? ORM::for_table('tbl_routers')->where('name', $rech['routers'])->find_one() : null;
        $zone = $router ? self::zone_title($router) : 'Wifi-Otantik';
        $slug = $router ? (string)$router['slug'] : '';
        $rid = $rech ? (int)$rech['id'] : 0;
        $info = ($rech && function_exists('wifi_otantik_expiry_info')) ? wifi_otantik_expiry_info($rech) : [];
        $planName = $rech ? (string)($rech['namebp'] ?? 'Forfait') : 'Forfait';
        $exp = $rech ? self::end_label($rech) : '';
        $left = !empty($info['expired']) ? 'Terminé' : (string)($info['label'] ?? '');
        switch ($tpl) {
            case 'wo_hello':
                return self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'manual', $cid, 0);
            case 'wo_code':
                if (!$rech) {
                    self::$lastError = 'aucun ticket pour cet abonné';
                    return false;
                }
                return self::send_tpl($phone, 'wo_code', [$zone, $user, $exp !== '' ? $exp : '—'], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => self::token('c', $rid)]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'manual', $cid, 0);
            case 'wo_status':
                if (!$rech) {
                    self::$lastError = 'aucun ticket pour cet abonné';
                    return false;
                }
                return self::send_tpl($phone, 'wo_status', [$zone, $planName, $left !== '' ? $left : '—', $exp !== '' ? $exp : '—'], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'manual', $cid, 0);
            case 'wo_ticket_ready':
                if (!$rech) {
                    self::$lastError = 'aucun ticket pour cet abonné';
                    return false;
                }
                return self::send_tpl($phone, 'wo_ticket_ready', [$zone, $planName, $user, $exp !== '' ? $exp : '—'], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => self::token('c', $rid)]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '2', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'manual', $cid, 0);
            case 'wo_contact_ack':
                if (!$router) {
                    self::$lastError = 'aucune zone associée à cet abonné';
                    return false;
                }
                return self::send_tpl($phone, 'wo_contact_ack', [$zone], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'manual', $cid, 0);
            case 'wo_ending_soon':
                if (!$rech) {
                    self::$lastError = 'aucun ticket pour cet abonné';
                    return false;
                }
                return self::send_tpl($phone, 'wo_ending_soon', [$planName, $zone, $left !== '' ? $left : '—'], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
                ], 'manual', $cid, 0);
            case 'wo_ending_now':
                return self::send_tpl($phone, 'wo_ending_now', [$planName, $zone], [
                    ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ], 'manual', $cid, 0);
            default:
                self::$lastError = 'modèle non pris en charge pour cet abonné';
                return false;
        }
    }

    public static function notify_withdraw($phone, $amount, $status, $ref = '')
    {
        $phone = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
        if ($phone === '') {
            return false;
        }
        $st = strtoupper((string)$status);
        $label = $st === 'FAILED' ? 'échoué' : ($st === 'PENDING' ? 'en cours' : 'envoyé');
        return self::send_tpl($phone, 'wo_withdraw', [
            (string)(int)$amount,
            $phone,
            $label,
            $ref !== '' ? $ref : '—',
        ], [], 'withdraw', 0);
    }

    public static function notify_zone_down($router)
    {
        if (!$router) {
            return false;
        }
        $ownerId = (int)($router['owner_id'] ?? 0);
        $phone = '';
        try {
            $t = $ownerId ? ORM::for_table('tbl_otantik_tenants')->where('user_id', $ownerId)->find_one() : null;
            $phone = $t ? (string)($t['wa_notify_phone'] ?? '') : '';
            if ($phone === '') {
                $phone = $t ? (string)($t['phone'] ?? '') : '';
            }
            if ($phone === '' && $ownerId) {
                $u = ORM::for_table('tbl_users')->find_one($ownerId);
                $phone = $u ? (string)($u['phone'] ?? '') : '';
            }
        } catch (Throwable $e) {
        }
        $phone = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
        if ($phone === '') {
            return false;
        }
        $rid = (int)($router['id'] ?? 0);
        if ($rid && self::already('wo_zone_ack', 'zone', $rid, 86400, $phone)) {
            return false;
        }
        $name = (string)($router['name'] ?? 'Zone');
        return self::send_tpl($phone, 'wo_zone_down', [$name], [], 'zone', $rid, 7200);
    }

    public static function cron()
    {
        if (!self::ready()) {
            return ['soon' => 0, 'now' => 0, 'down' => 0];
        }
        $soon = 0;
        $now = 0;
        try {
            $rows = ORM::for_table('tbl_user_recharges')->where('status', 'on')->find_many();
        } catch (Throwable $e) {
            $rows = [];
        }
        foreach ($rows as $row) {
            $info = function_exists('wifi_otantik_expiry_info') ? wifi_otantik_expiry_info($row) : [];
            if (empty($info) || !empty($info['expired']) || !empty($info['none'])) {
                continue;
            }
            $left = (int)($info['left'] ?? 0);
            $endTs = (int)($info['end_ts'] ?? 0);
            $start = strtotime(trim((string)$row['recharged_on'] . ' ' . (string)($row['recharged_time'] ?? '00:00:00')));
            $total = ($endTs && $start) ? ($endTs - $start) : 0;
            if ($left <= 0 || $total < 60) {
                continue;
            }
            $soonAt = self::soon_seconds($total);
            if ($left <= 300) {
                if (self::remind($row, 'now')) {
                    $now++;
                }
            } elseif ($soonAt > 0 && $left <= $soonAt) {
                if (self::remind($row, 'soon')) {
                    $soon++;
                }
            }
        }
        $down = self::cron_zones();
        return ['soon' => $soon, 'now' => $now, 'down' => $down];
    }

    public static function soon_seconds($total)
    {
        $total = (int)$total;
        if ($total < 12 * 60) {
            return 0;
        }
        $pct = (int)round($total * 0.10);
        if ($total >= 3 * 86400) {
            $pct = max($pct, 86400);
        }
        $pct = max($pct, 300);
        if ($pct < 780) {
            $pct = 900;
        }
        if ($pct >= (int)($total * 0.5)) {
            return 0;
        }
        return $pct;
    }

    public static function webhook()
    {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        global $config;
        if ($method === 'GET') {
            $mode = (string)($_GET['hub_mode'] ?? ($_GET['hub.mode'] ?? ''));
            $token = (string)($_GET['hub_verify_token'] ?? ($_GET['hub.verify_token'] ?? ''));
            $challenge = (string)($_GET['hub_challenge'] ?? ($_GET['hub.challenge'] ?? ''));
            $expect = trim((string)($config['otantik_wa_verify'] ?? ''));
            if ($mode === 'subscribe' && $expect !== '' && hash_equals($expect, $token)) {
                header('Content-Type: text/plain; charset=utf-8');
                echo $challenge;
                exit;
            }
            http_response_code(403);
            echo 'forbidden';
            exit;
        }
        $raw = (string)file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            echo 'ok';
            exit;
        }
        foreach ((array)($data['entry'] ?? []) as $entry) {
            foreach ((array)($entry['changes'] ?? []) as $ch) {
                $val = $ch['value'] ?? [];
                foreach ((array)($val['contacts'] ?? []) as $c) {
                    self::save_contact_name((string)($c['wa_id'] ?? ''), (string)($c['profile']['name'] ?? ''));
                }
                foreach ((array)($val['messages'] ?? []) as $msg) {
                    self::handle_in($msg);
                }
            }
        }
        echo 'ok';
        exit;
    }

    /** wa_id in the webhook is full E.164 digits (237...) - store by local 9-digit number, matching every other phone key in this app. */
    private static function save_contact_name($waId, $name)
    {
        $name = trim((string)$name);
        $local = $waId !== '' ? substr($waId, 3) : '';
        if ($local === '' || $name === '') {
            return;
        }
        try {
            $row = ORM::for_table('tbl_otantik_wa_contacts')->find_one($local);
            if (!$row) {
                $row = ORM::for_table('tbl_otantik_wa_contacts')->create();
                $row->phone = $local;
            }
            $row->display_name = mb_substr($name, 0, 128);
            $row->updated_at = date('Y-m-d H:i:s');
            $row->save();
        } catch (Throwable $e) {
        }
    }

    public static function contact_name($phone)
    {
        try {
            $row = ORM::for_table('tbl_otantik_wa_contacts')->find_one((string)$phone);
            return $row ? (string)$row['display_name'] : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    public static function landing($kind, $key)
    {
        $kind = strtolower(trim((string)$kind));
        $key = trim((string)$key);
        if ($kind === 'b') {
            header('Location: ' . self::land_buy($key), true, 302);
            exit;
        }
        list($ok, $id) = self::parse_token($kind, $key);
        $use = $kind;
        if (!$ok || $id < 1) {
            self::page('Lien invalide', 'Ce lien WhatsApp a expiré. Achetez un forfait depuis le portail Wi-Fi.', '');
        }
        $row = null;
        try {
            $row = ORM::for_table('tbl_user_recharges')->find_one($id);
        } catch (Throwable $e) {
        }
        if (!$row) {
            self::page('Introuvable', 'Aucun forfait ne correspond. Achetez un forfait depuis le portail.', '');
        }
        $router = ORM::for_table('tbl_routers')->where('name', $row['routers'])->find_one();
        $slug = $router ? (string)$router['slug'] : '';
        $buy = $slug !== '' ? self::land_buy($slug) : self::portal_root() . '/';
        $info = function_exists('wifi_otantik_expiry_info') ? wifi_otantik_expiry_info($row) : [];
        $code = (string)$row['username'];
        $zone = $router ? self::zone_title($router) : (string)$row['routers'];
        $plan = (string)($row['namebp'] ?? '');
        $left = !empty($info['expired']) ? 'Terminé' : (string)($info['label'] ?? '—');
        $end = (string)($info['end_label'] ?? '');
        if ($use === 'b') {
            header('Location: ' . $buy, true, 302);
            exit;
        }
        if ($use === 'c') {
            $kindPlan = strtoupper((string)($row['type'] ?? '')) === 'PPPOE' ? 'Identifiant' : 'Code Wi-Fi';
            $extra = $kindPlan === 'Identifiant' ? 'Service PPPoE : pppoe-otantik' : 'Saisissez ce code sur le portail Wi-Fi de la zone.';
            self::page($kindPlan, $code, $buy, $zone . ' · ' . $plan . ($end !== '' ? ' · jusqu’au ' . $end : ''), $extra);
        }
        self::page('Statut', $left, $buy, $zone . ' · ' . $plan, $end !== '' ? ('Fin : ' . $end . ' · Code : ' . $code) : $code);
    }

    private static function remind($row, $phase)
    {
        $rid = (int)$row['id'];
        $tpl = $phase === 'now' ? 'wo_ending_now' : 'wo_ending_soon';
        if (self::already($tpl, 'recharge', $rid, $phase === 'now' ? 7200 : 86400 * 3)) {
            return false;
        }
        $router = ORM::for_table('tbl_routers')->where('name', $row['routers'])->find_one();
        $cust = null;
        try {
            if ((int)($row['customer_id'] ?? 0) > 0) {
                $cust = ORM::for_table('tbl_customers')->find_one((int)$row['customer_id']);
            }
            if (!$cust) {
                $cust = ORM::for_table('tbl_customers')->where('username', $row['username'])->find_one();
            }
        } catch (Throwable $e) {
        }
        $phone = self::phone_of($cust, '');
        if ($phone === '' && $cust) {
            $phone = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($cust['phonenumber'] ?? '') : '';
        }
        if ($phone === '') {
            return false;
        }
        $zone = $router ? self::zone_title($router) : (string)$row['routers'];
        $plan = (string)($row['namebp'] ?? 'Forfait');
        $slug = $router ? (string)$router['slug'] : '';
        $info = function_exists('wifi_otantik_expiry_info') ? wifi_otantik_expiry_info($row) : [];
        $left = (string)($info['label'] ?? 'bientôt');
        if ($phase === 'now') {
            $ok = self::send_tpl($phone, 'wo_ending_now', [$plan, $zone], [
                ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
            ], 'recharge', $rid, 7200);
        } else {
            $ok = self::send_tpl($phone, 'wo_ending_soon', [$plan, $zone, $left], [
                ['type' => 'button', 'sub_type' => 'url', 'index' => '0', 'parameters' => [['type' => 'text', 'text' => $slug]]],
                ['type' => 'button', 'sub_type' => 'url', 'index' => '1', 'parameters' => [['type' => 'text', 'text' => self::token('s', $rid)]]],
            ], 'recharge', $rid, 86400 * 3);
        }
        return $ok;
    }

    /**
     * Marker file: "a down alert was already sent for this router's CURRENT
     * outage". Without this, cron_zones() re-notified every ~2h for as long
     * as an outage lasted (send_tpl's own TTL only throttles frequency, it
     * doesn't stop at one). This makes the alert edge-triggered: sent once
     * when it goes down, then silent until zone_alert_clear() runs (called
     * from the peer heartbeat once it's confirmed back online), after which
     * the next outage fires a fresh alert.
     */
    private static function zone_alert_file($rid)
    {
        return rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wo-zone-alerted-' . (int)$rid;
    }

    public static function zone_alert_clear($rid)
    {
        $f = self::zone_alert_file($rid);
        if (is_file($f)) {
            @unlink($f);
        }
    }

    /**
     * The peer heartbeat (last_seen) can go stale for reasons that have
     * nothing to do with the router's actual health - a broken scheduler
     * script on the router, a one-off tunnel hiccup on just that path.
     * Verified live: all three production routers had last_seen stale by
     * 3-15 days, yet a direct RouterOS API call to the worst of them
     * (15 days stale) succeeded immediately. Never alert on heartbeat
     * staleness alone - confirm the router is actually unreachable first.
     * On success this also self-heals last_seen/status, so a broken
     * heartbeat script doesn't leave the UI showing stale state either.
     */
    private static function router_actually_reachable($router)
    {
        if (!function_exists('wifi_otantik_mk_client') || !function_exists('wifi_otantik_ros_rows')) {
            return false;
        }
        try {
            $client = wifi_otantik_mk_client($router, 4, true);
            $rows = wifi_otantik_ros_rows($client, '/system/resource/print', 1, 'uptime');
            if (!$rows) {
                return false;
            }
            try {
                $peer = ORM::for_table('tbl_otantik_peers')->where('router_id', (int)$router['id'])->find_one();
                if ($peer) {
                    $peer->status = 'completed';
                    $peer->last_seen = date('Y-m-d H:i:s');
                    $peer->save();
                }
            } catch (Throwable $e) {
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function cron_zones()
    {
        $n = 0;
        $cut = date('Y-m-d H:i:s', time() - 900);
        try {
            $peers = ORM::for_table('tbl_otantik_peers')->where('status', 'completed')->where_lt('last_seen', $cut)->find_many();
        } catch (Throwable $e) {
            return 0;
        }
        foreach ($peers as $p) {
            $rid = (int)$p['router_id'];
            $r = ORM::for_table('tbl_routers')->find_one($rid);
            if (!$r) {
                continue;
            }
            if (self::router_actually_reachable($r)) {
                self::zone_alert_clear($rid);
                continue;
            }
            if (is_file(self::zone_alert_file($rid))) {
                continue;
            }
            if (self::notify_zone_down($r)) {
                @file_put_contents(self::zone_alert_file($rid), (string)time());
                $n++;
            }
        }
        return $n;
    }

    private static function handle_in($msg)
    {
        $from = self::e164($msg['from'] ?? '');
        $phone = $from !== '' ? substr($from, 3) : '';
        $text = strtolower(trim((string)($msg['text']['body'] ?? '')));
        $btn = strtolower(trim((string)($msg['button']['text'] ?? ($msg['interactive']['button_reply']['title'] ?? ''))));
        $btnId = strtolower(trim((string)($msg['interactive']['button_reply']['id'] ?? '')));
        $all = trim($btnId . ' ' . $btn . ' ' . $text);
        if ($phone === '') {
            return;
        }
        $shown = trim((string)($msg['text']['body'] ?? ($msg['button']['text'] ?? ($msg['interactive']['button_reply']['title'] ?? ''))));
        if ($shown === '' && $btnId !== '') {
            $shown = $btnId;
        }
        self::log_event('in', 'in', $phone, true, $shown, $btnId);
        if (preg_match('/réglé|regle|c[\'’ ]?est r[eé]gl/', $all) && self::ack_zone($phone)) {
            self::send_session($phone, 'Merci. On arrête l’alerte pour cette zone pendant 24 h.');
            return;
        }
        $intent = 'help';
        if (in_array($btnId, ['buy', 'code', 'status', 'help', 'hello'], true)) {
            $intent = $btnId;
        } elseif (preg_match('/acheter|forfait|buy|renouvel|recharg/', $all)) {
            $intent = 'buy';
        } elseif (preg_match('/code|ticket|identifiant/', $all)) {
            $intent = 'code';
        } elseif (preg_match('/statut|status|reste/', $all)) {
            $intent = 'status';
        } elseif (preg_match('/aide|help|besoin/', $all)) {
            $intent = 'help';
        } elseif ($all === '' || preg_match('/bonjour|salut|hello|menu/', $all)) {
            $intent = 'hello';
        }
        $ctx = self::context_phone($phone);
        $zone = $ctx['zone'] !== '' ? $ctx['zone'] : 'Wifi-Otantik';
        $slug = $ctx['slug'];
        $code = $ctx['code'];
        $left = $ctx['left'];
        $end = $ctx['end'];
        $menu = [
            ['type' => 'reply', 'id' => 'buy', 'text' => 'Acheter un forfait'],
            ['type' => 'reply', 'id' => 'code', 'text' => 'Voir mon code'],
            ['type' => 'reply', 'id' => 'status', 'text' => 'Voir le statut'],
        ];
        if ($intent === 'buy') {
            if ($slug === '') {
                self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'hello', 0, 3600);
                return;
            }
            self::send_session($phone, 'Achetez votre forfait ' . $zone . ' en un toucher. Paiement MTN MoMo ou Orange Money.', [
                ['type' => 'url', 'text' => 'Acheter maintenant', 'url' => self::land_buy($slug)],
            ]);
            return;
        }
        if ($intent === 'code') {
            if ($code === '') {
                if ($slug !== '') {
                    self::send_session($phone, 'Aucun code actif pour ce numéro. Achetez un forfait pour vous connecter.', [
                        ['type' => 'url', 'text' => 'Acheter maintenant', 'url' => self::land_buy($slug)],
                    ]);
                } else {
                    self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'hello', 0, 3600);
                }
                return;
            }
            $line = $zone . ' — votre code : *' . $code . '*' . ($end !== '' ? "\nValable jusqu’au " . $end : '');
            self::send_session($phone, $line, [
                ['type' => 'reply', 'id' => 'buy', 'text' => 'Acheter un forfait'],
                ['type' => 'reply', 'id' => 'status', 'text' => 'Voir le statut'],
            ]);
            return;
        }
        if ($intent === 'status') {
            if ($left === '') {
                if ($slug !== '') {
                    self::send_session($phone, 'Aucun forfait actif. Achetez-en un pour vous reconnecter.', [
                        ['type' => 'url', 'text' => 'Acheter maintenant', 'url' => self::land_buy($slug)],
                    ]);
                } else {
                    self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'hello', 0, 3600);
                }
                return;
            }
            self::send_session($phone, $zone . "\nReste : " . $left . ($end !== '' ? "\nFin : " . $end : ''), [
                ['type' => 'url', 'text' => 'Renouveler', 'url' => self::land_buy($slug)],
                ['type' => 'reply', 'id' => 'code', 'text' => 'Voir mon code'],
            ]);
            return;
        }
        if ($intent === 'hello') {
            self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'hello', 0, 86400);
            return;
        }
        $ok = self::send_session(
            $phone,
            "On est là pour vous aider.\n\n• Acheter un forfait\n• Voir votre code\n• Voir le temps restant\n\nTouchez un bouton, ou écrivez-nous.",
            $menu
        );
        if (!$ok) {
            self::send_tpl($phone, 'wo_hello', ['client', $zone], [], 'hello', 0, 3600);
        }
    }

    private static function ack_zone($phone)
    {
        $local = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : preg_replace('/\D+/', '', (string)$phone);
        if (strlen($local) < 9) {
            return false;
        }
        $n = 0;
        try {
            $tenants = ORM::for_table('tbl_otantik_tenants')->find_many();
            foreach ($tenants as $t) {
                $tp = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($t['phone'] ?? '') : '';
                if ($tp !== $local) {
                    $u = ORM::for_table('tbl_users')->find_one((int)($t['user_id'] ?? 0));
                    $up = $u && function_exists('wifi_otantik_phone') ? wifi_otantik_phone($u['phone'] ?? '') : '';
                    if ($up !== $local) {
                        continue;
                    }
                }
                $owner = (int)($t['user_id'] ?? 0);
                if ($owner < 1) {
                    continue;
                }
                $routers = ORM::for_table('tbl_routers')->where('owner_id', $owner)->find_many();
                foreach ($routers as $r) {
                    self::mark('wo_zone_ack', 'zone', (int)$r['id'], $local);
                    $n++;
                }
            }
        } catch (Throwable $e) {
            return false;
        }
        return $n > 0;
    }

    private static function context_phone($phone)
    {
        $out = ['zone' => 'Wifi-Otantik', 'slug' => '', 'code' => '', 'left' => '', 'end' => '', 'rid' => 0];
        $cust = null;
        try {
            if (function_exists('wifi_otantik_hs_find_customer')) {
                $cust = wifi_otantik_hs_find_customer($phone, null);
            }
        } catch (Throwable $e) {
        }
        $user = $cust ? (string)$cust['username'] : '';
        $row = null;
        try {
            if ($user !== '') {
                $row = ORM::for_table('tbl_user_recharges')->where('username', $user)->order_by_desc('id')->find_one();
            }
            if (!$row) {
                $trx = ORM::for_table('tbl_payment_gateway')->where('username', $phone)->order_by_desc('id')->find_one();
                if ($trx && (string)($trx['payment_channel'] ?? '') !== '') {
                    $parts = explode(':', (string)$trx['payment_channel'], 2);
                    $u = trim((string)($parts[0] ?? ''));
                    if ($u !== '') {
                        $row = ORM::for_table('tbl_user_recharges')->where('username', $u)->order_by_desc('id')->find_one();
                    }
                    $rid = (int)($trx['routers_id'] ?? 0);
                    if ($rid && $out['slug'] === '') {
                        $r = ORM::for_table('tbl_routers')->find_one($rid);
                        if ($r) {
                            $out['slug'] = (string)$r['slug'];
                            $out['zone'] = self::zone_title($r);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
        }
        if ($row) {
            $out['rid'] = (int)$row['id'];
            $out['code'] = (string)$row['username'];
            $info = function_exists('wifi_otantik_expiry_info') ? wifi_otantik_expiry_info($row) : [];
            $out['left'] = !empty($info['expired']) ? 'Terminé' : (string)($info['label'] ?? '');
            $out['end'] = (string)($info['end_label'] ?? '');
            $r = ORM::for_table('tbl_routers')->where('name', $row['routers'])->find_one();
            if ($r) {
                $out['slug'] = (string)$r['slug'];
                $out['zone'] = self::zone_title($r);
            }
        }
        return $out;
    }

    public static function send_tpl($phone, $name, $bodyVars = [], $buttons = [], $refType = '', $refId = 0, $ttl = 0)
    {
        if (!self::ready()) {
            return false;
        }
        $local = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
        $to = self::e164($phone);
        if ($to === '') {
            self::$lastError = 'numéro invalide : reçu "' . $phone . '", normalisé "' . $local . '" (attendu 9 chiffres commençant par 6)';
            return false;
        }
        if ($ttl > 0 && self::already($name, $refType, $refId, $ttl, $local)) {
            self::$lastError = 'déjà envoyé récemment';
            return false;
        }
        $components = [];
        if ($name === 'wo_hello') {
            $logoUrl = class_exists('Tenant') ? Tenant::url_portal('/ui/ui_custom/images/logo.png') : '';
            if ($logoUrl !== '') {
                $components[] = ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['link' => $logoUrl]]]];
            }
        }
        if ($bodyVars) {
            $params = [];
            foreach ($bodyVars as $v) {
                $t = trim((string)$v);
                $params[] = ['type' => 'text', 'text' => $t !== '' ? mb_substr($t, 0, 255) : '—'];
            }
            $components[] = ['type' => 'body', 'parameters' => $params];
        }
        foreach ($buttons as $b) {
            $components[] = $b;
        }
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $name,
                'language' => ['code' => 'fr'],
                'components' => $components,
            ],
        ];
        $ok = self::graph($payload);
        self::log_event('out', 'tpl', $local, $ok, $ok ? implode(' · ', array_map('strval', $bodyVars)) : self::$lastError, $name);
        if ($ok) {
            self::mark($name, $refType, $refId, $local);
        }
        return $ok;
    }

    public static function send_session($phone, $text, $buttons = [])
    {
        if (!self::ready()) {
            return false;
        }
        $to = self::e164($phone);
        if ($to === '') {
            $local = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
            self::$lastError = 'numéro invalide : reçu "' . $phone . '", normalisé "' . $local . '" (attendu 9 chiffres commençant par 6)';
            return false;
        }
        $text = mb_substr(trim((string)$text), 0, 1024);
        $cta = null;
        $replies = [];
        foreach ($buttons as $b) {
            $type = (string)($b['type'] ?? 'reply');
            if ($type === 'url' && !empty($b['url']) && $cta === null) {
                $cta = $b;
                continue;
            }
            $title = mb_substr(trim((string)($b['text'] ?? '')), 0, 20);
            if ($title === '' || count($replies) >= 3) {
                continue;
            }
            $id = preg_replace('/[^a-z0-9_-]/', '', strtolower((string)($b['id'] ?? '')));
            if ($id === '') {
                $id = 'opt' . count($replies);
            }
            $replies[] = [
                'type' => 'reply',
                'reply' => ['id' => mb_substr($id, 0, 256), 'title' => $title],
            ];
        }
        if ($cta && $replies) {
            $text .= "\n" . mb_substr(trim((string)($cta['text'] ?? 'Lien')), 0, 40) . ' : ' . $cta['url'];
            $text = mb_substr($text, 0, 1024);
        }
        $local = function_exists('wifi_otantik_phone') ? wifi_otantik_phone($phone) : $phone;
        $note = $text;
        if ($cta && !$replies) {
            $ok = self::graph([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'cta_url',
                    'body' => ['text' => $text],
                    'action' => [
                        'name' => 'cta_url',
                        'parameters' => [
                            'display_text' => mb_substr(trim((string)($cta['text'] ?? 'Ouvrir')), 0, 20),
                            'url' => (string)$cta['url'],
                        ],
                    ],
                ],
            ]);
            if ($ok) {
                self::log_event('out', 'session', $local, true, $note);
                return true;
            }
        }
        if ($replies) {
            $ok = self::graph([
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => ['text' => $text],
                    'action' => ['buttons' => $replies],
                ],
            ]);
            if ($ok) {
                self::log_event('out', 'session', $local, true, $note);
                return true;
            }
        }
        $lines = $text;
        foreach ($buttons as $b) {
            if (!empty($b['url'])) {
                $lines .= "\n" . ($b['text'] ?? 'Lien') . ' : ' . $b['url'];
            }
        }
        $ok = self::graph([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => mb_substr($lines, 0, 4096), 'preview_url' => true],
        ]);
        self::log_event('out', 'session', $local, $ok, $ok ? $note : self::$lastError);
        return $ok;
    }

    public static function log_event($direction, $kind, $phone, $ok, $detail = '', $tpl = '')
    {
        try {
            if (class_exists('Tenant')) {
                Tenant::ensure_schema();
            }
            $row = ORM::for_table('tbl_otantik_wa_logs')->create();
            $row->direction = $direction === 'in' ? 'in' : 'out';
            $row->kind = mb_substr((string)$kind, 0, 16);
            $row->tpl = mb_substr((string)$tpl, 0, 64);
            $row->phone = mb_substr((string)$phone, 0, 32);
            $row->ok = $ok ? 1 : 0;
            $row->detail = mb_substr(trim((string)$detail), 0, 500);
            $row->created_at = date('Y-m-d H:i:s');
            $row->save();
        } catch (Throwable $e) {
        }
    }

    public static function tpl_label($name)
    {
        $cat = self::catalog();
        $name = (string)$name;
        if ($name !== '' && isset($cat[$name]['title'])) {
            return (string)$cat[$name]['title'];
        }
        if ($name === '') {
            return 'Réponse';
        }
        return $name;
    }

    /**
     * Sends directly via Meta's WhatsApp Cloud API (graph.facebook.com),
     * migrated off the WhatsChatAds BSP wrapper on 2026-10. $payload is
     * already built by every caller in native Meta Graph shape (see
     * send_tpl()/send_session() - messaging_product/to/type/template or
     * interactive or text), so unlike the old WhatsChatAds version this
     * posts it through unchanged - no reshaping needed.
     */
    private static function graph($payload)
    {
        global $config;
        self::$lastError = '';
        $tok = trim((string)($config['otantik_wa_token'] ?? ''));
        $phoneId = trim((string)($config['otantik_wa_phone_id'] ?? ''));
        if ($tok === '' || $phoneId === '') {
            self::$lastError = 'jeton ou identifiant de numéro WhatsApp manquant';
            self::store_diag(self::$lastError);
            return false;
        }
        $url = 'https://graph.facebook.com/v23.0/' . rawurlencode($phoneId) . '/messages';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $tok,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) {
            self::$lastError = $err ?: 'curl';
            self::store_diag(self::$lastError);
            return false;
        }
        $j = json_decode((string)$raw, true);
        if ($code >= 200 && $code < 300 && !empty($j['messages'][0]['id'])) {
            self::store_diag('');
            return true;
        }
        $e = $j['error'] ?? null;
        if (is_array($e)) {
            $msg = (string)($e['error_user_msg'] ?? $e['message'] ?? 'erreur Meta');
            if (!empty($e['error_subcode'])) {
                $msg .= ' (code ' . $e['code'] . '/' . $e['error_subcode'] . ')';
            } elseif (!empty($e['code'])) {
                $msg .= ' (code ' . $e['code'] . ')';
            }
        } else {
            $msg = $raw !== '' ? $raw : ('http ' . $code);
        }
        self::$lastError = $msg;
        self::store_diag(self::$lastError);
        return false;
    }

    private static function store_diag($err)
    {
        global $config;
        $prev = trim((string)($config['otantik_wa_last_error'] ?? ''));
        if ($err === '' && $prev === '') {
            return;
        }
        if (!function_exists('wifi_otantik_set_config')) {
            return;
        }
        try {
            $config['otantik_wa_last_error'] = $err;
            $config['otantik_wa_last_at'] = date('Y-m-d H:i:s');
            wifi_otantik_set_config('otantik_wa_last_error', mb_substr((string)$err, 0, 400));
            wifi_otantik_set_config('otantik_wa_last_at', $config['otantik_wa_last_at']);
        } catch (Throwable $e) {
        }
    }

    private static function already($tpl, $refType, $refId, $ttl, $phone = '')
    {
        try {
            $q = ORM::for_table('tbl_otantik_wa_sent')->where('tpl', $tpl)->where('ref_type', $refType)->where('ref_id', (int)$refId);
            if ($phone !== '') {
                $q->where('phone', $phone);
            }
            $row = $q->order_by_desc('id')->find_one();
            if (!$row) {
                return false;
            }
            return strtotime((string)$row['created_at']) > (time() - max(60, (int)$ttl));
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function mark($tpl, $refType, $refId, $phone)
    {
        try {
            $row = ORM::for_table('tbl_otantik_wa_sent')->create();
            $row->phone = mb_substr((string)$phone, 0, 32);
            $row->tpl = mb_substr((string)$tpl, 0, 64);
            $row->ref_type = mb_substr((string)$refType, 0, 32);
            $row->ref_id = (int)$refId;
            $row->created_at = date('Y-m-d H:i:s');
            $row->save();
        } catch (Throwable $e) {
        }
    }

    private static function phone_of($cust, $fallback)
    {
        $p = '';
        if ($cust) {
            $p = (string)($cust['phonenumber'] ?? '');
        }
        if ($p === '') {
            $p = $fallback;
        }
        return function_exists('wifi_otantik_phone') ? wifi_otantik_phone($p) : $p;
    }

    private static function latest_recharge($user, $router)
    {
        try {
            $q = ORM::for_table('tbl_user_recharges')->where('username', $user)->order_by_desc('id');
            if ($router) {
                $q->where('routers', $router['name']);
            }
            return $q->find_one();
        } catch (Throwable $e) {
            return null;
        }
    }

    private static function zone_title($router)
    {
        $t = trim((string)($router['portal_title'] ?? ''));
        if ($t !== '') {
            return $t;
        }
        return (string)($router['name'] ?? 'Wifi-Otantik');
    }

    private static function end_label($row)
    {
        $info = function_exists('wifi_otantik_expiry_info') ? wifi_otantik_expiry_info($row) : [];
        if (!empty($info['end_label'])) {
            return (string)$info['end_label'];
        }
        $d = trim((string)($row['expiration'] ?? ''));
        $t = trim((string)($row['time'] ?? ''));
        return trim($d . ' ' . $t);
    }

    private static function page($title, $main, $buy, $sub = '', $extra = '')
    {
        $h = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $m = htmlspecialchars($main, ENT_QUOTES, 'UTF-8');
        $s = htmlspecialchars($sub, ENT_QUOTES, 'UTF-8');
        $e = htmlspecialchars($extra, ENT_QUOTES, 'UTF-8');
        $b = htmlspecialchars($buy, ENT_QUOTES, 'UTF-8');
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $h . '</title></head><body style="margin:0;font-family:Arial,sans-serif;background:#002858;color:#fff;padding:24px">'
            . '<div style="max-width:420px;margin:40px auto;background:#fff;color:#002858;border-radius:18px;padding:24px;text-align:center">'
            . '<h1 style="font-size:20px;margin:0 0 8px">' . $h . '</h1>'
            . '<p style="font-size:28px;font-weight:800;letter-spacing:2px;margin:12px 0;color:#f05018">' . $m . '</p>'
            . ($s !== '' ? '<p style="color:#64748b;font-size:14px">' . $s . '</p>' : '')
            . ($e !== '' ? '<p style="color:#475569;font-size:14px">' . $e . '</p>' : '')
            . ($buy !== '' ? '<p><a href="' . $b . '" style="display:inline-block;margin-top:12px;background:#f05018;color:#fff;text-decoration:none;font-weight:700;padding:12px 18px;border-radius:12px">Acheter un forfait</a></p>' : '')
            . '</div></body></html>';
        exit;
    }
}
