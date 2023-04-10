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
class Config
{
    public const REALNAME_RELATIVE = 1;
    public const REALNAME_FULL = 2;
    public const REALNAME_RELATIVE_HIDE = 3;

    /**
     * @var Config_Interface
     */
    private static $_config;

    public static function setConfig(string $configFile): void
    {
        self::$_config = new Config_Ini($configFile);

        Registry::set('top', '<!DOCTYPE html><html><head><title>%title% - Gmanager v'.self::getVersion().'</title><meta charset="UTF-8" /><link rel="stylesheet" type="text/css" href="static/style.css"/><script type="text/javascript" src="static/script.js"></script></head><body>');
        Registry::set('foot', '<div class="w">Powered by Gemorroj<br/><a href="https://github.com/Gemorroj/gmanager">Gmanager v'.self::getVersion().'</a></div></body></html>');

        Language::setLanguage(self::get('language'));

        \define('GMANAGER_REQUEST_TIME', \time());

        \mb_internal_encoding('UTF-8');
        \setlocale(\LC_ALL, self::get('locale', 'PHP'));
        \date_default_timezone_set(self::get('timeZone', 'PHP'));
        @\set_time_limit(self::get('timeLimit', 'PHP'));
        \ini_set('max_execution_time', self::get('timeLimit', 'PHP'));
        \ini_set('memory_limit', self::get('memoryLimit', 'PHP'));

        \ini_set('error_log', Errors::getTraceFile());
        \ini_set('error_prepend_string', '<div class="red">');
        \ini_set('error_append_string', '</div><div class="rb"><br/></div>'.Registry::get('foot'));

        Errors::initHandler();

        if (self::get('enable', 'Auth')) {
            Auth::main();
        }

        Gmanager::getInstance()->init();
    }

    public static function get(string $property, string $section = 'Gmanager'): ?string
    {
        return self::$_config->get($property, $section);
    }

    public static function getSection(string $section = 'Gmanager'): array
    {
        return self::$_config->getSection($section);
    }

    public static function getTemp(): string
    {
        return GMANAGER_PATH.\DIRECTORY_SEPARATOR.'data';
    }

    public static function getVersion(): string
    {
        return '0.9.1';
    }
}
