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


class SQL_MySQL
{
    private $_resource;
    private $_Gmanager;


    /**
     * Constructor
     * 
     * @param object $data
     */
    public function __construct (Gmanager $data)
    {
        $this->_Gmanager = $data;
    }


    /**
     * MySQL connector
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @return resource or string
     */
    private function _connect ($host = 'localhost', $name = 'root', $pass = '', $db = '', $charset = 'utf8')
    {
        if (!$this->_resource = mysql_connect($host, $name, $pass)) {
            return $this->_Gmanager->report(Language::get('mysql_connect_false'), 1);
        }
        if ($charset) {
            mysql_unbuffered_query('SET NAMES `' . mysql_real_escape_string($charset, $this->_resource) . '`', $this->_resource);
        }

        if ($db) {
            if (!mysql_select_db($db, $this->_resource)) {
                return $this->_Gmanager->report(Language::get('mysql_select_db_false'), 1);
            }
        }

        return $this->_resource;
    }


    /**
     * SQL Parser
     * 
     * @param string $str
     * @return array
     */
    private function _parser ($str)
    {
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


    /**
     * Installer
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $sql
     * @return string
     */
    public function installer ($host = '', $name = '', $pass = '', $db = '', $charset = '', $sql = '')
    {
        if (!$sql || !$query = $this->_parser($sql)) {
            return '';
        }

        $out = '<?php' . "\n"
             . '// MySQL Installer' . "\n"
             . '// Created in Gmanager ' . Config::$version . "\n"
             . '// http://wapinet.ru/gmanager/' . "\n\n"

             . 'error_reporting(0);' . "\n\n"

             . 'if (strpos($_SERVER[\'HTTP_USER_AGENT\'], \'MSIE\') !== false) {' . "\n"
             . '    header(\'Content-type: text/html; charset=UTF-8\');' . "\n"
             . '} else {' . "\n"
             . '    header(\'Content-type: application/xhtml+xml; charset=UTF-8\');' . "\n"
             . '}' . "\n\n"

             . 'echo \'<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n"
             . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">' . "\n"
             . '<head>' . "\n"
             . '<title>SQL Installer</title>' . "\n"
             . '<style type="text/css">' . "\n"
             . 'body {' . "\n"
             . '    background-color: #cccccc;' . "\n"
             . '    color: #000000;' . "\n"
             . '}' . "\n"
             . '</style>' . "\n"
             . '</head>' . "\n"
             . '<body>' . "\n"
             . '<div>\';' . "\n\n\n"


             . 'if (!$_POST) {' . "\n"
             . '    echo \'<form action="\' . $_SERVER[\'PHP_SELF\'] . \'" method="post">' . "\n"
             . '    <div>' . "\n"
             . '    ' . Language::get('mysql_user') . '<br/>' . "\n"
             . '    <input type="text" name="name" value="' . htmlspecialchars($name) . '"/><br/>' . "\n"
             . '    ' . Language::get('mysql_pass') . '<br/>' . "\n"
             . '    <input type="text" name="pass" value="' . htmlspecialchars($pass) . '"/><br/>' . "\n"
             . '    ' . Language::get('mysql_host') . '<br/>' . "\n"
             . '    <input type="text" name="host" value="' . htmlspecialchars($host) . '"/><br/>' . "\n"
             . '    ' . Language::get('mysql_db') . '<br/>' . "\n"
             . '    <input type="text" name="db" value="' . htmlspecialchars($db) . '"/><br/>' . "\n"
             . '    <input type="submit" value="' . Language::get('install') . '"/>' . "\n"
             . '    </div>' . "\n"
             . '    </form>' . "\n"
             . '    </div></body></html>\';' . "\n"
             . '    exit;' . "\n"
             . '}' . "\n\n"

             . '$connect = mysql_connect($_POST[\'host\'], $_POST[\'name\'], $_POST[\'pass\']) or die (\'Can not connect to MySQL</div></body></html>\');' . "\n"
             . 'mysql_select_db($_POST[\'db\'], $connect) or die (\'Error select the database</div></body></html>\');' . "\n"
             . 'mysql_query(\'SET NAMES `' . str_ireplace('utf-8', 'utf8', $charset) . '`\', $connect);' . "\n\n";

        foreach ($query as $q) {
            $out .= '$sql = "' . str_replace('"', '\"', trim($q)) . ';";' . "\n"
                  . 'mysql_query($sql, $connect);' . "\n"
                  . 'if ($err = mysql_error($connect)) {' . "\n"
                  . '    $error[] = $err."\n SQL:\n".$sql;' . "\n"
                  . '}' . "\n\n";
        }

        $out .= 'if ($error) {' . "\n"
              . '    echo \'Error:<pre>\' . htmlspecialchars(print_r($error, true), ENT_NOQUOTES) . \'</pre>\';' . "\n"
              . '} else {' . "\n"
              . '    echo \'Ok\';' . "\n"
              . '}' . "\n\n"
    
              . 'echo \'</div></body></html>\'' . "\n"
              . '?>';

        return $out;
    }


    /**
     * Backup
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $data
     * @param array  $tables
     * @return mixed
     */
    function backup ($host = '', $name = '', $pass = '', $db = '', $charset = '', $data = '', $tables = array())
    {
        $connect = $this->_connect($host, $name, $pass, $db, $charset);
        if (is_resource($connect)) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $true = $false = '';
        if ($tables) {
            if ($tables['tables']) {
                foreach ($tables['tables'] as $f) {
                    $q = mysql_query('SHOW CREATE TABLE `' . str_replace('`', '``', $f) . '`;', $this->_resource);
                    if ($q) {
                        $true .= mysql_result($q, 0, 1) . ";\n\n";
                    } else {
                        $false .= mysql_error($this->_resource) . "\n";
                    }
                }
            }
            if ($tables['data']) {
                foreach ($tables['data'] as $f) {
                    $q = mysql_query('SELECT * FROM `' . str_replace('`', '``', $f) . '`;', $this->_resource);
                    if ($q) {
                        if (mysql_num_rows($q)) {
                            $true .= 'INSERT INTO `' . str_replace('`', '``', $f) . '` VALUES';
                            while ($row = mysql_fetch_row($q)) {
                                $true .= "\n(";
                                foreach ($row as $v) {
                                    $true .= $v === null ? 'NULL,' : "'" . mysql_real_escape_string($v, $this->_resource) . "',";
                                }
                                $true = rtrim($true, ',') . '),';
                            }
                            $true = rtrim($true, ',') . ";\n\n";
                        }
                    } else {
                        $false .= mysql_error($this->_resource) . "\n";
                    }
                }
            }

            if ($true) {
                $this->_Gmanager->mkdir(dirname($tables['file']));
                if (!$this->_Gmanager->file_put_contents($tables['file'], $true)) {
                    $false .= $this->_Gmanager->error() . "\n";
                }
            }

            if ($false) {
                return $this->_Gmanager->report(Language::get('mysql_backup_false') . '<pre>' . trim($false) . '</pre>', 1);
            } else {
                return $this->_Gmanager->report(Language::get('mysql_backup_true'), 0);
            }
        } else {
            $q = mysql_query('SHOW TABLES;', $this->_resource);
            if ($q) {
                while($row = mysql_fetch_row($q)) {
                    $true .= '<option value="' . rawurlencode($row[0]) . '">' . htmlspecialchars($row[0], ENT_NOQUOTES) . '</option>';
                }
                return $true;
            }
        }

        return false;
    }


    /**
     * Query
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $data
     * @return string
     */
    function query ($host = '', $name = '', $pass = '', $db = '', $charset = '', $data = '')
    {
        $connect = $this->_connect($host, $name, $pass, $db, $charset);
        if (is_resource($connect)) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $i = $time = $rows = 0;
        $out = null;
        foreach ($this->_parser($data) as $q) {
            $result = array();
            $str = '';
            $q = rtrim($q, ';');

            $start = microtime(true);
            $r = mysql_query($q . ';', $this->_resource);
            $time += microtime(true) - $start;

            if (!$r) {
                return $this->_Gmanager->report(Language::get('mysql_query_false'), 2) . '<div><code>' . mysql_error($this->_resource) . '</code></div>';
            } else {
                if (is_resource($r) && $row = mysql_num_rows($r)) {
                    $rows += $row;
                    while ($row = mysql_fetch_assoc($r)) {
                        $result[] = $row;
                    }
                } else if ($r === true) {
                    $rows += mysql_affected_rows($this->_resource);
                }
            }
            $i++;

            if ($result) {
                $str .= '<tr><th> ' . implode(' </th><th> ', array_map('htmlspecialchars', array_keys($result[0]))) . ' </th></tr>';

                foreach ($result as $v) {
                    $str .= '<tr class="border">';
                    foreach ($v as $value) {
                        $str .= $value === null ? '<td><pre style="margin:0;">NULL</pre></td>' : '<td><pre style="margin:0;"><a href="#sql" onclick="paste(\'' . rawurlencode($value) . '\');">' . htmlspecialchars($value, ENT_NOQUOTES) . '</a></pre></td>';
                    }
                    $str .= '</tr>';
                }

                $out .= '<table class="telo">' . $str . '</table>';
            }
        }

        mysql_close($this->_resource);
        return $this->_Gmanager->report(Language::get('mysql_true') . $i . '<br/>' . Language::get('mysql_rows') . $rows . '<br/>' . str_replace('%time%', round($time, 6), Language::get('microtime')), 0) . $out;
    }
}

?>
