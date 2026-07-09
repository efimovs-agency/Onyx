<?php
$manifestPath = dirname(__DIR__) . '/public/build/.vite/manifest.json';
$manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];

$stylePath = isset($manifest['src/css/style.css']) ? '/build/' . $manifest['src/css/style.css']['file'] : '/css/style.css';
$responsivePath = isset($manifest['src/css/responsive.css']) ? '/build/' . $manifest['src/css/responsive.css']['file'] : '/css/responsive.css';
$scriptPath = isset($manifest['src/js/script.js']) ? '/build/' . $manifest['src/js/script.js']['file'] : '/js/script.js';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Onyx | <?= __('support') ?? 'Поддержка' ?></title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <link rel="stylesheet" href="<?= $stylePath ?>">
    <link rel="stylesheet" href="<?= $responsivePath ?>">
    <script src="<?= $scriptPath ?>" defer></script>

    <style>
        /* Жесткий фикс прыжков макета при появлении скроллбара */
        html { scrollbar-gutter: stable; }
    </style>
</head>
<body>

<script>
    (function() {
        const savedTheme = localStorage.getItem('onyx_theme') || 'dark';
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
        }
    })();
</script>

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
                <a href="/" class="nav-item">
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
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1-1-1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <?= __('settings') ?? 'Настройки' ?>
                </a>
                <a href="/contact" class="nav-item active">
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
            <h1 class="page-title"><?= __('contact_support_title') ?? 'Связь с поддержкой' ?></h1>
            <div class="topbar-actions">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </header>

        <div class="elite-contact-container">
            <div class="elite-card-wrapper">
                <div class="elite-card">
                    <div class="elite-contact-grid">
                        
                        <div class="elite-info">
                            <h2 class="elite-title"><?= __('contact_us_title') ?? 'Свяжитесь с нами' ?></h2>
                            <p class="elite-desc"><?= __('contact_desc') ?? 'Оставьте ваш запрос. Команда Onyx рассмотрит его и ответит по защищенному каналу.' ?></p>
                            
                            <div class="elite-features">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                    </div>
                                    <div class="feature-text">
                                        <strong><?= __('feature_encryption') ?? 'AES-256 Шифрование' ?></strong>
                                        <span><?= __('feature_encryption_desc') ?? 'Защита данных уровня Enterprise' ?></span>
                                    </div>
                                </div>
                                
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg>
                                    </div>
                                    <div class="feature-text">
                                        <strong><?= __('feature_infra') ?? 'Vercel & Supabase' ?></strong>
                                        <span><?= __('feature_infra_desc') ?? 'Топовая инфраструктура' ?></span>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    </div>
                                    <div class="feature-text">
                                        <strong><?= __('feature_sla') ?? 'SLA < 15 минут' ?></strong>
                                        <span><?= __('feature_sla_desc') ?? 'Приоритетный ответ клиенту' ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="elite-status">
                                <div class="status-dot"></div>
                                <span><?= __('support_online') ?? 'Support is Online' ?></span>
                            </div>
                        </div>

                        <div class="elite-form-wrapper">
                            <form id="contactForm" novalidate autocomplete="off">
                                <div style="position: absolute; opacity: 0; top: -9999px; left: -9999px; pointer-events: none;">
                                    <input type="text" name="chrome_fake_name" tabindex="-1" autocomplete="name">
                                    <input type="email" name="chrome_fake_email" tabindex="-1" autocomplete="email">
                                </div>

                                <div class="elite-input-group">
                                    <input type="text" name="name" id="name" class="elite-input" placeholder=" " required autocomplete="new-password" spellcheck="false" data-form-type="other">
                                    <label for="name" class="elite-label"><?= __('your_name_label') ?? 'Ваше Имя' ?></label>
                                </div>
                                
                                <div class="elite-input-group">
                                    <input type="email" name="email" id="email" class="elite-input" placeholder=" " required autocomplete="new-password" spellcheck="false" data-form-type="other">
                                    <label for="email" class="elite-label"><?= __('contact_email_label') ?? 'Email для связи' ?></label>
                                </div>
                                
                                <div class="elite-input-group textarea-group">
                                    <textarea name="message" id="message" class="elite-input" placeholder=" " required autocomplete="off" spellcheck="false"></textarea>
                                    <label for="message" class="elite-label"><?= __('message_text_label') ?? 'Текст обращения' ?></label>
                                </div>
                                
                                <button type="submit" class="elite-submit-btn"><?= __('send_message_btn') ?? 'Отправить сообщение' ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal-overlay" id="logoutModal">
    <div class="card modal-box premium-3d-modal" style="padding: 48px !important; max-width: 440px !important; border-radius: 32px !important;">
        <h2 class="modal-title-left" style="text-align: center; margin-bottom: 16px;"><?= __('confirmation') ?? 'Подтверждение' ?></h2>
        <p style="text-align: center; color: #86868b; font-size: 16px; margin-bottom: 40px;"><?= __('logout_confirm_desc') ?? 'Вы действительно хотите выйти из рабочей среды Onyx' ?></p>
        <div class="premium-actions" style="margin-top: 0; gap: 16px;">
            <button type="button" class="btn-cancel-3d" onclick="closeLogoutModal()" style="padding: 16px !important;"><?= __('cancel_btn') ?? 'Отмена' ?></button>
            <button type="button" class="btn-danger-3d" onclick="window.location.href='/logout'" style="padding: 16px !important; background: rgba(255, 59, 48, 0.2) !important; color: #ff453a !important; border: 1px solid rgba(255, 59, 48, 0.4) !important;"><?= __('yes_logout_btn') ?? 'Выйти' ?></button>
        </div>
    </div>
