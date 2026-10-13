<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient;

use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MichaelDrennen\Balcony\MarketQuotesClient\DTO\MarketDailyBarDTO;
use MichaelDrennen\Balcony\MarketQuotesClient\Exceptions\BalconyClientException;

final class Client
{
    private readonly ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient([
            'timeout' => $config->timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * Daily bars for every configured benchmark ticker.
     *
     * @return MarketDailyBarDTO[]
     */
    public function fetchAllBars(string $from, string $to): array
    {
        $all = [];

        foreach ($this->config->tickers as $ticker) {
            foreach ($this->fetchBars($ticker, $from, $to) as $bar) {
                $all[] = $bar;
            }
        }

        return $all;
    }

    /**
     * @return MarketDailyBarDTO[]
     */
    public function fetchBars(string $ticker, string $from, string $to): array
    {
        $url = sprintf(
            '%s/v2/aggs/ticker/%s/range/1/day/%s/%s',
            rtrim($this->config->baseUrl, '/'),
            rawurlencode($ticker),
            $from,
            $to,
        );

        try {
            $response = $this->http->request('GET', $url, [
                'query' => ['adjusted' => 'true', 'sort' => 'asc', 'apiKey' => $this->config->apiKey],
            ]);
        } catch (GuzzleException $e) {
            throw BalconyClientException::transport($e->getMessage(), $e);
        }

        $status = $response->getStatusCode();

        if ($status === 429) {
            $retryHeader = $response->getHeaderLine('Retry-After');
            throw BalconyClientException::rateLimited(is_numeric($retryHeader) ? (int) $retryHeader : null);
        }

        if ($status >= 400) {
            throw BalconyClientException::httpError($status, (string) $response->getBody());
        }

        return $this->parse((string) $response->getBody(), $ticker);
    }

    /**
     * Parse a Polygon aggregates payload. Bars with missing or non-numeric OHLCV fields are skipped.
     *
     * @return MarketDailyBarDTO[]
     */
    public function parse(string $json, string $ticker): array
    {
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw BalconyClientException::malformed('Polygon payload is not a JSON object.');
        }

        $status = $decoded['status'] ?? null;

        if (!in_array($status, ['OK', 'DELAYED'], true)) {
            throw BalconyClientException::malformed(sprintf('Polygon returned status "%s".', (string) $status));
        }

        $results = $decoded['results'] ?? [];

        if (!is_array($results)) {
            return [];
        }

        $rows = [];

        foreach ($results as $bar) {
            if (!is_array($bar)) {
                continue;
            }

            $tradingDate = self::parseEpochMillis($bar['t'] ?? null);
            $open = self::parseNumber($bar['o'] ?? null);
            $high = self::parseNumber($bar['h'] ?? null);
            $low = self::parseNumber($bar['l'] ?? null);
            $close = self::parseNumber($bar['c'] ?? null);
            $volume = self::parseNumber($bar['v'] ?? null);

            if ($tradingDate === null || $open === null || $high === null || $low === null
                || $close === null || $volume === null) {
                continue;
            }

            $rows[] = new MarketDailyBarDTO(
                ticker: $ticker,
                tradingDate: $tradingDate,
                open: $open,
                high: $high,
                low: $low,
                close: $close,
                volume: (int) round($volume),
            );
        }

        return $rows;
    }

    public static function parseNumber(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);

        return ($trimmed !== '' && is_numeric($trimmed)) ? (float) $trimmed : null;
    }

    /**
     * Polygon timestamps are epoch milliseconds at the start of the bar. Convert to the UTC calendar date.
     */
    public static function parseEpochMillis(mixed $raw): ?DateTimeImmutable
    {
        if (!is_int($raw) && !(is_string($raw) && ctype_digit($raw))) {
            return null;
        }

        $seconds = intdiv((int) $raw, 1000);
        $utc = new DateTimeZone('UTC');

        return (new DateTimeImmutable('@' . $seconds))->setTimezone($utc)->setTime(0, 0);
    }
}
