<?php
/**
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @see      https://github.com/Gemorroj/gmanager
 */
class Auth
{
    public static function main(): void
    {
        // CGI fix
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $params = \explode(':', \base64_decode(\substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            $_SERVER['PHP_AUTH_USER'] = $params[0];
            unset($params[0]);
            $_SERVER['PHP_AUTH_PW'] = \implode('', $params);
        }
        // CGI fix

        if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] != Config::get('user', 'Auth') || $_SERVER['PHP_AUTH_PW'] != Config::get('pass', 'Auth')) {
            \header('WWW-Authenticate: Basic realm="Authentication"');
            \header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
            \header('Content-type: text/html; charset=UTF-8');
            exit(\str_replace('%title%', 'Error', Registry::get('top')).'<p style="color:red;font-size:24pt;text-align:center">Unauthorized</p>'.Registry::get('foot'));
        }
    }
}
