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
 * Advanced Database Connection
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
     * Fetch count (from query, with subselect)
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


    ###########################################################################
    # Proxy methods
    ###########################################################################

    public function cacheFieldInfo()
    {
        return $this->connection->cacheFieldInfo();
    }

    public function INSERTquery($table, $fields_values, $no_quote_fields = FALSE)
    {
        return $this->connection->INSERTquery($table, $fields_values, $no_quote_fields);
    }

    public function INSERTmultipleRows($table, array $fields, array $rows, $no_quote_fields = FALSE)
    {
        return $this->connection->INSERTmultipleRows($table, $fields, $rows, $no_quote_fields);
    }

    public function DELETEquery($table, $where)
    {
        return $this->connection->DELETEquery($table, $where);
    }

    public function SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        return $this->connection->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
    }

    public function MetaType($type, $table, $maxLength = -1)
    {
        return $this->connection->MetaType($type, $table, $maxLength);
    }

    public function MySQLMetaType($t)
    {
        return $this->connection->MySQLMetaType($t);
    }

    public function MySQLActualType($meta)
    {
        return $this->connection->MySQLActualType($meta);
    }

    public function SELECTsubquery($select_fields, $from_table, $where_clause)
    {
        return $this->connection->SELECTsubquery($select_fields, $from_table, $where_clause);
    }

    public function UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE)
    {
        return $this->connection->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
    }

    public function TRUNCATEquery($table)
    {
        return $this->connection->TRUNCATEquery($table);
    }

    public function admin_get_dbs()
    {
        return $this->connection->admin_get_dbs();
    }

    public function admin_get_tables()
    {
        return $this->connection->admin_get_tables();
    }

    public function admin_get_fields($tableName)
    {
        return $this->connection->admin_get_fields($tableName);
    }

    public function admin_get_keys($tableName)
    {
        return $this->connection->admin_get_keys($tableName);
    }

    public function admin_get_charsets()
    {
        return $this->connection->admin_get_charsets();
    }

    public function admin_query($query)
    {
        return $this->connection->admin_query($query);
    }

    public function cleanIntArray($arr)
    {
        return $this->connection->cleanIntArray($arr);
    }

    public function cleanIntList($list)
    {
        return $this->connection->cleanIntList($list);
    }

    public function clearCachedFieldInfo()
    {
        return $this->connection->clearCachedFieldInfo();
    }

    public function connectDB($host = NULL, $username = NULL, $password = NULL, $db = NULL)
    {
        return $this->connection->connectDB($host, $username, $password, $db);
    }

    public function debug($func, $query = '')
    {
        return $this->connection->debug($func, $query);
    }

    public function debugHandler($function, $execTime, $inData)
    {
        return $this->connection->debugHandler($function, $execTime, $inData);
    }

    public function debug_WHERE($table, $where, $script = '')
    {
        return $this->connection->debug_WHERE($table, $where, $script);
    }

    public function debug_check_recordset($res)
    {
        return $this->connection->debug_check_recordset($res);
    }

    public function debug_log($query, $ms, $data, $join, $errorFlag, $script = '')
    {
        return $this->connection->debug_log($query, $ms, $data, $join, $errorFlag, $script);
    }

    public function debug_explain($query)
    {
        return $this->connection->debug_explain($query);
    }

    public function escapeStrForLike($str, $table)
    {
        return $this->connection->escapeStrForLike($str, $table);
    }

    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = FALSE)
    {
        return $this->connection->exec_INSERTquery($table, $fields_values, $no_quote_fields);
    }

    public function exec_INSERTmultipleRows($table, array $fields, array $rows, $no_quote_fields = FALSE)
    {
        return $this->connection->exec_INSERTmultipleRows($table, $fields, $rows, $no_quote_fields);
    }

    public function exec_DELETEquery($table, $where)
    {
        return $this->connection->exec_DELETEquery($table, $where);
    }

    public function exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '')
    {
        return $this->connection->exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $whereClause, $groupBy, $orderBy, $limit);
    }

    public function exec_SELECT_queryArray($queryParts)
    {
        return $this->connection->exec_SELECT_queryArray($queryParts);
    }

    public function exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', $uidIndexField = '')
    {
        return $this->connection->exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit, $uidIndexField);
    }

    public function exec_SELECTgetSingleRow($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $numIndex = FALSE)
    {
        return $this->connection->exec_SELECTgetSingleRow($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $numIndex);
    }

    public function exec_SELECTcountRows($field, $table, $where = '')
    {
        return $this->connection->exec_SELECTcountRows($field, $table, $where);
    }

    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = FALSE)
    {
        return $this->connection->exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields);
    }

    public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        return $this->connection->exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
    }

    public function exec_TRUNCATEquery($table)
    {
        return $this->connection->exec_TRUNCATEquery($table);
    }

    public function fullQuoteArray($arr, $table, $noQuote = FALSE, $allowNull = FALSE)
    {
        return $this->connection->fullQuoteArray($arr, $table, $noQuote, $allowNull);
    }

    public function fullQuoteStr($str, $table, $allowNull = FALSE)
    {
        return $this->connection->fullQuoteStr($str, $table, $allowNull);
    }

    public function getDateTimeFormats($table)
    {
        return $this->connection->getDateTimeFormats($table);
    }

    public function getDatabaseHandle()
    {
        return $this->connection->getDatabaseHandle();
    }

    public function initialize()
    {
        return $this->connection->initialize();
    }

    public function quoteWhereClause($where_clause)
    {
        return $this->connection->quoteWhereClause($where_clause);
    }

    public function quoteStr($str, $table)
    {
        return $this->connection->quoteStr($str, $table);
    }

    public function handler_getFromTableList($tableList)
    {
        return $this->connection->handler_getFromTableList($tableList);
    }

    public function handler_init($handlerKey)
    {
        return $this->connection->handler_init($handlerKey);
    }

    public function isConnected()
    {
        return $this->connection->isConnected();
    }

    public function listQuery($field, $value, $table)
    {
        return $this->connection->listQuery($field, $value, $table);
    }

    public function prepare_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', array $input_parameters = array())
    {
        return $this->connection->prepare_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit, $input_parameters);
    }

    public function prepare_PREPAREDquery($query, array $queryComponents)
    {
        return $this->connection->prepare_PREPAREDquery($query, $queryComponents);
    }

    public function prepare_SELECTqueryArray(array $queryParts, array $input_parameters = array())
    {
        return $this->connection->prepare_SELECTqueryArray($queryParts, $input_parameters);
    }

    public function quoteFieldNames($select_fields)
    {
        return $this->connection->quoteFieldNames($select_fields);
    }

    public function quoteFromTables($from_table)
    {
        return $this->connection->quoteFromTables($from_table);
    }

    public function quoteName($name, $handlerKey = NULL, $useBackticks = FALSE)
    {
        return $this->connection->quoteName($name, $handlerKey, $useBackticks);
    }

    public function runningNative()
    {
        return $this->connection->runningNative();
    }

    public function runningADOdbDriver($driver)
    {
        return $this->connection->runningADOdbDriver($driver);
    }

    public function searchQuery($searchWords, $fields, $table, $constraint = \TYPO3\CMS\Core\Database\DatabaseConnection::AND_Constraint)
    {
        return $this->connection->searchQuery($searchWords, $fields, $table, $constraint);
    }

    public function splitGroupOrderLimit($str)
    {
        return $this->connection->splitGroupOrderLimit($str);
    }

    public function setDatabaseHost($host = 'localhost')
    {
        return $this->connection->setDatabaseHost($host);
    }

    public function setDatabasePort($port = 3306)
    {
        return $this->connection->setDatabasePort($port);
    }

    public function setDatabaseSocket($socket = NULL)
    {
        return $this->connection->setDatabaseSocket($socket);
    }

    public function setDatabaseName($name)
    {
        return $this->connection->setDatabaseName($name);
    }

    public function setDatabaseUsername($username)
    {
        return $this->connection->setDatabaseUsername($username);
    }

    public function setDatabasePassword($password)
    {
        return $this->connection->setDatabasePassword($password);
    }

    public function setPersistentDatabaseConnection($persistentDatabaseConnection)
    {
        return $this->connection->setPersistentDatabaseConnection($persistentDatabaseConnection);
    }

    public function setConnectionCompression($connectionCompression)
    {
        return $this->connection->setConnectionCompression($connectionCompression);
    }

    public function setInitializeCommandsAfterConnect(array $commands)
    {
        return $this->connection->setInitializeCommandsAfterConnect($commands);
    }

    public function setConnectionCharset($connectionCharset = 'utf8')
    {
        return $this->connection->setConnectionCharset($connectionCharset);
    }

    public function setDatabaseHandle($handle)
    {
        return $this->connection->setDatabaseHandle($handle);
    }

    public function sql_error()
    {
        return $this->connection->sql_error();
    }

    public function sql_errno()
    {
        return $this->connection->sql_errno();
    }

    public function sql_num_rows($res)
    {
        return $this->connection->sql_num_rows($res);
    }

    public function sql_fetch_assoc($res)
    {
        return $this->connection->sql_fetch_assoc($res);
    }

    public function sql_fetch_row($res)
    {
        return $this->connection->sql_fetch_row($res);
    }

    public function sql_free_result($res)
    {
        return $this->connection->sql_free_result($res);
    }

    public function sql_insert_id()
    {
        return $this->connection->sql_insert_id();
    }

    public function sql_affected_rows()
    {
        return $this->connection->sql_affected_rows();
    }

    public function sql_data_seek($res, $seek)
    {
        return $this->connection->sql_data_seek($res, $seek);
    }

    public function sql_field_metatype($table, $field)
    {
        return $this->connection->sql_field_metatype($table, $field);
    }

    public function sql_field_type($res, $pointer)
    {
        return $this->connection->sql_field_type($res, $pointer);
    }

    public function sql_query($query)
    {
        return $this->connection->sql_query($query);
    }

    public function sql_pconnect($host = NULL, $username = NULL, $password = NULL)
    {
        return $this->connection->sql_pconnect($host, $username, $password);
    }

    public function sql_select_db($TYPO3_db = NULL)
    {
        return $this->connection->sql_select_db($TYPO3_db);
    }

    public function stripOrderBy($str)
    {
        return $this->connection->stripOrderBy($str);
    }

    public function stripGroupBy($str)
    {
        return $this->connection->stripGroupBy($str);
    }

}