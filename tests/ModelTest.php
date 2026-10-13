<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient\Tests;

use DateTimeImmutable;
use MichaelDrennen\Balcony\MarketQuotesClient\DTO\MarketDailyBarDTO;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testDtoMapsToModelAttributes(): void
    {
        $dto = new MarketDailyBarDTO('MBB', new DateTimeImmutable('2026-10-09'), 92.35, 92.65, 92.20, 92.50, 2451920);

        $this->assertSame([
            'ticker' => 'MBB',
            'trading_date' => '2026-10-09',
            'open' => 92.35,
            'high' => 92.65,
            'low' => 92.20,
            'close' => 92.50,
            'volume' => 2451920,
        ], $dto->asModelAttributes());
    }

    public function testEloquentModelAcceptsDtoAttributes(): void
    {
        if (!class_exists('Illuminate\Database\Eloquent\Model')) {
            $this->markTestSkipped('illuminate/database is not installed.');
        }

        // Eloquent's guarded-attribute check reads the column listing, so the model needs a connection.
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $dto = new MarketDailyBarDTO('MBB', new DateTimeImmutable('2026-10-09'), 1.0, 2.0, 0.5, 1.5, 10);
        $model = new \MichaelDrennen\Balcony\MarketQuotesClient\Models\MarketDailyBar($dto->asModelAttributes());

        $this->assertSame('MBB', $model->ticker);
        $this->assertSame('balcony_market_daily_bars', $model->getTable());
    }
}
