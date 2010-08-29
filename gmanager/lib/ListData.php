<?php
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.4 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class ListData
{
    /**
     * getData
     * 
     * @return string
     */
    public static function getData ()
    {
        // TODO: Переписать метод look и search
        return '';
    }


    /**
     * getEmptyData
     * 
     * @return string
     */
    public static function getEmptyData ()
    {
        return '<tr class="border"><th colspan="' . (array_sum(Config::$index) + 1) . '">' . $GLOBALS['lng']['dir_empty'] . '</th></tr>';
    }


    /**
     * getEmptySearchData
     * 
     * @return string
     */
    public static function getEmptySearchData ()
    {
        return '<tr class="border"><th colspan="' . (array_sum(Config::$index) + 1) . '">' . $GLOBALS['lng']['empty_search'] . '</th></tr>';
    }


    /**
     * getDenyData
     * 
     * @return string
     */
    public static function getDenyData ()
    {
        return '<tr><td class="red" colspan="' . (array_sum(Config::$index) + 1) . '">' . $GLOBALS['lng']['permission_denided'] . '</td></tr>';
    }
}

?>
