<?php
/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2018 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class PiwikAnalyticsJSPiwikModuleFrontController
 */
class PiwikAnalyticsJSPiwikModuleFrontController extends ModuleFrontController
{
    /**
     * PiwikAnalyticsJSPiwikModuleFrontController constructor.
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function __construct()
    {
        parent::__construct();
        PKHelper::debugLogger('START: PiwikAnalyticsJSPiwikModuleFrontController::__construct();');
        $matomoUrl = ((bool) Configuration::get('PIWIK_CRHTTPS') ? 'https://' : 'http://').Configuration::get('PIWIK_HOST');

        // Edit the line below, and replace xyz by the token_auth for the user "UserTrackingAPI"
        // which you created when you followed instructions above.
        $TOKEN_AUTH = Configuration::get('PIWIK_TOKEN_AUTH');

        PKHelper::debugLogger('Config values Loaded');

        // 1) PIWIK.JS PROXY: No _GET parameter, we serve the JS file
        if (
            (count($_GET) == 3 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc')) ||
            (count($_GET) == 4 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc') && Tools::getIsset('id_lang')) ||
            (count($_GET) == 5 && Tools::getIsset('module') && Tools::getIsset('controller') && Tools::getIsset('fc') && Tools::getIsset('id_lang') && Tools::getIsset('isolang'))
        ) {
            PKHelper::debugLogger('Got piwik.js request with _GET count of : '.count($_GET)."\n".str_repeat('==', 50)."\n".print_r($_GET, true)."\n".str_repeat('==', 50));
            $modifiedSince = false;
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
                // strip any trailing data appended to header
                if (false !== ($semicolon = strpos($modifiedSince, ';'))) {
                    $modifiedSince = substr($modifiedSince, 0, $semicolon);
                }
                $modifiedSince = strtotime($modifiedSince);
            }
            // Re-download the piwik.js once a day maximum
            $lastModified = time() - 86400;
            // set HTTP response headers
            $this->sendHeader('Vary: Accept-Encoding');

            // Returns 304 if not modified since
            if (!empty($modifiedSince) && $modifiedSince < $lastModified) {
                PKHelper::debugLogger('Set Header 304 Not Modified');
                $this->sendHeader(sprintf("%s 304 Not Modified", $_SERVER['SERVER_PROTOCOL']));
            } else {
                $this->sendHeader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                $this->sendHeader('Content-Type: application/javascript; charset=UTF-8');

                PKHelper::debugLogger('Send request to Piwik');
                PKHelper::debugLogger("\t: {$matomoUrl}piwik.js");
                $exHeaders = [sprintf("Accept-Language: %s\r\n", @str_replace(["\n", "\t", "\r"], "", $this->arrayValue($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '')))];
                PKHelper::debugLogger("\t: Extra heders\n".str_repeat('==', 50)."\n".print_r($exHeaders, true)."\n".str_repeat('==', 50));
                if ($piwikJs = PKHelper::get_http($matomoUrl.'piwik.js', $exHeaders)) {
                    PKHelper::debugLogger('Send Piwik js to client');
                    die($piwikJs);
                } else {
                    if (!empty(PKHelper::$errors)) {
                        foreach (PKHelper::$errors as $value) {
                            PKHelper::ErrorLogger($value);
                        }
                    }
                    PKHelper::debugLogger('Error:....'."\n".str_repeat('==', 50).print_r(PKHelper::$error, true)."\n".str_repeat('==', 50)."\n".print_r(PKHelper::$errors, true)."\n".str_repeat('==', 50));
                    $this->sendHeader($_SERVER['SERVER_PROTOCOL'].'505 Internal server error');
                }
            }
            PKHelper::debugLogger('END: PiwikAnalyticsJSPiwikModuleFrontController::__construct();');
            die();
        }
        PKHelper::debugLogger('Got piwik image request with _GET count of :'.count($_GET)."\n".str_repeat('==', 50)."\n".print_r($_GET, true)."\n".str_repeat('==', 50));
        // 2) PIWIK.PHP PROXY: GET parameters found, this is a tracking request, we redirect it to Piwik
        $url = sprintf("%spiwik.php?cip=%s&token_auth=%s&", $matomoUrl, $this->getVisitIp(), $TOKEN_AUTH);

        foreach ($_GET as $key => $value) {
            $url .= urlencode($key).'='.urlencode($value).'&';
        }


        PKHelper::debugLogger('Send request to Piwik ::: '.$url.(version_compare(PHP_VERSION, '5.3.0', '<') ? '&send_image=1' /* PHP 5.2 force returning */ : ''));

        $this->sendHeader("Content-Type: image/gif");
        $content = PKHelper::get_http($url.(version_compare(PHP_VERSION, '5.3.0', '<') ? '&send_image=1' /* PHP 5.2 force returning */ : ''), [sprintf("Accept-Language: %s\r\n", @str_replace(["\n", "\t", "\r"], "", $this->arrayValue($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '')))]);

        PKHelper::debugLogger('Piwik request complete');
        // Forward the HTTP response code
        // not for cURL, working on it. (@todo cURL response_header [piwik.php])
        if (!headers_sent() && isset($http_response_header[0])) {
            header($http_response_header[0]);
        }

        PKHelper::debugLogger('END: PiwikAnalyticsJSPiwikModuleFrontController::__construct();');
        die($content);
    }

    /**
     * @return null
     */
    protected function getVisitIp()
    {
        $matchIp = '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/';
        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP',
        ];
        foreach ($ipKeys as $ipKey) {
            if (isset($_SERVER[$ipKey]) && preg_match($matchIp, $_SERVER[$ipKey])) {
                return $_SERVER[$ipKey];
            }
        }

        return !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    protected function sendHeader($header, $replace = true)
    {
        headers_sent() || header($header, $replace);
    }
}
