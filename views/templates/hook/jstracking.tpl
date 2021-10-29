{*
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
 * @link http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*}
<script type="text/javascript" data-cookieconsent="statistics">
    var u = '{Tools::getShopProtocol()|escape:'javascript'}{$PIWIK_HOST|escape:'javascript'}';
    var _paq = _paq || [];
    {if isset($PIWIK_DNT)}{$PIWIK_DNT}{/if}
      _paq.push(['setSiteId', '{$PIWIK_SITEID|escape:'javascript'}']);
    {if $PIWIK_USE_PROXY}
      _paq.push(['setTrackerUrl', u]);
    {else}
      _paq.push(['setTrackerUrl', u + 'piwik.php']);
    {/if}
    {if isset($PIWIK_COOKIE_DOMAIN) && $PIWIK_COOKIE_DOMAIN}
      _paq.push(['setCookieDomain', '{$PIWIK_COOKIE_DOMAIN|escape:'javascript'}']);
    {/if}
    {if isset($PIWIK_SET_DOMAINS) && $PIWIK_SET_DOMAINS}
      _paq.push(['setDomains', {$PIWIK_SET_DOMAINS}]);
    {/if}
    {if isset($PIWIK_COOKIE_TIMEOUT)}
      _paq.push(['setVisitorCookieTimeout', '{$PIWIK_COOKIE_TIMEOUT|escape:'javascript'}']);
    {/if}
    {if isset($PIWIK_SESSION_TIMEOUT)}
      _paq.push(['setSessionCookieTimeout', '{$PIWIK_SESSION_TIMEOUT|escape:'javascript'}']);
    {/if}
    {if isset($PIWIK_RCOOKIE_TIMEOUT)}
      _paq.push(['setReferralCookieTimeout', '{$PIWIK_RCOOKIE_TIMEOUT|escape:'javascript'}']);
    {/if}
      _paq.push(['enableLinkTracking']);
    {if isset($PIWIK_UUID)}
      _paq.push(['setUserId', '{$PIWIK_UUID|escape:'javascript'}']);
      _paq.push(['setCustomVariable', 1, 'First Name', '{$cookie->customer_firstname|escape:'javascript'}', 'visit']);
      _paq.push(['setCustomVariable', 2, 'Last Name', '{$cookie->customer_lastname|escape:'javascript'}', 'visit']);
      {if isset($PIWIK_GROUP_NAME)}	
		_paq.push(['setCustomVariable', 3, 'Group Name', '{$PIWIK_GROUP_NAME|escape:'javascript'}', 'visit']);
	  {/if} 
  	  {if isset($PIWIK_GROUP_ID)}	 
	    _paq.push(['setCustomVariable', 4, 'Group Id', '{$PIWIK_GROUP_ID|escape:'javascript'}', 'visit']);
	  {/if} 
    {/if}
    {if isset($smarty.request.email)}
      _paq.push(['setUserId', '{$smarty.request.email|escape:'javascript'}']);
    {/if}
    {if isset($PIWIK_PRODUCTS) && is_array($PIWIK_PRODUCTS)}
    {foreach from=$PIWIK_PRODUCTS item=piwikproduct}
      _paq.push(['setEcommerceView', '{$piwikproduct.SKU|escape:'javascript'}', '{$piwikproduct.NAME|escape:'javascript'}', {json_encode($piwikproduct.CATEGORY, $smarty.const.JSON_UNESCAPED_UNICODE)}, '{$piwikproduct.PRICE|floatval}']);
    {/foreach}
    {/if}
    {if isset($piwik_category) && is_array($piwik_category)}
      _paq.push(['setEcommerceView', false, false, '{$piwik_category.NAME|escape:'javascript'}']);
    {/if}
    {if $PIWIK_CART}
    {if is_array($PIWIK_CART_PRODUCTS)}
    {foreach from=$PIWIK_CART_PRODUCTS item=_product}
      _paq.push(['addEcommerceItem', '{$_product.SKU|escape:'javascript'}', '{$_product.NAME|escape:'javascript'}', {json_encode($_product.CATEGORY, $smarty.const.JSON_UNESCAPED_UNICODE)}, '{$_product.PRICE|escape:'javascript'}', '{$_product.QUANTITY|escape:'javascript'}']);
    {/foreach}
    {/if}
    {if isset($PIWIK_CART_TOTAL)}
      _paq.push(['trackEcommerceCartUpdate', {$PIWIK_CART_TOTAL|floatval}]);
    {/if}
    {/if}
    {if $PIWIK_ORDER}
    {if is_array($PIWIK_ORDER_PRODUCTS)}
    {foreach from=$PIWIK_ORDER_PRODUCTS item=_product}
      _paq.push(['addEcommerceItem', '{$_product.SKU|escape:'javascript'}', '{$_product.NAME|escape:'javascript'}', {json_encode($_product.CATEGORY, $smarty.const.JSON_UNESCAPED_UNICODE)}, '{$_product.PRICE|escape:'javascript'}', '{$_product.QUANTITY|escape:'javascript'}']);
    {/foreach}
    {/if}
      _paq.push(['trackEcommerceOrder', '{$PIWIK_ORDER_DETAILS.order_id|escape:'javascript'}', '{$PIWIK_ORDER_DETAILS.order_total|escape:'javascript'}', '{$PIWIK_ORDER_DETAILS.order_sub_total|escape:'javascript'}', '{$PIWIK_ORDER_DETAILS.order_tax|escape:'javascript'}', '{$PIWIK_ORDER_DETAILS.order_shipping|escape:'javascript'}', '{$PIWIK_ORDER_DETAILS.order_discount|escape:'javascript'}']);
    {/if}
    {if isset($PIWIK_SITE_SEARCH) && !isset($PIWIK_PRODUCTS)}
    {$PIWIK_SITE_SEARCH}
    {else}
    {if !empty($PK404)}
      _paq.push(['setDocumentTitle', '404/URL = ' + encodeURIComponent(document.location.pathname + document.location.search) + '/From = ' + encodeURIComponent(document.referrer)]);
    {/if}
      _paq.push(['trackPageView']);
    {/if}
    (function () {
      var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
      g.type = 'text/javascript';
      g.defer = true;
      g.async = true;
      g.src = {if $PIWIK_USE_PROXY}u{else}u + 'piwik.js'{/if};
      s.parentNode.insertBefore(g, s);
    }());
</script>
{if !empty($PIWIK_EXHTML)}
{$PIWIK_EXHTML}
{/if}
