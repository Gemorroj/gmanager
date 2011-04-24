<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2011 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8 beta
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
        if (Registry::get('sysType') == 'WIN') {
            return iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8', $data);
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
        if (Registry::get('sysType') == 'WIN') {
            return iconv('UTF-8', Config::get('Gmanager', 'altEncoding'), $data);
        } else {
            return $data;
        }
    }
}

?>
