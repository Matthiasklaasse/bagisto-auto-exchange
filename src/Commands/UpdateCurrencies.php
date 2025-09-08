<?php

namespace Matthias\BagistoAutoExchange\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateCurrencies extends Command
{
    protected $signature = 'bagisto:auto-exchange';
    protected $description = 'Add all currencies and update exchange rates from the store base currency';

    public function handle()
    {
        $this->info("Fetching latest exchange rates...");

        $apiKey = env('EXCHANGERATE_API_KEY');
        $baseCurrency = core()->getBaseCurrencyCode();

        if (empty($apiKey)) {
            $this->error('Set your API key in .env: EXCHANGERATE_API_KEY get your key at https://exchangerate-api.com/');
            return 1;
        }

        $response = Http::get("https://v6.exchangerate-api.com/v6/$apiKey/latest/$baseCurrency");

        if (!$response->successful()) {
            $this->error("Failed to fetch exchange rates. Check your API key.");
            return 1;
        }

        $conversions = $response->json()['conversion_rates'];

        foreach ($conversions as $code => $rate) {
            if ($code === $baseCurrency) continue;

            $details = $this->getCurrencyDetails($code);
            $currency = DB::table('currencies')->where('code', $code)->first();

            if(empty($details)){
                continue;
            }

            if (!$currency) {
                $currencyId = DB::table('currencies')->insertGetId([
                    'code' => $code,
                    'name' => $details['name'],
                    'symbol' => $details['symbolNative'] ?? $code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->info("Inserted currency: $code");
            } else {
                $currencyId = $currency->id;
            }

            $existingRate = DB::table('currency_exchange_rates')
                ->where('target_currency', $currencyId)
                ->first();

            if ($existingRate) {
                DB::table('currency_exchange_rates')
                    ->where('id', $existingRate->id)
                    ->update([
                        'rate' => $rate,
                        'updated_at' => now(),
                    ]);
                $this->info("Updated exchange rate: $baseCurrency -> $code = $rate");
            } else {
                $newId = DB::table('currency_exchange_rates')->insertGetId([
                    'target_currency' => $currencyId,
                    'rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info("Inserted exchange rate: $baseCurrency -> $code = $rate");
            }
        }

        $this->info("All currencies and exchange rates updated successfully!, to make them visible addthem to your channel in the dashboard");
        return 0;
    }

    protected function getCurrencyDetails($code)
    {
        $currencies = json_decode(file_get_contents(__DIR__ . '/currencies.json'), true);
    
        foreach ($currencies as $currency) {
            if ($currency['code'] === $code) {
                return $currency;
            }
        }
    
        return null;
    }
    
}
