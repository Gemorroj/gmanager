<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2017 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class Config
{
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

        Registry::set('top', '<!DOCTYPE html><html><head><title>%title% - Gmanager 0.9 beta</title><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="public/style.min.css"/><script type="text/javascript" src="public/script.min.js"></script></head><body>');
        Registry::set('foot', '<div class="w">Powered by Gemorroj<br/><a href="https://github.com/Gemorroj/gmanager">Gmanager v 0.9 beta</a></div></body></html>');

        Language::setLanguage(self::get('Gmanager', 'language'));

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
     * getVersion
     * 
     * @return string
     */
    public static function getVersion ()
    {
        return '0.9b';
    }
}
