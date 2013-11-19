<?php
namespace Pitchanon\FacebookConnect\Provider;

use Pitchanon\FacebookConnect\Provider\Facebookphpsdk\src\Facebook;

/**
 * FacebookConnect
 *
 * Test on: Apache/2.4.4 (Win32) OpenSSL/1.0.1e PHP/5.5.1
 * Test on: Laravel Framework version 4.0.9
 * Test on: Facebook PHP SDK (v.3.2.2)
 *
 * @package FacebookConnect
 * @version 0.0.1
 * @category facebook
 * @author PitchanonD. <Pitchanon.d@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FacebookConnect {

    private $facebook;

    /**
     * Instantiate a new Controller instance.
     */
    public function __construct() {
        // The Facebook Platform is a set of APIs
        // require 'facebook-php-sdk-master/src/facebook.php';
    }

    /**
     * Get a web file (HTML, XHTML, XML, image, etc.) from a URL
     *
     * @param  string $url URL
     *
     * @return string      Return an array/json containing the HTTP server response header fields and content.
     */
    private function curl_get_file_contents($url) {
        // header('Content-type: application/json');
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_USERAGENT => "spider", // who am i
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false // Disabled SSL Cert checks
        );

        curl_setopt_array($ch,$options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;

        return json_encode(array('header' => $header));
    }

    /**
     * Test Connect Facebook examples
     *
     * @param array $application Example: array('appId' => YOUR_APP_ID, 'secret' => YOUR_APP_SECRET);
     *
     * @return string              Message for test Success!
     */
    public static function test($application) {
        // Create our Application instance (replace this with your appId and secret).
        // $facebook = new Facebookphpsdk\src\Facebook($application);
        $facebook = new Facebook($application);

        // Get User ID
        $user = $facebook->getUser();

        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }

        // Login or logout url will be needed depending on current user state.
        if ($user) {
            $logoutUrl = $facebook->getLogoutUrl();
        } else {
            $statusUrl = $facebook->getLoginStatusUrl();
            $loginUrl = $facebook->getLoginUrl();
        }

        // This call will always work since we are fetching public data.
        $pitchanon = $facebook->api('/popphoenix');

        header( 'Content-type: text/html; charset=utf-8' );
        echo $facebook->test();
        echo '<br />';
        if ($user) {
            echo 'You<br />';
            echo '<img src="https://graph.facebook.com/'.$user.'/picture"><br />';
            echo '<a href="'.$logoutUrl.'">Logout</a><br />';
            echo 'Your User Object (/me)';
            echo var_dump($user_profile);
            echo '<br />';
        } else {
            echo 'Check the login status using OAuth 2.0 handled by the PHP SDK:';
            echo '<a href="'.$statusUrl.'">Check the login status</a><br />';
            echo 'Login using OAuth 2.0 handled by the PHP SDK:';
            echo '<a href="'.$loginUrl.'">Login with Facebook</a><br />';
            echo 'You are not Connected.';
            echo '<br />';
        }
        echo 'Public profile of Pitchanon: '.$pitchanon['name'].'<br />'.time();

        return true;
    }

    /**
     * Connect to facebook get User data, If not login facebook or not has user permissions is redirect to Login or App permissions
     *
     * @param array $application Example: array('appId' => YOUR_APP_ID, 'secret' => YOUR_APP_SECRET);
     * @param string $permissions Example: publish_stream, user_likes, user_photos
     * @param string $url_app URL of this app
     *
     * @return string              json user data
     */
    public function getUser($application,$permissions,$url_app) {
        /*if(isset($_GET['code'])){
            // https://graph.facebook.com/oauth/access_token?client_id=YOUR_APP_ID&redirect_uri=YOUR_URL&client_secret=YOUR_APP_SECRET&code=THE_CODE_FROM_ABOVE
            $url = "https://graph.facebook.com/oauth/access_token?client_id=".$application['appId']."&redirect_uri=".urlencode($url_app)."&client_secret=".$application['secret']."&code=".$_GET['code']."";
            $header = $this->curl_get_file_contents($url);
        }*/

        // Create our Application instance (replace this with your appId and secret).
        // $facebook = new Facebookphpsdk\src\Facebook($application);
        $this->facebook = new Facebook($application);

        // Get User ID
        $user = $this->facebook->getUser();
        $loginUrl = $this->facebook->getLoginUrl(array('redirect_uri' => $url_app, 'scope' => $permissions));

        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $this->facebook->api('/me');
                $access_token = $this->facebook->getAccessToken(); // https://graph.facebook.com/me?access_token=$access_token
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }

        if (!$user) {
            echo '<script type="text/javascript">window.location = "'.$loginUrl.'";</script>';
            exit();
        }

        // Check Permissions
        $permissions_api = $this->facebook->api("/me/permissions");

        if (isset($permissions_api['data']['0']['publish_stream'])) {

            // Check verify facebook access token
            // Attempt to query the graph:
            $curl_access_token = $this->curl_get_file_contents('https://graph.facebook.com/me?access_token='. $access_token);
            $object_curl_access_token = json_decode($curl_access_token);
            $object_curl_content = json_decode($object_curl_access_token->header->content);

            //Check for errors
            if (isset($object_curl_content->error)) {
                // check to see if this is an oAuth error:
                if ($object_curl_content->error->type == "OAuthException") {
                    // Retrieving a valid access token.
                    $dialog_url= "https://www.facebook.com/dialog/oauth?client_id=" . $application['appId'] . "&redirect_uri=" . urlencode($url_app);
                    echo '<script type="text/javascript">top.location.href="'.$dialog_url.'";</script>';
                    exit();
                } else {
                    echo "other error has happened";
                }

            } else {

                // remove code param in url
                if (isset($_GET['code'])) {
                    echo '<script type="text/javascript">window.location = "'.$url_app.'";</script>';
                }

                // success
                return json_encode(array(
                    'facebook' => array(
                        'status' => array(
                                'code' => 200,
                                'message' => 'OK',
                                'type' => 'string'
                                ),
                        'user_profile' => $user_profile,
                        'access_token' => $access_token
                        )
                    )
                );

            }

        } else {
            echo '<script type="text/javascript">window.location = "'.$loginUrl.'";</script>';
            exit();
        }

    }

    /**
     * post links, feed to user facebook wall
     *
     * @param array $application Example: array('appId' => YOUR_APP_ID, 'secret' => YOUR_APP_SECRET);
     * @param  array $message     Example: $message = array('link' => '', 'message' => '','picture' => '', 'name' => '','description'   => '');
     * @param  string $type        type of post (links,feed)
     *
     * @return string              id of post
     */
    public function postToFacebook($application,$message,$type=""){
        $this->facebook = new Facebook(array(
            'appId' => $application['appId'],
            'secret' => $application['secret'],
            'cookie' => true,
            'fileUpload' => false
            ));

        if (!$type) {
            $type = 'feed';
        }
        // links, feed
        $return_post = $this->facebook->api('/me/'.$type, 'POST', array(
            'link'      => $message['link'],
            'message'    => $message['message'],
            'picture'       => $message['picture'],
            'name'      => $message['name'],
            'description'   => $message['description']
            ));

        return json_encode(array('postToFacebook' => $return_post)); // string(37) "{"id":"13303xxxxx_102024310696xxxxx"}"
    }

}
?>