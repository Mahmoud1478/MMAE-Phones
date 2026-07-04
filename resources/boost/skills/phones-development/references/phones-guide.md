# MMAE Phones — Usage Guide

Complete reference for **using** `mmae/phones` in a Laravel app: validating and normalizing phone numbers for **209 countries**, with drop-in validation rules, UI placeholders, and an international-number country detector.

---

## 1. How it works

A phone number can arrive in many shapes — local (`01012345678`), `00` international (`00201012345678`), `+` international (`+201012345678`), or bare (`201012345678`). This package parses any accepted shape for a given country, tells you whether it's valid, and **normalizes it to one canonical string** so you store exactly one form.

Every country code (`EG`, `SA`, `US`, …) is backed by four coordinated tools, all derived from `config/phones.php`:

| Tool | Class | Use it to |
|---|---|---|
| **Phone** | `MMAE\Phones\Phones\{CODE}Phone` | check validity and normalize a number |
| **Rule** | `MMAE\Phones\Rules\{CODE}PhoneRule` | validate in `$request->validate()`, with `exists`/`unique` |
| **Placeholder** | `MMAE\Phones\Placeholders\{CODE}Placeholder` | render input hints, examples, format masks |
| **Detector** | `MMAE\Phones\CountryDetector` | recover the country of an *international* number |

Each tool has a **country-locked** form (`EGPhone`, `EGPhoneRule`, `EGPlaceholder`) for when the country is fixed, and a **generic** form (`Phone`, `PhoneRule`, `Placeholder`) that takes an explicit `$countryCode` for when it varies at runtime.

Under the hood a country's regex is built from its dialing `key` (e.g. `20`), national trunk `local_key` (e.g. `0`), and a `provider`/`digits` `pattern`. You never touch that — you call the classes.

List every available country code in the current app:

```bash
php artisan config:show phones
```

---

## 2. Install

```bash
composer require mmae/phones
```

Zero-config: `config/phones.php`, the detector index, and translations all load automatically. The provider is auto-discovered. **Nothing to publish — validation, normalization, and detection work the moment it's installed.**

---

## 3. Phone API (`{CODE}Phone` / `Phone`)

All phone classes extend `MMAE\Phones\Base\BasePhone` (implements `Stringable` and `Arrayable`).

```php
use MMAE\Phones\Phones\EGPhone;   // country-locked
use MMAE\Phones\Phone;            // generic, takes an explicit code

$phone = EGPhone::make('01012345678');
$phone = Phone::make('01012345678', 'EG');
```

| Method | Returns | Purpose |
|---|---|---|
| `isValid()` | `bool` | number matches the country's format |
| `isNotValid()` | `bool` | inverse of `isValid()` |
| `toString()` / `(string) $phone` | `string` | normalized `key+provider+digits`; **`''` if invalid** |
| `all()` | `array` | every accepted key-prefixed variant (`0`, `00`, `+`, bare) |
| `segments()` | `array` | named capture groups (`key`, `provider`, `digits`) |
| `withPlus()` | `static` | make output use a `+` prefix |
| `withoutPlus()` | `static` | switch back to no `+` prefix (default) |
| `number()` | `string` | the original, unmodified input |
| `toArray()` | `array` | alias for `all()` |
| `config(?string $key = null)` | `mixed` | read the country's config array, or one key |

Key behaviors:

- **`toString()` returns `''` for an invalid number.** Always gate on `isValid()`/`isNotValid()` (or validate with the rule) before persisting.
- **`withPlus()` / `withoutPlus()` flip a static flag** shared by the class, not per-instance state — set it right before casting to string.

Normalization — every accepted shape collapses to one canonical form:

```php
EGPhone::make('01012345678')->toString();     // 201012345678
EGPhone::make('00201012345678')->toString();  // 201012345678
EGPhone::make('+201012345678')->toString();   // 201012345678
EGPhone::make('201012345678')->toString();    // 201012345678
EGPhone::make('01099')->toString();           // '' (invalid)

EGPhone::make('01012345678')->withPlus()->toString();   // +201012345678
```

Manual check without the rule:

```php
if (EGPhone::make($data['phone'])->isNotValid()) {
    return back()->withErrors(['phone' => 'wrong format']);
}
```

Inspect the parts:

