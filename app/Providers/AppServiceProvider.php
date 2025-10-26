<?php

namespace App\Providers;

use App\Http\Resources\CompteResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompteResource::class, function($app, array $params){
            return new CompteResource($params['compte']);
        });

        $this->app->bind('compte.resource.collection', function ($app, $params = []) {
        $collection = $params['collection'] ?? collect();
        return CompteResource::collection($collection)->response()->getData(true);
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
