## Facebook Connect for Laravel 4

Facebook Connect is a useful to create app facebook and get testing request.

### Installation

- [API on Packagist](https://packagist.org/packages/pitchanon/facebook-connect)
- [API on GitHub](https://github.com/Pitchanon/Laravel4-FacebookConnect)

To get the lastest version of Theme simply require it in your `composer.json` file.

~~~
"require": {

	"pitchanon/facebook-connect": "dev-master"

}
~~~

You'll then need to run `composer install` or `composer update` to download it and have the autoloader updated.

Once Theme is installed you need to register the service provider with the application. Open up `app/config/app.php` and find the `providers` key.

~~~
'providers' => array(

    'Pitchanon\FacebookConnect\FacebookConnectServiceProvider'

)
~~~

## Usage

In Controller.

Testing.

~~~php
// Test Connect (for test only)
$application = array('appId' => 'YOUR_APP_ID', 'secret' => 'YOUR_APP_SECRET');
FacebookConnect::test($application);

~~~

Usage.

~~~php
// Response entries.
$application = array('appId' => 'YOUR_APP_ID', 'secret' => 'YOUR_APP_SECRET');
$permissions = 'publish_stream';
$url_app = 'http://laravel-test.local/';
FacebookConnect::getUser($application,$permissions,$url_app); // Can get return facebook User data form getUser()

// post to wall facebook.
$message = array(
      'link'    => 'http://laravel-test.local/',
      'message' => 'test message',
      'picture'   => 'http://laravel-test.local/test.gif',
      'name'    => 'test Title ',
      'description' => 'test description '
      );
FacebookConnect::postToFacebook($application,$message,'feed');

~~~


>> note: laravel-test.local is my [Localhost](http://laravel-test.local/).

## Support or Contact

If you have some problem, Contact Pitchanon.d@gmail.com

<a href='http://www.playdn.com/'>http://www.playdn.com/</a>