```php
EGPhone::make('01012345678')->segments();
// ['key' => '20', 'provider' => '10', 'digits' => '12345678']

EGPhone::make('01012345678')->all();
// every accepted shape: ['01012345678', '0020101...', '+2010...', '2010...']
```

---

## 4. Validation rules (`{CODE}PhoneRule` / `PhoneRule`)

Every country ships a `{CODE}PhoneRule` (`MMAE\Phones\Rules\`) implementing Laravel's `ValidationRule`. Use directly in `validate()` / `Validator::make()`.

```php
use MMAE\Phones\Rules\EGPhoneRule;

$request->validate([
    'phone' => ['required', EGPhoneRule::make()],
]);
```

Country varies per request → generic `PhoneRule` with an explicit code:

```php
use MMAE\Phones\Rules\PhoneRule;

$request->validate([
    'phone' => [PhoneRule::make($user->country_code)],
]);
```

### Fluent modifiers

| Method | Purpose |
|---|---|
| `make()` | fluent constructor (generic `PhoneRule::make($code)` takes the code) |
| `message(string $message)` | override the invalid-format message (literal or translation key) |
| `nullable(bool = true)` | pass and skip every check when the value is `null` |
| `allowEmpty(bool = true)` | pass and skip every check when the value is `''` |
| `required()` | undo `nullable()`/`allowEmpty()` so null/empty are rejected |
| `absent()` | mirror of `required()` — allow both null and empty to skip |
| `exists(string $table, ?string $column = null, ?string $message = null)` | require the number to already exist in a table |
| `unique(string $table, ?string $column = null, mixed $ignore = null, ?string $ignoreColumn = null, ?string $message = null)` | require the number to be unique in a table |
| `validateUsing(Closure $callback)` | take full control of the flow |

### Existence & uniqueness

`exists()` / `unique()` match against **every accepted shape** (`$phone->all()`), so a number stored in any form is found regardless of the submitted shape. The format check runs first — **the DB is never queried for an invalid number**.

```php
EGPhoneRule::make()->exists('users');                    // must already exist
EGPhoneRule::make()->unique('users');                    // must be unique
EGPhoneRule::make()->unique('users', ignore: $user->id); // ignore current row on update
EGPhoneRule::make()->exists('contacts', 'mobile', 'No such contact.'); // custom column + message
```

Defaults: `$column` → `phone`, `$ignoreColumn` → `id`, `$message` → the package translation key.

### Null & empty

```php
EGPhoneRule::make()->nullable();     // null  → pass
EGPhoneRule::make()->allowEmpty();   // ''    → pass
EGPhoneRule::make()->required();     // re-reject null/empty
```

Laravel already skips non-implicit rules on empty strings, so `allowEmpty()` is only observable when driving the rule directly. A present `null` reaches the rule and fails with `phones::validation.required` unless `nullable()`.

> A non-string, non-int, non-null value (e.g. an array) throws a `RuntimeException` rather than failing validation.

### Full-control callback

`validateUsing()` replaces the entire built-in flow. It receives the resolved phone, attribute, raw value, a `RuleConfig`, and `$fail`.

```php
use MMAE\Phones\Base\BasePhone;
use MMAE\Phones\Configs\RuleConfig;

EGPhoneRule::make()->exists('users')->validateUsing(
    function (BasePhone $phone, string $attribute, mixed $value, RuleConfig $config, Closure $fail) {
        if ($phone->isNotValid()) {
            $fail(trans($config->format->message));

            return;
        }

        if ($config->exists->enabled && ! MyLookup::has($phone->all())) {
            $fail(trans($config->exists->message));
        }
    }
);
```

`RuleConfig` is a readonly DTO exposing `format`, `nullable`, `allowEmpty`, `exists`, `unique`. **Its messages are raw translation keys — wrap in `trans()`.**

### Translations

Messages resolve from the `phones` namespace (`lang/{locale}/validation.php`), keyed by `phone`, `required`, `exists`, `unique`. English + Arabic ship; messages follow `app()->getLocale()`. To add locales or reword:

```bash
php artisan vendor:publish --tag=mmae::phones-lang
```

Copies to `lang/vendor/phones/{locale}/validation.php`.

---

## 5. Country detection (`CountryDetector`)

Use when numbers arrive in **international form only** — a dialing code, no ISO country attached (spreadsheets of `+2010…`, `+9665…`, `+15551234567`).

```php
use MMAE\Phones\CountryDetector;

CountryDetector::detect('+201000000000');   // ['EG']
CountryDetector::detect('00201000000000');  // ['EG'] — 00 / bare / spaces & dashes accepted
CountryDetector::detect('+15551234567');    // ['US', 'CA', 'PR', ...] — every country sharing +1
CountryDetector::detect('01000000000');     // [] — local form, no dialing code
CountryDetector::detect('+99900000');       // [] — no country has this code + length

CountryDetector::detectFirst('+201000000000'); // 'EG' — first match, or null
```

Constraints:

- **International-only by design.** A local/trunk-`0` number carries no country → `[]`. There you already know the country; use `Phone`/`{CODE}Phone`.
- **Shared dialing codes return several codes.** Every NANP territory is `+1`, so `detect()` returns all matches in config order — disambiguate with other row data. `detectFirst()` takes the first; only safe when you don't need exactly one country.
- **Fast (bulk-ready):** ~1.5–1.8 µs per number; a 1M-row import spends under ~2 s in detection.

---

## 6. Placeholders (`{CODE}Placeholder` / `Placeholder`)

Describe the **shape** of a valid number (accepted provider prefixes + subscriber length) for input hints, example numbers, and masks.

```php
use MMAE\Phones\Placeholders\EGPlaceholder;

$data = EGPlaceholder::make()->extract();   // MMAE\Phones\Configs\PlaceholderData

$data->localFormat();          // '01[0,1,2,5]XXXXXXXX' — every provider, local form
$data->internationalFormat();  // '+201[0,1,2,5]XXXXXXXX'
$data->local();                // '010XXXXXXXX' — canonical (first) provider
$data->international();         // '+2010XXXXXXXX'
$data->providers;              // ['10', '11', '12', '15']
$data->digitsMin;              // 8
```

Custom mask character (default `X`):

```php
EGPlaceholder::make('#')->extract()->localFormat();   // '01[0,1,2,5]########'
```

Country at runtime → generic `Placeholder`:

```php
use MMAE\Phones\Placeholders\Placeholder;

Placeholder::make($user->country_code)->extract()->internationalFormat();
```

### PlaceholderData API

| Member | Returns | Purpose |
|---|---|---|
| `code`, `key`, `localKey` | `string` | ISO code, dialing code, national trunk prefix |
| `providers` | `array` | every accepted provider prefix |
| `digitsMin` / `digitsMax` | `int` | subscriber-part length range |
| `provider()` | `string` | canonical (first) provider prefix |
| `digitsMask()` | `string` | masked subscriber part, e.g. `XXXXXXXX` |
| `bare()` / `local()` / `international(bool $plus = true)` | `string` | one example number per shape |
| `bareFormat()` / `localFormat()` / `internationalFormat(bool $plus = true)` | `string` | format mask covering **every** provider |
| `providerMask()` | `string` | all providers collapsed, e.g. `1[0,1,2,5]` |
| `examples()` | `array` | `{provider, bare, local, international}` per provider |
| `toArray()` | `array` | the whole thing as a nested array |

---

## 7. Use cases

### 7.1 Single fixed-country field

```php
$request->validate(['phone' => ['required', EGPhoneRule::make()]]);
$phone = EGPhone::make($request->phone)->toString();      // 201012345678
```

### 7.2 Users register with any country

```php
use Illuminate\Validation\Rule;
use MMAE\Phones\Phone;
use MMAE\Phones\Rules\PhoneRule;

$data = $request->validate([
    'country_code' => ['required', Rule::in(array_keys(config('phones')))],
    'phone' => ['required', PhoneRule::make($request->country_code)],
]);

$data['phone'] = Phone::make($data['phone'], $data['country_code'])->withPlus()->toString();
```

### 7.3 No duplicate phone, ignoring current row on edit

```php
EGPhoneRule::make()->unique('users', ignore: $user->id);
```

### 7.4 Send SMS in `+` form

```php
$sms->to(Phone::make($user->phone, $user->country_code)->withPlus()->toString())->send();
```

### 7.5 Show which country a stored number belongs to

```php
CountryDetector::detectFirst($user->phone);   // 'EG' | null
```

### 7.6 Bulk import — detect → validate → normalize

Many owners, each with international-only numbers, validated **before** anything is saved. Stream/chunk a large file so memory stays flat.

```php
use Illuminate\Support\Facades\DB;
use MMAE\Phones\CountryDetector;
use MMAE\Phones\Phone;

$valid = [];
$failed = [];

foreach ($rows as $owner) {                 // iterable<array{name: string, phones: list<string>}>
    $normalized = [];

    foreach ($owner['phones'] as $raw) {
        $code = CountryDetector::detectFirst($raw);   // country from the dialing key
        $phone = $code ? Phone::make($raw, $code) : null;

        if ($phone === null || $phone->isNotValid()) {
            $failed[] = ['name' => $owner['name'], 'phone' => $raw];

            continue;
        }

        $normalized[] = $phone->withPlus()->toString(); // canonical +CC form
    }

    if ($normalized !== []) {
        $valid[] = ['name' => $owner['name'], 'phones' => $normalized];
    }
}

DB::transaction(function () use ($valid) {
    foreach ($valid as $owner) {
        // Owner::create([...]) + attach $owner['phones']
    }
});

report_invalid($failed);   // surface rejects instead of silently dropping them
```

> If the import must land on exactly one country, disambiguate a shared `+1` with other row data (a country column) rather than blindly taking `detectFirst()`.

---

## 8. Eloquent integration

### 8.1 Migration column

Store the canonical international form; index it if you look numbers up.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('country_code', 2)->default('EG');   // key into config('phones')
    $table->string('phone')->index();                    // canonical +CC… string
    $table->timestamps();
});
```

### 8.2 Custom cast — normalize on write

Keeps the model clean: assign any shape, store the canonical `+` form. `Phone::make()` needs a country, read from a sibling `country_code`.

```php
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use MMAE\Phones\Phone;

/**
 * @implements CastsAttributes<string, string>
 */
final class PhoneCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;   // already stored canonical
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $code = $attributes['country_code'] ?? $model->country_code;
        $phone = Phone::make((string) $value, $code);

        if ($phone->isNotValid()) {
            throw new \InvalidArgumentException("Invalid {$code} phone: {$value}");
        }

        return [$key => $phone->withPlus()->toString()];
    }
}
```

```php
// app/Models/User.php
protected function casts(): array
{
    return ['phone' => \App\Casts\PhoneCast::class];
}
```

> Validate in the request first (§4). The cast is a **last-line guard**, not a replacement for validation — it throws rather than producing friendly errors.

### 8.3 Accessor — expose the detected country

```php
use Illuminate\Database\Eloquent\Casts\Attribute;
use MMAE\Phones\CountryDetector;

protected function detectedCountry(): Attribute
{
    return Attribute::get(fn () => CountryDetector::detectFirst($this->phone));
}
```

---

## 9. FormRequest

Country-locked endpoint:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use MMAE\Phones\Rules\SAPhoneRule;

final class StoreSaudiUserRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'phone' => ['required', SAPhoneRule::make()->unique('users')],
        ];
    }
}
```

Multi-country endpoint (country is itself a field):

```php
use Illuminate\Validation\Rule;
use MMAE\Phones\Rules\PhoneRule;

