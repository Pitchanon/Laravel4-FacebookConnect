<?php
/**
 * FacebookConnect.
 *
 * PHP version 5
 *
 * @category Facebook
 * @package  FacebookConnect
 * @author   PitchanonD. <Pitchanon.d@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @link     https://github.com/Pitchanon/Laravel4-FacebookConnect github
 */
namespace Pitchanon\FacebookConnect\Provider;

use Pitchanon\FacebookConnect\Provider\Facebookphpsdk\src\Facebook;

// if (class_exists('FacebookConnect', true) === false) {
//     $error = 'Class FacebookConnect not found';
//     throw new FacebookConnect_Exception($error);
// }

/**
 * FacebookConnect.
 *
 * Test on: Apache/2.4.4 (Win32) OpenSSL/1.0.1e PHP/5.5.1
 * Test on: Laravel Framework version 4.0.9
 * Test on: Facebook PHP SDK (v.3.2.2)
 *
 * @category Facebook
 * @package  FacebookConnect
 * @author   PitchanonD. <Pitchanon.d@gmail.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version  0.0.2
 * @link     https://github.com/Pitchanon/Laravel4-FacebookConnect github
 */
class FacebookConnect
{

    private static $__facebook;

    /**
     * getInstance.
     *
     * @param array $application Example: array('appId' => YOUR_APP_ID, 'secret' => YOUR_APP_SECRET);
     *
     * @return object              new Facebook($application)
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     */
    public static function getFacebook($application = array())
    {
        if (!isset(self::$__facebook) || empty(self::$__facebook)) {
            self::$__facebook = new Facebook($application);
        }
        return self::$__facebook;
    }

    /**
     * Authenticated.
     *
     * @param array  $permissions List permissions
     * @param string $url_app     Canvas URL
     *
     * @return array               User data facebook
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     */
    public static function getUser($permissions, $url_app)
    {
        $permissions_array = array();

        // for check permissions list
        $permissions_array = array_map('trim', explode(',', $permissions));

        // Authenticated
        // Get the FB UID of the currently logged in user
        $user = self::getFacebook()->getUser();

        /**
         * Create a login URL using the Facebook library's getLoginUrl() method.
         * If not, let's redirect to the ALLOW page so we can get access.
         *
         * @var array
         *
         * redirect_uri – this is the page Facebook redirects to after the user has gone through the Facebook permissions page.
         * scope – this is a comma-delimited list of permissions the application needs.
         * fbconnect – this should be 1 to tell Facebook that the application will be using Facebook to authenticate the user.
         */
        $loginUrl = self::getFacebook()->getLoginUrl(
            array(
                'redirect_uri' => $url_app,
                'scope' => $permissions,
                // 'canvas' => 1,
                'fbconnect' => 1,
                // 'display'   =>  "page",
                // 'next' => $start_page // page ที่จะไปเมื่อ log in เสร็จ
                )
        );

        // if the user has already allowed the application, you'll be able to get his/her FB UID
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = self::getFacebook()->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }

        // If not, let's redirect to the ALLOW page so we can get access
        if (empty($user)) {
            echo '<script type="text/javascript">top.location.href = "'.$loginUrl.'";</script>';
            exit();
        }

        // get the user's access token
        $access_token = self::getFacebook()->getAccessToken();

        /**
         * Facebook Permissions
         *
         * publish_stream – allows the application to publish updates to Facebook on the user’s behalf.
         * read_stream – allows the application to read from the user’s News Feed.
         * offline_access – converts the access_token to one that doesn’t expire, thus letting the application make API calls anytime. Without this, the application’s access_token will expire after a few minutes, which isn’t ideal in this case.
         * manage_pages – lets the application access the user’s Facebook Pages. Since the application we’re building deals with Facebook Pages, we’ll need this as well.
         */
        $permissions_list = self::getFacebook()->api(
            "/me/permissions",
            'GET',
            array(
                'access_token' => $access_token
                )
        );

        // if (empty($permissions_list['data']['0']['publish_stream'])) {
        //     echo '<script type="text/javascript">top.location.href = "'.$loginUrl.'";</script>';
        //     exit();
        // }

        // check if the permissions we need have been allowed by the user
        // if not then redirect them again to facebook's permissions page
        foreach ($permissions_array as $perm) {
			$perm_unlisted = false;
			foreach($permissions_list['data'][0] as $fb_perm)
			{
				if($fb_perm['permission'] == $perm && $fb_perm['status'] == 'granted')
				{
					$perm_unlisted = true;
					break;
				}
			}
			if($perm_unlisted) {
				echo '<script type="text/javascript">top.location.href = "'.$loginUrl.'";</script>';
				exit();
			}
        }

        // Set the current access token to be a long-lived token.
        self::getFacebook()->setExtendedAccessToken();

        // get the news feed of the active page using the page's access token
        $user_feed = self::getFacebook()->api(
            '/me/feed',
            array(
                'access_token' => $access_token
                )
        );

        // if the user has allowed all the permissions we need,
        // get the information about the pages that he or she managers
        $user_accounts = self::getFacebook()->api(
            '/me/accounts',
            array(
                'access_token' => $access_token
                )
        );

        // Success
        $response = array(
            'user_profile' => $user_profile,
            'user_feed' => $user_feed,
            'user_accounts' => $user_accounts,
            'access_token' => $access_token
            );
        return $response;
    }

    /**
     * Check user likes the page in Facebook.
     *
     * @param integer $page_id Facebook fan page id
     * @param integer $user_id Facebook User id
     *
     * @return [type]          [description]
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     */
    public function getUserLikePage($page_id, $user_id)
    {
        $response = self::getFacebook()->api(
            array(
                "method"    => "fql.query",
                "query"     => "SELECT uid FROM page_fan WHERE uid={$user_id} AND page_id={$page_id}"
                )
        ); //,type,page_id,profile_section 1169893316XXXXX
        return $response;
    }

    /**
     * post links, feed to user facebook wall.
     *
     * @param array  $message Example: $message = array('link' => '', 'message' => '','picture' => '', 'name' => '','description'   => '', 'access_token' => '');
     * @param string $type    Type of message (links,feed)
     *
     * @return string                  Id of message
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     */
    public function postToFacebook(array $message, $type = null)
    {
        if (is_null($type)) {
            $type = 'feed';
        }

        // links, feed
        $response = self::getFacebook()->api(
            '/me/' . $type,
            'POST',
            $message
        );

        return $response; // return feed id Array ( [id] => 1330355140_102030093014XXXXX )
    }

    /**
     * This wrapper function exists in order to circumvent PHP’s strict obeying of HTTP error codes. In this case, Facebook returns error code 400 which PHP obeys and wipes out the response.
     *
     * @param string $url Uniform resource locator
     *
     * @return string                      Data
     *
     * @author Ankur Pansari
     */
    private function _curlGetFileContents($url)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        $err  = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        if ($contents) {
            return $contents;
        } else {
            return false;
        }
    }

}
?>
