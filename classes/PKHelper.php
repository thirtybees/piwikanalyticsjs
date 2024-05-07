<?php
/**
 * Copyright (C) 2017-2024 thirty bees
 * Copyright (C) 2014 Christian Jensen
 *
 * This file is part of PiwikAnalyticsJS for prestashop.
 *
 * PiwikAnalyticsJS for prestashop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PiwikAnalyticsJS for prestashop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PiwikAnalyticsJS for prestashop.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     thirty bees <contact@thirtybees.com>
 * @author     Christian M. Jensen
 * @deprecated http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class PKHelper
 */
class PKHelper
{
    /** @var array */
    public static $acp = [
        'updatePiwikSite'         => [
            'required' => ['idSite'],
            'optional' => [
                'siteName',
                'urls',
                'ecommerce',
                'siteSearch',
                'searchKeywordParameters',
                'searchCategoryParameters',
                'excludedIps',
                'excludedQueryParameters',
                'timezone',
                'currency',
                'group',
                'startDate',
                'excludedUserAgents',
                'keepURLFragments',
                'type',
            ],
            'order'    => [
                'idSite',
                'siteName',
                'urls',
                'ecommerce',
                'siteSearch',
                'searchKeywordParameters',
                'searchCategoryParameters',
                'excludedIps',
                'excludedQueryParameters',
                'timezone',
                'currency',
                'group',
                'startDate',
                'excludedUserAgents',
                'keepURLFragments',
                'type',
            ],
        ],
        'getPiwikSite'            => ['required' => ['idSite'], 'optional' => [''], 'order' => ['idSite'],],
        'getPiwikSite2'           => ['required' => ['idSite'], 'optional' => [''], 'order' => ['idSite'],],
        'getSitesGroups'          => ['required' => [], 'optional' => [], 'order' => [],],
        'getSitesWithViewAccess'  => ['required' => [], 'optional' => [], 'order' => [],],
        'getSitesWithAdminAccess' => ['required' => [], 'optional' => ['fetchAliasUrls'], 'order' => ['fetchAliasUrls'],],
        'getTokenAuth'            => [
            'required' => ['userLogin'],
            'optional' => ['password', 'md5Password'],
            'order'    => ['userLogin', 'password', 'md5Password'],
        ],
    ];

    /**
     * all errors isset by class PKHelper
     *
     * @var string[]
     */
    public static $errors = [];

    /**
     * last isset error by class PKHelper
     *
     * @var string
     */
    public static $error = '';
    /**
     * @var array
     */
    protected static $_cachedResults = [];

    /**
     * prefix to use for configurations values
     */
    const FAKEUSERAGENT = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0 (Fake Useragent from CLASS:PKHelper.php)";

    /**
     * @var string
     */
    public static $httpAuthUsername = '';

    /**
     * @var string
     */
    public static $httpAuthPassword = '';

    /**
     * @var string
     */
    public static $piwikHost = '';

    /**
     * create a log of all events if set to "1", useful if tracking is not working
     * Log debug == 1
     * DO NOT log == 0
     * log will be saved to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     */
    const DEBUGLOG = 1;

    /**
     * @var FileLogger
     */
    private static $_debug_logger = null;

    /** @var FileLogger */
    private static $_error_logger = null;

    /**
     * logs message to [PS ROOT]/log/YYYYMMDD_piwik.error.log
     *
     * @param string $message
     */
    public static function ErrorLogger($message)
    {
        if (static::$_error_logger == null) {
            static::$_error_logger = new FileLogger(FileLogger::ERROR);
            static::$_error_logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_piwik.error.log');
        }
        static::$_error_logger->logError($message);
    }

    /**
     * logs message to [PS ROOT]/log/YYYYMMDD_piwik.debug.log
     *
     * @param string $message
     */
    public static function debugLogger($message)
    {
        if (PKHelper::DEBUGLOG != 1) {
            return;
        }
        if (static::$_debug_logger == null) {
            static::$_debug_logger = new FileLogger(FileLogger::DEBUG);
            static::$_debug_logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_piwik.debug.log');
        }
        static::$_debug_logger->logDebug($message);
    }

