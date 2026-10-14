<?php

/**
 * Wifi-Otantik SaaS isolation.
 * SuperAdmin = plateforme. Tout autre admin = client (tenant).
 */
class Tenant
{
    public static function is_platform($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (!$admin) {
            return false;
        }
        return ($admin['user_type'] ?? '') === 'SuperAdmin';
    }

    public static function id($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (!$admin) {
            return 0;
        }
        // Staff sub-accounts carry root = the owning tenant's user id (stock
        // PHPNuxBill column, previously always 0/unused here). Resolving it
        // is what makes a sub-account see its parent's routers/wallet/revenue
        // instead of an empty tenant of its own - do this everywhere tenant
        // scoping happens, since every router_names()/wallet/revenue query
        // in this plugin goes through Tenant::id().
        $root = (int)($admin['root'] ?? 0);
        return $root > 0 ? $root : (int)$admin['id'];
    }

    /**
     * The effective role for capability checks. root == 0 (every account
     * that existed before staff sub-accounts) is always 'Proprietaire'
     * regardless of its stock user_type value - so existing owner accounts
     * keep full access unchanged. root > 0 (a staff sub-account) uses its
     * own user_type as the role tag (repurposing the existing enum instead
     * of adding a column).
     */
    public static function role($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (!$admin) {
            return '';
        }
        if (self::is_platform($admin)) {
            return 'SuperAdmin';
        }
        $root = (int)($admin['root'] ?? 0);
        if ($root <= 0) {
            return 'Proprietaire';
        }
        return (string)($admin['user_type'] ?: 'Gerant');
    }

    private static function capability_map()
    {
        return [
            // staff_manage (creating/disabling other staff logins) is kept
            // separate from finance_manage - it's an ownership action, not
            // a money one, so giving Comptable full money access doesn't
            // also hand them the power to create other staff accounts.
            'Proprietaire' => ['dashboard', 'sales', 'network', 'finance_view', 'finance_manage', 'staff_manage'],
            'Gerant' => ['dashboard', 'sales', 'network', 'finance_view', 'finance_manage'],
            'Vendeur' => ['dashboard', 'sales'],
            'Technicien' => ['network'],
            'Comptable' => ['finance_view', 'finance_manage'],
        ];
    }

    public static function can($capability, $admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (self::is_platform($admin)) {
            return true;
        }
        $map = self::capability_map();
        return in_array($capability, $map[self::role($admin)] ?? [], true);
    }

    /**
     * The first page this admin actually has access to - used both as the
     * redirect target when require_can() blocks a page (never bounces to a
     * page they also can't see, which would loop) and as the fresh-login
     * landing page for restricted roles.
     */
    public static function landing_url($admin = null)
    {
        if ($admin === null) {
            $admin = $GLOBALS['admin'] ?? Admin::_info();
        }
        if (self::can('dashboard', $admin)) {
            return getUrl('plugin/wifi_otantik_home');
        }
        if (self::can('sales', $admin)) {
            return getUrl('plugin/wifi_otantik_tickets');
        }
        if (self::can('network', $admin)) {
            return getUrl('plugin/wifi_otantik_monitor');
        }
        if (self::can('finance_view', $admin)) {
            return getUrl('plugin/wifi_otantik_erp');
        }
        return getUrl('logout');
    }

    public static function require_can($capability, $admin = null)
    {
        if (!self::can($capability, $admin)) {
            r2(self::landing_url($admin), 'e', Lang::T('You do not have permission to access this page'));
        }
    }

