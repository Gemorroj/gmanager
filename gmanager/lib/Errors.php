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
 * PHP version >= 5.2.1
 * 
 */


class Errors
{
    /**
     * Error message
     * 
     * @var string
     */
    private static $_php_errormsg;


    /**
     * Trace log file
     * 
     * @return string
     */
    public static function getTraceFile ()
    {
        return GMANAGER_PATH . '/data/GmanagerTrace.log';
    }


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
        if (!defined('E_DEPRECATED')) {
            define('E_DEPRECATED', 8192);
        }
        if (!defined('E_USER_DEPRECATED')) {
            define('E_USER_DEPRECATED', 16384);
        }

        if (preg_match('/Gmanager\.php\((\d+)\) : eval\(\)/', $errfile)) {
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


                case E_PARSE:
                    echo 'PARSE ERROR: ' . $errstr . ' on line ' . $errline . "\n";
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
            if (!Config::get('Gmanager', 'trace') || !is_writable(dirname(self::getTraceFile())) || (file_exists(self::getTraceFile()) && !is_writable(self::getTraceFile()))) {
                return true;
            }

            switch ($errno) {
                case E_USER_ERROR:
                    @ob_end_clean();
                    echo ini_get('error_prepend_string') . 'USER ERROR: ' . $errstr . '<br/>Fatal error on line ' . $errline . ' ' . $errfile . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')<br/>Aborting...' . ini_get('error_append_string');
                    file_put_contents(self::getTraceFile(), 'USER ERROR: ' . $errstr . '. Fatal error on line ' . $errline . ' ' . $errfile . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
                    exit;
                    break;


                case E_WARNING:
                case E_USER_WARNING:
                    self::$_php_errormsg = 'WARNING: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                case E_NOTICE:
                case E_USER_NOTICE:
                    self::$_php_errormsg = 'NOTICE: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                case E_STRICT:
                    self::$_php_errormsg = 'STRICT: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                case E_RECOVERABLE_ERROR:
                    self::$_php_errormsg = 'RECOVERABLE ERROR: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                case E_PARSE:
                    self::$_php_errormsg = 'PARSE ERROR: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    self::$_php_errormsg = 'DEPRECATED: ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;


                default:
                    self::$_php_errormsg = 'Error type: [' . $errno . '], ' . $errstr . ' on line ' . $errline . ' ' . $errfile;
                    break;
            }
            file_put_contents(self::getTraceFile(), '[' . date('r') . '] ' . self::$_php_errormsg . ', PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . "\n" . print_r(debug_backtrace(), true) . "\n\n", FILE_APPEND);
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
}

?>
