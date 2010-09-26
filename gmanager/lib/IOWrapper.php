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


class IOWrapper
{
    /**
     * Getter
     * 
     * @param string $data
     * @return string
     */
    public static function get ($data)
    {
        if (Config::$sysType == 'WIN') {
            return iconv(Config::$altencoding, 'UTF-8', $data);
        } else {
            return $data;
        }
    }


    /**
     * Setter
     * 
     * @param string $data
     * @return string
     */
    public static function set ($data)
    {
        if (Config::$sysType == 'WIN') {
            return iconv('UTF-8', Config::$altencoding, $data);
        } else {
            return $data;
        }
    }
}

?>
