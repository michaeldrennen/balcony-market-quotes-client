<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient\DTO;

use DateTimeImmutable;

final class MarketDailyBarDTO implements MetricObservation
{
    public function __construct(
        public readonly string $ticker,
        public readonly DateTimeImmutable $tradingDate,
        public readonly float $open,
        public readonly float $high,
        public readonly float $low,
        public readonly float $close,
        public readonly int $volume,
    ) {
    }

    public function asModelAttributes(): array
    {
        return [
            'ticker' => $this->ticker,
            'trading_date' => $this->tradingDate->format('Y-m-d'),
            'open' => $this->open,
            'high' => $this->high,
            'low' => $this->low,
            'close' => $this->close,
            'volume' => $this->volume,
        ];
    }
}
