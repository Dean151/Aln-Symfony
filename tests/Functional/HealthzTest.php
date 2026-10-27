<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Socket\SocketHeartbeat;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\Response;

final class HealthzTest extends ApiTestCase
{
    use ClockSensitiveTrait;

    protected static ?bool $alwaysBootKernel = false;

    public function testHealthy(): void
    {
        $client = self::createClient();
        $heartbeat = self::getContainer()->get(SocketHeartbeat::class);
        $heartbeat->beat($heartbeat->now());

        $client->request('GET', '/healthz');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Cache-Control', 'no-store, private');
        $this->assertJsonEquals([
            'status' => 'ok',
            'checks' => ['database' => 'ok', 'queue' => 'ok', 'socket' => 'ok'],
        ]);
    }

    public function testStaleSocketHeartbeat(): void
    {
        $client = self::createClient();
        $heartbeat = self::getContainer()->get(SocketHeartbeat::class);
        $heartbeat->beat($heartbeat->now());
        self::mockTime('+'.(SocketHeartbeat::MAX_AGE + 1).' seconds');

        $client->request('GET', '/healthz');
        $this->assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        $this->assertJsonEquals([
            'status' => 'fail',
            'checks' => ['database' => 'ok', 'queue' => 'ok', 'socket' => 'fail'],
        ]);
    }
}
