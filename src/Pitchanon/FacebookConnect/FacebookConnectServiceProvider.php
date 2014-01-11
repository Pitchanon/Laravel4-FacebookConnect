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
		// Auto create app alias with boot method.
		// Shortcut so developers don't need to add an Alias in app/config/app.php
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
		// Register 'FacebookConnect' instance container to our FacebookConnect object
		$this->app['FacebookConnect'] = $this->app->share(function($app) {
			return new Provider\FacebookConnect;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'FacebookConnect'
			);
	}

}