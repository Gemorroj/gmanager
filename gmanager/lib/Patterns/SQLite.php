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


class Patterns_SQLite
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

?>
