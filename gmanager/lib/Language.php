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


class Language
{
    private static $_lng = array();

    /**
     * setLanguage
     * 
     * @return void
     */
    public static function setLanguage ()
    {
        require_once dirname(__FILE__) . '/../lng/' . Config::$language . '.php';
        self::$_lng = & $language;
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
