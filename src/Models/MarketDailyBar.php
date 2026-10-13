<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MarketQuotesClient\Models;

use Illuminate\Database\Eloquent\Model;

final class MarketDailyBar extends Model
{
    protected $table = 'balcony_market_daily_bars';

    protected $guarded = ['id'];

    protected $casts = [
        'trading_date' => 'date',
        'open' => 'decimal:4',
        'high' => 'decimal:4',
        'low' => 'decimal:4',
        'close' => 'decimal:4',
        'adjusted_close' => 'decimal:4',
        'volume' => 'integer',
    ];
}
