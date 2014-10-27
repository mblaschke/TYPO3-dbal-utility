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
class PreparedStatement extends \TYPO3\CMS\Core\Database\PreparedStatement {

    /**
     * @inherit
     */
    public function execute(array $input_parameters = array()) {
        // INIT
        $queryStartTime = null;

        if ($GLOBALS['TYPO3_DB'] instanceof \Lightwerk\DbalUtility\TYPO3\Database\DatabaseConnection) {
            #####################
            # Query stats
            #####################

            // Inc query counter
            $GLOBALS['TYPO3_DB']->queryCount++;

            // Start query timer
            if ($GLOBALS['TYPO3_DB']->isQueryLogTimingEnabled) {
                $queryStartTime = microtime(true);
            }

            #####################
            # Run query
            #####################

            // Exec query
            $ret = parent::execute($input_parameters);

            // Strict mode
            if ($GLOBALS['TYPO3_DB']->isSqlExceptionsEnabled && (!$ret || $GLOBALS['TYPO3_DB']->sql_errno())) {
                // SQL statement failed
                $errorMsg = 'SQL Error: ' . $GLOBALS['TYPO3_DB']->sql_error() . ' [errno: ' . $GLOBALS['TYPO3_DB']->sql_errno() . ']';

                $e = new \Lightwerk\DbalUtility\Database\DatabaseException($errorMsg, 1403340242);
                $e->setSqlError($GLOBALS['TYPO3_DB']->sql_error());
                $e->setSqlErrorNumber($GLOBALS['TYPO3_DB']->sql_errno());
                $e->setSqlQuery($this->query);
                throw $e;
            }

            #####################
            # Query log
            #####################

            if ($GLOBALS['TYPO3_DB']->isQueryLogEnabled) {
                // Query status
                $queryStatus = 0;
                if (!$ret || $GLOBALS['TYPO3_DB']->sql_errno()) {
                    $queryStatus = $GLOBALS['TYPO3_DB']->sql_errno();
                }

                // Calc query time
                $queryTime = null;
                if ($queryStartTime) {
                    $queryTime = microtime(true) - $queryStartTime;
                }

                // Fetch query cost
                $queryCost = NULL;
                if ($GLOBALS['TYPO3_DB']->isQueryCostFetch) {
                    $GLOBALS['TYPO3_DB']->queryLogIgnoreNextQuery = TRUE;

                    $costQuery = 'SHOW STATUS LIKE \'Last_query_cost\'';
                    $costRes = $GLOBALS['TYPO3_DB']->sql_query($costQuery);
                    if ($costRes && $costRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($costRes)) {
                        $queryCost = end($costRow);
                    }
                }

                // Log query
                \Lightwerk\DbalUtility\Service\RequestLogService::appendToLogfile(
                    \Lightwerk\DbalUtility\Service\RequestLogService::QUERY_TYPE_PREPARED_STATEMENT,
                    $this->query,
                    $queryStatus,
                    $queryTime,
                    $queryCost
                );
            }

        } else {
            // Database connection is not ours, fallback to normal mode
            $ret = parent::execute($input_parameters);
        }

        return $ret;
    }

}