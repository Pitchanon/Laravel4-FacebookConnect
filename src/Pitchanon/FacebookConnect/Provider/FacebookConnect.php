<?php

namespace Pitchanon\FacebookConnect\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Pitchanon\FacebookConnect\Exceptions\FacebookConnectException;

class FacebookConnect
{
    protected const OAUTH_BASE = 'https://www.facebook.com';
    protected const GRAPH_BASE = 'https://graph.facebook.com';

    protected string $appId;
    protected string $appSecret;
    protected string $graphVersion;
    protected ?string $defaultRedirectUri;
    protected array $defaultScopes;
    protected Client $http;

    public function __construct(array $config = [], ?Client $http = null)
    {
        $this->appId              = (string) ($config['app_id'] ?? '');
        $this->appSecret          = (string) ($config['app_secret'] ?? '');
        $this->graphVersion       = (string) ($config['graph_version'] ?? 'v19.0');
        $this->defaultRedirectUri = $config['redirect_uri'] ?? null;
        $this->defaultScopes      = (array) ($config['default_scopes'] ?? ['email', 'public_profile']);

        $this->http = $http ?? new Client([
            'timeout' => (int) ($config['http_timeout'] ?? 15),
        ]);
    }

    public function getLoginUrl(?string $redirectUri = null, array $scopes = [], ?string $state = null): string
    {
        $this->requireAppConfig();

        $redirectUri = $redirectUri ?? $this->defaultRedirectUri;
        if (empty($redirectUri)) {
            throw new FacebookConnectException('A redirect URI is required to build the login URL.');
        }

        $params = [
            'client_id'     => $this->appId,
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(',', $scopes ?: $this->defaultScopes),
            'response_type' => 'code',
        ];

        if ($state !== null) {
            $params['state'] = $state;
        }

        return self::OAUTH_BASE . '/' . $this->graphVersion . '/dialog/oauth?' . http_build_query($params);
    }

    public function getAccessTokenFromCode(string $code, ?string $redirectUri = null): array
    {
        $this->requireAppConfig();

        $redirectUri = $redirectUri ?? $this->defaultRedirectUri;
        if (empty($redirectUri)) {
            throw new FacebookConnectException('A redirect URI is required to exchange the code.');
        }

        return $this->get('/oauth/access_token', [
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ]);
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $this->requireAppConfig();

        return $this->get('/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $this->appId,
            'client_secret'     => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);
    }

    public function getUser(string $accessToken, array $fields = ['id', 'name', 'email']): array
    {
        return $this->get('/me', [
            'fields'       => implode(',', $fields),
            'access_token' => $accessToken,
        ]);
    }

    public function getUserPermissions(string $accessToken): array
    {
        return $this->get('/me/permissions', ['access_token' => $accessToken]);
    }

    public function hasGrantedPermissions(string $accessToken, array $required): bool
    {
        $response = $this->getUserPermissions($accessToken);
        $granted  = [];

        foreach ($response['data'] ?? [] as $perm) {
            if (($perm['status'] ?? '') === 'granted' && isset($perm['permission'])) {
                $granted[] = $perm['permission'];
            }
        }

        foreach ($required as $perm) {
            if (!in_array($perm, $granted, true)) {
                return false;
            }
        }

        return true;
    }

    public function getUserAccounts(string $accessToken): array
    {
        return $this->get('/me/accounts', ['access_token' => $accessToken]);
    }

    public function getUserFeed(string $accessToken): array
    {
        return $this->get('/me/feed', ['access_token' => $accessToken]);
    }

    public function postToFeed(array $message, string $accessToken, string $target = 'me'): array
    {
        return $this->post('/' . ltrim($target, '/') . '/feed', $message + [
            'access_token' => $accessToken,
        ]);
    }

    public function userLikesPage(string $userId, string $pageId, string $accessToken): bool
    {
        $response = $this->get('/' . $userId . '/likes/' . $pageId, [
            'access_token' => $accessToken,
        ]);

        return !empty($response['data']);
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $params]);
    }

    public function post(string $endpoint, array $params = []): array
    {
        return $this->request('POST', $endpoint, ['form_params' => $params]);
    }

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        $url = self::GRAPH_BASE . '/' . $this->graphVersion . '/' . ltrim($endpoint, '/');

        try {
            $response = $this->http->request($method, $url, $options + ['http_errors' => false]);
        } catch (GuzzleException $e) {
            throw new FacebookConnectException('HTTP request to Graph API failed: ' . $e->getMessage(), 0, $e);
        }

        $body    = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            throw new FacebookConnectException(
                'Unexpected Graph API response (status ' . $response->getStatusCode() . '): ' . $body
            );
        }

        if (isset($decoded['error'])) {
            $message = $decoded['error']['message'] ?? 'Unknown Graph API error';
            $code    = (int) ($decoded['error']['code'] ?? 0);
            throw new FacebookConnectException($message, $code);
        }

        return $decoded;
    }

    protected function requireAppConfig(): void
    {
        if ($this->appId === '' || $this->appSecret === '') {
            throw new FacebookConnectException(
                'Facebook app_id and app_secret must be configured before using this method.'
            );
        }
    }
}
