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
    static private $_id         = array();
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


    /**
     * Valid chmod
     * 
     * @param mixed $chmod
     * @return int
     */
    private function _chmoder ($chmod)
    {
        if (!is_int($chmod)) {
            $strlen = strlen($chmod);

            if (($strlen != 3 && $strlen != 4) || !is_numeric($chmod)) {
                return false;
            } else if ($strlen == 3) {
                $chmod = '0' . $chmod;
            }
            $chmod = octdec($chmod);
        }
        return $chmod;
    }


    /**
     * mkdir
     * @param string $dir
     * @param mixed $chmod
     * @return bool
     */
    public function mkdir ($dir, $chmod = 0755)
    {
        ftp_chdir($this->_res, '/');
        if (!$this->is_dir($dir)) {
            if (!@ftp_mkdir($this->_res, IOWrapper::set($dir))) {
                return false;
            }
        }

        $this->chmod($dir, $this->_chmoder($chmod));
        return true;
    }


    /**
     * chmod
     * @param string $file
     * @param mixed $chmod
     * @return bool
     */
    public function chmod ($file, $chmod = 0755)
    {
        if (Config::$sysType == 'WIN') {
            //trigger_error($GLOBALS['lng']['win_chmod']);
            return true;
        }

        ftp_chdir($this->_res, '/');
        if ($file[0] != '/') {
            $file = '/' . $file;
        }
        return ftp_chmod($this->_res, $this->_chmoder($chmod), IOWrapper::set($file));
    }


    /**
     * file_get_contents
     * 
     * @param string $file
     * @return string
     */
    public function file_get_contents ($file)
    {
        ftp_chdir($this->_res, '/');
        $tmp = fopen('php://temp', 'r+');

        if (ftp_fget($this->_res, $tmp, IOWrapper::set($file), FTP_BINARY, 0)) {
            rewind($tmp);
            return stream_get_contents($tmp);
        } else {
            return '';
        }
    }


    /**
     * file_put_contents
     * 
     * @param string $file
     * @param string $data
     * @return int (0 or 1)
     */
    public function file_put_contents ($file, $data = '')
    {
        $php_temp = Config::$temp . '/GmanagerEditor' . $_SERVER['REQUEST_TIME'] . '.tmp';
        file_put_contents($php_temp, $data);
        chmod($php_temp, 0666);

        $tmp = iconv_substr($file, 0, iconv_strrpos($file, '/'));
        if ($tmp === false) {
            $tmp = substr($file, 0, strrpos($file, '/'));
        }

        ftp_chdir($this->_res, IOWrapper::set($tmp));
        $result = ftp_put($this->_res, basename(IOWrapper::set($file)), $php_temp, FTP_BINARY);

        unlink($php_temp);
        return intval($result);
    }


    /**
     * is_dir
     * 
     * @param string $str
     * @return bool
     */
    public function is_dir ($str)
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


    /**
     * is_file
     * 
     * @param string $str
     * @return bool
     */
    public function is_file ($str)
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


    /**
     * is_link
     * 
     * @param string $str
     * @return bool
     */
    public function is_link ($str)
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


    /**
     * is_readable
     * 
     * @param string $str
     * @return bool
     */
    public function is_readable ($str)
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_readable($this->_url . $str);
    }


    /**
     * is_writable
     * 
     * @param string $str
     * @return bool
     */
    public function is_writable ($str)
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_writable($this->_url . $str);
    }


    /**
     * filesize
     * 
     * @param string $file
     * @return int
     */
    public function filesize ($file)
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


    /**
     * file_exists
     * 
     * @param string $str
     * @return bool
     */
    public function file_exists ($str)
    {
        //$str = self::_change_symbol($str);
        //return file_exists($this->_url . $str);
        return ($this->is_file($str) || $this->is_dir($str) || $this->is_link($str));
    }


    /**
     * filemtime
     * 
     * @param string $str
     * @return int
     */
    public function filemtime ($str)
    {
        //$str = self::_change_symbol($str);
        //return filemtime($this->_url . $str);
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($str)]['mtime'];
    }


    /**
     * unlink
     * 
     * @param string $file
     * @return bool
     */
    public function unlink ($file)
    {
        //$file = self::_change_symbol($file);
        ftp_chdir($this->_res, '/');
        return ftp_delete($this->_res, IOWrapper::set($file));
    }


    /**
     * rename
     * 
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function rename ($from, $to)
    {
        //$from = self::_change_symbol($from);
        //$to = self::_change_symbol($to);
        ftp_chdir($this->_res, '/');
        return ftp_rename($this->_res, IOWrapper::set($from), IOWrapper::set($to));
    }


    /**
     * copy
     * 
     * @param string $from
     * @param string $to
     * @param mixed  $chmod
     * @return bool
     */
    public function copy ($from, $to, $chmod = 0644)
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


    /**
     * rmdir
     * 
     * @param string $dir
     * @return bool
     */
    public function rmdir ($dir)
    {
        //$dir = self::_change_symbol($dir);
        ftp_chdir($this->_res, '/');
        return @ftp_rmdir($this->_res, IOWrapper::set($dir));
    }


    /**
     * iterator
     * 
     * @param string $dir
     * @return array
     */
    public function iterator ($dir)
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


    /**
     * fileperms
     * 
     * @param string $str
     * @return int
     */
    public function fileperms ($str)
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


    /**
     * stat
     * 
     * @param string $str
     * @return array
     */
    public function stat ($str)
    {
        $dir = str_replace('\\', '/', dirname($str));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        return self::$_rawlist[$dir][basename($str)];
    }


    /**
     * readlink
     * 
     * @param string $link
     * @return array
     */
    public function readlink ($link)
    {
        $dir = str_replace('\\', '/', dirname($link));
        if (!isset(self::$_rawlist[$dir])) {
            $this->_rawlist($dir);
        }
        $t1 = self::$_rawlist[$dir][basename($link)]['file'];
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

        return array($t1, $t2);
    }


    /**
     * getcwd
     * 
     * @return string
     */
    public function getcwd ()
    {
        $str = IOWrapper::get(ftp_pwd($this->_res));
        if ($str == '.') {
            $str = '/';
        }
        return $str;
    }


    /**
     * realpath
     * 
     * @param string $path
     * @return string
     */
    public function realpath ($path)
    {
        return IOWrapper::get(realpath(IOWrapper::set($path)));
    }


    /**
     * rawlist
     * 
     * @param string $dir
     * @return array
     */
    private function _rawlist ($dir = '/')
    {
        $raw_dir = self::$_dir = str_replace('\\', '/', $dir);

        ftp_chdir($this->_res, '/');
        if (preg_match('/^[A-Z]+?:[\\*|\/*]+(.*)/', $dir, $match)) {
            $raw_dir = $match[1] ? '/' . $match[1] : '/';
        }

        foreach ((array)ftp_rawlist($this->_res, '/' . IOWrapper::set($raw_dir)) as $var) {
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


    /**
     * rawlistCallback
     * 
     * @param array $data
     * @return void
     */
    private function _rawlistCallback ($data)
    {
        $data[10] = IOWrapper::get(trim($data[10]));

        self::$_rawlist[self::$_dir][basename($data[10])] = array(
            'chmod' => $data[1] == 'd' && Config::$sysType == 'WIN' ? 0777 : (Config::$sysType == 'WIN' ? 0666 : $this->_chmodNum($data[2])),
            'uid'   => $data[3],
            'owner' => is_numeric($data[3]) ? (isset(self::$_id[$data[3]]) ? self::$_id[$data[3]] : self::$_id[$data[3]] = Gmanager::id2name($data[3])) : $data[3],
            'gid'   => $data[4],
            'group' => is_numeric($data[4]) ? (isset(self::$_id[$data[4]]) ? self::$_id[$data[4]] : self::$_id[$data[4]] = Gmanager::id2name($data[4])) : $data[4],
            'size'  => $data[5],
            'mtime' => strtotime($data[6] . ' ' . $data[7] . ' ' . $data[8] . ':' . $data[9]),
            'file'  => $data[10],
            'type'  => $data[1] == 'd' ? 'dir' : ($data[1] == 'l' ? 'link' : 'file')
        );
    }


    /**
     * chmodNum
     * 
     * @param string $perm
     * @return int
     */
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
