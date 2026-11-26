<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait MimeType
{
    /**
     * @var string|array<string>|null
     */
    protected string|array|null $postMimeType = null;

    /**
     * @param  string|array<string>  $value
     */
    public function postMimeType(string|array $value): self
    {
        $this->postMimeType = $value;

        return $this;
    }
}
