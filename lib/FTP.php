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
class FTP extends Gmanager
{
    /**
     * @var resource
     */
    private $_res;
    /**
     * @var array
     */
    private static $_idUser = [];
    /**
     * @var array
     */
    private static $_idGroup = [];
    /**
     * @var array
     */
    private static $_rawlist = [];
    /**
     * @var string
     */
    private static $_dir = '/';
    //private $_url;

    /**
     * __construct.
     *
     * @param string $user
     * @param string $pass
     * @param string $host
     * @param int    $port
     */
    public function __construct($user = 'root', $pass = '', $host = 'localhost', $port = 21)
    {
        $host = NetworkWrapper::convertHost($host);
        $this->_res = \ftp_connect($host, $port, 10);
        \ftp_login($this->_res, $user, $pass);
        \ftp_pasv($this->_res, true);

        $this->_setSysType(\ftp_systype($this->_res));

        // URL
        //$this->_url = 'ftp://' . $user . ':' . $pass . '@' . $host . ':' . $port;
    }

    /**
     * __destruct.
     */
    public function __destruct()
    {
        \ftp_close($this->_res);
    }

    /*
    private static function _change_symbol ($str = '')
    {
        return ($str[0] == '/' ? $str : '/' . $str);
    }
    */

    /**
     * mkdir.
     *
     * @param string     $dir
     * @param int|string $chmod
     * @param bool       $recursive
     *
     * @return bool
     */
    public function mkdir($dir, $chmod = 0755, $recursive = false)
    {
        \ftp_chdir($this->_res, '/');
        if (!$this->is_dir($dir)) {
            if ($recursive) {
                foreach (\explode('/', $dir) as $part) {
                    if (!@\ftp_chdir($this->_res, $part)) {
                        if (!@\ftp_mkdir($this->_res, $part)) {
                            return false;
                        }
                        \ftp_chdir($this->_res, $part);
                    }
                }
            } else {
                if (!@\ftp_mkdir($this->_res, $dir)) {
                    return false;
                }
            }
        }

        $this->chmod($dir, $this->_chmoder($chmod));

        return true;
    }

    /**
     * chmod.
     *
     * @param string     $file
     * @param int|string $chmod
     *
     * @return bool
     */
    public function chmod($file, $chmod = 0755)
    {
        if ('WIN' == Registry::get('sysType')) {
            //trigger_error(Language::get('win_chmod'));
            return true;
        }

        \ftp_chdir($this->_res, '/');
        if ('/' != $file[0]) {
            $file = '/'.$file;
        }

        return \ftp_chmod($this->_res, $this->_chmoder($chmod), $file);
    }

    /**
     * file_get_contents.
     *
     * @param string $file
     *
     * @return string
     */
    public function file_get_contents($file)
    {
        \ftp_chdir($this->_res, '/');
        $tmp = \fopen('php://temp', 'r+');

        if (\ftp_fget($this->_res, $tmp, $file, \FTP_BINARY, 0)) {
            \rewind($tmp);

            return \stream_get_contents($tmp);
        }

        return '';
    }

    /**
     * file_put_contents.
     *
     * @param string $file
     * @param string $data
     *
     * @return int (0 or 1)
     */
    public function file_put_contents($file, $data = '')
    {
        $php_temp = Config::getTemp().'/GmanagerFtpTemp'.GMANAGER_REQUEST_TIME.'.tmp';
        \file_put_contents($php_temp, $data);
        \chmod($php_temp, 0666);

        \ftp_chdir($this->_res, \mb_substr($file, 0, \mb_strrpos($file, '/')));
        $result = \ftp_put($this->_res, Helper_System::basename($file), $php_temp, \FTP_BINARY);

        \unlink($php_temp);

        return $result ? 1 : 0;
    }

    /**
     * is_dir.
     *
     * @param string $str
     *
     * @return bool
     */
    public function is_dir($str)
    {
        //$str = self::_change_symbol($str);
        //return is_dir($this->_url . $str);

        $rawlist = $this->_rawlist(\dirname($str));

        return 'dir' === $rawlist[Helper_System::basename($str)]['type'];
    }

    /**
     * is_file.
     *
     * @param string $str
     *
     * @return bool
     */
    public function is_file($str)
    {
        //$str = self::_change_symbol($str);
        //return is_file($this->_url . $str);

        $rawlist = $this->_rawlist(\dirname($str));

        return 'file' === $rawlist[Helper_System::basename($str)]['type'];
    }

    /**
     * is_link.
     *
     * @param string $str
     *
     * @return bool
     */
    public function is_link($str)
    {
        //$str = self::_change_symbol($str);
        //return is_link($this->_url . $str);

        $rawlist = $this->_rawlist(\dirname($str));

        return 'link' === $rawlist[Helper_System::basename($str)]['type'];
    }

