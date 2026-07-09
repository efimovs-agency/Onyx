/* ==========================================================================
   ONYX GLOBAL APPLICATION LOGIC
   Core behaviors, state management, and third-party initializations for the
   dashboard, routing, and analytics interfaces.
   ========================================================================== */

   // Global registry for dynamic ApexCharts updates upon theme switching
window.onyxCharts = [];

// Global translation interface for dynamic text rendering
window.t = function(key, fallback) {
    return (window.i18n && window.i18n[key]) ? window.i18n[key] : fallback;
};

/* ==========================================================================
   UTILITY FUNCTIONS
   Shared helper functions required across multiple application modules.
   ========================================================================== */
function shakeElement(element) {
    element.style.transform = 'translateX(-10px)';
    setTimeout(() => element.style.transform = 'translateX(10px)', 40);
    setTimeout(() => element.style.transform = 'translateX(-10px)', 80);
    setTimeout(() => element.style.transform = 'translateX(10px)', 120);
    setTimeout(() => element.style.transform = 'translateX(0)', 160);
}

/* ==========================================================================
   SMOOTH SCROLLING (LENIS)
   Enhances the native scrolling experience across the application to provide
   a premium, fluid interface interaction.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Lenis !== 'undefined') {
        const lenis = new Lenis({ 
            duration: 1.2, 
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), 
            smooth: true 
        });
        
        function raf(time) { 
            lenis.raf(time); 
            requestAnimationFrame(raf); 
        }
        
        requestAnimationFrame(raf);
    }
});

/* ==========================================================================
   MODAL & DROPDOWN CONTROLLERS
   Manages the global state of overlay elements (settings, logout, etc.)
   and ensures predictable closure behavior on exterior document clicks.
   ========================================================================== */
window.openLogoutModal = function() { 
    document.getElementById('logoutModal')?.classList.add('active'); 
};

window.closeLogoutModal = function() { 
    document.getElementById('logoutModal')?.classList.remove('active'); 
};

window.openSettingsModal = function() { 
    const wrapper = document.getElementById('settingsModalWrapper');
    const gearBtn = document.getElementById('gearButton');
    if (wrapper) wrapper.classList.add('active'); 
    if (gearBtn) gearBtn.classList.add('active');
};

window.closeSettingsModal = function() { 
    const wrapper = document.getElementById('settingsModalWrapper');
    const gearBtn = document.getElementById('gearButton');
    
    if (gearBtn) gearBtn.classList.remove('active');
    if (wrapper) wrapper.classList.remove('active');
    
    // Ensure all nested dropdowns within settings are closed when modal closes
    document.querySelectorAll('.custom-select-wrapper').forEach(el => el.classList.remove('open'));
};

document.addEventListener('click', function(event) {
    const logout = document.getElementById('logoutModal');
    const wrapper = document.getElementById('settingsModalWrapper');
    const modal = document.getElementById('settingsContent');
    const editModal = document.getElementById('editModal'); 
    
    if (event.target === logout) closeLogoutModal();
    if (event.target === editModal) closeEditModal();
    if (event.target === wrapper && modal && !modal.contains(event.target)) closeSettingsModal();
    
    // Global closure for active dropdowns (Language, Sorting) upon outside click
    document.querySelectorAll('.custom-select-wrapper, .sort-dropdown-wrapper').forEach(dropdownWrapper => {
        if (!dropdownWrapper.contains(event.target)) {
            dropdownWrapper.classList.remove('open');
        }
    });
});

