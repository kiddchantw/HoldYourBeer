import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

// 儲存導覽狀態的 key
const ONBOARDING_STATE_KEY = 'holdyourbeer_onboarding_state';

// Helper to get translations
function getTranslations() {
    return window.onboardingTranslations || {
        steps: {
            beer_list: { title: '啤酒收藏列表', description: '這是你的啤酒收藏' },
            add_beer: { title: '新增啤酒', description: '點這裡新增啤酒', description_empty: '開始追蹤第一支啤酒' },
            counter: { title: '計數器', description: '使用 +/- 按紐' },
            charts: { title: '圖表區域', description: '查看統計', footer: '未來會充滿紀錄' },
            type_selector: { title: '圖表類型', description: '切換圖表類型' },
            time_filter: { title: '時間篩選', description: '篩選時間' }
        },
        buttons: { next: 'Next', prev: 'Prev', done: 'Done' }
    };
}

// 動態獲取 Dashboard 導覽步驟
function getDashboardSteps() {
    const t = getTranslations().steps;
    return [
        {
            element: '#beer-list',
            popover: {
                title: t.beer_list.title,
                description: t.beer_list.description + '<br><br><span style="font-size: 2em;">🍺 📝</span>',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#add-beer-button',
            popover: {
                title: t.add_beer.title,
                description: t.add_beer.description + '<br><br><span style="font-size: 2em;">➕</span>',
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '.beer-counter',
            popover: {
                title: t.counter.title,
                description: t.counter.description + '<br><br><span style="font-size: 2em;">➖ 1️⃣ ➕</span>',
                side: 'left',
                align: 'start'
            }
        }
    ];
}

// 動態獲取 Charts 導覽步驟
function getChartsSteps() {
    const t = getTranslations().steps;
    return [
        {
            element: '#chart-container',
            popover: {
                title: t.charts.title,
                description: t.charts.description + '<br><br><span style="font-size: 2em;">📊 🥧 📈</span><br><br>' + t.charts.footer,
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#chart-type-selector',
            popover: {
                title: t.type_selector.title,
                description: t.type_selector.description,
                side: 'bottom',
                align: 'start'
            }
        },
        {
            element: '#time-filter',
            popover: {
                title: t.time_filter.title,
                description: t.time_filter.description,
                side: 'left',
                align: 'start'
            }
        }
    ];
}

// 建立 Driver 實例
function createDriver(steps, onComplete) {
    const t = getTranslations().buttons;
    return driver({
        showProgress: true,
        steps: steps,
        nextBtnText: t.next,
        prevBtnText: t.prev,
        doneBtnText: t.done,
        progressText: '{{current}} / {{total}}',
        // 使用 onDestroyed 確保在導覽銷毀後執行回調
        onDestroyed: () => {
            if (onComplete) {
                onComplete();
            }
        }
    });
}

// 啟動 Dashboard 導覽
export function startDashboardTour() {
    let steps = getDashboardSteps();

    // 檢查是否有啤酒計數器，如果沒有（新用戶空狀態），移除計數器導覽步驟
    if (!document.querySelector('.beer-counter')) {
        steps = steps.filter(step => !step.element.includes('.beer-counter'));

        // 如果是空狀態，更新新增按鈕的說明
        const addBtnStep = steps.find(step => step.element === '#add-beer-button');
        if (addBtnStep) {
            const t = getTranslations().steps.add_beer;
            addBtnStep.popover.description = t.description_empty + '<br><br><span style="font-size: 2em;">🍺 ✨</span>';
        }
    }

    const driverObj = createDriver(steps, () => {
        // 導覽銷毀後，儲存狀態並跳轉到 Charts
        // 加一點延遲確保 UI 轉換順暢
        setTimeout(() => {
            localStorage.setItem(ONBOARDING_STATE_KEY, 'charts');
            // 使用動態 URL
            if (window.appRoutes && window.appRoutes.charts) {
                window.location.href = window.appRoutes.charts;
            } else {
                console.error('Charts route not found');
            }
        }, 300);
    });

    driverObj.drive();
}

// 啟動 Charts 導覽
export function startChartsTour() {
    const steps = getChartsSteps();
    const driverObj = createDriver(steps, () => {
        // 導覽完成，顯示完成確認或直接完成
        // 這裡直接完成並回到 Dashboard
        completeOnboarding();
    });

    driverObj.drive();
}

// 檢查是否需要繼續導覽
export function checkOnboardingState() {
    const state = localStorage.getItem(ONBOARDING_STATE_KEY);

    // 檢查當前 URL 是否匹配 Charts 頁面
    // 因為 window.appRoutes.charts 是完整 URL，我們做個簡單比對
    const currentPath = window.location.pathname;
    const chartsUrl = window.appRoutes ? new URL(window.appRoutes.charts) : null;

    if (state === 'charts' && chartsUrl && currentPath === chartsUrl.pathname) {
        // 在 Charts 頁面，繼續導覽
        localStorage.removeItem(ONBOARDING_STATE_KEY);
        startChartsTour();
    }
}

// 完成導覽，更新資料庫
export function completeOnboarding() {
    const url = window.appRoutes ? window.appRoutes.onboarding_complete : '/onboarding/complete';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => {
            if (response.ok) {
                localStorage.removeItem(ONBOARDING_STATE_KEY);
                if (window.appRoutes && window.appRoutes.dashboard) {
                    window.location.href = window.appRoutes.dashboard;
                } else {
                    window.location.href = '/dashboard';
                }
            } else {
                console.error('Onboarding completion failed:', response.statusText);
                // 嘗試解析錯誤訊息
                response.text().then(text => console.error(text));
                // 顯示錯誤給用戶
                alert('Unable to complete tour. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error completing onboarding:', error);
            alert('A system error occurred.');
        });
}

// 監聽 Livewire 事件
document.addEventListener('livewire:init', () => {
    Livewire.on('start-onboarding-tour', () => {
        startDashboardTour();
    });
});

// 頁面載入時檢查狀態
window.addEventListener('DOMContentLoaded', () => {
    checkOnboardingState();

    // 檢查是否需要重新啟動導覽
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('restart_onboarding') === '1') {
        startDashboardTour();
    }
});
