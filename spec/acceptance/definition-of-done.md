# Definition of Done (DoD)

This document outlines the universal criteria that any User Story or Feature must meet to be considered "Done".

---

## Universal Criteria for All Features

To be considered complete, every feature must satisfy the following conditions.

### 📜 Specification & Documentation
- [ ] A corresponding `.feature` file exists in `spec/features/` and has been reviewed by the team.
- [ ] If the feature involves API changes, `spec/api/api.yaml` has been updated and reviewed.
- [ ] Any necessary user-facing documentation has been drafted.

### 💻 Code & Implementation
- [ ] All code has been peer-reviewed by at least one other developer.
- [ ] The feature branch has been successfully merged into the main branch.

### ✅ Testing & Verification
- [ ] All related unit tests (Pest/PHPUnit) have been written and are passing.
- [ ] All related behavior tests (Behat) for the `.feature` file are passing.
- [ ] All related API contract tests (Dredd) are passing.
- [ ] The entire CI/CD pipeline (e.g., GitHub Actions) for the feature branch is **green** (✅).

### 🎨 UX & Design
- [ ] The user interface is responsive and displays correctly on mobile, tablet, and desktop screens.

--- 

## Example Application: User Story "Beer Tracking"

Here is how the universal criteria are applied to a specific feature.

- **[✅] Specification & Documentation**
  - [x] `spec/features/beer-tracking.feature` is complete.
  - [x] `spec/api/api.yaml` is updated with `/tastings` and other endpoints.

- **[✅] Code & Implementation**
  - [x] Pull Request #15 has been reviewed and approved.
  - [x] Merged to `main` via commit `abc1234`.

- **[✅] Testing & Verification**
  - [x] Unit tests for `Beer`, `Brand`, and `Tasting` models are passing.
  - [x] `behat spec/features/beer-tracking.feature` runs successfully.
  - [x] `dredd` tests against the updated API spec are all passing.
  - [x] The CI pipeline for Pull Request #15 is **green**.

**Conclusion: The "Beer Tracking" feature is considered DONE.**