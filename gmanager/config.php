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


// Протокол, через который будет работать менеджер FTP или HTTP (в верхнем регистре)
$GLOBALS['mode']            = 'HTTP';

$GLOBALS['FTP']['user']     = 'root';               // Пользователь FTP
$GLOBALS['FTP']['pass']     = '';                   // Пароль FTP
$GLOBALS['FTP']['host']     = 'localhost';          // Хост FTP
$GLOBALS['FTP']['port']     = 21;                   // Порт FTP

$GLOBALS['lng']             = 'ru';                 // Локализация (en, ua, ru)

$GLOBALS['link']            = 50;                   // Сокращать имена файлов, если они длиннее чем указанное количество символов
$GLOBALS['auth']            = 0;                    // Авторизация (0 - выкл, 1 - вкл)
$GLOBALS['user_pass']       = '1234';               // Пароль
$GLOBALS['user_name']       = 'Gemorroj';           // Логин
$GLOBALS['string']          = 1;                    // Поле ввода где можно в ручную набирать путь к папке/файлу (0 - выкл, 1 - вкл)
$GLOBALS['realname']        = 2;                    // 0 - Относительные пути к файлам/директориям, 1 - Полные пути к файлам/директориям, 2 - Скрывать относительные пути к файлам/директориям
$GLOBALS['syntax']          = 1;                    // 0 - проверка синтаксиса PHP кода у себя на сервере (если работает exec), 1 - проверка синтаксиса через специальный сервис на wapinet.ru
$GLOBALS['target']          = 0;                    // Открывать редактор в отдельном окне  (0 - выкл, 1 - вкл)
$GLOBALS['dir_size']        = 0;                    // Подсчет размеров директорий.  (0 - выкл, 1 - вкл)
$GLOBALS['del_notify']      = 1;                    // Подтверждения при удалении файлов/директорий
$GLOBALS['wrap']            = 0;                    // Переносы строк в текстовом редакторе (0 - выкл, 1 - вкл)
$GLOBALS['limit']           = 50;                   // Максимальное количество файлов на странице по умолчанию
$GLOBALS['php']             = '/usr/local/bin/php'; // Путь к PHP


// Набор символов для рандомного переименования файлов
$GLOBALS['rand']            = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';


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
setlocale(LC_ALL, 'ru_RU.UTF-8');
// Временная зона
date_default_timezone_set('Europe/Moscow');
// Формат даты
$GLOBALS['date_format']     = '%d.%m.%Y %H:%M';
// Вторичная кодировка
$GLOBALS['altencoding']     = 'Windows-1251';
// Кодировка в консоли
$GLOBALS['consencoding']    = 'CP866';

//ignore_user_abort(1);                           // продолжать работу скрипта, даже если закрыли окно браузера
@set_time_limit(1024);                            // максимальное время работы скрипта
ini_set('max_execution_time', '1024');            // максимальное время работы скрипта
iconv_set_encoding('internal_encoding', 'UTF-8'); // кодировка по умолчанию для iconv
ini_set('memory_limit', '256M');                  // лимит оперативной памяти

// Временная папка
//$GLOBALS['temp'] = ini_get('upload_tmp_dir');
//$GLOBALS['temp'] = is_writable($GLOBALS['temp']) ? $GLOBALS['temp'] : dirname(__FILE__) . '/data';
$GLOBALS['temp']    = dirname(__FILE__) . '/data';
$GLOBALS['errors']  = $GLOBALS['temp'] . '/errors.dat'; // Запись ошибок (если false, пустая строка, null или 0, запись не производится)


// Верх
// %dir% - заменяется на имя текущей директории или файла
$GLOBALS['top'] = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title>%dir% - Gmanager 0.7.4 beta</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
<script type="text/javascript" src="js.js"></script>
</head>
<body>';

// Низ
$GLOBALS['foot'] = '<div class="w">Powered by Gemorroj<br/><a href="http://wapinet.ru/gmanager/">wapinet.ru</a></div></body></html>';


// Версия Менеджера (Не Менять!)
$GLOBALS['version'] = '0.7.4b';


if ($GLOBALS['auth']) {
    // CGI fix
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_params = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        $_SERVER['PHP_AUTH_USER'] = $auth_params[0];
        unset($auth_params[0]);
        $_SERVER['PHP_AUTH_PW'] = implode('', $auth_params);
        unset($auth_params);
    }
    // CGI fix

    if (@$_SERVER['PHP_AUTH_USER'] != $GLOBALS['user_name'] || @$_SERVER['PHP_AUTH_PW'] != $GLOBALS['user_pass']) {
        header('WWW-Authenticate: Basic realm="Authentification"');
        header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
        header('Content-type: text/html; charset=UTF-8');
        exit('<html><head><title>Error</title></head><body><p style="color:red;font-size:24pt;text-align:center">Unauthorized</p></body></html>');
    }
}


set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib');
function __autoload ($class)
{
    require dirname(__FILE__) . '/lib/' . str_replace('_', '/', $class) . '.php';
}

if ($GLOBALS['mode'] == 'HTTP') {
    class Main extends HTTP{}
} else {
    class Main extends FTP
    {
        public function __construct ()
        {
            parent::__construct($GLOBALS['FTP']['user'], $GLOBALS['FTP']['pass'], $GLOBALS['FTP']['host'], $GLOBALS['FTP']['port']);
        }
    }
}


require dirname(__FILE__) . '/lng/' . $GLOBALS['lng'] . '.php';
$ms = microtime(true);
$Gmanager = new Gmanager;

ini_set('error_prepend_string', '<div class="red">');
ini_set('error_append_string', '</div><div class="rb"><br/></div>' . $GLOBALS['foot']);
set_error_handler(array($Gmanager, 'error_handler'));

?>
