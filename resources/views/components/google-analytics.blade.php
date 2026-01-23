{{-- Google Analytics 4 (GA4) Tracking Component --}}
@if(config('services.google_analytics.enabled') && config('services.google_analytics.measurement_id') && session('cookie_consent') === true)
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.measurement_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{{ config('services.google_analytics.measurement_id') }}', {
    'send_page_view': true,
    @auth
    'user_id': '{{ auth()->id() }}',
    @endauth
  });

  @auth
  // 注入用戶屬性到 window 物件，供 analytics.js 使用
  window.userProperties = {
    'user_locale': '{{ app()->getLocale() }}',
    'total_beers': {{ auth()->user()->beerCounts()->sum('count') ?? 0 }},
    'account_age_days': {{ now()->diffInDays(auth()->user()->created_at) }}
  };
  @endauth
</script>
@endif
