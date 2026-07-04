# Changelog

All notable changes to `mmae/phones` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-07-05

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
    call. Measured ~1.8 µs per number (~570k detections/sec, ~1.8 s per million)
    on PHP 8.4 over a real 50/50 mix across every country (`detectFirst()` ~1.5 µs);
    reproduce with the workbench `phones:benchmark` harness, which streams a
    generated `--valid` ratio mix (or a `phones:dataset --file` CSV) through a
    chunked generator so memory stays flat at any size.
  - Shared dialing codes resolve by lookup, not regex-per-country: for a code
    used by many countries (e.g. every `+1` NANP territory), literal provider
    prefixes bake into a `provider → countries` map so one hash lookup replaces
    ~20 `preg_match` calls — ~3× faster on `+1`, identical results. Non-literal
    patterns stay as regex.
  - The hot path trusts the baked index shape: `detect()` drops per-call
    `is_array`/`is_int`/`is_string` guards (a further ~10–15%) and keeps only
    control-flow checks. **`config/phone-lookup.php` is a required build artifact
    — never hand-edit it, and regenerate with `phones:build-lookup` after any
    `config/phones.php` change.** There is no runtime fallback: a missing index
    throws a `RuntimeException` on first `detect()` (checked once, off the hot
    path) pointing you to `phones:build-lookup`; a malformed or hand-edited index
    is undefined behavior and can `TypeError` mid-detection.
  - `flush()` drops the cached index after runtime config changes.
- **`phones:build-lookup` command** — compiles `config/phones.php` into a
  ready-to-load `config/phone-lookup.php` detection index (via
  `LookupCompiler::compile()`), so detection skips the per-process compile.
  The package ships a lookup baked from its bundled schema; re-run after
  publishing and extending the config. The baked index is required — detection
  has no runtime fallback and throws if it is missing.
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
- **Laravel Boost integration** (`resources/boost/`) — the package now ships
  AI-assistant guidance that [Laravel Boost](https://github.com/laravel/boost)
  auto-discovers from the installed package, no setup required:
  - `guidelines/core.blade.php` — an always-on summary that teaches the four
    tools (`{CODE}Phone` / `{CODE}PhoneRule` / `{CODE}Placeholder` /
    `CountryDetector`), how to pick an entry point, and the validate-then-
    normalize rule.
  - `skills/phones-development/` — an on-demand skill (`SKILL.md` + a full
    `references/phones-guide.md`) covering every API, use cases, Eloquent /
    FormRequest / API / Livewire recipes, and how to test and validate results.
  - Discovered via `resources/boost/{guidelines,skills}` and shipped in the dist
    (not `export-ignore`d), so `composer require mmae/phones` alone surfaces it in
    any Boost-enabled project.
- **README — Livewire examples (§9).** Two single-file components: a form using
  `PhoneRule` validation + `Phone` normalization + `unique` de-duplication, and a
  country-detector card using `CountryDetector` + `Placeholder`, including `+1`
  NANP multi-match handling. Illustrated with screenshots under `docs/`
  (dev-only, `export-ignore`d).

### Changed

- Service provider now loads and publishes package translations.
- Expanded country coverage to 209 countries (one `{CODE}Phone` per config entry).
- Service provider exposes package identity via `MMAEPhonesServiceProvider::NAME`
  and `::VERSION` constants.

### Removed

- **BREAKING: `BasePhone::for()` removed.** A phone's country is now fixed at
  construction — pass the code to `make()` (or use a country-locked
  `{CODE}Phone` subclass). There is no setter to swap the country on an existing
  instance, so the internal country code can no longer be mutated after the
  object is built. The `for()` autocomplete entry is also dropped from the
  generated `.phpstorm.meta.php`.

### Performance

- **`BasePhone` memoizes its regex and match.** The compiled per-country regex
  and the `preg_match` result are now computed once per instance instead of on
  every call. Routing `isValid()` through `segments()` removes the duplicate
  `preg_match` that `toString()` and `all()` previously triggered, roughly
  halving regex executions on the hot path. Results stay deterministic because
  the number is `readonly` and the country is fixed at construction.

## [0.1.0]

### Added

- Initial release: `BasePhone`, per-country `{CODE}Phone` classes, the generic
  `Phone` class, and `config/phones.php`.
