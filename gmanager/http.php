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


$GLOBALS['mode'] = new http;
$GLOBALS['class'] = 'http';


class http
{
    static private $stat = array();


    public function mkdir($dir = '', $chmod = '0755')
    {
        settype($chmod, 'string');
        $strlen = strlen($chmod);
        if (!is_numeric($chmod) || ($strlen != 3 && $strlen != 4)) {
            // return false;
            $chmod = '0755';
        }
        if ($strlen == 3) {
            $chmod = '0' . $chmod;
        }

        $chmod = decoct(octdec(intval($chmod)));
        $result = @mkdir($dir, $chmod, true);
        if ($result) {
            $this->chmod($dir, $chmod);
        }
        return $result;
    }


    public function chmod($file = '', $chmod = '0755')
    {
        /*
        $win = PHP_OS;
        if ($win[0] . $win[1] . $win[2] == 'WIN') {
            trigger_error($GLOBALS['lng']['win_chmod']);
            return false;
        }
        */

        settype($chmod, 'string');
        $strlen = strlen($chmod);
        if (!is_numeric($chmod) || ($strlen != 3 && $strlen != 4)) {
            return false;
        }

        if ($strlen == 3) {
            $chmod = '0' . $chmod;
        }

        return @chmod($file, octdec(intval($chmod)));
    }


    public function file_get_contents($str = '')
    {
        return file_get_contents($str);
    }


    public function file_put_contents($file = '', $data = '')
    {
        if (!$f = @fopen($file, 'a')) {
            return 0;
        }

        ftruncate($f, 0);

        if ($data != '') {
            fputs($f, $data);
        }

        fclose($f);

        return 1;
    }


    public function is_dir($str = '')
    {
        return @is_dir($str);
    }


    public function is_file($str = '')
    {
        return is_file($str);
    }


    public function is_link($str = '')
    {
        return is_link($str);
    }


    public function is_readable($str = '')
    {
        return is_readable($str);
    }


    public function is_writable($str = '')
    {
        return is_writable($str);
    }


    public function stat($str = '')
    {
        if (!isset(self::$stat[$str])) {
            self::$stat[$str] = @stat($str);
        }
        if (function_exists('posix_getpwuid') && $uid = @posix_getpwuid(self::$stat[$str][4])) {
            self::$stat[$str][4] = self::$stat[$str]['uid'] = $uid['name'];
        }
        return self::$stat[$str];
    }


    public function fileperms($str = '')
    {
        if (!isset(self::$stat[$str][2])) {
            self::$stat[$str] = @stat($str);
        }
        return self::$stat[$str][2];
        //return fileperms($str);
    }


    public function filesize($str = '')
    {
        if (!isset(self::$stat[$str][7])) {
            self::$stat[$str] = stat($str);
        }
        return self::$stat[$str][7];
        //return sprintf('%u', filesize($str));
    }


    public function filemtime($str = '')
    {
        if (!isset(self::$stat[$str][9])) {
            self::$stat[$str] = stat($str);
        }
        return self::$stat[$str][9];
        //return filemtime($str);
    }


    public function readlink($str = '')
    {
        chdir($GLOBALS['current']);
        return array(
            basename($str),
            realpath(readlink($str))
        );
    }


    public function file_exists($str = '')
    {
        return file_exists($str);
    }


    public function unlink($str = '')
    {
        return unlink($str);
    }


    public function rename($from = '', $to = '')
    {
        return rename($from, $to);
    }


    public function copy($from = '', $to = '', $chmod = '0644')
    {
        if ($result = @copy($from, $to)) {
            $this->chmod($to, $chmod);
        }
        return $result;
    }


    public function rmdir($str = '')
    {
        return rmdir($str);
    }


    public function getcwd()
    {
        return getcwd();
    }


    public function iterator($str = '')
    {
        return array_diff(scandir($str, 0), array('.', '..'));
    }
}

?>
