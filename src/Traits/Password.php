<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Password
{
    protected ?bool $hasPassword = null;

    protected ?string $postPassword = null;

    public function withoutPassword(): self
    {
        $this->hasPassword = false;

        return $this;
    }

    public function withPassword(?string $postPassword = null): self
    {
        if ($postPassword !== null) {
            $this->postPassword = $postPassword;
        } else {
            $this->hasPassword = true;
        }

        return $this;
    }
}
