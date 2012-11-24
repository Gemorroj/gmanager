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


class HTTP extends Gmanager
{
    /**
     * @var array
     */
    static private $_stat   = array();
    /**
     * @var array
     */
    static private $_id     = array();


    /**
     * __construct
     */
    public function __construct ()
    {
        Registry::set('sysType', strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'WIN' : 'NIX');
    }


    /**
     * mkdir
     * 
     * @param string $dir
     * @param int|string $chmod
     * @return bool
     */
    public function mkdir ($dir, $chmod = 0755)
    {
        return @mkdir(IOWrapper::set($dir), $this->_chmoder($chmod), true);
    }


    /**
     * chmod
     * 
     * @param string $file
     * @param int|string $chmod
     * @return bool
     */
    public function chmod ($file, $chmod = 0755)
    {
        if (Registry::get('sysType') == 'WIN') {
            //trigger_error(Language::get('win_chmod'));
            return true;
        }

        return @chmod(IOWrapper::set($file), $this->_chmoder($chmod));
    }


    /**
     * file_get_contents
     * 
     * @param string $file
     * @return string
     */
    public function file_get_contents ($file)
    {
        return file_get_contents(IOWrapper::set($file));
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
        if (!$f = @fopen(IOWrapper::set($file), 'a')) {
            return 0;
        }

        ftruncate($f, 0);

        if ($data != '') {
            fputs($f, $data);
        }

        fclose($f);

        return 1;
    }


    /**
     * is_dir
     * 
     * @param string $str
     * @return bool
     */
    public function is_dir ($str)
    {
        return is_dir(IOWrapper::set($str));
    }


    /**
     * is_file
     * 
     * @param string $str
     * @return bool
     */
    public function is_file ($str)
    {
        return is_file(IOWrapper::set($str));
    }


    /**
     * is_link
     * 
     * @param string $str
     * @return bool
     */
    public function is_link ($str)
    {
        return is_link(IOWrapper::set($str));
    }


    /**
     * is_readable
     * 
     * @param string $str
     * @return bool
     */
    public function is_readable ($str)
    {
        return is_readable(IOWrapper::set($str));
    }


    /**
     * is_writable
     * 
     * @param string $str
     * @return bool
     */
    public function is_writable ($str)
    {
        return is_writable(IOWrapper::set($str));
    }


    /**
     * stat
     * 
     * @param string $str
     * @return array
     */
    public function stat ($str)
    {
        $str = IOWrapper::set($str);

        if (!isset(self::$_stat[$str])) {
            self::$_stat[$str] = @stat($str);
        }

        if (isset(self::$_id[self::$_stat[$str]['uid']])) {
            self::$_stat[$str]['owner'] = self::$_id[self::$_stat[$str]['uid']];
        } else {
            self::$_stat[$str]['owner'] = self::$_id[self::$_stat[$str]['uid']] = Helper_System::id2name(self::$_stat[$str]['uid']);
        }

        if (isset(self::$_id[self::$_stat[$str]['gid']])) {
            self::$_stat[$str]['group'] = self::$_id[self::$_stat[$str]['gid']];
        } else {
            self::$_stat[$str]['group'] = self::$_id[self::$_stat[$str]['gid']] = Helper_System::id2name(self::$_stat[$str]['gid']);
        }

        return self::$_stat[$str];
    }


    /**
     * fileperms
     * 
     * @param string $str
     * @return int
     */
    public function fileperms ($str)
    {
        $str = IOWrapper::set($str);

        if (!isset(self::$_stat[$str][2])) {
            self::$_stat[$str] = @stat($str);
        }
        return self::$_stat[$str][2];
    }


    /**
     * filesize
     * 
     * @param string $file
     * @return int
     */
    public function filesize ($file)
    {
        $file = IOWrapper::set($file);

        if (!isset(self::$_stat[$file][7])) {
            self::$_stat[$file] = stat($file);
        }
        return self::$_stat[$file][7];
    }


    /**
     * filemtime
     * 
     * @param string $str
     * @return int
     */
    public function filemtime ($str)
    {
        $str = IOWrapper::set($str);

        if (!isset(self::$_stat[$str][9])) {
            self::$_stat[$str] = stat($str);
        }
        return self::$_stat[$str][9];
    }


    /**
     * filetype
     * 
     * @param string $str
     * @return string
     */
    public function filetype ($str)
    {
        return filetype(IOWrapper::set($str));
    }


    /**
     * readlink
     * 
     * @link http://www.php.net/manual/ru/function.readlink.php for Windows
     * @param string $link
     * @return array
     */
    public function readlink ($link)
    {
        $chdir = Registry::get('currentType') == 'dir' ? Registry::get('current') : dirname(Registry::get('current'));
        chdir($chdir);
        return array(Helper_System::basename($link), IOWrapper::get(realpath(readlink(IOWrapper::set($link)))));
    }


    /**
     * file_exists
     * 
     * @param string $str
     * @return bool
     */
    public function file_exists ($str)
    {
        return file_exists(IOWrapper::set($str));
    }


    /**
     * unlink
     * 
     * @param string $file
     * @return bool
     */
    public function unlink ($file)
    {
        return unlink(IOWrapper::set($file));
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
        return rename(IOWrapper::set($from), IOWrapper::set($to));
    }


    /**
     * copy
     * 
     * @param string $from
     * @param string $to
     * @param int|string  $chmod
     * @return bool
     */
    public function copy ($from, $to, $chmod = 0644)
    {
        $from = IOWrapper::set($from);
        $to   = IOWrapper::set($to);

        $result = copy($from, $to);
        if ($result) {
            $this->chmod($to, $chmod);
        }

        return $result;
    }


    /**
     * copy
     *
     * @param string $from
     * @param string $to
     * @param int|string  $chmod
     * @return bool
     */
    public function symlink ($from, $to, $chmod = 0644)
    {
        $from = IOWrapper::set($from);
        $to   = IOWrapper::set($to);

        $result = symlink($from, $to);
        if ($result) {
            $this->chmod($to, $chmod);
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
        $dir = IOWrapper::set($dir);

        return is_dir($dir) ? rmdir($dir) : true;
    }


    /**
     * getcwd
     * 
     * @return string
     */
    public function getcwd ()
    {
        return IOWrapper::get(getcwd());
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
     * iterator
     * 
     * @param string $dir
     * @return array
     */
    public function iterator ($dir)
    {
        return array_map(array('IOWrapper', 'get'), (array)array_diff(scandir(IOWrapper::set($dir), 0), array('.', '..')));
    }
}
