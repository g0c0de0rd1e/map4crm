<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Geocoder\Provider\Nominatim\Nominatim;
use Http\Adapter\Curl\Client as CurlClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Geocoder\StatefulGeocoder;

class GeocoderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(StatefulGeocoder::class, function ($app) {
            $httpClient = new CurlClient();
            $psr17Factory = new Psr17Factory();
            $provider = Nominatim::withOpenStreetMapServer($httpClient, 'your-user-agent');
            return new StatefulGeocoder($provider, 'en');
        });
    }
}