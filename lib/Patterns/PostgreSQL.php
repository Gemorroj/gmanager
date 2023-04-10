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
class Patterns_PostgreSQL implements Patterns_Interface
{
    /**
     * pattern.
     *
     * @return array
     */
    public static function get()
    {
        return [
            'SELECT oid, * from pg_database' => 'SELECT oid, * from pg_database;',
            'SELECT * FROM information_schema.tables' => 'SELECT * FROM information_schema.tables;',
            'SELECT * FROM `table`' => 'SELECT * FROM ``;',
            'UPDATE `table` SET `` = \'\'' => 'UPDATE `` SET `` = \'\';',
            'INSERT INTO `table` () VALUES ()' => 'INSERT INTO `` () VALUES ();',
            'ALTER TABLE `table`' => 'ALTER TABLE ``;',
            'DROP DATABASE `db`' => 'DROP DATABASE ``;',
            'DROP TABLE `table`' => 'DROP TABLE ``;',
            'CREATE DATABASE `db`' => 'CREATE DATABASE `` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;',
            'CREATE TABLE `table`' => 'CREATE TABLE ``;',
            'TRUNCATE TABLE `table`' => 'TRUNCATE TABLE ``;',
            'DELETE FROM `table`' => 'DELETE FROM ``;',
        ];
    }
}