/** @return array<string, mixed> */
public function rules(): array
{
    return [
        'country_code' => ['required', Rule::in(array_keys(config('phones')))],
        'phone' => ['required', PhoneRule::make($this->input('country_code'))->unique('users')],
    ];
}
```

---

## 10. API controller + resource

```php
// Controller — normalize on store
use MMAE\Phones\Phone;
use MMAE\Phones\Rules\PhoneRule;

public function store(Request $request)
{
    $data = $request->validate([
        'country_code' => ['required', \Illuminate\Validation\Rule::in(array_keys(config('phones')))],
        'phone' => ['required', PhoneRule::make($request->input('country_code'))->unique('users')],
    ]);

    $data['phone'] = Phone::make($data['phone'], $data['country_code'])->withPlus()->toString();

    return new UserResource(User::create($data));
}
```

```php
// UserResource — expose stored + detected forms
use MMAE\Phones\CountryDetector;

/** @return array<string, mixed> */
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'phone' => $this->phone,                                    // stored canonical +CC…
        'country' => $this->country_code
            ?? CountryDetector::detectFirst($this->phone),          // fall back to detection
    ];
}
```

---

## 11. Blade (non-Livewire) — placeholder hint

```blade
@php($ph = \MMAE\Phones\Placeholders\EGPlaceholder::make()->extract())

