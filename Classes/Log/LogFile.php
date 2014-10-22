<?php
namespace Lightwerk\DbalUtility\Log;

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
 * Database Debug Log Service
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class LogFile {

    /**
     * Log file id
     *
     * @var string
     */
    protected $logId;

    /**
     * Path to log file
     *
     * @var string
     */
    protected $logFile;

    /**
     * Log file header
     *
     * @var null|mixed
     */
    protected $header = NULL;

    /**
     * Log file queries
     *
     * @var null|mixed
     */
    protected $queries = NULL;

    /**
     * Constructor
     *
     * @param string $logId Log id
     */
    public function __construct($logId) {
        $logDirectory = \Lightwerk\DbalUtility\Service\RequestLogService::logDirectory();

        $logId = preg_replace('[^a-zA-Z0-9]', '', $logId);

        $this->logId   = $logId;
        $this->logFile = $logDirectory . '/' . $logId;

        if (!is_file($this->logFile)) {
            throw new \RuntimeException('Log file doesn\'t exists', 1413912587);
        }
    }

    public function getLogId() {
        return $this->logId;
    }

    public function getHeader() {
        if (!$this->header) {
            $this->readHeaderOnly();
        }

        return $this->header;
    }

    public function getQueryList() {
        if (!$this->queries) {
            $this->readLog();
        }

        return $this->queries;
    }


    protected function readHeaderOnly() {
        if (is_file($this->logFile)) {
            $f = fopen($this->logFile, 'r');
            $header = fgets($f);
            fclose($f);

            $header = base64_decode($header);
            $header = json_decode($header);
            $this->header = $header;
        } else {
            throw new \RuntimeException('Logfile doesn\'t exists', 1413844942);
        }
    }

    protected function readLog() {
        if (is_file($this->logFile)) {
            $content = file_get_contents($this->logFile);
            $content = explode("\n", $content);

            $header = $content[0];
            unset($content[0]);

            $this->header = json_decode(base64_decode($header));

            $this->queries = array();
            foreach ($content as $line) {
                $this->queries[] = json_decode(base64_decode($line));
            }

        } else {
            throw new \RuntimeException('Logfile doesn\'t exists', 1413912819);
        }
    }


}