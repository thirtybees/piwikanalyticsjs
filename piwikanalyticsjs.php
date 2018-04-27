<?php
/**
 * Copyright (C) 2017-2018 thirty bees
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

if (!defined('_TB_VERSION_')) {
    exit;
}

require_once __DIR__.'/vendor/autoload.php';

/**
 * Class PiwikAnalyticsJs
 */
class PiwikAnalyticsJs extends Module
{
    /**
     * setReferralCookieTimeout
     */
    const PK_RC_TIMEOUT = 262974;
    /**
     * setVisitorCookieTimeout
     */
    const PK_VC_TIMEOUT = 569777;
    /**
     * setSessionCookieTimeout
     */
    const PK_SC_TIMEOUT = 30;
    const TOKEN_AUTH = 'PIWIK_TOKEN_AUTH';
    const SITEID = 'PIWIK_SITEID';
    const HOST = 'PIWIK_HOST';
    const USE_PROXY = 'PIWIK_USE_PROXY';
    const PROXY_SCRIPT = 'PIWIK_PROXY_SCRIPT';
    const COOKIE_DOMAIN = 'PIWIK_COOKIE_DOMAIN';
    const DNT = 'PIWIK_DNT';
    const DEFAULT_CURRENCY = 'PIWIK_DEFAULT_CURRENCY';
    const USRNAME = 'PIWIK_USRNAME';
    const USRPASSWD = 'PIWIK_USRPASSWD';
    const CRHTTPS = 'PIWIK_CRHTTPS';
    const PRODID_V1 = 'PIWIK_PRODID_V1';
    const PRODID_V2 = 'PIWIK_PRODID_V2';
    const PRODID_V3 = 'PIWIK_PRODID_V3';
    const SESSION_TIMEOUT = 'PIWIK_SESSION_TIMEOUT';
    const COOKIE_TIMEOUT = 'PIWIK_COOKIE_TIMEOUT';
    const PAUTHUSR = 'PIWIK_PAUTHUSR';
    const PAUTHPWD = 'PIWIK_PAUTHPWD';
    const EXHTML = 'PIWIK_EXHTML';
    const SET_DOMAINS = 'PIWIK_SET_DOMAINS';
    const DREPDATE = 'PIWIK_DREPDATE';
    const RCOOKIE_TIMEOUT = 'PIWIK_RCOOKIE_TIMEOUT';
    const ORDER = 'PIWIK_ORDER';
    const ORDER_PRODUCTS = 'PIWIK_ORDER_PRODUCTS';
    const ORDER_DETAILS = 'PIWIK_ORDER_DETAILS';
    const CART = 'PIWIK_CART';
    const UUID = 'PIWIK_UUID';
    const SITE_SEARCH = 'PIWIK_SITE_SEARCH';
    const TAPID = 'PIWIK_TAPID';
    const CART_PRODUCTS = 'PIWIK_CART_PRODUCTS';
    const CART_TOTAL = 'PIWIK_CART_TOTAL';
    const PRODUCTS = 'PIWIK_PRODUCTS';
    const category = 'PIWIK_category';
    /** @var bool $isOrder */
    private static $isOrder = false;
    /** @var bool $matomoSite */
    protected $matomoSite = false;

    /**
     * PiwikAnalyticsJs constructor.
     *
     * @param string|null  $name
     * @param Context|null $context
     *
     * @throws PrestaShopException
     */
    public function __construct($name = null, $context = null)
    {
        $this->name = 'piwikanalyticsjs';
        $this->tab = 'analytics_stats';
        $this->version = '1.2.0';
        $this->author = 'thirty bees';
        $this->displayName = $this->l('Matomo Web Analytics');

        $this->bootstrap = true;

        parent::__construct($name, ($context instanceof Context ? $context : null));

        // Warnings on module list page
        $warnings = [];
        if ($this->id && !Configuration::get(static::TOKEN_AUTH)) {
            $warnings[] = $this->l('is not ready to roll you need to configure the auth token');
        }
        if ($this->id && !Configuration::get(static::SITEID)) {
            $warnings[] = $this->l('You have not yet set your Matomo Site ID');
        }
        if ($this->id && !Configuration::get(static::HOST)) {
            $warnings[] = $this->l('is not ready to roll. You need to configure the Matomo server URL');
        }
        $this->warning = implode('<br>', $warnings);

        $this->description = $this->l('Matomo Web Analytics JavaScript plugin');
    }

