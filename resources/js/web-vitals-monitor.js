// Google Analytics Web Vitals 監控模組
// Phase 8: Performance Monitoring - Core Web Vitals Integration

import { onCLS, onLCP, onTTFB, onFCP, onINP } from 'web-vitals';

/**
 * 將 Web Vitals 指標發送至 Google Analytics
 * @param {Object} metric - Web Vitals 指標物件
 * @property {string} metric.name - 指標名稱 (LCP, FID, CLS, etc.)
 * @property {number} metric.value - 指標數值
 * @property {string} metric.id - 唯一識別碼（用於去重）
 * @property {number} metric.delta - 與上次報告的差異
 * @property {string} metric.rating - 評級 ("good" | "needs-improvement" | "poor")
 */
function sendToGoogleAnalytics(metric) {
  // 檢查 gtag 是否可用（GDPR Cookie Consent）
  if (typeof gtag === 'undefined') {
    return;
  }

  // 發送自訂事件至 GA4
  gtag('event', metric.name, {
    event_category: 'Web Vitals',
    event_label: metric.id, // 唯一 ID（用於去重）
    value: Math.round(metric.name === 'CLS' ? metric.value * 1000 : metric.value), // CLS 乘以 1000 以保留精度
    non_interaction: true, // 不影響跳出率
    metric_rating: metric.rating, // "good" | "needs-improvement" | "poor"
    metric_delta: Math.round(metric.delta), // 與上次報告的差異
  });

  // Debug 模式：輸出至 console
  if (window.location.search.includes('debug=true')) {
    console.log('[Web Vitals]', metric.name, metric.value, metric);
  }
}

/**
 * 初始化 Core Web Vitals 追蹤
 */
function initWebVitals() {
  // LCP - Largest Contentful Paint（最大內容繪製時間）
  // 目標：< 2.5s (good), < 4s (needs improvement), >= 4s (poor)
  onLCP(sendToGoogleAnalytics);

  // INP - Interaction to Next Paint（互動至下一次繪製，取代 FID）
  // 目標：< 200ms (good), < 500ms (needs improvement), >= 500ms (poor)
  // 註：web-vitals v4.x 已移除 FID，全面改用 INP
  onINP(sendToGoogleAnalytics);

  // CLS - Cumulative Layout Shift（累積版面配置位移）
  // 目標：< 0.1 (good), < 0.25 (needs improvement), >= 0.25 (poor)
  onCLS(sendToGoogleAnalytics);

  // TTFB - Time to First Byte（首位元組時間）
  // 額外指標：伺服器響應速度
  onTTFB(sendToGoogleAnalytics);

  // FCP - First Contentful Paint（首次內容繪製）
  // 額外指標：頁面開始繪製的速度
  onFCP(sendToGoogleAnalytics);
}

// 僅在支援 PerformanceObserver 的瀏覽器中初始化
if ('PerformanceObserver' in window) {
  initWebVitals();
} else {
  console.warn('[Web Vitals] PerformanceObserver not supported in this browser.');
}
