<?php

declare(strict_types=1);

namespace Pollora\Query\Traits;

trait Period
{
    protected ?int $m = null;

    protected ?int $w = null;

    protected ?int $year = null;

    protected ?int $monthnum = null;

    protected ?int $day = null;

    protected ?int $hour = null;

    protected ?int $minute = null;

    protected ?int $second = null;

    /**
     * Year and month as YYYYMM (the `m` parameter).
     */
    public function yearMonth(int $yearMonth): self
    {
        $this->m = $yearMonth;

        return $this;
    }

    /**
     * Week of the year (0 to 53), the `w` parameter.
     */
    public function week(int $week): self
    {
        $this->w = $week;

        return $this;
    }

    public function year(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function month(int $month): self
    {
        $this->monthnum = $month;

        return $this;
    }

    public function day(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function hour(int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function minute(int $minute): self
    {
        $this->minute = $minute;

        return $this;
    }

    public function second(int $second): self
    {
        $this->second = $second;

        return $this;
    }
}
