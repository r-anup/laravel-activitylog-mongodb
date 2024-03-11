<?php

namespace Spatie\Activitylog\Test;

use CreateActivityLogTable;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\MongodbServiceProvider;
use MongoDB\Laravel\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Test\Models\Article;
use Spatie\Activitylog\Test\Models\User;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        $this->checkCustomRequirements();

        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Flush the database after each test function
     */
    public function tearDown(): void
    {
        Activity::truncate();
        Article::truncate();
        User::truncate();
    }

    protected function checkCustomRequirements()
    {
        collect($this->getAnnotations())->filter(function ($location) {
            return in_array('!Travis', Arr::get($location, 'requires', []));
        })->each(function ($location) {
            getenv('TRAVIS') && $this->markTestSkipped('Travis will not run this test.');
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            MongodbServiceProvider::class,
            ActivitylogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'mongodb');
        $app['config']->set('database.connections.mongodb', [
            'host' => 'localhost',
            'port' => '27017',
            'driver' => 'mongodb',
            'database' => 'laravel_activitylog_mongodb_test',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
    }

    protected function setUpDatabase()
    {
        $this->seedModels(Article::class, User::class);
    }

    protected function seedModels(...$modelClasses)
    {
        collect($modelClasses)->each(function (string $modelClass) {
            foreach (range(1, 0) as $index) {
                $modelClass::create(['name' => "name {$index}"]);
            }
        });
    }

    public function getLastActivity(): ?Activity
    {
        return Activity::all()->last();
    }

    public function markTestAsPassed()
    {
        $this->assertTrue(true);
    }

    public function isLaravel6OrLower(): bool
    {
        $majorVersion = (int) substr(App::version(), 0, 1);

        return $majorVersion <= 6;
    }
}
