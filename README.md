# balcony-market-quotes-client

PHP 8.1+ client for Polygon.io daily aggregate bars for benchmark ETFs (`BIL`, `MBB`, `HYG`, `LQD`, `SPY`, `VNQ`, `IEF`), part of The Balcony macro flow system.

## Usage (framework-agnostic)

```php
use MichaelDrennen\Balcony\MarketQuotesClient\Client;
use MichaelDrennen\Balcony\MarketQuotesClient\Config;

$client = new Client(new Config(apiKey: 'YOUR_POLYGON_KEY'));

foreach ($client->fetchBars('MBB', '2026-10-01', '2026-10-09') as $dto) {
    // $dto->ticker, $dto->tradingDate, $dto->open, $dto->high, $dto->low, $dto->close, $dto->volume
}

// All configured benchmark tickers
$bars = $client->fetchAllBars('2026-10-01', '2026-10-09');
```

Bar timestamps (epoch milliseconds, UTC) are converted to a calendar trading date.

## Laravel persistence

```php
use MichaelDrennen\Balcony\MarketQuotesClient\Models\MarketDailyBar;

foreach ($bars as $dto) {
    MarketDailyBar::updateOrCreate(
        ['ticker' => $dto->ticker, 'trading_date' => $dto->tradingDate->format('Y-m-d')],
        $dto->asModelAttributes(),
    );
}
```

## Errors

Failures throw `MichaelDrennen\Balcony\MarketQuotesClient\Exceptions\BalconyClientException`. HTTP 429 sets `isRateLimited()` and exposes `retryAfterSeconds`.

## Tests

```
composer install
vendor/bin/phpunit
```
