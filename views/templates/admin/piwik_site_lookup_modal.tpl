{*
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
*}
<div>
  <i class="icon icon-2x icon-times-circle pull-right PiwikLookupClose"
     onclick="PiwikLookup();"
     title="{l s='Close' mod='piwikanalyticsjs'}">
  </i>
  <h2>{l s='Matomo credentials' mod='piwikanalyticsjs'}</h2>
  <p>{l s='Enter the username and password of your Matomo login, we need this to get your API key (auth token)' mod='piwikanalyticsjs'}</p>
  <p>
    <label for="PiwikLookupLoginFormUsername">
      {l s='Username:' mod='piwikanalyticsjs'}
    </label>
    <input id="PiwikLookupLoginFormUsername" type="text" onkeyup="PiwikLookupLogin(event);"/>
  </p>
  <p>
    <label for="PiwikLookupLoginFormPassword">{l s='Password:' mod='piwikanalyticsjs'}</label>
    <input id="PiwikLookupLoginFormPassword" type="password" onkeyup="PiwikLookupLogin(event);"/>
  </p>
  <p>
    <label for="PiwikLookupLoginFormHttpAuthSettings" style="width: 100%; text-align: left;">
      <input id="PiwikLookupLoginFormHttpAuthSettings" type="CHECKBOX"
             onclick='if(this.checked) { $("#PiwikLookupLoginFormHttpAuthSettingsWrapper").show(); } else { $("#PiwikLookupLoginFormHttpAuthSettingsWrapper").hide(); }'/>
      {l s='HTTP Basic Authorization?' mod='piwikanalyticsjs'}
    </label>
    <br>
  </p>
  <div id="PiwikLookupLoginFormHttpAuthSettingsWrapper" style="display: none">
    <label for="PiwikLookupLoginFormHttpAuthUsername">{l s='HTTP Authorization Username:' mod='piwikanalyticsjs'}</label>
    <input id="PiwikLookupLoginFormHttpAuthUsername" type="text" onkeyup="PiwikLookupLogin(event);"/>
    <p>
      <label for="PiwikLookupLoginFormHttpAuthPassword">{l s='HTTP Authorization Password:' mod='piwikanalyticsjs'}</label>
      <input id="PiwikLookupLoginFormHttpAuthPassword" type="password" onkeyup="PiwikLookupLogin(event);"/>
    </p>
  </div>
  <p>
    <a href="#" class="btn btn-default" onclick="PiwikLookupLogin(event);">{l s='Login' mod='piwikanalyticsjs'} <i class="icon icon-chevron-right"></i></a>
  </p>
</div>
