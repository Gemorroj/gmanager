<?php
// кодировка UTF-8
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7 alpha
 * 
 * PHP version >= 5.2.1
 * 
 */

// 6143
error_reporting(0); // Отключаем сообщения об ошибках


// Выбираем протокол, через который будет работать менеджер (ftp или http)
// настройки соединения в файле ftp.php
// !!! в текущей версии поддержка FTP реализована еще не полностью !!!
require 'http.php';
//require 'ftp.php';

$link = 50;                     // Сокращать имена файлов, если они длиннее чем указанное количество символов
$auth = 0;                      // Авторизация (0 - выкл, 1 - вкл)
$user_pass = '1234';            // Пароль
$user_name = 'Gemorroj';        // Логин
$string = 1;                    // Поле ввода где можно в ручную набирать путь к папке/файлу (0 - выкл, 1 - вкл)
$realname = 2;                  // 0 - Относительные пути к файлам/директориям, 1 - Полные пути к файлам/директориям, 2 - Скрывать относительные пути к файлам/директориям
$syntax = 1;                    // 0 - проверка синтаксиса PHP кода у себя на сервере (если работает exec), 1 - проверка синтаксиса через специальный сервис на wapinet.ru
$target = 0;                    // Открывать редактор в отдельном окне  (0 - выкл, 1 - вкл)
$dir_size = 0;                  // Подсчет размеров директорий.  (0 - выкл, 1 - вкл)
$del_notify = 1;				// Подтверждения при удалении файлов/директорий

$php = '/usr/local/bin/php';    // Путь к PHP
$pclzip = 'pclzip.lib.php';		// Путь к PEAR классу PclZip
$tar = 'Tar.php';               // Путь к PEAR классу Achive_TAR (в той же папке должен находиться PEAR.php)

// Максимальное количество файлов на странице по умолчанию
$limit = 50;


setlocale(LC_ALL, 'ru_RU.utf8'); // Локаль
$date_format = '%d.%m.%Y %H:%M';// Формат даты
putenv('TZ=Europe/Moscow');     // Меняем время

// Можете не менять
//ignore_user_abort(1); // продолжать работу скрипта, даже если закрыли окно браузера
ini_set('memory_limit','128M'); // лимит оперативной памяти
set_time_limit(999); // максимальное время работы скрипта
ini_set('max_execution_time',999); // максимальное время работы скрипта
iconv_set_encoding('internal_encoding', 'UTF-8'); // кодировка по умолчанию для iconv

// Верх
// %dir% - заменяется на имя текущей директории или файла
$top = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title>%dir% - Gmanager 0.7 alpha</title>
<link rel="stylesheet" type="text/css" href="style.css"/>
<script type="text/javascript" src="js.js"></script>
</head>
<body>';

// Низ
$foot = '<div class="w">Powered by Gemorroj<br/>
<a href="http://wapinet.ru">wapinet.ru</a></div>
</body></html>';


