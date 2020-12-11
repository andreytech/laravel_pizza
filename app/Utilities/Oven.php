<?php

namespace App\Utilities;

abstract class Oven
{
    const STATUS_OFF = 'off';
    const STATUS_HEATED = 'heated';

    protected $status = '';

    public function __construct()
    {
        $this->status = self::STATUS_OFF;
    }

    /**
     * Just echo time to heat up
     *
     * @return self
     */
    abstract public function heatUp(): self;

    /**
     * Calculate and echo time to cook
     * Update Pizza status (raw -> cooked and cooked -> overcooked)
     * throw BadFunctionCall if oven is not on
     *
     * @param Pizza $pizza
     * @return self
     */
    abstract public function bake(Pizza &$pizza): self;

    /**
     * Just echo 'oven is off'
     *
     * @return self
     */
    abstract public function turnOff(): self;

    public function getStatus(): string
    {
        return $this->status;
    }
}
