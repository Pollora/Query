<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait MetaField
{
    /**
     * @var string|array<string>|null
     */
    protected string|array|null $metaKey = null;

    protected mixed $metaValue = null;

    protected ?string $metaCompare = null;

    protected ?string $metaType = null;

    protected ?string $metaCompareKey = null;

    protected ?string $metaTypeKey = null;

    /**
     * @param  string|array<string>  $key
     */
    public function metaKey(string|array $key): self
    {
        $this->metaKey = $key;

        return $this;
    }

    public function metaValue(mixed $value): self
    {
        $this->metaValue = $value;

        return $this;
    }

    public function metaCompare(string $compare): self
    {
        $this->metaCompare = $compare;

        return $this;
    }

    public function metaType(string $type): self
    {
        $this->metaType = strtoupper($type);

        return $this;
    }

    public function metaCompareKey(string $compare): self
    {
        $this->metaCompareKey = $compare;

        return $this;
    }

    public function metaTypeKey(string $type): self
    {
        $this->metaTypeKey = strtoupper($type);

        return $this;
    }

    /**
     * Orders by a meta value. As WP does, this also restricts the results to posts having that key.
     */
    public function orderByMeta(string $key, string $order = 'DESC', bool $numeric = false): self
    {
        $this->metaKey = $key;

        return $this->orderBy($numeric ? 'meta_value_num' : 'meta_value', $order);
    }
}
