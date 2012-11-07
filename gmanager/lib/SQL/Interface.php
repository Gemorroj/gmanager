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
 * PHP version >= 5.2.3
 * 
 */


interface SQL_Interface
{
    public function installer ($host = null, $name = null, $pass = null, $db = '', $charset = null, $sql = '');
    public function backup ($host = null, $name = null, $pass = null, $db = '', $charset = null, $tables = array());
    public function query ($host = null, $name = null, $pass = null, $db = '', $charset = null, $data = '');
}