// Язык
$lng = array(
'title_index'			=>	'Содержимое Папки',
'title_edit'			=>	'Редактор',
'title_change'			=>	'Дополнительно',
'check'					=>	'Все',
'n'						=>	'N',
'name'					=>	'Имя',
'type'					=>	'Тип',
'size'					=>	'Размер',
'change'				=>	'Изменение',
'rename'				=>	'Переименование',
'meter'					=>	'Счетчик',
'edit'					=>	'Редактировать',
'del'					=>	'Удаление',
'chmod'					=>	'CHMOD',
'date'					=>	'Дата',
'ch_index'				=>	'*',
'create'				=>	'Создать',
'upload'				=>	'Загрузить Файл',
'up'					=>	'Загрузить',
'url'					=>	'URL',
'headers'				=>	'Заголовки',
'scan'					=>	'Сканер',
'pattern'				=>	'Шаблон',
'enter'					=>	'Обзор',
'look'					=>	'Смотреть',
'go'					=>	'Перейти',
'ch'					=>	'Изменить',
'dl'					=>	'Удалить',
'cr'					=>	'Создать',
'save'					=>	'Сохранить',
'send_mail'				=>	'Отправить E-mail',
'mail_to'				=>	'Кому',
'mail_from'				=>	'От Кого',
'mail_theme'			=>	'Тема',
'mail_mess'				=>	'Сообщение',
'sz'					=>	'Размер',
'mod'					=>	'Дополнительно',
'phpinfo'				=>	'PHPINFO',
'eval'					=>	'EVAL',
'eval_go'				=>	'Выполнить',
'php_code'				=>	'PHP Код',
'result'				=>	'Результат',
'get'					=>	'Скачать',
'new_version'			=>	'Проверка Обновления',
'version_new'			=>	'Новая Версия',
'version_old'			=>	'Текущая Версия',
'new_version_true'		=>	'Доступна Новая Версия',
'new_version_false'		=>	'У Вас Последняя Версия Менеджера',
'not_connect'			=>	'Не Удалось Соединиться с Сервером',
'change_name'			=>	'Имя',
'change_chmod'			=>	'CHMOD',
'change_del'			=>	'Удалить Исходный Файл/Директорию',
'change_func'			=>	'Переместить/Переименовать',
'change_func2'			=>	'Переместить/Скопировать',
'syntax'				=>	'PHP Валидатор',
'validator'				=>	'XML Валидатор',
'charset'				=>	'Кодировка',
'charset_no'			=>	'Оригинал',
'warning'				=>	'Такой Файл Уже Существует<br/>Вся Информация в Нем Будет Уничтожена',
'back'					=>	'Назад',
'file'					=>	'Файл',
'dir'					=>	'Каталог',
'dir_empty'				=>	'Директория Пуста',
'not_found'				=>	'Файл не Найден',
'copy_file_true'		=>	'Файл %file% Скопирован',
'copy_file_false'		=>	'Файл %file% не Скопирован',
'move_file_true'		=>	'Файл %file% Перемещен',
'move_file_false'		=>	'Файл %file% не Перемещен',
'del_file_true'			=>	'Файл Удален',
'del_file_false'		=>	'Файл не Удален',
'full_del_file_dir_true'	=>	'Выбранные Файлы/Папки Удалены',
'full_del_file_dir_false'	=>	'Следующие Файлы/Папки не Удалены',
'full_rechmod'			=>	'Права Изменены',
'create_dir_true'		=>	'Каталог Создан',
'create_dir_false'		=>	'Каталог не Создан',
'fputs_file_true'		=>	'Данные Записаны',
'fputs_file_false'		=>	'Данные не Записаны',
'chmod_true'			=>	'Права Изменены',
'chmod_false'			=>	'Права не Изменены',
'chmod_mode_false'		=>	'Права Заданы не Верно',
'full_rename'			=>	'Операция Выполнена',
'copy_files_true'		=>	'Директория %dir% Скопирована',
'move_files_true'		=>	'Директория %dir% Перемещена',
'del_dir_true'			=>	'Каталог Удален',
'del_dir_false'			=>	'Следующие Файлы/Папки не Удалось Удалить',
'syntax_true'			=>	'Синтаксических Ошибок не Найдено',
'syntax_not_check'		=>	'Файл не Проверен',
'syntax_unknown'		=>	'Unknown',
'validator_true'		=>	'Синтаксических Ошибок не Найдено',
'validator_not_check'	=>	'Файл не Проверен',
'add_archive'			=>	'Добавить в Архив',
'add_archive_dir'		=>	'Добавить в Папку',
'add_archive_true'		=>	'Файлы/Папки Добавлены в Архив',
'add_archive_false'		=>	'Файлы/Папки в Архив не Добавлены',
'archive_size'			=>	'Размер в Архиве',
'real_size'				=>	'Реальный Размер',
'archive_date'			=>	'Добавлено в Архив',
'extract_archive'		=>	'Распаковать Архив',
'extract_file'			=>	'Распаковать Файлы/Папки',
'extract_file_true'		=>	'Файлы/Папки Распакованы',
'extract_file_false'	=>	'Файлы/Папки не Распакованы',
'extract_true'			=>	'Архив Распакован',
'extract_false'			=>	'Архив не Распакован',
'archive_error'			=>	'Невозможно Открыть Архив',
'create_archive'		=>	'Создать ZIP Архив',
'create_archive_true'	=>	'ZIP Архив Создан',
'create_archive_false'	=>	'ZIP Архив не Создан',
'upload_true'			=>	'Файл Загружен',
'upload_false'			=>	'Файл не Загружен',
'send_mail_true'		=>	'Сообщение Отправлено',
'send_mail_false'		=>	'Сообщение не Отправлено',
'replace'				=>	'Заменить',
'replace_from'			=>	'Заменить',
'replace_to'			=>	'На',
'replace_true'			=>	'Операция Успешно Выполнена<br/>Число Замен ',
'replace_false_file'	=>	'Не Удалось Записать Измененные Данные',
'replace_false_str'		=>	'Не Найдено Ни Одного Соответствия Заданному Шаблону',
'regexp'				=>	'Регулярное Выражение',
'regexp_error'			=>	'Ошибка в Синтаксисе Регулярного Выражения',
'search'				=>	'Поиск',
'what_search'			=>	'Что Ищем',
'where_search'			=>	'Где Ищем',
'in_files'				=>	'в файлах',
'in_text'				=>	'в тексте',
'register'				=>	'Учитывать Регистр',
'str_register'			=>	'Регистр',
'str_register_no'		=>	'Не Изменять',
'str_register_low'		=>	'строчные',
'str_register_up'		=>	'ЗАГЛАВНЫЕ',
'yes'					=>	'да',
'no'					=>	'нет',
'tables'				=>	'Залить Таблицы',
'tables_file'			=>	'Файл с Таблицами',
'sql'					=>	'SQL',
'sql_query'				=>	'SQL Запрос',
'mysql_user'			=>	'Пользователь',
'mysql_pass'			=>	'Пароль',
'mysql_host'			=>	'Хост',
'mysql_db'				=>	'База',
'mysq_connect_false'	=>	'Не Удалось Соедениться с MySQL',
'mysq_select_db_false'	=>	'Не Удалось Соедениться с Базой',
'mysq_query_false'		=>	'Ошибка При Выполнении Операций с Базой',
'mysql_true'			=>	'Выполнено Запросов: ',
'microtime'				=>	'Операция Заняла: %time% сек.',
'create_sql_installer'	=>	'Создать Инсталлятор',
'save_as'				=>	'Сохранить как',
'sql_parser_error'		=>	'Ошибка SQL парсера',
'install'				=>	'Установить',
'unknown'				=>	'Неизвестно',
'disable_function'		=>	'PHP Модуль не Установлен или Функция Заблокирована',
'limit'					=>	'файлов на страницу',
'of files'				=>	'на файлы',
'of folders'			=>	'на папки',
'md5'					=>	'MD5',
'look'					=>	'Смотреть',
'del_notify'			=>	'Действительно удалить?',
);

// Версия Менеджера (Не Менять!)
$version = '0.7';
?>