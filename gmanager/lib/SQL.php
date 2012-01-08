<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2011 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class SQL
{
    const DB_MYSQL      = 'mysql';
    const DB_POSTGRESQL = 'postgresql';
    const DB_SQLITE     = 'sqlite';


    /**
     * factory
     * 
     * @param  bool $force
     * @return SQL_PDO_MySQL|SQL_MySQLi|SQL_MySQL|SQL_PDO_PostgreSQL|SQL_PostgreSQL|SQL_PDO_SQLite|null
     */
    public static function factory ($force = false)
    {
        switch (Registry::get('sqlDb')) {
            case self::DB_MYSQL:
                if ($force || extension_loaded('pdo_mysql')) {
                    return new SQL_PDO_MySQL;
                } else if ($force || extension_loaded('mysqli')) {
                    return new SQL_MySQLi;
                } else if ($force || extension_loaded('mysql')) {
                    return new SQL_MySQL;
                }
                break;


            case self::DB_POSTGRESQL:
                if ($force || extension_loaded('pdo_pgsql')) {
                    return new SQL_PDO_PostgreSQL;
                } else if ($force || extension_loaded('pgsql')) {
                    return new SQL_PostgreSQL;
                }
                break;


            case self::DB_SQLITE:
                if ($force || extension_loaded('pdo_sqlite')) {
                    return new SQL_PDO_SQLite;
                }
                break;
        }

        return null;
    }


    /**
     * SQL Parser
     * 
     * @param string $str
     * @return array
     */
    public static function parser ($str)
    {
        //TODO: supported '' or ""
        $queries  = array();
        $position = 0;
        $query    = '';

        for ($len = mb_strlen($str); $position < $len; ++$position) {
            $char  = $str[$position];

            switch ($char) {
                case '-':
                    if (mb_substr($str, $position, 3) != '-- ') {
                        $query .= $char;
                        break;
                    }


                case '#':
                    while ($char != "\r" && $char != "\n" && $position < $len - 1) {
                        $char = $str[++$position];
                    }
                    break;


                case '`':
                case "'":
                case '"':
                    $quote  = $char;
                    $query .= $quote;

                    while ($position < $len - 1) {
                        $char = $str[++$position];
                        if ($char == '\\') {
                            $query .= $char;
                            if ($position < $len - 1) {
                                $char   = $str[++$position];
                                $query .= $char;
                                if ($position < $len - 1) {
                                    $char = $str[++$position];
                                }
                            } else {
                                break;
                            }
                        }

                        if ($char == $quote) {
                            break;
                        }
                        $query .= $char;
                    }

                    $query .= $quote;
                    break;


                case ';':
                    $query = trim($query);
                    if ($query) {
                        $queries[] = $query;
                    }
                    $query = '';
                    break;


                default:
                    $query .= $char;
                    break;
            }
        }

        $query = trim($query);
        if ($query) {
            $queries[] = $query;
        }

        return $queries;
    }
}

?>
