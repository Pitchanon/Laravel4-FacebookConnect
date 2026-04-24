<?php

namespace Pitchanon\FacebookConnect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string getLoginUrl(?string $redirectUri = null, array $scopes = [], ?string $state = null)
 * @method static array  getAccessTokenFromCode(string $code, ?string $redirectUri = null)
 * @method static array  getLongLivedToken(string $shortLivedToken)
 * @method static array  getUser(string $accessToken, array $fields = ['id', 'name', 'email'])
 * @method static array  getUserPermissions(string $accessToken)
 * @method static array  getUserAccounts(string $accessToken)
 * @method static array  getUserFeed(string $accessToken)
 * @method static array  postToFeed(array $message, string $accessToken, string $target = 'me')
 * @method static bool   userLikesPage(string $userId, string $pageId, string $accessToken)
 * @method static array  get(string $endpoint, array $params = [])
 * @method static array  post(string $endpoint, array $params = [])
 *
 * @see \Pitchanon\FacebookConnect\Provider\FacebookConnect
 */
class FacebookConnect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'facebook-connect';
    }
}
