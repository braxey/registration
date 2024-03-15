<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Logger;
use Illuminate\Support\Facades\Log;
use Faker\Factory as FakerFactory;

class LoggerTest extends TestCase
{
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    public function testInfoLog()
    {
        $identifier = $this->faker->text(10);
        $message = $this->faker->text(100);
        $expectedMessage = "$identifier -- $message";

        $loggedMessage = null;
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($arg) use (&$loggedMessage, $expectedMessage) {
                $loggedMessage = $arg;
                return $loggedMessage === $expectedMessage;
            });

        Logger::info($identifier, $message);

        $this->assertSame($expectedMessage, $loggedMessage);
    }

    public function testErrorLog()
    {
        $identifier = $this->faker->text(10);
        $message = $this->faker->text(100);
        $expectedMessage = "$identifier -- $message";

        $loggedMessage = null;
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($arg) use (&$loggedMessage, $expectedMessage) {
                $loggedMessage = $arg;
                return $loggedMessage === $expectedMessage;
            });

        Logger::error($identifier, $message);

        $this->assertSame($expectedMessage, $loggedMessage);
    }

    public function testWarningLog()
    {
        $identifier = $this->faker->text(10);
        $message = $this->faker->text(100);
        $expectedMessage = "$identifier -- $message";

        $loggedMessage = null;
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($arg) use (&$loggedMessage, $expectedMessage) {
                $loggedMessage = $arg;
                return $loggedMessage === $expectedMessage;
            });

        Logger::warning($identifier, $message);

        $this->assertSame($expectedMessage, $loggedMessage);
    }
}
