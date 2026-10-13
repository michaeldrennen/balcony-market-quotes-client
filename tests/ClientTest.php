<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use MichaelDrennen\Balcony\MarketQuotesClient\Client;
use MichaelDrennen\Balcony\MarketQuotesClient\Config;
use MichaelDrennen\Balcony\MarketQuotesClient\Exceptions\BalconyClientException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function fixture(): string
    {
        return (string) file_get_contents(__DIR__ . '/Fixtures/sample_response.json');
    }

    public function testParsesBarIntoDto(): void
    {
        $dtos = (new Client(new Config('key')))->parse($this->fixture(), 'MBB');

        $this->assertCount(1, $dtos);
        $this->assertSame('MBB', $dtos[0]->ticker);
        $this->assertSame('2026-10-09', $dtos[0]->tradingDate->format('Y-m-d'));
        $this->assertSame(92.35, $dtos[0]->open);
        $this->assertSame(2451920, $dtos[0]->volume);
    }

    public function testFetchBarsUsesMockedHttp(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(200, [], $this->fixture())])),
        ]);

        $this->assertCount(1, (new Client(new Config('key'), $http))->fetchBars('MBB', '2026-10-01', '2026-10-09'));
    }

    public function testRateLimitThrows(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(429, ['Retry-After' => '1'], '')])),
            'http_errors' => false,
        ]);

        try {
            (new Client(new Config('key'), $http))->fetchBars('SPY', '2026-10-01', '2026-10-09');
            $this->fail('Expected BalconyClientException.');
        } catch (BalconyClientException $e) {
            $this->assertTrue($e->isRateLimited());
            $this->assertSame(1, $e->retryAfterSeconds);
        }
    }

    public function testErrorStatusThrows(): void
    {
        $this->expectException(BalconyClientException::class);
        (new Client(new Config('key')))->parse('{"status":"ERROR","message":"bad key"}', 'MBB');
    }

    public function testInvalidTickerRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('key', ['mbb!']);
    }
}
