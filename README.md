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

Getting Started with the Facebook SDK for PHP.

In Controller.

~~~php
// Response entries.
$application = array('appId' => 'YOUR_APP_ID', 'secret' => 'YOUR_APP_SECRET');
$permissions = 'publish_stream';
$url_app = 'http://laravel-test.local/';

// getInstance
FacebookConnect::getFacebook($application);

$getUser = FacebookConnect::getUser($permissions, $url_app); // Return facebook User data

~~~

Post to wall.

~~~php
// post to wall facebook.
$message = array(
      'link'    => 'http://laravel-test.local/',
      'message' => 'test message',
      'picture'   => 'http://laravel-test.local/test.gif',
      'name'    => 'test Title ',
      'description' => 'test description '
      );

FacebookConnect::postToFacebook($message,'feed');

~~~

Check user likes the page in Facebook.

~~~php
// Check user likes the page in Facebook.
$page_id = 'FACEBOOK_PAGE_ID';
$user_id = $getUser['user_profile']['id']; // form FacebookConnect::getUser();

$check_like_fan_page = FacebookConnect::getUserLikePage($page_id, $user_id);

if (!empty($check_like_fan_page) && array_key_exists('uid', $check_like_fan_page[0]) && $check_like_fan_page[0]['uid'] = $user_id) {
    echo 'LIKE';
else {
    echo 'DONT LIKE';
}

~~~



>> note: laravel-test.local is my [Localhost](http://laravel-test.local/).

## Support or Contact

If you have some problem, Contact Pitchanon.d@gmail.com

<a href='http://www.playdn.com/'>http://www.playdn.com/</a>
