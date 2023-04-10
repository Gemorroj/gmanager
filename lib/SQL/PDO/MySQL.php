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
class SQL_PDO_MySQL implements SQL_Interface
{
    /**
     * @var PDO
     */
    private $_resource;

    /**
     * PDO MySQL connector.
     *
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     *
     * @return PDO|string
     */
    private function _connect($host = 'localhost', $name = 'root', $pass = '', $db = '', $charset = 'utf8')
    {
        try {
            $dsn = '';
            $dsn .= $db ? 'dbname='.\addslashes($db).';' : '';
            $dsn .= $host ? 'host='.\addslashes($host).';' : '';

            $attr = $charset ? [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.\addslashes($charset)] : [];

            $this->_resource = new PDO('mysql:'.$dsn, $name, $pass, $attr);
        } catch (Exception $e) {
            return Helper_View::message(Language::get('sql_connect_false').'<br/>'.\htmlspecialchars($e->getMessage(), \ENT_NOQUOTES), Helper_View::MESSAGE_ERROR);
        }

        return $this->_resource;
    }

    /**
     * Installer.
     *
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $sql
     *
     * @return string
     */
    public function installer($host = null, $name = null, $pass = null, $db = '', $charset = null, $sql = '')
    {
        if (!$sql || !($query = SQL::parser($sql))) {
            return '';
        }

        $out = '<?php'."\n"
             .'// PDO MySQL Installer'."\n"
             .'// Created in Gmanager '.Config::getVersion()."\n"
             .'// https://github.com/Gemorroj/gmanager'."\n\n"

             .'error_reporting(0);'."\n\n"

             .'header(\'Content-type: text/html; charset=UTF-8\');'."\n\n"

             .'echo \'<!DOCTYPE html>'."\n"
             .'<html>'."\n"
             .'<head>'."\n"
             .'<title>PDO MySQL Installer</title>'."\n"
             .'<style type="text/css">'."\n"
             .'body {'."\n"
             .'    background-color: #cccccc;'."\n"
             .'    color: #000000;'."\n"
             .'}'."\n"
             .'</style>'."\n"
             .'</head>'."\n"
             .'<body>'."\n"
             .'<div>\';'."\n\n\n"

             .'if (!$_POST) {'."\n"
             .'    echo \'<form action="\' . $_SERVER[\'PHP_SELF\'] . \'" method="post">'."\n"
             .'    <div>'."\n"
             .'    '.Language::get('sql_user').'<br/>'."\n"
             .'    <input type="text" name="name" value="'.\htmlspecialchars($name).'"/><br/>'."\n"
             .'    '.Language::get('sql_pass').'<br/>'."\n"
             .'    <input type="text" name="pass" value="'.\htmlspecialchars($pass).'"/><br/>'."\n"
             .'    '.Language::get('sql_host').'<br/>'."\n"
             .'    <input type="text" name="host" value="'.\htmlspecialchars($host).'"/><br/>'."\n"
             .'    '.Language::get('sql_db').'<br/>'."\n"
             .'    <input type="text" name="db" value="'.\htmlspecialchars($db).'"/><br/>'."\n"
             .'    <input type="submit" value="'.Language::get('install').'"/>'."\n"
             .'    </div>'."\n"
             .'    </form>'."\n"
             .'    </div></body></html>\';'."\n"
             .'    exit;'."\n"
             .'}'."\n\n"

             .'try {'."\n"
             .'    $connect = new PDO(\'mysql:dbname=\' . $_POST[\'db\'] . \';host=\' . $_POST[\'host\'], $_POST[\'name\'], $_POST[\'pass\'], array(PDO::MYSQL_ATTR_INIT_COMMAND => \'SET NAMES '.\str_ireplace('utf-8', 'utf8', $charset).'\'));'."\n"
             .'} catch (Exception $e) {'."\n"
             .'    exit(\'Can not connect to MySQL</div></body></html>\');'."\n"
             .'}'."\n\n";

        foreach ($query as $q) {
            $out .= '$sql = "'.\str_replace('"', '\"', \trim($q)).';";'."\n"
                  .'if (!$connect->query($sql)) {'."\n"
                  .'    $tmp = $connect->errorInfo();'."\n"
                  .'    $error[] = $tmp[2] . "\n SQL:\n" . $sql;'."\n"
                  .'}'."\n\n";
        }

        $out .= 'if ($error) {'."\n"
              .'    echo \'Error:<pre>\' . htmlspecialchars(print_r($error, true), ENT_NOQUOTES) . \'</pre>\';'."\n"
              .'} else {'."\n"
              .'    echo \'Ok\';'."\n"
              .'}'."\n\n"

              .'echo \'</div></body></html>\''."\n"
              .'?>';

        return $out;
    }