/* ==========================================================================
   THEME MANAGEMENT (LIGHT / DARK MODE)
   Handles theme toggling, persistence via localStorage, and real-time
   synchronization with rendered ApexCharts instances.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const themeButtons = document.querySelectorAll('.theme-btn');
    const body = document.body;
    const html = document.documentElement;

    const savedTheme = localStorage.getItem('onyx_theme') || 'dark';
    
    // Initialization fallback ensuring state synchronization
    if (savedTheme === 'light') {
        html.classList.add('light-theme');
        body.classList.add('light-theme');
    }

    themeButtons.forEach(btn => {
        btn.classList.remove('active');
        if (
            (savedTheme === 'light' && (btn.textContent.includes('Светлая') || btn.textContent.includes('Light') || btn.textContent.includes('Світла'))) ||
            (savedTheme === 'dark' && (btn.textContent.includes('Темная') || btn.textContent.includes('Dark') || btn.textContent.includes('Темна')))
        ) {
            btn.classList.add('active');
        }
    });

    themeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.classList.contains('active')) return;

            document.querySelectorAll('.theme-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const isLight = this.textContent.includes('Светлая') || this.textContent.includes('Light') || this.textContent.includes('Світла');
            const newTheme = isLight ? 'light' : 'dark';

            if (isLight) {
                html.classList.add('light-theme');
                body.classList.add('light-theme');
                localStorage.setItem('onyx_theme', 'light');
            } else {
                html.classList.remove('light-theme');
                body.classList.remove('light-theme');
                localStorage.setItem('onyx_theme', 'dark');
            }
            
            // Sync theme changes natively to active charts without full re-render
            if (window.onyxCharts) {
                window.onyxCharts.forEach(chart => {
                    chart.updateOptions({ theme: { mode: newTheme } });
                });
            }
        });
    });
});

/* ==========================================================================
   FORM VALIDATION
   Client-side validation for route creation forms providing immediate
   visual feedback before payload submission.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const createForm = document.getElementById('createRouteForm');
    
    if (createForm) {
        const urlInput = document.getElementById('createOriginalUrl');
        const aliasInput = document.getElementById('createCustomCode');
        
        createForm.addEventListener('submit', function(e) {
            let hasError = false;

            if (urlInput && urlInput.value.trim() === '') {
                urlInput.classList.add('input-error');
                shakeElement(urlInput);
                hasError = true;
            }
            
            if (aliasInput && aliasInput.value.trim() === '') {
                aliasInput.classList.add('input-error');
                shakeElement(aliasInput);
                hasError = true;
            }

            if (hasError) e.preventDefault();
        });

        if (urlInput) {
            urlInput.addEventListener('input', function() { 
                this.classList.remove('input-error'); 
            });
        }
        
        if (aliasInput) {
            aliasInput.addEventListener('input', function() { 
                this.classList.remove('input-error'); 
            });
        }
    }
});

/* ==========================================================================
   ANALYTICS ENGINE (CHARTS, GLOBE, FILTERS)
   Parses dataset attributes and configures visualization components using
   ApexCharts and Three-Globe implementations.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const dataStore = document.getElementById('chartDataStore');
    if (!dataStore) return;

    const timelineData = JSON.parse(dataStore.getAttribute('data-timeline') || '[]');
    const osData = JSON.parse(dataStore.getAttribute('data-os') || '[]');
    const sourceData = JSON.parse(dataStore.getAttribute('data-source') || '[]');
    const globeData = JSON.parse(dataStore.getAttribute('data-globe') || '[]');
    const hourlyDataUTC = JSON.parse(dataStore.getAttribute('data-hourly') || '[]');
    
    const fontSettings = { colors: '#86868b', fontFamily: 'Montserrat, sans-serif', fontWeight: 500 };
    const savedTheme = localStorage.getItem('onyx_theme') || 'dark';

    const renderFallback = (selector) => {
        const el = document.querySelector(selector);
        if (el) {
            el.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; min-height:300px;">${window.t('no_transactions', 'Нет транзакций за выбранный период')}</div>`;
        }
    };

    const urlParams = new URLSearchParams(window.location.search);
    const hasLinkId = urlParams.has('link_id');

    // --------------------------------------------------------------------------
    // Traffic Comparison Chart
    // --------------------------------------------------------------------------
    try {
        const compChartDiv = document.querySelector("#comparisonChart");
        if (compChartDiv) {
            if (!hasLinkId) {
                compChartDiv.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; min-height:300px;">${window.t('select_route_metrics', 'Выберите маршрут для вывода метрик')}</div>`;
            } else if (timelineData.length > 0) {
                const categories = timelineData.map(item => {
                    const date = new Date(item.click_date);
                    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
                });
                const totalClicksData = timelineData.map(item => parseInt(item.click_count || 0));
                const uniqueIpsData = timelineData.map(item => parseInt(item.unique_count || 0));

                const compChart = new ApexCharts(compChartDiv, {
                    chart: { 
                        type: 'bar', 
                        height: '100%', 
                        toolbar: { show: false, autoSelected: 'pan' }, 
                        zoom: { enabled: true, type: 'x' }, 
                        backgroundColor: 'transparent', 
                        parentHeightOffset: 0 
                    },
                    theme: { mode: savedTheme },
                    colors: ['#0A84FF', '#e5c158'], 
                    plotOptions: { 
                        bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } 
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    series: [
                        { name: window.t('chart_total', 'Всего переходов'), data: totalClicksData },
                        { name: window.t('chart_unique', 'Уникальные IP'), data: uniqueIpsData }
                    ],
                    xaxis: { 
                        categories: categories, 
                        labels: { style: fontSettings, rotate: 0, hideOverlappingLabels: true }, 
                        axisBorder: { show: false }, 
                        axisTicks: { show: false } 
                    },
                    yaxis: { labels: { style: fontSettings } },
                    fill: { opacity: 1 },
                    grid: { 
                        borderColor: 'rgba(255,255,255,0.05)', 
                        strokeDashArray: 4, 
                        padding: { top: 0, right: 25, bottom: 0, left: 25 } 
                    },
                    legend: { 
                        position: 'top', 
                        horizontalAlign: 'right', 
                        labels: { colors: '#fff' } 
                    }
                });
                compChart.render();
                window.onyxCharts.push(compChart);
            } else { 
                renderFallback("#comparisonChart"); 
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Engagement Volume Chart
    // --------------------------------------------------------------------------
    try {
        const engageChartDiv = document.querySelector("#engagementChart");
        if (engageChartDiv) {
            if (!hasLinkId) {
                engageChartDiv.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; min-height:300px;">${window.t('select_route_metrics', 'Выберите маршрут для вывода метрик')}</div>`;
            } else if (timelineData.length > 0) {
                const categories = timelineData.map(item => {
                    const date = new Date(item.click_date);
                    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
                });
                
                const volumeData = timelineData.map(item => parseInt(item.click_count || 0));
                const totalPeriodClicks = volumeData.reduce((a, b) => a + b, 0);

                const engageChart = new ApexCharts(engageChartDiv, {
                    chart: { 
                        type: 'bar', 
                        height: '100%', 
                        toolbar: { show: false, autoSelected: 'pan' }, 
                        zoom: { enabled: true, type: 'x' }, 
                        backgroundColor: 'transparent', 
                        parentHeightOffset: 0 
                    },
                    theme: { mode: savedTheme },
                    series: [{ name: window.t('chart_clicks', 'Клики'), data: volumeData }],
                    colors: ['#34a88f'],
                    plotOptions: { 
                        bar: { borderRadius: 4, columnWidth: '40%', dataLabels: { position: 'top' } } 
                    },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -25, 
                        style: { fontSize: '14px', fontFamily: 'Montserrat', fontWeight: 700, colors: ['#fff'] },
                        formatter: function (val) { 
                            return totalPeriodClicks === 0 ? "0%" : Math.round((val / totalPeriodClicks) * 100) + "%"; 
                        }
                    },
                    grid: { 
                        show: false, 
                        padding: { top: 0, right: 25, bottom: 0, left: 25 } 
                    }, 
                    xaxis: { 
                        categories: categories, 
                        labels: { style: fontSettings, rotate: 0, hideOverlappingLabels: true }, 
                        axisBorder: { show: true, color: 'rgba(255,255,255,0.1)' }, 
                        axisTicks: { show: false } 
                    },
                    yaxis: { show: false }, 
                    tooltip: { theme: 'dark' }
                });
                engageChart.render();
                window.onyxCharts.push(engageChart);
            } else { 
                renderFallback("#engagementChart"); 
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Hourly Activity Cycle Chart
    // --------------------------------------------------------------------------
    try {
        const hourlyChartDiv = document.querySelector("#hourlyChart");
        if (hourlyChartDiv) {
            if (!hasLinkId) {
                hourlyChartDiv.innerHTML = `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; min-height:300px;">${window.t('select_route_metrics', 'Выберите маршрут для вывода метрик')}</div>`;
            } else if (hourlyDataUTC.length === 24 && hourlyDataUTC.some(val => val > 0)) {
                
                const offsetHours = -(new Date().getTimezoneOffset() / 60);
                const localHourlyData = new Array(24).fill(0);
                
                for (let i = 0; i < 24; i++) {
                    let localHour = Math.floor(i + offsetHours) % 24;
                    if (localHour < 0) localHour += 24; 
                    localHourlyData[localHour] += hourlyDataUTC[i];
                }

                const hourLabels = Array.from({length: 24}, (_, i) => `${i}:00`);

                const hourlyChart = new ApexCharts(hourlyChartDiv, {
                    chart: { 
                        type: 'area', 
                        height: '100%', 
                        toolbar: { show: false, autoSelected: 'pan' }, 
                        zoom: { enabled: true, type: 'x' }, 
                        backgroundColor: 'transparent', 
                        parentHeightOffset: 0 
                    },
                    theme: { mode: savedTheme },
                    colors: ['#ff3b30'], 
                    fill: { 
                        type: 'gradient', 
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } 
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 }, 
                    series: [{ name: window.t('chart_clicks', 'Клики'), data: localHourlyData }],
                    xaxis: { 
                        categories: hourLabels, 
                        labels: { 
                            style: fontSettings, 
                            rotate: 0, 
                            formatter: function (val) { 
                                if (!val) return ''; 
                                let hour = parseInt(val.split(':')[0]); 
                                return (hour % 4 === 0) ? val : ''; 
                            } 
                        }, 
                        axisBorder: { show: false }, 
                        axisTicks: { show: false }
                    },
                    yaxis: { show: false },
                    grid: { 
                        borderColor: 'rgba(255,255,255,0.05)', 
                        strokeDashArray: 4, 
                        padding: { top: 0, right: 25, bottom: 0, left: 25 } 
                    },
                    tooltip: { theme: 'dark' }
                });
                hourlyChart.render();
                window.onyxCharts.push(hourlyChart);
            } else { 
                renderFallback("#hourlyChart"); 
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Operating System Ecosystem Chart
    // --------------------------------------------------------------------------
    try {
        const osChartDiv = document.querySelector("#osChart");
        if (osChartDiv) {
            if (osData.length > 0) {
                const osChart = new ApexCharts(osChartDiv, {
                    chart: { type: 'donut', width: '100%', height: 320, backgroundColor: 'transparent' }, 
                    theme: { mode: savedTheme },
                    stroke: { show: true, colors: ['#121214'], width: 3 }, 
                    colors: ['#e5c158', '#ffffff', '#32d74b', '#ff3b30', '#86868b'], 
                    labels: osData.map(item => item.os_name), 
                    series: osData.map(item => parseInt(item.click_count)),
                    legend: { 
                        position: 'bottom', 
                        labels: fontSettings, 
                        itemMargin: { horizontal: 10, vertical: 5 } 
                    },
                    plotOptions: { 
                        pie: { 
                            donut: { 
                                background: 'transparent', 
                                size: '75%', 
                                labels: { 
                                    show: true, 
                                    name: { show: true }, 
                                    value: { show: true, color: '#fff', fontSize: '24px', fontWeight: 700 } 
                                } 
                            } 
                        } 
                    }
                });
                osChart.render();
                window.onyxCharts.push(osChart);
            } else { 
                renderFallback("#osChart"); 
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Acquisition Vectors Chart
    // --------------------------------------------------------------------------
    try {
        const sourceChartDiv = document.querySelector("#sourceChart");
        if (sourceChartDiv) {
            if (sourceData.length > 0) {
                const sourceChart = new ApexCharts(sourceChartDiv, {
                    chart: { 
                        type: 'bar', 
                        height: '100%', 
                        toolbar: { show: false }, 
                        backgroundColor: 'transparent', 
                        parentHeightOffset: 0 
                    },
                    theme: { mode: savedTheme }, 
                    grid: { show: false, padding: { top: 0, right: 20, bottom: 0, left: 0 } },
                    colors: ['#e5c158'], 
                    plotOptions: { 
                        bar: { horizontal: true, borderRadius: 4, barHeight: '40%', distributed: true } 
                    },
                    dataLabels: { 
                        enabled: true, 
                        textAnchor: 'start', 
                        style: { colors: ['#fff'], fontSize: '12px', fontFamily: 'Montserrat' }, 
                        formatter: function (val, opt) { 
                            return opt.w.globals.labels[opt.dataPointIndex] + ": " + val 
                        }, 
                        offsetX: 0 
                    },
                    series: [{ name: window.t('chart_clicks', 'Клики'), data: sourceData.map(item => parseInt(item.click_count)) }],
                    xaxis: { 
                        categories: sourceData.map(item => item.source_name), 
                        labels: { show: false }, 
                        axisBorder: { show: false }, 
                        axisTicks: { show: false } 
                    },
                    yaxis: { show: false }, 
                    legend: { show: false }
                });
                sourceChart.render();
                window.onyxCharts.push(sourceChart);
            } else { 
                renderFallback("#sourceChart"); 
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Interactive 3D Traffic Globe
    // --------------------------------------------------------------------------
    try {
        const globeContainer = document.getElementById('globeViz');
        if (globeContainer && typeof Globe !== 'undefined') {
            if (!hasLinkId) {
                globeContainer.innerHTML = `<div style="width:100%; height:500px; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; border-radius:16px; border:1px solid rgba(255,255,255,0.02);">${window.t('map_unavailable', 'Карта недоступна (Выберите маршрут)')}</div>`;
            } else if (globeData.length > 0) {
                const world = Globe()(globeContainer)
                    .globeImageUrl('//unpkg.com/three-globe/example/img/earth-blue-marble.jpg')
                    .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
                    .backgroundColor('rgba(0,0,0,0)')
                    .showAtmosphere(true).atmosphereColor('#4287f5').atmosphereAltitude(0.15)
                    .pointsData(globeData)
                    .pointAltitude(0.02)
                    .pointColor(() => '#e5c158')
                    .pointRadius('size')
                    .pointLabel('ip')
                    .pointResolution(32);

                const globeMaterial = world.globeMaterial();
                globeMaterial.color.set('#ffffff'); 
                globeMaterial.roughness = 0.6;
                globeMaterial.metalness = 0.1;

                world.controls().autoRotate = true; 
                world.controls().autoRotateSpeed = 0.5; 
                world.controls().enableZoom = false; 

                setTimeout(() => {
                    world.width(globeContainer.clientWidth).height(globeContainer.clientHeight);
                    world.pointOfView({ altitude: 2.2 }); 
                }, 100);

                window.addEventListener('resize', () => {
                    if (globeContainer.clientWidth > 0) {
                        world.width(globeContainer.clientWidth).height(globeContainer.clientHeight);
                    }
                });
            } else {
                 globeContainer.innerHTML = `<div style="width:100%; height:500px; display:flex; align-items:center; justify-content:center; color:#55555a; font-style:italic; border-radius:16px; border:1px solid rgba(255,255,255,0.02);">${window.t('map_no_sessions', 'Сессии трафика отсутствуют')}</div>`;
            }
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Custom Analytics Link Selector
    // --------------------------------------------------------------------------
    try {
        const selectorWrapper = document.getElementById('analyticsLinkSelector');
        if (selectorWrapper) {
            const searchInput = document.getElementById('analyticsSearchInput');
            const optionsList = document.getElementById('analyticsOptionsList');
            const trigger = selectorWrapper.querySelector('.sort-trigger');
            const options = optionsList.querySelectorAll('.sort-option[data-search]');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                selectorWrapper.classList.toggle('open');
                if (selectorWrapper.classList.contains('open')) searchInput.focus();
            });

            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                options.forEach(opt => {
                    const searchStr = opt.getAttribute('data-search') || '';
                    opt.style.display = searchStr.includes(term) ? 'flex' : 'none';
                });
            });
        }
    } catch (e) {}

    // --------------------------------------------------------------------------
    // Time Period Filters
    // --------------------------------------------------------------------------
    try {
        const currentRange = urlParams.get('range') || '7d';
        const filterButtons = document.querySelectorAll('.time-filters .filter-btn');
        
        if (filterButtons.length > 0) {
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-range') === currentRange) {
                    btn.classList.add('active');
                }
                btn.addEventListener('click', () => {
                    const selectedRange = btn.getAttribute('data-range');
                    if (selectedRange && selectedRange !== currentRange) {
                        urlParams.set('range', selectedRange);
                        window.location.search = urlParams.toString(); 
                    }
                });
            });
        }
    } catch (e) {}
});

/* ==========================================================================
   LINK MANAGEMENT LOGIC
   Handles interactive data manipulation for individual links, including
   clipboard duplication, record editing, and configuration extraction.
   ========================================================================== */
