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


class Registry
{
    static private $_data = array();
    static private $_Gmanager = null;


    /**
     * set
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    static public function set ($key, $val)
    {
        self::$_data[$key] = $val;
    }


    /**
     * get
     * 
     * @param string $key
     * @return mixed
     */
    static public function get ($key)
    {
        return self::$_data[$key];
    }


    /**
     * remove
     * 
     * @param string $key
     * @return void
     */
    static public function remove ($key)
    {
        unset(self::$_data[$key]);
    }


    /**
     * exists
     * 
     * @param string $key
     * @return bool
     */
    static public function exists ($key)
    {
        return array_key_exists($key, self::$_data);
    }


    /**
     * setGmanager
     * 
     * @param object $Gmanager
     * @return void
     */
    static public function setGmanager ($Gmanager)
    {
        self::$_Gmanager = $Gmanager;
    }


    /**
     * getGmanager
     * 
     * @return object
     */
    static public function getGmanager ()
    {
        return self::$_Gmanager;
    }
}

?>
