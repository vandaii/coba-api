<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Fresh Milk',
            'Condensed Milk',
            'Creamer',
            'Mineral Water',
            'Sparkling Water',
            'Sugar',
            'Brown Sugar',
            'Palm Sugar',
            'Ice Cube',
            'Syrup Strawberry',
            'Syrup Vanilla',
            'Syrup Caramel',
            'Lemon Juice',
            'Lime Juice',
            'Matcha Powder',
            'Coffee Bean',
            'Ground Coffee',
            'Espresso Concentrate',
            'Chocolate Powder',
            'Cocoa Syrup',
            'Tapioca Pearl',
            'Nata de Coco',
            'Aloe Vera Cube',
            'Jelly Strawberry',
            'Jelly Mango',
            'Tea Black',
            'Tea Green',
            'Thai Tea Mix',
            'Milk Tea Base',
            'Yogurt Drink',
            'Whipped Cream',
            'Ice Cream Vanilla',
            'Ice Cream Chocolate',
            'Boba Brown Sugar',
            'Mint Leaf',
            'Lemongrass Extract',
            'Honey',
            'Cinnamon Powder',
            'Ginger Extract',
            'Oat Milk',
            'Soy Milk',
            'Almond Milk',
            'Watermelon Syrup',
            'Mango Puree',
            'Banana Puree',
            'Strawberry Puree',
            'Blueberry Jam',
            'Hazelnut Syrup',
            'Mocha Sauce',
            'Cheese Cream'
        ];

        $units = ['Liter', 'PCS', 'Box', 'KG'];

        $name = $this->faker->unique()->randomElement($names);

        return [
            'item_name' => $name,
            'UoM' => $this->faker->randomElement($units),
        ];
    }
}
