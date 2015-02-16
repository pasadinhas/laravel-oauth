<?php namespace Pasadinhas\LaravelOAuth;

use Illuminate\Support\ServiceProvider;
use OAuth\ServiceFactory;

/**
 * Class LaravelOAuthServiceProvider
 * @package Pasadinhas\LaravelOAuth
 */
class LaravelOAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['oauth'] = $this->app->share(function($app)
        {
            return new Factory($app['config'], new ServiceFactory());
        });
	}

    public function boot()
    {
        $this->package('pasadinhas/laravel-oauth', 'oauth');

        $this->publishes([
            __DIR__.'/../../config/oauth.php' => config_path('oauth.php')
        ]);
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
