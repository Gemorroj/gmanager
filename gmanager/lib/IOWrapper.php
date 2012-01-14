<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8
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
        $enc = Config::get('Gmanager', 'altEncoding');
        return ($enc !== 'UTF-8' ? mb_convert_encoding($data, 'UTF-8', $enc) : $data);
    }


    /**
     * Setter
     * 
     * @param string $data
     * @return string
     */
    public static function set ($data)
    {
        $enc = Config::get('Gmanager', 'altEncoding');
        return ($enc !== 'UTF-8' ? mb_convert_encoding($data, $enc, 'UTF-8') : $data);
    }
}

?>