    /**
     * is_readable.
     *
     * @param string $str
     *
     * @return bool
     */
    public function is_readable($str)
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_readable($this->_url . $str);
    }

    /**
     * is_writable.
     *
     * @param string $str
     *
     * @return bool
     */
    public function is_writable($str)
    {
        return true;
        //$str = self::_change_symbol($str);
        //return is_writable($this->_url . $str);
    }

    /**
     * filesize.
     *
     * @param string $file
     *
     * @return int
     */
    public function filesize($file)
    {
        //$file = self::_change_symbol($file);

        //ftp_chdir($this->_res, '/');
        //return sprintf('%u', ftp_size($this->_res, $file));

        $rawlist = $this->_rawlist(\dirname($file));

        return $rawlist[Helper_System::basename($file)]['size'];
    }

    /**
     * file_exists.
     *
     * @param string $str
     *
     * @return bool
     */
    public function file_exists($str)
    {
        //$str = self::_change_symbol($str);
        //return file_exists($this->_url . $str);
        return $this->is_file($str) || $this->is_dir($str) || $this->is_link($str);
    }

    /**
     * filetype.
     *
     * @param string $str
     *
     * @return string
     */
    public function filetype($str)
    {
        if ($this->is_file($str)) {
            return 'file';
        } elseif ($this->is_dir($str)) {
            return 'dir';
        } elseif ($this->is_link($str)) {
            return 'link';
        }

        return '';
    }

    /**
     * filemtime.
     *
     * @param string $str
     *
     * @return int
     */
    public function filemtime($str)
    {
        //$str = self::_change_symbol($str);
        //return filemtime($this->_url . $str);

        $rawlist = $this->_rawlist(\dirname($str));

        return $rawlist[Helper_System::basename($str)]['mtime'];
    }

    /**
     * unlink.
     *
     * @param string $file
     *
     * @return bool
     */
    public function unlink($file)
    {
        //$file = self::_change_symbol($file);
        \ftp_chdir($this->_res, '/');

        return \ftp_delete($this->_res, $file);
    }

    /**
     * rename.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function rename($from, $to)
    {
        //$from = self::_change_symbol($from);
        //$to = self::_change_symbol($to);
        \ftp_chdir($this->_res, '/');

        return \ftp_rename($this->_res, $from, $to);
    }

    /**
     * copy.
     *
     * @param string     $from
     * @param string     $to
     * @param int|string $chmod
     *
     * @return bool
     */
    public function copy($from, $to, $chmod = 0644)
    {
        //$from = self::_change_symbol($from);
        //$to = self::_change_symbol($to);
        //$result = copy($this->_url . $from, $this->url . $to);
        //$this->chmod($this->_url . $to, $chmod);

        $result = false;
        if (($r = $this->file_get_contents($from)) !== false) {
            $result = $this->file_put_contents($to, $r);
            if ($result) {
                $this->chmod($to, $chmod);
            }
        }

        return $result;
    }

    /**
     * symlink.
     *
     * @param string     $from
     * @param string     $to
     * @param int|string $chmod
     *
     * @return bool
     */
    public function symlink($from, $to, $chmod = 0644)
    {
        return $this->copy($from, $to, $chmod);
    }

    /**
     * rmdir.
     *
     * @param string $dir
     *
     * @return bool
     */
    public function rmdir($dir)
    {
        //$dir = self::_change_symbol($dir);
        \ftp_chdir($this->_res, '/');

        return @\ftp_rmdir($this->_res, $dir);
    }

    /**
     * iterator.
     *
     * @param string $dir
     *
     * @return array
     */
    public function iterator($dir)
    {
        $tmp = [];

        foreach ($this->_rawlist($dir) as $var) {
            if ('.' !== $var['file']) {
                $tmp[] = Helper_System::basename($var['file']);
            }
        }

        return $tmp;
    }

    /**
     * fileperms.
     *
     * @param string $str
     *
     * @return int
     */
    public function fileperms($str)
    {
        //$str = self::_change_symbol($str);
        //return fileperms($this->_url . $str);

        $rawlist = $this->_rawlist(\dirname($str));

        return $rawlist[Helper_System::basename($str)]['chmod'];
    }

    /**
     * stat.
     *
     * @param string $str
     *
     * @return array
     */
    public function stat($str)
    {
        $rawlist = $this->_rawlist(\dirname($str));

        return $rawlist[Helper_System::basename($str)];
    }

    /**
     * readlink.
     *
     * @param string $link
     *
     * @return array
     */
    public function readlink($link)
    {
        $rawlist = $this->_rawlist(\dirname($link));
        $t1 = $rawlist[Helper_System::basename($link)]['file'];
        $t2 = \explode(' -> ', $t1);
        $t2 = \end($t2);
        if (\PATH_SEPARATOR != $t2[0]) {
            if ('.' == $t2) {
                $t2 = \mb_substr(Registry::get('current'), 0, -1);
            } elseif ('..' == $t2) {
                $t2 = \mb_substr(\strrev(\mb_strstr(\strrev(Registry::get('current')), '/')), 0, -1);
            } else {
                $t2 = ('.' != Registry::get('current') ? Registry::get('current') : '').$t2;
            }
        }

        return [$t1, $t2];
    }

    /**
     * getcwd.
     *
     * @return string
     */
    public function getcwd()
    {
        return \ftp_pwd($this->_res);
    }

    /**
     * realpath.
     *
     * @param string $path
     *
     * @return string
     */
    public function realpath($path)
    {
        //return realpath($path);

        $str = \preg_replace('/\/(?:\/*)/', '/', \preg_replace('/\w+\/\.\.\//', '', \str_replace('\\', '/', $path)));
        if ('/' === \mb_substr($str, -1)) {
            return \mb_substr($str, 0, -1);
        }

        return $str;
    }

    /**
     * rawlist.
     *
     * @param string $dir
     *
     * @return array
     */
    private function _rawlist($dir = '/')
    {
        self::$_dir = $dir = \str_replace('\\', '/', $dir);

        if (isset(self::$_rawlist[self::$_dir])) {
            return self::$_rawlist[self::$_dir];
        } elseif (isset(self::$_rawlist[self::$_dir.'/'])) {
            return self::$_rawlist[self::$_dir.'/'];
        }

        \ftp_chdir($this->_res, '/');
        if (\preg_match('/^[A-Z]+?:[\\*|\/*]+(.*)/', $dir, $match)) {
            $dir = $match[1] ? '/'.$match[1] : '/';
        }

        foreach ((array) \ftp_rawlist($this->_res, '-A /'.$dir) as $var) {
            if (' ..' == \mb_substr($var, -3)) {
                continue;
            }
            \preg_replace_callback(
                '`^(d|l|\-{1}+)(.{9}+)\s*(?:\d{1,3})\s*(\d+?|\w+?)\s*(\d+?|\w+?)\s*(\d*)\s([a-zA-Z]{3}+)\s*([0-9]{1,2}+)\s*([0-9]{2}+):?([0-9]{2}+)\s*(.*)$`U',
                [$this, '_rawlistCallback'],
                $var
            );
        }

        self::$_rawlist[self::$_dir]['.'] = [
            'chmod' => '0',
            'uid' => '',
            'owner' => '',
            'gid' => '',
            'group' => '',
            'size' => '0',
            'mtime' => '',
            'file' => '.',
            'type' => 'dir',
        ];

        return self::$_rawlist[self::$_dir];
    }

    /**
     * rawlistCallback.
     *
     * @param array $data
     */
    private function _rawlistCallback($data)
    {
        $data[10] = \trim($data[10]);

        self::$_rawlist[self::$_dir][Helper_System::basename($data[10])] = [
            'chmod' => 'd' == $data[1] && 'WIN' == Registry::get('sysType') ? 0777 : ('WIN' == Registry::get('sysType') ? 0666 : $this->_chmodNum($data[2])),
            'uid' => $data[3],
            'owner' => \is_numeric($data[3]) ? (self::$_idUser[$data[3]] ?? self::$_idUser[$data[3]] = Helper_System::id2user($data[3])) : $data[3],
            'gid' => $data[4],
            'group' => \is_numeric($data[4]) ? (self::$_idGroup[$data[4]] ?? self::$_idGroup[$data[4]] = Helper_System::id2group($data[4])) : $data[4],
            'size' => $data[5],
            'mtime' => \strtotime($data[6].' '.$data[7].' '.$data[8].':'.$data[9]),
            'file' => $data[10],
            'type' => 'd' == $data[1] ? 'dir' : ('l' == $data[1] ? 'link' : 'file'),
        ];
    }

    /**
     * chmodNum.
     *
     * @param string $perm
     *
     * @return int
     */
    private function _chmodNum($perm = 'rw-r--r--')
    {
        $m = 0;

        if ('r' == $perm[0]) {
            $m += 0400;
        }
        if ('w' == $perm[1]) {
            $m += 0200;
        }
        if ('x' == $perm[2]) {
            $m += 0100;
        } elseif ('s' == $perm[2]) {
            $m += 04100;
        } elseif ('S' == $perm[2]) {
            $m += 04000;
        }

        if ('r' == $perm[3]) {
            $m += 040;
        }
        if ('w' == $perm[4]) {
            $m += 020;
        }
        if ('x' == $perm[5]) {
            $m += 010;
        } elseif ('s' == $perm[5]) {
            $m += 02010;
        } elseif ('S' == $perm[5]) {
            $m += 02000;
        }

        if ('r' == $perm[6]) {
            $m += 04;
        }
        if ('w' == $perm[7]) {
            $m += 02;
        }
        if ('x' == $perm[8]) {
            $m += 01;
        } elseif ('t' == $perm[8]) {
            $m += 01001;
        } elseif ('T' == $perm[8]) {
            $m += 01000;
        }

        return $m;
    }
}
