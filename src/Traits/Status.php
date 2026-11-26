<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Status
{
    /**
     * @var string|array<string>|null
     */
    protected string|array|null $postStatus = null;

    /**
     * @param  string|array<string>  $status
     */
    public function postStatus(string|array $status): self
    {
        $this->postStatus = $status;

        return $this;
    }
}
