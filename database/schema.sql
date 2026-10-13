CREATE TABLE IF NOT EXISTS balcony_market_daily_bars (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticker VARCHAR(16) NOT NULL,
    trading_date DATE NOT NULL,
    open DECIMAL(12,4) NOT NULL,
    high DECIMAL(12,4) NOT NULL,
    low DECIMAL(12,4) NOT NULL,
    close DECIMAL(12,4) NOT NULL,
    adjusted_close DECIMAL(12,4) NULL,
    volume BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY balcony_market_ticker_date_unique (ticker, trading_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
