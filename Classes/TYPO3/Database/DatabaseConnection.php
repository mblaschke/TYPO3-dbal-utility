<?php
namespace Lightwerk\DbalUtility\TYPO3\Database;

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
 * Database Connection
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class DatabaseConnection extends \TYPO3\CMS\Core\Database\DatabaseConnection {

    /**
     * Query count
     *
     * @var int
     */
    public $queryCount = 0;

    /**
     * Flag if query log is enabled
     *
     * @var bool
     */
    public $isQueryLogEnabled = FALSE;

    /**
     * Flag if query log timing is enabled
     *
     * @var bool
     */
    public $isQueryLogTimingEnabled = FALSE;

    /**
     * Flag if query strict mode is enabled
     *
     * @var bool
     */
    public $isQueryStrictMode = FALSE;

    /**
     * Ignore next query by query log
     *
     * @var bool
     */
    public $queryLogIgnoreNextQuery = FALSE;

    /**
     * Flag if query cost fetching is enabled
     *
     * @var bool
     */
    public $isQueryCostFetch = FALSE;

    /**
     * Initialize the database connection
     *
     * @return void
     */
    public function initialize() {
        parent::initialize();

        $this->isQueryLogEnabled       = \Lightwerk\DbalUtility\Service\EnvironmentService::isQueryLogEnabled();
        $this->isQueryLogTimingEnabled = \Lightwerk\DbalUtility\Service\EnvironmentService::isQueryLogTimingEnabled();
        $this->isQueryStrictMode       = \Lightwerk\DbalUtility\Service\EnvironmentService::isQueryStrictModeEnabled();
        $this->isQueryCostFetch        = \Lightwerk\DbalUtility\Service\EnvironmentService::isQueryCostFetchEnabled();
    }

    /**
     * @inherit
     */
    protected function query($query) {
        // Check if ignore-mode is set
        if ($this->queryLogIgnoreNextQuery) {
            $this->queryLogIgnoreNextQuery = FALSE;
            return parent::query($query);
        }

        #####################
        # Query stats
        #####################

        // Inc query counter
        $this->queryCount++;

        // Start query timer (if enabled)
        $queryStartTime = null;
        if ($this->isQueryLogEnabled && $this->isQueryLogTimingEnabled) {
            $queryStartTime = microtime(true);
        }

        #####################
        # Run query
        #####################

        // Exec query
        $ret = parent::query($query);

        // Strict mode
        if ($this->isQueryStrictMode && (!$ret || $this->sql_errno())) {
            // SQL statement failed
            $errorMsg = 'SQL Error: ' . $this->sql_error() . ' [errno: ' . $this->sql_errno() . ']';

            $e = new \Lightwerk\DbalUtility\Database\DatabaseException($errorMsg, 1403340242);
            $e->setSqlError($this->sql_error());
            $e->setSqlErrorNumber($this->sql_errno());
            $e->setSqlQuery($query);
            throw $e;
        }

        #####################
        # Query log
        #####################

        if ($this->isQueryLogEnabled) {
            $queryStatus = 0;
            if (!$ret || $this->sql_errno()) {
                $queryStatus = $this->sql_errno();
            }

            // Call query time
            $queryTime = NULL;
            if ($queryStartTime) {
                $queryTime = microtime(true) - $queryStartTime;
            }

            // Fetch query cost
            $queryCost = NULL;
            if ($this->isQueryCostFetch) {
                $costQuery = 'SHOW STATUS LIKE \'Last_query_cost\'';
                $costRes = parent::query($costQuery);
                if ($costRes && $costRow = $this->sql_fetch_assoc($costRes)) {
                    $queryCost = end($costRow);
                }
            }

            // Log query
            \Lightwerk\DbalUtility\Service\RequestLogService::appendToLogfile(
                \Lightwerk\DbalUtility\Service\RequestLogService::QUERY_TYPE_QUERY,
                $query,
                $queryStatus,
                $queryTime,
                $queryCost
            );
        }


        return $ret;
    }

}