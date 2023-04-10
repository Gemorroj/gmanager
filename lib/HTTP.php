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
class HTTP extends Gmanager
{
    /**
     * @var array
     */
    private static $_stat = [];
    /**
     * @var array
     */
    private static $_idUser = [];
    /**
     * @var array
     */
    private static $_idGroup = [];

    /**
     * __construct.
     */
    public function __construct()
    {
        $this->_setSysType(\php_uname('s'));
    }

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
        return @\mkdir($dir, $this->_chmoder($chmod), $recursive);
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

        return @\chmod($file, $this->_chmoder($chmod));
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
        return \file_get_contents($file);
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
        if (!$f = @\fopen($file, 'a')) {
            return 0;
        }

        \ftruncate($f, 0);

        if ('' != $data) {
            \fputs($f, $data);
        }

        \fclose($f);

        return 1;
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
        return \is_dir($str);
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
        return \is_file($str);
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
        return \is_link($str);
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
        return \is_readable($str);
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
        return \is_writable($str);
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
        if (!isset(self::$_stat[$str])) {
            self::$_stat[$str] = @\stat($str);
        }

        if (isset(self::$_idUser[self::$_stat[$str]['uid']])) {
            self::$_stat[$str]['owner'] = self::$_idUser[self::$_stat[$str]['uid']];
        } else {
            self::$_stat[$str]['owner'] = self::$_idUser[self::$_stat[$str]['uid']] = Helper_System::id2user(self::$_stat[$str]['uid']);
        }

        if (isset(self::$_idGroup[self::$_stat[$str]['gid']])) {
            self::$_stat[$str]['group'] = self::$_idGroup[self::$_stat[$str]['gid']];
        } else {
            self::$_stat[$str]['group'] = self::$_idGroup[self::$_stat[$str]['gid']] = Helper_System::id2group(self::$_stat[$str]['gid']);
        }

        return self::$_stat[$str];
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
        if (!isset(self::$_stat[$str][2])) {
            self::$_stat[$str] = @\stat($str);
        }

        return self::$_stat[$str][2];
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
        if (!isset(self::$_stat[$file][7])) {
            self::$_stat[$file] = \stat($file);
        }

        return self::$_stat[$file][7];
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
        if (!isset(self::$_stat[$str][9])) {
            self::$_stat[$str] = \stat($str);
        }

        return self::$_stat[$str][9];
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
        return \filetype($str);
    }

    /**
     * readlink.
     *
     * @see http://www.php.net/manual/ru/function.readlink.php for Windows
     *
     * @param string $link
     *
     * @return array
     */
    public function readlink($link)
    {
        $chdir = 'dir' == Registry::get('currentType') ? Registry::get('current') : \dirname(Registry::get('current'));
        \chdir($chdir);

        return [Helper_System::basename($link), (\realpath(\readlink($link)))];
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
        return \file_exists($str);
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
        return \unlink($file);
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
        return \rename($from, $to);
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
        $result = \copy($from, $to);
        if ($result) {
            $this->chmod($to, $chmod);
        }

        return $result;
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
    public function symlink($from, $to, $chmod = 0644)
    {
        $result = \symlink($from, $to);
        if ($result) {
            $this->chmod($to, $chmod);
        }

        return $result;
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
        return \is_dir($dir) ? \rmdir($dir) : true;
    }

    /**
     * getcwd.
     *
     * @return string
     */
    public function getcwd()
    {
        return \getcwd();
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
        return \realpath($path);
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
        return (array) \array_diff(\scandir($dir, 0), ['.', '..']);
    }
}
