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


class SQL
{
    /**
     * Database
     *
     * @const string
     */
    const DB_MYSQL      = 'mysql';
    const DB_POSTGRESQL = 'postgresql';
    const DB_SQLITE     = 'sqlite';


    /**
     * @var string
     */
    private $_db;
    /**
     * @var bool
     */
    private $_force = false;


    /**
     * setDb
     *
     * @param string $db
     * @return SQL
     */
    public function setDb ($db)
    {
        $this->_db = $db;
        return $this;
    }


    /**
     * setForce
     *
     * @param bool $force
     * @return SQL
     */
    public function setForce ($force = false)
    {
        $this->_force = $force;
        return $this;
    }


    /**
     * factory
     *
     * @return SQL_PDO_MySQL|SQL_MySQLi|SQL_MySQL|SQL_PDO_PostgreSQL|SQL_PostgreSQL|SQL_PDO_SQLite|null
     */
    public function factory ()
    {
        switch ($this->_db) {
            case self::DB_MYSQL:
                if ($this->_force || extension_loaded('pdo_mysql')) {
                    return new SQL_PDO_MySQL;
                } elseif ($this->_force || extension_loaded('mysqli')) {
                    return new SQL_MySQLi;
                } elseif ($this->_force || extension_loaded('mysql')) {
                    return new SQL_MySQL;
                }
                break;


            case self::DB_POSTGRESQL:
                if ($this->_force || extension_loaded('pdo_pgsql')) {
                    return new SQL_PDO_PostgreSQL;
                } elseif ($this->_force || extension_loaded('pgsql')) {
                    return new SQL_PostgreSQL;
                }
                break;


            case self::DB_SQLITE:
                if ($this->_force || extension_loaded('pdo_sqlite')) {
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
