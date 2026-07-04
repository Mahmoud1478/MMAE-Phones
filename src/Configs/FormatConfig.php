<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhoneRule;

/**
 * Format (regex) check settings for a single rule run.
 *
 * Part of {@see RuleConfig}. The format check always runs in the default flow;
 * `enabled` exists so a custom {@see BasePhoneRule::validateUsing()}
 * callback can branch on it.
 *
 * @internal
 */
final readonly class FormatConfig
{
    /**
     * @param  bool  $enabled  whether the format (regex) check applies
     * @param  string  $message  translation key used when the format is invalid
     */
    public function __construct(
        public bool $enabled,
        public string $message,
    ) {}
}
