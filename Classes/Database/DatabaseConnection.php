<?php
namespace Lightwerk\DbalUtility\Database;

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
class DatabaseConnection {

    /**
     * TYPO3 native database connection
     *
     * @var \TYPO3\CMS\Dbal\Database\DatabaseConnection
     */
    protected $connection;

    /**
     * Constructor
     */
    public function __construct() {
        if (empty($this->connection)) {
            $this->connection = $GLOBALS['TYPO3_DB'];
        }
    }

    ###########################################################################
    # Wrapper
    ###########################################################################

    /**
     * Get TYPO3 native database connection
     *
     * @return \TYPO3\CMS\Dbal\Database\DatabaseConnection
     */
    public function connection() {
        return $this->connection;
    }

    /**
     * Fetch assoc
     *
     * @param  mixed $result Query result
     * @return mixed
     */
    public function fetchAssoc($result) {
        return $this->connection->sql_fetch_assoc($result);
    }

    /**
     * Query Result seek
     *
     * @param  mixed $result   Query result
     * @param  integer  $position Position
     * @return mixed
     */
    public function queryResultSeek($result, $position) {
        return $this->connection->sql_data_seek($result, $position);
    }

    /**
     * Fetch result row count
     *
     * @param  resource $result Query result
     * @return integer
     */
    public function queryResultRowCount($result) {
        return $this->connection->sql_num_rows($result);
    }

    ###########################################################################
    # Query functions
    ###########################################################################