<label for="phone">Phone ({{ $ph->internationalFormat() }})</label>
<input id="phone" name="phone" placeholder="{{ $ph->local() }}" value="{{ old('phone') }}">
@error('phone') <small>{{ $message }}</small> @enderror
```

Country at runtime:

```blade
@php($ph = \MMAE\Phones\Placeholders\Placeholder::make($countryCode)->extract())
<input name="phone" placeholder="{{ $ph->local() }}">
```

---

## 12. Livewire

### 12.1 Validated form — normalize + de-duplicate

`PhoneRule` validates, `Rule::in(array_keys(config('phones')))` accepts any country, the value is normalized to `+` form before saving, `->unique('users', 'phone', $id)` matches every stored shape (ignoring the current row on edit).

```php
<?php
use Illuminate\Validation\Rule;
use Livewire\Component;
use MMAE\Phones\Phone;
use MMAE\Phones\Rules\PhoneRule;
use App\Models\User;

new class extends Component {
    public ?int $editingId = null;
    public string $country_code = 'EG';
    public string $phone = '';

    protected function rules(): array
    {
        return [
            'country_code' => ['required', Rule::in(array_keys(config('phones')))],
            'phone' => [
                PhoneRule::make($this->country_code)
                    ->unique('users', 'phone', $this->editingId),
            ],
        ];
    }

    public function save(): void
    {
        $this->validate();

        User::updateOrCreate(
            ['id' => $this->editingId],
            [
                'country_code' => $this->country_code,
                'phone' => Phone::make($this->phone, $this->country_code)->withPlus()->toString(),
            ],
        );
    }
};
?>

