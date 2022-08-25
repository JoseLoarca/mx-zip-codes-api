<?php

namespace App\Providers;

use App\Http\Resources\SettlementCollection;
use App\Http\Resources\SettlementResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Remove 'data' wrapping
        JsonResource::withoutWrapping();

        // This is used to easily monitor the queries performed by zip-codes endpoint
        if (config('app.debug')) {
            DB::listen(function($query) {
                Log::info($query->sql, [$query->bindings, $query->time]);
            });
        }
    }
}
