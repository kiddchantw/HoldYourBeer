# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2025-12-17

### Added
- **Shops Integration**:
  - New `Shop` model and database table.
  - `GET /api/v1/shops/suggestions` endpoint for autocomplete.
  - `shop_name` field in Beer creation API (auto-creates shops).
  - `tasting_logs.shop_id` to track personal purchase locations.
- **Frontend**:
  - Two-step beer creation flow in Livewire.
  - Autocomplete for Brands, Beers, and Shops.
  - Mobile-responsive design for complex forms.

### Changed
- Refactored `CreateBeer` component to use a 2-step process.
- Updated `POST /api/v1/beers` to accept `shop_name`.
