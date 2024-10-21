<?php

declare(strict_types=1);

namespace Pollora\Query\Utils\Types;

interface ValueTypeContract
{
    public function detect(mixed $value): ?string;
}
