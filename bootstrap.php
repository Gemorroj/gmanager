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


define('GMANAGER_START', microtime(true));
define('GMANAGER_PATH', dirname(__FILE__));

Config::setConfig('config.ini');

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    GMANAGER_PATH . DIRECTORY_SEPARATOR . 'lib' . PATH_SEPARATOR .
    GMANAGER_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PEAR'
);


/**
 * Autoloader
 *
 * @param string $class
 */
function __autoload ($class)
{
    require GMANAGER_PATH . '/lib/' . str_replace('_', '/', $class) . '.php';
}
