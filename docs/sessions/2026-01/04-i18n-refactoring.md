# Session: I18n Refactoring & Language Switcher Fixes

**Date**: 2026-01-04
**Status**: ✅ Completed
**Duration**: 1 小時
**Issue**: N/A
**Contributors**: @kiddchan, Claude AI
**Branch**: develop
**Tags**: #refactor, #infrastructure, #architecture
**Categories**: Internationalization, Infrastructure, UI/UX

---

## 📋 Overview

### Goal
Fix broken translation loading and language switcher functionality on the web interface by refactoring the i18n architecture and standardizing locale handling.

### Related Documents
- **Related Sessions**: [Session 02: OAuth Forgot Password UX](02-oauth-forgot-password-ux.md)

### Commits
- (Pending commit)

---

## 🎯 Context

### Problem
While implementing the "Forgot Password" flow for OAuth users, we discovered that:
1.  Translations were not loading properly due to conflicts between `lang/` and `resources/lang/`.
2.  The Locale switcher on the login page was broken (Javascript dependency issues).
3.  Inconsistent locale naming (`zh-TW` vs `zh_TW`) caused routing and file loading errors.

### Current State
- **Files**: Split between `lang/` and `resources/lang/`.
- **Locale**: Mixed usage of `zh-TW` (URL) and `zh_TW` (PHP).
- **UI**: Dropdown switcher depends on Alpine.js which is missing in Guest layout.

**Gap**: A unified, standard i18n architecture and a robust, no-js-dependency language switcher.

---

## 💡 Planning

### Approach Analysis

#### Option A: Unified Directory & Simplified Switcher [✅ CHOSEN]
Move all files to `lang/`, standardizing on Snake Case (`zh_TW`) for backend and Kebab Case (`zh-TW`) for URL, and replacing the complex dropdown with a simple toggle button.

**Pros**:
- Complies with Laravel 10+ standards.
- Eliminates "file not found" ambiguity.
- Better UX: Single click toggle is faster than dropdown.
- More robust: Less JS dependency.

**Cons**:
- Requires renaming existing files.
- Needs careful regex update in routing.

**Decision Rationale**: The current split structure is causing active bugs. Unifying them is the only long-term solution.

### Design Decisions

#### D1: Locale Naming Convention
- **Options**: A) All Kebab (`zh-TW`), B) All Snake (`zh_TW`), C) Hybrid
- **Chosen**: C) Hybrid
- **Reason**: URLs must use Kebab for SEO/Standard (`zh-TW`). PHP prefers Snake (`zh_TW`) for directory/file naming.
- **Implementation**: Middleware handles the conversion layer.

---

## ✅ Implementation Checklist

### Phase 1: Directory Consolidation [✅ Completed]
- [x] Merge `resources/lang/` content into `lang/`.
- [x] Remove obsolete `resources/lang/` directory.
- [x] Rename `lang/zh-TW.json` to `lang/zh_TW.json`.

### Phase 2: Locale Standardization Logic [✅ Completed]
- [x] Update `SetLocale` middleware to accept `zh-TW` from URL but set internal App Locale to `zh_TW`.
- [x] Update `routes/web.php` regex to accept both formats.
- [x] Ensure `config/app.php` fallback works correctly.

### Phase 3: Language Switcher Refactoring [✅ Completed]
- [x] Update `guest.blade.php` layout to include `@livewireStyles` and `@livewireScripts`.
- [x] Refactor `language-switcher.blade.php`:
    - Remove Dropdown/Alpine dependency.
    - Implement direct "Toggle Link".
    - Fix URL generation to always force `zh-TW` (hyphen).
    - Add missing routes (`password.request`).

---

## 🚧 Blockers & Solutions

### Blocker 1: Dropdown not opening
- **Issue**: Guest layout missing Livewire/Alpine scripts.
- **Solution**: Injected `@livewireStyles` and `@livewireScripts`.
- **Resolved**: 2026-01-04

### Blocker 2: Forgot Password Route Missing
- **Issue**: `route()` failed for `password.request` in switcher.
- **Solution**: Added route to `routeMap`.
- **Resolved**: 2026-01-04

---

## 📊 Outcome

### What Was Built
- A unified translation file structure in `lang/`.
- A robust, single-click language toggle button.
- A standardized locale handling mechanism (Middleware).

### Files Created/Modified
```
lang/
├── zh_TW/ (Merged)
├── zh_TW.json (Renamed)
app/Http/Middleware/
├── SetLocale.php (Modified)
routes/
├── web.php (Modified)
resources/views/
├── components/language-switcher.blade.php (Refactored)
├── layouts/guest.blade.php (Modified)
```

---

## 🎓 Lessons Learned

### 1. Locale Naming Hell
**Learning**: Mixing `zh-TW` and `zh_TW` without a clear strategy leads to bugs.
**Solution/Pattern**: Define clear boundaries: URL = Kebab, Backend = Snake. Use Middleware as the adapter.

### 2. Layout Dependencies
**Learning**: Guest layouts often miss scripts (like Livewire/Alpine) present in App layouts.
**Solution**: Always check layout dependencies when reusable components (like Dropdown) fail.

---

## ✅ Completion

**Status**: ✅ Completed
**Completed Date**: 2026-01-04

---