/* ==========================================================================
   LINK MANAGEMENT LOGIC
   Handles interactive data manipulation for individual links, including
   clipboard duplication, record editing, and configuration extraction.
   ========================================================================== */
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => { 
        
        // Удаляем предыдущее уведомление, если пользователь кликает несколько раз подряд
        const existingToast = document.getElementById('onyx-copy-toast');
        if (existingToast) existingToast.remove();

        const isLight = document.body.classList.contains('light-theme');
        const toast = document.createElement('div');
        toast.id = 'onyx-copy-toast';

        // Премиальные стили (Glassmorphism + Apple UI)
        toast.style.position = 'fixed';
        toast.style.bottom = '40px';
        toast.style.left = '50%';
        toast.style.transform = 'translate(-50%, 50px)';
        toast.style.opacity = '0';
        toast.style.background = isLight ? 'rgba(255, 255, 255, 0.75)' : 'rgba(28, 28, 30, 0.75)';
        toast.style.backdropFilter = 'blur(16px)';
        toast.style.webkitBackdropFilter = 'blur(16px)';
        toast.style.border = isLight ? '1px solid rgba(0, 0, 0, 0.08)' : '1px solid rgba(255, 255, 255, 0.08)';
        toast.style.color = isLight ? '#1c1c1e' : '#f5f5f7';
        toast.style.padding = '14px 24px';
        toast.style.borderRadius = '100px';
        toast.style.fontFamily = 'Montserrat, sans-serif';
        toast.style.fontSize = '14px';
        toast.style.fontWeight = '500';
        toast.style.boxShadow = isLight ? '0 10px 30px rgba(0, 0, 0, 0.1)' : '0 10px 30px rgba(0, 0, 0, 0.4)';
        toast.style.zIndex = '9999';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.gap = '10px';
        toast.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)';

        // Иконка успешного действия (Зеленая галочка)
        const icon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#32d74b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

        // Формируем контент без вывода самой ссылки
        const message = window.t('copied_success', 'Ссылка скопирована в буфер обмена.');
        toast.innerHTML = `${icon} <span>${message}</span>`;

        document.body.appendChild(toast);

        // Принудительный рефлоу для запуска CSS-анимации
        void toast.offsetWidth;

        // Анимация появления
        toast.style.transform = 'translate(-50%, 0)';
        toast.style.opacity = '1';

        // Анимация исчезновения и удаление из DOM через 2.5 секунды
        setTimeout(() => {
            toast.style.transform = 'translate(-50%, 20px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 400); // Ожидание завершения transition
        }, 2500);
        
    });
};

