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
<a href="#" class="btn btn-default" onclick='return PiwikLookup();' title='{l s='Click here to open the Matomo site lookup wizard' mod='piwikanalyticsjs'}'>{l s='Configuration Wizard' mod='piwikanalyticsjs'} <i class="icon icon-magic"></i></a>
<br>
<br>
{if !empty($matomoSiteName)}
  {l s='Based on the settings you provided this is the info I get from Matomo!' mod='piwikanalyticsjs'}
  <br>
  <strong>{l s='Name' mod='piwikanalyticsjs'}</strong>: <i>{$matomoSiteName|escape:'htmlall'}</i>
  <br>
  <strong>{l s='Main Url' mod='piwikanalyticsjs'}</strong>: <i>{$matomoSiteUrl|escape:'htmlall'}</i>
{/if}
