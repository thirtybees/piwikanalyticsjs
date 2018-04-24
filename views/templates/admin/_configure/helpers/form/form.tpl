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
{extends file="helpers/form/form.tpl"}

{block name="input"}
  {if $input.type == 'html'}
    {if isset($input.html_content)}
      {$input.html_content}
    {else}
      {$input.name}
    {/if}
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

{block name="legend"}
  <div class="panel-heading">
    {if isset($field.image) && isset($field.title)}<img src="{$field.image|escape:'html'}"
                                                        alt="{$field.title|escape:'html'}"
                                                        {if isset($field.width)}width="{$field.width|intval}"{/if}
                                                        {if isset($field.height)}height="{$field.height|intval}"{/if}
      />{/if}
    {if isset($field.icon)}<i class="{$field.icon|escape:'html'}"></i>{/if}
    {$field.title|escape:'html'}
  </div>
{/block}
