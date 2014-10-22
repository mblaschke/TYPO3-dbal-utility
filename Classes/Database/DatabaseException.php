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
 * Database Exception
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class DatabaseException extends \RuntimeException {

    /**
     * SQL error message
     *
     * @var string|null
     */
    protected $sqlError = NULL;

    /**
     * SQL error message number
     *
     * @var string|integer|null
     */
    protected $sqlErrorNumber = NULL;

    /**
     * SQL query
     *
     * @var string|null
     */
    protected $sqlQuery = NULL;

    /**
     * Set sql error message
     *
     * @param string|null $sqlError
     */
    public function setSqlError($sqlError)
    {
        $this->sqlError = $sqlError;
    }

    /**
     * Get sql error message
     *
     * @return string|null
     */
    public function getSqlError()
    {
        return $this->sqlError;
    }

    /**
     * Set sql error number
     *
     * @param null|string|integer $sqlErrorNumber
     */
    public function setSqlErrorNumber($sqlErrorNumber)
    {
        $this->sqlErrorNumber = $sqlErrorNumber;
    }

    /**
     * Get sql error number
     *
     * @return null|string|integer
     */
    public function getSqlErrorNumber()
    {
        return $this->sqlErrorNumber;
    }

    /**
     * Set sql query
     *
     * @param string|null $sqlQuery
     */
    public function setSqlQuery($sqlQuery)
    {
        $this->sqlQuery = $sqlQuery;
    }

    /**
     * Get sql query
     *
     * @return string|null
     */
    public function getSqlQuery()
    {
        return $this->sqlQuery;
    }
}