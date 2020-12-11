<?php

namespace App\Utilities;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Support\Collection;

class Luigis
{
    /** @var Fridge */
    private $fridge;
    /** @var Oven */
    private $oven;

    public function __construct(Oven $oven = null)
    {
        $this->fridge = new Fridge();
        $this->oven = $oven ? $oven : new ElectricOven();
    }

    public function restockFridge(): void
    {
        /** @var Ingredient $ingredient */
        foreach (Ingredient::all() as $ingredient) {
            $this->fridge->add($ingredient, 10);
        }
    }

    /**
     * @param Order $order
     * @return Pizza[]|Collection
     */
    public function deliver(Order $order): Collection
    {
        $order->status = Order::STATUS_PREPARING;

        if($this->oven->getStatus() !== Oven::STATUS_HEATED) {
            $this->oven->heatUp();
        }

        $order->status = Order::STATUS_COOKING;

        // prepare and cook each recipe in the order
        $pizzas = $order->recipes->map(function ($recipe) {
            $pizza = $this->prepare($recipe);
            $this->cook($pizza);
            return $pizza;
        });

        $order->status = Order::STATUS_READY;

        $this->oven->turnOff();

        $order->status = Order::STATUS_DELIVERED;

        return $pizzas;
    }

    // note:
    //  you can only create a new Pizza if you first take all the
    //  ingredients required by the recipe from the fridge
    private function prepare(Recipe $recipe): Pizza
    {
        // 1) Check fridge has enough of each ingredient
        // 2) restockFridge if needed
        // 3) take ingredients from the fridge
        // 4) create new Pizza

        $recipe->ingredientRequirements->each(function (RecipeIngredient $recipeIngredient) {
            if(!$this->fridge->has($recipeIngredient->ingredient, $recipeIngredient->amount)) {
                $currentAmount = $this->fridge->amount($recipeIngredient->ingredient);
                $amountNeeded = $recipeIngredient->amount - $currentAmount;
                $this->fridge->add($recipeIngredient->ingredient, $amountNeeded);
            }

            $this->fridge->take($recipeIngredient->ingredient, $recipeIngredient->amount);
        });

        $pizza = new Pizza($recipe);
        return $pizza;
    }

    private function cook(Pizza &$pizza): void
    {
        $this->oven->bake($pizza);
    }
}
