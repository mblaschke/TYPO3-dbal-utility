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
 * Database Result
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class DatabaseResult implements \Iterator {
    ###########################################################################
    ## Attributes
    ###########################################################################

    /**
     * Database connection
     *
     * @var \Lightwerk\DbalUtility\Database\DatabaseConnection
     */
    protected $connection = NULL;

    /**
     * Query Result
     *
     * @var mixed
     */
    protected $queryRes = NULL;

    /**
     * Iterator position
     *
     * @var integer
     */
    protected $iteratorPos = 0;

    /**
     * Number of results (row-count)
     *
     * @var integer
     */
    protected $queryRowCount = 0;


    ###########################################################################
    ## Constructor
    ###########################################################################

    /**
     * Constructor
     *
     * @param \Lightwerk\DbalUtility\Database\DatabaseConnection $dbCon       Database connction
     * @param resource                                    $queryResult Query Result
     */
    public function __construct(\Lightwerk\DbalUtility\Database\DatabaseConnection $dbCon, $queryResult) {
        $this->setDatabaseConnection($dbCon);
        $this->setResult($queryResult);
    }

    /**
     * Destruct
     */
    public function __destruct() {
        // remove result from memory
        $this->connection->free($this->queryRes);
    }

    ###########################################################################
    ## Iterator methods
    ###########################################################################

    /**
     * Rewind position
     */
    public function rewind() {
        $this->iteratorPos = 0;
    }

    /**
     * Check if key is valid
     *
     * @return      boolean
     */
    public function valid() {
        return ($this->iteratorPos < $this->queryRowCount);
    }

    /**
     * Return current key
     *
     * @return      mixed
     */
    public function key() {
        return $this->iteratorPos;
    }

    /**
     * Return current row
     *
     * @return      array
     */
    public function current() {
        return $this->fetch();
    }

    /**
     * Goto next row
     */
    public function next() {
        ++$this->iteratorPos;
    }

    ###########################################################################
    ## Public methods
    ###########################################################################

    /**
     * Fetch one row from result
     *
     * @return array
     */
    public function fetch() {
        // seek to row number
        $this->seek($this->iteratorPos);
        $ret = $this->connection->fetchAssoc($this->queryRes);

        if ( !is_array($ret) ) {
            $ret = array();
        }

        return $ret;
    }

    /**
     * Total count of results
     *
     * @return integer
     */
    public function count() {
        return $this->queryRowCount;
    }

    /**
     * Seek to a row position
     *
     * @param  integer $position  Position
     * @return boolean
     */
    public function seek($position) {
        $position = abs($position);
        if ($position < $this->queryRowCount) {
            $this->connection->queryResultSeek($this->queryRes, $position);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Fetch all rows from result
     *
     * @return  array
     */
    public function fetchAll() {
        // INIT
        $ret = array();

        $this->seek(0);

        while ($row = $this->connection->fetchAssoc($this->queryRes)) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Fetch all rows from result
     *
     * @param   mixed   $indexCol   Index column name
     * @return  array
     */
    public function fetchAllWithIndex($indexCol = false) {
        // INIT
        $ret = array();

        $this->seek(0);

        while ($row = $this->connection->fetchAssoc($this->queryRes)) {
            if ( $indexCol === NULL ) {
                // use first key as index
                $index = reset($row);
            } else {
                $index = $row[$indexCol];
            }

            $ret[$index] = $row;
        }

        return $ret;
    }

    /**
     * Set database connection
     *
     * @param \Lightwerk\DbalUtility\Database\DatabaseConnection $connection
     */
    public function setDatabaseConnection(\Lightwerk\DbalUtility\Database\DatabaseConnection $connection) {
        $this->connection = $connection;
    }

    /**
     * Set sql query result resource
     *
     * @param  resource  $queryResult Query results
     */
    public function setResult($queryResult) {
        $this->queryRes = $queryResult;

        // calc row numbers
        $this->queryRowCount = $this->connection->queryResultRowCount($this->queryRes);
    }

}