    /**
     * Fetch one
     *
     * @param  string $query SQL query
     * @return mixed
     */
    public function fetchOne($query) {
        $ret = NULL;

        $res = $this->_query($query);
        if ($res) {
            if ($row = $this->connection->sql_fetch_assoc($res)) {
                $ret = reset($row);
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch row
     *
     * @param   string $query SQL query
     * @return array
     */
    public function fetchRow($query) {
        $ret = NULL;

        $res = $this->_query($query);
        if ($res) {
            if ($row = $this->connection->sql_fetch_assoc($res)) {
                $ret = $row;
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch All
     *
     * @param  string  $query SQL query
     * @return array
     */
    public function fetchAll($query) {
        $ret = array();

        $res = $this->_query($query);
        if ($res) {
            while ($row = $this->connection->sql_fetch_assoc($res)) {
                $ret[] = $row;
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch All with index (first value)
     *
     * @param  string $query    SQL query
     * @param  string $indexCol Index column name
     * @return array
     */
    public function fetchAllWithIndex($query, $indexCol = NULL) {
        $ret = array();

        $res = $this->_query($query);
        if ($res) {
            while ($row = $this->connection->sql_fetch_assoc($res)) {
                if ($indexCol === NULL) {
                    // use first key as index
                    $index = reset($row);
                } else {
                    $index = $row[$indexCol];
                }

                $ret[$index] = $row;
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch List
     *
     * @param  string $query SQL query
     * @return array
     */
    public function fetchList($query) {
        $ret = array();

        $res = $this->_query($query);
        if ($res) {
            while ($row = $this->connection->sql_fetch_row($res)) {
                $ret[$row[0]] = $row[1];
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch column
     *
     * @param  string $query SQL query
     * @return array
     */
    public function fetchCol($query) {
        $ret = array();

        $res = $this->_query($query);
        if ($res) {
            while ($row = $this->connection->sql_fetch_row($res)) {
                $ret[] = $row[0];
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch column
     *
     * @param  string $query SQL query
     * @return array
     */
    public function fetchColWithIndex($query) {
        $ret = array();

        $res = $this->_query($query);
        if ($res) {
            while ($row = $this->connection->sql_fetch_row($res)) {
                $ret[ $row[0] ] = $row[0];
            }
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Fetch count (from query)
     *
     * @param  string $query SQL query
     * @return integer
     */
    public function fetchCount($query) {
        $query = 'SELECT COUNT(*) FROM (' . $query . ') tmp';
        return $this->fetchOne($query);
    }

    /**
     * Exec query (INSERT)
     *
     * @param  string  $query SQL query
     * @return integer        Last insert id
     */
    public function execInsert($query) {
        $ret = FALSE;

        $res = $this->_query($query);

        if ($res) {
            $ret = $this->connection->sql_insert_id();
            $this->free($res);
        }

        return $ret;
    }

    /**
     * Exec query (DELETE, UPDATE etc)
     *
     * @param  string  $query SQL query
     * @return integer        Affected rows
     */
    public function exec($query) {
        $ret = FALSE;

        $res = $this->_query($query);

        if ($res) {
            $ret = $this->connection->sql_affected_rows();
            $this->free($res);
        }

        return $ret;
    }

    ###########################################################################
    # Transaction functions
    ###########################################################################

    /**
     * Start transaction
     */
    public function startTransaction() {
        $this->exec('START TRANSACTION');
    }

    /**
     * Commit transaction
     */
    public function commit() {
        $this->exec('COMMIT');
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->exec('ROLLBACK');
    }

    ###########################################################################
    # Quote functions
    ###########################################################################


    /**
     * Quote value
     *
     * @param   string  $value  Value
     * @param   string  $table  Table
     * @return  string
     */
    public function quote($value, $table = NULL) {
        if ($value === NULL) {
            return 'NULL';
        }

        return $this->connection->fullQuoteStr($value, $table);
    }

    /**
     * Quote array with values
     *
     * @param   array  $valueList  Values
     * @param   string $table   Table
     * @return  array
     */
    public function quoteArray($valueList, $table = NULL) {
        $ret = array();
        foreach ($valueList as $k => $v) {
            $ret[$k] = $this->quote($v, $table);
        }

        return $ret;
    }

    /**
     * Sanitize column for sql usage
     *
     * @param   string  $field  SQL Field/Attribut
     * @return  string
     */
    public function sanitizeSqlColumn($field) {
        return preg_replace('/[^_a-zA-Z0-9\.]/', '', $field);
    }


    /**
     * Sanitize table for sql usage
     *
     * @param  string  $table  SQL Table
     * @return string
     */
    public function sanitizeSqlTable($table) {
        return preg_replace('/[^_a-zA-Z0-9]/', '', $table);
    }

    ###########################################################################
    # Helper functions
    ###########################################################################

    /**
     * Add condition to query
     *
     * @param  array|string $condition Condition
     * @return string
     */
    public function addCondition($condition) {
        $ret = ' ';

        if (!empty($condition)) {
            if (is_array($condition)) {
                $ret .= ' AND (( ' . implode(" )\nAND (", $condition) . ' ))';
            } else {
                $ret .= ' AND ( ' . $condition . ' )';
            }
        }

        return $ret;
    }

    /**
     * Create condition WHERE field IN (1,2,3,4)
     *
     * @param  string  $field    SQL field
     * @param  array   $values   Values
     * @param  boolean $required Required
     * @return string
     */
    public function conditionIn($field, $values, $required = TRUE) {
        if (!empty($values)) {
            $quotedValues = $this->quoteArray($values, 'pages');

            $ret = $this->sanitizeSqlColumn($field) . ' IN (' . implode(',', $quotedValues) . ')';
        } else {
            if ($required) {
                $ret = '1=0';
            } else {
                $ret = '1=1';
            }
        }

        return $ret;
    }

    /**
     * Create condition WHERE field NOT IN (1,2,3,4)
     *
     * @param  string  $field    SQL field
     * @param  array   $values   Values
     * @param  boolean $required Required
     * @return string
     */
    public function conditionNotIn($field, $values, $required = TRUE) {
        if (!empty($values)) {
            $quotedValues = $this->quoteArray($values, 'pages');

            $ret = $this->sanitizeSqlColumn($field) . ' NOT IN (' . implode(',', $quotedValues) . ')';
        } else {
            if ($required) {
                $ret = '1=0';
            } else {
                $ret = '1=1';
            }
        }

        return $ret;
    }

    ###########################################################################
    # SQL warpper functions
    ###########################################################################

    /**
     * Execute sql query
     *
     * @param   string $query SQL query
     * @return  resource
     * @throws  \Exception
     */
    public function query($query) {
        $res = $this->_query($query);

        if ($res) {
            $res = new \Lightwerk\DbalUtility\Database\DatabaseResult($this, $res);
        }

        return $res;
    }

    /**
     * Execute sql query
     *
     * @param   string $query SQL query
     * @return  mixed
     * @throws  DatabaseException
     */
    protected function _query($query) {
        $res = $this->connection->sql_query($query);

        if (!$res || $this->connection->sql_errno()) {
            // SQL statement failed
            $errorMsg = 'SQL Error: ' . $this->connection->sql_error() . ' [errno: ' . $this->connection->sql_errno() . ']';

            $e = new \Lightwerk\DbalUtility\Database\DatabaseException($errorMsg, 1403340242);
            $e->setSqlError($this->connection->sql_error());
            $e->setSqlErrorNumber($this->connection->sql_errno());
            $e->setSqlQuery($query);
            throw $e;
        }

        return $res;
    }

    /**
     * Free sql result
     *
     * @param resource $res SQL resource
     */
    public function free($res) {
        if ($res && $res !== TRUE) {
            $this->connection->sql_free_result($res);
        }
    }
}