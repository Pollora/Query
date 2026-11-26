<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait PostType
{
    /**
     * @var string|array<string>
     */
    protected string|array $postType = 'any';

    /**
     * @param  string|array<string>  $postType
     */
    public function postType(string|array $postType): self
    {
        $this->postType = $postType;

        return $this;
    }
}