</div>

<div id="settingsModalWrapper">
    <div class="settings-modal" id="settingsContent" onclick="event.stopPropagation()">
        <div class="settings-header">
            <h2><?= __('settings') ?? 'Настройки системы' ?></h2>
            <button class="btn-close" aria-label="Закрыть настройки" onclick="closeSettingsModal()">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <div class="settings-body">
            <div class="settings-group">
                <h3><?= __('appearance_title') ?? 'Внешний вид' ?></h3>
                <div class="settings-list">
                    <div class="settings-item">
                        <div class="settings-info">
                            <h4><?= __('theme_title') ?? 'Тема интерфейса' ?></h4><p><?= __('theme_desc') ?? 'Цветовая схема панели управления' ?></p>
                        </div>
                        <div class="theme-control">
                            <button class="theme-btn"><?= __('theme_light') ?? 'Светлая' ?></button><button class="theme-btn active"><?= __('theme_dark') ?? 'Темная' ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-group">
                <h3><?= __('localization_title') ?? 'Локализация' ?></h3>
                <div class="settings-list">
                    <div class="settings-item">
                        <div class="settings-info">
                            <h4><?= __('language_title') ?? 'Язык (Language)' ?></h4>
                            <p><?= __('language_desc') ?? 'Локализация интерфейса' ?></p>
                        </div>
                        <div class="custom-select-wrapper" id="languageSelect" onclick="this.classList.toggle('open')">
    <div class="custom-select-trigger">
        <span class="lang-trigger-inner" style="display: flex; align-items: center; gap: 8px;">
            <img src="https://flagcdn.com/w20/ru.png" alt="flag" style="width: 20px; border-radius: 2px;">Русский
        </span>
    </div>
    
    <div class="custom-options-panel" onclick="event.stopPropagation()">
        <div class="custom-options-list" id="langOptionsList">
            <div class="custom-option selected">
                <img src="https://flagcdn.com/w20/ru.png" alt="flag"> Русский
            </div>
            <div class="custom-option">
                <img src="https://flagcdn.com/w20/us.png" alt="flag"> English
            </div>
            <div class="custom-option">
                <img src="https://flagcdn.com/w20/ua.png" alt="flag"> Українська
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Блокируем перезагрузку страницы!

            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerText;
            
            // Меняем текст кнопки на время отправки
            submitBtn.innerText = 'Отправка...';
            submitBtn.disabled = true;

            const formData = new FormData(contactForm);

            fetch('/api/contact', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Успешно: ' + data.message); // Замени на красивый toast, если есть
                    contactForm.reset();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла системная ошибка при отправке.');
            })
            .finally(() => {
                // Возвращаем кнопку в исходное состояние
                submitBtn.innerText = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>
</body>
</html>