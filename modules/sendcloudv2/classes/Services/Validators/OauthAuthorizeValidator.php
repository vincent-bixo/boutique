<?php
/**
 * Utility class for SendCloud module.
 *
 * PHP version 7.4
 *
 *  @author    SendCloud Global B.V. <contact@sendcloud.eu>
 *  @copyright 2023 SendCloud Global B.V.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  @category  Shipping
 *
 *  @see      https://sendcloud.eu
 */

namespace Sendcloud\PrestaShop\Classes\Services\Validators;

use Sendcloud\PrestaShop\Classes\Bootstrap\ServiceRegister;
use Sendcloud\PrestaShop\Classes\DTO\AuthorizationObject;
use Sendcloud\PrestaShop\Classes\Exceptions\InvalidPayloadException;
use Sendcloud\PrestaShop\Classes\Interfaces\ColumnNamesInterface;
use Sendcloud\PrestaShop\Classes\Services\ConfigService;
use Sendcloud\PrestaShop\Classes\Utilities\Base64UrlEncoder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OauthAuthorizeValidator
 *
 * @package Sendcloud\PrestaShop\Classes\Services\Validators
 */
class OauthAuthorizeValidator
{
    const GRANT_TYPE = 'authorization_code';

    /**
     * Validates payload request
     *
     * @param array $requestData
     * @param string $module
     *
     * @return void
     * @throws InvalidPayloadException
     */
    public static function verifyPayload($requestData, $module)
    {
        $dataExist = self::dataExist($requestData);
        $isDataValid = self::isDataValid($module, $requestData);
        $isFormDataValid = self::checkAuthorizationData($requestData);
        $isCodeValid = self::checkAuthorizationCode($requestData);
        $isCodeChallengeValid = self::checkCodeChallenge($requestData);

        if (!$dataExist || !$isDataValid || !$isFormDataValid || !$isCodeValid || !$isCodeChallengeValid) {
            throw new InvalidPayloadException('Invalid request.');
        }
    }

    /**
     * Checks if all parameters exist in the request
     *
     * @param $requestData
     *
     * @return bool
     */
    private static function dataExist($requestData)
    {
        return isset($requestData['module']);
    }

    /**
     * Checks form parameters
     *
     * @param $requestData
     *
     * @return bool
     */
    private static function checkAuthorizationData($requestData)
    {
        return isset($requestData['code'])
            && isset($requestData['client_id'])
            && isset($requestData['grant_type'])
            && isset($requestData['code_verifier']);
    }

    /**
     * Checks if request data is valid
     *
     * @param $module
     * @param $requestData
     *
     * @return bool
     */
    private static function isDataValid($module, $requestData)
    {
        $isModuleValid = $requestData['module'] === $module;
        $isGrantTypeValid = $requestData['grant_type'] === self::GRANT_TYPE;

        return $isModuleValid && $isGrantTypeValid;
    }

    /**
     * Validates if authorization code is the same as the one saved during connect request
     *
     * @param array $requestData
     *
     * @return bool
     */
    private static function checkAuthorizationCode($requestData)
    {
        /** @var string $authObject */
        $authObject = self::getConfigService()->getConfigValue(ColumnNamesInterface::AUTH_PARAMS);

        if (!$authObject) {
            return false;
        }
        $code = (AuthorizationObject::fromArray(json_decode($authObject, true)))->getAuthCode();

        return $code === $requestData['code'];
    }

    /**
     * Validates if code_verifier is the same as the sha256 computed code_challenge
     *
     * @param array $requestData
     *
     * @return bool
     */
    private static function checkCodeChallenge($requestData)
    {
        /** @var string $authObject */
        $authObject = self::getConfigService()->getConfigValue(ColumnNamesInterface::AUTH_PARAMS);
        $codeChallenge = (AuthorizationObject::fromArray(json_decode($authObject, true)))->getCodeChallenge();
        $computedCodeChallenge = Base64UrlEncoder::encode(hash('sha256', $requestData['code_verifier'], true));

        return $codeChallenge === $computedCodeChallenge;
    }

    /**
     * @return ConfigService
     */
    private static function getConfigService()
    {
        return ServiceRegister::getService(ConfigService::class);
    }
}