/* ==========================================================================
   LINK FILTERING AND SORTING
   In-memory array operations to rapidly restructure active link nodes without
   issuing redundant server requests.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('linksGridContainer');
    if (!grid) return;

    const searchInput = document.getElementById('linkSearch');
    const sortWrapper = document.getElementById('sortDropdown');
    const cards = Array.from(grid.querySelectorAll('.link-3d-card')).filter(card => !card.classList.contains('empty-state-card'));
    
    if (cards.length === 0) return;

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            cards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const url = card.getAttribute('data-url') || '';
                card.style.display = (title.includes(term) || url.includes(term)) ? 'flex' : 'none';
            });
        });
    }

    if (sortWrapper) {
        const trigger = sortWrapper.querySelector('.sort-trigger');
        const label = sortWrapper.querySelector('#sortLabel');
        const options = sortWrapper.querySelectorAll('.sort-option');

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            sortWrapper.classList.toggle('open');
        });

        options.forEach(opt => {
            opt.addEventListener('click', (e) => {
                e.stopPropagation();
                options.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                label.textContent = opt.textContent;
                sortWrapper.classList.remove('open');

                const sortType = opt.getAttribute('data-sort');
                
                const sortedCards = cards.sort((a, b) => {
                    const idA = parseInt(a.getAttribute('data-id')) || 0;
                    const idB = parseInt(b.getAttribute('data-id')) || 0;
                    const clicksA = parseInt(a.getAttribute('data-clicks')) || 0;
                    const clicksB = parseInt(b.getAttribute('data-clicks')) || 0;
                    const titleA = a.getAttribute('data-title');
                    const titleB = b.getAttribute('data-title');

                    switch (sortType) {
                        case 'date-desc': return idB - idA; 
                        case 'date-asc': return idA - idB;  
                        case 'clicks-desc': return clicksB - clicksA; 
                        case 'clicks-asc': return clicksA - clicksB; 
                        case 'alpha-asc': return titleA.localeCompare(titleB); 
                        case 'alpha-desc': return titleB.localeCompare(titleA); 
                        default: return 0;
                    }
                });

                grid.innerHTML = '';
                sortedCards.forEach((card, index) => {
                    grid.appendChild(card);
                    if (typeof anime !== 'undefined') {
                        anime({ 
                            targets: card, 
                            opacity: [0, 1], 
                            translateY: [15, 0], 
                            duration: 400, 
                            delay: index * 30, 
                            easing: 'easeOutQuint' 
                        });
                    }
                });
            });
        });
    }
});

/* ==========================================================================
   INTERNATIONALIZATION (I18N) LOGIC
   Maintains user language preferences utilizing cookies and issues state
   synchronization updates to the application backend.
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const langOptions = document.querySelectorAll('.lang-options-list .custom-option, #langOptionsList .custom-option');
    if (langOptions.length === 0) return;

    const langMap = { 'Русский': 'ru', 'English': 'en', 'Українська': 'uk' };
    const flagMap = { 'ru': 'ru', 'en': 'us', 'uk': 'ua' };

    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return 'en';
    };
    
    const currentLang = getCookie('lang');

    const triggerSpans = document.querySelectorAll('.lang-trigger-inner');
    if (triggerSpans.length > 0) {
        const langText = Object.keys(langMap).find(key => langMap[key] === currentLang) || 'English';
        const htmlContent = `<img src="https://flagcdn.com/w20/${flagMap[currentLang] || 'us'}.png" alt="flag" style="width: 18px; border-radius: 2px;"> ${langText}`;
        
        triggerSpans.forEach(span => { span.innerHTML = htmlContent; });
        
        langOptions.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.textContent.trim() === langText) opt.classList.add('selected');
        });
    }

    langOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const text = this.textContent.trim();
            const langCode = langMap[text] || 'en';

            document.cookie = `lang=${langCode}; path=/; max-age=31536000`;

            const formData = new URLSearchParams();
            formData.append('action', 'set_language');
            formData.append('lang', langCode);

            fetch('/', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(() => {
                window.location.reload();
            });
        });
    });
});

/* ==========================================================================
   MOBILE MENU CONTROLLER
   Manages the state and viewport overflow protections for the off-canvas
   navigation utilized on mobile endpoints.
   ========================================================================== */
