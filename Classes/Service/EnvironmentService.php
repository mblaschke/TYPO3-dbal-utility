<?php
namespace Lightwerk\DbalUtility\Service;

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
 * Environment service
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class EnvironmentService {

    /**
     * Check if debug mode is enabled
     * (cached)
     *
     * @return bool
     */
    public static function isDebugMode() {
        static $ret = NULL;

        if ($ret === NULL) {
            // Check if current context is development mode
            $ret = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isDevelopment();
        }

        return $ret;
    }

    /**
     * Check if query log is enabled in current mode
     * (Cached)
     *
     * @return bool
     */
    public static function isQueryLogEnabled() {
        static $ret = NULL;

        if($ret === NULL && self::isDebugMode()) {
            $ret = FALSE;

            if (TYPO3_MODE == 'FE' && self::getExtensionConfiguration('queryLogFE', 0)) {
                // FE query log enabled
                $ret = TRUE;
            } elseif (TYPO3_MODE == 'BE' && self::getExtensionConfiguration('queryLogBE', 0)) {
                // BE query log enabled
                $ret = TRUE;
            } elseif (defined('TYPO3_cliMode') && self::getExtensionConfiguration('queryLogCLI', 0)) {
                // CLI query log enabled
                $ret = TRUE;
            }
        }

        return $ret;
    }


    /**
     * Check if query log timing is enabled in current mode
     *
     * @return bool
     */
    public static function isQueryLogTimingEnabled() {
        static $ret = NULL;

        if($ret === NULL && self::isDebugMode()) {
            $ret = (bool)self::getExtensionConfiguration('queryLogTimings', 1);
        }

        return $ret;
    }

    /**
     * Check if query strict mode (throw exception on error) is enabled
     *
     * @return bool
     */
    public static function isSqlExceptionsEnabled() {
        static $ret = NULL;

        if($ret === NULL && self::isDebugMode()) {
            $ret = (bool)self::getExtensionConfiguration('sqlExceptions', 0);
        }

        return $ret;
    }

    /**
     * Check if cache queries should be logged
     * (Cached)
     *
     * @return bool
     */
    public static function isCacheQueriesAreIgnored() {
        static $ret = NULL;

        if($ret === NULL) {
            $ret = (bool)self::getExtensionConfiguration('queryLogIgnoreCacheQueries', 1);
        }

        return $ret;
    }

    /**
     * Check if fetching of query cost is enabeld
     * (cached)
     *
     * @return bool
     */
    public static function isQueryCostFetchEnabled() {
        static $ret = NULL;

        if($ret === NULL) {
            $ret = (bool)self::getExtensionConfiguration('queryLogCost', 0);
        }

        return $ret;
    }

    /**
     * Get extension configuration
     *
     * @param  string $key     Configuration key
     * @param  mixed  $default Default value
     * @return mixed
     */
    public static function getExtensionConfiguration($key, $default) {
        static $extConf = NULL;

        if($extConf === NULL) {

            // Extract ext configuration (if available)
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dbal_utility'])) {
                $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dbal_utility']);
            }

            if (!is_array($extConf)) {
                $extConf = array();
            }
        }

        $ret = $default;
        if (array_key_exists($key, $extConf)) {
            $ret = $extConf[$key];
        }

        return $ret;
    }


}