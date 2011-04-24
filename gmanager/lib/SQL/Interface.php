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


interface SQL_Interface
{
    public function installer ($host, $name, $pass, $db, $charset, $sql);
    public function backup ($host, $name, $pass, $db, $charset, $tables = array());
    public function query ($host, $name, $pass, $db, $charset, $data);
}

?>
