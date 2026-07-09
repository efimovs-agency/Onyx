<?php use App\Core\Vite; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Onyx | <?= __('my_links') ?? 'База маршрутов' ?></title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.33/bundled/lenis.min.js"></script>
    
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/responsive.css">
    <script src="/js/script.js" defer></script>

    <style>
        html { scrollbar-gutter: stable; }
        html.lenis { height: auto; }
        .lenis.lenis-smooth { scroll-behavior: auto !important; }
        .lenis.lenis-smooth [data-lenis-prevent] { overscroll-behavior: contain; }
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
                <a href="/links" class="nav-item active">
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
            <h1 class="page-title"><?= __('my_links') ?? 'Мои ссылки' ?></h1>
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

        <div class="links-toolbar">
            <div class="search-wrapper-3d" style="flex: 1; max-width: 320px;">
                <svg class="search-icon-3d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="linkSearch" class="search-input-3d" placeholder="<?= __('search_route_placeholder') ?? 'Поиск маршрута...' ?>" spellcheck="false" autocomplete="off">
            </div>
            
            <div class="toolbar-actions-right">
                
                <div class="sort-dropdown-wrapper" id="sortDropdown">
                    <button class="btn-filter-3d sort-trigger" type="button">
                        <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="6" x2="13" y2="6"></line>
                            <line x1="4" y1="12" x2="9" y2="12"></line>
                            <line x1="4" y1="18" x2="6" y2="18"></line>
                            <polyline points="16 14 19 17 22 14"></polyline>
                            <line x1="19" y1="7" x2="19" y2="17"></line>
                        </svg>
                        <span id="sortLabel"><?= __('sort_newest') ?? 'Сначала новые' ?></span>
                        <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="sort-options-panel">
                        <div class="sort-option selected" data-sort="date-desc"><?= __('sort_newest') ?? 'Сначала новые' ?></div>
                        <div class="sort-option" data-sort="date-asc"><?= __('sort_oldest') ?? 'Сначала старые' ?></div>
                        <div class="sort-option" data-sort="clicks-desc"><?= __('sort_most_active') ?? 'Самые активные' ?></div>
                        <div class="sort-option" data-sort="clicks-asc"><?= __('sort_least_active') ?? 'Менее активные' ?></div>
                        <div class="sort-option" data-sort="alpha-asc"><?= __('sort_alpha_asc') ?? 'По алфавиту (А - Я)' ?></div>
                        <div class="sort-option" data-sort="alpha-desc"><?= __('sort_alpha_desc') ?? 'По алфавиту (Я - А)' ?></div>
                    </div>
                </div>

                <button class="btn-secondary-3d" id="exportBtn" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span><?= __('export_csv') ?? 'Экспорт CSV' ?></span>
                </button>
                
            </div>
        </div>

        <div class="links-3d-grid" id="linksGridContainer">
            <?php if (!empty($userLinks)): ?>
                <?php foreach ($userLinks as $link): ?>
                    <div class="link-3d-card" 
                         data-id="<?= htmlspecialchars($link['id'] ?? 0) ?>"
                         data-clicks="<?= htmlspecialchars($link['clicks_count'] ?? 0) ?>"
                         data-title="<?= htmlspecialchars(mb_strtolower($link['title'] ?: __('untitled') ?? 'без названия')) ?>"
                         data-url="<?= htmlspecialchars(mb_strtolower($link['short_code'] ?? '')) ?>">
                        
                        <div class="card-3d-header">
                            <div class="card-3d-status">
                                <div class="status-dot"></div> ACTIVE
                            </div>
                            <button class="copy-trigger-3d" onclick="copyToClipboard('<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/<?= htmlspecialchars($link['short_code']) ?>')" title="<?= __('copy_btn') ?? 'Копировать' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>

                        <div class="card-3d-body">
                            <h3 class="link-title-3d">
                                <?= htmlspecialchars($link['title'] ?: __('untitled') ?? 'Без названия') ?>
                            </h3>
                            
                            <a href="/<?= htmlspecialchars($link['short_code']) ?>" target="_blank" class="short-url-3d">
                                <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/<?= htmlspecialchars($link['short_code']) ?>
                            </a>
                            <div class="original-url-3d-wrapper">
                                <div class="original-url-3d" title="<?= htmlspecialchars($link['original_url']) ?>">
                                    <?= htmlspecialchars($link['original_url']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-3d-footer">
                            <button type="button" class="btn-analytics-3d" onclick="window.location.href='/analytics?link_id=<?= $link['id'] ?>'">
                                <?= __('analyze_btn') ?? 'Анализ' ?>
                            </button>
                            
                            <div class="card-actions-right">
                                <button type="button" class="btn-edit-3d" onclick="openEditModal(<?= $link['id'] ?>, '<?= htmlspecialchars($link['title'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($link['original_url'], ENT_QUOTES) ?>')">
                                    <?= __('configure_btn') ?? 'Настроить' ?>
                                </button>
                                
                                <form action="/links" method="POST" class="delete-form-3d">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                                    <button type="submit" class="btn-delete-3d" title="<?= __('delete_route_btn') ?? 'Удалить маршрут' ?>"><?= __('delete_btn') ?? 'Удалить' ?></button>
                                </form>
                            </div>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="link-3d-card empty-state-card" style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: center; min-height: 240px;">
                    <p class="empty-state"><?= __('no_active_routes') ?? 'В базе нет активных маршрутов.' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="modal-overlay" id="editModal">
    <div class="card modal-box premium-3d-modal">
        <h2 class="modal-title-left"><?= __('configure_route_title') ?? 'Настройка маршрута' ?></h2>
        <form action="/links" method="POST" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="link_id" id="editLinkId">
            
            <div class="premium-input-group">
                <label><?= __('campaign_name_label') ?? 'Название кампании' ?></label>
                <input type="text" name="title" id="editTitle" class="premium-3d-input" autocomplete="off" spellcheck="false" placeholder="<?= __('untitled') ?? 'Без названия' ?>">
            </div>
            
            <div class="premium-input-group">
                <label><?= __('target_url_label') ?? 'Целевой URL (Оригинал)' ?></label>
                <input type="text" name="original_url" id="editOriginalUrl" class="premium-3d-input" autocomplete="off" spellcheck="false" placeholder="https://...">
            </div>
            
            <div class="premium-actions">
                <button type="button" class="btn-cancel-3d" onclick="closeEditModal()"><?= __('cancel_btn') ?? 'Отмена' ?></button>
                <button type="submit" class="btn-gold-3d"><?= __('save_btn') ?? 'Сохранить' ?></button>
            </div>
        </form>
    </div>
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
    document.addEventListener('DOMContentLoaded', () => {
        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const urlInput = document.getElementById('editOriginalUrl');
                if (urlInput.value.trim() === '') {
                    e.preventDefault(); 
                    urlInput.classList.add('input-error');
                    urlInput.placeholder = '<?= __('error_empty_url') ?? "ОШИБКА: Вставьте целевой URL!" ?>';
                    urlInput.style.transform = 'translateX(-10px)';
                    setTimeout(() => urlInput.style.transform = 'translateX(10px)', 40);
                    setTimeout(() => urlInput.style.transform = 'translateX(-10px)', 80);
                    setTimeout(() => urlInput.style.transform = 'translateX(10px)', 120);
                    setTimeout(() => urlInput.style.transform = 'translateX(0)', 160);
                }
            });
            document.getElementById('editOriginalUrl').addEventListener('input', function() {
                this.classList.remove('input-error');
                this.placeholder = 'https://...';
            });
        }
    });
</script>
</body>
</html>