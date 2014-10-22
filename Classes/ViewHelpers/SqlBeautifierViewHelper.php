<?php
namespace Lightwerk\DbalUtility\ViewHelpers;

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
 * SQL Beautifier View helper
 *
 * @package     TYPO3
 * @subpackage  dbal_utility
 */
class SqlBeautifierViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * Beautifies SQL Query
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function render() {
        // Render SQL query
        $content = $this->renderChildren();

        // Decode escaping, we need the raw query
        $content = htmlspecialchars_decode($content);

        // Beautify sql query (with external lib)
        \Lightwerk\DbalUtility\Utility\SqlFormatter::$pre_attributes = '';
        $content = \Lightwerk\DbalUtility\Utility\SqlFormatter::format($content);

        return $content;
    }

}