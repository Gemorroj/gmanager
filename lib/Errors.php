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
class Errors
{
    /**
     * Error message.
     *
     * @var string
     */
    private static $_php_errormsg;

    /**
     * Trace log file.
     *
     * @return string
     */
    public static function getTraceFile()
    {
        return GMANAGER_PATH.'/data/GmanagerTrace.log';
    }

    /**
     * Init error handler.
     */
    public static function initHandler()
    {
        \set_error_handler('Errors::errorHandler');
    }

    /**
     * Get result errors for eval error handler.
     *
     * @param string $content
     * @param string $token
     *
     * @return array
     */
    public static function getResultHandlerEval($content, $token)
    {
        $errorPrependString = \ini_get('error_prepend_string');
        $errorAppendString = \ini_get('error_append_string');

        if ($errorPrependString && \mb_substr($content, 0, \mb_strlen($errorPrependString)) === $errorPrependString) {
            $content = \mb_substr($content, \mb_strlen($errorPrependString));
        }
        if ($errorAppendString && \mb_substr($content, -\mb_strlen($errorAppendString)) === $errorAppendString) {
            $content = \mb_substr($content, 0, -\mb_strlen($errorAppendString));
        }

        [$data, $stat] = \explode($token, $content, 2);

        return [
            'content' => $data,
            'stat' => \json_decode($stat, true),
        ];
    }

    /**
     * errorHandler.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return bool
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!Config::get('Gmanager', 'trace') || !\is_writable(\dirname(self::getTraceFile())) || (\file_exists(self::getTraceFile()) && !\is_writable(self::getTraceFile()))) {
            return true;
        }

        switch ($errno) {
            case \E_USER_ERROR:
                @\ob_end_clean();
                echo \ini_get('error_prepend_string').'USER ERROR: '.$errstr.'<br/>Fatal error on line '.$errline.' '.$errfile.', PHP '.\PHP_VERSION.' ('.\PHP_OS.')<br/>Aborting...'.\ini_get('error_append_string');
                \file_put_contents(self::getTraceFile(), 'USER ERROR: '.$errstr.'. Fatal error on line '.$errline.' '.$errfile.', PHP '.\PHP_VERSION.' ('.\PHP_OS.')'."\n".\print_r(\debug_backtrace(), true)."\n\n", \FILE_APPEND);
                exit;
                break;

            case \E_WARNING:
            case \E_USER_WARNING:
                self::$_php_errormsg = 'WARNING: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            case \E_NOTICE:
            case \E_USER_NOTICE:
                self::$_php_errormsg = 'NOTICE: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            case \E_STRICT:
                self::$_php_errormsg = 'STRICT: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            case \E_RECOVERABLE_ERROR:
                self::$_php_errormsg = 'RECOVERABLE ERROR: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            case \E_PARSE:
                self::$_php_errormsg = 'PARSE ERROR: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            case \E_DEPRECATED:
            case \E_USER_DEPRECATED:
                self::$_php_errormsg = 'DEPRECATED: '.$errstr.' on line '.$errline.' '.$errfile;
                break;

            default:
                self::$_php_errormsg = 'Error type: ['.$errno.'], '.$errstr.' on line '.$errline.' '.$errfile;
                break;
        }

        \file_put_contents(self::getTraceFile(), '['.\date('r').'] '.self::$_php_errormsg.', PHP '.\PHP_VERSION.' ('.\PHP_OS.')'."\n".\print_r(\debug_backtrace(), true)."\n\n", \FILE_APPEND);

        return true;
    }

    /**
     * get.
     *
     * @return string
     */
    public static function get()
    {
        if (self::$_php_errormsg) {
            return self::$_php_errormsg;
        }

        $err = \error_get_last();
        if ($err) {
            return $err['message'].' ('.$err['file'].': '.$err['line'].')';
        }

        return Language::get('unknown_error');
    }
}
