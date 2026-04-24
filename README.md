## Facebook Connect for Laravel

A small Laravel package that wraps the Meta (Facebook) Graph API for the common
OAuth login flow, profile lookup, page posting, and "does this user like a page"
checks. Works with Laravel 11 / 12 and PHP 8.2+.

The package talks to Graph API **v19.0** by default over plain HTTP (Guzzle), so
it does not depend on the archived `facebook/graph-sdk` package.

### Version compatibility

| Package version | Laravel          | PHP     | Facebook SDK                  | Status       |
| --------------- | ---------------- | ------- | ----------------------------- | ------------ |
| `^2.0`          | 11.x, 12.x       | ^8.2    | Graph API v19 (Guzzle client) | Current      |
| `^1.0`          | 4.x, 5.x         | >= 5.3  | Bundled Facebook PHP SDK 3.x  | Maintenance  |

The legacy 1.x line is kept on the `1.x` branch for existing Laravel 4/5 users.
Critical fixes may still be backported, but new features only land on `2.x`.

### Installation

Latest (Laravel 11/12):

```bash
composer require pitchanon/facebook-connect:^2.0
```

Legacy (Laravel 4/5):

```bash
composer require pitchanon/facebook-connect:^1.0
```

The service provider and `FacebookConnect` facade are auto-discovered.

Publish the config file:

```bash
php artisan vendor:publish --tag=facebook-connect-config
```

Then set your Meta app credentials in `.env`:

```env
FACEBOOK_APP_ID=your-app-id
FACEBOOK_APP_SECRET=your-app-secret
FACEBOOK_REDIRECT_URI=https://your-app.test/auth/facebook/callback
FACEBOOK_GRAPH_VERSION=v19.0
```

### Usage

#### 1. Redirect the user to Facebook

```php
use FacebookConnect;

Route::get('/auth/facebook', function () {
    $state = bin2hex(random_bytes(16));
    session(['fb_oauth_state' => $state]);

    return redirect(FacebookConnect::getLoginUrl(
        redirectUri: null, // falls back to config
        scopes: ['email', 'public_profile'],
        state: $state,
    ));
});
```

#### 2. Handle the callback

```php
Route::get('/auth/facebook/callback', function (\Illuminate\Http\Request $request) {
    abort_unless(hash_equals(session('fb_oauth_state', ''), (string) $request->query('state')), 419);

    $token = FacebookConnect::getAccessTokenFromCode($request->query('code'));
    $longLived = FacebookConnect::getLongLivedToken($token['access_token']);

    $profile = FacebookConnect::getUser($longLived['access_token'], ['id', 'name', 'email']);

    // persist $profile + $longLived['access_token'] however you like
    return $profile;
});
```

#### 3. Post to a user or page feed

```php
FacebookConnect::postToFeed([
    'message' => 'Hello from Laravel!',
    'link'    => 'https://example.com',
], $accessToken, target: 'me');
```

For page posts, pass the page ID as `target` and use a page access token
(available via `FacebookConnect::getUserAccounts($userAccessToken)`).

#### 4. Check if a user likes a page

```php
$likes = FacebookConnect::userLikesPage($userId, $pageId, $accessToken);
```

#### 5. Verify granted permissions

```php
if (!FacebookConnect::hasGrantedPermissions($accessToken, ['email'])) {
    return redirect(FacebookConnect::getLoginUrl());
}
```

#### 6. Arbitrary Graph calls

```php
FacebookConnect::get('/me/photos', ['access_token' => $accessToken, 'limit' => 5]);
FacebookConnect::post('/me/feed', ['message' => 'Hi', 'access_token' => $accessToken]);
```

### Errors

All Graph API errors are thrown as
`Pitchanon\FacebookConnect\Exceptions\FacebookConnectException` with the message
and code returned by Meta.

### Migration notes (from the old Laravel 4 version)

- The bundled Facebook PHP SDK v3.2.2 has been removed &mdash; it has been
  unmaintained since 2018 and relied on deprecated endpoints (`fql.query`,
  `setExtendedAccessToken`, cookie-based sessions).
- `FacebookConnect::getUser($permissions, $url_app)` no longer exists as a
  one-shot "redirect or return profile" method. Use `getLoginUrl()` +
  `getAccessTokenFromCode()` + `getUser($accessToken)` instead &mdash; this is
  testable and does not `echo`/`exit` from inside the class.
- `offline_access` and `publish_stream` have been removed by Meta years ago.
  Use long-lived tokens (`getLongLivedToken()`) and the current publishing
  permissions (e.g. `pages_manage_posts` for page posting, subject to app
  review).
- The check-fan call no longer uses FQL (deprecated since 2016); it uses the
  `/{user-id}/likes/{page-id}` edge instead.

### License

GPL-3.0-or-later. See `LICENSE`.
