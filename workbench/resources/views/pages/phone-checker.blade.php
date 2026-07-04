<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use MMAE\Phones\Configs\PlaceholderData;
use MMAE\Phones\CountryDetector;
use MMAE\Phones\Phone;
use MMAE\Phones\Placeholders\Placeholder;

new #[Title('Phone Checker')] class extends Component
{
    /** Full international number, with dialing key (+CC…, 00CC…, or bare CC…). */
    public string $number = '';

    /** Country the user picked among several sharing a dialing key (e.g. +1 NANP). */
    public ?string $selected = null;

    /** Reset the pick whenever the number changes so it can't stick to a stale country. */
    public function updatedNumber(): void
    {
        $this->selected = null;
    }

    /**
     * ISO codes of every country the number could belong to (config order).
     * A shared dialing key (every NANP country is +1) yields several.
     *
     * @return list<string>
     */
    #[Computed]
    public function detected(): array
    {
        return $this->number !== '' ? CountryDetector::detect(trim($this->number)) : [];
    }

    /** The active country: the user's pick if still valid, else the first match. */
    #[Computed]
    public function country(): ?string
    {
        if ($this->selected !== null && in_array($this->selected, $this->detected, true)) {
            return $this->selected;
        }

        return $this->detected[0] ?? null;
    }

    /** Full placeholder description of the detected country. */
    #[Computed]
    public function info(): ?PlaceholderData
    {
        return $this->country ? Placeholder::make($this->country)->extract() : null;
    }

    /** The entered number resolved against the detected country. */
    #[Computed]
    public function phone(): ?Phone
    {
        return $this->country ? Phone::make(trim($this->number), $this->country) : null;
    }

    #[Computed]
    public function valid(): ?bool
    {
        return $this->phone?->isValid();
    }

    /**
     * Regex segments (provider + subscriber digits) of a valid number.
     *
     * @return array<int|string, string>
     */
    #[Computed]
    public function segments(): array
    {
        return $this->valid ? $this->phone->segments() : [];
    }

    /**
     * Every accepted shape of a valid number (local, 00, +, …).
     *
     * @return list<string>
     */
    #[Computed]
    public function shapes(): array
    {
        return $this->valid ? $this->phone->all() : [];
    }
};
?>

<div class="mx-auto max-w-2xl px-6 py-12">
    <h1 class="text-2xl font-bold tracking-tight">mmae/phones — Country Detector</h1>
    <p class="mt-1 text-sm text-gray-500">
        Paste a full international number (with dialing key). The country is detected and its full
        placeholder schema is shown.
    </p>

    <div class="mt-8">
        <label class="block text-sm font-medium text-gray-700">International phone number</label>
        <input type="text" wire:model.live.debounce.300ms="number" placeholder="+201012345678"
            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 font-mono focus:border-indigo-500 focus:ring-indigo-500">
        <p class="mt-1 text-xs text-gray-400">Accepts <code>+CC…</code>, <code>00CC…</code> or bare <code>CC…</code>.</p>
    </div>

    @if ($number !== '')
        @if ($this->detected === [])
            <div class="mt-6 rounded-lg bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                No country detected. Enter the number in international form, including its dialing key.
            </div>
        @else
            {{-- Detected countries — a shared key (+1 NANP) lists many; click to switch. --}}
            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-500">Detected:</span>
                @foreach ($this->detected as $code)
                    <button type="button" wire:click="$set('selected', '{{ $code }}')"
                        @class([
                            'rounded-full px-2.5 py-0.5 text-xs font-semibold transition',
                            'bg-indigo-600 text-white' => $code === $this->country,
                            'bg-gray-100 text-gray-600 hover:bg-gray-200' => $code !== $this->country,
                        ])>{{ $code }}</button>
                @endforeach
                @if (count($this->detected) > 1)
                    <span class="text-xs text-gray-400">{{ count($this->detected) }} share +{{ $this->info->key }} — pick one</span>
                @endif
            </div>

            {{-- Validity of the entered number against the primary country --}}
            <div class="mt-4">
                <span @class([
                    'inline-block rounded-lg px-3 py-1.5 text-sm font-medium',
                    'bg-green-50 text-green-800' => $this->valid,
                    'bg-red-50 text-red-800' => ! $this->valid,
                ])>
                    @if ($this->valid)
                        Valid {{ $this->country }} number — provider {{ $this->segments['provider'] ?? '' }},
                        {{ $this->segments['digits'] ?? '' }}
                    @else
                        Detected {{ $this->country }} by dialing key, but the number fails its format.
                    @endif
                </span>
            </div>

            {{-- Full country placeholder info --}}
            @php($info = $this->info)
            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        {{ $info->code }} — country placeholder
                    </h2>
                </div>
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Dialing key</dt>
                        <dd class="col-span-2 font-mono text-gray-900">+{{ $info->key }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Trunk (local) key</dt>
                        <dd class="col-span-2 font-mono text-gray-900">{{ $info->localKey !== '' ? $info->localKey : '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Providers</dt>
                        <dd class="col-span-2 flex flex-wrap gap-1">
                            @foreach ($info->providers as $prefix)
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-700">{{ $prefix }}</span>
                            @endforeach
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Subscriber digits</dt>
                        <dd class="col-span-2 text-gray-900">
                            {{ $info->digitsMin === $info->digitsMax ? $info->digitsMin : "{$info->digitsMin}–{$info->digitsMax}" }}
                            <span class="ml-1 font-mono text-gray-400">{{ $info->digitsMask() }}</span>
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Format (masked)</dt>
                        <dd class="col-span-2 space-y-0.5 font-mono text-gray-900">
                            <div>{{ $info->internationalFormat() }}</div>
                            <div class="text-gray-500">{{ $info->localFormat() }}</div>
                            <div class="text-gray-400">{{ $info->bareFormat() }}</div>
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 px-5 py-3">
                        <dt class="font-medium text-gray-500">Example</dt>
                        <dd class="col-span-2 space-y-0.5 font-mono text-gray-900">
                            <div>{{ $info->international() }}</div>
                            <div class="text-gray-500">{{ $info->local() }}</div>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Accepted shapes of the entered number --}}
            @if ($this->shapes !== [])
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500">Accepted shapes of this number</h3>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($this->shapes as $shape)
                            <span class="rounded bg-indigo-50 px-2 py-0.5 font-mono text-xs text-indigo-700">{{ $shape }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>
