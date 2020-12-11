<?php


namespace App\Utilities;

use App\Models\RecipeIngredient;
use BadFunctionCallException;

class ElectricOven extends Oven
{
    /**
     * Just echo time to heat up
     *
     * @return self
     */
    public function heatUp(): Oven
    {
        echo "10 minutes to heat up\n";
        $this->status = self::STATUS_HEATED;

        return $this;
    }

    /**
     * Calculate and echo time to cook
     * Update Pizza status (raw -> cooked and cooked -> overcooked)
     * throw BadFunctionCall if oven is not on
     *
     * @param Pizza $pizza
     * @return self
     */
    public function bake(Pizza &$pizza): Oven
    {
        if ($this->status !== self::STATUS_HEATED) {
            throw new BadFunctionCallException("Oven is not on");
        }
        switch ($pizza->getStatus()) {
            case $pizza::STATUS_RAW:
                $pizza->setStatus($pizza::STATUS_COOKED);
                break;
            case $pizza::STATUS_COOKED:
                $pizza->setStatus($pizza::STATUS_OVER_COOKED);
                break;
        }

        $timeToBake = 5;
        $minutesPerIngredient = 1;
        $timeToBake = $pizza->getRecipe()->ingredientRequirements->reduce(
            function ($sum, RecipeIngredient $recipeIngredient) use ($minutesPerIngredient) {
                return $sum + $recipeIngredient->amount * $minutesPerIngredient;
            }, $timeToBake
        );
        echo $timeToBake . " minutes to bake pizza\n";

        return $this;
    }

    /**
     * Just echo 'oven is off'
     *
     * @return self
     */
    public function turnOff(): Oven
    {
        echo "oven is off\n";
        $this->status = self::STATUS_OFF;

        return $this;
    }

}
