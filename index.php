<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      http://wapinet.ru/gmanager/
 * @version   0.8.1 beta
 *
 * PHP version >= 5.2.3
 *
 */


define('GMANAGER_START', microtime(true));
define('GMANAGER_PATH', dirname(__FILE__));
define('GMANAGER_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace(array('\\', '//'), '/', dirname($_SERVER['PHP_SELF']) . '/'));

Config::setConfig('.config.ini');

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
function __autoload($class)
{
    require GMANAGER_PATH . '/lib/' . str_replace('_', '/', $class) . '.php';
}


switch (isset($_GET['gmanager_action']) ? $_GET['gmanager_action'] : 'index') {
    case 'edit':
        include GMANAGER_PATH . '/controllers/edit.php';
        break;

    case 'change':
        include GMANAGER_PATH . '/controllers/change.php';
        break;

    default:
        include GMANAGER_PATH . '/controllers/index.php';
        break;
}
