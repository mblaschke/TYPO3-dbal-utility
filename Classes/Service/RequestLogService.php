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
 * Request Log Service
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class RequestLogService {

    const QUERY_TYPE_QUERY              = 'query';
    const QUERY_TYPE_PREPARED_STATEMENT = 'prepared statement';

    /**
     * Debug log id
     *
     * @var string
     */
    protected static $debugId;

    /**
     * Init and maintain log directory
     */
    public static function initLogDirectory() {
        static $alreadyRun = FALSE;

        // only run once
        if (!$alreadyRun) {
            // Check if log di
            if (!is_dir(self::logDirectory())) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::mkdir(self::logDirectory());
            }

            self::expireLogFiles();

            $alreadyRun = TRUE;
        }
    }

    /**
     * Expire log files
     */
    public static function expireLogFiles() {
        // Clear old log files in directory
        $currTime = time();
        foreach (new \DirectoryIterator(self::logDirectory()) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($currTime - $fileInfo->getCTime() >= 10 * 60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }


    /**
     * Get current debug log id
     *
     * @return string
     */
    public static function currentDebugId() {
        if (empty(self::$debugId)) {
            self::$debugId  = sha1(uniqid('', true));
        }

        return self::$debugId;
    }

    /**
     * Get absolute path to log directory
     *
     * @return string
     */
    public static function logDirectory() {
        return PATH_site . '/typo3temp/DbDebug';
    }

    /**
     * Get list of current log files, sorted by modification date
     *
     * @return array
     */
    public static function getLogFileList() {
        $ret = array();

        foreach (new \DirectoryIterator(self::logDirectory()) as $fileInfo) {
            // Ignore dot files
            if ($fileInfo->isDot()) {
                continue;
            }

            $ret[$fileInfo->getMTime()] = new \Lightwerk\DbalUtility\Log\LogFile($fileInfo->getBasename());
        }

        // sort, newest files on top
        krsort($ret);

        return $ret;
    }

    /**
     * Append query to current log file
     *
     * @param string    $queryType      SQL query type
     * @param string    $queryStatement SQL query
     * @param mixed     $queryStatus    SQL query statys
     * @param float|int $queryTime      SQL query run time
     */
    public static function appendToLogfile($queryType, $queryStatement, $queryStatus, $queryTime) {
        static $fp = NULL;

        if ($fp === NULL) {
            // Init log directory, make sure everything is ok
            self::initLogDirectory();

            // Generate log file name (sha1 hashed filename)
            $logFile = self::logDirectory() . '/' . self::currentDebugId();

            // Open file, trunkate if needed and write header (on first line)
            $fp = fopen($logFile, 'w+');
            fwrite($fp, self::generateLogHeader());
        }

        // Filter query
        self::filterQuery($queryStatement);

        // Write query line (second and other lines)
        fwrite($fp,  "\n". self::buildQueryLine($queryType, $queryStatement, $queryStatus, $queryTime));
    }

    /**
     * Filter query
     *
     * @param string $queryStatement SQL Query
     * @return string
     */
    protected static function filterQuery(&$queryStatement) {
        // Trim query, we do not need whitespaces at begin and end
        $queryStatement = trim($queryStatement);

        // TYPO3 caching queries
        if (EnvironmentService::isCacheQueriesAreIgnored()) {
            if( preg_match('/(SELECT[\s]+.*[\s]+FROM|INSERT[\s]+INTO|DELETE[\s]+FROM)[\s]+(cache|cf)_[a-z]+/im', $queryStatement)) {
                $queryStatement = '-- hidden TYPO3 caching query --';
            }
        }
    }

    /**
     * Generate log header
     *
     * @return string
     */
    protected function generateLogHeader() {
        // Set basic mode
        $mode = TYPO3_MODE;

        // Generate url
        $url = null;
        if (!defined('TYPO3_cliMode')) {
            if (!empty($_SERVER['HTTPS']) || $_SERVER['SERVER_PORT'] == 443) {
                $url = 'https://';
            } else {
                $url = 'http://';
            }

            $url .= $_SERVER['REMOTE_ADDR'];
            $url .= $_SERVER['REQUEST_URI'];
        }

        // Build
        $cmd = null;
        if (defined('TYPO3_cliMode')) {
            $mode = 'CLI';

            $cmd = implode(' ', $_SERVER['argv']);
        }

        // Build log header informations
        $ret = array(
            'mode'   => $mode,
            'uri'    => $_SERVER['REQUEST_URI'],
            'url'    => $url,
            'cmd'    => $cmd,
            'host'   => $_SERVER['SERVER_NAME'],
            'ip'     => $_SERVER['REMOTE_ADDR'],
            'time'   => $_SERVER['REQUEST_TIME'],
            'method' => $_SERVER['REQUEST_METHOD'],
        );

        return self::encodeLogLine($ret);
    }

    /**
     * Build query log line
     *
     * @param  string    $queryType      SQL query type
     * @param  string    $queryStatement SQL query
     * @param  mixed     $queryStatus    SQL query statys
     * @param  float|int $queryTime      SQL query run time
     * @return string
     */
    protected function buildQueryLine($queryType, $queryStatement, $queryStatus, $queryTime) {
        // We don't want to have the full SQL line
        $maxLength = 2000;
        if (strlen($queryStatement) > $maxLength) {
            $queryStatement = substr($queryStatement, 0, $maxLength);
        }

        $ret = array(
            'type'   => $queryType,
            'query'  => $queryStatement,
            'status' => $queryStatus,
            'time'   => $queryTime,
        );

        return self::encodeLogLine($ret);
    }

    /**
     * Encode log line
     *
     * @param  string $line Log line
     * @return string
     */
    protected function encodeLogLine($line) {
        return base64_encode(json_encode($line));
    }

}