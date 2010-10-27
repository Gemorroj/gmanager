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


/**
 * Конфигурация
 */
class Config extends HTTP // Протокол, через который будет работать менеджер FTP или HTTP (в верхнем регистре)
{
    /**
     * Директория по умолчанию
     */
    public static $current          = './';


    /**
     * Данные для авторизации в менеджере
     */
    public static $auth = array (
        'on'   => false,        // Авторизация на вход в менеджер
        'pass' => '1234',       // Пароль
        'user' => 'Gemorroj'    // Логин
    );


    /**
     * Данные для FTP
     */
    public static $ftp = array (
        'user' => 'root',       // Пользователь FTP
        'pass' => '',           // Пароль FTP
        'host' => 'localhost',  // Хост FTP
        'port' => 21            // Порт FTP
    );


    /**
     * Локализация (en, ua, ru)
     */
    public static $language        = 'ru';

    /**
     * Поле ввода где можно в ручную набирать путь к папке/файлу
     */
    public static $addressBar      = true;
    /**
     * Отображение путей к файлам
     * 0 - Относительные пути к файлам/директориям,
     * 1 - Полные пути к файлам/директориям,
     * 2 - Скрывать относительные пути к файлам/директориям
     */
    public static $realname        = 2;
    /**
     * Проверка PHP синтаксиса
     * 0 - проверка синтаксиса PHP кода у себя на сервере (если работает exec)
     * 1 - проверка синтаксиса через специальный сервис на wapinet.ru
     */
    public static $syntax          = 1;
    /**
     * Открывать редактор в отдельном окне
     */
    public static $target          = false;
    /**
     * Подсчет размеров директорий
     */
    public static $dir_size        = false;
    /**
     * Переносы строк в текстовом редакторе
     */
    public static $wrap            = false;
    /**
     * Максимальное количество файлов на странице по умолчанию
     */
    public static $limit           = 50;
    /**
     * Сокращать имена файлов, если они длиннее чем указанное количество символов
     */
    public static $link            = 50;
    /**
     * Путь к PHP
     */
    public static $php             = '/usr/local/bin/php';
    /**
     * Набор символов для рандомного переименования файлов
     */
    public static $rand            = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';


    /**
     * Отображаемые колонки в менеджере
     */
    public static $index = array (
        'name'      =>  true,       // Имя файла
        'down'      =>  true,       // Ссылка скачать
        'type'      =>  true,       // Тип файла
        'size'      =>  true,       // Размер
        'change'    =>  true,       // Ссылка изменить
        'del'       =>  true,       // Ссылка удалить
        'chmod'     =>  true,       // CHMOD
        'date'      =>  true,       // Дата последнего изменения
        'uid'       =>  true,       // Владелец
        'gid'       =>  true,       // Группа
        'n'         =>  true        // Номер
    );


    /**
     * Построчный редактор
     */
    public static $line_editor = array(
        'on'        =>  false,      // Вкл/Выкл
        'min_lines' =>  10,         // минимальное количество строк в файле с которого запускается построчный редактор, а не обычный
        'lines'     =>  10          // количество отображаемых строк
    );


    /**
     * Формат даты
     */
    public static $date_format     = '%d.%m.%Y %H:%M';
    /**
     * Системная кодировка
     */
    public static $altencoding     = 'Windows-1251';
    /**
     * Кодировка консоли
     */
    public static $consencoding    = 'CP866';


    /**
     * Верх
     * %title% - заменяется на имя текущей директории или файла
     */
    public static $top = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru"><head><title>%title% - Gmanager 0.7.4 beta</title><link rel="stylesheet" type="text/css" href="style.css"/><script type="text/javascript" src="js.js"></script></head><body>';
    /**
     * Низ
     */
    public static $foot = '<div class="w">Powered by Gemorroj<br/><a href="http://wapinet.ru/gmanager/">wapinet.ru</a></div></body></html>';


    /**
     * Версия Менеджера (Не Менять!)
     */
    public static $version = '0.7.4b';


    /**
     * далее не трогаем
     */
    public static $mode;
    public static $temp;
    public static $errors;
    public static $hCurrent;
    public static $rCurrent;
    public static $sysType;


    public function __construct ()
    {
        // Авторизация
        Auth::main();
        // Локализация
        Language::setLanguage();

        self::$mode = get_parent_class();

        // Временная папка
        self::$temp    = dirname(__FILE__) . '/../data';
        // Запись ошибок (если false, пустая строка, null или 0, запись не производится)
        self::$errors  = self::$temp . '/errors.dat';


        // Локаль
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        // Временная зона
        date_default_timezone_set('Europe/Moscow');


        // продолжать работу скрипта, даже если закрыли окно браузера
        //ignore_user_abort(1);
        // максимальное время работы скрипта
        @set_time_limit(1024);
        // максимальное время работы скрипта
        ini_set('max_execution_time', '1024');
        // кодировка по умолчанию для iconv
        iconv_set_encoding('internal_encoding', 'UTF-8');
        // лимит оперативной памяти
        ini_set('memory_limit', '256M');

        if (self::$mode == 'FTP') {
            parent::__construct(self::$ftp['user'], self::$ftp['pass'], self::$ftp['host'], self::$ftp['port']);
        } else {
            parent::__construct();
        }
    }
}


set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
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


ini_set('error_prepend_string', '<div class="red">');
ini_set('error_append_string', '</div><div class="rb"><br/></div>' . Config::$foot);
set_error_handler('Gmanager::error_handler');

?>
