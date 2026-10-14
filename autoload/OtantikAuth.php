<?php

/**
 * OTP email, Google Sign-In, and login helpers for Wifi-Otantik.
 */
class OtantikAuth
{
    public static $lastMailError = '';

    public static function mail_ready()
    {
        global $config;
        $host = trim((string)($config['smtp_host'] ?? ''));
        $from = trim((string)($config['mail_from'] ?? ''));
        $pass = trim((string)($config['smtp_pass'] ?? ''));
        return $host !== '' && $pass !== '' && filter_var($from, FILTER_VALIDATE_EMAIL);
    }

    public static function mail_from()
    {
        global $config;
        $from = trim((string)($config['mail_from'] ?? ''));
        return filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : '';
    }

    public static function mail_from_name()
    {
        global $config;
        $n = trim((string)($config['mail_from_name'] ?? ''));
        if ($n !== '') {
            return $n;
        }
        $n = trim((string)($config['CompanyName'] ?? ''));
        return $n !== '' ? $n : 'Wifi-Otantik';
    }

    private static function boot_mailer()
    {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return true;
        }
        $base = $GLOBALS['root_path'] ?? (defined('ROOTPATH') ? ROOTPATH : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
        $dir = rtrim((string)$base, '/\\') . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'autoload'
            . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR;
        foreach (['Exception.php', 'SMTP.php', 'PHPMailer.php'] as $f) {
            if (!is_file($dir . $f)) {
                return false;
            }
            require_once $dir . $f;
        }
        return class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    }

    public static function mail_hint($raw = '')
    {
        $e = strtolower(trim($raw !== '' ? $raw : (string)self::$lastMailError));
        if ($e === '' || $e === 'not ready' || $e === 'bad to') {
            return 'Complétez l’adresse Gmail et le mot de passe d’application, puis relancez le test.';
        }
        if (preg_match('/auth|username|password|534|535|invalid login|please log in/i', $e)) {
            return 'Gmail a refusé la connexion. Recréez un mot de passe d’application (16 caractères) et collez-le sans le mot de passe habituel du compte.';
        }
        if (preg_match('/connect|timed out|timeout|network|could not/i', $e)) {
            return 'Le serveur n’arrive pas à joindre Gmail (réseau ou port). Réessayez dans un instant.';
        }
        return 'L’envoi a échoué. Vérifiez l’adresse Gmail et le mot de passe d’application (spams aussi).';
    }

    public static function mail($to, $subject, $body, $meta = [])
    {
        global $config;
        self::$lastMailError = '';
        $to = trim((string)$to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::$lastMailError = 'bad to';
            return false;
        }
        if (!self::mail_ready() || !self::boot_mailer()) {
            self::$lastMailError = 'not ready';
            return false;
        }
        $user = trim((string)($config['smtp_user'] ?? ''));
        $host = trim((string)($config['smtp_host'] ?? ''));
        if ($user === '') {
            $user = self::mail_from();
        }
        $pass = preg_replace('/\s+/', '', (string)($config['smtp_pass'] ?? ''));
        $secure = strtolower(trim((string)($config['smtp_ssltls'] ?? 'tls')));
        $port = (int)($config['smtp_port'] ?? 587);
        $tries = [[$host, $port > 0 ? $port : 587, $secure === 'none' ? 'tls' : $secure]];
        if (stripos($host, 'gmail.com') !== false) {
            $tries = [['smtp.gmail.com', 587, 'tls'], ['smtp.gmail.com', 465, 'ssl']];
        }
        foreach ($tries as $try) {
            if (self::mail_send($to, $subject, $body, $try[0], (int)$try[1], $try[2], $user, $pass, is_array($meta) ? $meta : [])) {
                return true;
            }
        }
        return false;
    }