<form wire:submit="save">
    <select wire:model="country_code">
        @foreach (array_keys(config('phones')) as $code)
            <option value="{{ $code }}">{{ $code }}</option>
        @endforeach
    </select>

    <input type="text" wire:model="phone" placeholder="01012345678">
    @error('phone') <p>{{ $message }}</p> @enderror

    <button type="submit">Save</button>
</form>
```

### 12.2 Detector + placeholder card

Paste an international number → detect the country → render its `PlaceholderData`. A shared key (`+1`) lists all matches; the user picks which drives the card.

```php
<?php
use Livewire\Attributes\Computed;
use Livewire\Component;
use MMAE\Phones\Configs\PlaceholderData;
use MMAE\Phones\CountryDetector;
use MMAE\Phones\Phone;
use MMAE\Phones\Placeholders\Placeholder;

new class extends Component {
    public string $number = '';
    public ?string $selected = null;

    public function updatedNumber(): void
    {
        $this->selected = null;   // reset the pick when the number changes
    }

    /** @return list<string> every country sharing this dialing key */
    #[Computed]
    public function detected(): array
    {
        return $this->number !== '' ? CountryDetector::detect(trim($this->number)) : [];
    }

    #[Computed]
    public function country(): ?string
    {
        return in_array($this->selected, $this->detected, true)
            ? $this->selected
            : ($this->detected[0] ?? null);
    }

    #[Computed]
    public function info(): ?PlaceholderData
    {
        return $this->country ? Placeholder::make($this->country)->extract() : null;
    }

    #[Computed]
    public function valid(): ?bool
    {
        return $this->country
            ? Phone::make(trim($this->number), $this->country)->isValid()
            : null;
    }
};
?>

<div>
    <input type="text" wire:model.live.debounce.300ms="number" placeholder="+17875550123">

    @if ($this->detected)
        @foreach ($this->detected as $code)
            <button type="button" wire:click="$set('selected', '{{ $code }}')"
                @class(['font-bold' => $code === $this->country])>{{ $code }}</button>
        @endforeach

        @php($info = $this->info)
        <dl>
            <dt>Dialing key</dt> <dd>+{{ $info->key }}</dd>
            <dt>Trunk key</dt>   <dd>{{ $info->localKey ?: '—' }}</dd>
            <dt>Providers</dt>   <dd>{{ implode(', ', $info->providers) }}</dd>
            <dt>Digits</dt>      <dd>{{ $info->digitsMin }}–{{ $info->digitsMax }}</dd>
            <dt>Format</dt>      <dd>{{ $info->internationalFormat() }}</dd>
            <dt>Example</dt>     <dd>{{ $info->international() }}</dd>
        </dl>

        <p>{{ $this->valid ? "Valid {$this->country} number" : "Fails {$this->country} format" }}</p>
    @elseif ($number !== '')
        <p>No country detected — enter the number in international form (with dialing key).</p>
    @endif
