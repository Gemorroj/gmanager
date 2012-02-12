<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8.1 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class Language
{
    private static $_lng = array();

    /**
     * setLanguage
     * 
     * @param string $lng
     */
    public static function setLanguage ($lng = 'en')
    {
        self::$_lng = require_once GMANAGER_PATH . '/lng/' . $lng . '.php';
    }


    /**
     * get
     * 
     * @param  string $str
     * @return string
     */
    public static function get ($str)
    {
        return self::$_lng[$str];
    }
}

?>
