<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Model;

defined('ABSPATH') || exit;

/**
 * Ponto único de resolução das URLs de API.
 *
 * Ordem de resolução para cada endpoint:
 *   1. variável de ambiente (ex.: PAGARME_API_URL)
 *   2. constante PHP com o mesmo nome (ex.: definida no wp-config.php)
 *   3. default de produção (constants.php)
 *
 * O front-end recebe as URLs prontas do backend (ver getFrontendUrls),
 * então nenhuma URL de API deve ficar hardcoded em JS.
 */
final class ApiEndpoints
{
    const ENV_API_URL = 'PAGARME_API_URL';
    const ENV_HUB_API_URL = 'PAGARME_HUB_API_URL';
    const ENV_CDN_URL = 'PAGARME_CDN_URL';

    const CARD_BRANDS_PATH = '/assets/images/logos/brands/png/';

    /**
     * @return string e.g. https://api.pagar.me
     */
    public static function getApiUrl()
    {
        return self::resolve(self::ENV_API_URL, WCMP_DEFAULT_API_URL);
    }

    /**
     * @return string e.g. https://hubapi.pagar.me
     */
    public static function getHubApiUrl()
    {
        return self::resolve(self::ENV_HUB_API_URL, WCMP_DEFAULT_HUB_API_URL);
    }

    /**
     * @return string e.g. https://cdn.mundipagg.com
     */
    public static function getCdnUrl()
    {
        return self::resolve(self::ENV_CDN_URL, WCMP_DEFAULT_CDN_URL);
    }

    /**
     * @return string e.g. https://api.pagar.me/core/v5/tokens
     */
    public static function getTokensUrl()
    {
        return self::getApiUrl() . '/core/v5/tokens';
    }

    /**
     * @return string e.g. https://api.pagar.me/bin/v1/
     */
    public static function getBinUrl()
    {
        return self::getApiUrl() . '/bin/v1/';
    }

    /**
     * @return string e.g. https://cdn.mundipagg.com/assets/images/logos/brands/png/
     */
    public static function getCardBrandsCdnUrl()
    {
        return self::getCdnUrl() . self::CARD_BRANDS_PATH;
    }

    /**
     * URLs consumidas pelo JavaScript do checkout (legado e React).
     *
     * @return array<string, string>
     */
    public static function getFrontendUrls()
    {
        return [
            'tokens'        => self::getTokensUrl(),
            'bin'           => self::getBinUrl(),
            'cardBrandsCdn' => self::getCardBrandsCdnUrl(),
        ];
    }

    /**
     * @param string $envName
     * @param string $default
     * @return string
     */
    private static function resolve($envName, $default)
    {
        $fromEnv = getenv($envName);
        if (is_string($fromEnv) && $fromEnv !== '') {
            return rtrim($fromEnv, '/');
        }

        if (defined($envName) && is_string(constant($envName)) && constant($envName) !== '') {
            return rtrim(constant($envName), '/');
        }

        return $default;
    }
}
