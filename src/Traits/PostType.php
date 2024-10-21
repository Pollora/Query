<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait PostType
{
    protected string $postType = 'any';

    public function postType(string|array $postType): self
    {
        $this->postType = $postType;

        return $this;
    }
}
