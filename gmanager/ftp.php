<?php
// encoding = 'utf-8'
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.3 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


$GLOBALS['mode'] = new ftp;
$GLOBALS['class'] = 'ftp';

class ftp
{
    private $user = 'root';      // логин
    private $password = '';      // пароль
    private $host = 'localhost'; // хост
    private $port = 21;          // порт
    private $res;
    private $url;
    private $dir;
    static private $rawlist;

    public function __construct()
    {
        // установка соединения
        $this->res = ftp_connect($this->host, $this->port, 10);

        // вход с именем пользователя и паролем
        ftp_login($this->res, $this->user, $this->password);

        // включение пассивного режима
        ftp_pasv($this->res, true);
        
        // формируем строку URL
        //$this->url = 'ftp://' . $this->user . ':' . $this->password . '@' . $this->host . ':' . $this->port;
    }



    public function __destruct()
    {
        // закрываем соединение
        return ftp_close($this->res);
    }


    //////////////////////////////////////////////////////////////////

    public static function change_symbol($str = '')
    {
        return ($str[0] == '/' ? $str : '/' . $str);
    }

    public function mkdir($dir = '', $chmod = '0755')
    {
        ftp_chdir($this->res, '/');
        if (!$this->is_dir($dir)) {
            if (!@ftp_mkdir($this->res, $dir)) {
                return false;
            }
        }

        $this->chmod($dir, $chmod);
        return true;
    }

    public function chmod($file = '', $chmod = '0755')
    {
        /*
        $win = ftp_systype($this->res);
        if ($win[0] . $win[1] . $win[2] == 'WIN') {
            trigger_error($GLOBALS['lng']['win_chmod']);
            return false;
        }
        */

        ftp_chdir($this->res, '/');
        settype($chmod, 'string');
        $strlen = strlen($chmod);
        if (!is_numeric($chmod) || ($strlen != 3 && $strlen != 4)) {
            return false;
        }
        if ($strlen == 3) {
            $chmod = '0' . $chmod;
        }
        if ($file[0] != '/') {
            $file = '/' . $file;
        }
        return ftp_chmod($this->res, octdec(intval($chmod)), $file);
    }

    public function file_get_contents($str = '')
    {
        ftp_chdir($this->res, '/');
        $tmp = fopen('php://memory', 'r+');

        if (ftp_fget($this->res, $tmp, $str, FTP_BINARY, 0)) {
            rewind($tmp);
            return stream_get_contents($tmp);
        } else {
            return false;
        }
    }

    public function file_put_contents($file = '', $data = '')
    {
        $php_temp = $GLOBALS['temp'] . '/GmanagerEditor' . $_SERVER['REQUEST_TIME'] . '.tmp';
        file_put_contents($php_temp, $data);
        chmod($php_temp, 0666);

        $tmp = iconv_substr($file, 0, strrpos($file, '/'));
        if ($tmp === false) {
            $tmp = substr($file, 0, strrpos($file, '/'));
        }

        ftp_chdir($this->res, $tmp);
        $result = ftp_put($this->res, basename($file), $php_temp, FTP_BINARY);

        unlink($php_temp);
        return $result;
    }

