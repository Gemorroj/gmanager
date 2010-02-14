<?php
// encoding = 'utf-8'
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.3 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


// E_ALL | E_STRICT
//error_reporting(E_ALL | E_STRICT); // Отключаем сообщения об ошибках

// Протокол, через который будет работать менеджер (ftp или http)
// настройки соединения FTP в файле ftp.php
require 'http.php';
//require 'ftp.php';

// Локализация
require 'lng/ru.php';


$GLOBALS['link']        = 50;                       // Сокращать имена файлов, если они длиннее чем указанное количество символов
$GLOBALS['auth']        = 0;                        // Авторизация (0 - выкл, 1 - вкл)
$GLOBALS['user_pass']   = '1234';                   // Пароль
$GLOBALS['user_name']   = 'Gemorroj';               // Логин
$GLOBALS['string']      = 1;                        // Поле ввода где можно в ручную набирать путь к папке/файлу (0 - выкл, 1 - вкл)
$GLOBALS['realname']    = 2;                        // 0 - Относительные пути к файлам/директориям, 1 - Полные пути к файлам/директориям, 2 - Скрывать относительные пути к файлам/директориям
$GLOBALS['syntax']      = 1;                        // 0 - проверка синтаксиса PHP кода у себя на сервере (если работает exec), 1 - проверка синтаксиса через специальный сервис на wapinet.ru
$GLOBALS['target']      = 0;                        // Открывать редактор в отдельном окне  (0 - выкл, 1 - вкл)
$GLOBALS['dir_size']    = 0;                        // Подсчет размеров директорий.  (0 - выкл, 1 - вкл)
$GLOBALS['del_notify']  = 1;                        // Подтверждения при удалении файлов/директорий

$GLOBALS['php']         = '/usr/local/bin/php';     // Путь к PHP
$GLOBALS['pclzip']      = 'pclzip.lib.php';         // Путь к PEAR классу PclZip
$GLOBALS['tar']         = 'Tar.php';                // Путь к PEAR классу Achive_TAR (в той же папке должен находиться PEAR.php)
$GLOBALS['wrap']        = 0;                        // Переносы строк в текстовом редакторе (0 - выкл, 1 - вкл)
$GLOBALS['rand']        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Набор символов для рандомного переименования файлов

// Максимальное количество файлов на странице по умолчанию
$GLOBALS['limit']       = 50;

// Отображаемые колонки (0 - выкл, 1 - вкл)
$GLOBALS['index'] = array(
    'name'      =>  1,
    'down'      =>  1,
    'type'      =>  1,
    'size'      =>  1,
    'change'    =>  1,
    'del'       =>  1,
    'chmod'     =>  1,
    'date'      =>  1,
    'uid'       =>  1,
    'n'         =>  1,
);

// Построчный редактор
$GLOBALS['line_editor'] = array(
    'on'        =>  0,  // 0 - выкл, 1 - вкл
    'min_lines' =>  10, // минимальное количество строк с которого запускается построчный редактор, а не обычный
    'lines'     =>  10, // количество строк в построчном редакторе
);


// Локаль
setlocale(LC_ALL, 'ru_RU.utf8');
// Формат даты
$GLOBALS['date_format'] = '%d.%m.%Y %H:%M';
// Временная зона
date_default_timezone_set('Europe/Moscow');

// Вторичная кодировка
$GLOBALS['altencoding'] = 'Windows-1251';
// Кодировка в консоли
$GLOBALS['consencoding'] = 'CP866';

//ignore_user_abort(1);                             // продолжать работу скрипта, даже если закрыли окно браузера
set_time_limit(999);                                // максимальное время работы скрипта
ini_set('max_execution_time', '999');               // максимальное время работы скрипта
iconv_set_encoding('internal_encoding', 'UTF-8');   // кодировка по умолчанию для iconv
ini_set('memory_limit', '128M');                    // лимит оперативной памяти

// Временная папка
//$GLOBALS['temp'] = ini_get('upload_tmp_dir');
//$GLOBALS['temp'] = is_writable($GLOBALS['temp']) ? $GLOBALS['temp'] : dirname(__FILE__) . '/data';
$GLOBALS['temp'] = dirname(__FILE__) . '/data';

// Верх
// %dir% - заменяется на имя текущей директории или файла
$GLOBALS['top'] = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title>%dir% - Gmanager 0.7.3 beta</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
<script type="text/javascript" src="js.js"></script>
</head>
<body>';

// Низ
$GLOBALS['foot'] = '<div class="w">Powered by Gemorroj<br/>
<a href="http://wapinet.ru">wapinet.ru</a></div>
</body></html>';


// Версия Менеджера (Не Менять!)
$GLOBALS['version'] = '0.7.3b';

?>