    public static function ensure_schema()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $ver = 45;
        $verFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wo-schema.ver';
        if (is_file($verFile) && (int)trim((string)@file_get_contents($verFile)) >= $ver) {
            $done = true;
            return;
        }
        $done = true;
        $db = ORM::get_db();
        try {
            $db->exec('SET SESSION lock_wait_timeout = 3');
        } catch (Exception $e) {
        }
        $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_tenants` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int NOT NULL,
            `phone` varchar(32) NOT NULL DEFAULT '',
            `wallet` decimal(14,2) NOT NULL DEFAULT 0,
            `commission_rate` decimal(5,2) NOT NULL DEFAULT 0,
            `status` varchar(16) NOT NULL DEFAULT 'Active',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_peers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `router_id` int NOT NULL,
            `owner_id` int NOT NULL,
            `wg_ip` varchar(32) NOT NULL,
            `public_key` varchar(64) NOT NULL,
            `private_key` varchar(64) NOT NULL,
            `status` varchar(64) NOT NULL DEFAULT 'pending',
            `last_seen` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `router_id` (`router_id`),
            UNIQUE KEY `wg_ip` (`wg_ip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_withdrawals` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `owner_id` int NOT NULL,
            `receiver` varchar(32) NOT NULL,
            `amount` decimal(14,2) NOT NULL,
            `service` varchar(16) NOT NULL,
            `status` varchar(32) NOT NULL DEFAULT 'PENDING',
            `mesomb_ref` varchar(128) DEFAULT NULL,
            `message` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `owner_id` (`owner_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        try {
            $cols = $db->query("SHOW COLUMNS FROM tbl_routers LIKE 'owner_id'")->fetchAll();
            if (!$cols) {
                $db->exec("ALTER TABLE tbl_routers ADD COLUMN owner_id int NOT NULL DEFAULT 0");
            }
        } catch (Exception $e) {
        }
        try {
            $cols = $db->query("SHOW COLUMNS FROM tbl_routers LIKE 'slug'")->fetchAll();
            if (!$cols) {
                $db->exec("ALTER TABLE tbl_routers ADD COLUMN slug varchar(64) NOT NULL DEFAULT ''");
            }
        } catch (Exception $e) {
        }
        try {
            $db->exec("ALTER TABLE tbl_otantik_peers MODIFY COLUMN status varchar(64) NOT NULL DEFAULT 'pending'");
        } catch (Exception $e) {
        }
        try {
            $cols = $db->query("SHOW COLUMNS FROM tbl_otantik_peers LIKE 'last_seen'")->fetchAll();
            if (!$cols) {
                $db->exec("ALTER TABLE tbl_otantik_peers ADD COLUMN last_seen datetime DEFAULT NULL");
            }
        } catch (Exception $e) {
        }
        $extra = [
            ['tbl_routers', 'lan_host', "varchar(128) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'ssid', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'assistance_contact', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'latitude', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'longitude', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'location_address', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'zone_note', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'pppoe_interface', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'ros_version', "varchar(8) NOT NULL DEFAULT 'auto'"],
            ['tbl_routers', 'portal_title', "varchar(128) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_welcome', "varchar(512) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_buy_label', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_phone', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_whatsapp', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_email', "varchar(128) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'tg_bot_token', "varchar(128) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'tg_chat_id', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_logo', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_color', "varchar(16) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_navy', "varchar(16) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_slides', 'text'],
            ['tbl_routers', 'portal_help_kicker', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_help_title', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_help_text', "varchar(512) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_help_btn', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_web', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_facebook', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_contact_intro', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_wa_text', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'portal_wa_on', 'tinyint(1) NOT NULL DEFAULT 1'],
            ['tbl_routers', 'portal_services_on', 'tinyint(1) NOT NULL DEFAULT 1'],
            ['tbl_routers', 'portal_services', 'text'],
            ['tbl_routers', 'portal_ref_on', 'tinyint(1) NOT NULL DEFAULT 1'],
            ['tbl_routers', 'portal_ref_discount', 'int NOT NULL DEFAULT 10'],
            ['tbl_routers', 'portal_ref_reward', 'int NOT NULL DEFAULT 10'],
            ['tbl_routers', 'portal_ref_points', 'int NOT NULL DEFAULT 50'],
            ['tbl_routers', 'portal_ref_bundle_pts', 'int NOT NULL DEFAULT 100'],
            ['tbl_routers', 'portal_ref_help', 'text'],
            ['tbl_routers', 'portal_ref_rules', 'text'],
            ['tbl_otantik_hs_refs', 'ref_phone', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_otantik_hs_refs', 'points', 'int NOT NULL DEFAULT 0'],
            ['tbl_otantik_ledger', 'reseller_id', 'int NOT NULL DEFAULT 0'],
            ['tbl_otantik_ledger', 'kind', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_otantik_ledger', 'note', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_customers', 'wo_share_code', "varchar(12) NOT NULL DEFAULT ''"],
            ['tbl_customers', 'wo_credit', 'decimal(14,2) NOT NULL DEFAULT 0'],
            ['tbl_customers', 'latitude', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_customers', 'longitude', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_customers', 'location_address', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_customers', 'routers', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_routers', 'trial_on', 'tinyint(1) NOT NULL DEFAULT 0'],
            ['tbl_routers', 'trial_mins', 'int NOT NULL DEFAULT 15'],
            ['tbl_routers', 'trial_down', 'int NOT NULL DEFAULT 1'],
            ['tbl_routers', 'trial_up', 'int NOT NULL DEFAULT 1'],
            ['tbl_otantik_tenants', 'country', "varchar(64) NOT NULL DEFAULT 'Cameroun'"],
            ['tbl_otantik_tenants', 'kyc_status', "varchar(16) NOT NULL DEFAULT 'none'"],
            ['tbl_otantik_tenants', 'kyc_id_type', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'kyc_id_number', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'kyc_note', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'kyc_submitted_at', 'datetime DEFAULT NULL'],
            ['tbl_otantik_tenants', 'referred_by', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'city', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'address', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'erp_enabled', 'tinyint(1) NOT NULL DEFAULT 0'],
            ['tbl_otantik_tenants', 'erp_plan', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'erp_until', 'datetime DEFAULT NULL'],
            ['tbl_otantik_tenants', 'business_name', "varchar(128) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'business_niu', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_tenants', 'wa_notify_phone', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_otantik_withdrawals', 'reason', "varchar(255) NOT NULL DEFAULT ''"],
            ['tbl_otantik_withdrawals', 'expense_id', 'int unsigned NOT NULL DEFAULT 0'],
            ['tbl_otantik_peers', 'ppp_user', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_peers', 'ppp_pass', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_peers', 'ppp_ip', "varchar(32) NOT NULL DEFAULT ''"],
            ['tbl_users', 'google_id', "varchar(64) NOT NULL DEFAULT ''"],
            ['tbl_otantik_mac_bypass', 'bytes_limit', 'bigint NOT NULL DEFAULT 0'],
            ['tbl_routers', 'mac_bypass_strict', 'tinyint(1) NOT NULL DEFAULT 0'],
            ['tbl_routers', 'portal_audio_mode', "varchar(8) NOT NULL DEFAULT 'off'"],
            ['tbl_routers', 'portal_audio_text', 'text'],
            // Links a recharge to the payment transaction that created it, so
            // correcting a recharge's date can also correct the transaction's
            // date - without this, the revenue chart/ERP ledger/wallet history
            // keep showing the sale under its original date forever.
            ['tbl_user_recharges', 'trx_id', 'int NOT NULL DEFAULT 0'],
            // Withdrawal OTP codes go here when set, regardless of which
            // staff member (owner, Gerant, Comptable - anyone with
            // finance_manage) actually initiates the withdrawal, so the
            // owner keeps a real approval gate over payouts even when
            // delegating who can start one. Falls back to the initiator's
            // own profile email when left blank.
            ['tbl_otantik_tenants', 'withdraw_email', "varchar(128) NOT NULL DEFAULT ''"],
            // Set by a SuperAdmin "Deconnecter" action. Admin::validateToken()
            // rejects any existing session/cookie whose embedded login time is
            // older than this timestamp, forcing a fresh login everywhere -
            // regardless of the single_session setting.
            ['tbl_users', 'force_logout_at', 'datetime DEFAULT NULL'],
            // Separate premium flag from erp_enabled - a tenant can have
            // ERP without the WhatsApp inbox, or vice versa.
            ['tbl_otantik_tenants', 'wa_enabled', 'tinyint(1) NOT NULL DEFAULT 0'],
            ['tbl_otantik_tenants', 'wa_until', 'datetime DEFAULT NULL'],
        ];
        $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_webfig_tokens` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `token` varchar(64) NOT NULL,
            `router_id` int NOT NULL,
            `expires_at` datetime NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `token` (`token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        foreach ($extra as $col) {
            try {
                $found = $db->query("SHOW COLUMNS FROM `{$col[0]}` LIKE '{$col[1]}'")->fetchAll();
                if (!$found) {
                    $db->exec("ALTER TABLE `{$col[0]}` ADD COLUMN `{$col[1]}` {$col[2]}");
                }
            } catch (Exception $e) {
            }
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_inbox` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL DEFAULT 0,
                `router_id` int NOT NULL DEFAULT 0,
                `router_name` varchar(64) NOT NULL DEFAULT '',
                `name` varchar(128) NOT NULL DEFAULT '',
                `phone` varchar(64) NOT NULL DEFAULT '',
                `message` text,
                `seen` tinyint(1) NOT NULL DEFAULT 0,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_id` (`owner_id`),
                KEY `seen` (`seen`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_hs_refs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL DEFAULT 0,
                `router_id` int NOT NULL DEFAULT 0,
                `trx_id` int NOT NULL DEFAULT 0,
                `buyer_phone` varchar(32) NOT NULL DEFAULT '',
                `ref_code` varchar(12) NOT NULL DEFAULT '',
                `ref_phone` varchar(32) NOT NULL DEFAULT '',
                `discount` int NOT NULL DEFAULT 0,
                `reward` int NOT NULL DEFAULT 0,
                `points` int NOT NULL DEFAULT 0,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `ref_code` (`ref_code`),
                KEY `owner_id` (`owner_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_ledger` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `trx_id` int NOT NULL DEFAULT 0,
                `gross` decimal(14,2) NOT NULL DEFAULT 0,
                `rate` decimal(5,2) NOT NULL DEFAULT 0,
                `fee` decimal(14,2) NOT NULL DEFAULT 0,
                `net` decimal(14,2) NOT NULL DEFAULT 0,
                `reseller_id` int NOT NULL DEFAULT 0,
                `kind` varchar(32) NOT NULL DEFAULT '',
                `note` varchar(255) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_id` (`owner_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("ALTER TABLE tbl_otantik_tenants MODIFY COLUMN commission_rate decimal(5,2) NOT NULL DEFAULT 0");
        } catch (Exception $e) {
        }
        try {
            $db->exec("ALTER TABLE tbl_payment_gateway MODIFY COLUMN payment_channel varchar(128) NOT NULL DEFAULT ''");
        } catch (Exception $e) {
        }
        try {
            $flag = $db->query("SELECT value FROM tbl_appconfig WHERE setting = 'otantik_commission_v2'")->fetch();
            if (!$flag) {
                $db->exec("UPDATE tbl_otantik_tenants SET commission_rate = 0");
                $db->exec("INSERT INTO tbl_appconfig (setting, value) VALUES ('otantik_commission_v2', '1')");
            }
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_otp` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `email` varchar(128) NOT NULL,
                `purpose` varchar(32) NOT NULL,
                `code_hash` varchar(64) NOT NULL,
                `payload` text,
                `expires_at` datetime NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `email_purpose` (`email`,`purpose`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_resellers` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `name` varchar(128) NOT NULL DEFAULT '',
                `phone` varchar(32) NOT NULL DEFAULT '',
                `email` varchar(128) NOT NULL DEFAULT '',
                `password` varchar(255) NOT NULL DEFAULT '',
                `commission` decimal(5,2) NOT NULL DEFAULT 10.00,
                `wallet` decimal(14,2) NOT NULL DEFAULT 0,
                `profit` decimal(14,2) NOT NULL DEFAULT 0,
                `sales_total` decimal(14,2) NOT NULL DEFAULT 0,
                `tickets_count` int NOT NULL DEFAULT 0,
                `status` varchar(16) NOT NULL DEFAULT 'active',
                `permissions` text,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_id` (`owner_id`),
                KEY `phone` (`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_reseller_zones` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `reseller_id` int NOT NULL,
                `router_id` int NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `res_router` (`reseller_id`,`router_id`),
                KEY `router_id` (`router_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_books` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `reseller_id` int NOT NULL DEFAULT 0,
                `kind` varchar(32) NOT NULL DEFAULT '',
                `amount` decimal(14,2) NOT NULL DEFAULT 0,
                `commission` decimal(14,2) NOT NULL DEFAULT 0,
                `client_amount` decimal(14,2) NOT NULL DEFAULT 0,
                `wallet_after` decimal(14,2) NOT NULL DEFAULT 0,
                `trx_id` int NOT NULL DEFAULT 0,
                `router_id` int NOT NULL DEFAULT 0,
                `note` varchar(255) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_id` (`owner_id`),
                KEY `reseller_id` (`reseller_id`),
                KEY `kind` (`kind`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_mac_bypass` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `router_id` int NOT NULL DEFAULT 0,
                `username` varchar(64) NOT NULL DEFAULT '',
                `mac` varchar(32) NOT NULL DEFAULT '',
                `rate` varchar(32) NOT NULL DEFAULT '',
                `bytes_limit` bigint NOT NULL DEFAULT 0,
                `trx_id` int NOT NULL DEFAULT 0,
                `status` varchar(16) NOT NULL DEFAULT 'on',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `router_mac` (`router_id`,`mac`),
                KEY `username` (`username`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_mk_logs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL DEFAULT 0,
                `router_id` int NOT NULL DEFAULT 0,
                `router_name` varchar(64) NOT NULL DEFAULT '',
                `action` varchar(32) NOT NULL DEFAULT 'other',
                `target` varchar(128) NOT NULL DEFAULT '',
                `message` varchar(512) NOT NULL DEFAULT '',
                `ok` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_created` (`owner_id`,`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_flow` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `direction` varchar(8) NOT NULL DEFAULT 'in',
                `source` varchar(32) NOT NULL DEFAULT '',
                `amount` decimal(14,2) NOT NULL DEFAULT 0,
                `label` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(64) NOT NULL DEFAULT '',
                `method` varchar(32) NOT NULL DEFAULT '',
                `ref_type` varchar(32) NOT NULL DEFAULT '',
                `ref_id` int NOT NULL DEFAULT 0,
                `expense_id` int unsigned NOT NULL DEFAULT 0,
                `meta` text,
                `occurred_at` datetime NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_occurred` (`owner_id`,`occurred_at`),
                KEY `ref` (`ref_type`,`ref_id`),
                UNIQUE KEY `owner_ref` (`owner_id`,`ref_type`,`ref_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_expenses` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `number` varchar(32) NOT NULL DEFAULT '',
                `title` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(64) NOT NULL DEFAULT '',
                `amount` decimal(14,2) NOT NULL DEFAULT 0,
                `vendor` varchar(128) NOT NULL DEFAULT '',
                `payment_method` varchar(32) NOT NULL DEFAULT '',
                `note` varchar(512) NOT NULL DEFAULT '',
                `status` varchar(16) NOT NULL DEFAULT 'paid',
                `recurring_id` int unsigned NOT NULL DEFAULT 0,
                `source_type` varchar(32) NOT NULL DEFAULT 'manual',
                `source_id` int unsigned NOT NULL DEFAULT 0,
                `expense_date` date DEFAULT NULL,
                `paid_at` datetime NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_paid` (`owner_id`,`paid_at`),
                UNIQUE KEY `owner_number` (`owner_id`,`number`),
                KEY `source` (`source_type`,`source_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        foreach ([
            ['number', "varchar(32) NOT NULL DEFAULT ''"],
            ['payment_method', "varchar(32) NOT NULL DEFAULT ''"],
            ['source_type', "varchar(32) NOT NULL DEFAULT 'manual'"],
            ['source_id', 'int unsigned NOT NULL DEFAULT 0'],
            ['expense_date', 'date DEFAULT NULL'],
        ] as $expenseCol) {
            try {
                $name = $expenseCol[0];
                $type = $expenseCol[1];
                $exists = $db->query("SHOW COLUMNS FROM `tbl_otantik_erp_expenses` LIKE " . $db->quote($name))->fetchAll();
                if (!$exists) {
                    $db->exec("ALTER TABLE `tbl_otantik_erp_expenses` ADD COLUMN `$name` $type");
                }
            } catch (Exception $e) {
            }
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_recurring` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `title` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(64) NOT NULL DEFAULT '',
                `amount` decimal(14,2) NOT NULL DEFAULT 0,
                `day_of_month` tinyint unsigned NOT NULL DEFAULT 1,
                `active` tinyint(1) NOT NULL DEFAULT 1,
                `last_run` date DEFAULT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_active` (`owner_id`,`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_docs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `kind` varchar(16) NOT NULL DEFAULT 'quote',
                `number` varchar(32) NOT NULL DEFAULT '',
                `client_name` varchar(128) NOT NULL DEFAULT '',
                `client_phone` varchar(32) NOT NULL DEFAULT '',
                `client_email` varchar(128) NOT NULL DEFAULT '',
                `subject` varchar(255) NOT NULL DEFAULT '',
                `lines_json` mediumtext,
                `subtotal` decimal(14,2) NOT NULL DEFAULT 0,
                `tax` decimal(14,2) NOT NULL DEFAULT 0,
                `total` decimal(14,2) NOT NULL DEFAULT 0,
                `status` varchar(16) NOT NULL DEFAULT 'draft',
                `issue_date` date DEFAULT NULL,
                `valid_until` date DEFAULT NULL,
                `paid_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `owner_number` (`owner_id`,`number`),
                KEY `owner_kind` (`owner_id`,`kind`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $exists = $db->query("SHOW COLUMNS FROM `tbl_otantik_erp_docs` LIKE 'issue_date'")->fetchAll();
            if (!$exists) {
                $db->exec("ALTER TABLE `tbl_otantik_erp_docs` ADD COLUMN `issue_date` date DEFAULT NULL AFTER `status`");
            }
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_files` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `target_type` varchar(32) NOT NULL DEFAULT '',
                `target_id` int unsigned NOT NULL DEFAULT 0,
                `filename` varchar(255) NOT NULL DEFAULT '',
                `original_name` varchar(255) NOT NULL DEFAULT '',
                `mime` varchar(64) NOT NULL DEFAULT '',
                `size` int unsigned NOT NULL DEFAULT 0,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `target` (`target_type`,`target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $exists = $db->query("SHOW COLUMNS FROM `tbl_otantik_erp_files` LIKE 'original_name'")->fetchAll();
            if (!$exists) {
                $db->exec("ALTER TABLE `tbl_otantik_erp_files` ADD COLUMN `original_name` varchar(255) NOT NULL DEFAULT '' AFTER `filename`");
            }
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_wa_sent` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `phone` varchar(32) NOT NULL DEFAULT '',
                `tpl` varchar(64) NOT NULL DEFAULT '',
                `ref_type` varchar(32) NOT NULL DEFAULT '',
                `ref_id` int NOT NULL DEFAULT 0,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `lookup` (`tpl`,`ref_type`,`ref_id`,`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_wa_logs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `direction` varchar(8) NOT NULL DEFAULT 'out',
                `kind` varchar(16) NOT NULL DEFAULT '',
                `tpl` varchar(64) NOT NULL DEFAULT '',
                `phone` varchar(32) NOT NULL DEFAULT '',
                `ok` tinyint(1) NOT NULL DEFAULT 1,
                `detail` varchar(500) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `created` (`created_at`),
                KEY `phone` (`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_erp_assets` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `owner_id` int NOT NULL,
                `ref` varchar(64) NOT NULL DEFAULT '',
                `name` varchar(255) NOT NULL DEFAULT '',
                `category` varchar(64) NOT NULL DEFAULT '',
                `brand_model` varchar(128) NOT NULL DEFAULT '',
                `status` varchar(32) NOT NULL DEFAULT 'Actif',
                `asset_condition` varchar(32) NOT NULL DEFAULT 'Bon',
                `cost` decimal(14,2) NOT NULL DEFAULT 0,
                `purchase_date` date DEFAULT NULL,
                `note` varchar(512) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `owner_id` (`owner_id`),
                UNIQUE KEY `owner_ref` (`owner_id`,`ref`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_login_logs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int NOT NULL DEFAULT 0,
                `username` varchar(64) NOT NULL DEFAULT '',
                `status` varchar(16) NOT NULL DEFAULT 'success',
                `reason` varchar(255) NOT NULL DEFAULT '',
                `ip` varchar(64) NOT NULL DEFAULT '',
                `user_agent` varchar(255) NOT NULL DEFAULT '',
                `device_label` varchar(128) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_reconcile_logs` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `trx_id` int NOT NULL DEFAULT 0,
                `owner_id` int NOT NULL DEFAULT 0,
                `action` varchar(32) NOT NULL DEFAULT '',
                `ok` tinyint(1) NOT NULL DEFAULT 0,
                `message` varchar(255) NOT NULL DEFAULT '',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `trx_id` (`trx_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            // The customer's own WhatsApp display name, captured for free
            // from contacts[].profile.name on every incoming webhook -
            // Meta's API never exposes profile pictures (deliberate privacy
            // restriction, not fetchable by any legitimate means), but the
            // display name rides along with every inbound message at no
            // extra cost.
            $db->exec("CREATE TABLE IF NOT EXISTS `tbl_otantik_wa_contacts` (
                `phone` varchar(20) NOT NULL,
                `display_name` varchar(128) NOT NULL DEFAULT '',
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
        }
        try {
            $n = (int)$db->query('SELECT COUNT(*) FROM `tbl_otantik_wa_logs`')->fetchColumn();
            if ($n < 1) {
                $db->exec("INSERT INTO `tbl_otantik_wa_logs` (`direction`,`kind`,`tpl`,`phone`,`ok`,`detail`,`created_at`)
                    SELECT 'out','tpl',`tpl`,`phone`,1,'',`created_at` FROM `tbl_otantik_wa_sent` ORDER BY `id` DESC LIMIT 200");
            }
        } catch (Exception $e) {
        }
        try {
            // Staff sub-accounts (root > 0) reuse this enum as their role
            // tag - add the four new roles alongside the stock values so
            // nothing that already relies on 'SuperAdmin'/'Admin'/etc breaks.
            $db->exec("ALTER TABLE `tbl_users` MODIFY `user_type` ENUM('SuperAdmin','Admin','Report','Agent','Sales','Gerant','Vendeur','Technicien','Comptable') NOT NULL DEFAULT 'Admin'");
        } catch (Exception $e) {
        }
        try {
            $db->exec("ALTER TABLE `tbl_users` ADD INDEX `idx_root` (`root`)");
        } catch (Exception $e) {
        }
        try {
            $ok = $db->query("SHOW COLUMNS FROM `tbl_customers` LIKE 'wo_share_code'")->fetchAll();
            if ($ok) {
                @file_put_contents($verFile, (string)$ver);
            }
        } catch (Exception $e) {
        }
    }

    public static function tenant_row($userId)
    {
        self::ensure_schema();
        $row = ORM::for_table('tbl_otantik_tenants')->where('user_id', (int)$userId)->find_one();
        if ($row) {
            return $row;
        }
        $row = ORM::for_table('tbl_otantik_tenants')->create();
        $row->user_id = (int)$userId;
        $row->phone = '';
        $row->wallet = 0;
        $row->commission_rate = 0;
        $row->status = 'Active';
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
        return $row;
    }

    public static function default_commission()
    {
        global $config;
        $v = isset($config['otantik_commission']) ? (float)$config['otantik_commission'] : 15;
        if ($v < 0) {
            $v = 0;
        }
        if ($v > 90) {
            $v = 90;
        }
        return $v;
    }

    public static function effective_commission($owner = null)
    {
        // Tenant row present: respect their rate, including 0% (new admins / new Wifi Zones).
        // Do NOT treat 0 as "unset" — that wrongly applied the platform default (e.g. 15%).
        if ($owner) {
            $own = (float)($owner['commission_rate'] ?? 0);
            if ($own < 0) {
                $own = 0;
            }
            if ($own > 90) {
                $own = 90;
            }
            return $own;
        }
        return self::default_commission();
    }

    public static function split($amount, $owner = null)
    {
        $gross = (float)$amount;
        $rate = self::effective_commission($owner);
        $fee = round($gross * ($rate / 100), 2);
        $net = round($gross - $fee, 2);
        return [
            'rate' => $rate,
            'gross' => $gross,
            'fee' => $fee,
            'net' => $net,
        ];
    }

    public static function routers_query($admin = null)
    {
        self::ensure_schema();
        $q = ORM::for_table('tbl_routers')->order_by_desc('id');
        if (!self::is_platform($admin)) {
            $q->where('owner_id', self::id($admin));
        }
        return $q;
    }

    public static function router_names($admin = null)
    {
        $rows = self::routers_query($admin)->find_many();
        $names = [];
        foreach ($rows as $r) {
            $names[] = $r['name'];
        }
        return $names;
    }

    public static function owns_router($router, $admin = null)
    {
        if (self::is_platform($admin)) {
            return true;
        }
        if (!$router) {
            return false;
        }
        return (int)$router['owner_id'] === self::id($admin);
    }

    public static function require_router($id, $admin = null)
    {
        $r = ORM::for_table('tbl_routers')->find_one($id);
        if (!$r || !self::owns_router($r, $admin)) {
            r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
        }
        return $r;
    }

    public static function require_plan($id, $admin = null)
    {
        $p = ORM::for_table('tbl_plans')->find_one($id);
        if (!$p) {
            r2(getUrl('plugin/wifi_otantik_plans'), 'e', Lang::T('Data Not Found'));
        }
        $router = ORM::for_table('tbl_routers')->where('name', $p['routers'])->find_one();
        if (!$router || !self::owns_router($router, $admin)) {
            r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
        }
        return $p;
    }

    public static function guard($handler, $routes, $admin)
    {
        if (!$admin) {
            return;
        }
        $legacyAdminlte = ['dashboard', 'reports', 'widgets', 'map', 'assets', 'community', 'settings'];
        if (in_array($handler, $legacyAdminlte, true)) {
            if ($handler === 'settings' && self::is_platform($admin)) {
                r2(getUrl('plugin/wifi_otantik_platform'));
            }
            r2(getUrl('plugin/wifi_otantik_home'));
        }
        if (self::is_platform($admin)) {
            return;
        }
        $action = $routes[1] ?? '';
        if ($handler === 'plugin') {
            if (strpos($action, 'wifi_otantik_') === 0) {
                return;
            }
            r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
        }
        $allowed = ['admin', 'logout', 'accounts', 'default', 'login', 'provision', 'hotspot', 'wifi', 'paiement', 'w'];
        if (in_array($handler, $allowed, true)) {
            return;
        }
        r2(getUrl('plugin/wifi_otantik_home'), 'e', Lang::T('You do not have permission to access this page'));
    }

    public static function slugify($name)
    {
        $s = strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = trim($s, '-');
        if ($s === '') {
            $s = 'zone';
        }
        $base = substr($s, 0, 40);
        $slug = $base;
        $i = 1;
        while (ORM::for_table('tbl_routers')->where('slug', $slug)->find_one()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    public static function http_host()
    {
        return strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
    }

    public static function scheme()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return 'https';
        }
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return 'https';
        }
        return 'https';
    }

    public static function host_wifi()
    {
        global $config;
        return $config['otantik_host_wifi'] ?? 'wifi.otantik.cm';
    }

    public static function host_business()
    {
        global $config;
        return $config['otantik_host_business'] ?? 'business.otantik.cm';
    }

    public static function host_portal()
    {
        global $config;
        return $config['otantik_host_portal'] ?? 'portal.otantik.cm';
    }

    public static function url_wifi($path = '/')
    {
        return self::scheme() . '://' . self::host_wifi() . $path;
    }

    public static function url_business($path = '/')
    {
        return self::scheme() . '://' . self::host_business() . $path;
    }

    public static function url_portal($path = '/')
    {
        return self::scheme() . '://' . self::host_portal() . $path;
    }

    public static function is_wifi_host()
    {
        return self::http_host() === self::host_wifi();
    }

    public static function is_business_host()
    {
        return self::http_host() === self::host_business();
    }

    public static function is_portal_host()
    {
        return self::http_host() === self::host_portal();
    }
}
