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


class Auth
{
    /**
     * Auth
     */
    public static function main ()
    {
        // CGI fix
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $params = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            $_SERVER['PHP_AUTH_USER'] = $params[0];
            unset($params[0]);
            $_SERVER['PHP_AUTH_PW'] = implode('', $params);
        }
        // CGI fix

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != Config::get('Auth', 'user') || $_SERVER['PHP_AUTH_PW'] != Config::get('Auth', 'pass')) {
            header('WWW-Authenticate: Basic realm="Authentification"');
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('Content-type: text/html; charset=UTF-8');
            exit(str_replace('%title%', 'Error', Registry::get('top')) . '<p style="color:red;font-size:24pt;text-align:center">Unauthorized</p>' . Registry::get('foot'));
        }
    }
}
