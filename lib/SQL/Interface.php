<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2017 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


interface SQL_Interface
{
    public function installer ($host = null, $name = null, $pass = null, $db = '', $charset = null, $sql = '');
    public function backup ($host = null, $name = null, $pass = null, $db = '', $charset = null, $tables = array());
    public function query ($host = null, $name = null, $pass = null, $db = '', $charset = null, $data = '');
}
