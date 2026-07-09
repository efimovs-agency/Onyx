<?php use App\Core\Vite; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Onyx | <?= __('dashboard') ?? 'Дашборд' ?></title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <?= Vite::assets([
        'src/css/style.css',
        'src/css/responsive.css',
        'src/js/script.js'
    ]) ?>

    <style>
        html { scrollbar-gutter: stable; }
    </style>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('onyx_theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light-theme');
            } else {
                document.documentElement.classList.remove('light-theme');
            }
        })();
    </script>

</head>
<?php 
// Серверная проверка
$bodyClass = '';
if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'light') {
    $bodyClass = 'light-theme';
}
?>
<body class="<?= $bodyClass ?>">

<script>
    (function() {
        const savedTheme = localStorage.getItem('onyx_theme') || 'dark';
        if (savedTheme === 'light') { document.body.classList.add('light-theme'); } 
        else { document.body.classList.remove('light-theme'); }
    })();
</script>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="auth-layout">
        <div class="card auth-card">
            <h2><?= __('registration') ?? 'Регистрация' ?></h2>
            
            <?php if (isset($error) && strpos($error, 'успешна') === false): ?>
                <span class="error"><?= htmlspecialchars($error) ?></span>
            <?php elseif (isset($error)): ?>
                <span class="success"><?= htmlspecialchars($error) ?></span>
            <?php endif; ?>
            
            <form action="/" method="POST" novalidate autocomplete="off">
                <div class="honeypot-wrapper" style="opacity: 0; position: absolute; z-index: -1; pointer-events: none;">
                    <input type="text" name="trap_username" autocomplete="username" tabindex="-1">
                    <input type="email" name="trap_email" autocomplete="email" tabindex="-1">
                    <input type="password" name="trap_password" autocomplete="new-password" tabindex="-1">
                </div>
                
                <input type="hidden" name="action" value="register">
                
                <input type="text" inputmode="email" name="email" data-secure-id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="<?= __('email_placeholder') ?? 'Email' ?>" required autocomplete="new-password" spellcheck="false" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');">
                
                <input type="text" name="login" data-secure-id="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="<?= __('login_placeholder') ?? 'Логин' ?>" required autocomplete="off" spellcheck="false" data-lpignore="true">
                
                <input type="password" name="password" data-secure-id="password" class="input-password-secure" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" placeholder="<?= __('password_placeholder') ?? 'Пароль' ?>" required autocomplete="off" spellcheck="false" data-lpignore="true">
                
                <button type="submit"><?= __('register_btn') ?? 'Зарегистрироваться' ?></button>
            </form>
            
            <div class="auth-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
                <a href="/login" class="link-muted"><?= __('already_have_account') ?? 'Уже есть аккаунт?' ?></a>
                
                <div class="custom-select-wrapper" onclick="this.classList.toggle('open')" style="width: auto; background: transparent; border: none; padding: 0;">
                    <div class="custom-select-trigger" style="padding: 6px 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); cursor: pointer;">
                        <span class="lang-trigger-inner" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #a1a1a6; font-weight: 500;">
                            <img src="https://flagcdn.com/w20/ru.png" alt="flag" style="width: 18px; border-radius: 2px;"> Русский
                        </span>
                    </div>
                    <div class="custom-options-panel" onclick="event.stopPropagation()" style="top: calc(100% + 8px); bottom: auto; right: 0; left: auto; transform-origin: top right; min-width: 160px; z-index: 100;">
                        <div class="custom-options-list lang-options-list">
                            <div class="custom-option selected"><img src="https://flagcdn.com/w20/ru.png" alt="flag"> Русский</div>
                            <div class="custom-option"><img src="https://flagcdn.com/w20/us.png" alt="flag"> English</div>
                            <div class="custom-option"><img src="https://flagcdn.com/w20/ua.png" alt="flag"> Українська</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php else: ?>
    <div class="dashboard-layout">
        
        <aside class="sidebar">
           <div class="brand">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="brand-logo-onyx">OX</div>
                <span class="brand-text">Onyx</span>
            </div>
            <button class="mobile-close-btn" onclick="toggleMobileMenu()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
            <nav class="nav-menu">
                <div class="nav-group">
                    <span class="nav-group-title">Core</span>
                    <a href="/" class="nav-item active">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <?= __('dashboard') ?? 'Дашборд' ?>
                    </a>
                    <a href="/links" class="nav-item">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <?= __('my_links') ?? 'Мои ссылки' ?>
                    </a>
                    <a href="/analytics" class="nav-item">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                        <?= __('analytics') ?? 'Аналитика' ?>
                    </a>
                </div>

                <div class="nav-group">
                    <span class="nav-group-title">System</span>
                    <a href="javascript:void(0)" onclick="openSettingsModal()" class="nav-item">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <?= __('settings') ?? 'Настройки' ?>
                    </a>
                    <a href="/contact" class="nav-item">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <?= __('support') ?? 'Поддержка' ?>
                    </a>
                </div>
            </nav>
            
            <button class="btn-sidebar-logout" onclick="openLogoutModal()"><?= __('logout') ?? 'Выйти' ?></button>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-profile-left">
                        <div class="avatar"><?= strtoupper(mb_substr($_SESSION['login'] ?? 'V', 0, 1)) ?></div>
                        <div class="user-info">
                            <span class="username"><?= htmlspecialchars($_SESSION['login'] ?? 'VaxiZe') ?></span>
                            <span class="role">Admin</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="mobile-sidebar-overlay" onclick="toggleMobileMenu()"></div>

            <header class="topbar">
                <h1 class="page-title"><?= __('dashboard') ?? 'Дашборд' ?></h1>
                
                <div class="topbar-actions" style="display: flex; align-items: center; gap: 16px;">
                    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="bento-grid">
                
                <div class="widget create-widget">
                    <h2 class="widget-title" style="margin-bottom: 32px;"><?= __('new_route_title') ?? 'Новый маршрут' ?></h2>
                    
                    <?php if (isset($linkError)): ?>
                        <span class="error"><?= htmlspecialchars($linkError) ?></span>
                    <?php endif; ?>

                    <form action="/" method="POST" novalidate id="createRouteForm">
                        <input type="hidden" name="action" value="shorten">
                        
                        <div class="premium-input-group" style="margin-bottom: 28px;">
                            <label style="color: #a1a1a6; font-weight: 700; font-size: 11px; letter-spacing: 0.8px;"><?= __('target_url_label') ?? 'Целевой URL (Оригинал)' ?></label>
                            <input type="text" id="createOriginalUrl" name="original_url" class="premium-3d-input" placeholder="https://google.com" required autocomplete="off" spellcheck="false">
                        </div>

                        <div class="input-row" style="margin-bottom: 32px;">
                            <div class="premium-input-group flex-1" style="margin-bottom: 0;">
                                <label style="color: #a1a1a6; font-weight: 700; font-size: 11px; letter-spacing: 0.8px;"><?= __('short_code_label') ?? 'Короткий код (Алиас)' ?></label>
                                <input type="text" id="createCustomCode" name="custom_code" class="premium-3d-input" placeholder="fb-promo" required autocomplete="off" spellcheck="false">
                            </div>

                            <div class="premium-input-group flex-1" style="margin-bottom: 0;">
                                <label style="color: #a1a1a6; font-weight: 700; font-size: 11px; letter-spacing: 0.8px;"><?= __('campaign_name_label') ?? 'Название кампании' ?></label>
                                <input type="text" id="createTitle" name="title" class="premium-3d-input" placeholder="<?= __('campaign_name_placeholder') ?? 'Реклама в Telegram' ?>" autocomplete="off" spellcheck="false">
                            </div>
                        </div>
                        
                        <div class="premium-actions">
                            <button type="submit" class="btn-gold-3d" style="width: 100%; padding: 18px; font-size: 14px; font-weight: 700; letter-spacing: 0.5px;"><?= __('create_route_btn') ?? 'Создать защищенный маршрут' ?></button>
                        </div>
                    </form>
                </div>

                <div class="widget stats-widget">
                    <h2 class="widget-title" style="margin-bottom: 4px;"><?= __('summary') ?? 'Сводка' ?></h2>
                    <span style="display: block; font-size: 12px; color: #888888; margin-bottom: 32px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?= __('summary_desc') ?? 'Суммарно по активной базе' ?></span>
                    
                    <?php
                        $activeLinksCount = count($userLinks ?? []);
                        $activeTotalClicks = 0;
                        if (!empty($userLinks)) {
                            foreach ($userLinks as $link) {
                                $activeTotalClicks += (int)($link['clicks_count'] ?? 0);
                            }
                        }
                    ?>
                    
                    <div class="stats-grid">
                        <div class="stat-box">
                            <span class="stat-label"><?= __('total_links') ?? 'Всего ссылок' ?></span>
                            <span class="stat-value"><?= number_format($activeLinksCount, 0, '.', ' ') ?></span>
                        </div>
                        
                        <div class="stat-box">
                            <span class="stat-label"><?= __('total_clicks') ?? 'Всего переходов' ?></span>
                            <span class="stat-value stat-highlight"><?= number_format($activeTotalClicks, 0, '.', ' ') ?></span>
                        </div>
                    </div>
                </div>

                <div class="widget list-widget">
                    <h2 class="widget-title"><?= __('recent_operations') ?? 'Последние операции' ?></h2>
                    
                    <?php if (!empty($userLinks)): ?>
                        <div class="links-container">
                            <?php foreach (array_slice($userLinks, 0, 5) as $link): ?>
                                <div class="link-row">
                                    <div class="link-data">
                                        <span class="list-link-title" style="color: #fff; font-weight: 600; font-size: 15px; margin-bottom: 4px;">
                                            <?= htmlspecialchars($link['title'] ?: __('untitled') ?? 'Без названия') ?>
                                        </span>
                                        <a href="/<?= htmlspecialchars($link['short_code']) ?>" target="_blank" class="link-short" style="font-size: 13px; color: #a1a1a6;">
                                            <?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?>/<?= htmlspecialchars($link['short_code']) ?>
                                        </a>
                                    </div>
                                    
                                    <div class="link-actions">
                                        <div class="click-counter">
                                            <span class="click-count-number" style="font-size: 18px; font-weight: 700; color: #fff;"><?= number_format($link['clicks_count'] ?? 0, 0, '.', ' ') ?></span>
                                            <span class="click-count-label" style="color: #86868b; font-size: 10px;"><?= __('clicks_label') ?? 'кликов' ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align: center; margin-top: 32px;">
                            <a href="/links" style="font-size: 13px; font-weight: 700; padding: 12px 28px; background: rgba(255,255,255,0.05); border-radius: 12px; color: #fff; text-decoration: none; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: all 0.2s;"><?= __('go_to_links_btn') ?? 'Перейти в базу ссылок' ?></a>
                        </div>
                    <?php else: ?>
                        <p class="empty-state" style="color: #86868b;"><?= __('no_active_routes') ?? 'Активных трекинг-маршрутов не обнаружено' ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="logoutModal">
        <div class="card modal-box premium-3d-modal" style="padding: 48px !important; max-width: 440px !important; border-radius: 32px !important;">
            <h2 class="modal-title-left" style="text-align: center; margin-bottom: 16px;"><?= __('confirmation') ?? 'Подтверждение' ?></h2>
            <p style="text-align: center; color: #86868b; font-size: 16px; margin-bottom: 40px;"><?= __('logout_confirm_desc') ?? 'Вы действительно хотите выйти из рабочей среды Onyx?' ?></p>
            <div class="premium-actions" style="margin-top: 0; gap: 16px;">
                <button type="button" class="btn-cancel-3d" onclick="closeLogoutModal()" style="padding: 16px !important;"><?= __('cancel_btn') ?? 'Отмена' ?></button>
                <button type="button" class="btn-danger-3d" onclick="window.location.href='/logout'" style="padding: 16px !important; background: rgba(255, 59, 48, 0.2) !important; color: #ff453a !important; border: 1px solid rgba(255, 59, 48, 0.4) !important;"><?= __('yes_logout_btn') ?? 'Выйти' ?></button>
            </div>
        </div>
    </div>

    <div id="settingsModalWrapper">
    <div class="settings-modal" id="settingsContent" onclick="event.stopPropagation()">
        <div class="settings-header">
            <h2><?= __('settings') ?? 'System Settings' ?></h2>
            <button class="btn-close" aria-label="Закрыть настройки" onclick="closeSettingsModal()"></button>
        </div>
        
        <div class="settings-body">
            <div class="settings-group">
                <span class="settings-group-title"><?= __('appearance_title') ?? 'APPEARANCE' ?></span>
                <div class="settings-list">
                    <div class="settings-item">
                        <div class="settings-info">
                            <h4><?= __('theme_title') ?? 'Interface Theme' ?></h4>
                            <p><?= __('theme_desc') ?? 'Control panel color scheme' ?></p>
                        </div>
                        <div class="theme-control">
                            <button class="theme-btn"><?= __('theme_light') ?? 'Light' ?></button>
                            <button class="theme-btn active"><?= __('theme_dark') ?? 'Dark' ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-group">
                <span class="settings-group-title"><?= __('localization_title') ?? 'LOCALIZATION' ?></span>
                <div class="settings-list">
                    <div class="settings-item">
                        <div class="settings-info">
                            <h4><?= __('language_title') ?? 'Language' ?></h4>
                            <p><?= __('language_desc') ?? 'Interface localization' ?></p>
                        </div>
                        <div class="custom-select-wrapper" id="languageSelect" onclick="this.classList.toggle('open')">
                            <div class="custom-select-trigger">
                                <span class="lang-trigger-inner">
                                    <img src="https://flagcdn.com/w20/us.png" alt="flag"> English
                                </span>
                            </div>
                            <div class="custom-options-panel" onclick="event.stopPropagation()">
                                <div class="custom-options-list lang-options-list">
                                    <div class="custom-option"><img src="https://flagcdn.com/w20/ru.png" alt="flag"> Русский</div>
                                    <div class="custom-option selected"><img src="https://flagcdn.com/w20/us.png" alt="flag"> English</div>
                                    <div class="custom-option"><img src="https://flagcdn.com/w20/ua.png" alt="flag"> Українська</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php endif; ?>
</body>
</html>