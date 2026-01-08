# Session: Fix Mobile Login View

**Date**: 2026-01-08
**Status**: ✅ Completed
**Duration**: 0.5 hours
**Issue**: USER_REQUEST
**Contributors**: Claude AI
**Tags**: #ui, #mobile, #bugfix

---

## 📋 Overview

### Goal
Fix the issue where the "Go to Register" button (Sign up link) is not visible on mobile devices (specifically iPhone 17) on the login page.

---

## 🎯 Context

### Problem
The user reported that on iPhone 17 (mobile web), the "Sign up now" link at the bottom of the login form is not visible.
The analysis revealed that:
1. The `guest` layout had `overflow-hidden` on the main container with `min-h-screen`.
2. There is a fixed footer at the bottom (`fixed bottom-0`).
3. If the content flows behind the footer or is clipped by `overflow-hidden`, the bottom part of the form becomes inaccessible.

### Solution
1. Remove `overflow-hidden` from the main container in `resources/views/layouts/guest.blade.php` to allow proper scrolling behavior.
2. Add `pb-16` (padding-bottom) to the main container to ensure the content clears the fixed footer when scrolled to the bottom.

---

## ✅ Implementation Checklist

### Phase 1: Fix Layout [✅ Completed]
- [x] Modify `resources/views/layouts/guest.blade.php`:
    - Remove `overflow-hidden`
    - Add `pb-16`

### Phase 2: Adjust Padding (Feedback Loop) [✅ Completed]
- [x] Increase padding to `pb-32` in `resources/views/layouts/guest.blade.php`
- [x] Add `mb-6` to the form container for extra spacing

---

## 📊 Outcome

### Files Modified
```
resources/views/layouts/guest.blade.php (modified)
resources/views/auth/login.blade.php (modified)
```

The login page should now have significant bottom padding (128px), ensuring the footer does not overlap the "Sign up" content.

### Phase 3: Testing [✅ | 🔄 | ⏳]
- [ ] Unit tests (單元測試)
- [ ] Widget tests (Widget 測試)
- [ ] Integration tests (整合測試)
### Phase 5: Optimize Layout Structure [✅ Completed]
- [x] Move "Start Your Beer Collection" text in `auth/login.blade.php` to a new `heading` slot in `layouts/guest.blade.php`.
- [x] Position the `heading` slot directly below the logo in `layouts/guest.blade.php` to save vertical space inside the form card.

### Phase 6: Refine Card Styling [✅ Completed]
- [x] Update `layouts/guest.blade.php`:
    - Change `w-full` to `w-[90%]` (tuned from 96% -> 95% -> 90%) to provide comfortable side padding (5% each side).
    - Change `sm:rounded-xl` to `rounded-2xl` to apply rounded corners on all devices.
    - Note: Tailwind arbitrary values (JIT) require `npm run build` to regenerate CSS.
### Phase 7: Final Vertical Spacing [✅ Completed]
- [x] Update `layouts/guest.blade.php`:
    - Reduce space between Heading and Card by 50%:
        - Heading container: `mb-6` -> `mb-3`
        - Card container: `mt-6` -> `mt-3`

### Phase 8: Transparent Footer [✅ Completed]
- [x] Refactor `components/footer.blade.php` to support class overriding via `$attributes->merge`.
- [x] Update `layouts/guest.blade.php` to pass `!bg-transparent !backdrop-blur-none !border-none` to the footer.
- [x] Update `layouts/app.blade.php` (for dashboard, charts, profile) to pass `!bg-transparent !backdrop-blur-none !border-none` to the footer.

### Phase 9: Vertical Centering [✅ Completed]
- [x] Update `layouts/guest.blade.php`:
    - Enable vertical centering on mobile: `sm:justify-center` -> `justify-center`.
    - Remove top padding `pt-6` -> `pt-0` (let flex center it).
    - Reduce bottom padding `pb-32` -> `pb-16` to reduce the "far" perception while keeping footer clearance.

### Phase 10: Final Adjustments [✅ Completed]
- [x] Update `layouts/guest.blade.php`:
    - Remove `overflow-hidden` from the main container to allow proper scrolling behavior.
    - Add `pb-16` (padding-bottom) to the main container to ensure the content clears the fixed footer when scrolled to the bottom.

---

## 🚧 Blockers & Solutions

### Blocker 1: [Title] [✅ RESOLVED | 🔄 IN PROGRESS | ⏸️ BLOCKED]
- **Issue**: [阻礙進度的原因]
- **Impact**: [造成的影響]
- **Solution**: [如何解決]
- **Resolved**: [解決時間]

---

## 📊 Outcome

### What Was Built
[交付成果清單 - 完成後填寫]

### Files Created/Modified
```
lib/
├── path/to/file.dart (new|modified)
test/
├── test_file.dart (new)
```

### Metrics
- **Code Coverage**: XX%
- **Lines Added**: ~XXX
- **Lines Modified**: ~XXX
- **Test Files**: X 新增, Y 修改

---

## 🎓 Lessons Learned

### 1. [Lesson Title]
**Learning**: [我們學到了什麼？]

**Solution/Pattern**: [我們如何處理它]

**Future Application**: [如何應用於未來的工作]

---

## ✅ Completion

**Status**: 🔄 In Progress → ✅ Completed
**Completed Date**: YYYY-MM-DD
**Session Duration**: X hours

> ℹ️ **Next Steps**: 詳見 [Session Guide](GUIDE.md)
> 1. 更新上方狀態與日期
> 2. 根據 Tags 更新 INDEX 檔案
> 3. 運行 `./scripts/archive-session.sh`

---

## 🔮 Future Improvements

### Not Implemented (Intentional)
- ⏳ [決定暫不實作的部分與原因]

### Potential Enhancements
- 📌 [未來迭代的想法]

### Technical Debt
- 🔧 [目前暫時接受的已知問題]

---

## 🔗 References

### Related Work
- [類似實作的連結]

### External Resources
- [使用的文章、文件、套件]

### Team Discussions
- [Slack/Discord 討論連結]
