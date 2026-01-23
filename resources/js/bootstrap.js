import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ========================================
// API 響應時間追蹤（Phase 8: Performance Monitoring）
// ========================================
window.axios.interceptors.request.use((config) => {
  config.metadata = { startTime: performance.now() };
  return config;
});

window.axios.interceptors.response.use(
  (response) => {
    const duration = performance.now() - response.config.metadata.startTime;

    // 發送至 Google Analytics
    if (typeof gtag !== 'undefined') {
      gtag('event', 'api_response_time', {
        event_category: 'Performance',
        event_label: response.config.url,
        value: Math.round(duration),
        non_interaction: true,
      });
    }

    return response;
  },
  (error) => {
    // 也追蹤失敗的請求
    if (error.config && error.config.metadata) {
      const duration = performance.now() - error.config.metadata.startTime;

      if (typeof gtag !== 'undefined') {
        gtag('event', 'api_response_time', {
          event_category: 'Performance',
          event_label: error.config.url,
          value: Math.round(duration),
          error: true,
          non_interaction: true,
        });
      }
    }

    return Promise.reject(error);
  }
);