    /**
     * get content to display in the admin area
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @global string $currentIndex
     */
    public function getContent()
    {
        if (Tools::getIsset('pkapicall')) {
            $this->__pkapicall();
            die();
        }
        $this->context->controller->addJS($this->_path.'views/js/sweetalert-2.1.0.min.js');

        $this->context->controller->addJqueryPlugin('tagify', _PS_JS_DIR_.'jquery/plugins/');

        $this->processFormsUpdate();
        if (Configuration::get(static::TOKEN_AUTH)) {
            $this->matomoSite = PKHelper::getPiwikSite();
        } else {
            $this->matomoSite = false;
        }

        foreach (PKHelper::$errors as $error) {
            $this->context->controller->errors[] = $error;
        }
        PKHelper::$error = '';
        PKHelper::$errors = [];

        if ($this->id && !Configuration::get(static::TOKEN_AUTH) && !Tools::getIsset(static::TOKEN_AUTH)) {
            $this->context->controller->errors[] = $this->l('Matomo auth token is empty');
        }
        if ($this->id && ((int) Configuration::get(static::SITEID) <= 0) && !Tools::getIsset(static::SITEID)) {
            $this->context->controller->errors[] = $this->l('Matomo site id is lower or equal to "0"');
        }
        if ($this->id && !Configuration::get(static::HOST)) {
            $this->context->controller->errors[] = $this->l('Matomo host cannot be empty');
        }

        $fieldsForm = [];

        $languages = Language::getLanguages(false);
        foreach ($languages as $languages_key => $languages_value) {
            // is_default
            $languages[$languages_key]['is_default'] = ($languages_value['id_lang'] == (int) Configuration::get('PS_LANG_DEFAULT') ? true : false);
        }
        $helper = new HelperForm();
        $helper->module = $this;

        $helper->languages = $languages;
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->currentIndex = AdminController::$currentIndex."&configure={$this->name}";
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->title = $this->displayName;
        $helper->submit_action = "submitUpdate{$this->name}";

        if (Configuration::get(static::TOKEN_AUTH)) {
            $imageTracking = PKHelper::getPiwikImageTrackingCode();
        } else {
            $imageTracking = [
                'default' => $this->l('I need your Site ID and Auth Token before I can grab your image tracking code'),
                'proxy'   => $this->l('I need your Site ID and Auth Token before I can grab your image tracking code'),
            ];
        }
        $this->context->smarty->assign([
            'matomoSiteName' => isset($this->matomoSite[0]['name']) ? $this->matomoSite[0]['name'] : '',
            'matomoSiteId'   => isset($this->matomoSite[0]['idsite']) ? $this->matomoSite[0]['idsite'] : '',
            'matomoSiteUrl'  => isset($this->matomoSite[0]['main_url']) ? $this->matomoSite[0]['main_url'] : '',
            'imageTracking'  => $imageTracking,
        ]);

        $fieldsForm[0]['form']['legend'] = [
            'title'  => $this->displayName,
            'image'  => $this->_path.'logo.gif',
            'width'  => 20,
            'height' => 20,
        ];

        if ($this->matomoSite !== false) {
            $fieldsForm[0]['form']['input'][] = [
                'type' => 'html',
                'name' => $this->display(__FILE__, 'views/templates/admin/desc_site_wizard.tpl'),
            ];
        } else {
            $fieldsForm[0]['form']['input'][] = [
                'type' => 'html',
                'name' => $this->display(__FILE__, 'views/templates/admin/desc_site_wizard.tpl'),
            ];
        }

        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Matomo Host'),
            'name'     => static::HOST,
            'desc'     => $this->l('Example: www.example.com/matomo/'),
            'hint'     => $this->l('The host where Matomo is installed'),
            'required' => true,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'   => 'switch',
            'label'  => $this->l('Use proxy script'),
            'name'   => static::USE_PROXY,
            'desc'   => $this->l('Whether or not to use the proxy instead of the Matomo host directly'),
            'values' => [
                [
                    'id'    => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ],
                [
                    'id'    => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
            ],
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Proxy script'),
            'name'     => static::PROXY_SCRIPT,
            'hint'     => $this->l('Example: www.example.com/pkproxy.php'),
            'desc'     => sprintf(
                $this->l('the FULL path to proxy script to use, build-in: [%s]'),
                $this->context->link->getModuleLink($this->name, 'piwik', [], true)
            ),
            'required' => false,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Matomo site id'),
            'name'     => static::SITEID,
            'desc'     => $this->l('Example: 10'),
            'hint'     => $this->l('You can find your Matomo Site ID by logging in your Matomo installation. Under Settings > Websites > Manage'),
            'required' => true,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Matomo auth token'),
            'name'     => static::TOKEN_AUTH,
            'desc'     => $this->l('You can find Matomo token by logging in on your Matomo installation. Under Settings > Platform > API'),
            'required' => true,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Track visitors across subdomains'),
            'name'     => static::COOKIE_DOMAIN,
            'desc'     => $this->l('The default is the document domain; if your web site can be visited at both www.example.com and example.com, you would use: "*.example.com" OR ".example.com" without the quotes')
                .'<br />'
                .$this->l('Leave empty to exclude this from the tracking code'),
            'hint'     => $this->l('So if one visitor visits x.example.com and y.example.com, they will be counted as a unique visitor. (setCookieDomain)'),
            'required' => false,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'     => 'text',
            'label'    => $this->l('Hide known alias URLs'),
            'name'     => static::SET_DOMAINS,
            'desc'     => $this->l('In the "Outlinks" report, hide clicks to known alias URLs, Example: *.example.com')
                .'<br />'
                .$this->l('Note: to add multiple domains you must separate them with space " " one space')
                .'<br />'
                .$this->l('Note: the currently tracked website is added to this array automatically')
                .'<br />'
                .$this->l('Leave empty to exclude this from the tracking code'),
            'hint'     => $this->l('So clicks on links to Alias URLs (eg. x.example.com) will not be counted as "Outlink". (setDomains)'),
            'required' => false,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'    => 'switch',
            'label'   => $this->l('Enable client-side DoNotTrack detection'),
            'name'    => static::DNT,
            'desc'    => $this->l('So tracking requests will not be sent if visitors do not wish to be tracked.'),
            'values'  => [
                [
                    'id'    => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Enabled'),
                ],
                [
                    'id'    => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
            ],
        ];

        foreach (PKHelper::$errors as $error) {
            $this->context->controller->errors[] = $error;
        }
        PKHelper::$error = '';
        PKHelper::$errors = [];

        $fieldsForm[0]['form']['input'][] = [
            'type' => 'html',
            'name' => $this->display(__FILE__, 'views/templates/admin/desc_image_tracking.tpl'),
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'                      => 'code',
            'label'                     => $this->l('Extra HTML'),
            'id'                        => static::EXHTML,
            'name'                      => static::EXHTML,
            'desc'                      => $this->l('Some extra HTML code to put after the Matomo tracking code, this can be any html of your choice'),
            'rows'                      => 10,
            'cols'                      => 50,
            'mode'                      => 'html',
            'enableBasicAutocompletion' => true,
            'enableSnippets'            => true,
            'enableLiveAutocompletion'  => true,
        ];

        $fieldsForm[0]['form']['input'][] = [
            'type'    => 'select',
            'label'   => $this->l('Matomo Currency'),
            'name'    => static::DEFAULT_CURRENCY,
            'desc'    => sprintf($this->l('Based on your settings in Matomo your default currency is %s'), ($this->matomoSite !== false ? $this->matomoSite[0]['currency'] : $this->l('unknown'))),
            'options' => [
                'default' => ['value' => 0, 'label' => $this->l('Choose currency')],
                'query'   => array_map(function ($currency) {
                    return [
                        'iso_code' => $currency['iso_code'],
                        'name'     => "{$currency['name']} ({$currency['iso_code']})",
                    ];
                }, Currency::getCurrencies()),
                'id'      => 'iso_code',
                'name'    => 'name',
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            'type'    => 'select',
            'label'   => $this->l('Matomo Report date'),
            'name'    => static::DREPDATE,
            'desc'    => $this->l('Report date to load by default from "Stats => Matomo Analytics"'),
            'options' => [
                'default' => ['value' => 'day|today', 'label' => $this->l('Today')],
                'query'   => [
                    ['str' => 'day|today', 'name' => $this->l('Today')],
                    ['str' => 'day|yesterday', 'name' => $this->l('Yesterday')],
                    ['str' => 'range|previous7', 'name' => $this->l('Previous 7 days (not including today)')],
                    ['str' => 'range|previous30', 'name' => $this->l('Previous 30 days (not including today)')],
                    ['str' => 'range|last7', 'name' => $this->l('Last 7 days (including today)')],
                    ['str' => 'range|last30', 'name' => $this->l('Last 30 days (including today)')],
                    ['str' => 'week|today', 'name' => $this->l('Current Week')],
                    ['str' => 'month|today', 'name' => $this->l('Current Month')],
                    ['str' => 'year|today', 'name' => $this->l('Current Year')],
                ],
                'id'      => 'str',
                'name'    => 'name',
            ],
        ];

        $fieldsForm[0]['form']['input'][] = [
            'type'         => 'text',
            'label'        => $this->l('Matomo Username'),
            'name'         => static::USRNAME,
            'desc'         => $this->l('You can store your Username for Matomo here to make it easy to open Matomo interface from your stats page with automatic login'),
            'required'     => false,
            'autocomplete' => false,
        ];
        $fieldsForm[0]['form']['input'][] = [
            'type'         => 'password',
            'label'        => $this->l('Matomo Password'),
            'name'         => static::USRPASSWD,
            'desc'         => $this->l('You can store your Password for Matomo here to make it easy to open Matomo interface from your stats page with automatic login'),
            'required'     => false,
            'autocomplete' => false,
            'class'        => 'fixed-width-xxl',
        ];

        $fieldsForm[0]['form']['buttons'] = ['submit' => [
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right',
            'icon'  => 'process-icon-save',
            'js'    => 'return submitPiwikSiteAPIUpdate(event, false)',
        ]];

        $fieldsForm[1]['form'] = [
            'legend'      => [
                'title'  => $this->displayName.' '.$this->l('Advanced'),
                'image'  => $this->_path.'logo.gif',
                'width'  => 20,
                'height' => 20,
            ],
            'description' => $this->l('In this section you can modify certain aspects of the way this plugin sends products, searches, category view etc... to Matomo'),
            'input'       => [
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Use HTTPS'),
                    'name'   => static::CRHTTPS,
                    'hint'   => $this->l('ONLY enable this feature if Matomo installation is accessible via https'),
                    'desc'   => $this->l('Use Hypertext Transfer Protocol Secure (HTTPS) in all requests from code to Matomo.').
                        '<br>'.$this->l('This only affects how requests are sent from proxy script to piwik.').
                        '<br>'.$this->l('Your visitors will still use the protocol they visit your shop with.'),
                    'values' => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'html',
                    'name' => $this->display(__FILE__, 'views/templates/admin/desc_product_variants.tpl'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Product id V1'),
                    'name'     => static::PRODID_V1,
                    'desc'     => $this->l('This template is used in case ALL three values are available ("Product ID", "Product Attribute ID" and "Product Reference")'),
                    'required' => false,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Product id V2'),
                    'name'     => static::PRODID_V2,
                    'desc'     => $this->l('This template is used in case only "Product ID" and "Product Reference" are available'),
                    'required' => false,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Product id V3'),
                    'name'     => static::PRODID_V3,
                    'desc'     => $this->l('This template is used in case only "Product ID" and "Product Attribute ID" are available'),
                    'required' => false,
                ],
                [
                    'type' => 'html',
                    'name' => $this->display(__FILE__, 'views/templates/admin/desc_cookies.tpl'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Matomo Session Cookie timeout'),
                    'name'     => static::SESSION_TIMEOUT,
                    'required' => false,
                    'hint'     => $this->l('this value must be set in minutes'),
                    'desc'     => $this->l('Matomo Session Cookie timeout, the default is 30 minutes'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Matomo Visitor Cookie timeout'),
                    'name'     => static::COOKIE_TIMEOUT,
                    'required' => false,
                    'hint'     => $this->l('this value must be set in minutes'),
                    'desc'     => $this->l('Matomo Visitor Cookie timeout, the default is 13 months (569777 minutes)'),
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Matomo Referral Cookie timeout'),
                    'name'     => static::RCOOKIE_TIMEOUT,
                    'required' => false,
                    'hint'     => $this->l('this value must be set in minutes'),
                    'desc'     => $this->l('Matomo Referral Cookie timeout, the default is 6 months (262974 minutes)'),
                ],
                [
                    'type' => 'html',
                    'name' => $this->display(__FILE__, 'views/templates/admin/desc_basic_auth.tpl'),
                ],
                [
                    'type'         => 'text',
                    'label'        => $this->l('HTTP Basic Authentication Username'),
                    'name'         => static::PAUTHUSR,
                    'required'     => false,
                    'autocomplete' => false,
                    'desc'         => $this->l('this field along with password can be used if Matomo installation is protected by HTTP Basic Authorization'),
                ],
                [
                    'type'         => 'password',
                    'label'        => $this->l('HTTP Basic Authentication Password'),
                    'name'         => static::PAUTHPWD,
                    'required'     => false,
                    'autocomplete' => false,
                    'desc'         => $this->l('this field along with username can be used if Matomo installation is protected by HTTP Basic Authorization'),
                    'class'        => 'fixed-width-xxl',
                ],
            ],
            'buttons' => [
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-save',
                    'js'    => 'return submitPiwikSiteAPIUpdate(event, false)',
                ],
            ]
        ];

        if ($this->matomoSite !== false) {
            $tmp = PKHelper::getMyPiwikSites(true);
            foreach (PKHelper::$errors as $error) {
                $this->context->controller->errors[] = $error;
            }
            PKHelper::$error = '';
            PKHelper::$errors = [];
            $pksite_default = ['value' => 0, 'label' => $this->l('Choose Matomo site')];
            $pksites = [];
            foreach ($tmp as $pksid) {
                $pksites[] = [
                    'pkid' => $pksid['idsite'],
                    'name' => "{$pksid['name']} #{$pksid['idsite']}",
                ];
            }
            unset($tmp, $pksid);

            $pktimezone_default = ['value' => 0, 'label' => $this->l('Choose Timezone')];
            $pktimezones = [];
            $tmp = PKHelper::getTimezonesList();
            foreach (PKHelper::$errors as $error) {
                $this->context->controller->errors[] = $error;
            }
            PKHelper::$error = '';
            PKHelper::$errors = [];
            foreach ($tmp as $key => $pktz) {
                if (!isset($pktimezones[$key])) {
                    $pktimezones[$key] = [
                        'name'  => $this->l($key),
                        'query' => [],
                    ];
                }
                foreach ($pktz as $pktzK => $pktzV) {
                    $pktimezones[$key]['query'][] = [
                        'tzId'   => $pktzK,
                        'tzName' => $pktzV,
                    ];
                }
            }
            unset($tmp, $pktz, $pktzV, $pktzK);
            $fieldsForm[2]['form'] = [
                'legend' => [
                    'title'  => $this->displayName.' '.$this->l('Advanced').' - '.$this->l('Edit Matomo site'),
                    'image'  => $this->_path.'logo.gif',
                    'width'  => 20,
                    'height' => 20,
                ],
                'input'  => [
                    [
                        'type'     => 'select',
                        'label'    => $this->l('Matomo Site'),
                        'name'     => 'SPKSID',
                        'desc'     => sprintf($this->l('Based on your settings in Matomo your default site is %s'), $this->matomoSite[0]['idsite']),
                        'options'  => [
                            'default' => $pksite_default,
                            'query'   => $pksites,
                            'id'      => 'pkid',
                            'name'    => 'name',
                        ],
                        'onchange' => 'return ChangePKSiteEdit(this.value)',
                    ],
                    [
                        'type' => 'html',
                        'name' => $this->display(__FILE__, 'views/templates/admin/desc_embed.tpl'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Matomo Site Name'),
                        'name'  => 'PKAdminSiteName',
                        'desc'  => $this->l('Name of this site in Matomo'),
                    ],
                    //                    array(
                    //                        'type' => 'text',
                    //                        'label' => $this->l('Site urls'),
                    //                        'name' => 'PKAdminSiteUrls',
                    //                    ),
                    [
                        'type'    => 'switch',
                        'is_bool' => true,
                        'label'   => $this->l('Ecommerce'),
                        'name'    => 'PKAdminEcommerce',
                        'desc'    => $this->l('Is this site an ecommerce site?'),
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'is_bool' => true,
                        'label'   => $this->l('Site Search'),
                        'name'    => 'PKAdminSiteSearch',
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type'  => 'tags',
                        'label' => $this->l('Search Keyword Parameters'),
                        'name'  => 'PKAdminSearchKeywordParameters',
                    ],
                    [
                        'type'  => 'tags',
                        'label' => $this->l('Search Category Parameters'),
                        'name'  => 'PKAdminSearchCategoryParameters',
                    ],
                    [
                        'type'  => 'tags',
                        'label' => $this->l('Excluded ip addresses'),
                        'name'  => 'PKAdminExcludedIps',
                        'desc'  => $this->l('IP addresses excluded from tracking'),
                    ],
                    [
                        'type'  => 'tags',
                        'label' => $this->l('Excluded Query Parameters'),
                        'name'  => 'PKAdminExcludedQueryParameters',
                        'desc'  => $this->l('Please read:').' <a href="https://matomo.org/faq/how-to/faq_81/" target="_blank">https://matomo.org/faq/how-to/faq_81/</a>',
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Timezone'),
                        'name'    => 'PKAdminTimezone',
                        'desc'    => sprintf($this->l('Based on your settings in Matomo your default timezone is %s'), $this->matomoSite[0]['timezone']),
                        'options' => [
                            'default'     => $pktimezone_default,
                            'optiongroup' => [
                                'label' => 'name',
                                'query' => $pktimezones,
                            ],
                            'options'     => [
                                'id'    => 'tzId',
                                'name'  => 'tzName',
                                'query' => 'query',
                            ],
                        ],
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Currency'),
                        'name'    => 'PKAdminCurrency',
                        'desc'    => sprintf($this->l('Based on your settings in Matomo your default currency is %s'), $this->matomoSite[0]['currency']),
                        'options' => [
                            'default' => ['value' => 0, 'label' => $this->l('Choose currency')],
                            'query'   => array_map(function ($currency) {
                                return [
                                    'iso_code' => $currency['iso_code'],
                                    'name'     => "{$currency['name']} ({$currency['iso_code']})",
                                ];
                            }, Currency::getCurrencies()),
                            'id'      => 'iso_code',
                            'name'    => 'name',
                        ],
                    ],
                    //                    array(
                    //                        'type' => 'text',
                    //                        'label' => $this->l('Website group'),
                    //                        'name' => 'PKAdminGroup',
                    //                        'desc' => sprintf('Requires plugin "WebsiteGroups" before it can be used from within Piwik'),
                    //                    ),
                    //                    array(
                    //                        'type' => 'text',
                    //                        'label' => $this->l('Website start date'),
                    //                        'name' => 'PKAdminStartDate',
                    //                    ),
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Excluded User Agents'),
                        'name'  => 'PKAdminExcludedUserAgents',
                        'rows'  => 10,
                        'cols'  => 50,
                        'desc'  => $this->l('Please read:').' <a href="http://matomo.org/faq/how-to/faq_17483/">http://matomo.org/faq/how-to/faq_17483/</a>',
                    ],
                    [
                        'type'    => 'switch',
                        'is_bool' => true,
                        'label'   => $this->l('Keep URL Fragments'),
                        'name'    => 'PKAdminKeepURLFragments',
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                ],
                'buttons' => [
                    'submit' => [
                        'title' => $this->l('Save'),
                        'id'    => 'submitUpdatePiwikAdmSite',
                        'name'  => 'submitUpdatePiwikAdmSite',
                        'class' => 'btn btn-default pull-right',
                        'icon'  => 'process-icon-save',
                        'js'    => 'return submitPiwikSiteAPIUpdate(event, true)',
                    ],
                ],
            ];
        }
        $helper->fields_value = $this->getFormFields();
        $this->context->smarty->assign([
            'psversion'        => _PS_VERSION_,
            'tbversion'        => _TB_VERSION_,
            /* piwik_site_lookup */
            'psl_CPREFIX'      => 'PIWIK',
            'psl_currentIndex' => $helper->currentIndex,
            'psl_token'        => $helper->token,
            /* piwik_site_manager */
            'psm_currentIndex' => $helper->currentIndex,
            'psm_token'        => $helper->token,
        ]);

        return $helper->generateForm($fieldsForm)
            .$this->display(__FILE__, 'views/templates/admin/piwik_site_manager.tpl')
            .$this->display(__FILE__, 'views/templates/admin/piwik_site_lookup.tpl');
    }

    /**
     * @return void
     */
    protected function __pkapicall()
    {
        $apiMethod = Tools::getValue('pkapicall');
        if (method_exists('PKHelper', $apiMethod) && isset(PKHelper::$acp[$apiMethod])) {
            $required = PKHelper::$acp[$apiMethod]['required'];
            // $optional = PKHelper::$acp[$apiMethod]['optional'];
            $order = PKHelper::$acp[$apiMethod]['order'];
            foreach ($required as $requiredOption) {
                if (!Tools::getIsset($requiredOption)) {
                    PKHelper::debugLogger("__pkapicall():\n\t- Required parameter \"".$requiredOption.'" is missing');
                    die(json_encode([
                        'error' => true,
                        'message' => sprintf($this->l('Required parameter "%s" is missing'), $requiredOption),
                    ]));
                }
            }
            foreach ($order as & $value) {
                if (Tools::getIsset($value)) {
                    $value = Tools::getValue($value);
                } else {
                    $value = null;
                }
            }

            if (Tools::getIsset('httpUser')) {
                PKHelper::$httpAuthUsername = Tools::getValue('httpUser');
            }
            if (Tools::getIsset('httpPasswd')) {
                PKHelper::$httpAuthPassword = Tools::getValue('httpPasswd');
            }
            if (Tools::getIsset('piwikhost')) {
                PKHelper::$piwikHost = Tools::getValue('piwikhost');
            }

            PKHelper::debugLogger("__pkapicall():\n\t- Call PKHelper::".$apiMethod);
            $result = call_user_func_array(['PKHelper', $apiMethod], $order);
            if ($result === false) {
                $lastError = '';
                if (!empty(PKHelper::$errors)) {
                    $lastError = "\n".PKHelper::$error;
                }
                die(json_encode([
                    'error' => true,
                    'message' => sprintf($this->l('Unknown error occurred%s'), $lastError),
                ]));
            } else {
                PKHelper::debugLogger("__pkapicall():\n\t- All good");
                if (is_array($result) && isset($result[0])) {
                    $message = $result;
                } else {
                    if (is_object($result)) {
                        $message = $result;
                    } else {
                        $message = (is_string($result) && !is_bool($result) ? $result : (is_array($result) ? implode(', ', $result) : true));
                    }
                }

                if (is_bool($message)) {
                    die(json_encode(['error' => false, 'message' => $this->l('Successfully Updated')]));
                } else {
                    die(json_encode(['error' => false, 'message' => $message]));
                }
            }
        } else {
            die(json_encode(['error' => true, 'message' => sprintf($this->l('Method "%s" dos not exists in class PKHelper'), $apiMethod)]));
        }
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    protected function processFormsUpdate()
    {
        if (Tools::isSubmit('submitUpdate'.$this->name)) {
            if (Tools::getIsset(static::HOST)) {
                $tmp = Tools::getValue(static::HOST, '');
                if (!empty($tmp) && (filter_var($tmp, FILTER_VALIDATE_URL) || filter_var('http://'.$tmp, FILTER_VALIDATE_URL))) {
                    $tmp = str_replace(['http://', 'https://', '//'], '', $tmp);
                    if (substr($tmp, -1) != "/") {
                        $tmp .= "/";
                    }
                    Configuration::updateValue(static::HOST, $tmp);
                } else {
                    $this->context->controller->errors[] = $this->l('Matomo host cannot be empty');
                }
            }
            if (Tools::getIsset(static::SITEID)) {
                $tmp = (int) Tools::getValue(static::SITEID, 0);
                Configuration::updateValue(static::SITEID, $tmp);
                if ($tmp <= 0) {
                    $this->context->controller->errors[] = $this->l('Matomo site id is lower or equal to "0"');
                }
            }
            if (Tools::getIsset(static::TOKEN_AUTH)) {
                $tmp = Tools::getValue(static::TOKEN_AUTH, '');
                Configuration::updateValue(static::TOKEN_AUTH, $tmp);
                if (empty($tmp)) {
                    $this->context->controller->errors[] = $this->l('Matomo auth token is empty');
                }
            }
            /* setReferralCookieTimeout */
            if (Tools::getIsset(static::RCOOKIE_TIMEOUT)) {
                // the default is 6 months
                $tmp = (int) Tools::getValue(static::RCOOKIE_TIMEOUT, static::PK_RC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(static::RCOOKIE_TIMEOUT, $tmp);
            }
            /* setVisitorCookieTimeout */
            if (Tools::getIsset(static::COOKIE_TIMEOUT)) {
                // the default is 13 months
                $tmp = (int) Tools::getValue(static::COOKIE_TIMEOUT, static::PK_VC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(static::COOKIE_TIMEOUT, $tmp);
            }
            /* setSessionCookieTimeout */
            if (Tools::getIsset(static::SESSION_TIMEOUT)) {
                // the default is 30 minutes
                $tmp = (int) Tools::getValue(static::SESSION_TIMEOUT, static::PK_SC_TIMEOUT);
                $tmp = (int) ($tmp * 60); //* convert to seconds
                Configuration::updateValue(static::SESSION_TIMEOUT, $tmp);
            }
            if (Tools::getIsset(static::USE_PROXY)) {
                Configuration::updateValue(static::USE_PROXY, Tools::getValue(static::USE_PROXY));
            }
            if (Tools::getIsset(static::EXHTML)) {
                Configuration::updateValue(static::EXHTML, Tools::getValue(static::EXHTML), true);
            }
            if (Tools::getIsset(static::COOKIE_DOMAIN)) {
                Configuration::updateValue(static::COOKIE_DOMAIN, Tools::getValue(static::COOKIE_DOMAIN));
            }
            if (Tools::getIsset(static::SET_DOMAINS)) {
                Configuration::updateValue(static::SET_DOMAINS, Tools::getValue(static::SET_DOMAINS));
            }
            if (Tools::getIsset(static::DNT)) {
                Configuration::updateValue(static::DNT, Tools::getValue(static::DNT, 0));
            }
            if (Tools::getIsset(static::PROXY_SCRIPT)) {
                Configuration::updateValue(static::PROXY_SCRIPT, str_replace(["http://", "https://", '//'], '', Tools::getValue(static::PROXY_SCRIPT)));
            }
            if (Tools::getIsset(static::CRHTTPS)) {
                Configuration::updateValue(static::CRHTTPS, Tools::getValue(static::CRHTTPS, 0));
            }
            if (Tools::getIsset(static::PRODID_V1)) {
                Configuration::updateValue(static::PRODID_V1, Tools::getValue(static::PRODID_V1, '{ID}-{ATTRID}#{REFERENCE}'));
            }
            if (Tools::getIsset(static::PRODID_V2)) {
                Configuration::updateValue(static::PRODID_V2, Tools::getValue(static::PRODID_V2, '{ID}#{REFERENCE}'));
            }
            if (Tools::getIsset(static::PRODID_V3)) {
                Configuration::updateValue(static::PRODID_V3, Tools::getValue(static::PRODID_V3, '{ID}#{ATTRID}'));
            }
            if (Tools::getIsset(static::DEFAULT_CURRENCY)) {
                Configuration::updateValue(static::DEFAULT_CURRENCY, Tools::getValue(static::DEFAULT_CURRENCY, 'EUR'));
            }

            if (Tools::getIsset(static::USRNAME)) {
                Configuration::updateValue(static::USRNAME, Tools::getValue(static::USRNAME, ''));
            }
            if (Tools::getIsset(static::USRPASSWD) && Tools::getValue(static::USRPASSWD, '') != '') {
                Configuration::updateValue(static::USRPASSWD, Tools::getValue(static::USRPASSWD, Configuration::get(static::USRPASSWD)));
            }

            if (Tools::getIsset(static::PAUTHUSR)) {
                Configuration::updateValue(static::PAUTHUSR, Tools::getValue(static::PAUTHUSR, ''));
            }
            if (Tools::getIsset(static::PAUTHPWD) && Tools::getValue(static::PAUTHPWD, '') != '') {
                Configuration::updateValue(static::PAUTHPWD, Tools::getValue(static::PAUTHPWD, Configuration::get(static::PAUTHPWD)));
            }

            if (Tools::getIsset(static::DREPDATE)) {
                Configuration::updateValue(static::DREPDATE, Tools::getValue(static::DREPDATE, 'day|tody'));
            }

            if (empty($this->context->controller->errors)) {
                $this->context->controller->confirmations[] = $this->l('Configuration Updated');
            }
        }
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     */
    protected function getFormFields()
    {
        $PIWIK_PRODID_V1 = Configuration::get(static::PRODID_V1);
        $PIWIK_PRODID_V2 = Configuration::get(static::PRODID_V2);
        $PIWIK_PRODID_V3 = Configuration::get(static::PRODID_V3);
        $PIWIK_PROXY_SCRIPT = Configuration::get(static::PROXY_SCRIPT);
        $PIWIK_RCOOKIE_TIMEOUT = (int) Configuration::get(static::RCOOKIE_TIMEOUT);
        $PIWIK_COOKIE_TIMEOUT = (int) Configuration::get(static::COOKIE_TIMEOUT);
        $PIWIK_SESSION_TIMEOUT = (int) Configuration::get(static::SESSION_TIMEOUT);

        return [
            static::HOST                      => Configuration::get(static::HOST),
            static::SITEID                    => Configuration::get(static::SITEID),
            static::TOKEN_AUTH                => Configuration::get(static::TOKEN_AUTH),
            static::SESSION_TIMEOUT           => ($PIWIK_SESSION_TIMEOUT != 0 ? (int) ($PIWIK_SESSION_TIMEOUT / 60) : (int) (static::PK_SC_TIMEOUT)),
            static::COOKIE_TIMEOUT            => ($PIWIK_COOKIE_TIMEOUT != 0 ? (int) ($PIWIK_COOKIE_TIMEOUT / 60) : (int) (static::PK_VC_TIMEOUT)),
            static::RCOOKIE_TIMEOUT           => ($PIWIK_RCOOKIE_TIMEOUT != 0 ? (int) ($PIWIK_RCOOKIE_TIMEOUT / 60) : (int) (static::PK_RC_TIMEOUT)),
            static::USE_PROXY                 => Configuration::get(static::USE_PROXY),
            static::EXHTML                    => Configuration::get(static::EXHTML),
            static::CRHTTPS                   => Configuration::get(static::CRHTTPS),
            static::DEFAULT_CURRENCY          => Configuration::get(static::DEFAULT_CURRENCY),
            static::PRODID_V1                 => (!empty($PIWIK_PRODID_V1) ? $PIWIK_PRODID_V1 : '{ID}-{ATTRID}#{REFERENCE}'),
            static::PRODID_V2                 => (!empty($PIWIK_PRODID_V2) ? $PIWIK_PRODID_V2 : '{ID}#{REFERENCE}'),
            static::PRODID_V3                 => (!empty($PIWIK_PRODID_V3) ? $PIWIK_PRODID_V3 : '{ID}-{ATTRID}'),
            static::COOKIE_DOMAIN             => Configuration::get(static::COOKIE_DOMAIN),
            static::SET_DOMAINS               => Configuration::get(static::SET_DOMAINS),
            static::DNT                       => Configuration::get(static::DNT),
            static::PROXY_SCRIPT              => empty($PIWIK_PROXY_SCRIPT) ? str_replace(['http://', 'https://'], '', $this->context->link->getModuleLink($this->name, 'piwik', [], true)) : $PIWIK_PROXY_SCRIPT,
            static::USRNAME                   => Configuration::get(static::USRNAME),
            static::USRPASSWD                 => Configuration::get(static::USRPASSWD),
            static::PAUTHUSR                  => Configuration::get(static::PAUTHUSR),
            static::PAUTHPWD                  => Configuration::get(static::PAUTHPWD),
            static::DREPDATE                  => Configuration::get(static::DREPDATE),
            /* stuff thats isset by ajax calls to Piwik API ---(here to avoid not isset warnings..!)--- */
            'PKAdminSiteName'                 => ($this->matomoSite !== false ? $this->matomoSite[0]['name'] : ''),
            'PKAdminEcommerce'                => ($this->matomoSite !== false ? $this->matomoSite[0]['ecommerce'] : ''),
            'PKAdminSiteSearch'               => ($this->matomoSite !== false ? $this->matomoSite[0]['sitesearch'] : ''),
            'PKAdminSearchKeywordParameters'  => ($this->matomoSite !== false ? $this->matomoSite[0]['sitesearch_keyword_parameters'] : ''),
            'PKAdminSearchCategoryParameters' => ($this->matomoSite !== false ? $this->matomoSite[0]['sitesearch_category_parameters'] : ''),
            'SPKSID'                          => ($this->matomoSite !== false ? $this->matomoSite[0]['idsite'] : Configuration::get(static::SITEID)),
            'PKAdminExcludedIps'              => ($this->matomoSite !== false ? $this->matomoSite[0]['excluded_ips'] : ''),
            'PKAdminExcludedQueryParameters'  => ($this->matomoSite !== false ? $this->matomoSite[0]['excluded_parameters'] : ''),
            'PKAdminTimezone'                 => ($this->matomoSite !== false ? $this->matomoSite[0]['timezone'] : ''),
            'PKAdminCurrency'                 => ($this->matomoSite !== false ? $this->matomoSite[0]['currency'] : ''),
            'PKAdminGroup'                    => ($this->matomoSite !== false ? $this->matomoSite[0]['group'] : ''),
            'PKAdminStartDate'                => '',
            'PKAdminSiteUrls'                 => '',
            'PKAdminExcludedUserAgents'       => ($this->matomoSite !== false ? $this->matomoSite[0]['excluded_user_agents'] : ''),
            'PKAdminKeepURLFragments'         => ($this->matomoSite !== false ? $this->matomoSite[0]['keep_url_fragment'] : 0),
            'PKAdminSiteType'                 => ($this->matomoSite !== false ? $this->matomoSite[0]['type'] : 'website'),
        ];
    }

    /**
     * hook into maintenance page.
     *
     * @param array $params empty array
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 0.8
     */
    public function hookdisplayMaintenance($params)
    {
        return $this->hookFooter($params);
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookFooter()
    {
        if ((int) Configuration::get(static::SITEID) <= 0) {
            return '';
        }

        if (static::$isOrder) {
            return '';
        }

        $this->setDefaultConfig();
        $this->context->smarty->assign(static::ORDER, false);

        /* cart tracking */
        if (!$this->context->cookie->PIWIKTrackCartFooter) {
            $this->context->cookie->PIWIKTrackCartFooter = time();
        }
        if (strtotime($this->context->cart->date_upd) >= $this->context->cookie->PIWIKTrackCartFooter) {
            $this->context->cookie->PIWIKTrackCartFooter = strtotime($this->context->cart->date_upd) + 2;
            $smarty_ad = [];

            $Currency = new Currency($this->context->cart->id_currency);
            foreach ($this->context->cart->getProducts() as $key => $value) {
                if (!isset($value['id_product']) || !isset($value['name']) || !isset($value['total_wt']) || !isset($value['quantity'])) {
                    continue;
                }
                $smarty_ad[] = [
                    'SKU'      => $this->parseProductSku($value['id_product'], (isset($value['id_product_attribute']) && $value['id_product_attribute'] > 0 ? $value['id_product_attribute'] : false), (isset($value['reference']) ? $value['reference'] : false)),
                    'NAME'     => $value['name'].(isset($value['attributes']) ? ' ('.$value['attributes'].')' : ''),
                    'CATEGORY' => $this->getCategoryNamesByProduct($value['id_product'], false),
                    'PRICE'    => $this->currencyConvertion(
                        [
                            'price'           => $value['total_wt'],
                            'conversion_rate' => $Currency->conversion_rate,
                        ]
                    ),
                    'QUANTITY' => $value['quantity'],
                ];
            }
            if (count($smarty_ad) > 0) {
                $this->context->smarty->assign(static::CART, true);
                $this->context->smarty->assign(static::CART_PRODUCTS, $smarty_ad);
                $this->context->smarty->assign(static::CART_TOTAL, $this->currencyConvertion(
                    [
                        'price'           => $this->context->cart->getOrderTotal(),
                        'conversion_rate' => $Currency->conversion_rate,
                    ]
                ));
            } else {
                $this->context->smarty->assign(static::CART, false);
            }
            unset($smarty_ad);
        } else {
            $this->context->smarty->assign(static::CART, false);
        }

        $is404 = false;
        if (!empty($this->context->controller->errors)) {
            foreach ($this->context->controller->errors as $key => $value) {
                if ($value == Tools::displayError('Product not found')) {
                    $is404 = true;
                }
                if ($value == Tools::displayError('This product is no longer available.')) {
                    $is404 = true;
                }
            }
        }
        if (
            (strtolower(get_class($this->context->controller)) == 'pagenotfoundcontroller') ||
            (isset($this->context->controller->php_self) && ($this->context->controller->php_self == '404')) ||
            (isset($this->context->controller->page_name) && (strtolower($this->context->controller->page_name) == 'pagenotfound'))
        ) {
            $is404 = true;
        }

        $this->context->smarty->assign(['PK404' => $is404]);

        $this->prepareHookFooter();

        return $this->display(__FILE__, 'views/templates/hook/jstracking.tpl');
    }

    /**
     * @throws PrestaShopException
     */
    protected function setDefaultConfig()
    {
        $this->context->smarty->assign(static::USE_PROXY, (bool) Configuration::get(static::USE_PROXY));

        //* using proxy script?
        if ((bool) Configuration::get(static::USE_PROXY)) {
            $this->context->smarty->assign(static::HOST, Configuration::get(static::PROXY_SCRIPT));
        } else {
            $this->context->smarty->assign(static::HOST, Configuration::get(static::HOST));
        }

        $this->context->smarty->assign(static::SITEID, Configuration::get(static::SITEID));

        $pkvct = (int) Configuration::get(static::COOKIE_TIMEOUT); /* no iset if the same as default */
        if ($pkvct != 0 && $pkvct !== false && ($pkvct != (int) (static::PK_VC_TIMEOUT * 60))) {
            $this->context->smarty->assign(static::COOKIE_TIMEOUT, $pkvct);
        }
        unset($pkvct);

        $pkrct = (int) Configuration::get(static::RCOOKIE_TIMEOUT); /* no iset if the same as default */
        if ($pkrct != 0 && $pkrct !== false && ($pkrct != (int) (static::PK_RC_TIMEOUT * 60))) {
            $this->context->smarty->assign(static::RCOOKIE_TIMEOUT, $pkrct);
        }
        unset($pkrct);

        $pksct = (int) Configuration::get(static::SESSION_TIMEOUT); /* no iset if the same as default */
        if ($pksct != 0 && $pksct !== false && ($pksct != (int) (static::PK_SC_TIMEOUT * 60))) {
            $this->context->smarty->assign(static::SESSION_TIMEOUT, $pksct);
        }
        unset($pksct);

        $this->context->smarty->assign(static::EXHTML, Configuration::get(static::EXHTML));

        $PIWIK_COOKIE_DOMAIN = Configuration::get(static::COOKIE_DOMAIN);
        $this->context->smarty->assign(static::COOKIE_DOMAIN, (empty($PIWIK_COOKIE_DOMAIN) ? false : $PIWIK_COOKIE_DOMAIN));

        $PIWIK_SET_DOMAINS = Configuration::get(static::SET_DOMAINS);
        if (!empty($PIWIK_SET_DOMAINS)) {
            $sdArr = explode(' ', Configuration::get(static::SET_DOMAINS));
            if (count($sdArr) > 1) {
                $PIWIK_SET_DOMAINS = "['".trim(implode("','", $sdArr), ",'")."']";
            } else {
                $PIWIK_SET_DOMAINS = "'{$sdArr[0]}'";
            }
            $this->context->smarty->assign(static::SET_DOMAINS, (!empty($PIWIK_SET_DOMAINS) ? $PIWIK_SET_DOMAINS : false));
            unset($sdArr);
        } else {
            $this->context->smarty->assign(static::SET_DOMAINS, false);
        }
        unset($PIWIK_SET_DOMAINS);

        if ((bool) Configuration::get(static::DNT)) {
            $this->context->smarty->assign(static::DNT, "_paq.push([\"setDoNotTrack\", true]);");
        }

        if ($this->context->customer->isLogged()) {
            $this->context->smarty->assign(static::UUID, $this->context->customer->email);
        }
    }

    /**
     * @param      $id
     * @param bool $attrid
     * @param bool $ref
     *
     * @return mixed
     * @throws PrestaShopException
     */
    protected function parseProductSku($id, $attrid = false, $ref = false)
    {
        if (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== false) && (!empty($ref) && !is_null($ref) && $ref !== false)) {
            $PIWIK_PRODID_V1 = Configuration::get(static::PRODID_V1);

            return str_replace(['{ID}', '{ATTRID}', '{REFERENCE}'], [$id, $attrid, $ref], $PIWIK_PRODID_V1);
        } elseif (Validate::isInt($id) && (!empty($ref) && !is_null($ref) && $ref !== false)) {
            $PIWIK_PRODID_V2 = Configuration::get(static::PRODID_V2);

            return str_replace(['{ID}', '{REFERENCE}'], [$id, $ref], $PIWIK_PRODID_V2);
        } elseif (Validate::isInt($id) && (!empty($attrid) && !is_null($attrid) && $attrid !== false)) {
            $PIWIK_PRODID_V3 = Configuration::get(static::PRODID_V3);

            return str_replace(['{ID}', '{ATTRID}'], [$id, $attrid], $PIWIK_PRODID_V3);
        } else {
            return $id;
        }
    }

    /**
     * convert into default currency used in Matomo
     *
     * @param array $params
     *
     * @return float
     * @since 0.4
     * @throws PrestaShopException
     */
    protected function currencyConvertion($params)
    {
        $pkc = Configuration::get("PIWIK_DEFAULT_CURRENCY");
        if (empty($pkc)) {
            return (float) $params['price'];
        }
        if ($params['conversion_rate'] === false || $params['conversion_rate'] == 0.00 || $params['conversion_rate'] == 1.00) {
            //* shop default
            return Tools::convertPrice((float) $params['price'], Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        } else {
            $_shop_price = (float) ((float) $params['price'] / (float) $params['conversion_rate']);

            return Tools::convertPrice($_shop_price, Currency::getCurrencyInstance((int) (Currency::getIdByIsoCode($pkc))));
        }
    }

    /**
     * add Prestashop !LATEST! specific settings
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 0.4
     */
    protected function prepareHookFooter()
    {
        /* product tracking */
        $controller = $this->context->controller;
        if ($controller instanceof ProductController) {
            $products = [['product' => $controller->getProduct(), 'categorys' => null]];
            if (isset($products) && isset($products[0]['product'])) {
                $smarty_ad = [];
                foreach ($products as $product) {
                    if (!Validate::isLoadedObject($product['product'])) {
                        continue;
                    }
                    if ($product['categorys'] == null) {
                        $product['categorys'] = $this->getCategoryNamesByProduct($product['product']->id, false);
                    }
                    $smarty_ad[] = [
                        /* (required) SKU: Product unique identifier */
                        'SKU'      => $this->parseProductSku($product['product']->id, false, (isset($product['product']->reference) ? $product['product']->reference : false)),
                        /* (optional) Product name */
                        'NAME'     => $product['product']->name,
                        /* (optional) Product category, or array of up to 5 categories */
                        'CATEGORY' => $product['categorys'], //$category->name,
                        /* (optional) Product Price as displayed on the page */
                        'PRICE'    => $this->currencyConvertion(
                            [
                                'price'           => Product::getPriceStatic($product['product']->id, true, false),
                                'conversion_rate' => $this->context->currency->conversion_rate,
                            ]
                        ),
                    ];
                }
                $this->context->smarty->assign([static::PRODUCTS => $smarty_ad]);
                unset($smarty_ad);
            }
        }

        /* category tracking */
        if ($controller instanceof CategoryController) {
            $category = $controller->getCategory();
            if (Validate::isLoadedObject($category)) {
                $this->context->smarty->assign([
                    static::category => ['NAME' => $category->name],
                ]);
            }
        }
    }

    /**
     * PIWIK don't track links on the same site eg.
     * if product is view in an iframe so we add this and makes sure that it is content only view
     *
     * @param mixed $param
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookdisplayRightColumnProduct($param)
    {
        if ((int) Configuration::get(static::SITEID) <= 0) {
            return '';
        }
        if ((int) Tools::getValue('content_only') > 0 && get_class($this->context->controller) == 'ProductController') { // we also do this in the tpl file.!
            return $this->hookFooter($param);
        }

        return '';
    }

    /**
     * only checks that the module is registered in hook "footer",
     * this why we only appent javescript to the end of the page!
     *
     * @throws PrestaShopException
     */
    public function hookHeader()
    {
        if (!$this->isRegisteredInHook('footer')) {
            $this->registerHook('footer');
        }
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookOrderConfirmation($params)
    {
        if ((int) Configuration::get(static::SITEID) <= 0) {
            return '';
        }

        $order = $params['objOrder'];
        if (Validate::isLoadedObject($order)) {

            $this->setDefaultConfig();

            $this->context->smarty->assign(static::ORDER, true);
            $this->context->smarty->assign(static::CART, false);


            $smarty_ad = [];
            foreach ($params['objOrder']->getProductsDetail() as $value) {
                $smarty_ad[] = [
                    'SKU'      => $this->parseProductSku($value['product_id'], (isset($value['product_attribute_id']) ? $value['product_attribute_id'] : false), (isset($value['product_reference']) ? $value['product_reference'] : false)),
                    'NAME'     => $value['product_name'],
                    'CATEGORY' => $this->getCategoryNamesByProduct($value['product_id'], false),
                    'PRICE'    => $this->currencyConvertion(
                        [
                            'price'           => (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : (isset($value['total_price_tax_incl']) ? floatval($value['total_price_tax_incl']) : 0.00)),
                            'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                        ]
                    ),
                    'QUANTITY' => $value['product_quantity'],
                ];
            }
            $this->context->smarty->assign(static::ORDER_PRODUCTS, $smarty_ad);
            if (isset($params['objOrder']->total_paid_tax_incl) && isset($params['objOrder']->total_paid_tax_excl)) {
                $tax = $params['objOrder']->total_paid_tax_incl - $params['objOrder']->total_paid_tax_excl;
            } else {
                if (isset($params['objOrder']->total_products_wt) && isset($params['objOrder']->total_products)) {
                    $tax = $params['objOrder']->total_products_wt - $params['objOrder']->total_products;
                } else {
                    $tax = 0.00;
                }
            }
            $ORDER_DETAILS = [
                'order_id'        => $params['objOrder']->id,
                'order_total'     => $this->currencyConvertion(
                    [
                        'price'           => floatval(isset($params['objOrder']->total_paid_tax_incl) ? $params['objOrder']->total_paid_tax_incl : (isset($params['objOrder']->total_paid) ? $params['objOrder']->total_paid : 0.00)),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ]
                ),
                'order_sub_total' => $this->currencyConvertion(
                    [
                        'price'           => floatval($params['objOrder']->total_products_wt),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ]
                ),
                'order_tax'       => $this->currencyConvertion(
                    [
                        'price'           => floatval($tax),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ]
                ),
                'order_shipping'  => $this->currencyConvertion(
                    [
                        'price'           => floatval((isset($params['objOrder']->total_shipping_tax_incl) ? $params['objOrder']->total_shipping_tax_incl : (isset($params['objOrder']->total_shipping) ? $params['objOrder']->total_shipping : 0.00))),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ]
                ),
                'order_discount'  => $this->currencyConvertion(
                    [
                        'price'           => (isset($params['objOrder']->total_discounts_tax_incl) ?
                            ($params['objOrder']->total_discounts_tax_incl > 0 ?
                                floatval($params['objOrder']->total_discounts_tax_incl) : false) : (isset($params['objOrder']->total_discounts) ?
                                ($params['objOrder']->total_discounts > 0 ?
                                    floatval($params['objOrder']->total_discounts) : false) : 0.00)),
                        'conversion_rate' => (isset($params['objOrder']->conversion_rate) ? $params['objOrder']->conversion_rate : 0.00),
                    ]
                ),
            ];
            $this->context->smarty->assign(static::ORDER_DETAILS, $ORDER_DETAILS);

            // avoid double tracking on complete order.
            static::$isOrder = true;

            return $this->display(__FILE__, 'views/templates/hook/jstracking.tpl');
        }
    }

    /**
     * search action
     *
     * @param array $params
     *
     * @since 0.4
     * @return string
     * @throws PrestaShopException
     */
    public function hookSearch($params)
    {
        if ((int) Configuration::get(static::SITEID) <= 0) {
            return '';
        }
        $this->hookactionSearch($params);
    }

    /**
     * Search action
     *
     * @param array $param
     *
     * @return string
     * @throws PrestaShopException
     */
    public function hookactionSearch($param)
    {
        if ((int) Configuration::get(static::SITEID) <= 0) {
            return '';
        }
        $param['total'] = intval($param['total']);
        /* if multi pages in search add page number of current if set! */
        $page = '';
        if (Tools::getIsset('p')) {
            $page = " (".Tools::getValue('p').")";
        }
        // $param['expr'] is not the searched word if lets say search is Snitmøntre then the $param['expr'] will be Snitmontre
        $expr = Tools::getIsset('search_query') ? htmlentities(Tools::getValue('search_query')) : $param['expr'];
        $this->context->smarty->assign([
            static::SITE_SEARCH => "_paq.push(['trackSiteSearch',\"{$expr}{$page}\",false,{$param['total']}]);",
        ]);

        return '';
    }

    /**
     * Install the module
     *
     * @return boolean false on install error
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        /* create complete new page tab */
        $tab = new Tab();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'Piwik Analytics';
        }
        $tab->module = 'piwikanalyticsjs';
        $tab->active = true;

        if (method_exists('Tab', 'getInstanceFromClassName')) {
            $tab->class_name = 'PiwikAnalytics';
            $AdminParentStats = TabCore::getInstanceFromClassName('AdminStats');
            if ($AdminParentStats == null || !($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) || $AdminParentStats->id == 0) {
                $AdminParentStats = TabCore::getInstanceFromClassName('AdminParentStats');
            }
        } else {
            if (method_exists('Tab', 'getIdFromClassName')) {
                $tab->class_name = 'PiwikAnalytics';
                $tmpId = TabCore::getIdFromClassName('AdminStats');
                if ($tmpId != null && $tmpId > 0) {
                    $AdminParentStats = new Tab($tmpId);
                } else {
                    $tmpId = TabCore::getIdFromClassName('AdminParentStats');
                    if ($tmpId != null && $tmpId > 0) {
                        $AdminParentStats = new Tab($tmpId);
                    }
                }
            }
        }

        $tab->id_parent = (isset($AdminParentStats) && ($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? $AdminParentStats->id : -1);
        if ($tab->add()) {
            Configuration::updateValue(static::TAPID, (int) $tab->id);
        } else {
            $this->_errors[] = sprintf($this->l('Unable to create new tab "Piwik Analytics", Please forward tthe following info to the developer %s'), "<br/>"
                .(isset($AdminParentStats) ? "isset(\$AdminParentStats): True" : "isset(\$AdminParentStats): False")
                ."<br/>"
                ."Type of \$AdminParentStats: ".gettype($AdminParentStats)
                ."<br/>"
                ."Class name of \$AdminParentStats: ".get_class($AdminParentStats)
                ."<br/>"
                .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats instanceof Tab: True" : "\$AdminParentStats instanceof Tab: False")
                ."<br/>"
                .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->id: ".$AdminParentStats->id : "\$AdminParentStats->id: ?0?")
                ."<br/>"
                .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->name: ".$AdminParentStats->name : "\$AdminParentStats->name: ?0?")
                ."<br/>"
                .(($AdminParentStats instanceof Tab || $AdminParentStats instanceof TabCore) ? "\$AdminParentStats->class_name: ".$AdminParentStats->class_name : "\$AdminParentStats->class_name: ?0?")
                ."<br/>"
                ."thirty bees version: "._TB_VERSION_
                ."<br/>"
                ."PHP version: ".PHP_VERSION
            );
        }

        /* default values */
        foreach ($this->getConfigFields(false) as $key => $value) {
            Configuration::updateValue($key, $value);
        }

        return (parent::install() && $this->registerHook('header') && $this->registerHook('footer') && $this->registerHook('displayBackOfficeHeader') && $this->registerHook('actionSearch') && $this->registerHook('displayRightColumnProduct') && $this->registerHook('orderConfirmation') && $this->registerHook('displayMaintenance'));
    }

    /**
     * @param bool $form
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    protected function getConfigFields($form = false)
    {
        $fields = [
            static::USE_PROXY        => 0,
            static::HOST             => '',
            static::SITEID           => 0,
            static::TOKEN_AUTH       => '',
            static::COOKIE_TIMEOUT   => static::PK_VC_TIMEOUT,
            static::SESSION_TIMEOUT  => static::PK_SC_TIMEOUT,
            static::DEFAULT_CURRENCY => 'EUR',
            static::CRHTTPS          => 0,
            static::PRODID_V1        => '{ID}-{ATTRID}#{REFERENCE}',
            static::PRODID_V2        => '{ID}#{REFERENCE}',
            static::PRODID_V3        => '{ID}#{ATTRID}',
            static::COOKIE_DOMAIN    => Tools::getShopDomain(),
            static::SET_DOMAINS      => '',
            static::DNT              => 0,
            static::EXHTML           => '',
            static::RCOOKIE_TIMEOUT  => static::PK_RC_TIMEOUT,
            static::USRNAME          => '',
            static::USRPASSWD        => '',
            static::PAUTHUSR         => '',
            static::PAUTHPWD         => '',
            static::DREPDATE         => 'day|today',
        ];
        $ret = [];
        if ($form) {
            foreach ($fields as $key => $value) {
                $ret[$key] = Configuration::get($key);
            }
        } else {
            foreach ($fields as $key => $value) {
                $ret[$key] = $value;
            }
        }

        return $ret;
    }

    /**
     * Uninstall the module
     *
     * @return boolean false on uninstall error
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach ($this->getConfigFields(false) as $key => $value) {
                Configuration::deleteByName($key);
            }
            $adminParentStats = Tab::getInstanceFromClassName('PiwikAnalytics');
            if (Validate::isLoadedObject($adminParentStats)) {
                $adminParentStats->delete();
            }
            Configuration::deleteByName(static::TAPID);

            return true;
        }

        return false;
    }

    /**
     * get category names by product id
     *
     * @param int  $id    product id
     * @param bool $array get categories as PHP array (TRUE), or javacript (FAlSE)
     *
     * @return string|array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getCategoryNamesByProduct($id, $array = true)
    {
        $_categories = Product::getProductCategoriesFull($id, $this->context->cookie->id_lang);
        if (!is_array($_categories)) {
            return $array ? [] : '[]';
        }
        if ($array) {
            $categories = [];
            foreach ($_categories as $category) {
                $categories[] = $category['name'];
                if (count($categories) == 5) {
                    break;
                }
            }
        } else {
            $categories = '[';
            $c = 0;
            foreach ($_categories as $category) {
                $c++;
                $categories .= '"'.$category['name'].'",';
                if ($c == 5) {
                    break;
                }
            }
            $categories = rtrim($categories, ',');
            $categories .= ']';
        }

        return $categories;
    }
}