window.toggleMobileMenu = function() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
        
        if (sidebar.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
};

/* ==========================================================================
   VIEW: HOME & DASHBOARD EVENT DELEGATION
   Safely intercepts inline event attributes extracted from home.php
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Client-Side Theme Synchronization 
    // Ensures HTML structure matches local state even if backend misses the session.
    const savedTheme = localStorage.getItem('onyx_theme') || 'dark';
    if (savedTheme === 'light') {
        document.documentElement.classList.add('light-theme');
        document.body.classList.add('light-theme');
    } else {
        document.documentElement.classList.remove('light-theme');
        document.body.classList.remove('light-theme');
    }

    // 2. Generic Dropdown & Modal Logic
    const toggleDropdowns = document.querySelectorAll('.js-toggle-dropdown');
    toggleDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            this.classList.toggle('open');
        });
    });

    const stopProps = document.querySelectorAll('.js-stop-propagation');
    stopProps.forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // 3. Application State Triggers
    const mobileMenuToggles = document.querySelectorAll('.js-toggle-mobile-menu');
    mobileMenuToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            if (typeof window.toggleMobileMenu === 'function') window.toggleMobileMenu();
        });
    });

    const openSettingsBtn = document.getElementById('js-open-settings');
    if (openSettingsBtn) {
        openSettingsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (typeof window.openSettingsModal === 'function') window.openSettingsModal();
        });
    }
    
    const closeSettingsBtn = document.getElementById('js-close-settings');
    if (closeSettingsBtn) {
        closeSettingsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (typeof window.closeSettingsModal === 'function') window.closeSettingsModal();
        });
    }

    const openLogoutBtn = document.getElementById('js-open-logout');
    if (openLogoutBtn) {
        openLogoutBtn.addEventListener('click', () => {
            if (typeof window.openLogoutModal === 'function') window.openLogoutModal();
        });
    }

    const closeLogoutBtn = document.getElementById('js-close-logout');
    if (closeLogoutBtn) {
        closeLogoutBtn.addEventListener('click', () => {
            if (typeof window.closeLogoutModal === 'function') window.closeLogoutModal();
        });
    }

    const confirmLogoutBtn = document.getElementById('js-confirm-logout');
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', () => {
            window.location.href = '/logout';
        });
    }
});

/* ==========================================================================
   AUTH FORM DEBOUNCE
   Prevents double-submission and sticky states on authentication forms
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const authForms = document.querySelectorAll('.auth-card form');
    authForms.forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                // Визуальная блокировка предотвращает повторный POST с пустыми полями
                btn.style.opacity = '0.7';
                btn.style.pointerEvents = 'none';
            }
        });
    });
});