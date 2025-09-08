<?php

namespace Matthias\BagistoAutoExchange;

use Illuminate\Support\ServiceProvider;

class BagistoAutoExchangeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\UpdateCurrencies::class,
        ]);
    }

    public function boot()
    {
        //
    }
}
