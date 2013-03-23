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


class SQL_MySQL implements SQL_Interface
{
    /**
     * @var resource
     */
    private $_resource;


    /**
     * MySQL connector
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @return resource|string
     */
    private function _connect ($host = 'localhost', $name = 'root', $pass = '', $db = '', $charset = 'utf8')
    {
        if (!$this->_resource = mysql_connect($host, $name, $pass)) {
            return Helper_View::message(Language::get('sql_connect_false'), Helper_View::MESSAGE_ERROR);
        }
        if ($charset) {
            mysql_set_charset($charset, $this->_resource);
        }

        if ($db) {
            if (!mysql_select_db($db, $this->_resource)) {
                return Helper_View::message(Language::get('sql_select_db_false'), Helper_View::MESSAGE_ERROR);
            }
        }

        return $this->_resource;
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
    public function installer ($host = null, $name = null, $pass = null, $db = '', $charset = null, $sql = '')
    {
        if (!$sql || !($query = SQL::parser($sql))) {
            return '';
        }

        $out = '<?php' . "\n"
             . '// MySQL Installer' . "\n"
             . '// Created in Gmanager ' . Config::getVersion() . "\n"
             . '// http://wapinet.ru/gmanager/' . "\n\n"

             . 'error_reporting(0);' . "\n\n"

            . 'if (isset($_SERVER[\'HTTP_ACCEPT\']) && stripos($_SERVER[\'HTTP_ACCEPT\'], \'application/xhtml+xml\') !== false) {' . "\n"
            . '    header(\'Content-type: text/xhtml+xml; charset=UTF-8\');' . "\n"
            . '} else {' . "\n"
            . '    header(\'Content-type: application/html; charset=UTF-8\');' . "\n"
            . '}' . "\n\n"

             . 'echo \'<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n"
             . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">' . "\n"
             . '<head>' . "\n"
             . '<title>MySQL Installer</title>' . "\n"
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
             . '    ' . Language::get('sql_user') . '<br/>' . "\n"
             . '    <input type="text" name="name" value="' . htmlspecialchars($name) . '"/><br/>' . "\n"
             . '    ' . Language::get('sql_pass') . '<br/>' . "\n"
             . '    <input type="text" name="pass" value="' . htmlspecialchars($pass) . '"/><br/>' . "\n"
             . '    ' . Language::get('sql_host') . '<br/>' . "\n"
             . '    <input type="text" name="host" value="' . htmlspecialchars($host) . '"/><br/>' . "\n"
             . '    ' . Language::get('sql_db') . '<br/>' . "\n"
             . '    <input type="text" name="db" value="' . htmlspecialchars($db) . '"/><br/>' . "\n"
             . '    <input type="submit" value="' . Language::get('install') . '"/>' . "\n"
             . '    </div>' . "\n"
             . '    </form>' . "\n"
             . '    </div></body></html>\';' . "\n"
             . '    exit;' . "\n"
             . '}' . "\n\n"

             . '$connect = mysql_connect($_POST[\'host\'], $_POST[\'name\'], $_POST[\'pass\']) or die (\'Can not connect to MySQL</div></body></html>\');' . "\n"
             . 'mysql_select_db($_POST[\'db\'], $connect) or die (\'Error select the database</div></body></html>\');' . "\n"
             . 'mysql_set_charset(\'' . $charset . '\', $connect);' . "\n\n";

        foreach ($query as $q) {
            $out .= '$sql = "' . str_replace('"', '\"', trim($q)) . ';";' . "\n"
                  . 'mysql_query($sql, $connect);' . "\n"
                  . 'if ($err = mysql_error($connect)) {' . "\n"
                  . '    $error[] = $err . "\n SQL:\n" . $sql;' . "\n"
                  . '}' . "\n\n";
        }

        $out .= 'mysql_close();' . "\n\n"
              . 'if ($error) {' . "\n"
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
     * @param array  $tables
     * @return mixed
     */
    public function backup ($host = null, $name = null, $pass = null, $db = '', $charset = null, $tables = array())
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
                                    $true .= $v === null ? 'NULL,' : "'" . str_replace("'", "''", $v) . "',";
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
                $dir = dirname($tables['file']);
                if (!Gmanager::getInstance()->is_dir($dir)) {
                    Gmanager::getInstance()->mkdir($dir);
                }
                if (!Gmanager::getInstance()->file_put_contents($tables['file'], $true)) {
                    $false .= Errors::get() . "\n";
                }
            }

            if ($false) {
                return Helper_View::message(Language::get('sql_backup_false') . '<pre>' . trim($false) . '</pre>', Helper_View::MESSAGE_ERROR);
            } else {
                return Helper_View::message(Language::get('sql_backup_true'), Helper_View::MESSAGE_SUCCESS);
            }
        } else {
            $q = mysql_query('SHOW TABLES;', $this->_resource);
            if ($q) {
                while ($row = mysql_fetch_row($q)) {
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
    public function query ($host = null, $name = null, $pass = null, $db = '', $charset = null, $data = '')
    {
        $connect = $this->_connect($host, $name, $pass, $db, $charset);
        if (is_resource($connect)) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $i = $time = $rows = 0;
        $out = null;
        foreach (SQL::parser($data) as $q) {
            $result = array();
            $str = '';
            $q = rtrim($q, ';');

            $start = microtime(true);
            $r = mysql_query($q . ';', $this->_resource);
            $time += microtime(true) - $start;

            if (!$r) {
                return Helper_View::message(Language::get('sql_query_false'), Helper_View::MESSAGE_ERROR_EMAIL) . '<div><code>' . mysql_error($this->_resource) . '</code></div>';
            } else {
                if (is_resource($r) && $row = mysql_num_rows($r)) {
                    $rows += $row;
                    while ($row = mysql_fetch_assoc($r)) {
                        $result[] = $row;
                    }
                } elseif ($r === true) {
                    $rows += mysql_affected_rows($this->_resource);
                }
            }
            $i++;

            if ($result) {
                $str .= '<tr><th> ' . implode(' </th><th> ', array_map('htmlspecialchars', array_keys($result[0]))) . ' </th></tr>';

                foreach ($result as $v) {
                    $str .= '<tr class="border">';
                    foreach ($v as $value) {
                        $str .= $value === null ? '<td><pre style="margin:0;">NULL</pre></td>' : '<td><pre style="margin:0;"><a href="#sql" onclick="Gmanager.paste(\'' . rawurlencode($value) . '\');">' . htmlspecialchars($value, ENT_NOQUOTES) . '</a></pre></td>';
                    }
                    $str .= '</tr>';
                }

                $out .= '<table class="telo">' . $str . '</table>';
            }
        }

        mysql_close($this->_resource);
        return Helper_View::message(Language::get('sql_true') . $i . '<br/>' . Language::get('sql_rows') . $rows . '<br/>' . str_replace('%time%', round($time, 6), Language::get('microtime')), Helper_View::MESSAGE_SUCCESS) . $out;
    }
}