    /**
     *
     * @param string $idSite
     * @param string|null $siteName
     * @param string|null $urls
     * @param string|null $ecommerce
     * @param string|null $siteSearch
     * @param string|null $searchKeywordParameters
     * @param string|null $searchCategoryParameters
     * @param string|null $excludedIps
     * @param string|null $excludedQueryParameters
     * @param string|null $timezone
     * @param string|null $currency
     * @param string|null $group
     * @param string|null $startDate
     * @param string|null $excludedUserAgents
     * @param string|null $keepURLFragments
     * @param string|null $type $type
     *
     * @return bool|string
     *
     * @throws PrestaShopException
     */
    public static function updatePiwikSite(
        $idSite,
        $siteName = null,
        $urls = null,
        $ecommerce = null,
        $siteSearch = null,
        $searchKeywordParameters = null,
        $searchCategoryParameters = null,
        $excludedIps = null,
        $excludedQueryParameters = null,
        $timezone = null,
        $currency = null,
        $group = null,
        $startDate = null,
        $excludedUserAgents = null,
        $keepURLFragments = null,
        $type = null
    ) {
        if (!static::baseTest() || ($idSite <= 0)) {
            return false;
        }
        $url = static::getBaseURL($idSite);
        $url .= "&method=SitesManager.updateSite&format=JSON";
        if ($siteName !== null) {
            $url .= "&siteName=".urlencode($siteName);
        }

        if ($urls !== null) {
            foreach (explode(',', $urls) as $value) {
                $url .= "&urls[]=".urlencode(trim($value));
            }
        }
        if ($ecommerce !== null) {
            $url .= "&ecommerce=".urlencode($ecommerce);
        }
        if ($siteSearch !== null) {
            $url .= "&siteSearch=".urlencode($siteSearch);
        }
        if ($searchKeywordParameters !== null) {
            $url .= "&searchKeywordParameters=".urlencode($searchKeywordParameters);
        }
        if ($searchCategoryParameters !== null) {
            $url .= "&searchCategoryParameters=".urlencode($searchCategoryParameters);
        }
        if ($excludedIps !== null) {
            $url .= "&excludedIps=".urlencode($excludedIps);
        }
        if ($excludedQueryParameters !== null) {
            $url .= "&excludedQueryParameters=".urlencode($excludedQueryParameters);
        }
        if ($timezone !== null) {
            $url .= "&timezone=".urlencode($timezone);
        }
        if ($currency !== null) {
            $url .= "&currency=".urlencode($currency);
        }
        if ($group !== null) {
            $url .= "&group=".urlencode($group);
        }
        if ($startDate !== null) {
            $url .= "&startDate=".urlencode($startDate);
        }
        if ($excludedUserAgents !== null) {
            $url .= "&excludedUserAgents=".urlencode($excludedUserAgents);
        }
        if ($keepURLFragments !== null) {
            $url .= "&keepURLFragments=".urlencode($keepURLFragments);
        }
        if ($type !== null) {
            $url .= "&type=".urlencode($type);
        }
        if ($result = static::getAsJsonDecoded($url)) {
            $url2 = static::getBaseURL($idSite)."&method=SitesManager.getSiteFromId&format=JSON";
            unset(static::$_cachedResults[md5($url2)]); // Clear cache for updated site

            return (
                $result['result'] == 'success'
                && $result['message'] == 'ok' ? true : ($result['result'] != 'success' ? $result['message'] : false)
            );
        } else {
            return false;
        }
    }

    /**
     * get all website groups
     *
     * @return array|false
     * @throws PrestaShopException
     */
    public static function getSitesGroups()
    {
        if (!static::baseTest()) {
            return false;
        }

        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getSitesGroups&format=JSON";

        return ($result = static::getAsJsonDecoded($url)) ? $result : false;
    }

    /**
     * Get users token auth from Piwik
     * NOTE: password is required either an md5 encoded password or a normal string
     *
     * @param string $userLogin the user name
     * @param string|null $password password as clear text string
     * @param string|null $md5Password md5 encoded password
     *
     * @return string|boolean
     * @throws PrestaShopException
     */
    public static function getTokenAuth($userLogin, $password = null, $md5Password = null)
    {
        if (empty($password)) {
            $password = $md5Password;
            if (empty($md5Password)) {
                static::$error = static::l('A password is required for method PKHelper::getTokenAuth()!');
                static::$errors[] = static::$error;

                return false;
            }
        } else {
            $password = md5($password);
        }

        $url = static::getBaseURL(0, null, null, 'API', null, '');
        $url .= "&method=UsersManager.getTokenAuth&userLogin={$userLogin}&md5Password={$password}&format=JSON";
        if ($result = static::getAsJsonDecoded($url)) {
            if (isset($result['result'])) {
                static::$error = $result['message'];
                static::$errors[] = static::$error;
            }

            return isset($result['value']) ? $result['value'] : false;
        } else {
            return false;
        }
    }