</div>
```

---

## 13. Testing & validating the results

Confirm the package behaves as expected in **your** app before trusting it in production. Two levels: quick manual checks in tinker, and automated assertions in your test suite.

### 13.1 Quick manual check (tinker)

```bash
php artisan tinker
```

```php
use MMAE\Phones\Phones\EGPhone;
use MMAE\Phones\CountryDetector;

EGPhone::make('01012345678')->isValid();     // true
EGPhone::make('01012345678')->toString();    // "201012345678"
EGPhone::make('01099')->isValid();           // false
EGPhone::make('01099')->toString();          // "" — invalid yields empty
EGPhone::make('01012345678')->all();         // every accepted shape
CountryDetector::detect('+201012345678');    // ["EG"]
```

**What to eyeball:**
- valid input → non-empty canonical string, all shapes point at the same number;
- invalid input → `isValid() === false` **and** `toString() === ''`;
- `detect()` on an international number → the country you expect; on a local number → `[]`.

### 13.2 Automated tests (Pest / PHPUnit)

Test **your usage** — that your form/controller validates, normalizes, and de-dupes correctly. You don't need to re-test the package's own country data.

```php
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use MMAE\Phones\Phones\EGPhone;
use MMAE\Phones\Rules\EGPhoneRule;
use MMAE\Phones\CountryDetector;

it('normalizes every accepted shape to one canonical form', function (string $input) {
    expect(EGPhone::make($input)->toString())->toBe('201012345678');
})->with(['01012345678', '00201012345678', '+201012345678', '201012345678']);

it('treats an invalid number as empty', function () {
    $phone = EGPhone::make('01099');
    expect($phone->isNotValid())->toBeTrue()
        ->and($phone->toString())->toBe('');
});

it('passes the rule for a valid number and fails for garbage', function () {
    expect(Validator::make(['phone' => '01012345678'], ['phone' => [EGPhoneRule::make()]])->passes())->toBeTrue()
        ->and(Validator::make(['phone' => '01099'], ['phone' => [EGPhoneRule::make()]])->fails())->toBeTrue();
});

it('enforces unique across every stored shape', function () {
    User::factory()->create(['phone' => '+201012345678']);

    $v = Validator::make(
        ['phone' => '01012345678'],                     // different shape, same number
        ['phone' => [EGPhoneRule::make()->unique('users')]],
    );

    expect($v->fails())->toBeTrue();
});

it('detects the country of an international number', function () {
    expect(CountryDetector::detectFirst('+201012345678'))->toBe('EG');
});
```

### 13.3 Test an endpoint end-to-end

```php
it('stores the normalized phone', function () {
    $this->postJson('/api/users', [
        'name' => 'Sara',
        'country_code' => 'EG',
        'phone' => '01012345678',        // local form in
    ])->assertCreated();

    $this->assertDatabaseHas('users', [
        'phone' => '+201012345678',      // canonical form persisted
    ]);
});

it('rejects a malformed phone', function () {
    $this->postJson('/api/users', [
        'name' => 'Sara',
        'country_code' => 'EG',
        'phone' => '01099',
    ])->assertJsonValidationErrors('phone');
});
```

### 13.4 Assert on the DB shape, not the input shape

Because the value is normalized before saving, always assert the **canonical** string in `assertDatabaseHas`, never the raw input. Store one form (`+CC…`), assert that one form.

---

## 14. Cheat-sheet — common mistakes

- **Storing `toString()` of an unvalidated number** → you may persist `''`. Validate first (`isValid()` or the rule).
- **Expecting `detect()` on a local number** → returns `[]`. Detection is international-only; use `Phone` with a known code.
- **Blindly `detectFirst()` on a `+1` number** → may pick the wrong NANP country. Disambiguate with row data.
- **Assuming `withPlus()` is per-instance** → it flips a static flag; set it right before casting to string.
- **Asserting the input shape in tests** → assert the normalized canonical form the DB actually holds.
- **Passing an array/object to the rule** → throws `RuntimeException` (not a validation failure). Cast to string first.
