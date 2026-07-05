---
name: phones-development
description: Validate, normalize, and detect phone numbers with mmae/phones (209 countries). Use when working with phone validation, the {CODE}Phone/Phone classes, {CODE}PhoneRule/PhoneRule validation rules, CountryDetector, {CODE}Placeholder/Placeholder UI hints, or bulk phone imports in a Laravel app.
---

# MMAE Phones Development

`mmae/phones` validates and normalizes phone numbers for **209 countries**. Every entry in `config/phones.php` ships a matched set of four tools:

| Tool | Namespace | Purpose |
|---|---|---|
| `{CODE}Phone` | `MMAE\Phones\Phones\` | validate + normalize a number to one canonical form |
| `{CODE}PhoneRule` | `MMAE\Phones\Rules\` | Laravel `ValidationRule` (format + `exists`/`unique`) |
| `{CODE}Placeholder` | `MMAE\Phones\Placeholders\` | UI hints, example numbers, format masks |
| `CountryDetector` | `MMAE\Phones\CountryDetector` | resolve the country of an international number |

Generic variants (`MMAE\Phones\Phone`, `Rules\PhoneRule`, `Placeholders\Placeholder`) take an **explicit country code** as an argument, for when the country varies per request.

## Choosing the entry point

- **Known country** (a fixed field like a Saudi-only signup) → `{CODE}Phone` to normalize + `{CODE}PhoneRule` to validate.
- **Country varies per request** (multi-country registration with a `country_code` field) → generic `Phone::make($number, $code)` + `PhoneRule::make($code)`.
- **Unknown country, international number** (bulk CSV of `+2010…`, `+9665…`) → `CountryDetector::detect()` / `detectFirst()` to recover the country, then `Phone`.
- **UI hint / mask / example** → `{CODE}Placeholder` / `Placeholder`.

## Minimal patterns

Validate + normalize a known-country number:

```php
use MMAE\Phones\Phones\EGPhone;
use MMAE\Phones\Rules\EGPhoneRule;

$data = $request->validate([
    'phone' => ['required', EGPhoneRule::make()->unique('users')],
]);

// normalize any accepted shape (local 0, 00, +, bare) to one canonical form
$data['phone'] = EGPhone::make($data['phone'])->toString();
```

Country varies per request:

```php
use MMAE\Phones\Phone;
use MMAE\Phones\Rules\PhoneRule;

$request->validate(['phone' => [PhoneRule::make($user->country_code)]]);

$phone = Phone::make($user->phone, $user->country_code);
$sms->to($phone->withPlus()->toString())->send();   // +CC… form
```

Detect the country of an international number:

```php
use MMAE\Phones\CountryDetector;

CountryDetector::detect('+201000000000');    // ['EG']
CountryDetector::detect('+15551234567');     // ['US', 'CA', 'PR', ...] — shared +1
CountryDetector::detectFirst('+201000000000'); // 'EG' | null
CountryDetector::detect('01000000000');      // [] — local form has no country
```

## Rules that matter (read the reference before non-trivial work)

- **Detection is international-only by design.** A local/trunk-`0` number (`01000000000`) returns `[]` — there's no dialing code to detect. Use `Phone` with an explicit code instead.
- **A shared dialing code returns several codes.** Every NANP territory is `+1`, so `detect()` returns all matches in config order; disambiguate with other row data, don't blindly take `detectFirst()`.
- **`exists()`/`unique()` match every stored shape** (`$phone->all()`), so a number saved as local/`00`/`+`/bare is found regardless of how the new value was submitted. The format check runs first — the DB is never queried for an invalid number.
- **`withPlus()`/`withoutPlus()` mutate a static flag**, not per-instance state — set right before casting to string.
- **A phone's country is fixed at construction** — pass the code to `make()`; there is no setter to swap it afterward.
- **Config & translations merge automatically** — no publish to start. Publish only to customize; **regenerate `config/phone-lookup.php` with `php artisan phones:build-lookup` after any `config/phones.php` change**, or detection silently misses new countries.
- **`toString()` returns `''` for an invalid number** — always check `isValid()`/`isNotValid()` (or validate with the rule) before persisting.
- **`format()` is display-only** — braced tokens `{key}`/`{local}`/`{provider}`/`{digits}`, other text literal (no escaping), `''` when invalid. Persist the canonical `toString()` form; format only for output.

## Reference

Full API + copy-paste recipes live in [`references/phones-guide.md`](references/phones-guide.md). Jump to what you need:

- §1 — how it works (the four tools, canonical normalization).
- §3–6 — complete `BasePhone` / `BasePhoneRule` / `CountryDetector` / `PlaceholderData` APIs.
- §7 — use cases (single-country, multi-country, unique, SMS, detect-stored, bulk import).
- §8 — Eloquent: migration column, custom `PhoneCast`, detected-country accessor.
- §9 — FormRequest (country-locked + multi-country).
- §10 — API controller + resource.
- §11 — Blade placeholder hint (non-Livewire).
- §12 — Livewire (validated form + detector/placeholder card).
- §13 — **testing & validating results** (tinker checks, Pest asserts, endpoint tests, assert canonical DB shape).
- §14 — common-mistakes cheat-sheet.

**Reach for the reference before any non-trivial task** — the recipes are drop-in and encode the gotchas.
