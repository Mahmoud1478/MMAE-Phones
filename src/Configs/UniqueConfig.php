<?php

declare(strict_types=1);

namespace MMAE\Phones\Configs;

use MMAE\Phones\Base\BasePhone;

/**
 * Uniqueness-check settings for a rule run: the number must not already exist in
 * `table`.`column`, matched against every accepted shape ({@see BasePhone::all()}).
 * Disabled by default (passes).
 *
 * @internal
 */
final readonly class UniqueConfig
{
    /**
     * @param  bool  $enabled  whether the uniqueness check applies
     * @param  string  $table  table the number must be unique in
     * @param  string|null  $column  column to match; defaults to the attribute name when null
     * @param  mixed  $ignore  record id to exclude, e.g. the current row on update
     * @param  string  $ignoreColumn  column the `$ignore` id is matched on
     * @param  string  $message  translation key used when the number already exists
     */
    public function __construct(
        public bool $enabled,
        public string $table,
        public ?string $column,
        public mixed $ignore,
        public string $ignoreColumn,
        public string $message,
    ) {}
}
