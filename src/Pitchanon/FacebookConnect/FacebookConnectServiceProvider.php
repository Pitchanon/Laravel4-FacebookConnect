<?php namespace Pitchanon\FacebookConnect;

use Illuminate\Support\ServiceProvider;

class FacebookConnectServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function boot() {
		$this->package('Pitchanon/FacebookConnect');

		// Auto create app alias with boot method.
		$loader = \Illuminate\Foundation\AliasLoader::getInstance();
		$loader->alias('FacebookConnect', 'Pitchanon\FacebookConnect\Facades\FacebookConnect');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register 'facebookconnect' instance container to our FacebookConnect object
		$this->app['facebookconnect'] = $this->app->share(function($app) {
			return new Provider\FacebookConnect; // Class Name
		});

		// Shortcut so developers don't need to add an Alias in app/config/app.php
		/*$this->app->booting(function() {
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('FacebookConnect', 'Pitchanon\FacebookConnect\Facades\FacebookConnect'); // Class Name
		});*/
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}