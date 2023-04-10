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


\define('GMANAGER_START', \microtime(true));
\define('GMANAGER_PATH', __DIR__);
\define('GMANAGER_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . \str_replace(array('\\', '//'), '/', \dirname($_SERVER['PHP_SELF']) . '/'));

/**
 * Composer autoloader
 */
require __DIR__ . '/vendor/autoload.php';


Config::setConfig(__DIR__ . '/.config.ini');

// for PhpProcess
\putenv('PHP_PATH=' . Config::get('path', 'PHP'));

switch ($_GET['gmanager_action'] ?? 'index') {
    case 'edit':
        include __DIR__ . '/controllers/edit.php';
        break;

    case 'change':
        include __DIR__ . '/controllers/change.php';
        break;

    default:
        include __DIR__ . '/controllers/index.php';
        break;
}
