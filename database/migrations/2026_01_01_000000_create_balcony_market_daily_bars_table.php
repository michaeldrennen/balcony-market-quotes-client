<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balcony_market_daily_bars', function (Blueprint $table): void {
            $table->id();
            $table->string('ticker', 16);
            $table->date('trading_date');
            $table->decimal('open', 12, 4);
            $table->decimal('high', 12, 4);
            $table->decimal('low', 12, 4);
            $table->decimal('close', 12, 4);
            $table->decimal('adjusted_close', 12, 4)->nullable();
            $table->unsignedBigInteger('volume');
            $table->timestamps();

            $table->unique(['ticker', 'trading_date'], 'balcony_market_ticker_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balcony_market_daily_bars');
    }
};
