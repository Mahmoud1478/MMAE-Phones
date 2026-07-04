# Changelog

All notable changes to `mmae/phones` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Country detection** (`MMAE\Phones\CountryDetector`) — resolve which
  configured country an international number belongs to from its dialing key
  alone, for bulk imports where numbers arrive with a `+CC` / `00CC` / bare code
  but no ISO country.
  - `detect($number)` returns every matching code in config order (a shared
    dialing code like `+1` yields all NANP territories); `detectFirst()` takes
    the first, or `null`. A local/trunk-`0` number carries no country and
    returns `[]`.
  - Built for volume: a length-first index jumps straight to same-length
    candidates, then walks a dialing-code trie one digit at a time, so an
    impossible length is rejected before any work. The index is loaded once and
    cached in memory — the hot path is a single array probe, no `config()` per
    call. Measured ~1.4 µs per number (~700k detections/sec, ~1.5 s per million)
    on PHP 8.4 with the precompiled index.
  - `flush()` drops the cached index after runtime config changes.
- **`phones:build-lookup` command** — compiles `config/phones.php` into a
  ready-to-load `config/phone-lookup.php` detection index (via
  `CountryDetector::compileIndex()`), so detection skips the per-process compile.
  The package ships a lookup baked from its bundled schema; re-run after
  publishing and extending the config. Without the file, detection falls back to
  compiling from `config('phones')` at runtime.
- **Validation rules.** A `{CODE}PhoneRule` for every one of the 209 supported
  countries (`MMAE\Phones\Rules\`), each implementing Laravel's `ValidationRule`
  with its locale locked, plus a generic `PhoneRule::make($countryCode)` for
  per-request country codes.
- **Base rule API** (`MMAE\Phones\Base\BasePhoneRule`) — chainable modifiers:
  - `message()` to override the invalid-format message.
  - `exists()` / `unique()` database checks that match every accepted shape of
    the number (`$phone->all()`), with nullable `column`/`ignoreColumn`/`message`
    parameters and an `ignore` value for updates.
  - `nullable()` / `allowEmpty()` to skip checks on null/empty values, and
    `required()` / `absent()` to toggle them back.
  - `validateUsing()` full-control callback receiving
    `($phone, $attribute, $value, RuleConfig $config, $fail)`.
- **Config DTOs** under `MMAE\Phones\Configs\` (`RuleConfig`, `FormatConfig`,
  `NullableConfig`, `AllowEmptyConfig`, `ExistsConfig`, `UniqueConfig`) and the
  default flow in `MMAE\Phones\Defaults\Rules`.
- **Translations.** `phones` translation namespace with English and Arabic
  `validation.php` (`phone`, `required`, `exists`, `unique` keys), loaded by the
  service provider and publishable via the `mmae::phones-lang` tag. Messages
  follow `app()->getLocale()`.

### Changed

- Service provider now loads and publishes package translations.
- Expanded country coverage to 209 countries (one `{CODE}Phone` per config entry).

## [0.1.0]

### Added

- Initial release: `BasePhone`, per-country `{CODE}Phone` classes, the generic
  `Phone` class, and `config/phones.php`.
