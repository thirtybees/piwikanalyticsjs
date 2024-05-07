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
 * @return boolean
 *
 * @throws PrestaShopException
 */
function upgrade_module_0_6_6() {
    Configuration::updateValue('PIWIK_CRHTTPS', 0);
    Configuration::updateValue('PIWIK_PRODID_V1', '{ID}-{ATTRID}#{REFERENCE}');
    Configuration::updateValue('PIWIK_PRODID_V2', '{ID}#{REFERENCE}');
    Configuration::updateValue('PIWIK_PRODID_V3', '{ID}#{ATTRID}');

    return true;
}
