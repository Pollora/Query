<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Comment
{
    /**
     * A number of comments, or a `value` / `compare` pair.
     *
     * @var int|array<string, int|string>|null
     */
    protected int|array|null $commentCount = null;

    /**
     * @param  int|array<string, int|string>  $count
     */
    public function commentCount(int|array $count): self
    {
        $this->commentCount = $count;

        return $this;
    }
}
