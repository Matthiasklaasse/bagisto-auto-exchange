# Bagisto Auto Exchange

A Laravel / Bagisto package to automatically add all currencies and update exchange rates from your store's base currency.

## Features

- Fetches the latest exchange rates from [ExchangeRate API](https://www.exchangerate-api.com/).  
- Inserts new currencies into the store if they don't exist.  
- Updates or inserts exchange rates automatically.  
- run ```php artisan bagisto:auto-exchange``` and you're good to go👍