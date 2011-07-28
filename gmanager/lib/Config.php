<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2011 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class Config
{
    const SYNTAX_LOCALHOST          = 1;
    const SYNTAX_WAPINET            = 2;
    const REALNAME_RELATIVE         = 1;
    const REALNAME_FULL             = 2;
    const REALNAME_RELATIVE_HIDE    = 3;

    private static $_config;


    /**
     * Constructor
     * 
     * @param string $config
     */
    public function __construct ($config)
    {
        self::$_config = new Config_Ini($config);

        Registry::set('top', '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru"><head><title>%title% - Gmanager 0.8 beta</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><link rel="stylesheet" type="text/css" href="css.css"/><script type="text/javascript" src="js.js"></script></head><body>');
        Registry::set('foot', '<div class="w">Powered by Gemorroj<br/><a href="http://wapinet.ru/gmanager/">wapinet.ru</a></div></body></html>');

        Language::setLanguage(self::get('Gmanager', 'language'));

        define('PCLZIP_TEMPORARY_DIR', $this->getTemp() . '/');
        define('GMANAGER_REQUEST_TIME', time());

        iconv_set_encoding('internal_encoding', 'UTF-8');
        setlocale(LC_ALL, self::get('PHP', 'locale'));
        date_default_timezone_set(self::get('PHP', 'timeZone'));
        @set_time_limit(self::get('PHP', 'timeLimit'));
        ini_set('max_execution_time', self::get('PHP', 'timeLimit'));
        ini_set('memory_limit', self::get('PHP', 'memoryLimit'));

        ini_set('error_prepend_string', '<div class="red">');
        ini_set('error_append_string', '</div><div class="rb"><br/></div>' . Registry::get('foot'));
        ini_set('error_log', Errors::getTraceFile());
        set_error_handler('Errors::errorHandler');

        if (self::get('Gmanager', 'mode') === 'FTP') {
            Registry::setGmanager(new FTP(
                self::get('FTP', 'user'),
                self::get('FTP', 'pass'),
                self::get('FTP', 'host'),
                self::get('FTP', 'port')
            ));
        } else {
            Registry::setGmanager(new HTTP);
        }
        Registry::getGmanager()->main();


        if (self::get('Auth', 'enable')) {
            Auth::main();
        }
    }


    /**
     * get
     * 
     * @return string
     */
    public static function get ($section = 'Gmanager', $property)
    {
        return self::$_config->get($section, $property);
    }


    /**
     * get
     * 
     * @return string
     */
    public static function getSection ($section = 'Gmanager')
    {
        return self::$_config->getSection($section);
    }


    /**
     * getTemp
     * 
     * @return string
     */
    public static function getTemp ()
    {
        return realpath(dirname(__FILE__) . '/../data');
    }


    /**
     * getVersion
     * 
     * @return string
     */
    public static function getVersion ()
    {
        return '0.8b';
    }
}


new Config(dirname(__FILE__) . '/../config.ini');

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PEAR'
);


/**
 * Autoloader
 *
 * @param string $class
 * @return void
 */
function __autoload ($class)
{
    require dirname(__FILE__) . '/' . str_replace('_', '/', $class) . '.php';
}

?>
