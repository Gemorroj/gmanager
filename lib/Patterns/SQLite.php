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


class Patterns_SQLite implements Patterns_Interface
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'SELECT name FROM sqlite_master WHERE type = "table" ORDER BY name' => 'SELECT name FROM sqlite_master WHERE type = "table" ORDER BY name;',
            'SELECT * FROM table'                                               => 'SELECT * FROM ;',
            'UPDATE table SET  = \'\''                                          => 'UPDATE  SET = \'\';',
            'INSERT INTO table () VALUES ()'                                    => 'INSERT INTO  () VALUES ();',
            'ALTER TABLE table'                                                 => 'ALTER TABLE ;',
            'DROP TABLE table'                                                  => 'DROP TABLE ;',
            'CREATE TABLE table'                                                => 'CREATE TABLE  (' . "\n" . ');',
            'DELETE FROM table'                                                 => 'DELETE FROM ;',
        );
    }
}
