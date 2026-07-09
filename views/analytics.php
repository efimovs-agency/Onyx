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
    
    <title>Onyx | <?= __('enterprise_analytics') ?? 'Enterprise Analytics' ?></title>
    
    <link rel="stylesheet" href="<?= $stylePath ?>">
    <link rel="stylesheet" href="<?= $responsivePath ?>">
    <script src="<?= $scriptPath ?>" defer></script>
    
    <style>
        html { scrollbar-gutter: stable; }
        html.lenis { height: auto; }
        .lenis.lenis-smooth { scroll-behavior: auto !important; }
        .lenis.lenis-smooth [data-lenis-prevent] { overscroll-behavior: contain; }
        .analytics-link-list::-webkit-scrollbar { width: 4px; }
        .analytics-link-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
        .range-badge { font-size: 10px; color: #6e6e73; font-weight: 500; text-transform: lowercase; margin-left: 6px; letter-spacing: 0; }
        
        /* Локальный фикс сложной шапки аналитики для мобильных устройств */
        @media (max-width: 768px) {
            .analytics-topbar { flex-direction: column !important; align-items: flex-start !important; gap: 20px !important; height: auto !important; padding-bottom: 10px !important; }
            .analytics-topbar-header { width: 100% !important; display: flex !important; justify-content: space-between !important; align-items: center !important; }
            .analytics-actions { width: 100% !important; flex-direction: column !important; align-items: stretch !important; gap: 16px !important; }
            .analytics-actions .sort-dropdown-wrapper { width: 100% !important; max-width: none !important; }
            .analytics-actions .btn-filter-3d { width: 100% !important; max-width: none !important; min-width: 0 !important; }
        }
        @media (min-width: 769px) {
            .analytics-topbar { display: flex; justify-content: space-between; align-items: center; }
            .analytics-topbar-header { display: block; }
            .analytics-actions { display: flex; align-items: center; gap: 16px; }
            .analytics-actions .btn-filter-3d { min-width: 280px; max-width: 320px; }
        }
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

<div id="chartDataStore" style="display: none;" 
     data-timeline="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($timelineData ?? []) : [])) ?>" 
     data-ohlc="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($ohlcData ?? []) : [])) ?>"
     data-os="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($osData ?? []) : [])) ?>" 
     data-source="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($sourceData ?? []) : [])) ?>"
     data-globe="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($globePoints ?? []) : [])) ?>"
     data-hourly="<?= htmlspecialchars(json_encode(isset($currentLink) ? ($hourlyData ?? []) : [])) ?>">
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
    <script>window.location.href = '/login';</script>
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
                    <a href="/" class="nav-item">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <?= __('dashboard') ?? 'Дашборд' ?>
                    </a>
                    <a href="/links" class="nav-item">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                        <?= __('my_links') ?? 'Мои ссылки' ?>
                    </a>
                    <a href="/analytics" class="nav-item active">
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

            <header class="topbar analytics-topbar">
                <div class="analytics-topbar-header">
                    <h1 class="page-title">
                        <?= isset($currentLink) ? (__('isolated_analysis') ?? 'Изолированный анализ') : (__('enterprise_analytics') ?? 'Аналитика') ?>
                    </h1>
                    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                </div>
                
                <div class="analytics-actions">
                    <div class="sort-dropdown-wrapper" id="analyticsLinkSelector">
                        <button class="btn-filter-3d sort-trigger" type="button">
                            <div style="display: flex; align-items: center; overflow: hidden; width: 100%;">
                                <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                </svg>
                                <span id="selectedLinkLabel" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; flex: 1; text-align: left;">
                                    <?= isset($currentLink) ? htmlspecialchars($currentLink['title'] ?: (__('untitled') ?? 'Без названия')) : (__('select_route') ?? 'Выбрать маршрут...') ?>
                                </span>
                            </div>
                            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        
                        <div class="sort-options-panel" style="width: 340px; max-width: 100vw; padding: 12px;">
                            <div class="search-wrapper-3d" style="width: 100%; max-width: 100%; margin-bottom: 8px;">
                                <svg class="search-icon-3d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; left: 12px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <input type="text" id="analyticsSearchInput" class="search-input-3d" placeholder="<?= __('search_by_name') ?? 'Поиск по названию...' ?>" spellcheck="false" autocomplete="off" style="padding: 12px 12px 12px 34px !important; font-size: 13px !important; background: rgba(0,0,0,0.3) !important;" onclick="event.stopPropagation()">
                            </div>
                            
                            <div class="analytics-link-list" id="analyticsOptionsList" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                                <div class="sort-option <?= !isset($currentLink) ? 'selected' : '' ?>" onclick="window.location.href='/analytics'">
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="color: #fff; font-size: 13px; font-weight: 600;"><?= __('empty_dashboard') ?? 'Пустой дашборд' ?></span>
                                        <span style="color: #86868b; font-size: 11px;"><?= __('reset_filter') ?? 'Сбросить фильтр' ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($userLinks)): ?>
                                    <?php foreach ($userLinks as $link): ?>
                                        <div class="sort-option <?= (isset($currentLink) && $currentLink['id'] == $link['id']) ? 'selected' : '' ?>" 
                                             data-search="<?= htmlspecialchars(mb_strtolower($link['title'] ?: (__('untitled') ?? 'Без названия'))) ?> <?= htmlspecialchars(mb_strtolower($link['short_code'])) ?>"
                                             onclick="window.location.href='/analytics?link_id=<?= $link['id'] ?>'">
                                            <div style="display: flex; flex-direction: column; gap: 4px; overflow: hidden; width: 100%;">
                                                <span style="color: #fff; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($link['title'] ?: (__('untitled') ?? 'Без названия')) ?></span>
                                                <span style="color: #86868b; font-size: 11px; font-family: monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">/<?= htmlspecialchars($link['short_code']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding: 16px; text-align: center; color: #86868b; font-size: 12px;"><?= __('no_links_yet') ?? 'У вас пока нет ссылок' ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="time-filters">
                        <button class="filter-btn <?= ($_GET['range'] ?? '7d') === '1d' ? 'active' : '' ?>" data-range="1d"><?= __('filter_24h') ?? '24 часа' ?></button>
                        <button class="filter-btn <?= ($_GET['range'] ?? '7d') === '7d' ? 'active' : '' ?>" data-range="7d"><?= __('filter_7d') ?? '7 дней' ?></button>
                        <button class="filter-btn <?= ($_GET['range'] ?? '7d') === '30d' ? 'active' : '' ?>" data-range="30d"><?= __('filter_30d') ?? '30 дней' ?></button>
                        <button class="filter-btn <?= ($_GET['range'] ?? '7d') === '1y' ? 'active' : '' ?>" data-range="1y"><?= __('filter_1y') ?? 'Год' ?></button>
                    </div>
                </div>
            </header>

            <?php if (isset($currentLink)): ?>
                <div class="isolated-focus-banner">
                    <div class="isolated-focus-content">
                        <div class="isolated-focus-label"><?= __('focused_route') ?? 'Маршрут в фокусе' ?></div>
                        <h2 class="isolated-focus-title"><?= htmlspecialchars($currentLink['title'] ?: (__('untitled') ?? 'Без названия')) ?></h2>
                        
                        <div class="isolated-focus-routes">
                            <span class="route-source"><?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/<?= htmlspecialchars($currentLink['short_code']) ?></span>
                            <span class="route-arrow">→</span>
                            <span class="route-target" title="<?= htmlspecialchars($currentLink['original_url']) ?>"><?= htmlspecialchars($currentLink['original_url']) ?></span>
                        </div>
                    </div>
                    
                    <button type="button" class="isolated-focus-btn" onclick="openEditModal(<?= $currentLink['id'] ?>, '<?= htmlspecialchars($currentLink['title'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($currentLink['original_url'], ENT_QUOTES) ?>')">
                        <?= __('configure_btn') ?? 'Настроить' ?>
                    </button>
                </div>
            <?php endif; ?>

            <div class="analytics-grid">
                <div class="widget stat-box" style="padding: 24px;">
                    <span class="stat-label"><?= __('unique_ips') ?? 'Уникальные (IP)' ?> <span class="range-badge"><?= __('range_' . ($_GET['range'] ?? '7d')) ?? 'за 7 дней' ?></span></span>
                    <span class="stat-value" style="color: #fff; font-size: 28px; margin-bottom: 0;">
                        <?= isset($currentLink) ? number_format($uniqueVisitors ?? 0, 0, '.', ' ') : '0' ?>
                    </span> 
                </div>
                <div class="widget stat-box" style="padding: 24px;">
                    <span class="stat-label"><?= __('total_clicks_stats') ?? 'Всего переходов' ?> <span class="range-badge"><?= __('range_' . ($_GET['range'] ?? '7d')) ?? 'за 7 дней' ?></span></span>
                    <span class="stat-value stat-highlight" style="font-size: 28px; margin-bottom: 0;">
                        <?= isset($currentLink) ? number_format($totalClicks ?? 0, 0, '.', ' ') : '0' ?>
                    </span>
                </div>
                <div class="widget stat-box" style="padding: 24px;">
                    <span class="stat-label"><?= __('mobile_traffic') ?? 'Mobile-трафик' ?> <span class="range-badge"><?= __('range_' . ($_GET['range'] ?? '7d')) ?? 'за 7 дней' ?></span></span>
                    <span class="stat-value" style="color: #fff; font-size: 28px; margin-bottom: 0;">
                        <?= isset($currentLink) ? ($mobileShare ?? 0) : '0' ?>%
                    </span>
                </div>
                <div class="widget stat-box" style="padding: 24px; display: flex; flex-direction: column; justify-content: center;">
                    <span class="stat-label"><?= __('top_location') ?? 'Топ-локация (GEO)' ?> <span class="range-badge"><?= __('range_' . ($_GET['range'] ?? '7d')) ?? 'за 7 дней' ?></span></span>
                    <span class="stat-value" style="color: #fff; font-size: 18px; margin-bottom: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;" 
                          title="<?= htmlspecialchars(isset($currentLink) && !empty($topLocation) ? $topLocation : (__('no_data') ?? 'Нет данных')) ?>">
                        <?= htmlspecialchars(isset($currentLink) && !empty($topLocation) ? $topLocation : (__('no_data') ?? 'Нет данных')) ?>
                    </span>
                </div>
            </div>

            <div class="bento-grid-enterprise">
                <div class="widget chart-full" style="background: linear-gradient(145deg, rgba(30, 30, 35, 0.4) 0%, rgba(15, 15, 18, 0.6) 100%); border-radius: 24px; padding: 32px; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h2 class="widget-title" style="margin: 0 0 8px 0; font-size: 24px;"><?= __('traffic_quality') ?? 'Качество трафика (Сравнение)' ?></h2>
                            <p style="color: #86868b; font-size: 13px; margin: 0;"><?= __('traffic_quality_desc') ?? 'Соотношение всех переходов к уникальным посетителям по дням' ?></p>
                        </div>
                    </div>
                    <div id="comparisonChart" style="flex: 1; min-height: 400px; margin-top: 24px;"></div>
                </div>

                <div class="widget chart-full" style="background: linear-gradient(145deg, rgba(30, 30, 35, 0.4) 0%, rgba(15, 15, 18, 0.6) 100%); border-radius: 24px; padding: 32px; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h2 class="widget-title" style="margin: 0 0 8px 0; font-size: 24px;"><?= __('engagement_dynamics') ?? 'Динамика вовлеченности' ?></h2>
                            <p style="color: #86868b; font-size: 13px; margin: 0;"><?= __('engagement_dynamics_desc') ?? 'Процентное распределение кликов по дням за выбранный период' ?></p>
                        </div>
                    </div>
                    <div id="engagementChart" style="flex: 1; min-height: 400px; margin-top: 24px;"></div>
                </div>
                
                <div class="widget chart-full" style="background: linear-gradient(145deg, rgba(30, 30, 35, 0.4) 0%, rgba(15, 15, 18, 0.6) 100%); border-radius: 24px; padding: 32px; border: 1px solid rgba(255,255,255,0.05); box-shadow: inset 0 4px 20px rgba(0,0,0,0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <div>
                            <h2 class="widget-title" style="margin: 0 0 8px 0; font-size: 24px;"><?= __('activity_cycle') ?? 'Цикл активности (Часовые пояса)' ?></h2>
                            <p style="color: #86868b; font-size: 13px; margin: 0;"><?= __('activity_cycle_desc') ?? 'Тепловая карта переходов адаптирована под ваше локальное время' ?></p>
                        </div>
                    </div>
                    <div id="hourlyChart" style="flex: 1; min-height: 350px; margin-top: 24px;"></div>
                </div>
                
                <div class="widget chart-standard">
                    <h2 class="widget-title"><?= __('os_ecosystem') ?? 'Экосистема ОС' ?></h2>
                    <div id="osChart" style="flex: 1; display: flex; align-items: center;"></div>
                </div>
                
                <div class="widget chart-large">
                    <h2 class="widget-title"><?= __('acquisition_vectors') ?? 'Векторы привлечения' ?></h2>
                    <div id="sourceChart" style="flex: 1;"></div>
                </div>
                
                <div class="widget chart-full" style="padding: 0; background: #0a0a0c; position: relative;">
                    <div style="padding: 24px 24px 0 24px; position: absolute; z-index: 10;">
                        <h2 class="widget-title"><?= __('global_routing') ?? 'Глобальная маршрутизация' ?> <span class="premium-badge">Live</span></h2>
                        <p style="color: #86868b; font-size: 13px; margin-top: 4px;"><?= __('global_routing_desc') ?? 'Интерактивная карта гео-локации пользователей' ?></p>
                    </div>
                    <div id="globeViz" class="globe-wrapper"></div>
                </div>
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
<?php endif; ?>

<script>
    window.i18n = {
        select_route_metrics: "<?= __('select_route_metrics') ?? 'Выберите маршрут для вывода метрик' ?>",
        no_transactions: "<?= __('no_transactions') ?? 'Нет транзакций за выбранный период' ?>",
        map_unavailable: "<?= __('map_unavailable') ?? 'Карта недоступна (Выберите маршрут)' ?>",
        map_no_sessions: "<?= __('map_no_sessions') ?? 'Сессии трафика отсутствуют' ?>",
        chart_total: "<?= __('chart_total') ?? 'Всего переходов' ?>",
        chart_unique: "<?= __('chart_unique') ?? 'Уникальные IP' ?>",
        chart_clicks: "<?= __('chart_clicks') ?? 'Клики' ?>",
        copied_to_clipboard: "<?= __('copied_to_clipboard') ?? 'Скопировано в буфер: ' ?>"
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="//unpkg.com/three"></script>
    <script src="//unpkg.com/globe.gl"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.33/bundled/lenis.min.js"></script>
</body>
</html>