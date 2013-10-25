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


class Config
{
    const SYNTAX_LOCALHOST          = 1;
    const SYNTAX_WAPINET            = 2;
    const REALNAME_RELATIVE         = 1;
    const REALNAME_FULL             = 2;
    const REALNAME_RELATIVE_HIDE    = 3;

    /**
     * @var Config_Interface
     */
    private static $_config;


    /**
     * setConfig
     * 
     * @param string $config
     */
    public static function setConfig ($config)
    {
        self::$_config = new Config_Ini($config);

        Registry::set('top', '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru"><head><title>%title% - Gmanager 0.8.1 beta</title><meta http-equiv="Content-Type" content="' . self::getContentType() . '; charset=UTF-8" /><link rel="stylesheet" type="text/css" href="public/style.min.css"/><script type="text/javascript" src="public/script.min.js"></script></head><body>');
        Registry::set('foot', '<div class="w">Powered by Gemorroj<br/><a href="http://wapinet.ru/gmanager/">wapinet.ru</a></div></body></html>');

        Language::setLanguage(self::get('Gmanager', 'language'));

        define('PCLZIP_TEMPORARY_DIR', self::getTemp() . '/');
        define('GMANAGER_REQUEST_TIME', time());

        mb_internal_encoding('UTF-8');
        setlocale(LC_ALL, self::get('PHP', 'locale'));
        date_default_timezone_set(self::get('PHP', 'timeZone'));
        @set_time_limit(self::get('PHP', 'timeLimit'));
        ini_set('max_execution_time', self::get('PHP', 'timeLimit'));
        ini_set('memory_limit', self::get('PHP', 'memoryLimit'));

        ini_set('error_log', Errors::getTraceFile());
        ini_set('error_prepend_string', '<div class="red">');
        ini_set('error_append_string', '</div><div class="rb"><br/></div>' . Registry::get('foot'));

        Errors::initHandler();

        if (self::get('Auth', 'enable')) {
            Auth::main();
        }

        Gmanager::getInstance()->init();
    }


    /**
     * get
     *
     * @param string $section
     * @param string $property
     * @return string
     */
    public static function get ($section = 'Gmanager', $property)
    {
        return self::$_config->get($section, $property);
    }


    /**
     * get
     *
     * @param string $section
     * @return array
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
        return GMANAGER_PATH . DIRECTORY_SEPARATOR . 'data';
    }


    /**
     * getContentType
     * 
     * @return string
     */
    public static function getContentType ()
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml') !== false) {
            return 'application/xhtml+xml';
        }
        return 'text/html';
    }


    /**
     * getVersion
     * 
     * @return string
     */
    public static function getVersion ()
    {
        return '0.8.1b';
    }
}
