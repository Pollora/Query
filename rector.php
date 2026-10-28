<?php

declare(strict_types=1);

use Pollora\Harness\Profile;
use Pollora\Harness\Rector\Preset;

return Preset::configure(__DIR__, Profile::Core)
    ->withSkip([
        // Each skip says why, like the framework's rector.php.
    ]);
