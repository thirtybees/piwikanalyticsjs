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
 * @author thirty bees <contact@thirtybees.com>
 * @author Christian M. Jensen
 * @deprecated http://cmjnisse.github.io/piwikanalyticsjs-prestashop
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 *
 * @param PiwikAnalyticsJs $module
 *
 * @return boolean
 * @throws PrestaShopException
 */
function upgrade_module_0_6_5($module) {
    Configuration::updateValue('PIWIK_COOKIE_DOMAIN', '*.' . str_replace('www.', '', Tools::getShopDomain()));
    Configuration::updateValue('PIWIK_SET_DOMAINS', Tools::getShopDomain());
    Configuration::updateValue('PIWIK_DNT', 1);
    Configuration::updateValue('PIWIK_PROXY_SCRIPT', str_replace("http://", '', Context::getContext()->link->getModuleLink($module->name, 'piwik')));

    return true;
}
