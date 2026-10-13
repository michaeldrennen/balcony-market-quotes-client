<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient;

use InvalidArgumentException;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://api.polygon.io';

    /** @var string[] */
    public const DEFAULT_TICKERS = ['BIL', 'MBB', 'HYG', 'LQD', 'SPY', 'VNQ', 'IEF'];

    /**
     * @param string[] $tickers
     */
    public function __construct(
        public readonly string $apiKey,
        public readonly array $tickers = self::DEFAULT_TICKERS,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly float $timeoutSeconds = 15.0,
    ) {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('Polygon API key is required.');
        }

        foreach ($tickers as $ticker) {
            if (preg_match('/^[A-Z.]{1,10}$/', $ticker) !== 1) {
                throw new InvalidArgumentException(sprintf('Invalid benchmark ticker "%s".', $ticker));
            }
        }
    }
}
