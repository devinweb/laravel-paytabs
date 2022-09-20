<?php

namespace Devinweb\LaravelPaytabs\Tests;

use Devinweb\LaravelPaytabs\LaravelPaytabsServiceProvider;
use Devinweb\LaravelPaytabs\Tests\Support\User;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->artisan('migrate')->run();
    }

    protected function getPackageProviders($app)
    {
        return [LaravelPaytabsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    protected function createCustomer(): User
    {
        return User::create(
            [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $this->faker->sentence,
            ]
        );
    }

    protected function createCart()
    {
        return [
            'id' => $this->faker->randomDigit,
            'amount' => 80,
            'description' => $this->faker->sentence,

        ];
    }
}
