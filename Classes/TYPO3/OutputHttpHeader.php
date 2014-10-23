<?php
namespace Lightwerk\DbalUtility\TYPO3;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Markus Blaschke <typo3@markus-blaschke.de> (dbal_utility)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Output http header
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class OutputHttpHeader {

    /**
     * Add Debug HTTP Headers
     */
    public static function addHttpHeaderDebugId() {
        if (\Lightwerk\DbalUtility\Service\EnvironmentService::isDebugMode() && !headers_sent()) {
            header('X-TYPO3-QueryDebugId: ' . \Lightwerk\DbalUtility\Service\RequestLogService::currentDebugId());
        }
    }

    /**
     * Add Debug HTTP Headers
     */
    public static function addHttpHeaders() {
        if (\Lightwerk\DbalUtility\Service\EnvironmentService::isDebugMode() && !headers_sent()) {
            header('X-TYPO3-QueryCount:'    . (int)$GLOBALS['TYPO3_DB']->queryCount);
        }
    }

}