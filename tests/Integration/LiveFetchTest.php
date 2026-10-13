<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient\Tests\Integration;

use DateTimeImmutable;
use MichaelDrennen\Balcony\MarketQuotesClient\Client;
use MichaelDrennen\Balcony\MarketQuotesClient\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Hits the real remote source. Excluded from the default run; use `vendor/bin/phpunit --group integration`.
 */
#[Group('integration')]
final class LiveFetchTest extends TestCase
{
    private const MAX_AGE_DAYS = 10;

    public function testFetchesRecentDataFromLiveSource(): void
    {
        $key = getenv('POLYGON_API_KEY');

        if (!is_string($key) || trim($key) === '') {
            $this->markTestSkipped('Set POLYGON_API_KEY to run this live test.');
        }

        $dtos = (new Client(new Config($key)))->fetchBars(
            'SPY',
            (new DateTimeImmutable('-14 days'))->format('Y-m-d'),
            (new DateTimeImmutable())->format('Y-m-d'),
        );

        $this->assertNotEmpty($dtos, 'Expected recent SPY daily bars from Polygon.');

        $newest = max(array_map(static fn ($dto): DateTimeImmutable => $dto->tradingDate, $dtos));
        $ageDays = (int) $newest->diff(new DateTimeImmutable())->format('%a');

        $this->assertLessThanOrEqual(
            self::MAX_AGE_DAYS,
            $ageDays,
            sprintf('Newest record is from %s; the source may have stopped updating.', $newest->format('Y-m-d')),
        );
    }
}
