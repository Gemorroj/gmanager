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


class SQL_MySQLi implements SQL_Interface
{
    /**
     * @var mysqli
     */
    private $_resource;


    /**
     * MySQLi connector
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @return mysqli|string
     */
    private function _connect ($host = 'localhost', $name = 'root', $pass = '', $db = '', $charset = 'utf8')
    {
        $this->_resource = new mysqli($host, $name, $pass, $db);
        if (!$this->_resource || $this->_resource->connect_error) {
            return Helper_View::message(Language::get('sql_connect_false') . '<br/>' . htmlspecialchars($this->_resource->connect_error, ENT_NOQUOTES), Helper_View::MESSAGE_ERROR);
        }
        if ($charset) {
            $this->_resource->set_charset($charset);
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
             . '// MySQLi Installer' . "\n"
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
             . '<title>MySQLi Installer</title>' . "\n"
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

             . '$connect = new mysqli($_POST[\'host\'], $_POST[\'name\'], $_POST[\'pass\'], $_POST[\'db\']);' . "\n"
             . 'if (!$connect || $connect->connect_error) {' . "\n"
             . '     exit(\'Can not connect to MySQL</div></body></html>\');' . "\n"
             . '}' . "\n"
             . '$connect->set_charset(\'' . $charset . '\');' . "\n\n";

        foreach ($query as $q) {
            $out .= '$sql = "' . str_replace('"', '\"', trim($q)) . ';";' . "\n"
                  . '$connect->query($sql);' . "\n"
                  . 'if ($err = $connect->error) {' . "\n"
                  . '    $error[] = $err . "\n SQL:\n" . $sql;' . "\n"
                  . '}' . "\n\n";
        }

        $out .= '$connect->close();' . "\n\n"
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
        if (is_object($connect)) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $true = $false = '';
        if ($tables) {
            if ($tables['tables']) {
                foreach ($tables['tables'] as $f) {
                    $q = $this->_resource->query('SHOW CREATE TABLE `' . str_replace('`', '``', $f) . '`;');
                    if ($q) {
                        $tmp = $q->fetch_row();
                        $true .= $tmp[1] . ";\n\n";
                    } else {
                        $false .= $this->_resource->error . "\n";
                    }
                }
            }
            if ($tables['data']) {
                foreach ($tables['data'] as $f) {
                    $q = $this->_resource->query('SELECT * FROM `' . str_replace('`', '``', $f) . '`;');
                    if ($q) {
                        if ($q->num_rows) {
                            $true .= 'INSERT INTO `' . str_replace('`', '``', $f) . '` VALUES';
                            while ($row = $q->fetch_row()) {
                                $true .= "\n(";
                                foreach ($row as $v) {
                                    $true .= $v === null ? 'NULL,' : "'" . str_replace("'", "''", $v) . "',";
                                }
                                $true = rtrim($true, ',') . '),';
                            }
                            $true = rtrim($true, ',') . ";\n\n";
                        }
                    } else {
                        $false .= $this->_resource->error . "\n";
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
            $q = $this->_resource->query('SHOW TABLES;');
            if ($q) {
                while ($row = $q->fetch_row()) {
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
        if (is_object($connect)) {
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
            $r = $this->_resource->query($q . ';');
            $time += microtime(true) - $start;

            if (!$r) {
                return Helper_View::message(Language::get('sql_query_false'), Helper_View::MESSAGE_ERROR_EMAIL) . '<div><code>' . $this->_resource->error . '</code></div>';
            } else {
                if (is_object($r) && $row = $r->num_rows) {
                    $rows += $row;
                    while ($row = $r->fetch_assoc()) {
                        $result[] = $row;
                    }
                } elseif ($r === true) {
                    $rows += $this->_resource->affected_rows;
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

        $this->_resource->close();
        return Helper_View::message(Language::get('sql_true') . $i . '<br/>' . Language::get('sql_rows') . $rows . '<br/>' . str_replace('%time%', round($time, 6), Language::get('microtime')), Helper_View::MESSAGE_SUCCESS) . $out;
    }
}
