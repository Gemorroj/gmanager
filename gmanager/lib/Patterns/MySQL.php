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


class Patterns_MySQL implements Patterns_Interface
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'SHOW DATABASES'                    => 'SHOW DATABASES;',
            'SHOW TABLES'                       => 'SHOW TABLES;',
            'USE `database`'                    => 'USE ``;',
            'SELECT * FROM `table`'             => 'SELECT * FROM ``;',
            'UPDATE `table` SET `` = \'\''      => 'UPDATE `` SET `` = \'\';',
            'INSERT INTO `table` () VALUES ()'  => 'INSERT INTO `` () VALUES ();',
            'ALTER TABLE `table`'               => 'ALTER TABLE ``;',
            'DROP DATABASE `db`'                => 'DROP DATABASE ``;',
            'DROP TABLE `table`'                => 'DROP TABLE ``;',
            'CREATE DATABASE `db`'              => 'CREATE DATABASE `` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;',
            'CREATE TABLE `table`'              => 'CREATE TABLE ``;',
            'TRUNCATE TABLE `table`'            => 'TRUNCATE TABLE ``;',
            'DELETE FROM `table`'               => 'DELETE FROM ``;',
            'SHOW FIELDS FROM `table`'          => 'SHOW FIELDS FROM ``;',
            'SHOW CREATE TABLE `table`'         => 'SHOW CREATE TABLE ``;',
        );
    }
}