    /**
     * Get image tracking code for use with or without proxy script
     *
     * @return array
     * @throws PrestaShopException
     */
    public static function getPiwikImageTrackingCode()
    {
        $ret = [
            'default' => static::l('I need Site ID and Auth Token before i can get your image tracking code'),
            'proxy'   => static::l('I need Site ID and Auth Token before i can get your image tracking code'),
        ];

        $idSite = (int) Configuration::get(PiwikAnalyticsJs::SITEID);
        if (!static::baseTest() || ($idSite <= 0)) {
            return $ret;
        }

        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getImageTrackingCode&format=JSON&actionName=NoJavaScript";
        $url .= "&piwikUrl=".urlencode(rtrim(Configuration::get(PiwikAnalyticsJs::HOST), '/'));
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = false;
            }
        }
        if (static::$_cachedResults[$md5Url] !== false) {
            $ret['default'] = htmlentities('<noscript>'.static::$_cachedResults[$md5Url]['value'].'</noscript>');
            if (Configuration::get('PS_REWRITING_SETTINGS')) {
                $ret['proxy'] = str_replace(
                    Configuration::get(PiwikAnalyticsJs::HOST).'piwik.php',
                    Configuration::get('PIWIK'.'PROXY_SCRIPT'),
                    $ret['default']
                );
            } else {
                $ret['proxy'] = str_replace(
                    Configuration::get(PiwikAnalyticsJs::HOST).'piwik.php?',
                    Configuration::get('PIWIK'.'PROXY_SCRIPT').'&',
                    $ret['default']
                );
            }
        }

        return $ret;
    }

    /**
     * get Piwik site based on the current settings in the configuration
     *
     * @param int $idSite
     *
     * @return array|false
     *
     * @throws PrestaShopException
     */
    public static function getPiwikSite($idSite = 0)
    {
        if ($idSite == 0) {
            $idSite = (int) Configuration::get(PiwikAnalyticsJs::SITEID);
        }
        if (!static::baseTest() || ($idSite <= 0)) {
            return false;
        }

        $url = static::getBaseURL($idSite);
        $url .= "&method=SitesManager.getSiteFromId&format=JSON";
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = false;
            }
        }
        if (static::$_cachedResults[$md5Url] !== false) {
            if (isset(static::$_cachedResults[$md5Url]['result']) && static::$_cachedResults[$md5Url]['result'] === 'error') {
                static::$error = static::$_cachedResults[$md5Url]['message'];
                static::$errors[] = static::$error;

                return false;
            }
            if (!isset(static::$_cachedResults[$md5Url][0])) {
                return false;
            }
            if ((bool) static::$_cachedResults[$md5Url][0]['ecommerce'] === false || static::$_cachedResults[$md5Url][0]['ecommerce'] == 0) {
                static::$error = static::l('E-commerce is not active for your site in Matomo!, you can enable it in the advanced settings on this page');
                static::$errors[] = static::$error;
            }
            if ((bool) static::$_cachedResults[$md5Url][0]['sitesearch'] === false || static::$_cachedResults[$md5Url][0]['sitesearch'] == 0) {
                static::$error = static::l('Site search is not active for your site in Matomo!, you can enable it in the advanced settings on this page');
                static::$errors[] = static::$error;
            }

            return static::$_cachedResults[$md5Url];
        }

        return false;
    }

    /**
     * @param int $idSite
     *
     * @return array|false
     * @throws PrestaShopException
     */
    public static function getPiwikSite2($idSite = 0)
    {
        if ($idSite == 0) {
            $idSite = (int) Configuration::get(PiwikAnalyticsJs::SITEID);
        }
        if ($result = static::getPiwikSite($idSite)) {
            $url = static::getBaseURL($idSite);
            $url .= "&method=SitesManager.getSiteUrlsFromId&format=JSON";
            if ($resultUrls = static::getAsJsonDecoded($url)) {
                $result[0]['main_url'] = implode(',', $resultUrls);
            }

            return $result;
        }

        return false;
    }

    /**
     * get all supported time zones from piwik
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public static function getTimezonesList()
    {
        if (!static::baseTest()) {
            return [];
        }
        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getTimezonesList&format=JSON";
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = [];
            }
        }

        return static::$_cachedResults[$md5Url];
    }

    /**
     * @return array|false
     *
     * @throws PrestaShopException
     */
    public static function getSitesWithViewAccess()
    {
        if (!static::baseTest()) {
            return [];
        }
        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getSitesWithViewAccess&format=JSON";
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = [];
            }
        }

        return static::$_cachedResults[$md5Url];
    }

    /**
     * Alias of PKHelper::getSitesWithAdminAccess()
     * get all Piwik sites the current authentication token has admin access to
     *
     * @param boolean $fetchAliasUrls
     *
     * @return array|false
     * @throws PrestaShopException
     */
    public static function getMyPiwikSites($fetchAliasUrls = false)
    {
        return static::getSitesWithAdminAccess($fetchAliasUrls);
    }

    /**
     * get all Piwik sites the current authentication token has admin access to
     *
     * @param bool $fetchAliasUrls
     *
     * @return array|false
     * @throws PrestaShopException
     */
    public static function getSitesWithAdminAccess($fetchAliasUrls = false)
    {
        if (!static::baseTest()) {
            return [];
        }
        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getSitesWithAdminAccess&format=JSON".($fetchAliasUrls ? '&fetchAliasUrls=1' : '');
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url."2"])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = [];
            }
        }

        return static::$_cachedResults[$md5Url];
    }

    /**
     * Get all Piwik siteIDs the current authentication token has admin access to
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public static function getMyPiwikSiteIds()
    {
        if (!static::baseTest()) {
            return [];
        }
        $url = static::getBaseURL();
        $url .= "&method=SitesManager.getSitesIdWithAdminAccess&format=JSON";
        $md5Url = md5($url);
        if (!isset(static::$_cachedResults[$md5Url])) {
            if ($result = static::getAsJsonDecoded($url)) {
                static::$_cachedResults[$md5Url] = $result;
            } else {
                static::$_cachedResults[$md5Url] = [];
            }
        }

        return static::$_cachedResults[$md5Url];
    }

    /**
     * Get the base URL for all requests to Matomo
     *
     * @param int    $idSite
     * @param string $pkHost
     * @param bool   $https
     * @param string $pkModule
     * @param string $isoCode
     * @param string $tokenAuth
     *
     * @return string
     * @throws PrestaShopException
     */
    protected static function getBaseURL($idSite = null, $pkHost = null, $https = null, $pkModule = 'API', $isoCode = null, $tokenAuth = null)
    {
        if ($https === null) {
            $https = (bool) Configuration::get(PiwikAnalyticsJs::CRHTTPS);
        }

        if (! static::$piwikHost) {
            static::$piwikHost = (string)Configuration::get(PiwikAnalyticsJs::HOST);
        }

        if ($pkHost === null) {
            $pkHost = static::$piwikHost;
        }
        $pkHost = rtrim($pkHost, '/').'/';
        if ($isoCode === null) {
            $isoCode = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        }
        if ($idSite === null) {
            $idSite = Configuration::get(PiwikAnalyticsJs::SITEID);
        }
        if ($tokenAuth === null) {
            $tokenAuth = Configuration::get(PiwikAnalyticsJs::TOKEN_AUTH);
        }


        return ($https ? 'https' : 'http')."://{$pkHost}index.php?module={$pkModule}&language={$isoCode}&idSite={$idSite}&token_auth={$tokenAuth}";
    }

    /**
     * Check if the basics are there before we make any Matomo requests
     *
     * @return boolean
     * @throws PrestaShopException
     */
    protected static function baseTest()
    {
        static $_error1 = false;
        $pkToken = Configuration::get(PiwikAnalyticsJs::TOKEN_AUTH);
        $pkHost = Configuration::get(PiwikAnalyticsJs::HOST);
        if (empty($pkToken) || empty($pkHost)) {
            if (!$_error1) {
                static::$error = static::l('Matomo auth token and/or Matomo site id cannot be empty');
                static::$errors[] = static::$error;
                $_error1 = true;
            }

            return false;
        }

        return true;
    }

    /**
     * get output of api as json decoded object
     *
     * @param string $url the full http(s) url to use for fetching the api result
     *
     * @return array|false
     * @throws PrestaShopException
     */
    protected static function getAsJsonDecoded($url)
    {
        $getF = static::getHttp($url);
        if ($getF !== false) {
            return json_decode($getF, true);
        }

        return false;
    }

    /**
     * @param string $url
     * @param array $headers
     *
     * @return false|string
     * @throws PrestaShopException
     */
    public static function getHttp($url, $headers = [])
    {
        static $_error2 = false;
        PKHelper::debugLogger('START: PKHelper::get_http('.$url.','.print_r($headers, true).')');
        $lng = strtolower((isset(Context::getContext()->language->iso_code) ? Context::getContext()->language->iso_code : 'en'));
        $timeout = 5; // should go in module conf

        if (! static::$httpAuthUsername) {
            static::$httpAuthUsername = (string)Configuration::get(PiwikAnalyticsJs::PAUTHUSR);
        }
        if (! static::$httpAuthPassword) {
            static::$httpAuthPassword = (string)Configuration::get(PiwikAnalyticsJs::PAUTHPWD);
        }

        $httpauth_usr = static::$httpAuthUsername;
        $httpauth_pwd = static::$httpAuthPassword;

        PKHelper::debugLogger('Using \'file_get_contents\' to fetch remote');
        $httpauth = '';
        if (!empty($httpauth_usr) && !empty($httpauth_pwd)) {
            $httpauth = "Authorization: Basic ".base64_encode("$httpauth_usr:$httpauth_pwd")."\r\n";
        }
        $headers = (!empty($headers) ? implode('', $headers) : "Accept-language: {$lng}\r\n").$httpauth;
        $headers .= 'User-Agent:'.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : PKHelper::FAKEUSERAGENT);
        $newHeaders = [];
        foreach (explode("\r\n", $headers) as $header) {
            $headersParts = array_map('trim', array_pad(explode(':', $header), 2, ''));
            $newHeaders[$headersParts[0]] = $headersParts[1];
        }
        $guzzle = new Client([
            'verify'          => _PS_TOOL_DIR_.'cacert.pem',
            'timeout'         => (int) $timeout,
            'connect_timeout' => (int) $timeout,
            'headers'         => $newHeaders,
        ]);
        PKHelper::debugLogger('Calling: '.$url.(!empty($httpauth) ? "\n\t- With Http auth" : ""));
        try {
            $result = (string) $guzzle->get($url)->getBody();
        } catch (ClientException $e) {
            $responseInterface = $e->getResponse();
            $body = (string) $responseInterface->getBody();
            PKHelper::debugLogger("request returned ERROR: http response: {$body}.");
            $headerString = '';
            foreach ($responseInterface->getHeaders() as $key => $headerValues) {
                $header = implode(', ', $headerValues);
                $headerString .= "$key:$header\r\n";
            }
            PKHelper::debugLogger("http_response_header: {$headerString}");
            if (!$_error2) {
                static::$error = sprintf(static::l('Unable to connect to the API%s'), " {$body}");
                static::$errors[] = static::$error;
                $_error2 = true;
                PKHelper::debugLogger('Last error message: '.static::$error);
            }
            return false;
        } catch (GuzzleException $e) {
            PKHelper::debugLogger("request returned ERROR: http response: {$e->getMessage()}");
            return false;
        } catch (Exception $e) {
            PKHelper::debugLogger("request returned ERROR: http response: {$e->getMessage()}");
            return false;
        }

        PKHelper::debugLogger('request returned OK');
        PKHelper::debugLogger('END: PKHelper::get_http(): OK');

        return $result;
    }

    /**
     * @param string $string
     * @param bool $specific
     *
     * @return string
     */
    private static function l($string, $specific = false)
    {
        return Translate::getModuleTranslation('piwikanalyticsjs', $string, ($specific) ? $specific : 'pkhelper');
        // the following lines are need for the translation to work properly
        // $this->l('I need Site ID and Auth Token before i can get your image tracking code')
        // $this->l('E-commerce is not active for your site in Matomo!, you can enable it in the advanced settings on this page')
        // $this->l('Site search is not active for your site in Matomo!, you can enable it in the advanced settings on this page')
        // $this->l('Unable to connect to api %s')
        // $this->l('E-commerce is not active for your site in Matomo!')
        // $this->l('Site search is not active for your site in Matomo!')
        // $this->l('A password is required for method PKHelper::getTokenAuth()!')
    }
}
