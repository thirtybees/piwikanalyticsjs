{*
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2024 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark & property of PrestaShop SA
*}
{l s='in the next few inputs you can set how the product id is passed on to Matomo' mod='piwikanalyticsjs'}
<br>
{l s='there are three variables you can use:' mod='piwikanalyticsjs'}
<br/>
<kbd>{ldelim}ID{rdelim}</kbd>: {l s='this variable is replaced with id the product has in thirty bees' mod='piwikanalyticsjs'}
<br/>
<kbd>{ldelim}REFERENCE{rdelim}</kbd>: {l s='this variable is replaced with the unique reference you when adding adding/updating a product' mod='piwikanalyticsjs'}
<br/>
<kbd>{ldelim}ATTRID{rdelim}</kbd>: {l s='this variable is replaced with id the product attribute' mod='piwikanalyticsjs'}
<br/>
{l s='in cases where only the product id is available it be parsed as ID and nothing else' mod='piwikanalyticsjs'}