    public static function logo_file()
    {
        $base = $GLOBALS['root_path'] ?? (defined('ROOTPATH') ? ROOTPATH : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
        $dir = rtrim((string)$base, '/\\') . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        foreach (['logo.png', 'favicon.png'] as $f) {
            if (is_file($dir . $f)) {
                return $dir . $f;
            }
        }
        return '';
    }

    public static function wrap_html($title, $lead, $code = '', $extra = '')
    {
        $name = htmlspecialchars(self::mail_from_name(), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8');
        $lead = htmlspecialchars((string)$lead, ENT_QUOTES, 'UTF-8');
        $extra = trim((string)$extra);
        $extraHtml = $extra !== '' ? '<p style="margin:16px 0 0;font-size:14px;line-height:1.55;color:#475569">'
            . nl2br(htmlspecialchars($extra, ENT_QUOTES, 'UTF-8')) . '</p>' : '';
        $codeHtml = '';
        if ($code !== '') {
            $codeHtml = '<p style="margin:22px 0;padding:18px 12px;text-align:center;font-size:32px;letter-spacing:10px;font-weight:800;color:#f05018;background:#fff8f5;border:2px solid #f05018;border-radius:14px">'
                . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p style="margin:0;font-size:13px;color:#64748b;text-align:center">Valable 10 minutes. Saisissez uniquement ces 6 chiffres.</p>';
        }
        $logo = self::logo_file() !== ''
            ? '<img src="cid:otantiklogo" alt="' . $name . '" height="36" style="display:block;height:36px;width:auto;border:0">'
            : '<span style="color:#ffffff;font-size:20px;font-weight:800">' . $name . '</span>';
        return '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#eef2f6">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f6;padding:24px 12px">'
            . '<tr><td align="center">'
            . '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden">'
            . '<tr><td style="background:#002858;padding:20px 28px">' . $logo . '</td></tr>'
            . '<tr><td style="padding:28px 28px 8px;font-family:Arial,Helvetica,sans-serif;color:#002858">'
            . '<h1 style="margin:0 0 10px;font-size:22px;line-height:1.3">' . $title . '</h1>'
            . '<p style="margin:0;font-size:15px;line-height:1.55;color:#475569">' . $lead . '</p>'
            . $codeHtml . $extraHtml
            . '</td></tr>'
            . '<tr><td style="padding:8px 28px 24px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#94a3b8">Wifi-Otantik n’envoie jamais de lien pour ce code. Ne le communiquez à personne.</td></tr>'
            . '<tr><td style="background:#002858;padding:18px 28px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#cbd5e1">'
            . '<b style="color:#ffffff">' . $name . '</b><br>Hotspot et internet au Cameroun<br>'
            . 'Si vous n’êtes pas à l’origine de ce message, ignorez-le.'
            . '</td></tr></table></td></tr></table></body></html>';
    }

    private static function mail_send($to, $subject, $body, $host, $port, $secure, $user, $pass, $meta = [])
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Timeout = 20;
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = $user !== '';
            if ($mail->SMTPAuth) {
                $mail->Username = $user;
                $mail->Password = $pass;
            }
            if ($secure === 'ssl') {
                $mail->SMTPSecure = 'ssl';
            } elseif ($secure === 'none') {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            } else {
                $mail->SMTPSecure = 'tls';
            }
            $mail->setFrom(self::mail_from(), self::mail_from_name());
            $reply = trim((string)($GLOBALS['config']['mail_reply_to'] ?? ''));
            if (filter_var($reply, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($reply);
            }
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $plain = trim((string)$body);
            $title = (string)($meta['title'] ?? $subject);
            $lead = (string)($meta['lead'] ?? '');
            $code = (string)($meta['code'] ?? '');
            $extra = (string)($meta['extra'] ?? ($code === '' ? $plain : ''));
            $html = self::wrap_html($title, $lead !== '' ? $lead : $plain, $code, $code !== '' ? $extra : '');
            $logo = self::logo_file();
            if ($logo !== '') {
                $mail->addEmbeddedImage($logo, 'otantiklogo', 'logo.png');
            }
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $plain;
            $mail->send();
            self::$lastMailError = '';
            return true;
        } catch (Throwable $e) {
            self::$lastMailError = $e->getMessage();
            return false;
        }
    }

    public static function issue($email, $purpose, $payload = [])
    {
        Tenant::ensure_schema();
        $email = strtolower(trim((string)$email));
        $purpose = preg_replace('/[^a-z_]/', '', (string)$purpose);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $purpose === '') {
            return [false, 'Email invalide'];
        }
        $recent = ORM::for_table('tbl_otantik_otp')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where_gte('created_at', date('Y-m-d H:i:s', time() - 60))
            ->find_one();
        if ($recent) {
            return [false, 'Un code a déjà été envoyé. Patientez une minute.'];
        }
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $row = ORM::for_table('tbl_otantik_otp')->create();
        $row->email = $email;
        $row->purpose = $purpose;
        $row->code_hash = hash('sha256', $code . '|' . $email . '|' . $purpose);
        $row->payload = json_encode($payload);
        $row->expires_at = date('Y-m-d H:i:s', time() + 600);
        $row->created_at = date('Y-m-d H:i:s');
        $row->save();
        $copy = [
            'signup' => ['Votre inscription Wifi-Otantik', 'Bienvenue sur Wifi-Otantik', 'Utilisez ce code pour confirmer votre email et ouvrir votre espace.'],
            'reset' => ['Votre code mot de passe', 'Réinitialisation du mot de passe', 'Utilisez ce code pour choisir un nouveau mot de passe.'],
            'withdraw' => ['Votre code de retrait', 'Confirmez votre retrait', 'Utilisez ce code pour valider le virement vers votre numéro.'],
            'email' => ['Confirmez votre adresse email', 'Nouvelle adresse email', 'Utilisez ce code pour associer cette adresse à votre compte.'],
            'login' => ['Votre code de connexion', 'Connexion Wifi-Otantik', 'Utilisez ce code pour vous connecter.'],
        ];
        $rowCopy = $copy[$purpose] ?? ['Votre code Wifi-Otantik', 'Vérification', 'Utilisez ce code pour continuer.'];
        $ok = self::mail(
            $email,
            $rowCopy[0],
            $rowCopy[1] . "\n\nCode : {$code}\nValable 10 minutes.\nSi vous n’êtes pas à l’origine de cette demande, ignorez ce message.\n",
            [
                'title' => $rowCopy[1],
                'lead' => $rowCopy[2],
                'code' => $code,
            ]
        );
        if (!$ok) {
            if (!self::mail_ready()) {
                return [false, 'L’envoi des codes n’est pas encore configuré.'];
            }
            return [false, 'Impossible d’envoyer le code. Vérifiez l’email et réessayez.'];
        }
        return [true, self::hint($email)];
    }

