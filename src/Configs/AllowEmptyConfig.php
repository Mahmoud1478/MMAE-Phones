<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

/**
 * Empty-string handling settings for a single rule run.
 *
 * Part of {@see RuleConfig}. When `enabled`, an empty string `''` passes and
 * skips every other check; otherwise `''` fails with `message`.
 *
 * @internal
 */
final readonly class AllowEmptyConfig
{
    /**
     * @param  bool  $enabled  when true, an empty string passes and skips every other check
     * @param  string  $message  translation key used when an empty value is rejected
     */
    public function __construct(
        public bool $enabled,
        public string $message,
    ) {}
}