    public function is_dir($str = '')
    {
        //$str = self::change_symbol($str);
        //return is_dir($this->url.$str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return true;
        }
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$rawlist[$dir][$b]) && self::$rawlist[$dir][$b]['type'] == 'dir');
    }

    public function is_file($str = '')
    {
        //$str = self::change_symbol($str);
        //return is_file($this->url.$str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return false;
        }

        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$rawlist[$dir][$b]) && self::$rawlist[$dir][$b]['type'] == 'file');
    }

    public function is_link($str = '')
    {
        //$str = self::change_symbol($str);
        //return is_link($this->url.$str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return false;
        }

        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$rawlist[$dir][$b]) && self::$rawlist[$dir][$b]['type'] == 'link');
    }

    public function is_readable($str = '')
    {
        return true;
        //$str = self::change_symbol($str);
        //return is_readable($this->url.$str);
    }

    public function is_writable($str = '')
    {
        return true;
        //$str = self::change_symbol($str);
        //return is_writable($this->url.$str);
    }

    public function filesize($str = '')
    {
        //$str = self::change_symbol($str);

        //ftp_chdir($this->res, '/');
        //return sprintf('%u', ftp_size($this->res, $str));
        
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }
        return self::$rawlist[$dir][basename($str)]['size'];
    }

    public function file_exists($str = '')
    {
        //$str = self::change_symbol($str);
        //return file_exists($this->url.$str);
        return ($this->is_file($str) || $this->is_dir($str) || $this->is_link($str));
    }

    public function filemtime($str = '')
    {
        //$str = self::change_symbol($str);
        //return filemtime($this->url.$str);
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }
        return self::$rawlist[$dir][basename($str)]['mtime'];
    }

    public function unlink($str = '')
    {
        //$str = self::change_symbol($str);
        ftp_chdir($this->res, '/');
        return ftp_delete($this->res, $str);
    }

    public function rename($from = '', $to = '')
    {
        //$from = self::change_symbol($from);
        //$to = self::change_symbol($to);
        ftp_chdir($this->res, '/');
        return ftp_rename($this->res, $from, $to);
    }

    public function copy($from = '', $to = '', $chmod = '0644')
    {
        //$from = self::change_symbol($from);
        //$to = self::change_symbol($to);
        //$result = copy($this->url.$from, $this->url.$to);
        //$this->chmod($this->url.$to, $chmod);

        $result = false;
        if (($r = $this->file_get_contents($from)) !== false) {
            if ($result = $this->file_put_contents($to, $r)) {
                $this->chmod($to, $chmod);
            }
        }
        return $result;
    }

    public function rmdir($str = '')
    {
        //$str = self::change_symbol($str);
        ftp_chdir($this->res, '/');
        return ftp_rmdir($this->res, $str);
    }

    public function iterator($str = '')
    {
        $tmp = array();

        if (!isset(self::$rawlist[$str])) {
            $this->rawlist($str);
        }

        foreach (self::$rawlist[$str] as $var) {
            $tmp[] = basename($var['file']);
        }

        return $tmp;
    }

    public function fileperms($str = '')
    {
        if ($str == '.' || $str == '/' || $str == ''){
            return 0;
        }
        //$str = self::change_symbol($str);
        //return fileperms($this->url.$str);
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }
        return self::$rawlist[$dir][basename($str)]['chmod'];
    }

    public function stat($str = '')
    {
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }
        return self::$rawlist[$dir][basename($str)];
    }

    public function readlink($str = '')
    {
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$rawlist[$dir])) {
            $this->rawlist($dir);
        }
        $t1 = self::$rawlist[$dir][basename($str)]['file'];
        $t2 = explode(' -> ', $t1);
        $t2 = end($t2);
        if ($t2[0] != PATH_SEPARATOR) {
            if ($t2 == '.') {
                $t2 = substr($GLOBALS['current'], 0, -1);
            } else if ($t2 == '..') {
                $t2 = substr(strrev(strstr(strrev($GLOBALS['current']), '/')), 0, -1);
            } else {
                $t2 = ($GLOBALS['current'] != '.' ? $GLOBALS['current'] : '') . $t2;
            }
        }

        return array(
            $t1,
            $t2
        );
    }

    public function getcwd()
    {
        $str = ftp_pwd($this->res);
        if ($str == '.') {
            $str = '/';
        }
        return $str;
    }

    private function rawlist($dir = '/')
    {
        ftp_chdir($this->res, '/');
        $raw_dir = $dir = str_replace('\\', '/', $dir);
        if (preg_match('/^[A-Z]+?:[\\*|\/*]+(.*)/', $dir, $match)) {
            $raw_dir = $match[1] ? '/' . $match[1] : '/';
        }

        $items = array();
        foreach (array_slice((array)ftp_rawlist($this->res, '/' . $raw_dir), 2) as $var) {
            @preg_replace(
                '`^(.{10}+)\s*(\d{1,3})\s*(\d+?|\w+?)\s*(\d+?|\w+?)\s*(\d*)\s([a-zA-Z]{3}+)\s*([0-9]{1,2}+)\s*([0-9]{2}+):?([0-9]{2}+)\s*(.*)$`Ue',
                '$items[basename(trim("$10"))] = array(
                "chmod" => $this->chmodnum("$1"),
                "uid" => "$3",
                "gid" => "$4",
                "size" => "$5",
                "mtime" => strtotime("$6 $7 $8:$9"),
                "file" => trim("$10"),
                "type" => substr("$1", 0, 1) == "d" ? "dir" : (substr("$1", 0, 1) == "l" ? "link" : "file")
                );',
                $var
            );
        }
        $this->dir = $dir;
        self::$rawlist[$dir] = & $items;
        return $items;
    }

    private function chmodnum($perm = 'rw-r--r--')
    {
        $m = 0; 

        if ($perm[1] == 'r') {
            $m += 0400;
        }
        if ($perm[2] == 'w') {
            $m += 0200;
        }
        if ($perm[3] == 'x') {
            $m += 0100;
        } else if ($perm[3] == 's') {
            $m += 04100;
        } else if ($perm[3] == 'S') {
            $m += 04000;
        }


        if ($perm[4] == 'r') {
            $m += 040;
        }
        if ($perm[5] == 'w') {
            $m += 020;
        }
        if ($perm[6] == 'x') {
            $m += 010;
        } else if ($perm[6] == 's') {
            $m += 02010;
        } else if ($perm[6] == 'S') {
            $m += 02000;
        }


        if ($perm[7] == 'r') {
            $m += 04;
        }
        if ($perm[8] == 'w') {
            $m += 02;
        }
        if ($perm[9] == 'x') {
            $m += 01;
        } else if ($perm[9] == 't') {
            $m += 01001;
        } else if ($perm[9] == 'T') {
            $m += 01000;
        }


        return $m;
    }

}

?>
