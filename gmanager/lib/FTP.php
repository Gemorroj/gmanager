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


class FTP
{
    private $_res               = null;
    //private $_url               = null;
    static private $_uid        = array();
    static private $_rawlist    = null;
    static private $_dir        = '/';


    public function __construct ($user = 'root', $pass = '', $host = 'localhost', $port = 21)
    {
        $this->_res = ftp_connect($host, $port, 10);
        ftp_login($this->_res, $user, $pass);
        ftp_pasv($this->_res, true);
        Config::$sysType = strtoupper(substr(ftp_systype($this->_res), 0, 3)) == 'WIN' ? 'WIN' : 'NIX';

        // URL
        //$this->_url = 'ftp://' . $user . ':' . $pass . '@' . $host . ':' . $port;
    }


    public function __destruct ()
    {
        return ftp_close($this->_res);
    }

    /*
    private static function _change_symbol ($str = '')
    {
        return ($str[0] == '/' ? $str : '/' . $str);
    }
    */

    public function mkdir ($dir = '', $chmod = '0755')
    {
        ftp_chdir($this->_res, '/');
        if (!$this->is_dir($dir)) {
            if (!@ftp_mkdir($this->_res, $dir)) {
                return false;
            }
        }

        $this->chmod($dir, $chmod);
        return true;
    }


    public function chmod ($file = '', $chmod = '0755')
    {
        if (Config::$sysType == 'WIN') {
            //trigger_error($GLOBALS['lng']['win_chmod']);
            return true;
        }

        ftp_chdir($this->_res, '/');
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
        return ftp_chmod($this->_res, octdec(intval($chmod)), $file);
    }


    public function file_get_contents ($file = '')
    {
        ftp_chdir($this->_res, '/');
        $tmp = fopen('php://memory', 'r+');

        if (ftp_fget($this->_res, $tmp, $file, FTP_BINARY, 0)) {
            rewind($tmp);
            return stream_get_contents($tmp);
        } else {
            return false;
        }
    }


    public function file_put_contents ($file = '', $data = '')
    {
        $php_temp = Config::$temp . '/GmanagerEditor' . $_SERVER['REQUEST_TIME'] . '.tmp';
        file_put_contents($php_temp, $data);
        chmod($php_temp, 0666);

        $tmp = iconv_substr($file, 0, strrpos($file, '/'));
        if ($tmp === false) {
            $tmp = substr($file, 0, strrpos($file, '/'));
        }

        ftp_chdir($this->_res, $tmp);
        $result = ftp_put($this->_res, basename($file), $php_temp, FTP_BINARY);

        unlink($php_temp);
        return $result;
    }


