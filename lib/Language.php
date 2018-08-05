<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class Language
{
    /**
     * @var array
     */
    private static $_lng = array();

    /**
     * setLanguage
     * 
     * @param string $lng
     */
    public static function setLanguage ($lng = 'en')
    {
        self::$_lng = require GMANAGER_PATH . '/lng/' . $lng . '.php';
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
