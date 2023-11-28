<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name'        => $this->faker->firstName,
            'last_name'         => $this->faker->lastName,
            'email'             => $this->faker->unique()->safeEmail,
            'admin'             => false,
            'email_verified_at' => now('EST'),
            'password'          => bcrypt('password'),
            'remember_token'    => Str::random(10),
            'slots_booked'      => 0
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

     /**
     * Indicate that the user should be an admin.
     */
    public function admin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'admin' => 1,
            ];
        });
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(callable $callback = null): static
    {
        if (!Features::hasTeamFeatures()) {
            return $this->state([]);
        }
    
        return $this->has(
            Team::factory()
                ->state(function (array $attributes, User $user) {
                    return [
                        'name' => $user->first_name . '\'s Team',
                        'user_id' => $user->id,
                        'personal_team' => true,
                    ];
                })
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}
