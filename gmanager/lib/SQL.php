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


class SQL
{
    /**
     * main
     * 
     * @param  object $data
     * @return mixed (object or bool)
     */
    public static function main (Gmanager $data)
    {
        switch (Config::$sqlDriver) {
            case 'mysql':
                if (extension_loaded('pdo_mysql')) {
                    return new SQL_PDO_MySQL($data);
                } else if (extension_loaded('mysqli')) {
                    return new SQL_MySQLi($data);
                } else if (extension_loaded('mysql')) {
                    return new SQL_MySQL($data);
                }
                break;


            case 'postgresql':
                if (extension_loaded('pdo_pgsql')) {
                    return new SQL_PDO_PostgreSQL($data);
                } else if (extension_loaded('pgsql')) {
                    return new SQL_PostgreSQL($data);
                }
                break;


            case 'sqlite':
                if (extension_loaded('pdo_sqlite')) {
                    return new SQL_PDO_SQLite($data);
                }
                break;
        }

        return false;
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

        for ($strlen = iconv_strlen($str); $position < $strlen; ++$position) {
            $char  = $str[$position];

            switch ($char) {
                case '-':
                    if (substr($str, $position, 3) != '-- ') {
                        $query .= $char;
                        break;
                    }


                case '#':
                    while ($char != "\r" && $char != "\n" && $position < $strlen - 1) {
                        $char = $str[++$position];
                    }
                    break;


                case '`':
                case "'":
                case '"':
                    $quote  = $char;
                    $query .= $quote;

                    while ($position < $strlen - 1) {
                        $char = $str[++$position];
                        if ($char == '\\') {
                            $query .= $char;
                            if ($position < $strlen - 1) {
                                $char   = $str[++$position];
                                $query .= $char;
                                if ($position < $strlen - 1) {
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
