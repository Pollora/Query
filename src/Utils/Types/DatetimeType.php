<?php

declare(strict_types=1);

namespace Pollora\Query\Utils\Types;

final class DatetimeType implements ValueTypeContract
{
    public const string DATE = 'DATE';

    public const string DATETIME = 'DATETIME';

    public const string TIME = 'TIME';

    private const string DATE_REGEX = '/^\d{4}-\d{2}-\d{2}$/';

    private const string TIME_REGEX = '/^\d{2}:\d{2}:\d{2}$/';

    private const string DATETIME_REGEX = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

    public function detect(mixed $value): ?string
    {
        return match (true) {
            is_string($value) && preg_match(self::DATE_REGEX, $value) => self::DATE,
            is_string($value) && preg_match(self::TIME_REGEX, $value) => self::TIME,
            is_string($value) && preg_match(self::DATETIME_REGEX, $value) => self::DATETIME,
            default => null
        };
    }
}