    /**
     * Backup.
     *
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param array  $tables
     *
     * @return string|bool
     */
    public function backup($host = null, $name = null, $pass = null, $db = '', $charset = null, $tables = [])
    {
        $connect = $this->_connect($host, $name, $pass, $db, $charset);
        if ($connect instanceof PDO) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $true = $false = '';
        if ($tables) {
            if ($tables['tables']) {
                foreach ($tables['tables'] as $f) {
                    $q = $this->_resource->query('SHOW CREATE TABLE `'.\str_replace('`', '``', $f).'`;');
                    if ($q) {
                        $tmp = $q->fetch(PDO::FETCH_NUM);
                        $true .= $tmp[1].";\n\n";
                    } else {
                        $tmp = $this->_resource->errorInfo();
                        $false .= $tmp[2]."\n";
                    }
                }
            }
            if ($tables['data']) {
                foreach ($tables['data'] as $f) {
                    $q = $this->_resource->query('SELECT * FROM `'.\str_replace('`', '``', $f).'`;');
                    if ($q) {
                        if ($q->columnCount()) {
                            $true .= 'INSERT INTO `'.\str_replace('`', '``', $f).'` VALUES';
                            while ($row = $q->fetch(PDO::FETCH_NUM)) {
                                $true .= "\n(";
                                foreach ($row as $v) {
                                    $true .= null === $v ? 'NULL,' : "'".\str_replace("'", "''", $v)."',";
                                }
                                $true = \rtrim($true, ',').'),';
                            }
                            $true = \rtrim($true, ',').";\n\n";
                        }
                    } else {
                        $tmp = $this->_resource->errorInfo();
                        $false .= $tmp[2]."\n";
                    }
                }
            }

            if ($true) {
                $dir = \dirname($tables['file']);
                if (!Gmanager::getInstance()->is_dir($dir)) {
                    Gmanager::getInstance()->mkdir($dir, null, true);
                }
                if (!Gmanager::getInstance()->file_put_contents($tables['file'], $true)) {
                    $false .= Errors::get()."\n";
                }
            }

            if ($false) {
                return Helper_View::message(Language::get('sql_backup_false').'<pre>'.\htmlspecialchars(\trim($false), \ENT_NOQUOTES).'</pre>', Helper_View::MESSAGE_ERROR);
            }

            return Helper_View::message(Language::get('sql_backup_true'), Helper_View::MESSAGE_SUCCESS);
        }
        $q = $this->_resource->query('SHOW TABLES;');
        if ($q) {
            while ($row = $q->fetch(PDO::FETCH_NUM)) {
                $true .= '<option value="'.\rawurlencode($row[0]).'">'.\htmlspecialchars($row[0], \ENT_NOQUOTES).'</option>';
            }

            return $true;
        }

        return false;
    }

    /**
     * Query.
     *
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $data
     *
     * @return string
     */
    public function query($host = null, $name = null, $pass = null, $db = '', $charset = null, $data = '')
    {
        $connect = $this->_connect($host, $name, $pass, $db, $charset);
        if (\is_object($connect)) {
            $this->_resource = $connect;
        } else {
            return $connect;
        }

        $i = $time = $rows = 0;
        $out = null;
        foreach (SQL::parser($data) as $q) {
            $result = [];
            $str = '';
            $q = \rtrim($q, ';');

            $start = \microtime(true);
            $r = $this->_resource->query($q.';');
            $time += \microtime(true) - $start;

            if (!\is_object($r)) {
                $tmp = $this->_resource->errorInfo();

                return Helper_View::message(Language::get('sql_query_false'), Helper_View::MESSAGE_ERROR_EMAIL).'<div><code>'.\htmlspecialchars($tmp[2], \ENT_NOQUOTES).'</code></div>';
            }
            $rows += $r->rowCount();
            while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }

            ++$i;

            if ($result) {
                $str .= '<tr><th> '.\implode(' </th><th> ', \array_map('htmlspecialchars', \array_keys($result[0]))).' </th></tr>';

                foreach ($result as $v) {
                    $str .= '<tr class="border">';
                    foreach ($v as $value) {
                        $str .= null === $value ? '<td><pre style="margin:0;">NULL</pre></td>' : '<td><pre style="margin:0;"><a href="#sql" onclick="Gmanager.paste(\''.\rawurlencode($value).'\');">'.\htmlspecialchars($value, \ENT_NOQUOTES).'</a></pre></td>';
                    }
                    $str .= '</tr>';
                }

                $out .= '<table class="telo">'.$str.'</table>';
            }
        }

        return Helper_View::message(Language::get('sql_true').$i.'<br/>'.Language::get('sql_rows').$rows.'<br/>'.\str_replace('%time%', \round($time, 6), Language::get('microtime')), Helper_View::MESSAGE_SUCCESS).$out;
    }
}
