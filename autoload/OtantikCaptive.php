<?php

/**
 * Simple Wifi-Otantik MikroTik captive portal (no Smarty, no client-side Telegram keys).
 */
class OtantikCaptive
{
    public static function files()
    {
        return [
            'login.html', 'alogin.html', 'status.html', 'error.html', 'logout.html',
            'ticket.html', 'chat.html', 'apropos.html', 'services.html', 'code.html', 'pay.html',
            'menu-main.html', 'menu-highlights.html', 'menu-bell.html',
            'md5.js', 'wo.css', 'wo.js', 'logo.png', 'favicon.png',
            'bundle.jpg', 'slide-1.jpg', 'slide-2.jpg', 'slide-3.jpg', 'welcome.mp3',
        ];
    }

    public static function kit_dir()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom' . DIRECTORY_SEPARATOR . 'hs-kit';
    }

    public static function kit_url()
    {
        return rtrim(Tenant::url_portal('/ui/ui_custom/hs-kit'), '/');
    }

    public static function origin_base()
    {
        if (function_exists('wifi_otantik_origin_base')) {
            return wifi_otantik_origin_base();
        }
        return 'http://83.147.38.107';
    }

    public static function rehost_url($url, $api)
    {
        $api = rtrim((string)$api, '/');
        $n = preg_replace('#^https?://[^/]+#', $api, (string)$url);
        return (is_string($n) && $n !== '') ? $n : (string)$url;
    }

    public static function cfg($router, $preview = false)
    {
        $name = trim((string)($router['name'] ?? 'Wifi'));
        $title = trim((string)($router['portal_title'] ?? ''));
        if ($title === '') {
            $title = $name;
        }
        $welcome = trim((string)($router['portal_welcome'] ?? ''));
        if ($welcome === '') {
            $welcome = 'Payez en quelques secondes. Si vous avez déjà un ticket, entrez simplement votre code.';
        }
        $buy = trim((string)($router['portal_buy_label'] ?? ''));
        if ($buy === '') {
            $buy = 'Acheter du Wi-Fi';
        }
        $phone = trim((string)($router['portal_phone'] ?? ''));
        if ($phone === '') {
            $phone = trim((string)($router['assistance_contact'] ?? ''));
        }
        $wa = trim((string)($router['portal_whatsapp'] ?? ''));
        if ($wa === '') {
            $wa = $phone;
        }
        $email = trim((string)($router['portal_email'] ?? ''));
        $ssid = trim((string)($router['ssid'] ?? ''));
        $slug = trim((string)($router['slug'] ?? ''));
        $token = trim((string)($router['tg_bot_token'] ?? ''));
        $chat = trim((string)($router['tg_chat_id'] ?? ''));
        $colors = self::colors($router);
        $help = self::help($router);
        $slides = self::slides($router);
        $api = $preview ? ('https://' . self::portal_host()) : self::origin_base();
        $enc = rawurlencode($slug);
        // On the MikroTik splash, use tiny relative image paths (same folder as
        // login.html). Remote VPS URLs stall the CNA; solid navy was a stopgap.
        if ($preview) {
            $logo = self::rehost_url(self::logo_abs_url($router), $api);
            $bundle = self::rehost_url(self::pic_abs_url($router, 'bundle'), $api);
            $slideUrls = [];
            for ($i = 1; $i <= 3; $i++) {
                $slideUrls[] = self::rehost_url(self::pic_abs_url($router, 'slide-' . $i), $api);
            }
            $audioSrc = self::rehost_url(self::audio_abs_url($router), $api);
        } else {
            $logo = 'logo.png';
            $bundle = 'bundle.jpg';
            $slideUrls = ['slide-1.jpg', 'slide-2.jpg', 'slide-3.jpg'];
            $audioSrc = 'welcome.mp3';
        }
        // Same "off unless it can actually do something" guard as any other
        // optional portal feature - an admin who picks "upload" but never
        // attaches an MP3 gets no button at all rather than a TTS voice they
        // never chose, so misconfiguration is visible (see the admin-page
        // warning in otantik_captive.tpl) instead of surprising a customer.
        $audioMode = trim((string)($router['portal_audio_mode'] ?? 'off'));
        if (!in_array($audioMode, ['off', 'tts', 'upload'], true)) {
            $audioMode = 'off';
        }
        if ($audioMode === 'upload' && !self::has_custom_audio($router)) {
            $audioMode = 'off';
        }
        $audioText = trim((string)($router['portal_audio_text'] ?? ''));
        if ($audioText === '') {
            $audioText = self::default_audio_text();
        }
        return [
            'name' => $name,
            'title' => $title,
            'welcome' => $welcome,
            'buy_label' => $buy,
            'phone' => $phone,
            'whatsapp' => $wa,
            'email' => $email,
            'ssid' => $ssid,
            'slug' => $slug,
            'preview' => $preview,
            'pay' => $api . '/wifi/' . $enc . '/login.html',
            'buy' => $api . '/wifi/' . $enc . '/buy',
            'status' => $api . '/wifi/' . $enc . '/status.html',
            'plans' => $api . '/hotspot/' . $enc . '/plans',
            'contact' => $api . '/hotspot/' . $enc . '/contact',
            'logo' => $logo,
            'has_logo' => self::has_custom_logo($router),
            'tg' => ($token !== '' && $chat !== ''),
            'tg_token' => $token,
            'tg_chat' => $chat,
            'color' => $colors['accent'],
            'navy' => $colors['navy'],
            'color_light' => $colors['light'],
            'color_wash' => $colors['wash'],
            'navy_dark' => $colors['navy_dark'],
            'navy_mix' => $colors['navy_mix'],
            'is_default_color' => $colors['is_default'],
            'bundle_url' => $bundle,
            'slides' => $slides,
            'slide_urls' => $slideUrls,
            'help' => $help,
            'web' => trim((string)($router['portal_web'] ?? '')),
            'facebook' => trim((string)($router['portal_facebook'] ?? '')),
            'contact_intro' => trim((string)($router['portal_contact_intro'] ?? '')),
            'wa_text' => trim((string)($router['portal_wa_text'] ?? '')),
            'wa_on' => ((string)($router['portal_wa_on'] ?? '1')) !== '0',
            'services_on' => ((string)($router['portal_services_on'] ?? '1')) !== '0',
            'services' => self::services($router),
            'session' => $api . '/hotspot/' . $enc . '/session',
            'lookup' => $api . '/hotspot/' . $enc . '/lookup',
            'recover' => 'code.html',
            'recover_form' => $api . '/recuperer',
            'ref_on' => ((string)($router['portal_ref_on'] ?? '1')) !== '0',
            'ref_points' => (int)($router['portal_ref_points'] ?? 50) ?: 50,
            'ref_bundle_pts' => (int)($router['portal_ref_bundle_pts'] ?? 100) ?: 100,
            'ref_help' => trim((string)($router['portal_ref_help'] ?? '')),
            'ref_rules' => trim((string)($router['portal_ref_rules'] ?? '')),
            'ref_discount' => (int)($router['portal_ref_discount'] ?? 0),
            'ref_reward' => (int)($router['portal_ref_reward'] ?? 10),
            'audio_mode' => $audioMode,
            'audio_text' => $audioText,
            'audio_src' => $audioSrc,
        ];
    }

    public static function default_slides()
    {
        return [
            ['title' => 'Internet 3.0', 'text' => 'Surfez avec du haut débit !'],
            ['title' => '24H/24H', 'text' => 'Du haut débit chez vous !'],
            ['title' => '7J/7J', 'text' => 'Payez vos tickets en ligne'],
        ];
    }

    public static function slides($router)
    {
        $def = self::default_slides();
        $raw = trim((string)($router['portal_slides'] ?? ''));
        $arr = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($arr)) {
            return $def;
        }
        $out = [];
        for ($i = 0; $i < 3; $i++) {
            $t = trim((string)($arr[$i]['title'] ?? ''));
            $x = trim((string)($arr[$i]['text'] ?? ''));
            $out[] = [
                'title' => $t !== '' ? mb_substr($t, 0, 64) : $def[$i]['title'],
                'text' => $x !== '' ? mb_substr($x, 0, 128) : $def[$i]['text'],
            ];
        }
        return $out;
    }

    public static function default_services()
    {
        return [
            ['title' => 'Formation', 'text' => 'Nous formons vos équipes à gérer le hotspot.'],
            ['title' => 'Vente', 'text' => 'Forfaits Wi‑Fi et accessoires sur place.'],
            ['title' => 'Dépannage', 'text' => 'Un souci de connexion ? On intervient.'],
            ['title' => 'Installation', 'text' => 'Câblage, point d’accès et mise en service.'],
        ];
    }

    public static function services($router)
    {
        $def = self::default_services();
        $raw = trim((string)($router['portal_services'] ?? ''));
        $arr = $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($arr)) {
            return $def;
        }
        $out = [];
        for ($i = 0; $i < 4; $i++) {
            $t = trim((string)($arr[$i]['title'] ?? ''));
            $x = trim((string)($arr[$i]['text'] ?? ''));
            $out[] = [
                'title' => $t !== '' ? mb_substr($t, 0, 64) : $def[$i]['title'],
                'text' => $x !== '' ? mb_substr($x, 0, 255) : $def[$i]['text'],
            ];
        }
        return $out;
    }

    public static function default_help()
    {
        return [
            'kicker' => 'Besoin d’aide',
            'title' => 'Contact',
            'text' => 'Un souci pour vous connecter ? Écrivez-nous.',
            'btn' => 'Nous écrire',
        ];
    }

    public static function help($router)
    {
        $def = self::default_help();
        $k = trim((string)($router['portal_help_kicker'] ?? ''));
        $t = trim((string)($router['portal_help_title'] ?? ''));
        $x = trim((string)($router['portal_help_text'] ?? ''));
        $b = trim((string)($router['portal_help_btn'] ?? ''));
        return [
            'kicker' => $k !== '' ? mb_substr($k, 0, 64) : $def['kicker'],
            'title' => $t !== '' ? mb_substr($t, 0, 64) : $def['title'],
            'text' => $x !== '' ? mb_substr($x, 0, 512) : $def['text'],
            'btn' => $b !== '' ? mb_substr($b, 0, 64) : $def['btn'],
        ];
    }

    public static function default_colors()
    {
        return ['accent' => '#f05018', 'navy' => '#002858'];
    }

    public static function hex($s, $fallback)
    {
        $s = strtolower(trim((string)$s));
        if (preg_match('/^#?([0-9a-f]{6})$/', $s, $m)) {
            return '#' . $m[1];
        }
        if (preg_match('/^#?([0-9a-f]{3})$/', $s, $m)) {
            $h = $m[1];
            return '#' . $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
        }
        return $fallback;
    }

    public static function colors($router)
    {
        $def = self::default_colors();
        $accentIn = trim((string)($router['portal_color'] ?? ''));
        $navyIn = trim((string)($router['portal_navy'] ?? ''));
        $accent = $accentIn === '' ? $def['accent'] : self::hex($accentIn, $def['accent']);
        $navy = $navyIn === '' ? $def['navy'] : self::hex($navyIn, $def['navy']);
        return [
            'accent' => $accent,
            'navy' => $navy,
            'light' => self::mix($accent, '#ffffff', 0.28),
            'wash' => self::mix($accent, '#ffffff', 0.90),
            'navy_dark' => self::mix($navy, '#000000', 0.22),
            'navy_mix' => self::mix($navy, $accent, 0.38),
            'is_default' => ($accent === $def['accent'] && $navy === $def['navy'] && $accentIn === '' && $navyIn === ''),
        ];
    }

    public static function theme_css($cfg)
    {
        $accent = self::h($cfg['color'] ?? '#f05018');
        $light = self::h($cfg['color_light'] ?? '#ff7a45');
        $navy = self::h($cfg['navy'] ?? '#002858');
        $wash = self::h($cfg['color_wash'] ?? '#fff8f5');
        $navyDark = self::h($cfg['navy_dark'] ?? '#001e42');
        $navyMix = self::h($cfg['navy_mix'] ?? '#3a1a10');
        return ':root{--wo-hs-accent:' . $accent . ';--wo-hs-accent-2:' . $light
            . ';--wo-hs-navy:' . $navy . ';--wo-hs-wash:' . $wash
            . ';--wo-hs-navy-dark:' . $navyDark . ';--wo-hs-navy-mix:' . $navyMix . ';}'
            . 'html,body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}'
            . '#preloader{display:none!important}'
            . '.color-highlight,.color-highlight-light,.color-highlight-dark{color:' . $accent . '!important}'
            . '.bg-highlight,.bg-highlight-light,.bg-highlight-dark,.btn.bg-highlight{background-color:' . $accent . '!important;color:#fff!important}'
            . '.border-highlight,.border-highlight-light,.border-highlight-dark,.focus-highlight:focus{border-color:' . $accent . '!important}'
            . '.gradient-highlight,#footer-bar .active-nav i{background-image:linear-gradient(to bottom,' . $light . ',' . $accent . ')!important;color:#fff!important}'
            . '.theme-light .color-theme,.theme-light .header-bar .header-title,.theme-light .header-title.color-theme{color:' . $navy . '!important}'
            . '.theme-dark .color-theme,.theme-dark .header-bar .header-title,.theme-dark .header-title.color-theme{color:#fff!important}'
            . '.spinner-border.color-highlight{color:' . $accent . '!important;border-color:' . $accent . '!important;border-right-color:transparent!important}'
            . '#footer-bar .active-nav span{color:' . $accent . '!important}'
            . 'a.color-highlight,.default-link.gradient-highlight{background-image:linear-gradient(to bottom,' . $light . ',' . $accent . ')!important}'
            . '.color-icon-highlight{stroke:' . $accent . '!important}'
            . '.wo-pay-plan{display:block;border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 14px;margin:0 0 10px;cursor:pointer;color:var(--wo-hs-navy,#002858)}'
            . '.wo-pay-plan input{accent-color:var(--wo-hs-accent,#f05018);margin-right:8px}'
            . '.wo-pay-plan:has(input:checked),.wo-pay-plan.on{border-color:var(--wo-hs-accent,#f05018)!important;background:var(--wo-hs-wash,#fff8f5)!important}'
            . '.wo-pay-plan small{display:block;opacity:.7;margin-top:2px}'
            . '.wo-pay-sel{background:linear-gradient(160deg,var(--wo-hs-navy,#002858),var(--wo-hs-navy-dark,#001e42) 55%,var(--wo-hs-navy-mix,#3a1a10));color:#fff;border-radius:16px;padding:16px;margin-bottom:14px}'
            . '.wo-pay-sel small{display:block;opacity:.75;font-weight:700}'
            . '.wo-pay-sel strong{display:block;font-size:26px;line-height:1.1;margin:4px 0}'
            . '.wo-pay-sel span{display:block;opacity:.9}'
            . '.wo-pay-op{display:none;align-items:center;gap:8px;margin:-4px 0 12px;font-size:13px;font-weight:700}'
            . '.wo-pay-op.on{display:flex}'
            . '.wo-pay-op .dot{width:8px;height:8px;border-radius:50%}'
            . '.wo-pay-op.mtn{color:#854d0e}'
            . '.wo-pay-op.mtn .dot{background:#facc15}'
            . '.wo-pay-op.om{color:#9a3412}'
            . '.wo-pay-op.om .dot{background:var(--wo-hs-accent,#f05018)}'
            . '.wo-pay-op.bad{color:#b91c1c}'
            . '.wo-pay-op.bad .dot{background:#ef4444}'
            . '.wo-pay-scrim{position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;height:-webkit-fill-available;height:100dvh;min-height:100vh;z-index:2147483000;background:rgba(0,20,48,.62);display:none;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;padding:18px;padding-bottom:max(18px,env(safe-area-inset-bottom));-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);-webkit-overflow-scrolling:touch;backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px)}'
            . '.wo-pay-scrim[hidden]{display:none!important}'
            . '.wo-pay-scrim.is-on{display:-webkit-box!important;display:-webkit-flex!important;display:flex!important}'
            . '.wo-pay-wait{width:min(420px,100%);background:#fff;border-radius:22px;padding:26px 20px 22px;text-align:center;color:#002858;box-shadow:0 20px 50px rgba(0,40,88,.28);-webkit-transform:translateZ(0);transform:translateZ(0)}'
            . '.wo-pay-wait .wo-spin-wrap{position:relative;width:56px;height:56px;margin:0 auto 16px}'
            . '.wo-pay-wait .spin,.wo-pay-wait .spin2{position:absolute;inset:0;border-radius:50%;border:3px solid #fed7c2;border-top-color:#f05018;-webkit-animation:woSpin .75s linear infinite;animation:woSpin .75s linear infinite}'
            . '.wo-pay-wait .spin2{inset:8px;border-width:2px;border-color:#e2e8f0;border-top-color:#002858;-webkit-animation-duration:1.1s;animation-duration:1.1s;-webkit-animation-direction:reverse;animation-direction:reverse}'
            . '.wo-pay-wait .wo-dots{display:flex;justify-content:center;gap:6px;margin:10px 0 4px}'
            . '.wo-pay-wait .wo-dots i{width:6px;height:6px;border-radius:50%;background:#fed7c2;display:block;-webkit-animation:woDot 1.2s ease-in-out infinite;animation:woDot 1.2s ease-in-out infinite}'
            . '.wo-pay-wait .wo-dots i:nth-child(2){-webkit-animation-delay:.15s;animation-delay:.15s}'
            . '.wo-pay-wait .wo-dots i:nth-child(3){-webkit-animation-delay:.3s;animation-delay:.3s}'
            . '@-webkit-keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}'
            . '@keyframes woSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}'
            . '@-webkit-keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}'
            . '@keyframes woDot{0%,80%,100%{opacity:.35;transform:scale(.85)}40%{opacity:1;transform:scale(1.15)}}'
            . '.wo-pay-wait h2{margin:0 0 8px;font-size:20px;font-weight:800}'
            . '.wo-pay-wait p{margin:0 0 10px;color:#475569;font-size:14px;line-height:1.45}'
            . '.wo-ussd{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:14px 0 4px;text-align:left}'
            . '.wo-ussd div{border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 10px;transition:border-color .2s,background .2s,box-shadow .2s}'
            . '.wo-ussd div.on{border-color:#f05018;background:#fff8f5;box-shadow:0 0 0 3px rgba(240,80,24,.12)}'
            . '.wo-ussd b{display:block;font-size:12px;color:#64748b;margin-bottom:4px}'
            . '.wo-ussd code{font-size:18px;font-weight:800;color:#002858}'
            . '.wo-phone-err{display:none;color:#b91c1c;font-size:12px;font-weight:700;margin:-6px 0 10px}'
            . '#woPayPhone,#woPhone{font-size:16px!important}'
            . '.wo-btn-loading{color:transparent!important;position:relative;pointer-events:none}'
            . '.wo-btn-loading:after{content:"";position:absolute;left:0;right:0;top:0;bottom:0;margin:auto;width:16px;height:16px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;-webkit-animation:woSpin .6s linear infinite;animation:woSpin .6s linear infinite}'
            . '.bg-blue-dark,.btn.bg-blue-dark{background-color:' . $accent . '!important;color:#fff!important;border-color:' . $accent . '!important}'
            . 'a[data-bs-target="#menu-color"]{display:none!important}'
            . '.wo-bundle{display:block;text-decoration:none!important;margin:0 15px 14px;padding:16px 16px 12px;border-radius:22px;position:relative;'
            . 'background:#002858 url(' . self::h($cfg['bundle_url'] ?? 'bundle.jpg') . ') center/cover no-repeat;'
            . 'box-shadow:0 10px 24px rgba(0,40,88,.28);overflow:hidden}'
            . '.wo-bundle:before{content:"";position:absolute;inset:0;border-radius:inherit;background:linear-gradient(180deg,rgba(0,40,88,.28) 0%,rgba(0,30,66,.72) 52%,rgba(0,8,24,.92) 100%)}'
            . '.wo-bundle-row{display:flex;align-items:center;justify-content:space-between;gap:12px;position:relative;z-index:1}'
            . '.wo-bundle-copy{min-width:0;flex:1}'
            . '.wo-bundle-dur{font-size:12px;font-weight:600;color:#c8c8c8;letter-spacing:.02em}'
            . '.wo-bundle-title{font-size:22px;font-weight:800;color:#fff;line-height:1.15;margin:4px 0 6px;font-family:Inter,sans-serif}'
            . '.wo-bundle-meta{font-size:13px;font-weight:700;color:' . $accent . ';margin:0 0 8px}'
            . '.wo-bundle-badge{display:inline-block;background:#15803d;color:#fff;font-size:10px;font-weight:800;letter-spacing:.02em;padding:4px 8px;border-radius:3px;text-transform:uppercase}'
            . '.wo-bundle-pay{display:inline-block;flex-shrink:0;padding:9px 18px;border-radius:10px;background:' . $accent . ';color:#fff;font-size:12px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;white-space:nowrap;box-shadow:0 4px 10px rgba(0,0,0,.2)}'
            . '.wo-bundle-pay.wo-bundle-free{background:#15803d}'
            . '.wo-bundle-pay.wo-bundle-loading{color:transparent!important;position:relative}'
            . '.wo-bundle-pay.wo-bundle-loading:after{content:"";position:absolute;inset:0;margin:auto;width:14px;height:14px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;-webkit-animation:woBundleSpin .6s linear infinite;animation:woBundleSpin .6s linear infinite}'
            . '@-webkit-keyframes woBundleSpin{to{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}'
            . '@keyframes woBundleSpin{to{transform:rotate(360deg)}}'
            . '.wo-btn-loading{color:transparent!important;position:relative;pointer-events:none}'
            . '.wo-btn-loading:after{content:"";position:absolute;inset:0;margin:auto;width:16px;height:16px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;-webkit-animation:woBundleSpin .6s linear infinite;animation:woBundleSpin .6s linear infinite}'
            . '.wo-bundle-foot{margin-top:12px;text-align:center;font-size:11px;font-weight:600;color:#e5e5e5;position:relative;z-index:1}'
            . '#single-slider-3,.splide{visibility:visible!important}'
            . '#single-slider-3 .wo-slide,.wo-slide{height:140px!important;min-height:140px!important;background-size:cover!important;background-position:center!important;position:relative;overflow:hidden}'
            . '.wo-slide:before{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,40,88,.12) 0%,rgba(0,40,88,.82) 100%);z-index:0}'
            . '.wo-slide .card-bottom{position:relative;z-index:1}'
            . '#single-slider-3:not(.is-active) .splide__track{overflow:hidden}'
            . '#single-slider-3:not(.is-active) .splide__list{display:flex!important;margin:0;padding:0;-webkit-animation:woHsSlide 12s ease-in-out infinite;animation:woHsSlide 12s ease-in-out infinite}'
            . '#single-slider-3:not(.is-active) .splide__slide{flex:0 0 100%;min-width:100%;max-width:100%}'
            . '@-webkit-keyframes woHsSlide{0%,28%{ -webkit-transform:translateX(0);transform:translateX(0)}33%,61%{ -webkit-transform:translateX(-100%);transform:translateX(-100%)}66%,94%{ -webkit-transform:translateX(-200%);transform:translateX(-200%)}100%{ -webkit-transform:translateX(0);transform:translateX(0)}}'
            . '@keyframes woHsSlide{0%,28%{transform:translateX(0)}33%,61%{transform:translateX(-100%)}66%,94%{transform:translateX(-200%)}100%{transform:translateX(0)}}'
            . '.wo-dash{padding:4px 15px 24px}'
            . '.wo-dash h1{font-size:24px;font-weight:800;color:' . $navy . ';margin:0 0 4px}'
            . '.wo-dash-hi{color:#64748b;font-size:14px;margin:0 0 14px}'
            . '.wo-dash-hi a{color:' . $navy . ';font-weight:700;text-decoration:none}'
            . '.wo-dash-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px}'
            . '.wo-dash-card{background:#fff;border-radius:16px;padding:14px;box-shadow:0 8px 22px rgba(0,40,88,.06)}'
            . '.wo-dash-k{font-size:11px;font-weight:800;letter-spacing:.06em;color:#94a3b8;text-transform:uppercase}'
            . '.wo-dash-v{font-size:22px;font-weight:800;color:#0f172a;margin:6px 0 2px}'
            . '.wo-dash-s{font-size:12px;color:#94a3b8}'
            . '.wo-dash-plan{position:relative}'
            . '.wo-dash-badge{position:absolute;top:14px;right:14px;background:#dcfce7;color:#166534;font-size:10px;font-weight:800;padding:4px 8px;border-radius:999px}'
            . '.wo-dash-bar{height:6px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin:10px 0}'
            . '.wo-dash-bar i{display:block;height:100%;width:0;background:' . $navy . ';border-radius:99px}'
            . '.wo-dash-meta{display:flex;justify-content:space-between;gap:8px;font-size:12px;color:#64748b}'
            . '.wo-dash-line{display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-top:1px solid #eef2f7;font-size:14px}'
            . '.wo-dash-line:first-of-type{border-top:0}'
            . '.wo-dash-line b{color:#0f172a}'
            . '.wo-chat-box{display:block;height:min(70vh,650px);min-height:420px;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 8px 22px rgba(0,40,88,.06)}'
            . '.wo-chat-box iframe{display:block;width:100%;height:100%;border:0}'
            . '.wo-chat-alt{display:block;text-align:center;margin:14px 0 8px}'
            . '.wo-nav-services.off{display:none!important}'
            . '.wo-find-code,.wo-trial-link{display:block;text-align:center;margin:14px 0 0;font-size:14px;font-weight:700;color:var(--wo-hs-navy,#002858)!important;text-decoration:none}'
            . '.wo-trial-link{color:#166534!important;font-weight:700!important}'
            . '#footer-bar a:not(.btn){display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:2px;padding:8px 2px 10px!important}'
            . '#footer-bar a:not(.btn) i,#footer-bar .active-nav i{position:static!important;transform:none!important;width:auto!important;height:auto!important;margin:0!important;line-height:1!important;border-radius:0!important;left:auto!important}'
            . '#footer-bar a:not(.btn) span,#footer-bar .active-nav span{position:static!important;padding:0!important;margin:0!important;order:2;display:block;font-size:10px;line-height:1.15}'
            . '#footer-bar a:not(.btn) svg.wo-nav-svg{width:18px;height:18px;display:block;margin:0 auto 2px;flex-shrink:0}'
            . '.bi-ticket-perforated-fill::before,.bi-ticket-perforated::before{content:"\\f6c9"}'
            . '.wo-ticket-code{letter-spacing:.12em;font-weight:800;color:' . $accent . ';font-size:22px}'
            . '.wo-ticket-code{display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap}'
            . '.wo-copy-btn{display:inline-flex;align-items:center;gap:4px;border:1px solid var(--wo-hs-navy,#002858);background:transparent;color:var(--wo-hs-navy,#002858);font-size:11px;letter-spacing:0;font-weight:700;padding:3px 10px;border-radius:999px;cursor:pointer;vertical-align:middle}'
            . '.wo-copy-btn:active{opacity:.7}'
            . '.theme-dark .wo-copy-btn{border-color:#f8fafc;color:#f8fafc}'
            . '.wo-found-code{display:none;align-items:center;justify-content:space-between;gap:8px;margin:0 15px 12px;padding:10px 12px;border-radius:12px;background:#ecfdf5;border:1.5px solid #86efac;color:#166534!important;font-size:13px;font-weight:700}'
            . '.wo-found-code.on{display:flex}'
            . '.wo-plans{padding-bottom:12px}'
            . '.theme-dark .wo-dash h1,.theme-dark .wo-dash-hi a,.theme-dark .wo-dash-v,.theme-dark .wo-dash-line b{color:#f8fafc}'
            . '.theme-dark .wo-dash-card{background:#1f1f1f;box-shadow:none}'
            . '.theme-dark .wo-dash-hi,.theme-dark .wo-dash-s,.theme-dark .wo-dash-meta{color:#94a3b8}'
            . '.theme-dark .wo-dash-line{border-top-color:#2a2a2a}'
            . '.theme-dark .wo-dash-bar{background:#2a2a2a}'
            . '.theme-dark .wo-pay-plan{color:#f8fafc;border-color:#333}'
            . '.theme-dark .wo-pay-plan:has(input:checked),.theme-dark .wo-pay-plan.on{background:rgba(240,80,24,.16)!important}'
            . '.theme-dark .wo-find-code{color:#fff!important}'
            . '.theme-dark .wo-ref-line button,.theme-dark .wo-pts,.theme-dark .wo-ref-modal .box{color:#f8fafc}'
            . '.theme-dark .wo-pts{background:rgba(240,80,24,.12);border-color:#3a2a20}'
            . '.theme-dark .wo-ref-modal .box{background:#1f1f1f}'
            . self::layout_css();
    }

    /**
     * Layout that lives inside login.html so the splash still looks like a
     * portal when MikroTik never got styles/bootstrap.css (RB4011 fetch).
     */
    private static function layout_css()
    {
        return '*{box-sizing:border-box}html,body{margin:0;padding:0;min-height:100%;background:#f1f1f7;color:#0f172a;-webkit-text-size-adjust:100%}'
            . 'body.theme-light{background:#f1f1f7}body.theme-dark{background:#0f1117;color:#f8fafc}'
            . 'a{color:inherit}img{max-width:100%;height:auto;border:0}ul,ol{margin:0;padding:0;list-style:none}'
            . '#page{min-height:100%;position:relative;z-index:1}'
            . '.page-content{position:relative;padding:14px 0 96px;max-width:520px;margin:0 auto}'
            . '.header-clear-medium{padding-top:88px!important}'
            . '.header-bar{z-index:98;left:0;right:0;top:0;min-height:55px;display:flex;align-items:center;padding:0 6px;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}'
            . '.header-fixed{position:fixed}'
            . '.header-bar-detached{border-radius:20px;left:10px!important;right:10px!important;top:8px!important;box-shadow:0 0 15px rgba(0,0,0,.1)!important}'
            . '.header-bar a{display:flex;align-items:center;justify-content:center;min-width:42px;min-height:48px;text-decoration:none}'
            . '.header-bar .header-title{flex:1;justify-content:flex-start;font-size:15px;font-weight:800;text-decoration:none}'
            . '#footer-bar{position:fixed;left:10px;right:10px;bottom:10px;z-index:98;display:flex;align-items:stretch;background:rgba(255,255,255,.96);border-radius:18px;box-shadow:0 10px 30px rgba(0,40,88,.12);padding:4px 6px;min-height:58px}'
            . '.footer-bar-detached{left:10px;right:10px;bottom:10px}'
            . '.card,.card-style{position:relative;background:#fff;border-radius:22px;margin:0 15px 16px;overflow:hidden;box-shadow:0 8px 24px rgba(15,23,42,.06)}'
            . '.card-style .content,.content{padding:16px 18px 18px;margin:0}'
            . '.card-overlay{position:absolute;inset:0;z-index:1;border-radius:inherit;pointer-events:none}'
            . '.card-bottom,.card-top,.card-center{position:absolute;left:0;right:0;z-index:3;padding:12px}'
            . '.card-bottom{bottom:0}.card-top{top:0}'
            . '.form-control,input.form-control,input[type=text],input[type=password],input[type=tel],input[type=email],textarea.form-control{width:100%;height:48px;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;font-size:16px;color:#002858;background:#fff}'
            . 'textarea.form-control{height:auto;min-height:120px;padding:12px 14px}'
            . '.form-custom{position:relative;margin-bottom:12px}'
            . '.btn,.btn-full{display:block;width:100%;text-align:center;text-decoration:none;border:0;border-radius:10px;padding:13px 16px;font-weight:800;font-size:14px;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;background:#002858;color:#fff}'
            . '.w-100{width:100%}.d-flex{display:flex}.text-center{text-align:center}.text-uppercase{text-transform:uppercase}'
            . '.color-white{color:#fff!important}.mb-0{margin-bottom:0}.mb-1{margin-bottom:.25rem}.mb-3{margin-bottom:1rem}.mt-4{margin-top:1.5rem}'
            . '.p-2{padding:.5rem}.p-3{padding:1rem}.px-2{padding-left:.5rem;padding-right:.5rem}.px-3{padding-left:1rem;padding-right:1rem}.pb-3{padding-bottom:1rem}.py-3{padding-top:1rem;padding-bottom:1rem}.mt-n2{margin-top:-.5rem}'
            . '.font-12{font-size:12px}.font-13{font-size:13px}.font-14{font-size:14px}.font-700,.font-800{font-weight:700}'
            . '.rounded-xs{border-radius:10px}.rounded-m{border-radius:16px}.rounded-0{border-radius:0}'
            . '.offcanvas,.modal,.offcanvas-backdrop{display:none!important}'
            . '[data-card-height]{min-height:140px}[data-card-height="110"]{min-height:110px}[data-card-height="230"]{min-height:230px}'
            . '.splide{overflow:hidden;margin:0 15px 8px;visibility:visible!important}'
            . '.alert{margin:0 15px 14px;padding:12px 14px;border-radius:12px;background:#fff6e5;color:#92400e;font-size:14px}'
            . '.bg-gradient-fade{background:linear-gradient(180deg,transparent,rgba(0,20,48,.88))}'
            . '.bg-black{background:#000}.opacity-30{opacity:.3}.opacity-70{opacity:.7}.opacity-80{opacity:.8}'
            . 'h1,h2,h3,h5,h6,p{margin:0 0 .4em}'
            . '.menu-divider{display:block;padding:10px 16px 4px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8}'
            . '.menu-list a{display:flex;align-items:center;gap:10px;padding:10px 4px;text-decoration:none;border-bottom:1px solid #eef2f7}'
            . '.theme-dark .header-bar,.theme-dark #footer-bar,.theme-dark .card,.theme-dark .card-style{background:#1a1d24;color:#f8fafc}'
            . '.theme-dark .form-control,body.theme-dark input.form-control{background:#111;border-color:#333;color:#f8fafc}';
    }

    public static function css_bundle()
    {
        $dir = self::kit_dir();
        $out = '';
        foreach (['styles/bootstrap.css', 'styles/style.css'] as $rel) {
            $p = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($p)) {
                $out .= (string)file_get_contents($p) . "\n";
            }
        }
        return $out;
    }

    public static function js_bundle()
    {
        $dir = self::kit_dir();
        $out = "window.woHotspotLite=true;\n";
        foreach (['scripts/bootstrap.min.js', 'scripts/custom.js'] as $rel) {
            $p = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($p)) {
                $out .= (string)file_get_contents($p) . "\n";
            }
        }
        return $out;
    }

    private static function mix($a, $b, $t)
    {
        $a = ltrim(self::hex($a, '#000000'), '#');
        $b = ltrim(self::hex($b, '#ffffff'), '#');
        $t = max(0, min(1, (float)$t));
        $out = '#';
        for ($i = 0; $i < 3; $i++) {
            $x = hexdec(substr($a, $i * 2, 2));
            $y = hexdec(substr($b, $i * 2, 2));
            $out .= sprintf('%02x', (int)round($x + ($y - $x) * $t));
        }
        return $out;
    }

    public static function default_logo_path()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';
    }

    public static function default_favicon_path()
    {
        $fav = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'favicon.png';
        return is_file($fav) ? $fav : self::default_logo_path();
    }

    public static function logos_dir()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'portals';
    }

    public static function custom_logo_path($router)
    {
        $id = (int)($router['id'] ?? 0);
        if ($id < 1) {
            return '';
        }
        return self::logos_dir() . DIRECTORY_SEPARATOR . $id . '.png';
    }

    public static function has_custom_logo($router)
    {
        $path = self::custom_logo_path($router);
        return $path !== '' && is_file($path);
    }

    public static function logo_path($router)
    {
        if (self::has_custom_logo($router)) {
            return self::custom_logo_path($router);
        }
        return self::default_logo_path();
    }

    public static function logo_abs_url($router)
    {
        $slug = trim((string)($router['slug'] ?? ''));
        $url = Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/logo.png');
        $path = self::custom_logo_path($router);
        if ($path !== '' && is_file($path)) {
            $url .= '?t=' . filemtime($path);
        }
        return $url;
    }

    public static function logo_public_url($router)
    {
        $id = (int)($router['id'] ?? 0);
        $path = self::custom_logo_path($router);
        if ($id < 1 || $path === '' || !is_file($path)) {
            return '';
        }
        return Tenant::url_portal('/ui/ui_custom/images/portals/' . $id . '.png') . '?t=' . filemtime($path);
    }

    public static function audio_dir()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . 'portals';
    }

    public static function custom_audio_path($router)
    {
        $id = (int)($router['id'] ?? 0);
        if ($id < 1) {
            return '';
        }
        return self::audio_dir() . DIRECTORY_SEPARATOR . $id . '.mp3';
    }

    public static function has_custom_audio($router)
    {
        $path = self::custom_audio_path($router);
        return $path !== '' && is_file($path);
    }

    public static function audio_abs_url($router)
    {
        $slug = trim((string)($router['slug'] ?? ''));
        $url = Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/welcome.mp3');
        $path = self::custom_audio_path($router);
        if ($path !== '' && is_file($path)) {
            $url .= '?t=' . filemtime($path);
        }
        return $url;
    }

    public static function audio_public_url($router)
    {
        $id = (int)($router['id'] ?? 0);
        $path = self::custom_audio_path($router);
        if ($id < 1 || $path === '' || !is_file($path)) {
            return '';
        }
        return Tenant::url_portal('/ui/ui_custom/audio/portals/' . $id . '.mp3') . '?t=' . filemtime($path);
    }

    public static function default_audio_path()
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . 'silence.mp3';
    }

    /**
     * French script read aloud in TTS mode - default text for a fresh router
     * and fallback when portal_audio_text is blank. Terminology matches the
     * portal's own copy exactly (buy-button label, ticket-code field, help
     * button) so the narration and the on-screen UI never disagree.
     */
    public static function default_audio_text()
    {
        return "Bienvenue ! Vous êtes connecté au réseau Wi-Fi. Pour naviguer sur Internet, achetez d’abord un ticket Wi-Fi, comme un forfait de données. Appuyez sur le bouton « Acheter du Wi-Fi », choisissez votre forfait, puis payez par Mobile Money ou Orange Money. Vous recevrez un code secret. Entrez ce code dans la case « Code ticket » juste ici, puis appuyez sur « Connexion » pour naviguer. Si vous avez déjà un code, entrez-le directement. En cas de souci, appuyez sur « Nous écrire » pour joindre l’assistance.";
    }

    public static function default_bundle_path()
    {
        $p = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'bundle-default.png';
        if (is_file($p)) {
            return $p;
        }
        $kit = self::kit_dir() . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'bundle-default.png';
        return is_file($kit) ? $kit : self::default_logo_path();
    }

    public static function default_slide_path($i)
    {
        $i = (int)$i;
        if ($i < 1 || $i > 3) {
            $i = 1;
        }
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'ui_custom'
            . DIRECTORY_SEPARATOR . 'images';
        foreach (['.jpg', '.jpeg', '.png', '.webp', '.svg'] as $ext) {
            $p = $dir . DIRECTORY_SEPARATOR . 'slide-default-' . $i . $ext;
            if (is_file($p)) {
                return $p;
            }
        }
        return self::default_bundle_path();
    }

    public static function custom_pic_path($router, $stem)
    {
        $id = (int)($router['id'] ?? 0);
        $stem = preg_replace('/[^a-z0-9-]/', '', strtolower((string)$stem));
        if ($id < 1 || $stem === '') {
            return '';
        }
        $dir = self::logos_dir();
        foreach (['.jpg', '.jpeg', '.png', '.webp'] as $ext) {
            $p = $dir . DIRECTORY_SEPARATOR . $id . '-' . $stem . $ext;
            if (is_file($p)) {
                return $p;
            }
        }
        return $dir . DIRECTORY_SEPARATOR . $id . '-' . $stem . '.jpg';
    }

    public static function pic_path($router, $stem)
    {
        $custom = self::custom_pic_path($router, $stem);
        if ($custom !== '' && is_file($custom)) {
            return $custom;
        }
        if (preg_match('/^slide-([123])$/', (string)$stem, $m)) {
            return self::default_slide_path((int)$m[1]);
        }
        return self::default_bundle_path();
    }

    public static function has_custom_pic($router, $stem)
    {
        $p = self::custom_pic_path($router, $stem);
        return $p !== '' && is_file($p);
    }

    /** Downscale + recompress for captive portal (returns true if streamed). */
    private static function stream_lite_jpeg($path, $maxW = 640, $quality = 60)
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return false;
        }
        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return false;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w < 1 || $h < 1) {
            imagedestroy($src);
            return false;
        }
        $maxW = max(160, (int)$maxW);
        $quality = max(40, min(85, (int)$quality));
        if ($w > $maxW) {
            $nw = $maxW;
            $nh = max(1, (int)round($h * ($maxW / $w)));
            $dst = imagecreatetruecolor($nw, $nh);
            if (!$dst) {
                imagedestroy($src);
                return false;
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        imagejpeg($src, null, $quality);
        imagedestroy($src);
        return true;
    }

    public static function pic_abs_url($router, $stem)
    {
        $slug = trim((string)($router['slug'] ?? ''));
        $stem = preg_replace('/[^a-z0-9-]/', '', strtolower((string)$stem));
        $file = ($stem === 'bundle') ? 'bundle.jpg' : ($stem . '.jpg');
        $url = Tenant::url_portal('/hotspot/' . rawurlencode($slug) . '/' . $file);
        $path = self::pic_path($router, $stem);
        if ($path !== '' && is_file($path)) {
            $url .= '?t=' . filemtime($path);
        }
        return $url;
    }

    public static function pic_preview_url($router, $stem)
    {
        $path = self::pic_path($router, $stem);
        if ($path === '' || !is_file($path)) {
            return '';
        }
        $dir = str_replace('\\', '/', self::logos_dir());
        $norm = str_replace('\\', '/', $path);
        if ($dir !== '' && strpos($norm, $dir) === 0) {
            return Tenant::url_business('/ui/ui_custom/images/portals/' . basename($path)) . '?t=' . filemtime($path);
        }
        if (preg_match('/slide-default-([123])\.svg$/', $norm, $m)) {
            return Tenant::url_business('/ui/ui_custom/images/slide-default-' . $m[1] . '.svg') . '?t=' . filemtime($path);
        }
        return Tenant::url_business('/ui/ui_custom/images/bundle-default.png') . '?t=' . filemtime($path);
    }

    public static function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function wa_link($phone, $text = '')
    {
        $p = preg_replace('/\D+/', '', (string)$phone);
        if ($p === '') {
            return '';
        }
        if (strlen($p) === 9) {
            $p = '237' . $p;
        } elseif (strpos($p, '0') === 0 && strlen($p) === 10) {
            $p = '237' . substr($p, 1);
        }
        $url = 'https://wa.me/' . $p;
        if ($text !== '') {
            $url .= '?text=' . rawurlencode($text);
        }
        return $url;
    }

    public static function tel_href($phone)
    {
        $p = preg_replace('/\D+/', '', (string)$phone);
        return $p !== '' ? ('tel:+' . (strlen($p) === 9 ? '237' . $p : $p)) : '';
    }

    public static function serve($router, $file, $preview = false)
    {
        $file = strtolower(basename((string)$file));
        $allowed = self::files();
        if (!in_array($file, $allowed, true)) {
            $file = 'login.html';
        }
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        if ($file === 'logo.png' || $file === 'favicon.png') {
            $path = $file === 'favicon.png' && !self::has_custom_logo($router)
                ? self::default_favicon_path()
                : self::logo_path($router);
            if (!is_file($path)) {
                http_response_code(404);
                exit;
            }
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=3600');
            readfile($path);
            exit;
        }
        if (preg_match('/^(bundle|slide-[123])\.jpe?g$/', $file, $m)) {
            $stem = $m[1];
            $path = self::pic_path($router, $stem);
            if (!is_file($path)) {
                http_response_code(404);
                exit;
            }
            // Serve a CNA-friendly JPEG (small width + quality) so MikroTik
            // fetch / phones never pull full-size portal photos.
            $maxW = ($stem === 'bundle') ? 720 : 640;
            $quality = ($stem === 'bundle') ? 62 : 58;
            if (self::stream_lite_jpeg($path, $maxW, $quality)) {
                exit;
            }
            $info = @getimagesize($path);
            $mime = (string)($info['mime'] ?? '');
            if ($mime === '' && preg_match('/\.svg$/i', $path)) {
                $mime = 'image/svg+xml';
            }
            header('Content-Type: ' . ($mime !== '' ? $mime : 'image/jpeg'));
            header('Cache-Control: public, max-age=3600');
            readfile($path);
            exit;
        }
        if ($file === 'welcome.mp3') {
            // files() pushes this to every router unconditionally, so - like
            // bundle.jpg/slide-N.jpg - it must always return something valid
            // rather than 404, even when the tenant hasn't recorded anything.
            $path = self::custom_audio_path($router);
            if ($path === '' || !is_file($path)) {
                $path = self::default_audio_path();
            }
            header('Content-Type: audio/mpeg');
            header('Cache-Control: public, max-age=3600');
            if (is_file($path)) {
                readfile($path);
            }
            exit;
        }
        if ($file === 'md5.js') {
            $path = self::kit_dir() . DIRECTORY_SEPARATOR . 'md5.js';
            header('Content-Type: application/javascript; charset=utf-8');
            if (is_file($path)) {
                readfile($path);
            } else {
                echo self::md5_js();
            }
            exit;
        }
        if ($file === 'wo.css') {
            header('Content-Type: text/css; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
            echo self::css_bundle();
            exit;
        }
        if ($file === 'wo.js') {
            header('Content-Type: application/javascript; charset=utf-8');
            header('Cache-Control: public, max-age=3600');
            echo self::js_bundle();
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        if ($preview) {
            header('Content-Security-Policy: frame-ancestors https://business.otantik.cm https://portal.otantik.cm https://wifi.otantik.cm');
        }
        echo self::html($router, $file, $preview);
        exit;
    }

    public static function html($router, $file, $preview = false)
    {
        $cfg = self::cfg($router, $preview);
        $path = self::kit_dir() . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) {
            $path = self::kit_dir() . DIRECTORY_SEPARATOR . 'login.html';
        }
        $html = (string)file_get_contents($path);
        if (!$preview) {
            // Drop PWA chrome that stalls CNA (manifest / home-screen icons).
            $html = preg_replace('/<link[^>]+rel=["\']manifest["\'][^>]*>\s*/i', '', $html) ?? $html;
            $html = preg_replace('/<link[^>]+rel=["\']apple-touch-icon["\'][^>]*>\s*/i', '', $html) ?? $html;
        }
        // Portal preview must also disable kit PWA/Swup: they blank the
        // status dashboard and intercept the trial link.
        if (stripos($html, 'woHotspotLite') === false) {
            $boot = '<script>window.woHotspotLite=true;</script>';
            if (stripos($html, '<head>') !== false) {
                $html = preg_replace('/<head>/i', '<head>' . $boot, $html, 1) ?? $html;
            } else {
                $html = $boot . $html;
            }
        }
        $html = self::abs_assets($html, $preview);
        $html = self::bind_zone($html, $router, $cfg, $file, $preview);
        $html = self::inject_inline_menus($html, $cfg);
        $html = self::inject_theme($html, $cfg);
        if ($preview) {
            $html = self::abs_pages($html, $router);
            $html = self::hosted_vars($html, $cfg, true, $file);
            $html = self::inject_public_trial($html, $router);
        }
        $html = self::inject_zone_js($html, $router, $cfg, $file);
        $html = self::inject_login_guard($html, $file);
        $html = self::inject_error_fr($html, $file);
        return $html;
    }

    public static function hotspot_plans($router)
    {
        $rows = [];
        try {
            $q = ORM::for_table('tbl_plans')->where('type', 'Hotspot')->where('enabled', 1);
            if (!empty($router['name'])) {
                $q->where('routers', $router['name']);
            }
            foreach ($q->order_by_asc('price')->find_many() as $p) {
                $rows[] = $p;
            }
        } catch (Throwable $e) {
        }
        if ($rows && function_exists('wifi_otantik_plans_with_bw')) {
            return wifi_otantik_plans_with_bw($rows);
        }
        return $rows;
    }

    private static function abs_assets($html, $preview = false)
    {
        // Router-hosted pages must use flat hotspot/ filenames. Nested
        // styles/… and ?v= query strings 404 on MikroTik (RB4011), so the
        // splash rendered as raw HTML. Preview still uses the kit CDN URLs.
        if (!$preview) {
            $html = preg_replace('/<link[^>]+href="(?:\\.\\/)?(?:css\\/style\\.css|styles\\/bootstrap\\.css|fonts\\/bootstrap-icons\\.css|styles\\/style\\.css)[^"]*"[^>]*>\\s*/i', '', $html) ?? $html;
            $html = preg_replace('/<script[^>]+src="(?:\\.\\/)?scripts\\/(?:bootstrap\\.min|custom)\\.js[^"]*"[^>]*><\\/script>\\s*/i', '', $html) ?? $html;
            if (stripos($html, 'href="wo.css"') === false) {
                if (stripos($html, '</head>') !== false) {
                    $html = preg_replace('/<\\/head>/i', '<link rel="stylesheet" href="wo.css"></head>', $html, 1) ?? $html;
                } else {
                    $html = '<link rel="stylesheet" href="wo.css">' . $html;
                }
            }
            if (stripos($html, 'src="wo.js"') === false) {
                if (stripos($html, '</body>') !== false) {
                    $html = preg_replace('/<\\/body>/i', '<script src="wo.js"></script></body>', $html, 1) ?? $html;
                } else {
                    $html .= '<script src="wo.js"></script>';
                }
            }
            return $html;
        }
        $base = self::kit_url();
        foreach (['styles/', 'fonts/', 'scripts/', 'images/', 'app/', 'plugins/'] as $p) {
            $html = str_replace('href="' . $p, 'href="' . $base . '/' . $p, $html);
            $html = str_replace("href='" . $p, "href='" . $base . '/' . $p, $html);
            $html = str_replace('src="' . $p, 'src="' . $base . '/' . $p, $html);
            $html = str_replace("src='" . $p, "src='" . $base . '/' . $p, $html);
        }
        foreach (['_manifest.json', '_service-worker.js', 'canal.mp4'] as $p) {
            $html = str_replace('href="' . $p, 'href="' . $base . '/' . $p, $html);
            $html = str_replace('src="' . $p, 'src="' . $base . '/' . $p, $html);
        }
        $html = str_replace('href="css/style.css"', 'href="' . $base . '/styles/style.css"', $html);
        $html = str_replace('src="md5.js"', 'src="' . $base . '/md5.js"', $html);
        $html = str_replace("src='md5.js'", "src='" . $base . "/md5.js'", $html);
        return $html;
    }

    private static function zone_base($router)
    {
        $slug = trim((string)($router['slug'] ?? ''));
        return rtrim(Tenant::url_portal('/wifi/' . rawurlencode($slug)), '/') . '/';
    }

    private static function abs_pages($html, $router)
    {
        $base = self::zone_base($router);
        $pages = [
            'login.html', 'ticket.html', 'chat.html', 'apropos.html', 'status.html',
            'logout.html', 'services.html', 'error.html', 'alogin.html', 'code.html', 'pay.html',
            'menu-main.html', 'menu-highlights.html', 'menu-bell.html', 'md5.js',
        ];
        foreach ($pages as $p) {
            $abs = $base . $p;
            $html = str_replace('href="' . $p . '"', 'href="' . $abs . '"', $html);
            $html = str_replace("href='" . $p . "'", "href='" . $abs . "'", $html);
            $html = str_replace('src="' . $p . '"', 'src="' . $abs . '"', $html);
            $html = str_replace("src='" . $p . "'", "src='" . $abs . "'", $html);
        }
        return $html;
    }

    private static function portal_host()
    {
        $h = Tenant::host_portal();
        if ($h === '' || preg_match('/^\d{1,3}(?:\.\d{1,3}){3}$/', $h)) {
            return 'portal.otantik.cm';
        }
        return $h;
    }

    public static function portal_abs($path)
    {
        $path = '/' . ltrim((string)$path, '/');
        return 'https://' . self::portal_host() . $path;
    }

    public static function ticket_svg()
    {
        return '<svg class="wo-nav-svg" viewBox="0 0 16 16" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M0 4.5A1.5 1.5 0 0 1 1.5 3h13A1.5 1.5 0 0 1 16 4.5V6a.5.5 0 0 1-.5.5 1.5 1.5 0 1 0 0 3 .5.5 0 0 1 .5.5v1.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5V10a.5.5 0 0 1 .5-.5 1.5 1.5 0 0 0 0-3A.5.5 0 0 1 0 6V4.5Z"/></svg>';
    }

    private static function inject_zone_js($html, $router, $cfg = [], $file = '')
    {
        $slug = trim((string)($router['slug'] ?? ''));
        if ($slug === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            return $html;
        }
        $z = json_encode($slug, JSON_UNESCAPED_SLASHES);
        $plansUrl = json_encode((string)($cfg['plans'] ?? (self::origin_base() . '/hotspot/' . rawurlencode($slug) . '/plans')), JSON_UNESCAPED_SLASHES);
        $portalHost = json_encode(self::portal_host(), JSON_UNESCAPED_SLASHES);
        $js = '<script>(function(){var z=' . $z . ',ph=' . $portalHost . ';try{sessionStorage.setItem("wo_zone",z);}catch(e){}'
            . 'document.cookie="wo_zone="+encodeURIComponent(z)+";Path=/;Max-Age=2592000;SameSite=Lax";'
            . 'var pages=/^(?:\\.\\/)?(login|ticket|chat|apropos|services|status|logout|error|alogin|code|pay)\\.html(\\?.*)?$/;'
            . 'var host=(location.hostname||"").toLowerCase();'
            . 'var onPortal=host.indexOf("otantik.cm")!==-1||host===ph;'
            . 'function fix(v){if(!v||!pages.test(v))return v;if(!onPortal)return v.replace(/^\\.\\//,"");return "https://"+ph+"/wifi/"+z+"/"+v.replace(/^\\.\\//,"");}'
            . 'function apply(){document.querySelectorAll("a[href]").forEach(function(el){var v=el.getAttribute("href");var n=fix(v);if(n&&n!==v)el.setAttribute("href",n);});}'
            . 'if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",apply);else apply();'
            // On MikroTik splash, plans are already inlined — skip VPS fetch (avoids CNA stalls).
            . 'if(onPortal){var box=document.getElementById("wo-plans");if(box){fetch(' . $plansUrl . ',{credentials:"omit"}).then(function(r){return r.json()}).then(function(d){if(d&&d.ok&&d.html)box.innerHTML=d.html;}).catch(function(){});}}'
            . 'if(!document.getElementById("woPayScrim")){var s=document.createElement("div");s.id="woPayScrim";s.className="wo-pay-scrim";s.hidden=true;s.innerHTML="<div class=\\"wo-pay-wait\\"><div class=\\"wo-spin-wrap\\"><div class=\\"spin\\"></div><div class=\\"spin2\\"></div></div><h2>Paiement</h2><p>Préparation de la page…</p><div class=\\"wo-dots\\" aria-hidden=\\"true\\"><i></i><i></i><i></i></div></div>";(document.body||document.documentElement).appendChild(s);}'
            . 'document.addEventListener("click",function(e){var a=e.target&&e.target.closest?e.target.closest("a.wo-bundle,a[href*=\'pay.html\']"):null;if(!a)return;var h=a.getAttribute("href")||"";if(h.indexOf("username=T-")>=0)return;if(h.indexOf("pay.html")<0&&h.indexOf("/buy")<0)return;var sc=document.getElementById("woPayScrim");if(sc){sc.hidden=false;sc.removeAttribute("hidden");sc.classList.add("is-on");}},true);'
            . 'var path=location.pathname.replace(/\\/+$/,"");if(onPortal&&/^\\/wifi\\/[^\\/.]+$/.test(path))location.replace("/wifi/"+z+"/login.html"+location.search);})();</script>';
        if (stripos($html, '</head>') !== false) {
            $n = preg_replace('/<\/head>/i', $js . '</head>', $html, 1);
            return is_string($n) ? $n : $html;
        }
        return $js . $html;
    }

    private static function menu_main_inner($cfg)
    {
        $recover = self::h($cfg['recover'] ?? 'code.html');
        $title = self::h($cfg['title'] ?? 'Wifi-Otantik');
        return '<div class="card card-style bg-23 mb-3 rounded-m mt-3" data-card-height="110">'
            . '<div class="card-bottom p-3"><h1 class="color-white font-18 font-700 mb-0">' . $title . '</h1></div>'
            . '<div class="card-overlay bg-gradient-fade rounded-0"></div></div>'
            . '<span class="menu-divider">Navigation</span>'
            . '<div class="menu-list"><div class="card card-style rounded-m p-3 py-2 mb-0">'
            . '<a href="login.html" id="nav-homes"><i class="gradient-blue shadow-bg shadow-bg-xs bi bi-house-fill"></i><span>Accueil</span><i class="bi bi-chevron-right"></i></a>'
            . '<a href="ticket.html" id="nav-comps"><i class="gradient-red shadow-bg shadow-bg-xs bi bi-heart-fill"></i><span>Tarifs</span><i class="bi bi-chevron-right"></i></a>'
            . '<a class="default-link wo-find-code" href="' . $recover . '" id="nav-code"><i class="gradient-yellow shadow-bg shadow-bg-xs">' . self::ticket_svg() . '</i><span>Mon code</span><i class="bi bi-chevron-right"></i></a>'
            . '<a href="services.html" id="nav-pages" class="wo-nav-services"><i class="gradient-green shadow-bg shadow-bg-xs bi bi-star-fill"></i><span>Nos services</span><i class="bi bi-chevron-right"></i></a>'
            . '<a href="apropos.html" id="nav-mails"><i class="gradient-magenta shadow-bg shadow-bg-xs bi bi-envelope-fill"></i><span>Contact</span><i class="bi bi-chevron-right"></i></a>'
            . '<a href="status.html" id="nav-status"><i class="gradient-blue shadow-bg shadow-bg-xs bi bi-bookmark-fill"></i><span>Statut</span><i class="bi bi-chevron-right"></i></a>'
            . '</div></div>';
    }

    private static function inject_inline_menus($html, $cfg)
    {
        $html = preg_replace('/\sdata-menu-load="[^"]*"/i', '', $html);
        if (strpos($html, 'id="nav-code"') !== false || strpos($html, 'id="nav-homes"') !== false) {
            return is_string($html) ? $html : '';
        }
        $inner = self::menu_main_inner($cfg);
        if (preg_match('/<div id="menu-main"[^>]*>/i', $html)) {
            $n = preg_replace(
                '/(<div id="menu-main"[^>]*>)[\s\S]*?(<\/div>)/i',
                '$1' . $inner . '$2',
                $html,
                1
            );
            if (is_string($n)) {
                $html = $n;
            }
        }
        return $html;
    }

    private static function inject_alogin($html, $cfg, $file, $router = null)
    {
        if ($file !== 'alogin.html') {
            return $html;
        }
        $status = '$(link-status)';
        if ($router && function_exists('wifi_otantik_hs_portal_host')) {
            $gw = trim((string)wifi_otantik_hs_portal_host($router));
            if ($gw !== '' && filter_var($gw, FILTER_VALIDATE_IP)) {
                $status = 'http://' . $gw . '/status';
            }
        }
        $n = preg_replace('/content="\d+;\s*url=[^"]+"/i', 'content="2; url=' . $status . '"', $html, 1);
        if (is_string($n)) {
            $html = $n;
        }
        $script = '<script>function startClock(){var s=' . json_encode($status) . ';if(!s||s.indexOf("$")===0)s="/status";location.replace(s);}</script>';
        $n = preg_replace('/<script>\s*function startClock\(\)[\s\S]*?<\/script>/i', $script, $html, 1);
        if (is_string($n)) {
            $html = $n;
        }
        return $html;
    }

    private static function strip_demo_cards($html, $file)
    {
        if (!in_array($file, ['status.html', 'logout.html', 'alogin.html'], true)) {
            return $html;
        }
        $n = preg_replace(
            '/<div class="card card-style bg-14 text-center[\s\S]*?<div class="card-overlay bg-black opacity-70"><\/div>\s*<\/div>/',
            '',
            $html,
            1
        );
        if (is_string($n)) {
            $html = $n;
        }
        $n = preg_replace(
            '/<div class="card card-style py-3">\s*<div class="content px-2 text-center">\s*<h5 class="mb-n1 font-12 color-highlight[\s\S]*?<\/div>\s*<\/div>/',
            '',
            $html,
            1
        );
        if (is_string($n)) {
            $html = $n;
        }
        if ($file === 'status.html') {
            $n = preg_replace(
                '/<div class="splide single-slider slider-no-dots[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/',
                '',
                $html,
                1
            );
            if (is_string($n)) {
                $html = $n;
            }
        }
        $n = preg_replace('/<div class="offcanvas offcanvas-bottom[^"]*" id="menu-install-pwa-[\s\S]*?<\/div>\s*<\/div>/', '', $html);
        return is_string($n) ? $n : $html;
    }

    private static function trial_splash_href($router)
    {
        $gw = '';
        if (function_exists('wifi_otantik_hs_portal_host')) {
            $gw = trim((string)wifi_otantik_hs_portal_host($router));
        }
        if ($gw === '' || !filter_var($gw, FILTER_VALIDATE_IP)) {
            return '';
        }
        return 'http://' . $gw . '/login?dst=' . rawurlencode('http://' . $gw . '/status');
    }

    private static function inject_public_trial($html, $router)
    {
        if ((int)($router['trial_on'] ?? 0) !== 1) {
            return $html;
        }
        $href = self::trial_splash_href($router);
        if ($href === '') {
            $href = '#';
        }
        // hosted_vars turns $(link-login-only) into "#", so rewrite any trial
        // link on the public portal to the hotspot gateway login.
        if (strpos($html, 'wo-trial-link') !== false) {
            $n = preg_replace(
                '/(<a class="wo-trial-link" href=")[^"]*(")/',
                '$1' . self::h($href) . '$2',
                $html,
                1
            );
            return is_string($n) ? $n : $html;
        }
        $btn = '<a class="wo-trial-link" href="' . self::h($href) . '">Essai gratuit</a>';
        $n = preg_replace('/(<button\b[^>]*>\s*Connexion\s*<\/button>)/i', '$1' . $btn, $html, 1);
        return is_string($n) ? $n : $html;
    }

    private static function bind_zone($html, $router, $cfg, $file, $preview = false)
    {
        $title = self::h($cfg['title']);
        $phone = trim((string)$cfg['phone']);
        $phoneShow = $phone !== '' ? self::h($phone) : '';
        $tel = self::tel_href($phone);
        $wa = self::wa_link($cfg['whatsapp'], 'Bonjour, j’ai besoin d’aide pour le Wi-Fi ' . $cfg['title']);
        $n = preg_replace('/<title>[^<]*<\/title>/i', '<title>' . $title . '</title>', $html);
        if (is_string($n)) {
            $html = $n;
        }
        $n = preg_replace('/(class="header-title[^"]*"[^>]*>)(?:MnaSpot|Duo Mobile PWA Kit)(<\/a>)/', '$1' . $title . '$2', $html);
        if (is_string($n)) {
            $html = $n;
        }
        $html = self::inject_favicon($html, $router, $cfg);
        $html = self::inject_logo($html, $router, $cfg);
        $html = str_replace(
            [
                '>MnaSpot</h1>',
                'Chez MnaSpot',
                'Add MnaSpot',
                'Install MnaSpot',
                'Install Mnaspot',
                'Duo Mobile PWA Kit',
                'www.mnatogo.com',
                'www.mantogo.com',
            ],
            [
                '>' . $title . '</h1>',
                'Chez ' . $title,
                'Add ' . $title,
                'Install ' . $title,
                'Install ' . $title,
                $title,
                'portal.otantik.cm',
                'portal.otantik.cm',
            ],
            $html
        );
        if ($tel !== '') {
            $html = str_replace(['tel:+22899000762', 'https://wa.me/22899000762'], [$tel, $wa !== '' ? $wa : $tel], $html);
        }
        if ($wa !== '') {
            $html = str_replace('https://wa.me/22899000762', $wa, $html);
        }
        if ($phoneShow !== '') {
            $html = str_replace(['+228 99 00 07 62', '+22899000762'], $phoneShow, $html);
        }
        $html = self::inject_slider($html, $router, $cfg);
        $html = self::inject_audio($html, $router, $cfg);
        $html = self::inject_help($html, $router, $cfg);
        $html = self::inject_plans($html, $router, $cfg, $file);
        $html = self::inject_login($html, $router, $cfg, $file, $preview);
        $html = self::inject_status($html, $router, $cfg, $file);
        $html = self::inject_alogin($html, $cfg, $file, $router);
        $html = self::strip_demo_cards($html, $file);
        $html = self::inject_contact_page($html, $router, $cfg, $file);
        $html = self::inject_code_page($html, $cfg, $file);
        $html = self::inject_pay_page($html, $router, $cfg, $file);
        $html = self::inject_services($html, $router, $cfg, $file);
        $html = self::inject_menu($html, $cfg);
        $html = self::inject_chat($html, $router, $cfg, $file, $wa, $tel);
        if ($file === 'apropos.html') {
            $html = str_replace('class="card card-style contact-form"', 'class="card card-style"', $html);
            $html = str_replace('<script src="sendtelegram.js"></script>', '', $html);
            $html = str_replace('onsubmit=" return sendtelegram()"', '', $html);
            $html = str_replace('onsubmit="return sendtelegram()"', '', $html);
            $html = preg_replace('/<script[^>]*plugins\/contact\/form\.js[^>]*><\/script>/i', '', $html);
            $html = str_replace('action=""', 'action="#"', $html);
            $html = str_replace('action="' . self::h($cfg['contact']) . '"', 'action="#"', $html);
            $html = preg_replace('/(<form[^>]*id="contactForm"[^>]*)>/i', '$1 onsubmit="return false;" novalidate>', $html, 1);
            $n = preg_replace('/<\/body>/i', self::contact_script($cfg['contact'], $cfg) . '</body>', $html, 1);
            $html = is_string($n) ? $n : ($html . self::contact_script($cfg['contact'], $cfg));
        }
        return $html;
    }

    private static function intergram_id($cfg)
    {
        $raw = trim((string)($cfg['tg_chat'] ?? ''));
        // Intergram talks to a personal Telegram user id (positive). Group
        // chats (-100…) are only valid for the Bot API, not the live widget.
        if (preg_match('/^\d{5,}$/', $raw)) {
            return $raw;
        }
        return '6262651910';
    }

    private static function inject_chat($html, $router, $cfg, $file, $wa, $tel)
    {
        if ($file !== 'chat.html') {
            return $html;
        }
        $id = self::intergram_id($cfg);
        $title = trim((string)($cfg['title'] ?? 'Wifi-Otantik'));
        if ($title === '') {
            $title = 'Wifi-Otantik';
        }
        $phone = trim((string)($cfg['phone'] ?? ''));
        $color = trim((string)($cfg['color'] ?? '#f05018'));
        if ($color === '' || $color[0] !== '#') {
            $color = '#f05018';
        }
        $slug = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($cfg['slug'] ?? 'wifi')) ?: 'wifi';
        $emergency = $phone !== ''
            ? ('En cas d’urgence ' . $phone)
            : 'Un agent vous répond dès que possible.';
        $conf = [
            'titleClosed' => 'Live Chat',
            'titleOpen' => $title,
            'closedStyle' => 'chat',
            'closedChatAvatarUrl' => '',
            'cookieExpiration' => 1,
            'introMessage' => 'Que puis-je faire pour vous ?',
            'autoResponse' => 'Un instant svp...',
            'autoNoResponse' => $emergency,
            'placeholderText' => 'Envoyez un message...',
            'displayMessageTime' => true,
            'mainColor' => $color,
            'alwaysUseFloatingButton' => false,
            'desktopHeight' => 450,
            'desktopWidth' => 370,
        ];
        $src = 'https://www.intergram.xyz/chat.html?id=' . rawurlencode($id)
            . '&host=' . rawurlencode($slug)
            . '&conf=' . rawurlencode(json_encode($conf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $alt = '';
        $href = $wa !== '' ? $wa : $tel;
        if ($href !== '') {
            $label = $wa !== '' ? 'Continuer sur WhatsApp' : 'Appeler';
            $alt = '<p class="wo-chat-alt"><a href="' . self::h($href) . '" class="btn btn-m rounded-s bg-green-dark shadow-bg shadow-bg-s">' . self::h($label) . '</a></p>';
        }
        $block = '<div class="wo-chat-box">'
            . '<iframe src="' . self::h($src) . '" title="Chat Telegram" width="100%" height="100%" frameborder="0" allow="clipboard-write"></iframe>'
            . '</div>' . $alt;
        $n = preg_replace(
            '/<div style="display:\s*block;height:\s*650px;?\s*">[\s\S]*?<\/iframe>\s*<\/div>/i',
            $block,
            $html,
            1
        );
        return is_string($n) ? $n : $html;
    }

    private static function inject_theme($html, $cfg)
    {
        $css = '<style id="wo-hs-theme">' . self::theme_css($cfg) . '</style>';
        $late = '<style id="wo-hs-theme-late">' . self::theme_css($cfg) . '</style>';
        $accent = self::h($cfg['color'] ?? '#f05018');
        $n = preg_replace(
            '/(<meta[^>]*id=["\']theme-check["\'][^>]*content=["\'])[^"\']*(["\'])/i',
            '$1' . $accent . '$2',
            $html,
            1
        );
        if (is_string($n)) {
            $html = $n;
        }
        $html = preg_replace('/<link[^>]+(?:fonts\.googleapis\.com|fonts\.gstatic\.com)[^>]*>\s*/i', '', $html) ?: $html;
        if (!empty($cfg['preview'])) {
            $html = str_replace('scripts/custom.js"', 'scripts/custom.js?v=wo-hs10"', $html);
            $html = str_replace('styles/style.css"', 'styles/style.css?v=wo-hs10"', $html);
        }
        $boot = '<script id="wo-hs-theme-boot">(function(){try{if(localStorage.getItem("Duo-Theme")==="dark-mode"){document.documentElement.setAttribute("data-hs-theme","dark");}}catch(e){}})();</script>';
        if (stripos($html, 'id="wo-hs-theme-boot"') === false) {
            if (stripos($html, '</head>') !== false) {
                $n = preg_replace('/<\/head>/i', $boot . '</head>', $html, 1);
                if (is_string($n)) {
                    $html = $n;
                }
            }
        }
        $apply = '<script id="wo-hs-theme-apply">(function(){try{if(localStorage.getItem("Duo-Theme")==="dark-mode"&&document.body){document.body.classList.add("theme-dark");document.body.classList.remove("theme-light","detect-theme");}}catch(e){}})();</script>';
        if (stripos($html, 'id="wo-hs-theme-apply"') === false) {
            $n = preg_replace('/(<body\b[^>]*>)/i', '$1' . $apply, $html, 1);
            if (is_string($n)) {
                $html = $n;
            }
        }
        if (stripos($html, 'id="wo-hs-theme"') === false) {
            if (stripos($html, '</head>') !== false) {
                $n = preg_replace('/<\/head>/i', $css . '</head>', $html, 1);
                if (is_string($n)) {
                    $html = $n;
                }
            } else {
                $html = $css . $html;
            }
        }
        if (stripos($html, 'id="wo-hs-theme-late"') === false) {
            if (stripos($html, '</body>') !== false) {
                $n = preg_replace('/<\/body>/i', $late . '</body>', $html, 1);
                if (is_string($n)) {
                    $html = $n;
                }
            } else {
                $html .= $late;
            }
        }
        return $html;
    }

    private static function inject_favicon($html, $router, $cfg)
    {
        $slug = (string)($cfg['slug'] ?? '');
        if (empty($cfg['preview'])) {
            $href = 'favicon.png';
        } else {
            $href = $slug !== ''
                ? ('https://' . self::portal_host() . '/hotspot/' . rawurlencode($slug) . '/favicon.png')
                : ((string)($cfg['logo'] ?? 'favicon.png'));
        }
        $links = '<link rel="icon" href="' . self::h($href) . '" type="image/png">'
            . '<link rel="shortcut icon" href="' . self::h($href) . '" type="image/png">'
            . '<link rel="apple-touch-icon" href="' . self::h($href) . '">';
        $n = preg_replace('/<link\b[^>]*rel=["\'](?:shortcut icon|icon|apple-touch-icon)["\'][^>]*>/i', '', $html);
        if (is_string($n)) {
            $html = $n;
        }
        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $links . '</head>', $html, 1);
        } else {
            $html = $links . $html;
        }
        return $html;
    }

    private static function inject_logo($html, $router, $cfg)
    {
        $src = (string)($cfg['logo'] ?? '');
        if ($src === '') {
            $src = self::logo_public_url($router);
        }
        if ($src === '') {
            $src = 'logo.png';
        }
        $img = '<img class="wo-hs-logo" src="' . self::h($src) . '" alt="Wifi-Otantik">';
        if (!preg_match('/class="wo-hs-logo"/', $html)) {
            $n = preg_replace('/(<a\b[^>]*class="header-title[^"]*"[^>]*>)/i', '$1' . $img, $html);
            if (is_string($n)) {
                $html = $n;
            }
        }
        $css = '<style>.wo-hs-logo{height:32px!important;width:auto!important;max-width:88px;object-fit:contain;margin:0 8px 0 0;border-radius:6px;flex-shrink:0;line-height:1!important}.header-bar .header-title{display:flex;align-items:center}</style>';
        if (stripos($html, '.wo-hs-logo{') === false) {
            if (stripos($html, '</head>') !== false) {
                $html = preg_replace('/<\/head>/i', $css . '</head>', $html, 1);
            } else {
                $html = $css . $html;
            }
        }
        return $html;
    }

    private static function slider_html($cfg)
    {
        $slides = $cfg['slides'] ?? self::default_slides();
        $urls = $cfg['slide_urls'] ?? [];
        $out = "<!-- Start Slider-->\n"
            . "<div class=\"splide single-slider slider-no-dots slider-no-arrows slider-boxed text-center mt-n2\" id=\"single-slider-3\">\n"
            . "<div class=\"splide__track\"><div class=\"splide__list\">\n";
        foreach ($slides as $i => $s) {
            $img = self::h($urls[$i] ?? ($cfg['bundle_url'] ?? 'slide-1.jpg'));
            $style = "background-image:url('" . $img . "')";
            $out .= '<div class="splide__slide">'
                . '<div class="card card-style mx-0 shadow-card shadow-card-m wo-slide" data-card-height="140" style="' . $style . '">'
                . '<div class="card-bottom pb-2 px-3">'
                . '<h3 class="color-white mb-1">' . self::h($s['title'] ?? '') . '</h3>'
                . '<p class="color-white opacity-80 mb-0 mt-n1 font-14">' . self::h($s['text'] ?? '') . '</p>'
                . '</div></div></div>' . "\n";
        }
        $out .= "</div></div></div>\n<!-- end slider-->\n";
        return $out;
    }

    private static function inject_slider($html, $router, $cfg)
    {
        $block = self::slider_html($cfg);
        $start = strpos($html, '<!-- Start Slider-->');
        $end = strpos($html, '<!-- end slider-->');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($html, 0, $start) . $block . substr($html, $end + strlen('<!-- end slider-->'));
        }
        $n = preg_replace(
            '/<div class="splide single-slider[^"]*" id="single-slider-3">[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/',
            $block,
            $html,
            1
        );
        return is_string($n) ? $n : $html;
    }

    /**
     * Tap-to-play "how this works" explainer for first-time visitors more
     * used to buying mobile-data bundles than a WiFi captive portal. Mobile
     * browsers block real silent autoplay-with-sound, so this is a button,
     * never an autoplay attempt. Two modes, picked per-router by the tenant
     * admin: 'upload' plays a real <audio> file (native pause/resume is
     * reliable); 'tts' has the visitor's own phone read $cfg['audio_text']
     * aloud via the Web Speech API - free, no recording needed, but some
     * older/cheap Android WebViews expose the API without it actually
     * working, hence the onstart watchdog in the injected script below.
     */
    private static function audio_html($cfg)
    {
        $mode = $cfg['audio_mode'] ?? 'off';
        if ($mode === 'off') {
            return "<!-- Start Audio-->\n<!-- end audio-->\n";
        }
        $json = json_encode([
            'mode' => $mode,
            'text' => (string)($cfg['audio_text'] ?? ''),
            'src' => (string)($cfg['audio_src'] ?? ''),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        $btnLabel = self::h('🔊 Écouter');
        $fallback = self::h('La lecture audio n’est pas disponible sur cet appareil.');
        return "<!-- Start Audio-->\n"
            . '<div class="card card-style wo-audio-card" id="woAudioCard"><div class="content px-3 py-3 text-center">'
            . '<button type="button" class="btn btn-full bg-blue-dark rounded-xs text-uppercase font-700 w-100 btn-s" id="woAudioBtn">'
            . '<span id="woAudioBtnLabel">' . $btnLabel . '</span></button>'
            . '<p class="wo-audio-fallback" id="woAudioFallback" hidden>' . $fallback . '</p>'
            . '</div></div>'
            . '<audio id="woAudioEl" preload="none" hidden></audio>'
            . '<style>.wo-audio-card{margin-top:8px}.wo-audio-fallback{color:#64748b;font-size:13px;margin:8px 0 0}#woAudioBtn.is-playing{opacity:.85}</style>'
            . '<script id="wo-audio-cfg">window.woAudioCfg=' . $json . ';(' . self::audio_js_iife() . ')();</script>'
            . "\n<!-- end audio-->\n";
    }

    private static function audio_js_iife()
    {
        return <<<'JS'
function(){
  var cfg = window.woAudioCfg;
  if (!cfg || cfg.mode === 'off') return;
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('woAudioBtn');
    var label = document.getElementById('woAudioBtnLabel');
    var fallback = document.getElementById('woAudioFallback');
    var audioEl = document.getElementById('woAudioEl');
    if (!btn) return;
    var playing = false;
    function showFallback(msg) { if (fallback) { fallback.hidden = false; if (msg) fallback.textContent = msg; } }
    function reset() {
      playing = false;
      if (label) label.textContent = '🔊 Écouter';
      btn.classList.remove('is-playing');
    }
    if (cfg.mode === 'upload') {
      if (!cfg.src) { btn.hidden = true; return; }
      audioEl.src = cfg.src;
      audioEl.addEventListener('ended', reset);
      audioEl.addEventListener('error', function () { btn.hidden = true; showFallback(); });
      btn.addEventListener('click', function () {
        if (playing) { audioEl.pause(); audioEl.currentTime = 0; reset(); return; }
        audioEl.play().then(function () {
          playing = true; if (label) label.textContent = '⏸ Mettre en pause'; btn.classList.add('is-playing');
        }).catch(function () { showFallback(); });
      });
      return;
    }
    if (!('speechSynthesis' in window) || typeof SpeechSynthesisUtterance === 'undefined') {
      btn.hidden = true; showFallback(); return;
    }
    btn.addEventListener('click', function () {
      if (playing) { try { speechSynthesis.cancel(); } catch (e) {} reset(); return; }
      var u = new SpeechSynthesisUtterance(cfg.text || '');
      u.lang = 'fr-FR'; u.rate = 0.95;
      var started = false;
      u.onstart = function () { started = true; };
      u.onend = reset;
      u.onerror = function () { reset(); showFallback('La lecture vocale a échoué sur cet appareil.'); };
      try {
        speechSynthesis.cancel();
        speechSynthesis.speak(u);
        playing = true; if (label) label.textContent = '⏹ Arrêter'; btn.classList.add('is-playing');
        setTimeout(function () {
          if (playing && !started) { try { speechSynthesis.cancel(); } catch (e) {} reset(); showFallback('La voix du téléphone ne répond pas sur cet appareil.'); }
        }, 4000);
      } catch (e) { reset(); showFallback(); }
    });
  });
}
JS;
    }

    private static function inject_audio($html, $router, $cfg)
    {
        $block = self::audio_html($cfg);
        $start = strpos($html, '<!-- Start Audio-->');
        $end = strpos($html, '<!-- end audio-->');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($html, 0, $start) . $block . substr($html, $end + strlen('<!-- end audio-->'));
        }
        return $html;
    }

    private static function inject_login($html, $router, $cfg, $file, $preview = false)
    {
        if ($file !== 'login.html') {
            return $html;
        }
        // Never use a bare filename as dst: MikroTik resolves it as a host → "unknown host".
        $html = str_replace('name="dst" value="status.html"', 'name="dst" value="$(link-status)"', $html);
        $html = str_replace("name=\"dst\" value='status.html'", 'name="dst" value="$(link-status)"', $html);
        $recover = self::h($cfg['recover'] ?? '');
        if ($recover === '') {
            $recover = self::h('code.html');
        }
        $trial = '';
        if ((int)($router['trial_on'] ?? 0) === 1) {
            $label = function_exists('wifi_otantik_trial_label')
                ? wifi_otantik_trial_label($router['trial_mins'] ?? 15)
                : ((int)($router['trial_mins'] ?? 15) . ' min');
            $gw = '';
            if (function_exists('wifi_otantik_hs_portal_host')) {
                $gw = trim((string)wifi_otantik_hs_portal_host($router));
            }
            $dst = ($gw !== '' && filter_var($gw, FILTER_VALIDATE_IP))
                ? rawurlencode('http://' . $gw . '/status')
                : '$(link-status-esc)';
            if ($preview) {
                $href = self::trial_splash_href($router);
                if ($href === '') {
                    $href = '#';
                }
            } else {
                $href = '$(link-login-only)?dst=' . $dst
                    . '&amp;username=T-$(mac-esc)&amp;password=';
            }
            $trial = '<a class="wo-trial-link" href="' . $href . '">Essai gratuit · ' . self::h($label) . '</a>';
        }
        $n = preg_replace('/\$\(if trial == \'yes\'\)[\s\S]*?\$\(endif\)/', $trial, $html, 1);
        if (is_string($n)) {
            $html = $n;
        }
        // If the online payment page's auto-login POST failed to authenticate,
        // MikroTik re-renders this same login page - our ?wo_code= query
        // string survives that (no redirect happens), so custom.js can show
        // the code here instead of the user losing it entirely.
        if (strpos($html, 'id="woFoundCode"') === false) {
            $banner = '<div class="wo-found-code" id="woFoundCode"><span>Code retrouvé : <b id="woFoundCodeVal"></b></span>'
                . '<button type="button" class="wo-copy-btn" data-wo-copy-target="#woFoundCodeVal">Copier</button></div>';
            $html = str_replace('<!-- Start Tab Login-->', $banner . '<!-- Start Tab Login-->', $html);
        }
        if (strpos($html, 'wo-find-code') !== false) {
            $html = preg_replace('/(<a class="default-link wo-find-code" href=")[^"]*(")/', '$1' . $recover . '$2', $html, 1);
            return is_string($html) ? $html : (string)$html;
        }
        $find = '<a class="default-link wo-find-code" href="' . $recover . '">Retrouver mon code</a>';
        $n = preg_replace('/(<button\b[^>]*>\s*Connexion\s*<\/button>)/i', '$1' . $find, $html, 1);
        return is_string($n) ? $n : $html;
    }

    private static function help_html($cfg)
    {
        $h = $cfg['help'] ?? self::default_help();
        return "<!-- Start Help-->\n"
            . "<div class=\"card card-style py-3\">\n"
            . "<div class=\"content px-2 text-center\">\n"
            . "<h5 class=\"mb-n1 font-12 color-highlight font-700 text-uppercase\">" . self::h($h['kicker']) . "</h5>\n"
            . "<h2>" . self::h($h['title']) . "</h2>\n"
            . "<p class=\"mb-3\">" . self::h($h['text']) . "</p>\n"
            . "<a href=\"apropos.html\" class=\"default-link btn btn-m rounded-s gradient-highlight shadow-bg shadow-bg-s px-5 mb-0 mt-2\">" . self::h($h['btn']) . "</a>\n"
            . "</div></div>\n<!-- end help-->\n";
    }

    private static function inject_help($html, $router, $cfg)
    {
        $block = self::help_html($cfg);
        $start = strpos($html, '<!-- Start Help-->');
        $end = strpos($html, '<!-- end help-->');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($html, 0, $start) . $block . substr($html, $end + strlen('<!-- end help-->'));
        }
        $n = preg_replace(
            '/<div class="card card-style py-3">\s*<div class="content px-2 text-center">\s*<h5 class="mb-n1 font-12 color-highlight[\s\S]*?<\/div>\s*<\/div>/',
            $block,
            $html,
            1
        );
        return is_string($n) ? $n : $html;
    }

    private static function inject_status($html, $router, $cfg, $file)
    {
        $n = preg_replace('/<div class="card card-style">\s*<div class="content">\s*<p><!--OST Widget[\s\S]*?OST Widget code end --><\/p>\s*<\/div>\s*<\/div>/', '', $html, 1);
        if (is_string($n)) {
            $html = $n;
        }
        if ($file !== 'status.html') {
            return $html;
        }
        $logout = 'logout.html?erase-cookie=true';
        $brand = strtolower(trim((string)($router['lan_host'] ?? '')));
        $brand = preg_replace('#^https?://#', '', $brand);
        $brand = preg_replace('/[\/:].*$/', '', $brand);
        $brand = preg_replace('/[^a-z0-9.-]/', '', $brand);
        if ($brand === '' || filter_var($brand, FILTER_VALIDATE_IP)) {
            $brand = 'hotspot.lan';
        }
        $tipUrl = self::h('http://' . $brand . '/status');
        $sessPrimary = (string)($cfg['session'] ?? '');
        $sessAlt = '';
        if ($sessPrimary !== '') {
            $sessAlt = preg_replace('#^http://[^/]+#', 'https://wifi.otantik.cm', $sessPrimary) ?: '';
            if ($sessAlt === $sessPrimary) {
                $sessAlt = '';
            }
        }
        $code = '';
        if (function_exists('_get')) {
            $code = trim((string)_get('u'));
        }
        if ($code === '' && !empty($_COOKIE['wo_hs_code'])) {
            $code = trim((string)$_COOKIE['wo_hs_code']);
        }
        $boot = null;
        if ($code !== '' && function_exists('wifi_otantik_hs_session_payload')) {
            $boot = wifi_otantik_hs_session_payload($router, $code, '', false);
        }
        $dash = "<!-- Start STATUS-->\n<div class=\"wo-dash\">\n"
            . "<h1>Tableau de bord</h1>\n"
            . "<p class=\"wo-dash-hi\">Bonjour, <b>$(username)</b> · <a href=\"" . $logout . "\">Déconnexion</a></p>\n"
            . "<div class=\"wo-dash-row\">\n"
            . "<div class=\"wo-dash-card\"><div class=\"wo-dash-k\">Prix du forfait</div><div class=\"wo-dash-v\" id=\"woSpent\">—</div><div class=\"wo-dash-s\">Montant de ce bundle</div></div>\n"
            . "<div class=\"wo-dash-card\"><div class=\"wo-dash-k\">Data utilisée</div><div class=\"wo-dash-v\">$(bytes-in-nice)</div><div class=\"wo-dash-s\">Session MikroTik</div></div>\n"
            . "</div>\n"
            . "<div class=\"wo-dash-card wo-dash-plan\"><div class=\"wo-dash-k\">Forfait actif</div><span class=\"wo-dash-badge\" id=\"woBadge\">…</span>"
            . "<div class=\"wo-dash-v\" id=\"woPlan\">—</div><div class=\"wo-dash-bar\"><i id=\"woBar\"></i></div>"
            . "<div class=\"wo-dash-s\" id=\"woBarHint\" style=\"margin:-4px 0 8px\">La barre se remplit à mesure que le forfait se termine</div>"
            . "<div class=\"wo-dash-meta\"><span>Restant : <b id=\"woLeft\">$(session-time-left)</b></span><span>Expire : <b id=\"woExp\">—</b></span></div></div>\n"
            . "<div class=\"wo-dash-card\" style=\"margin-top:10px\"><div class=\"wo-dash-k\">Votre code ticket</div>"
            . "<div class=\"wo-dash-v wo-ticket-code\"><span id=\"woTicketCode\">$(username)</span>"
            . "<button type=\"button\" class=\"wo-copy-btn\" data-wo-copy-target=\"#woTicketCode\">Copier</button></div>"
            . "<div class=\"wo-dash-s\">Saisissez ce code pour vous reconnecter</div></div>\n"
            . "<div class=\"wo-dash-card\" style=\"margin-top:10px\"><div class=\"wo-dash-k\">Session en cours</div>"
            . "<div class=\"wo-dash-line\"><span>Adresse IP</span><b>$(ip)</b></div>"
            . "<div class=\"wo-dash-line\"><span>Envoi</span><b>$(bytes-out-nice)</b></div>"
            . "<div class=\"wo-dash-line\"><span>Réception</span><b>$(bytes-in-nice)</b></div></div>\n"
            . "<p class=\"wo-dash-hi\" style=\"margin-top:14px\">Pour revoir ce tableau : ouvrez <a href=\"" . $tipUrl . "\">" . $tipUrl . "</a> (favori recommandé).</p>\n"
            . "</div>\n"
            . "<script>(function(){function mkSec(s){s=String(s||'').replace(/^\\s+|\\s+$/g,'');if(!s||s.charAt(0)==='$')return 0;var n=0,m;m=s.match(/(\\d+)w/);if(m)n+=parseInt(m[1],10)*604800;m=s.match(/(\\d+)d/);if(m)n+=parseInt(m[1],10)*86400;m=s.match(/(\\d+)h/);if(m)n+=parseInt(m[1],10)*3600;m=s.match(/(\\d+)m/);if(m)n+=parseInt(m[1],10)*60;m=s.match(/(\\d+)s/);if(m)n+=parseInt(m[1],10);if(!n&&/^\\d{1,2}:\\d{2}:\\d{2}$/.test(s)){var p=s.split(':');n=(+p[0])*3600+(+p[1])*60+(+p[2]);}return n;}"
            . "function paint(used){used=Math.max(0,Math.min(100,Math.round(used)));var b=document.getElementById('woBar');if(!b)return;b.style.width=used+'%';b.style.background=used>=85?'#dc2626':(used>=55?'#f05018':'#002858');}"
            . "function fmtLeft(sec){sec=Math.max(0,sec|0);var d=Math.floor(sec/86400),h=Math.floor((sec%86400)/3600),m=Math.floor((sec%3600)/60);if(d>0)return d+' j '+h+' h';if(h>0)return h+' h '+m+' min';return m+' min';}"
            . "var up0=mkSec('$(uptime)');var left0=mkSec('$(session-time-left)');var t0=Date.now();"
            . "function tickMk(){var up=up0+Math.floor((Date.now()-t0)/1000);var left=Math.max(0,left0-Math.floor((Date.now()-t0)/1000));var tot=up+left;if(tot>0)paint(100*up/tot);var wl=document.getElementById('woLeft');if(wl&&left0>0)wl.textContent=fmtLeft(left);}"
            . "if(up0+left0>0){tickMk();setInterval(tickMk,1000);}"
            . "function woUser(){var q=new URLSearchParams(location.search||'');var u=(q.get('u')||'').replace(/^\\s+|\\s+$/g,'');"
            . "if(u)return u;try{u=localStorage.getItem('wo_hs_code')||'';}catch(e){}"
            . "if(u)return u;var m=document.cookie.match(/(?:^|; )wo_hs_code=([^;]*)/);if(m){try{u=decodeURIComponent(m[1]);}catch(e2){u=m[1];}}"
            . "if(u)return u;var mk='$(username)';if(mk&&mk.charAt(0)!=='$'&&mk!=='bleu-lune')return mk;return '';}"
            . "var u=woUser();var boot=" . json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";"
            . "if(!u&&boot&&boot.user)u=boot.user;"
            . "if(u){try{localStorage.setItem('wo_hs_code',u);document.cookie='wo_hs_code='+encodeURIComponent(u)+';Path=/;Max-Age=604800;SameSite=Lax';}catch(e3){}}"
            . "if(u){var tc=document.getElementById('woTicketCode');if(tc)tc.textContent=u;var hb=document.querySelector('.wo-dash-hi b');if(hb)hb.textContent=u;}"
            . "if(u&&u.indexOf('T-')===0){var sp=document.getElementById('woSpent');if(sp)sp.textContent='0 F';var pl=document.getElementById('woPlan');if(pl&&(pl.textContent==='—'||!pl.textContent))pl.textContent='Essai gratuit';}"
            . "var mac='$(mac-esc)';if(mac.charAt(0)==='$')mac='';"
            . "var urls=[" . json_encode($sessPrimary, JSON_UNESCAPED_SLASHES) . "," . json_encode($sessAlt, JSON_UNESCAPED_SLASHES) . "].filter(Boolean);"
            . "function applySession(d){if(!d||!d.ok)return false;var el=function(i){return document.getElementById(i)};"
            . "if(el('woSpent'))el('woSpent').textContent=d.price_label||d.spent||'0 F';"
            . "if(el('woPlan'))el('woPlan').textContent=d.plan||'—';"
            . "if(el('woLeft')&&!(left0>0))el('woLeft').textContent=d.remaining||'—';"
            . "if(el('woExp'))el('woExp').textContent=d.expires||'—';"
            . "if(!(up0+left0>0))paint(d.pct||0);"
            . "if(el('woBadge')){el('woBadge').textContent=d.active?'ACTIF':'EXPIRÉ';el('woBadge').style.background=d.active?'#dcfce7':'#fee2e2';el('woBadge').style.color=d.active?'#166534':'#991b1b';}return true;}"
            . "if(boot&&boot.ok)applySession(boot);"
            . "function pull(i){if(!urls.length||!u||u.charAt(0)==='$')return;var url=urls[Math.min(i,urls.length-1)];"
            . "fetch(url+(url.indexOf('?')>=0?'&':'?')+'u='+encodeURIComponent(u)+(mac?'&mac='+encodeURIComponent(mac):''),{credentials:'omit'})"
            . ".then(function(r){return r.json()}).then(function(d){if(!applySession(d)&&i+1<urls.length)pull(i+1);}).catch(function(){if(i+1<urls.length)pull(i+1);});}"
            . "pull(0);})();</script>\n<!-- end STATUS-->\n";
        $start = strpos($html, '<!-- Start STATUS-->');
        $end = strpos($html, '<!-- end STATUS-->');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($html, 0, $start) . $dash . substr($html, $end + strlen('<!-- end STATUS-->'));
        }
        if ($start !== false) {
            $stop = strpos($html, '<!--  video end -->', $start);
            if ($stop === false) {
                $stop = strpos($html, '<div class="card card-style bg-14', $start);
            }
            if ($stop !== false) {
                return substr($html, 0, $start) . $dash . substr($html, $stop);
            }
        }
        return $html;
    }

    private static function inject_code_page($html, $cfg, $file)
    {
        if ($file !== 'code.html') {
            return $html;
        }
        $lookup = self::h($cfg['lookup'] ?? '');
        $form = self::h($cfg['recover_form'] ?? '');
        $html = str_replace('data-wo-lookup=""', 'data-wo-lookup="' . $lookup . '"', $html);
        $html = str_replace('data-wo-recover=""', 'data-wo-recover="' . $form . '"', $html);
        return $html;
    }

    private static function inject_pay_page($html, $router, $cfg, $file)
    {
        if ($file !== 'pay.html') {
            return $html;
        }
        $action = self::h((string)($cfg['buy'] ?? ''));
        $recover = self::h((string)($cfg['recover'] ?? 'code.html'));
        $plans = self::hotspot_plans($router);
        $radios = '';
        $plansJs = [];
        foreach ($plans as $i => $p) {
            $id = (int)$p['id'];
            $price = (int)$p['price'];
            $name = (string)$p['name_plan'];
            $dur = self::plan_duration_fr($p);
            $data = self::plan_data_label($p);
            $meta = $dur . ($data !== '' ? ' · ' . $data : '');
            $checked = $i === 0 ? ' checked' : '';
            $radios .= '<label class="wo-pay-plan"><input type="radio" name="plan_id" value="'
                . $id . '" data-price="' . $price . '" data-name="' . self::h($name)
                . '" data-meta="' . self::h($meta) . '"' . $checked . ' required> <b>'
                . self::h($name) . '</b> — ' . $price . ' Fcfa'
                . '<small>(' . self::h($meta) . ')</small></label>';
            $plansJs[] = [
                'id' => $id,
                'price' => $price,
                'name' => $name,
                'meta' => $meta,
            ];
        }
        if ($radios === '') {
            $radios = '<p>Aucun forfait pour cette zone.</p>';
        }
        $plansJson = json_encode($plansJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($plansJson)) {
            $plansJson = '[]';
        }
        $inner = '<h3>Payer par Mobile Money</h3>'
            . '<div class="card card-style"><div class="content">'
            . '<p class="mb-3">Entrez votre numéro. Votre code s’affiche après le paiement.</p>'
            . '<form method="post" action="' . $action . '" id="woPay" class="demo-animation m-0">'
            . '<input type="hidden" name="mac" id="woPayMac" value="">'
            . '<div id="woPaySel" class="wo-pay-sel" hidden></div>'
            . '<div id="woPayRadios">' . $radios . '</div>'
            . '<div class="form-custom form-label form-icon mb-3">'
            . '<i class="bi bi-phone font-14"></i>'
            . '<input class="form-control rounded-xs" type="tel" name="phone" id="woPayPhone" inputmode="tel" autocomplete="tel" placeholder="6XX XXX XXX" required>'
            . '<label class="color-theme">Téléphone</label>'
            . '</div>'
            . '<div class="wo-pay-op" id="woOp"><span class="dot"></span><span class="txt"></span></div>'
            . '<p class="wo-phone-err" id="woPhoneErr">Numéro MTN ou Orange invalide.</p>'
            . '<button type="submit" class="btn btn-full bg-highlight rounded-xs text-uppercase font-700 w-100 btn-s mt-4">Payer</button>'
            . '</form>'
            . '<p class="text-center mt-3 mb-0"><a href="' . $recover . '">J’ai déjà payé — retrouver mon code</a></p>'
            . '</div></div>';
        $overlay = '<div class="wo-pay-scrim" id="woPayWait" hidden><div class="wo-pay-wait">'
            . '<div class="wo-spin-wrap"><div class="spin"></div><div class="spin2"></div></div>'
            . '<h2>Validez sur votre téléphone</h2>'
            . '<p>Une demande Mobile Money a été envoyée. Entrez votre code secret.</p>'
            . '<div class="wo-dots" aria-hidden="true"><i></i><i></i><i></i></div>'
            . '<div class="wo-ussd">'
            . '<div id="woBoxMtn"><b>MTN MoMo</b><code>*126#</code></div>'
            . '<div id="woBoxOm"><b>Orange Money</b><code>#150*50#</code></div>'
            . '</div>'
            . '<p class="font-12 opacity-70 mb-0">Pas de pop-up ? Composez le code USSD, puis confirmez.</p>'
            . '</div></div>';
        $js = '<script>(function(){var plans=' . $plansJson . ';'
            . 'var q=new URLSearchParams(location.search||"");'
            . 'var mac=q.get("mac")||"";var pid=q.get("pid")||"";'
            . 'var hm=document.getElementById("woPayMac");if(hm)hm.value=mac;'
            . 'var sel=document.getElementById("woPaySel");var radios=document.getElementById("woPayRadios");'
            . 'function findPlan(id){id=String(id||"");for(var i=0;i<plans.length;i++){if(String(plans[i].id)===id)return plans[i];}return null;}'
            . 'function paintSel(p){if(!sel||!p)return;sel.hidden=false;sel.removeAttribute("hidden");'
            . 'sel.innerHTML="<small>"+String(p.meta||"").replace(/</g,"&lt;")+"</small><strong>"+String(p.price)+" Fcfa</strong><span>"+String(p.name||"").replace(/</g,"&lt;")+"</span>";'
            . 'if(radios)radios.style.display="none";'
            . 'document.querySelectorAll("#woPayRadios input[name=plan_id]").forEach(function(r){r.removeAttribute("name");r.checked=false;});'
            . 'var old=document.getElementById("woPayPlanHidden");if(old)old.parentNode.removeChild(old);'
            . 'var h=document.createElement("input");h.type="hidden";h.name="plan_id";h.id="woPayPlanHidden";h.value=String(p.id);h.setAttribute("data-price",String(p.price));'
            . 'var f0=document.getElementById("woPay");if(f0)f0.insertBefore(h,f0.firstChild);}'
            . 'var picked=findPlan(pid);'
            . 'if(picked){paintSel(picked);}else if(pid){document.querySelectorAll("input[name=plan_id]").forEach(function(r){r.checked=String(r.value)===String(pid);});}'
            . 'var f=document.getElementById("woPay");var el=document.getElementById("woPayPhone");var err=document.getElementById("woPhoneErr");var op=document.getElementById("woOp");'
            . 'function digits(s){s=String(s||"").replace(/\\D+/g,"");if(s.indexOf("00")===0)s=s.slice(2);if(s.indexOf("237")===0&&s.length>9)s=s.slice(3);if(s.charAt(0)==="0"&&s.length>=10)s=s.slice(1);return s.slice(0,9);}'
            . 'function detect(p){p=digits(p);if(/^(69|65[5-9]|64|68[6-9])/.test(p))return "ORANGE";if(/^(67|65[0-4]|68[0-3])/.test(p))return "MTN";return "";}'
            . 'function format(p){p=digits(p);if(p.length<=3)return p;if(p.length<=6)return p.slice(0,3)+" "+p.slice(3);return p.slice(0,3)+" "+p.slice(3,6)+" "+p.slice(6);}'
            . 'function ok(s){var d=digits(s);return d.length===9&&d.charAt(0)==="6"&&detect(d)!=="";}'
            . 'function paintOp(){if(!op||!el)return;var d=digits(el.value);var s=detect(d);op.className="wo-pay-op";'
            . 'if(d.length<2){op.classList.remove("on");return;}op.classList.add("on");var t=op.querySelector(".txt");'
            . 'if(!s){op.classList.add("bad");if(t)t.textContent="Numéro non reconnu (MTN ou Orange)";}'
            . 'else if(s==="MTN"){op.classList.add("mtn");if(t)t.textContent="MTN Mobile Money";}'
            . 'else{op.classList.add("om");if(t)t.textContent="Orange Money";}}'
            . 'if(el){el.addEventListener("input",function(){var c=el.selectionStart;var before=el.value.length;el.value=format(el.value);'
            . 'try{el.setSelectionRange(Math.max(0,c+(el.value.length-before)),Math.max(0,c+(el.value.length-before)));}catch(e0){}'
            . 'if(err)err.style.display="none";paintOp();});paintOp();'
            . 'try{var saved=localStorage.getItem("wo_hs_phone")||"";if(saved&&!el.value){el.value=format(saved);paintOp();}}catch(e1){}}'
            . 'function showWait(sc){if(!sc)return;sc.hidden=false;sc.removeAttribute("hidden");sc.classList.add("is-on");'
            . 'try{document.documentElement.appendChild(sc);}catch(e){}try{void sc.offsetWidth;}catch(e2){}}'
            . 'function paint(fn){var run=function(){setTimeout(fn,160);};'
            . 'if(window.requestAnimationFrame)requestAnimationFrame(function(){requestAnimationFrame(run);});else run();}'
            . 'if(f){f.addEventListener("submit",function(ev){if(!ok(el&&el.value)){ev.preventDefault();if(err){err.style.display="block";err.textContent="Numéro MTN ou Orange invalide.";}paintOp();return false;}'
            . 'if(f.getAttribute("data-off")){ev.preventDefault();return false;}ev.preventDefault();f.setAttribute("data-off","1");'
            . 'if(err)err.style.display="none";'
            . 'try{if(el){el.blur();}if(document.activeElement&&document.activeElement.blur)document.activeElement.blur();window.scrollTo(0,0);'
            . 'localStorage.setItem("wo_hs_phone",digits(el.value));}catch(e3){}'
            . 'var b=f.querySelector("[type=submit]");if(b)b.classList.add("wo-btn-loading");'
            . 'var sc=document.getElementById("woPayWait");showWait(sc);'
            . 'var dig=digits(el&&el.value);var s=detect(dig);var bm=document.getElementById("woBoxMtn");var bo=document.getElementById("woBoxOm");'
            . 'if(bm)bm.classList.toggle("on",s==="MTN");if(bo)bo.classList.toggle("on",s==="ORANGE");'
            . 'paint(function(){if(b)b.disabled=true;HTMLFormElement.prototype.submit.call(f);});});}'
            . 'window.addEventListener("pageshow",function(){if(!f)return;f.removeAttribute("data-off");'
            . 'var b2=f.querySelector("[type=submit]");if(b2){b2.disabled=false;b2.classList.remove("wo-btn-loading");}'
            . 'var sc2=document.getElementById("woPayWait");if(sc2){sc2.hidden=true;sc2.setAttribute("hidden","hidden");sc2.classList.remove("is-on");}});'
            . '})();</script>';
        if (strpos($html, 'id="wo-pay-box"') !== false) {
            $n = preg_replace(
                '/<h3>Payer par Mobile Money<\/h3>\s*<div id="wo-pay-box"[^>]*>[\s\S]*?<\/div>\s*<\/div>/',
                $inner,
                $html,
                1
            );
            $html = is_string($n) ? $n : $html;
        } else {
            $n = preg_replace(
                '/<div class="page-content header-clear-medium">[\s\S]*?<\/div>/',
                '<div class="page-content header-clear-medium">' . $inner . '</div>',
                $html,
                1
            );
            $html = is_string($n) ? $n : $html;
        }
        if (stripos($html, '</body>') !== false) {
            $n = preg_replace('/<\/body>/i', $overlay . $js . '</body>', $html, 1);
            $html = is_string($n) ? $n : ($html . $overlay . $js);
        } else {
            $html .= $overlay . $js;
        }
        return $html;
    }

    private static function inject_contact_page($html, $router, $cfg, $file)
    {
        if ($file !== 'apropos.html') {
            return $html;
        }
        $phone = trim((string)$cfg['phone']);
        $tel = self::tel_href($phone);
        $waTxt = trim((string)($cfg['wa_text'] ?? ''));
        if ($waTxt === '') {
            $waTxt = 'Bonjour, j’ai besoin d’aide pour le Wi-Fi ' . ($cfg['title'] ?? '');
        }
        $wa = self::wa_link($cfg['whatsapp'] ?? '', $waTxt);
        $email = trim((string)($cfg['email'] ?? ''));
        $web = trim((string)($cfg['web'] ?? ''));
        $fb = trim((string)($cfg['facebook'] ?? ''));
        $intro = trim((string)($cfg['contact_intro'] ?? ''));
        if ($intro === '') {
            $intro = 'Un souci ? Écrivez-nous, on vous répond.';
        }
        $rows = '';
        if ($tel !== '') {
            $rows .= '<a href="' . self::h($tel) . '" class="default-link list-group-item"><i class="bi color-green-dark bi-telephone-fill"></i><div>Téléphone</div><span class="badge rounded-xl font-500 px-2 font-8 text-uppercase">Appeler</span></a>';
        }
        if ($wa !== '') {
            $rows .= '<a href="' . self::h($wa) . '" class="default-link list-group-item"><i class="bi color-whatsapp bi-whatsapp"></i><div>WhatsApp</div><span class="badge rounded-xl font-500 px-2 font-8 text-uppercase">Écrire</span></a>';
        }
        if ($email !== '') {
            $rows .= '<a href="mailto:' . self::h($email) . '" class="default-link list-group-item"><i class="bi color-blue-dark bi-envelope-fill"></i><div>Email</div><span class="badge rounded-xl font-500 px-2 font-8 text-uppercase">Envoyer</span></a>';
        }
        if ($web !== '') {
            $href = (stripos($web, 'http') === 0) ? $web : ('https://' . $web);
            $rows .= '<a href="' . self::h($href) . '" class="default-link list-group-item"><i class="bi color-highlight bi-globe"></i><div>Site web</div><span class="badge rounded-xl font-500 px-2 font-8 text-uppercase">Ouvrir</span></a>';
        }
        if ($fb !== '') {
            $href = (stripos($fb, 'http') === 0) ? $fb : ('https://' . ltrim($fb, '/'));
            $rows .= '<a href="' . self::h($href) . '" class="default-link list-group-item"><i class="bi color-facebook bi-facebook"></i><div>Facebook</div><span class="badge rounded-xl font-500 px-2 font-8 text-uppercase">Ouvrir</span></a>';
        }
		$info = '<h5 class="color-highlight font-13 mb-n1">Nous joindre</h5>'
            . '<h2 class="font-700 pb-0">Informations</h2>'
            . '<div class="list-group list-custom list-group-m rounded-xs list-group-flush">' . $rows . '</div></div></div>';
        $mark = strpos($html, '<h5 class="color-highlight font-13 mb-n1">');
        $nextCard = $mark !== false ? strpos($html, '<div class="card card-style py-3">', $mark) : false;
        if ($mark !== false && $nextCard !== false) {
            $html = substr($html, 0, $mark) . $info . substr($html, $nextCard);
        }
        $html = str_replace(
            [
                'Name is required!',
                'Email Address is required!',
                'Invalid email Address!',
                'Please enter your message!',
                'Get in Touch',
                'Tap to Call',
                'Tap to Open',
                'E-commerce',
                'Page FacebookS',
                'Code ticket',
                '>Connexion</button>',
            ],
            [
                'Le nom est obligatoire',
                'Le numéro est obligatoire',
                'Numéro invalide',
                'Écrivez votre message',
                'Nous joindre',
                'Appeler',
                'Ouvrir',
                '',
                'Page Facebook',
                'Nom',
                '>Envoyer</button>',
            ],
            $html
        );
        $html = preg_replace('/<a href="https:\/\/www\.geekrootlab\.com"[\s\S]*?<\/a>/', '', $html, 1);
        $html = str_replace('Pour plus d\'info ...', self::h($intro), $html);
        $html = str_replace('lang="en"', 'lang="fr"', $html);
        $html = str_replace('<title>A propos</title>', '<title>Contact</title>', $html);
        return $html;
    }

    private static function inject_services($html, $router, $cfg, $file)
    {
        if ($file !== 'services.html') {
            return $html;
        }
        if (empty($cfg['services_on'])) {
            $html = preg_replace('/<div class="page-content[\s\S]*?<div class="card card-style">[\s\S]*$/m', '<div class="page-content header-clear-medium"><div class="card card-style"><div class="content"><h1>Services</h1><p>Cette page n’est pas activée pour cette zone.</p></div></div></div></div></body></html>', $html, 1);
            return is_string($html) ? $html : '';
        }
        $items = $cfg['services'] ?? self::default_services();
        $acc = '';
        foreach ($items as $i => $it) {
            $n = $i + 1;
            $acc .= '<div class="accordion-item"><button class="accordion-button px-0 ps-1 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordion5-' . $n . '">'
                . '<i class="bi bi-heart-fill color-red-dark pe-3 font-14"></i><span class="font-600 font-13">' . self::h($it['title']) . '</span><i class="bi bi-plus font-20"></i></button>'
                . '<div id="accordion5-' . $n . '" class="accordion-collapse collapse" data-bs-parent="#accordion-group-5"><p class="mb-0 pb-3">' . self::h($it['text']) . '</p></div></div>';
        }
        $n = preg_replace('/<div class="accordion accordion-m border-0" id="accordion-group-5">[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/', '<div class="accordion accordion-m border-0" id="accordion-group-5">' . $acc . '</div></div></div>', $html, 1);
        return is_string($n) ? $n : $html;
    }

    private static function inject_menu($html, $cfg)
    {
        $recover = trim((string)($cfg['recover'] ?? ''));
        if ($recover === '') {
            $recover = 'code.html';
        }
        $recover = self::h($recover);
        $codeNav = '<a href="' . $recover . '">' . self::ticket_svg() . '<span>Mon code</span></a>';
        $n = preg_replace(
            '/<a href="#"\s+data-bs-toggle="offcanvas"\s+data-bs-target="#menu-main">\s*<i class="bi bi-list"><\/i>\s*<span>Menu<\/span>\s*<\/a>/i',
            $codeNav,
            $html
        );
        if (is_string($n)) {
            $html = $n;
        }
        if ($recover !== '') {
            $n = preg_replace('/(<a class="[^"]*wo-find-code[^"]*" href=")[^"]*(")/', '$1' . $recover . '$2', $html);
            if (is_string($n)) {
                $html = $n;
            }
        }
        $html = str_replace('<i class="bi bi-ticket-perforated-fill font-16"></i>', self::ticket_svg(), $html);
        $html = str_replace(
            '<i class="gradient-yellow shadow-bg shadow-bg-xs bi bi-ticket-perforated-fill"></i>',
            '<i class="gradient-yellow shadow-bg shadow-bg-xs">' . self::ticket_svg() . '</i>',
            $html
        );
        if (empty($cfg['services_on'])) {
            $html = preg_replace('/<a href="services\.html"[^>]*>[\s\S]*?<\/a>/', '', $html, 1);
            $html = str_replace('class="wo-nav-services"', 'class="wo-nav-services off"', $html);
        }
        $html = str_replace(
            [
                'NAVIGATION',
                'Paramètres',
                'Dark Mode',
                'Couleur',
                '<span>Status</span>',
                '<span>Home</span>',
                '<span>contact</span>',
                '>Status<',
                '>Home<',
                '>Message<',
            ],
            [
                'Navigation',
                'Réglages',
                'Mode sombre',
                'Couleur',
                '<span>Statut</span>',
                '<span>Accueil</span>',
                '<span>Contact</span>',
                '>Statut<',
                '>Accueil<',
                '>Messages<',
            ],
            $html
        );
        $html = preg_replace('#https?://[^"\']*otantik\.cm/recuperer[^"\']*#i', 'code.html', $html);
        return $html;
    }

    private static function inject_wa_fab($html, $cfg)
    {
        return $html;
    }

    private static function inject_plans($html, $router, $cfg, $file = '')
    {
        if ($file === 'pay.html') {
            return $html;
        }
        $cards = self::plan_cards($router, $cfg);
        $block = '<div id="wo-plans" class="wo-plans">'
            . "<!-- Start Tarfis-->\n" . $cards . "<!-- end tarifs-->\n"
            . '</div>';
        $n = preg_replace(
            '/(?:<div id="wo-trial"[^>]*>[\s\S]*?<\/div>\s*)?<div id="wo-plans"[^>]*>[\s\S]*?<!-- end tarifs-->\s*<\/div>/',
            $block,
            $html,
            1
        );
        if (is_string($n) && $n !== $html) {
            return $n;
        }
        $start = strpos($html, '<!-- Start Tarfis-->');
        if ($start === false) {
            $start = strpos($html, '<!-- Tarifs start-->');
        }
        if ($start === false && in_array($file, ['login.html', 'ticket.html'], true)) {
            $formEnd = strrpos($html, '</form>');
            if ($formEnd !== false) {
                $after = $formEnd + 7;
                $close = strpos($html, '</div>', $after);
                if ($close !== false) {
                    $close = strpos($html, '</div>', $close + 6);
                }
                if ($close !== false) {
                    return substr($html, 0, $close + 6) . "\n" . $block . substr($html, $close + 6);
                }
            }
            return $html . $block;
        }
        if ($start === false) {
            return $html;
        }
        $needle = '<!-- end tarifs-->';
        $end = strpos($html, $needle, $start);
        if ($end === false) {
            return substr($html, 0, $start) . $block . substr($html, $start);
        }
        $end += strlen($needle);
        $html = substr($html, 0, $start) . $block . substr($html, $end);
        return $html;
    }

    public static function plans_markup($router)
    {
        return self::plan_cards($router, self::cfg($router, false));
    }

    private static function checkout_href($cfg, $planId = 0)
    {
        // On the MikroTik splash, keep the user on local pay.html (same origin as
        // the CNA). Cloud /buy URLs break Apple/Android captive browsers.
        if (empty($cfg['preview'])) {
            $q = 'pay.html?mac=$(mac-esc)';
            if ((int)$planId > 0) {
                $q .= '&pid=' . (int)$planId;
            }
            return $q;
        }
        $slug = (string)($cfg['slug'] ?? '');
        $path = '/wifi/' . rawurlencode($slug) . '/buy';
        if ((int)$planId > 0) {
            $path .= '/' . (int)$planId;
        }
        if (function_exists('wifi_otantik_checkout_url') && $slug !== '') {
            $url = wifi_otantik_checkout_url($slug, $planId, false);
        } else {
            $url = self::portal_abs($path);
        }
        if ($url === '' || strpos($url, '/login.html') !== false) {
            $url = self::portal_abs($path);
        }
        $sep = strpos($url, '?') === false ? '?' : '&';
        return $url . $sep . 'mac=$(mac-esc)';
    }

    private static function plan_hours($p)
    {
        $n = max(1, (int)($p['validity'] ?? 1));
        $u = (string)($p['validity_unit'] ?? 'Hrs');
        if ($u === 'Mins') {
            return max(0.02, $n / 60);
        }
        if ($u === 'Days') {
            return $n * 24;
        }
        if ($u === 'Months') {
            return $n * 24 * 30;
        }
        return (float)$n;
    }

    private static function plan_duration_fr($p)
    {
        $n = (int)($p['validity'] ?? 0);
        $u = (string)($p['validity_unit'] ?? 'Hrs');
        if ($u === 'Mins') {
            return $n . ($n > 1 ? ' Minutes' : ' Minute');
        }
        if ($u === 'Hrs') {
            return $n . ($n > 1 ? ' Heures' : ' Heure');
        }
        if ($u === 'Days') {
            return $n . ($n > 1 ? ' Jours' : ' Jour');
        }
        if ($u === 'Months') {
            return $n . ' Mois';
        }
        return trim($n . ' ' . $u);
    }

    private static function plan_time_short($p)
    {
        $n = (int)($p['validity'] ?? 0);
        $u = (string)($p['validity_unit'] ?? 'Hrs');
        $map = ['Mins' => 'min', 'Hrs' => 'h', 'Days' => 'j', 'Months' => 'mois'];
        return $n . ($map[$u] ?? $u);
    }

    private static function plan_data_label($p)
    {
        $limit = (int)($p['data_limit'] ?? 0);
        if ($limit < 1) {
            return 'Illimité';
        }
        return $limit . ' ' . (string)($p['data_unit'] ?? 'MB');
    }

    private static function plan_best_id($plans)
    {
        $bestId = 0;
        $bestScore = null;
        foreach ($plans as $p) {
            $hours = self::plan_hours($p);
            $price = (float)($p['price'] ?? 0);
            $limited = (int)($p['data_limit'] ?? 0) > 0 ? 1 : 0;
            $score = ($limited * 1000000) + ($price / max(0.02, $hours));
            if ($bestScore === null || $score < $bestScore) {
                $bestScore = $score;
                $bestId = (int)$p['id'];
            }
        }
        return $bestId;
    }

    private static function plan_lines($p)
    {
        $units = ['Mins' => 'min', 'Hrs' => 'heures', 'Days' => 'jours', 'Months' => 'mois'];
        $u = (string)($p['validity_unit'] ?? 'Hrs');
        $lines = [];
        $dur = trim((string)($p['validity'] ?? '') . ' ' . ($units[$u] ?? $u));
        if ($dur !== '') {
            $lines[] = 'Durée : ' . $dur;
        }
        $down = (int)($p['bw_down'] ?? 0);
        $up = (int)($p['bw_up'] ?? 0);
        if ($down > 0 || $up > 0) {
            $lines[] = 'Débit : ' . $down . ' Mbps ↓  ·  ' . $up . ' Mbps ↑';
        }
        $dataLimit = (int)($p['data_limit'] ?? 0);
        if ($dataLimit > 0) {
            $lines[] = 'Volume : ' . $dataLimit . ' ' . (string)($p['data_unit'] ?? 'MB');
        } else {
            $lines[] = 'Volume illimité';
        }
        $shared = (int)($p['shared_users'] ?? 0);
        if ($shared > 1) {
            $lines[] = $shared . ' sessions simultanées';
        }
        $burst = trim((string)($p['burst_label'] ?? ''));
        if ($burst !== '') {
            $lines[] = 'Burst : ' . $burst;
        }
        return $lines;
    }

    private static function plan_detail($p)
    {
        return implode(' · ', self::plan_lines($p));
    }

    private static function bundle_card($href, $dur, $title, $meta, $foot, $badge = false, $cta = 'PAYEZ')
    {
        $payClass = (strtoupper((string)$cta) === 'PAYEZ') ? 'wo-bundle-pay' : 'wo-bundle-pay wo-bundle-free';
        $out = "\t\t<a href=\"" . $href . "\" class=\"wo-bundle\">\n"
            . "\t\t\t<div class=\"wo-bundle-row\">\n"
            . "\t\t\t\t<div class=\"wo-bundle-copy\">\n"
            . "\t\t\t\t\t<div class=\"wo-bundle-dur\">" . self::h($dur) . "</div>\n"
            . "\t\t\t\t\t<div class=\"wo-bundle-title\">" . self::h($title) . "</div>\n"
            . "\t\t\t\t\t<div class=\"wo-bundle-meta\">" . self::h($meta) . "</div>\n";
        if ($badge) {
            $out .= "\t\t\t\t\t<div class=\"wo-bundle-badge\">MEILLEUR QUALITÉ/PRIX</div>\n";
        }
        $out .= "\t\t\t\t</div>\n"
            . "\t\t\t\t<span class=\"" . $payClass . "\">" . self::h($cta) . "</span>\n"
            . "\t\t\t</div>\n"
            . "\t\t\t<div class=\"wo-bundle-foot\">" . self::h($foot) . "</div>\n"
            . "\t\t</a>\n"
            . "\t\t<!-- end tarifs-->\n";
        return $out;
    }

    private static function plan_cards($router, $cfg)
    {
        $plans = self::hotspot_plans($router);
        if (!$plans) {
            if ((int)($router['trial_on'] ?? 0) === 1) {
                return '';
            }
            return self::bundle_card(
                self::h(self::checkout_href($cfg)),
                'Hotspot',
                self::h($cfg['buy_label'] ?? '') !== '' ? $cfg['buy_label'] : 'Acheter du Wi-Fi',
                'Data: Illimité | Time: —',
                'Aucun forfait pour le moment',
                false
            );
        }
        $best = self::plan_best_id($plans);
        $out = '';
        foreach ($plans as $p) {
            $dur = self::plan_duration_fr($p);
            $data = self::plan_data_label($p);
            $time = self::plan_time_short($p);
            $price = (int)$p['price'];
            $title = ($price < 1 ? 'Gratuit' : ($price . ' Fcfa')) . ' - ' . $data;
            $meta = 'Data: ' . $data . ' | Time: ' . $time;
            $speed = ((int)($p['bw_down'] ?? 0) > 0 || (int)($p['bw_up'] ?? 0) > 0) ? 'Haut débit' : 'Haut débit';
            $conn = ((int)($p['data_limit'] ?? 0) < 1) ? 'Connexion illimitée' : $data;
            $foot = $dur . ' - ' . $conn . ' - ' . $speed;
            $cta = $price < 1 ? 'GRATUIT' : 'PAYEZ';
            $out .= "\t\t<!-- Tarifs start-->\n" . self::bundle_card(
                self::h(self::checkout_href($cfg, (int)$p['id'])),
                $dur,
                $title,
                $meta,
                $foot,
                (int)$p['id'] === $best && $price > 0,
                $cta
            );
        }
        return $out;
    }

    private static function contact_script($url, $cfg = [])
    {
        $u = json_encode($url, JSON_UNESCAPED_SLASHES);
        $accent = self::h($cfg['color'] ?? '#f05018');
        $navy = self::h($cfg['navy'] ?? '#002858');
        return '<style>.wo-hs-msg{display:none;position:fixed;inset:0;z-index:120;background:rgba(0,40,88,.45);place-items:center;padding:16px}.wo-hs-msg.on{display:grid}.wo-hs-card{width:min(380px,100%);background:#fff;border-radius:22px;padding:16px 18px 20px;text-align:center;font-family:Inter,sans-serif;color:' . $navy . '}.wo-hs-card h2{font-size:22px;margin:6px 0;color:' . $navy . '}.wo-hs-card p{color:#64748b;font-size:13px;line-height:1.45;margin:0 0 12px}.wo-hs-card a{color:' . $accent . ';font-weight:700;text-decoration:none;font-size:13px}#contactForm textarea.form-control{min-height:120px}#woContactBtn{touch-action:manipulation;-webkit-tap-highlight-color:transparent}</style>'
            . '<div class="wo-hs-msg" id="woHsMsg"><div class="wo-hs-card"><h2 id="woHsT"></h2><p id="woHsP"></p><a href="#" id="woHsC">Fermer →</a></div></div>'
            . '<script>(function(){function box(t,p){var m=document.getElementById("woHsMsg");if(!m){alert(t+"\\n"+p);return;}var h=document.getElementById("woHsT");var x=document.getElementById("woHsP");if(h)h.textContent=t;if(x)x.textContent=p;m.classList.add("on");var c=document.getElementById("woHsC");if(c)c.onclick=function(e){e.preventDefault();m.classList.remove("on");};}'
            . 'var f=document.getElementById("contactForm");if(!f||f.getAttribute("data-wo-contact")==="1")return;f.setAttribute("data-wo-contact","1");'
            . 'f.setAttribute("action","#");f.setAttribute("novalidate","novalidate");'
            . 'var btn=document.getElementById("woContactBtn")||f.querySelector("button, [type=submit]");'
            . 'if(btn){btn.setAttribute("type","button");btn.id=btn.id||"woContactBtn";}'
            . 'var sending=false;function val(){return{name:((document.getElementById("name")||{}).value||"").trim(),phone:((document.getElementById("email")||{}).value||"").trim(),msg:((document.getElementById("message")||{}).value||"").trim()};}'
            . 'function send(ev){if(ev){ev.preventDefault();ev.stopPropagation();if(ev.stopImmediatePropagation)ev.stopImmediatePropagation();}if(sending)return false;try{if(document.activeElement&&document.activeElement.blur)document.activeElement.blur();}catch(e){}'
            . 'var v=val();if(!v.name){box("Message incomplet","Indiquez votre nom.");return false;}if(!v.phone){box("Message incomplet","Indiquez votre numéro WhatsApp ou téléphone.");return false;}if(v.msg.length<3){box("Message incomplet","Écrivez votre message (au moins 3 caractères).");return false;}'
            . 'sending=true;if(btn){btn.disabled=true;btn.textContent="Envoi…";}'
            . 'var fd=new FormData();fd.append("ajax","1");fd.append("name",v.name);fd.append("phone",v.phone);fd.append("email",v.phone);fd.append("message",v.msg);fd.append("mesage",v.msg);'
            . 'fetch(' . $u . ',{method:"POST",body:fd,credentials:"omit",headers:{Accept:"application/json"}}).then(function(r){var ct=(r.headers.get("content-type")||"");if(ct.indexOf("json")>=0)return r.json();return r.text().then(function(t){if(/cloudflare|just a moment|attention required/i.test(t||""))return {ok:false,error:"Réseau bloqué. Réessayez depuis le Wi-Fi de la zone."};return {ok:true};});}).then(function(d){sending=false;if(btn){btn.disabled=false;btn.textContent="Envoyer";}if(d&&d.ok){box("Message envoyé","Nous avons bien reçu votre demande. Nous vous répondrons par appel ou WhatsApp.");f.reset();}else if(d&&d.wa){location.href=d.wa;}else{box("Envoi impossible",(d&&d.error)?d.error:"Le message n’a pas pu partir. Vérifiez votre connexion Wi‑Fi et réessayez.");}}).catch(function(){sending=false;try{f.setAttribute("action",' . $u . ');f.setAttribute("method","POST");var x=document.createElement("input");x.type="hidden";x.name="ajax";x.value="0";f.appendChild(x);f.submit();}catch(e){if(btn){btn.disabled=false;btn.textContent="Envoyer";}box("Envoi impossible","Le réseau n’a pas répondu. Restez connecté au Wi‑Fi de la zone et réessayez.");}});return false;}'
            . 'f.addEventListener("submit",send,true);'
            . 'if(btn){btn.addEventListener("click",send,true);}'
            . '})();</script>';
    }

    private static function inject_login_guard($html, $file)
    {
        if ($file !== 'login.html') {
            return $html;
        }
        // Soft guard: explain that the ticket POST only works on the Wi-Fi splash,
        // without implying the code itself is invalid.
        $js = '<script>(function(){var host=(location.hostname||"").toLowerCase();var onPortal=host.indexOf("otantik.cm")!==-1;'
            . 'document.addEventListener("submit",function(ev){var f=ev.target;if(!f||(f.name!=="login"&&f.name!=="sendin"))return;'
            . 'if(!onPortal)return;ev.preventDefault();ev.stopPropagation();'
            . 'alert("Votre code n’est pas vérifié ici. Reconnectez-vous au Wi-Fi de la zone : le portail du routeur pourra valider le code. Sur cette page web, utilisez seulement « Acheter ».");},true);})();</script>';
        if (stripos($html, '</body>') !== false) {
            $n = preg_replace('/<\/body>/i', $js . '</body>', $html, 1);
            return is_string($n) ? $n : $html;
        }
        return $html . $js;
    }

    private static function inject_error_fr($html, $file)
    {
        $js = '<style>.wo-login-err{background:#fef2f2!important;border:2px solid #fecaca!important;color:#7f1d1d!important;border-radius:14px;padding:12px 14px;margin:0 15px 12px}.wo-login-err strong{display:block;color:#b91c1c;font-size:14px;margin-bottom:4px}.wo-login-err .wo-err-txt{display:block;font-weight:700;line-height:1.45}</style>'
            . '<script>(function(){function fr(s){s=String(s||"");var t=s.toLowerCase();'
            . 'if(/unknown host|name resolution|dns/.test(t))return "Connexion réussie, mais la page suivante n’a pas pu s’ouvrir. Rouvrez le Wi-Fi ou allez sur Statut. Ce n’est pas un code invalide.";'
            . 'if(/invalid username|invalid password|unknown user|user not found|chap login|no valid user|login failed/.test(t)){'
            . 'if(/t-[0-9a-f]{2}/i.test(t)||/trial/.test(t))return "Essai gratuit indisponible pour le moment. Réessayez, ou achetez un forfait.";'
            . 'return "Code ticket incorrect. Vérifiez le code, ou achetez un forfait.";}'
            . 'if(/already author|already logged|simultaneous|no more sessions/.test(t))return "Ce téléphone est déjà connecté, ou ce code est déjà utilisé. Pour un autre code : Statut → Déconnexion, puis reconnectez-vous. Si le code est faux, vérifiez-le.";'
            . 'if(/uptime limit|session limit|time limit|expired|trial time|no more trial/.test(t))return "Essai ou ticket expiré. Achetez un forfait pour continuer.";'
            . 'if(/traffic limit|quota|limit-bytes|disabled/.test(t))return "Le volume de ce ticket est épuisé. Achetez un nouveau forfait.";'
            . 'if(/radius|nas|server not/.test(t))return "Le serveur d’authentification ne répond pas. Réessayez dans un instant.";'
            . 'if(/not allowed|web-proxy/.test(t))return "Connexion non autorisée pour ce ticket.";'
            . 's=s.replace(/^Hotspot error:\\s*/i,"").replace(/^login error:\\s*/i,"");'
            . 'if(!s||s.indexOf("$(")===0)return "Connexion impossible. Réessayez.";'
            . 'return s;}'
            . 'document.querySelectorAll(".wo-err-txt,[data-wo-err]").forEach(function(el){var t=(el.textContent||"").replace(/^\\s+|\\s+$/g,"");if(!t||t.indexOf("$(")===0)return;el.textContent=fr(t);});})();</script>';
        if (stripos($html, '</body>') !== false) {
            $n = preg_replace('/<\/body>/i', $js . '</body>', $html, 1);
            return is_string($n) ? $n : $html;
        }
        return $html . $js;
    }

    private static function hosted_vars($html, $cfg, $preview = false, $file = '')
    {
        $status = (string)($cfg['status'] ?? 'status.html');
        $login = (string)($cfg['pay'] ?? 'login.html');
        $logout = preg_replace('#login\.html$#', 'logout.html', $login);
        $user = ($preview && $file !== 'status.html') ? 'bleu-lune' : '';
        foreach ([
            '/\$\(if chap-id\).*?\$\(endif\)/s',
            '/\$\(if error\).*?\$\(endif\)/s',
            '/\$\(if popup == \'true\'\).*?\$\(endif\)/s',
            '/\$\(if trial == \'yes\'\).*?\$\(endif\)/s',
            '/\$\(if logged-in == \'yes\'\).*?\$\(endif\)/s',
            '/\$\(if [^)]+\).*?\$\(endif\)/s',
        ] as $rx) {
            $n = preg_replace($rx, '', $html);
            if (is_string($n)) {
                $html = $n;
            }
        }
        $html = str_replace([
            '$(link-login-only)',
            '$(link-login)',
            '$(link-orig)',
            '$(link-orig-esc)',
            '$(link-redirect)',
            '$(link-redirect-esc)',
            '$(link-status)',
            '$(link-logout)',
            '$(username)',
            '$(error)',
            '$(chap-id)',
            '$(chap-challenge)',
            '$(uptime)',
            '$(session-time-left)',
            '$(ip)',
            '$(bytes-in-nice)',
            '$(bytes-out-nice)',
            '$(mac)',
            '$(mac-esc)',
            '$(identity)',
            '$(hostname)',
            '$(trial)',
            ' onSubmit="return doLogin()" ',
            '$(if chap-id) onSubmit="return doLogin()" $(endif)',
        ], [
            '#',
            $login,
            '',
            '',
            $status,
            $status,
            $status,
            $logout,
            $user,
            '',
            '',
            '',
            $preview ? '00:05:00' : '—',
            $preview ? '01:00:00' : '—',
            $preview ? '10.5.50.10' : '—',
            $preview ? '120 Mo' : '—',
            $preview ? '18 Mo' : '—',
            $preview ? 'AA:BB:CC:DD:EE:FF' : '',
            '',
            '',
            '',
            '',
            ' ',
            '',
        ], $html);
        $n = preg_replace('/\$\([a-z0-9_-]+\)/i', '', $html);
        return is_string($n) ? $n : $html;
    }

    private static function preview_vars($html, $cfg)
    {
        return self::hosted_vars($html, $cfg, true, '');
    }

    public static function telegram($token, $chat, $text)
    {
        $token = trim((string)$token);
        $chat = trim((string)$chat);
        if ($token === '' || $chat === '' || $text === '') {
            return [false, 'Telegram non configuré'];
        }
        if (!preg_match('/^\d+:[A-Za-z0-9_-]+$/', $token) || !preg_match('/^-?\d+$/', $chat)) {
            return [false, 'Clés Telegram invalides'];
        }
        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = http_build_query([
            'chat_id' => $chat,
            'text' => $text,
            'disable_web_page_preview' => 'true',
        ]);
        $raw = '';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_CONNECTTIMEOUT => 6,
            ]);
            $raw = (string)curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code < 200 || $code >= 300) {
                return [false, 'Telegram a refusé le message'];
            }
        } else {
            $ctx = stream_context_create(['http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 12,
            ]]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw === false) {
                return [false, 'Impossible de joindre Telegram'];
            }
        }
        $j = json_decode($raw, true);
        if (!empty($j['ok'])) {
            return [true, 'ok'];
        }
        return [false, 'Telegram a refusé le message'];
    }

    public static function md5_js()
    {
        return "function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF);var msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF)}\n"
            . "function rol(num,cnt){return(num<<cnt)|(num>>>(32-cnt))}\n"
            . "function cmn(q,a,b,x,s,t){return safe_add(rol(safe_add(safe_add(a,q),safe_add(x,t)),s),b)}\n"
            . "function ff(a,b,c,d,x,s,t){return cmn((b&c)|((~b)&d),a,b,c,d,x,s,t)}\n"
            . "function gg(a,b,c,d,x,s,t){return cmn((b&d)|(c&(~d)),a,b,c,d,x,s,t)}\n"
            . "function hh(a,b,c,d,x,s,t){return cmn(b^c^d,a,b,c,d,x,s,t)}\n"
            . "function ii(a,b,c,d,x,s,t){return cmn(c^(b|(~d)),a,b,c,d,x,s,t)}\n"
            . "function coreMD5(x){var a=1732584193,b=-271733879,c=-1732584194,d=271733878;for(var i=0;i<x.length;i+=16){var oa=a,ob=b,oc=c,od=d;"
            . "a=ff(a,b,c,d,x[i+0],7,-680876936);d=ff(d,a,b,c,x[i+1],12,-389564586);c=ff(c,d,a,b,x[i+2],17,606105819);b=ff(b,c,d,a,x[i+3],22,-1044525330);"
            . "a=ff(a,b,c,d,x[i+4],7,-176418897);d=ff(d,a,b,c,x[i+5],12,1200080426);c=ff(c,d,a,b,x[i+6],17,-1473231341);b=ff(b,c,d,a,x[i+7],22,-45705983);"
            . "a=ff(a,b,c,d,x[i+8],7,1770035416);d=ff(d,a,b,c,x[i+9],12,-1958414417);c=ff(c,d,a,b,x[i+10],17,-42063);b=ff(b,c,d,a,x[i+11],22,-1990404162);"
            . "a=ff(a,b,c,d,x[i+12],7,1804603682);d=ff(d,a,b,c,x[i+13],12,-40341101);c=ff(c,d,a,b,x[i+14],17,-1502002290);b=ff(b,c,d,a,x[i+15],22,1236535329);"
            . "a=gg(a,b,c,d,x[i+1],5,-165796510);d=gg(d,a,b,c,x[i+6],9,-1069501632);c=gg(c,d,a,b,x[i+11],14,643717713);b=gg(b,c,d,a,x[i+0],20,-373897302);"
            . "a=gg(a,b,c,d,x[i+5],5,-701558691);d=gg(d,a,b,c,x[i+10],9,38016083);c=gg(c,d,a,b,x[i+15],14,-660478335);b=gg(b,c,d,a,x[i+4],20,-405537848);"
            . "a=gg(a,b,c,d,x[i+9],5,568446438);d=gg(d,a,b,c,x[i+14],9,-1019803690);c=gg(c,d,a,b,x[i+3],14,-187363961);b=gg(b,c,d,a,x[i+8],20,1163531501);"
            . "a=gg(a,b,c,d,x[i+13],5,-1444681467);d=gg(d,a,b,c,x[i+2],9,-51403784);c=gg(c,d,a,b,x[i+7],14,1735328473);b=gg(b,c,d,a,x[i+12],20,-1926607734);"
            . "a=hh(a,b,c,d,x[i+5],4,-378558);d=hh(d,a,b,c,x[i+8],11,-2022574463);c=hh(c,d,a,b,x[i+11],16,1839030562);b=hh(b,c,d,a,x[i+14],23,-35309556);"
            . "a=hh(a,b,c,d,x[i+1],4,-1530992060);d=hh(d,a,b,c,x[i+4],11,1272893353);c=hh(c,d,a,b,x[i+7],16,-155497632);b=hh(b,c,d,a,x[i+10],23,-1094730640);"
            . "a=hh(a,b,c,d,x[i+13],4,681279174);d=hh(d,a,b,c,x[i+0],11,-358537222);c=hh(c,d,a,b,x[i+3],16,-722521979);b=hh(b,c,d,a,x[i+6],23,76029189);"
            . "a=hh(a,b,c,d,x[i+9],4,-640364487);d=hh(d,a,b,c,x[i+12],11,-421815835);c=hh(c,d,a,b,x[i+15],16,530742520);b=hh(b,c,d,a,x[i+2],23,-995338651);"
            . "a=ii(a,b,c,d,x[i+0],6,-198630844);d=ii(d,a,b,c,x[i+7],10,1126891415);c=ii(c,d,a,b,x[i+14],15,-1416354905);b=ii(b,c,d,a,x[i+5],21,-57434055);"
            . "a=ii(a,b,c,d,x[i+12],6,1700485571);d=ii(d,a,b,c,x[i+3],10,-1894986606);c=ii(c,d,a,b,x[i+10],15,-1051523);b=ii(b,c,d,a,x[i+1],21,-2054922799);"
            . "a=ii(a,b,c,d,x[i+8],6,1873313359);d=ii(d,a,b,c,x[i+15],10,-30611744);c=ii(c,d,a,b,x[i+6],15,-1560198380);b=ii(b,c,d,a,x[i+13],21,1309151649);"
            . "a=ii(a,b,c,d,x[i+4],6,-145523070);d=ii(d,a,b,c,x[i+11],10,-1120210379);c=ii(c,d,a,b,x[i+2],15,718787259);b=ii(b,c,d,a,x[i+9],21,-343485551);"
            . "a=safe_add(a,oa);b=safe_add(b,ob);c=safe_add(c,oc);d=safe_add(d,od);}return[a,b,c,d]}\n"
            . "function binl2hex(binarray){var hex_tab='0123456789abcdef',str='';for(var i=0;i<binarray.length*4;i++){str+=hex_tab.charAt((binarray[i>>2]>>((i%4)*8+4))&0xF)+hex_tab.charAt((binarray[i>>2]>>((i%4)*8))&0xF)}return str}\n"
            . "function str2binl(str){var nblk=((str.length+8)>>6)+1,blks=new Array(nblk*16);for(var i=0;i<nblk*16;i++)blks[i]=0;for(i=0;i<str.length;i++)blks[i>>2]|=(str.charCodeAt(i)&0xFF)<<((i%4)*8);blks[i>>2]|=0x80<<((i%4)*8);blks[nblk*16-2]=str.length*8;return blks}\n"
            . "function hexMD5(str){return binl2hex(coreMD5(str2binl(str)))}\n";
    }
}
