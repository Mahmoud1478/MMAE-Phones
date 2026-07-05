{{-- MMAE Phones Guidelines for AI Code Assistants --}}
{{-- Source: https://github.com/Mahmoud1478/MMAE-Phones --}}
{{-- License: MIT | (c) Mahmoud Mostafa --}}

## MMAE Phones

- `mmae/phones` validates and normalizes phone numbers for **209 countries**. Each configured country in `config/phones.php` ships four things: a `{CODE}Phone` class (validate/normalize), a `{CODE}PhoneRule` (Laravel `ValidationRule` with `exists`/`unique`), a `{CODE}Placeholder` (UI hints/masks), and `CountryDetector` support (resolve country from an international number).
- Always activate the `phones-development` skill when working with phone validation, normalization, the `{CODE}Phone`/`Phone` classes, `{CODE}PhoneRule`/`PhoneRule` validation rules, `CountryDetector`, `{CODE}Placeholder`/`Placeholder`, or bulk phone imports.
- Pick the entry point by what you have: a **known** country → `{CODE}Phone` + `{CODE}PhoneRule`; a country that **varies per request** → generic `Phone` / `PhoneRule` with an explicit code; an international number of **unknown** country → `CountryDetector`; a form/UI hint → `{CODE}Placeholder` / `Placeholder`.
- Namespaces: phones `MMAE\Phones\Phones\`, rules `MMAE\Phones\Rules\`, placeholders `MMAE\Phones\Placeholders\`; the generics are `MMAE\Phones\Phone`, `MMAE\Phones\Rules\PhoneRule`, `MMAE\Phones\Placeholders\Placeholder`, and `MMAE\Phones\CountryDetector`.
- Zero-config: config, the detector index, and translations all load automatically — validation, normalization, and detection work the moment the package is installed. Nothing to publish to start.
- Always normalize before persisting and validate first: `toString()` returns `''` for an invalid number, so gate on `isValid()`/`isNotValid()` (or the rule). Store one canonical form (`->withPlus()->toString()`) and assert **that** form in tests, not the raw input.
- Store the canonical form; for **display only**, use `format()` with braced tokens (`{key}`, `{local}`, `{provider}`, `{digits}`) — e.g. `format('+{key} {provider}-{digits}')`. Any other text is literal (no escaping), and it returns `''` when invalid.
