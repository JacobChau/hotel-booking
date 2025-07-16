<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        $images = [
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/280860707.webp?k=a688721899e6724ba61f0a240141e1d08055262231b9531a380edbaef96dd231&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/464640824.webp?k=7d1bdb50139df2b271ad39749aa7fe87be8ffa1a7593d5442cc88f31920a2265&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/591837596.webp?k=3e35e446b314ee4d699a72cd51ef6fe9b3a244643e98eb41db9148a99d1cd401&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/218545660.webp?k=c02259d2c3e2763d222ee079d244f9b445bbd88b3914b0697630166301dcd747&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/63753059.webp?k=6b6791b4e62e48a237e3ec85a86ceafa645b2fcc8de8c05665bfaa87aa98d6eb&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/714844200.webp?k=ff6c99b0d6d09cf8c41c5e25ce5cc0eb7af9e6f727c1cbecb5d29c729762be94&o=',       
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/618231508.webp?k=3ac9e256e93d289eda4900dae1804cb92e1c9d3e0bd1f3ed95b987b5dcbbf50b&o=',
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/628967948.webp?k=ab8de90cfe8f6e01f09d8b7e6240d005aa1d4f07c32f0f87e72e6e460e828231&o='
        ];

        return [
            'title' => $this->faker->randomElement([
                'Deluxe King Room',
                'Executive Suite',
                'Standard Twin Room',
                'Superior Queen Room',
                'Family Room',
                'Suite',
                'Deluxe Room',
                'Executive Room',
                'Standard Room',
                'Superior Room',
            ]),
            'description' => $this->faker->sentence(20),
            'price' => $this->faker->randomFloat(2, 80, 500),
            'image' =>  $this->faker->randomElement($images),
            'created_at' => now(),
            'updated_at' => now(),
            'guest' => $this->faker->numberBetween(1, 10),
        ];
    }
}
