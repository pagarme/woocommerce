<?php

namespace Pagarme\Core\Kernel\Services;

/**
 * Single place that resolves the Pagar.me API base URLs.
 *
 * Each URL can be overridden by an environment variable; otherwise the
 * production default is used.
 */
class ApiUrlService
{
    const ENV_HUB_API_URL = 'PAGARME_HUB_API_URL';
    const ENV_API_URL = 'PAGARME_API_URL';

    const DEFAULT_HUB_API_URL = 'https://hubapi.pagar.me';
    const DEFAULT_API_URL = 'https://api.pagar.me';

    /**
     * @return string e.g. https://hubapi.pagar.me
     */
    public static function getHubApiUrl()
    {
        return self::resolve(self::ENV_HUB_API_URL, self::DEFAULT_HUB_API_URL);
    }

    /**
     * @return string e.g. https://api.pagar.me
     */
    public static function getApiUrl()
    {
        return self::resolve(self::ENV_API_URL, self::DEFAULT_API_URL);
    }

    private static function resolve($envName, $default)
    {
        $fromEnv = getenv($envName);
        $url = ($fromEnv !== false && $fromEnv !== '') ? $fromEnv : $default;

        return rtrim($url, '/');
    }
}
