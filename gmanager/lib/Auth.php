<?php
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.4 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class Auth
{
    /**
     * Auth
     * 
     * @return void
     */
    public static function main ()
    {
        if (Config::$auth['on']) {
            // CGI fix
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $params = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
                $_SERVER['PHP_AUTH_USER'] = $params[0];
                unset($params[0]);
                $_SERVER['PHP_AUTH_PW'] = implode('', $params);
            }
            // CGI fix

            if (@$_SERVER['PHP_AUTH_USER'] != Config::$auth['user'] || @$_SERVER['PHP_AUTH_PW'] != Config::$auth['pass']) {
                header('WWW-Authenticate: Basic realm="Authentification"');
                header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
                header('Content-type: text/html; charset=UTF-8');
                exit(str_replace('%title%', 'Error', Config::$top) . '<p style="color:red;font-size:24pt;text-align:center">Unauthorized</p>' . Config::$foot);
            }
        }
    }
}

?>
