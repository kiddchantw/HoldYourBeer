// Google Analytics 用戶參與度追蹤模組
// Phase 6: User Engagement Tracking

// ============================================
// 1. 頁面停留時間追蹤
// ============================================
let pageStartTime = Date.now();
let isEngaged = false;
let engagementTimer = null;

// 追蹤用戶參與（停留超過 10 秒視為 engaged）
function trackEngagement() {
    if (!isEngaged && typeof gtag !== 'undefined') {
        isEngaged = true;
        gtag('event', 'user_engagement', {
            'engagement_time_msec': 10000
        });
    }
}

// 10 秒後標記為 engaged
engagementTimer = setTimeout(trackEngagement, 10000);

// 頁面卸載時發送停留時間
window.addEventListener('beforeunload', function() {
    const timeSpent = Math.round((Date.now() - pageStartTime) / 1000);

    if (typeof gtag !== 'undefined' && timeSpent > 0) {
        gtag('event', 'page_view_time', {
            'event_category': 'engagement',
            'event_label': document.title,
            'value': timeSpent,
            'non_interaction': true
        });
    }
});

// visibilitychange 作為 beforeunload 的備用（行動裝置更可靠）
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        const timeSpent = Math.round((Date.now() - pageStartTime) / 1000);

        if (typeof gtag !== 'undefined' && timeSpent > 0) {
            gtag('event', 'page_view_time', {
                'event_category': 'engagement',
                'event_label': document.title,
                'value': timeSpent,
                'non_interaction': true
            });
        }
    }
});

// ============================================
// 2. Scroll Depth 追蹤
// ============================================
let maxScrollDepth = 0;
let scrollMilestones = {
    25: false,
    50: false,
    75: false,
    100: false
};

window.addEventListener('scroll', function() {
    const scrollPercentage = Math.round(
        (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100
    );

    if (scrollPercentage > maxScrollDepth) {
        maxScrollDepth = scrollPercentage;

        // 每個百分比只觸發一次
        if (maxScrollDepth >= 25 && !scrollMilestones[25] && typeof gtag !== 'undefined') {
            scrollMilestones[25] = true;
            gtag('event', 'scroll', {
                'event_category': 'engagement',
                'event_label': '25%'
            });
        }

        if (maxScrollDepth >= 50 && !scrollMilestones[50] && typeof gtag !== 'undefined') {
            scrollMilestones[50] = true;
            gtag('event', 'scroll', {
                'event_category': 'engagement',
                'event_label': '50%'
            });
        }

        if (maxScrollDepth >= 75 && !scrollMilestones[75] && typeof gtag !== 'undefined') {
            scrollMilestones[75] = true;
            gtag('event', 'scroll', {
                'event_category': 'engagement',
                'event_label': '75%'
            });
        }

        if (maxScrollDepth >= 100 && !scrollMilestones[100] && typeof gtag !== 'undefined') {
            scrollMilestones[100] = true;
            gtag('event', 'scroll', {
                'event_category': 'engagement',
                'event_label': '100%'
            });
        }
    }
});

// ============================================
// 3. 設定用戶屬性
// ============================================
// 從 window.userProperties 讀取（由 Blade 注入）
if (typeof gtag !== 'undefined' && window.userProperties) {
    gtag('set', 'user_properties', window.userProperties);
}
