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


class Errors
{
    const MESSAGE_OK = 0;
    const MESSAGE_FAIL = 1;
    const MESSAGE_EMAIL = 2;

    private static $_php_errormsg;


    /**
     * errorHandler
     * 
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @return bool
     */
    public static function errorHandler ($errno, $errstr, $errfile, $errline)
    {
        if (preg_match('/Gmanager\.php\((\d+)\) : eval\(\)\'d code/', $errfile)) {
            switch ($errno) {
                case E_USER_ERROR:
                    @ob_end_clean();
                    echo 'USER ERROR: ' . $errstr . '. Fatal error on line ' . $errline . ', aborting...' . "\n";
                    exit;
                    break;


                case E_WARNING:
                case E_USER_WARNING:
                    echo 'WARNING: ' . $errstr . ' on line ' . $errline . "\n";
                    break;


                case E_NOTICE:
                case E_USER_NOTICE:
                    echo 'NOTICE: ' . $errstr . ' on line ' . $errline . "\n";
                    break;


                case E_STRICT:
                    echo 'STRICT: ' . $errstr . ' on line ' . $errline . "\n";
                    break;


                case E_RECOVERABLE_ERROR:
                    echo 'RECOVERABLE ERROR: ' . $errstr . ' on line ' . $errline . "\n";
                    break;


                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    echo 'DEPRECATED: ' . $errstr . ' on line ' . $errline . "\n";
                    break;


                default:
                    echo 'Error type: [' . $errno . '], ' . $errstr . ' on line ' . $errline . "\n";
                    break;
            }
        } else {
            switch ($errno) {
                case E_USER_ERROR:
                    @ob_end_clean();
                    echo ini_get('error_prepend_string') . 'USER ERROR: ' . $errstr . '<br/>Fatal error on line ' . $errline . ' ' . $errfile . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')<br/>Aborting...' . ini_get('error_append_string');
                    if (Config::$errors) {
                        file_put_contents(Config::$errors, 'USER ERROR: ' . $errstr . '. Fatal error on line ' . $errline . ' ' . $errfile . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    exit;
                    break;


                case E_WARNING:
                case E_USER_WARNING:
                    self::$_php_errormsg = 'WARNING: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;


                case E_NOTICE:
                case E_USER_NOTICE:
                    self::$_php_errormsg = 'NOTICE: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;


                case E_STRICT:
                    self::$_php_errormsg = 'STRICT: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;


                case E_RECOVERABLE_ERROR:
                    self::$_php_errormsg = 'RECOVERABLE ERROR: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;


                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    self::$_php_errormsg = 'DEPRECATED: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;


                default:
                    self::$_php_errormsg = 'Error type: [' . $errno . '], ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    if (Config::$errors && is_writable(Config::$errors)) {
                        file_put_contents(Config::$errors, self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    }
                    break;
            }
        }

        return true;
    }


    /**
     * get
     * 
     * @return string
     */
    public static function get ()
    {
        if (self::$_php_errormsg) {
            return self::$_php_errormsg;
        }

        $err = error_get_last();
        if ($err) {
            return $err['message'] . ' (' . $err['file'] . ': ' . $err['line'] . ')';
        } else {
            return Language::get('unknown_error');
        }
    }


    /**
     * message
     * 
     * @param string $text
     * @param int    $error Errors::MESSAGE_OK - ok, Errors::MESSAGE_FAIL - error, Errors::MESSAGE_EMAIL - error + email
     * @return string
     */
    public static function message ($text = '', $error = Errors::MESSAGE_OK)
    {
        if ($error == self::MESSAGE_EMAIL) {
            return '<div class="red">' . $text . '<br/></div><div><form action="change.php?go=send_mail&amp;c=' . Config::$rCurrent . '" method="post"><div><input type="hidden" name="to" value="wapinet@mail.ru"/><input type="hidden" name="theme" value="Gmanager ' . Config::$version . ' Error (' . get_parent_class(Config) . ')"/><input type="hidden" name="mess" value="' . htmlspecialchars('URI: ' . basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'] . "\n" . 'PHP: ' . PHP_VERSION . "\n" . htmlspecialchars_decode(str_replace('<br/>', "\n", $text), ENT_COMPAT), ENT_COMPAT) . '"/><input type="submit" value="' . Language::get('send_report') . '"/></div></form></div>';
        } else if ($error == self::MESSAGE_FAIL) {
            return '<div class="red">' . $text . '<br/></div>';
        }

        return '<div class="green">' . $text . '<br/></div>';
    }
}

?>
