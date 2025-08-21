# Google Analytics Integration Design Document

## 1. Overview

This document outlines the technical design and implementation strategy for integrating Google Analytics (GA4) into the "HoldYourBeer" application. The goal is to capture key user interactions and application events to provide insights into user behavior, feature usage, and overall application performance. This integration is guided by the specifications laid out in `spec/features/google_analytics_integration.feature`.

## 2. Implementation Strategy

### 2.1. Frontend (Client-Side Tracking)

We will use Google's official `gtag.js` library for client-side event tracking. The tracking script will be loaded on all pages for authenticated users.

**Tracking Script Injection:**
- The Google Analytics tracking ID (`GA_MEASUREMENT_ID`) will be stored as an environment variable.
- A new Blade component (e.g., `components/google-analytics.blade.php`) will be created to house the `gtag.js` script.
- This component will be included in the main application layout (`layouts/app.blade.php`), ensuring it is loaded on every page.
- The script will only render if `GA_MEASUREMENT_ID` is present and the application is in `production` mode.

**Page View Tracking:**
- `gtag.js` automatically tracks page views upon initialization. No additional configuration is needed for basic page view tracking.

### 2.2. Backend (Server-Side Events)

For critical events that occur on the server-side (e.g., successful registration, purchase), we will use the **Measurement Protocol API for GA4**. This ensures that events are tracked reliably, even if the user has ad-blockers or leaves the page before a client-side event can be sent.

**Implementation:**
- A dedicated service class, `app/Services/GoogleAnalyticsService.php`, will be created to handle all interactions with the Measurement Protocol API.
- This service will have methods corresponding to specific events (e.g., `sendUserRegistrationEvent`, `sendBeerCreationEvent`).
- The service will be responsible for constructing the event payload and sending it to Google Analytics via an HTTP client.
- To avoid impacting user experience, these API calls will be dispatched as **queued jobs**.

## 3. Event Tracking Details

Based on `spec/features/google_analytics_integration.feature`, the following events will be tracked:

| Event Name | Trigger | Tracking Method | Data to be Sent |
| :--- | :--- | :--- | :--- |
| `page_view` | Page Load | Frontend (`gtag.js`) | Page title, URL, user session ID |
| `login` | Successful Login | Backend (Queued Job) | User ID (anonymized), login method (email/Google) |
| `sign_up` | Successful Registration | Backend (Queued Job) | User ID (anonymized), registration method |
| `beer_created` | New beer saved | Backend (Queued Job) | Beer brand, beer style, user's total beer count |
| `count_incremented`| Beer count +1 | Frontend (JavaScript) | Beer ID, new count |
| `search` | Brand/Beer search | Frontend (JavaScript) | Search term, number of results |
| `exception` | Application Error | Backend (Middleware) | Error message, error code, page URL |

## 4. Configuration

- `GOOGLE_ANALYTICS_ID`: The GA4 Measurement ID (e.g., `G-XXXXXXXXXX`).
- `GOOGLE_ANALYTICS_SECRET`: The Measurement Protocol API secret.

These will be added to `.env.example` and the production environment configuration.

## 5. Testing Strategy

- **Frontend:** Use browser developer tools to inspect network requests and ensure `gtag.js` events are being sent correctly to Google Analytics.
- **Backend:** Create feature tests (`tests/Feature/GoogleAnalyticsIntegrationTest.php`) that mock the `GoogleAnalyticsService` and assert that the correct methods are called with the expected parameters when specific actions occur. We will also test that the jobs are correctly dispatched to the queue.

## 6. Privacy Considerations

- All user-identifiable information (like User ID) will be anonymized or hashed before being sent to Google Analytics.
- We will implement a consent mechanism (cookie banner) to comply with GDPR and other privacy regulations, allowing users to opt-out of tracking. Tracking will be disabled by default until consent is given.