    public static function consume($email, $purpose, $code)
    {
        Tenant::ensure_schema();
        $email = strtolower(trim((string)$email));
        $code = preg_replace('/\D+/', '', (string)$code);
        $purpose = preg_replace('/[^a-z_]/', '', (string)$purpose);
        if (strlen($code) !== 6) {
            return [false, null, 'Code invalide'];
        }
        $row = ORM::for_table('tbl_otantik_otp')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where_gte('expires_at', date('Y-m-d H:i:s'))
            ->order_by_desc('id')
            ->find_one();
        if (!$row) {
            return [false, null, 'Code expiré. Demandez-en un nouveau.'];
        }
        $expect = hash('sha256', $code . '|' . $email . '|' . $purpose);
        if (!hash_equals((string)$row['code_hash'], $expect)) {
            return [false, null, 'Code incorrect'];
        }
        $payload = json_decode((string)$row['payload'], true) ?: [];
        $row->delete();
        return [true, $payload, 'ok'];
    }

    public static function hint($email)
    {
        $email = trim((string)$email);
        $at = strpos($email, '@');
        if ($at < 2) {
            return $email;
        }
        return substr($email, 0, 1) . '•••' . substr($email, $at);
    }

    public static function google_client_id()
    {
        global $config;
        return trim((string)($config['otantik_google_client_id'] ?? ''));
    }

    public static function google_user($credential)
    {
        $id = self::google_client_id();
        if ($id === '' || $credential === '') {
            return [null, 'Google Sign-In n’est pas configuré'];
        }
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
        $raw = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
            $raw = (string)curl_exec($ch);
            curl_close($ch);
        } else {
            $raw = (string)@file_get_contents($url);
        }
        $j = json_decode($raw, true);
        if (!is_array($j) || ($j['aud'] ?? '') !== $id) {
            return [null, 'Connexion Google refusée'];
        }
        $email = strtolower(trim((string)($j['email'] ?? '')));
        if ($email === '' || (($j['email_verified'] ?? '') !== 'true' && ($j['email_verified'] ?? '') !== true)) {
            return [null, 'Email Google non vérifié'];
        }
        return [[
            'email' => $email,
            'name' => trim((string)($j['name'] ?? '')),
            'sub' => (string)($j['sub'] ?? ''),
        ], ''];
    }

    public static function finish_login($userId, $msg = 'Bienvenue sur Wifi-Otantik')
    {
        $_SESSION['aid'] = (int)$userId;
        if (class_exists('Admin') && method_exists('Admin', 'setCookie')) {
            Admin::setCookie((int)$userId);
        }
        $u = ORM::for_table('tbl_users')->find_one((int)$userId);
        self::log_login((int)$userId, $u ? (string)$u['username'] : '', 'success');
        $dest = $u ? Tenant::landing_url($u->as_array()) : getUrl('plugin/wifi_otantik_home');
        r2($dest, 's', $msg);
    }

    public static function device_label($ua)
    {
        $ua = (string)$ua;
        if ($ua === '') {
            return 'Appareil inconnu';
        }
        $browser = 'Navigateur';
        if (stripos($ua, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'CriOS') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari') !== false) {
            $browser = 'Safari';
        }
        $os = 'Appareil';
        if (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Mac OS') !== false) {
            $os = 'Mac';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }
        return $browser . ' · ' . $os;
    }

    public static function log_login($userId, $username, $status, $reason = '')
    {
        try {
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
            $row = ORM::for_table('tbl_otantik_login_logs')->create();
            $row->user_id = (int)$userId;
            $row->username = (string)$username;
            $row->status = $status === 'success' ? 'success' : 'failed';
            $row->reason = trim((string)$reason);
            $row->ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $row->user_agent = substr($ua, 0, 255);
            $row->device_label = self::device_label($ua);
            $row->created_at = date('Y-m-d H:i:s');
            $row->save();
        } catch (Throwable $e) {
        }
    }
}
