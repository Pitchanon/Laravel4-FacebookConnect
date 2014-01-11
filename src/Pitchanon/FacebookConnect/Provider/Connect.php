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
 * @version 0.0.2
 * @category facebook
 * @author Pitchanon D. <Pitchanon.d@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FacebookConnect {

    private static $facebook;

    /**
     * getInstance
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     *
     * @method getFacebook
     *
     * @param array $application Example: array('appId' => YOUR_APP_ID, 'secret' => YOUR_APP_SECRET);
     *
     * @return object                   new Facebook($application)
     */
    public static function getFacebook($application = array()) {
        if (!isset(self::$facebook) || empty(self::$facebook)) {
            self::$facebook = new Facebook($application);
        }
        return self::$facebook;
    }

    /**
     * Authenticated
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     *
     * @method getUser
     *
     * @param  array  $permissions List permissions
     * @param  string  $url_app     Canvas URL
     *
     * @return array               User data facebook
     */
    public static function getUser($permissions, $url_app) {
        // Authenticated
        // Get User ID
        $user = self::getFacebook()->getUser();
        $loginUrl = self::getFacebook()->getLoginUrl(array(
            'redirect_uri' => $url_app,
            'scope' => $permissions,
            // 'canvas' => 1,
            // 'fbconnect' => 0,
            // 'next' => $start_page // page ที่จะไปเมื่อ log in เสร็จ
            )
        );

        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = self::getFacebook()->api('/me');
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
        $permissions_api = self::getFacebook()->api("/me/permissions");

        if (empty($permissions_api['data']['0']['publish_stream'])) {
            echo '<script type="text/javascript">window.location = "'.$loginUrl.'";</script>';
            exit();
        }

        // Get the current access token
        self::getFacebook()->setExtendedAccessToken();
        $access_token = self::getFacebook()->getAccessToken();

        // Success
        $response = array(
            'user_profile' => $user_profile,
            'access_token' => $access_token
            );
        return $response;
    }

    /**
     * Check user likes the page in Facebook
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     *
     * @method getUserLikePage
     *
     * @param  integer          $page_id Facebook fan page id
     * @param  integer          $user_id Facebook User id
     *
     * @return array                   User id form facebook if like fan page
     */
    public function getUserLikePage($page_id, $user_id) {
        $response = self::getFacebook()->api(array(
            "method"    => "fql.query",
            "query"     => "SELECT uid FROM page_fan WHERE uid={$user_id} AND page_id={$page_id}"
            )); //,type,page_id,profile_section 1169893316XXXXX
        return $response;
    }

    /**
     * post links, feed to user facebook wall
     *
     * @author Pitchanon D. <Pitchanon.d@gmail.com>
     *
     * @method postToFacebook
     *
     * @param  array         $message Example: $message = array('link' => '', 'message' => '','picture' => '', 'name' => '','description'   => '');
     * @param  string         $type    Type of message (links,feed)
     *
     * @return string                  Id of message
     */
    public function postToFacebook($message, $type = null){
        if (is_null($type)) {
            $type = 'feed';
        }

        // links, feed
        $response = self::getFacebook()->api('/me/' . $type, 'POST', array(
            'link'      => $message['link'],
            'message'    => $message['message'],
            'picture'       => $message['picture'],
            'name'      => $message['name'],
            'description'   => $message['description']
            ));

        return $response; // Array ( [id] => 1330355140_102030093014XXXXX )
    }

    /**
     * This wrapper function exists in order to circumvent PHP’s strict obeying of HTTP error codes. In this case, Facebook returns error code 400 which PHP obeys and wipes out the response.
     *
     * @author Ankur Pansari
     *
     * @method curl_get_file_contents
     *
     * @param  string                 $url Uniform resource locator
     *
     * @return string                      Data
     */
    private function curl_get_file_contents($url) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        $contents = curl_exec($c);
        $err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
        curl_close($c);
        if ($contents) {
            return $contents;
        } else {
            return false;
        }
    }

}
?>