    public function is_dir ($str = '')
    {
        //$str = self::_change_symbol($str);
        //return is_dir($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return true;
        }
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$_rawlist[$dir][$b]) && self::$_rawlist[$dir][$b]['type'] == 'dir');
    }


    public function is_file ($str = '')
    {
        //$str = self::_change_symbol($str);
        //return is_file($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return false;
        }

        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$_rawlist[$dir][$b]) && self::$_rawlist[$dir][$b]['type'] == 'file');
    }


    public function is_link ($str = '')
    {
        //$str = self::_change_symbol($str);
        //return is_link($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if ($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir) {
            return false;
        }

        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }

        $b = basename($str);
        return (isset(self::$_rawlist[$dir][$b]) && self::$_rawlist[$dir][$b]['type'] == 'link');
    }


    public function is_readable ($str = '')
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_readable($this->_url . $str);
    }


    public function is_writable ($str = '')
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_writable($this->_url . $str);
    }


    public function filesize ($file = '')
    {
        //$file = self::_change_symbol($file);

        //ftp_chdir($this->_res, '/');
        //return sprintf('%u', ftp_size($this->_res, $file));
        
        $dir = str_replace('\\', '/', dirname($file));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($file)]['size'];
    }


    public function file_exists ($str = '')
    {
        //$str = self::_change_symbol($str);
        //return file_exists($this->_url . $str);
        return ($this->is_file($str) || $this->is_dir($str) || $this->is_link($str));
    }


    public function filemtime ($str = '')
    {
        //$str = self::_change_symbol($str);
        //return filemtime($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($str)]['mtime'];
    }


    public function unlink ($file = '')
    {
        //$file = self::_change_symbol($file);
        ftp_chdir($this->_res, '/');
        return ftp_delete($this->_res, $file);
    }


    public function rename ($from = '', $to = '')
    {
        //$from = self::_change_symbol($from);
        //$to = self::_change_symbol($to);
        ftp_chdir($this->_res, '/');
        return ftp_rename($this->_res, $from, $to);
    }


    public function copy ($from = '', $to = '', $chmod = '0644')
    {
        //$from = self::_change_symbol($from);
        //$to = self::_change_symbol($to);
        //$result = copy($this->_url . $from, $this->url . $to);
        //$this->chmod($this->_url . $to, $chmod);

        $result = false;
        if (($r = $this->file_get_contents($from)) !== false) {
            if ($result = $this->file_put_contents($to, $r)) {
                $this->chmod($to, $chmod);
            }
        }
        return $result;
    }


    public function rmdir ($dir = '')
    {
        //$dir = self::_change_symbol($dir);
        ftp_chdir($this->_res, '/');
        return @ftp_rmdir($this->_res, $dir);
    }


    public function iterator ($dir = '')
    {
        $tmp = array();

        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }

        foreach ((array)@self::$_rawlist[$dir] as $var) {
            if ($var['file'] != '.') {
                $tmp[] = basename($var['file']);
            }
        }

        return $tmp;
    }


    public function fileperms ($str = '')
    {
        if ($str == ' .' || $str == './' || $str == '/' || $str == '' || $str == '\\'){
            $str = '.';
        }

        //$str = self::_change_symbol($str);
        //return fileperms($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($str)]['chmod'];
    }


    public function stat ($str = '')
    {
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($str)];
    }


    public function readlink ($str = '')
    {
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        $t1 = self::$_rawlist[$dir][basename($str)]['file'];
        $t2 = explode(' -> ', $t1);
        $t2 = end($t2);
        if ($t2[0] != PATH_SEPARATOR) {
            if ($t2 == '.') {
                $t2 = substr(Config::$current, 0, -1);
            } else if ($t2 == '..') {
                $t2 = substr(strrev(strstr(strrev(Config::$current), '/')), 0, -1);
            } else {
                $t2 = (Config::$current != '.' ? Config::$current : '') . $t2;
            }
        }

        return array(
            $t1,
            $t2
        );
    }


    public function getcwd ()
    {
        $str = ftp_pwd($this->_res);
        if ($str == '.') {
            $str = '/';
        }
        return $str;
    }


    private function _rawlist ($dir = '/')
    {
        ftp_chdir($this->_res, '/');
        $raw_dir = self::$_dir = str_replace('\\', '/', $dir);
        if (preg_match('/^[A-Z]+?:[\\*|\/*]+(.*)/', $dir, $match)) {
            $raw_dir = $match[1] ? '/' . $match[1] : '/';
        }

        foreach ((array)ftp_rawlist($this->_res, '/' . $raw_dir) as $var) {
            if (substr($var, -3) == ' ..') {
                continue;
            } else {
                preg_replace_callback(
                    '`^(d|l|\-{1}+)(.{9}+)\s*(?:\d{1,3})\s*(\d+?|\w+?)\s*(\d+?|\w+?)\s*(\d*)\s([a-zA-Z]{3}+)\s*([0-9]{1,2}+)\s*([0-9]{2}+):?([0-9]{2}+)\s*(.*)$`U',
                    array($this, '_rawlistCallback'),
                    $var
                );
            }
        }

        return self::$_rawlist[self::$_dir];
    }


    private function _rawlistCallback ($data)
    {
        $data[10] = trim($data[10]);

        self::$_rawlist[self::$_dir][basename($data[10])] = array(
            'chmod' => $data[1] == 'd' && Config::$sysType == 'WIN' ? 0777 : (Config::$sysType == 'WIN' ? 0666 : $this->_chmodNum($data[2])),
            'uid'   => $data[3],
            'name'  => is_numeric($data[3]) ? (isset(self::$_uid[$data[3]]) ? self::$_uid[$data[3]] : self::$_uid[$data[3]] = Gmanager::uid2name($data[3], Config::$sysType)) : $data[3],
            'gid'   => $data[4],
            'size'  => $data[5],
            'mtime' => strtotime($data[6] . ' ' . $data[7] . ' ' . $data[8] . ':' . $data[9]),
            'file'  => $data[10],
            'type'  => $data[1] == 'd' ? 'dir' : ($data[1] == 'l' ? 'link' : 'file')
        );
    }


    private function _chmodNum ($perm = 'rw-r--r--')
    {
        $m = 0;

        if ($perm[0] == 'r') {
            $m += 0400;
        }
        if ($perm[1] == 'w') {
            $m += 0200;
        }
        if ($perm[2] == 'x') {
            $m += 0100;
        } else if ($perm[2] == 's') {
            $m += 04100;
        } else if ($perm[2] == 'S') {
            $m += 04000;
        }


        if ($perm[3] == 'r') {
            $m += 040;
        }
        if ($perm[4] == 'w') {
            $m += 020;
        }
        if ($perm[5] == 'x') {
            $m += 010;
        } else if ($perm[5] == 's') {
            $m += 02010;
        } else if ($perm[5] == 'S') {
            $m += 02000;
        }


        if ($perm[6] == 'r') {
            $m += 04;
        }
        if ($perm[7] == 'w') {
            $m += 02;
        }
        if ($perm[8] == 'x') {
            $m += 01;
        } else if ($perm[8] == 't') {
            $m += 01001;
        } else if ($perm[8] == 'T') {
            $m += 01000;
        }

        return $m;
    }

}

?>
