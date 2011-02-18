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


class Gmanager extends Config
{
    public  static $pclzipTmp;
    public  static $pclzipF  = 0644;
    public  static $pclzipD  = 0755;
    private static $_ftpArchive;


    /**
     * __constructor
     * 
     * @param void
     * @return void
     */
    public function __construct ()
    {
        parent::__construct();

        if ($_SERVER['QUERY_STRING']) {
            $c = isset($_POST['c']) ? $_POST['c'] : (isset($_GET['c']) ? rawurlencode($_GET['c']) : '');

            if ($c) {
                Config::$current = str_replace('\\', '/', trim(rawurldecode($c)));

                if ($this->is_dir(Config::$current) || $this->is_link(Config::$current)) {
                    if (substr(Config::$current, -1) != '/') {
                        Config::$current .= '/';
                    }
                }
            } else {
                Config::$current = str_replace('\\', '/', trim(rawurldecode($_SERVER['QUERY_STRING'])));
                if ($this->is_dir(Config::$current) || $this->is_link(Config::$current)) {
                    if (substr(Config::$current, -1) != '/') {
                        Config::$current .= '/';
                    }
                }
            }
        } else if (substr(Config::$current, -1) != '/') {
            Config::$current .= '/';
        }

        Config::$hCurrent = htmlspecialchars(Config::$current, ENT_COMPAT);
        Config::$rCurrent = str_replace('%2F', '/', rawurlencode(Config::$current));
    }


    /**
     * sendHeader
     * 
     * @param void
     * @return void
     */
    public function sendHeader ()
    {
        if (stripos(@$_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            header('Content-type: text/html; charset=UTF-8');
        } else {
            header('Content-type: application/xhtml+xml; charset=UTF-8');
        }

        //header('Content-type: text/html; charset=UTF-8');
        header('Cache-control: no-cache');

        // кол-во файлов на странице
        $ip = isset($_POST['limit']);
        $ig = isset($_GET['limit']);
        Config::$limit = abs($ip ? $_POST['limit'] : ($ig ? $_GET['limit'] : (isset($_COOKIE['gmanager_limit']) ? $_COOKIE['gmanager_limit'] : Config::$limit)));

        if ($ip || $ig) {
            setcookie('gmanager_limit', Config::$limit, 2592000 + $_SERVER['REQUEST_TIME'], str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), $_SERVER['HTTP_HOST']);
        }
    }


    /**
     * head
     * 
     * @param void
     * @return string
     */
    public function head ()
    {
        if (Config::$mode != 'FTP') {
            $realpath = $this->realpath(Config::$current);
            $realpath = $realpath ? $realpath : Config::$current;
        } else {
            $realpath = Config::$current;
        }
        $chmod = $this->lookChmod(Config::$current);
        $chmod = $chmod ? $chmod : (isset($_POST['chmod'][0]) ? htmlspecialchars($_POST['chmod'][0], ENT_NOQUOTES) : (isset($_POST['chmod']) ? htmlspecialchars($_POST['chmod'], ENT_NOQUOTES) : 0));
    
        $d = dirname(str_replace('\\', '/', $realpath));
        $archive = $this->isArchive($this->getType(basename(Config::$current)));

        if ($this->is_dir(Config::$current) || $this->is_link(Config::$current)) {
            if (Config::$current == '.') {
                return '<div class="border">' . Language::get('dir') . ' <a href="index.php">' . htmlspecialchars($this->strLink($this->getcwd()), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($this->getcwd()) . ')<br/></div>';
            } else {
                return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . $d . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . Config::$rCurrent . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
            }
        } else if ($this->is_file(Config::$current) && $archive) {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($this->strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . htmlspecialchars($this->strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="index.php?' . Config::$rCurrent . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
        } else {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($this->strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . htmlspecialchars($this->strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="edit.php?' . Config::$rCurrent . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
        }
    }


    /**
     * langJS
     * 
     * @param void
     * @return string
     */
    public function langJS ()
    {
        return '<div style="display:none;"><span id="chF">' . Language::get('check_form') . '</span><span id="delN">' . Language::get('del_notify') . '</span></div>';
    }


    /**
     * staticName
     * 
     * @param string $current
     * @param string $dest
     * @return string
     */
    public function staticName ($current = '', $dest = '')
    {
        $substr = 'iconv_substr';
        if (!$len = iconv_strlen($current)) {
            $len = strlen($current);
            $substr = 'substr';
        }

        if ($substr($dest, 0, $len) == $current) {
            $static = $substr($dest, $len);
    
            if (strpos($static, '/')) {
                $static = strtok($static, '/');
            }
        } else {
            return '';
        }
        return $static;
    }


    /**
     * look
     * 
     * @param string $current
     * @param string $itype
     * @param string $down
     * @return string
     */
    public function look ($current = '', $itype = '', $down = '')
    {
        if (!$this->is_dir($current) || !$this->is_readable($current)) {
            return ListData::getListDenyData();
        }

        $add  = (isset($_GET['add_archive']) ? $_GET['add_archive'] : '');
        $pg   = (isset($_GET['pg']) ? $_GET['pg'] : 1);
        $html = ListData::getListData($this, $current, $itype, $down, $pg, $add);
        if ($html) {
            if ($itype == 'time') {
                $out = '&amp;time';
            } else if ($itype == 'type') {
                $out = '&amp;type';
            } else if ($itype == 'size') {
                $out = '&amp;size';
            } else if ($itype == 'chmod') {
                $out = '&amp;chmod';
            } else if ($itype == 'uid') {
                $out = '&amp;uid';
            } else if ($itype == 'gid') {
                $out = '&amp;gid';
            } else {
                $out = '';
            }
            $out .= $down ? '&amp;down' : '&amp;up';

            return $html . Paginator::get($pg, ListData::$getListCountPages, '&amp;c=' . $current . $out . $add);
        } else {
            return ListData::getListEmptyData();
        }
    }


    /**
     * copyD
     *
     * @param string $source
     * @param string $to
     * @return void
     */
    public function copyD ($source = '', $to = '')
    {
        $ex = explode('/', $source);
        $tmp1 = $tmp2 = '';

        foreach (explode('/', $to) as $var) {
            $ch = each($ex);
            $tmp1 .= $var . '/';
            $tmp2 .= $ch[1] . '/';

            if (!$this->is_dir($tmp1)) {
                $this->mkdir($tmp1, $this->lookChmod($tmp2));
            }
        }
    }


    /**
     * copyFiles
     * 
     * @param string $d
     * @param string $dest
     * @param string $static
     * @param bool   $overwrite
     * @return string
     */
    public function copyFiles ($d = '', $dest = '', $static = '', $overwrite = false)
    {
        $error = array();

        foreach ($this->iterator($d) as $file) {
            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if ($this->is_dir($d . '/' . $file)) {

                if ($this->mkdir($dest . '/' . $file, $ch)) {
                    $this->chmod($dest, $ch);
                    $this->copyFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !$this->file_exists($dest . '/' . $file)) {
                    if (!$this->copy($d . '/' . $file, $dest . '/' . $file, $ch)) {
                        $error[] = str_replace('%file%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_file_false')) . ' (' . Errors::get() . ')';
                    }
                } else {
                    $error[] = Language::get('overwrite_false') . ' (' . htmlspecialchars($dest . '/' . $file, ENT_NOQUOTES) . ')';
                }

            }
        }

        if ($error) {
            return Errors::message(implode('<br/>', $error), Errors::MESSAGE_EMAIL);
        } else {
            return Errors::message(str_replace('%title%', htmlspecialchars($dest, ENT_NOQUOTES), Language::get('copy_files_true')), Errors::MESSAGE_OK);
        }
    }


    /**
     * moveFiles
     * 
     * @param string $d
     * @param string $dest
     * @param string $static
     * @param bool   $overwrite
     * @return string
     */
    public function moveFiles ($d = '', $dest = '', $static = '', $overwrite = false)
    {
        $error = array();

        foreach ($this->iterator($d) as $file) {
            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if ($this->is_dir($d . '/' . $file)) {

                if ($this->mkdir($dest . '/' . $file, $ch)) {
                    $this->chmod($dest . '/' . $file, $ch);
                    $this->moveFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                    $this->rmdir($d . '/' . $file);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('move_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !$this->file_exists($dest . '/' . $file)) {
                    if ($this->rename($d . '/' . $file, $dest . '/' . $file)) {
                        $this->chmod($dest . '/' . $file, $ch);
                    } else {
                        $error[] = str_replace('%file%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('move_file_false')) . ' (' . Errors::get() . ')';
                    }
                } else {
                    $error[] = Language::get('overwrite_false') . ' (' . htmlspecialchars($dest . '/' . $file, ENT_NOQUOTES) . ')';
                }

            }
        }

        if ($error) {
            return Errors::message(implode('<br/>', $error), Errors::MESSAGE_EMAIL);
        } else {
            $this->rmdir($d);
            return Errors::message(str_replace('%title%', htmlspecialchars($dest, ENT_NOQUOTES), Language::get('move_files_true')), Errors::MESSAGE_OK);
        }
    }


    /**
     * copyFile
     * 
     * @param string $source
     * @param string $dest
     * @param mixed  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function copyFile ($source = '', $dest = '', $chmod = '', $overwrite = false)
    {
        if (!$overwrite && $this->file_exists($dest)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($dest, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if ($source == $dest) {
            if ($chmod) {
                $this->rechmod($dest, $chmod);
            }
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Errors::MESSAGE_OK);
        }

        $this->copyD(dirname($source), dirname($dest));

        if ($this->copy($source, $dest)) {
            if (!$chmod) {
                $chmod = $this->lookChmod($source);
            }
            $this->rechmod($dest, $chmod);

            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('copy_file_true'))), Errors::MESSAGE_OK);
        } else {
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('copy_file_false'))) . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * moveFile
     * 
     * @param string $source
     * @param string $dest
     * @param mixed  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function moveFile ($source = '', $dest = '', $chmod = '', $overwrite = false)
    {
        if (!$overwrite && $this->file_exists($dest)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($dest, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if ($source == $dest) {
            if ($chmod) {
                $this->rechmod($dest, $chmod);
            }
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Errors::MESSAGE_OK);
        }

        $this->copyD(dirname($source), dirname($dest));

        if ($this->rename($source, $dest)) {
            if (!$chmod) {
                $chmod = $this->lookChmod($source);
            }
            $this->rechmod($dest, $chmod);

            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Errors::MESSAGE_OK);
        } else {
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_false'))) . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * delFile
     * 
     * @param string $f
     * @return string
     */
    public function delFile ($f = '')
    {
        if ($this->unlink($f)) {
            return Errors::message(Language::get('del_file_true') . ' -&gt; ' . htmlspecialchars($f, ENT_NOQUOTES), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('del_file_false') . ' -&gt; ' . htmlspecialchars($f, ENT_NOQUOTES) . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * delDir
     * 
     * @param string $d
     * @return string
     */
    public function delDir ($d = '')
    {
        $err = '';
        $this->chmod($d, 0777);

        foreach ($this->iterator($d) as $f) {
            $realpath = $this->realpath($d . '/' . $f);
            $f = $realpath ? str_replace('\\', '/', $realpath) : str_replace('//', '/', $d . '/' . $f);
            $this->chmod($f, 0777);

            if ($this->is_dir($f) /*&& !$this->rmdir($f)*/) {
                $this->delDir($f . '/');
                $this->rmdir($f);
            } else if ($this->file_exists($f)) {
                if (!$this->unlink($f)) {
                    $err .= $f . '<br/>';
                }
            }
        }

        if (!$this->rmdir($d)) {
            $err .= Errors::get() . '<br/>';
        }
        if ($err) {
            return Errors::message(Language::get('del_dir_false') . '<br/>' . $err, Errors::MESSAGE_FAIL);
        }
        return Errors::message(Language::get('del_dir_true') . ' -&gt; ' . htmlspecialchars($d, ENT_NOQUOTES), Errors::MESSAGE_OK);
    }


    /**
     * size
     * 
     * @param string $source
     * @param bool   $is_dir
     * @return string
     */
    public function size ($source = '', $is_dir = false)
    {
        if ($is_dir) {
            $ds = array($source);
            $sz = 0;
            do {
                $d = array_shift($ds);
                foreach ($this->iterator($d) as $file) {
                    if ($this->is_dir($d . '/' . $file)) {
                        $ds[] = $d . '/' . $file;
                    } else {
                        $sz += $this->filesize($d . '/' . $file);
                    }
                }
            } while (sizeof($ds) > 0);

            return $sz;
        }

        return $this->filesize($source);
    }


    /**
     * formatSize
     * 
     * @param mixed $size
     * @param int   $int
     * @return string
     */
    public function formatSize ($size = false, $int = 2) {
        if ($size === false) {
            return Language::get('unknown');
        } else if ($size < 1024) {
            return $size . ' Byte';
        } else if ($size < 1048576) {
            return round($size / 1024, $int) . ' Kb';
        } else if ($size < 1073741824) {
            return round($size / 1048576, $int) . ' Mb';
        } else {
            return round($size / 1073741824, $int) . ' Gb';
        }
    }


    /**
     * lookChmod
     * 
     * @param string $file
     * @return string
     */
    public function lookChmod ($file = '')
    {
        return substr(sprintf('%o', $this->fileperms($file)), -4);
    }


    /**
     * createFile
     * 
     * @param string $file
     * @param string $text
     * @param mixed  $chmod
     * @return string
     */
    public function createFile ($file = '', $text = '', $chmod = 0644)
    {
        $this->createDir(dirname($file));

        if ($this->file_put_contents($file, $text)) {
            return Errors::message(Language::get('fputs_file_true'), Errors::MESSAGE_OK) . $this->rechmod($file, $chmod);
        } else {
            return Errors::message(Language::get('fputs_file_false') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * createDir
     * 
     * @param string $dir
     * @param mixed  $chmod
     * @return string
     */
    public function createDir ($dir = '', $chmod = 0755)
    {
        $tmp = $tmp2 = $err = '';
        $i = 0;
        $g = explode(DIRECTORY_SEPARATOR, $this->getcwd());

        foreach (explode('/', $dir) as $d) {
            $tmp .= $d . '/';
            if (isset($g[$i])) {
                $tmp2 .= $g[$i] . '/';
            }

            if ($tmp == $tmp2 || $this->is_dir($tmp)) {
                $i++;
                continue;
            }
            if (!$this->mkdir($tmp, $chmod)) {
                $err .= Errors::get() . ' -&gt; ' . htmlspecialchars($tmp, ENT_NOQUOTES) . '<br/>';
            }
            $i++;
        }

        if ($err) {
            return Errors::message(Language::get('create_dir_false') . '<br/>' . $err, Errors::MESSAGE_EMAIL);
        } else {
            return Errors::message(Language::get('create_dir_true'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * rechmod
     * 
     * @param string $current
     * @param mixed  $chmod
     * @return string
     */
    public function rechmod ($current = '', $chmod = 0755)
    {
        $len = strlen($chmod);

        if (($len != 3 && $len != 4) || !is_numeric($chmod)) {
            return Errors::message(Language::get('chmod_mode_false'), Errors::MESSAGE_EMAIL);
        }

        if ($this->chmod($current, $chmod)) {
            return Errors::message(Language::get('chmod_true') . ' -&gt; ' . htmlspecialchars($current, ENT_NOQUOTES) . ' : ' . (is_int($chmod) ? decoct($chmod) : $chmod), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('chmod_false') . ' -&gt; ' . htmlspecialchars($current, ENT_NOQUOTES) . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * frename
     * 
     * @param string $current
     * @param string $name
     * @param mixed  $chmod
     * @param bool   $del
     * @param string $to
     * @param bool   $overwrite
     * @return string
     */
    public function frename ($current = '', $name = '', $chmod = '', $del = false, $to = '', $overwrite = false)
    {
        if ($this->is_dir($current)) {
            $this->copyD($current, $to);

            if ($del) {
                return $this->moveFiles($current, $name, $this->staticName($current, $name), $overwrite);
            } else {
                return $this->copyFiles($current, $name, $this->staticName($current, $name), $overwrite);
            }
        } else {
            if ($del) {
                return $this->moveFile($current, $name, $chmod, $overwrite);
            } else {
                return $this->copyFile($current, $name, $chmod, $overwrite);
            }
        }
    }


    /**
     * syntax
     * 
     * @param string $current
     * @param array  $charset
     * @return string
     */
    public function syntax ($current = '', $charset = array())
    {
        if (!$this->is_file($current)) {
            return Errors::message(Language::get('not_found'), Errors::MESSAGE_EMAIL);
        }

        exec(escapeshellcmd(Config::$php) . ' -c -f -l ' . escapeshellarg($current), $rt, $v);
        $error = Errors::get();
        $size = sizeof($rt);

        if (!$size) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . $error, Errors::MESSAGE_EMAIL);
        }

        $erl = false;
        if ($v == 255 || $size > 2) {
            if ($st = trim(strip_tags($rt[1]))) {
                $erl = preg_replace('/.*\s(\d*)$/', '$1', $st, 1);
                $pg = $st;
            } else {
                $pg = Language::get('syntax_unknown');
            }
        } else {
            $pg = Language::get('syntax_true');
        }

        $fl = trim($this->file_get_contents($current));
        if ($charset[0]) {
            $fl = iconv($charset[0], $charset[1] . '//TRANSLIT', $fl);
        }

        return Errors::message($pg, $erl ? Errors::MESSAGE_FAIL : Errors::MESSAGE_OK) . $this->code($fl, $erl);
    }


    /**
     * syntax2
     * 
     * @param string $current
     * @param array  $charset
     * @return string
     */
    public function syntax2 ($current = '', $charset = array())
    {
        if (!$charset[0]) {
            $charset[0] = 'UTF-8';
        }
        $fp = fsockopen('wapinet.ru', 80, $er1, $er2, 10);
        if (!$fp) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Errors::MESSAGE_FAIL);
        }

        $f = rawurlencode(trim($this->file_get_contents($current)));

        fputs($fp, 'POST /syntax2/index.php HTTP/1.0' . "\r\n" .
            'Content-type: application/x-www-form-urlencoded; charset=' . $charset[0] . "\r\n" .
            'Content-length: ' . (iconv_strlen($f) + 2) . "\r\n" .
            'Host: wapinet.ru' . "\r\n" .
            'Connection: close' . "\r\n" .
            'User-Agent: GManager ' . Config::$version . "\r\n\r\n" .
            'f=' . $f . "\r\n\r\n");

        $r = '';
        while ($r != "\r\n") {
            $r = fgets($fp);
        }
        $r = '';
        while (!feof($fp)) {
            $r .= fread($fp, 1024);
        }
        fclose($fp);
        return trim($r);
    }


    /**
     * zipSyntax
     * 
     * @param string $current
     * @param string $f
     * @param array  $charset
     * @return string
     */
    public function zipSyntax ($current = '', $f = '', $charset = array())
    {
        $content = $this->editZipFile($current, $f);

        $tmp = Config::$temp . '/GmanagerSyntax' . $_SERVER['REQUEST_TIME'] . '.tmp';
        $fp = fopen($tmp, 'w');

        if (!$fp) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Errors::MESSAGE_FAIL);
        }

        fputs($fp, $content['text']);
        fclose($fp);

        if (Config::$syntax) {
            $pg = $this->syntax2($tmp, $charset);
        } else {
            $pg = $this->syntax($tmp, $charset);
        }
        unlink($tmp);

        return $pg;
    }


    /**
     * beautify
     * 
     * @param string $str
     * @return string
     */
    public function beautify ($str)
    {
        return Beautifier_PHP::beautify($str);
    }


    /**
     * validator
     * 
     * @param string $current
     * @param array  $charset
     * @return string
     */
    public function validator ($current = '', $charset = array())
    {
        if (!extension_loaded('xml')) {
            return Errors::message(Language::get('disable_function') . ' (xml)', Errors::MESSAGE_FAIL);
        }

        $fl = $this->file_get_contents($current);
        if ($charset[0]) {
            $fl = iconv($charset[0], $charset[1] . '//TRANSLIT', $fl);
        }

        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $fl)) {
            $err = xml_error_string(xml_get_error_code($xml_parser));
            $line = xml_get_current_line_number($xml_parser);
            $column = xml_get_current_column_number($xml_parser);
            xml_parser_free($xml_parser);
            return Errors::message('Error [Line ' . $line . ', Column ' . $column . ']: ' . $err, Errors::MESSAGE_FAIL) . $this->code($fl, $line);
        } else {
            xml_parser_free($xml_parser);
            return Errors::message(Language::get('validator_true'), Errors::MESSAGE_OK) . $this->code($fl);
        }
    }


    /**
     * xhtmlHighlight
     * 
     * @param string $fl
     * @return string
     */
    public function xhtmlHighlight ($fl = '')
    {
        return str_replace(array('&nbsp;', '<code>', '</code>'), array('&#160;', '', ''), preg_replace('#color="(.*?)"#', 'style="color: $1"', str_replace(array('<font ', '</font>'), array('<span ', '</span>'), highlight_string($fl, true))));
    }


    /**
     * urlHighlight
     * 
     * @param string $fl
     * @return string
     */
    public function urlHighlight ($fl = '')
    {
        return '<code>' . nl2br(
            preg_replace('/(&quot;|&#039;)[^<>]*(&quot;|&#039;)/iU', '<span style="color:#DD0000">$0</span>',
                preg_replace('/&lt;!--.*--&gt;/iU', '<span style="color:#FF8000">$0</span>',
                    preg_replace('/(&lt;[^\s!]*\s)([^<>]*)([\/?]?&gt;)/iU', '$1<span style="color:#007700">$2</span>$3',
                        preg_replace('/&lt;[^<>]*&gt;/iU', '<span style="color:#0000BB">$0</span>', htmlspecialchars($fl, ENT_QUOTES))
                    )
                )
            )
        ) . '</code>';
    }


    /**
     * code
     * 
     * @param string $fl
     * @param int    $line
     * @param bool   $url
     * @return string
     */
    public function code ($fl = '', $line = 0, $url = false)
    {
        $array = explode('<br />', $url ? $this->urlHighlight($fl) : $this->xhtmlHighlight($fl));
        $all = sizeof($array);
        $len = strlen($all);
        $pg = '';
        for ($i = 0; $i < $all; ++$i) {
            $next = $i + 1;
            $l = strlen($next);
            $pg .= '<span class="' . ($line == $next ? 'fail_code' : 'true_code') . '">' . ($l < $len ? str_repeat('&#160;', $len - $l) : '') . $next . '</span> ' . $array[$i] . '<br/>';
        }
    
        return '<div class="code"><code>' . $pg . '</code></div>';
    }


    /**
     * renameZipFile
     * 
     * @param string $current
     * @param string $name
     * @param string $arch_name
     * @param bool   $del
     * @param bool   $overwrite
     * @return string
     */
    public function renameZipFile ($current, $name, $arch_name, $del = false, $overwrite = false)
    {
        $tmp        = Config::$temp . '/GmanagerZip' . $_SERVER['REQUEST_TIME'];
        $zip        = $this->_pclZip($current);
        $folder     = '';
        $sysName    = IOWrapper::set($name);

        foreach ($zip->extract(PCLZIP_OPT_PATH, $tmp) as $f) {
            if ($f['status'] != 'ok') {
                $this->clean($tmp);
                if (Config::$mode == 'FTP') {
                    $this->ftpArchiveEnd();
                }
                return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL);
                break;
            }
            if ($arch_name == $f['stored_filename']) {
                $folder = $f['folder'];
            }
        }

        if (file_exists($tmp . '/' . $sysName)) {
            if ($overwrite) {
                if ($folder) {
                    $this->clean($tmp . '/' . $sysName);
                } else {
                    unlink($tmp . '/' . $sysName);
                }
            } else {
                $this->clean($tmp);
                if (Config::$mode == 'FTP') {
                    $this->ftpArchiveEnd();
                }
                return Errors::message(Language::get('overwrite_false'), Errors::MESSAGE_FAIL);
            }
        }

        if ($folder) {
            @mkdir($tmp . '/' . $sysName, 0755, true);
        } else if (!is_dir($tmp . '/' . dirname($sysName))) {
            @mkdir($tmp . '/' . dirname($sysName), 0755, true);
        }


        if ($folder) {
            // переделать на ftp
            if ($del) {
                $result = $this->moveFiles($tmp . '/' . $arch_name, $tmp . '/' . $name);
            } else {
                $result = $this->copyFiles($tmp . '/' . $arch_name, $tmp . '/' . $name);
            }
        } else {
            if ($del) {
                $result = rename($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            } else {
                $result = copy($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            }
        }

        if (!$result) {
            $this->clean($tmp);
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Errors::MESSAGE_FAIL);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Errors::MESSAGE_FAIL);
                }
            }
        }

        $result = $zip->create($tmp, PCLZIP_OPT_REMOVE_PATH, iconv_substr($tmp, iconv_strlen(dirname(dirname($tmp)))));

        $this->clean($tmp);
        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd($current);
        }

        if ($result) {
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_true')), Errors::MESSAGE_OK);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_true')), Errors::MESSAGE_OK);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_true')), Errors::MESSAGE_OK);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_true')), Errors::MESSAGE_OK);
                }
            }
        } else {
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Errors::MESSAGE_FAIL);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Errors::MESSAGE_FAIL);
                }
            }
        }
    }


    /**
     * renameTarFile
     * 
     * @param string $current
     * @param string $name
     * @param bool   $del
     * @param bool   $overwrite
     * @return string
     */
    public function renameTarFile ($current, $name, $arch_name, $del = false, $overwrite = false)
    {
        $tmp        = Config::$temp . '/GmanagerTar' . $_SERVER['REQUEST_TIME'];
        $tgz        = $this->_archiveTar($current);
        $sysName    = IOWrapper::set($name);

        $folder = '';
        foreach($tgz->listContent() as $f) {
            if ($arch_name == $f['filename']) {
                $folder = $f['typeflag'] == 5 ? 1 : 0;
                break;
            }
        }

        if (!$tgz->extract($tmp)) {
            $this->clean($tmp);
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL);
        }

        if (file_exists($tmp . '/' . $sysName)) {
            if ($overwrite) {
                if ($folder) {
                    $this->clean($tmp . '/' . $sysName);
                } else {
                    unlink($tmp . '/' . $sysName);
                }
            } else {
                $this->clean($tmp);
                if (Config::$mode == 'FTP') {
                    $this->ftpArchiveEnd();
                }
                return Errors::message(Language::get('overwrite_false'), Errors::MESSAGE_FAIL);
            }
        }

        if ($folder) {
            @mkdir($tmp . '/' . $sysName, 0755, true);
        } else {
            @mkdir($tmp . '/' . dirname($sysName), 0755, true);
        }

        if ($folder) {
            // переделать на ftp
            if ($del) {
                $result = $this->moveFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            } else {
                $result = $this->copyFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            }
        } else {
            if ($del) {
                $result = rename($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            } else {
                $result = copy($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            }
        }

        if (!$result) {
            $this->clean($tmp);
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Errors::MESSAGE_FAIL);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Errors::MESSAGE_FAIL);
                }
            }
        }

        $result = $tgz->createModify($tmp, '.', $tmp);

        $this->clean($tmp);
        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd($current);
        }

        if ($result) {
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_true')), Errors::MESSAGE_OK);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_true')), Errors::MESSAGE_OK);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_true')), Errors::MESSAGE_OK);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_true')), Errors::MESSAGE_OK);
                }
            }
        } else {
            if ($folder) {
                if ($del) {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Errors::MESSAGE_FAIL);
                }
            } else {
                if ($del) {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Errors::MESSAGE_FAIL);
                } else {
                    return Errors::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Errors::MESSAGE_FAIL);
                }
            }
        }
    }


    /**
     * listZipArchive
     * 
     * @param string $current
     * @param string $down
     * @return string
     */
    public function listZipArchive ($current = '', $down = '')
    {
        //TODO: refactoring to ListData
        $r_current = str_replace('%2F', '/', rawurlencode($current));

        $zip = $this->_pclZip($current);
        $list = $zip->listContent();

        if (!$list) {
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::$index) + 1) . '">' . Errors::message(Language::get('archive_error') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL) . '</td></tr>';
        } else {
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                $r_name = str_replace('%2F', '/', rawurlencode($list[$i]['filename']));

                if ($list[$i]['folder']) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]['filename'], ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars($this->getType($list[$i]['filename']), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars($this->strLink($list[$i]['filename'], true), ENT_NOQUOTES) . '</a>';
                    $size = $this->formatSize($list[$i]['size']);
                    $down = '<a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('get') . '</a>';
                }

                $l .= '<tr class="border"><td class="check"><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
                if (Config::$index['name']) {
                    $l .= '<td>' . $name . '</td>';
                }
                if (Config::$index['down']) {
                    $l .= '<td>' . $down . '</td>';
                }
                if (Config::$index['type']) {
                    $l .= '<td>' . $type . '</td>';
                }
                if (Config::$index['size']) {
                    $l .= '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $l .= '<td><a href="change.php?c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::$index['del']) {
                    $l .= '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del_zip_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('dl') . '</a></td>';
                }
                if (Config::$index['chmod']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['date']) {
                    $l .= '<td>' . strftime(Config::$date_format, $list[$i]['mtime']) . '</td>';
                }
                if (Config::$index['uid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['gid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['n']) {
                    $l .= '<td>' . ($i + 1) . '</td>';
                }

                $l .= '</tr>';
            }

            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }

            $prop = $zip->properties();
            if (isset($prop['comment']) && $prop['comment'] != '') {
                if (iconv('UTF-8', 'UTF-8', $prop['comment']) != $prop['comment']) {
                    $prop['comment'] = iconv(Config::$altencoding, 'UTF-8//TRANSLIT', $prop['comment']);
                }
                $l .= '<tr class="border"><td>' . Language::get('comment_archive') . '</td><td colspan="' . (array_sum(Config::$index) + 1) . '"><pre>' . htmlspecialchars($prop['comment'], ENT_NOQUOTES) . '</pre></td></tr>';
            }

            return $l;
        }
    }


    /**
     * listRarArchive
     * 
     * @param string $current
     * @param string $down
     * @return string
     */
    public function listRarArchive ($current = '', $down = '')
    {
        $r_current = str_replace('%2F', '/', rawurlencode($current));
        $rar = $this->_rarOpen($current);

        if (!$list = rar_list($rar)) {
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::$index) + 1) . '">' . Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL) . '</td></tr>';
        } else {
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {

                $r_name = str_replace('%2F', '/', rawurlencode($list[$i]->getName()));

                if (!$list[$i]->getCrc()) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]->getName(), ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars($this->getType($list[$i]->getName()), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars($this->strLink($list[$i]->getName(), true), ENT_NOQUOTES) . '</a>';
                    $size = $this->formatSize($list[$i]->getUnpackedSize());
                    $down = '<a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('get') . '</a>';
                }

                $l .= '<tr class="border"><td class="check"><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
                if (Config::$index['name']) {
                    $l .= '<td>' . $name . '</td>';
                }
                if (Config::$index['down']) {
                    $l .= '<td>' . $down . '</td>';
                }
                if (Config::$index['type']) {
                    $l .= '<td>' . $type . '</td>';
                }
                if (Config::$index['size']) {
                    $l .= '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['del']) {
                    $l .= '<td>' . Language::get('dl') . '</td>';
                }
                if (Config::$index['chmod']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['date']) {
                    $l .= '<td>' . strftime(Config::$date_format, strtotime($list[$i]->getFileTime())) . '</td>';
                }
                if (Config::$index['uid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['gid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['n']) {
                    $l .= '<td>' . ($i + 1) . '</td>';
                }

                $l .= '</tr>';
            }

            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }

            return $l;
        }
    }


    /**
     * listTarArchive
     * 
     * @param string $current
     * @param string $down
     * @return string
     */
    public function listTarArchive ($current = '', $down = '')
    {
        $tgz = $this->_archiveTar($current);

        if (!$list = $tgz->listContent()) {
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::$index) + 1) . '">' . Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL) . '</td></tr>';
        } else {
            $r_current = str_replace('%2F', '/', rawurlencode($current));
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                $r_name = rawurlencode($list[$i]['filename']);
    
                if ($list[$i]['typeflag']) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]['filename'], ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars($this->getType($list[$i]['filename']), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars($this->strLink($list[$i]['filename'], true), ENT_NOQUOTES) . '</a>';
                    $size = $this->formatSize($list[$i]['size']);
                    $down = '<a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('get') . '</a>';
                }
                $l .= '<tr class="border"><td class="check"><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
                if (Config::$index['name']) {
                    $l .= '<td>' . $name . '</td>';
                }
                if (Config::$index['down']) {
                    $l .= '<td>' . $down . '</td>';
                }
                if (Config::$index['type']) {
                    $l .= '<td>' . $type . '</td>';
                }
                if (Config::$index['size']) {
                    $l .= '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $l .= '<td><a href="change.php?c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::$index['del']) {
                    $l .= '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del_tar_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('dl') . '</a></td>';
                }
                if (Config::$index['chmod']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['date']) {
                    $l .= '<td>' . strftime(Config::$date_format, $list[$i]['mtime']) . '</td>';
                }
                if (Config::$index['uid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['gid']) {
                    $l .= '<td> </td>';
                }
                if (Config::$index['n']) {
                    $l .= '<td>' . ($i + 1) . '</td>';
                }

                $l .= '</tr>';
            }

            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd();
            }

            return $l;
        }
    }


    /**
     * editZipFile
     * 
     * @param string $current
     * @param string $f
     * @return string
     */
    public function editZipFile ($current = '', $f = '')
    {
        $zip = $this->_pclZip($current);
        $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd('');
        }

        if (!$ext) {
            return array('text' => Language::get('archive_error'), 'size' => 0, 'lines' => 0);
        } else {
            return array('text' => trim($ext[0]['content']), 'size' => $this->formatSize($ext[0]['size']), 'lines' => sizeof(explode("\n", $ext[0]['content'])));
        }
    }


    /**
     * editZipFileOk
     * 
     * @param string $current
     * @param string $f
     * @param string $text
     * @return string
     */
    public function editZipFileOk ($current = '', $f = '', $text = '')
    {
        self::$pclzipTmp = $f;
        $tmp = Config::$temp . '/GmanagerArchivers' . $_SERVER['REQUEST_TIME'] . '.tmp';

        $fp = fopen($tmp, 'w');

        if (!$fp) {
            return Errors::message(Language::get('fputs_file_false') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }

        fputs($fp, $text);
        fclose($fp);

        $zip = $this->_pclZip($current);
        $comment = $zip->properties();
        $comment = $comment['comment'];

        if ($zip->delete(PCLZIP_OPT_BY_NAME, $f) == 0) {
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }
            unlink($tmp);
            return Errors::message(Language::get('fputs_file_false') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL);
        }


        function pclzip_pre_add ($p_event, &$p_header)
        {
            $p_header['stored_filename'] = Gmanager::$pclzipTmp;
            return 1;
        }

        $fl = $zip->add($tmp, PCLZIP_CB_PRE_ADD, 'pclzip_pre_add', PCLZIP_OPT_COMMENT, $comment);

        unlink($tmp);
        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd($current);
        }

        if ($fl) {
            return Errors::message(Language::get('fputs_file_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('fputs_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * lookZipFile
     * 
     * @param string $current
     * @param string $f
     * @param string $str
     * @return string
     */
    public function lookZipFile ($current = '', $f = '', $str = false)
    {
        $r_current = str_replace('%2F', '/', rawurlencode($current));
        $r_f = str_replace('%2F', '/', rawurlencode($f));

        $zip = $this->_pclZip($current);
        $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd('');
        }

        if (!$ext) {
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        } else if ($ext[0]['status'] == 'unsupported_encryption') {
            return Errors::message(Language::get('archive_error_encrypt'), Errors::MESSAGE_EMAIL);
        } else {
            if ($str) {
                return $ext[0]['content'];
            } else {
                return Errors::message(Language::get('archive_size') . ': ' . $this->formatSize($ext[0]['compressed_size']) . '<br/>' . Language::get('real_size') . ': ' . $this->formatSize($ext[0]['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::$date_format, $ext[0]['mtime']) . '<br/>&#187;<a href="edit.php?c=' . $r_current . '&amp;f=' . $r_f . '">' . Language::get('edit') . '</a>', Errors::MESSAGE_OK) . $this->code(trim($ext[0]['content']));
            }
        }
    }


    /**
     * lookRarFile
     * 
     * @param string $current
     * @param string $f
     * @param string $str
     * @return string
     */
    public function lookRarFile ($current = '', $f = '', $str = false)
    {
        $rar = $this->_rarOpen($current);
        $entry = rar_entry_get($rar, $f);

        // создаем временный файл
        $tmp = Config::$temp . '/GmanagerRAR' . $_SERVER['REQUEST_TIME'] . '.tmp';
        $entry->extract(true, $tmp); // запишет сюда данные

        $ext = file_get_contents($tmp);
        unlink($tmp);

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd('');
        }

        if (!$ext) {
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        } else {
            if ($str) {
                return $ext;
            } else {
                return Errors::message(Language::get('archive_size') . ': ' . $this->formatSize($entry->getPackedSize()) . '<br/>' . Language::get('real_size') . ': ' . $this->formatSize($entry->getUnpackedSize()) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::$date_format, strtotime($entry->getFileTime())), Errors::MESSAGE_OK) . $this->code(trim($ext));
            }
        }
    }


    /**
     * lookTarFile
     * 
     * @param string $current
     * @param string $f
     * @param string $str
     * @return string
     */
    public function lookTarFile ($current = '', $f = '', $str = false)
    {
        $tgz = $this->_archiveTar($current);
        $ext = $tgz->extractInString($f);

        if (!$ext) {
            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        } else {
            $list = $tgz->listContent();

            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                if ($list[$i]['filename'] != $f) {
                    continue;
                } else {
                    if ($str) {
                        return $ext;
                    } else {
                        return Errors::message(Language::get('real_size') . ': ' . $this->formatSize($list[$i]['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::$date_format, $list[$i]['mtime']), Errors::MESSAGE_OK) . $this->code(trim($ext));
                    }
                }
            }
        }
    }


    /**
     * extractZipArchive
     * 
     * @param string $current
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function extractZipArchive ($current = '', $name = '', $chmod = array(), $overwrite = false)
    {
        $sysName = IOWrapper::set($name);

        function pclzip_cb_post_extract ($p_event, &$p_header) {
            global $Gmanager;

            if ($Gmanager->is_dir($p_header['filename'])) {
                $Gmanager->rechmod($p_header['filename'], Gmanager::$pclzipD);
            } else if (Config::$mode != 'FTP') {
                $Gmanager->rechmod($p_header['filename'], Gmanager::$pclzipF);
            }
            return 1;
        }


        self::$pclzipF = $chmod[0]; // CHMOD to files
        self::$pclzipD = $chmod[1]; // CHMOD to folders


        if (Config::$mode == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_current = Config::$temp . '/GmanagerFtpZip' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerZipFtp' . $_SERVER['REQUEST_TIME'];
            mkdir($ftp_name, 0777);
            file_put_contents($ftp_current, $this->file_get_contents($current));
        }


        $zip = $this->_pclZip(Config::$mode == 'FTP' ? $ftp_current : $current);


        if ($overwrite) {
            $res = $zip->extract(PCLZIP_OPT_PATH, Config::$mode == 'FTP' ? $ftp_name : $sysName, PCLZIP_CB_POST_EXTRACT, 'pclzip_cb_post_extract', PCLZIP_OPT_REPLACE_NEWER);
        } else {
            $res = $zip->extract(PCLZIP_OPT_PATH, Config::$mode == 'FTP' ? $ftp_name : $sysName, PCLZIP_CB_POST_EXTRACT, 'pclzip_cb_post_extract');
        }

        $err = '';
        foreach ($res as $status) {
            if ($status['status'] != 'ok') {
                $err .= str_replace('%file%', htmlspecialchars($status['stored_filename'], ENT_NOQUOTES), Language::get('extract_file_false_ext')) . ' (' . $status['status'] . ')<br/>';
            }
        }

        if (!$res) {
            if (Config::$mode == 'FTP') {
                unlink($ftp_current);
                rmdir($ftp_name);
            }
            return Errors::message(Language::get('extract_false') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL);
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName, self::$pclzipD);
            $this->ftpMoveFiles($ftp_name, $sysName, self::$pclzipF, self::$pclzipD, $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($sysName)) {
            if ($chmod) {
                $this->rechmod($sysName, $chmod[1]);
            }
            return Errors::message(Language::get('extract_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractRarArchive
     * 
     * @param string $current
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function extractRarArchive ($current = '', $name = '', $chmod = array(), $overwrite = false)
    {
        $sysName = IOWrapper::set($name);

        if (Config::$mode == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_current = Config::$temp . '/GmanagerFtpRar' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerFtpRar' . $_SERVER['REQUEST_TIME'];
            mkdir($ftp_name, 0777);
            file_put_contents($ftp_current, $this->file_get_contents($current));
        }

        $rar = $this->_rarOpen(Config::$mode == 'FTP' ? $ftp_current : $current);
        $err = '';
        foreach (rar_list($rar) as $f) {
            $n = $f->getName();

            if (!$overwrite && $this->file_exists($name . '/' . $n)) {
                $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($n, ENT_NOQUOTES) . ')<br/>';
            } else {
                $entry = rar_entry_get($rar, $n);
                if (!$entry->extract(Config::$mode == 'FTP' ? $ftp_name : $sysName)) {
                    if (Config::$mode == 'FTP') {
                        unlink($ftp_current);
                        rmdir($ftp_name);
                    }
                    $err .= str_replace('%file%', htmlspecialchars($n, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
                }
            }

            if ($this->is_dir($name . '/' . $n)) {
                $this->rechmod($name . '/' . $n, $chmod[1]);
            } else {
                $this->rechmod($name . '/' . $n, $chmod[0]);
            }
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName, $chmod[1]);
            $this->ftpMoveFiles($ftp_name, $sysName, $chmod[0], $chmod[1], $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($name)) {
            $this->rechmod($name, $chmod[1]);
            return Errors::message(Language::get('extract_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractTarArchive
     * 
     * @param string $current
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function extractTarArchive ($current = '', $name = '', $chmod = array(), $overwrite = false)
    {
        $sysName = IOWrapper::set($name);

        if (Config::$mode == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_current = Config::$temp . '/GmanagerFtpTar' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerFtpTar' . $_SERVER['REQUEST_TIME'];
            mkdir($ftp_name, 0777);
            file_put_contents($ftp_current, $this->file_get_contents($current));
        }

        $tgz = $this->_archiveTar(Config::$mode == 'FTP' ? $ftp_current : $current);
        $extract = $tgz->listContent();
        $err = '';

        if ($overwrite) {
            $res = $tgz->extract(Config::$mode == 'FTP' ? $ftp_name : $sysName);
        } else {
            $list = array();
            foreach ($extract as $f) {
                if ($this->file_exists($name . '/' . $f['filename'])) {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f['filename'], ENT_NOQUOTES) . ')<br/>';
                } else {
                    $list[] = $f['filename'];
                }
            }
            if (!$list) {
                return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
            }
    
            $res = $tgz->extractList($list, Config::$mode == 'FTP' ? $ftp_name : $sysName);
        }

        if (!$res) {
            if (Config::$mode == 'FTP') {
                unlink($ftp_current);
                rmdir($ftp_name);
            }
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }

        foreach ($extract as $f) {
            if ($this->is_dir($name . '/' . $f['filename'])) {
                $this->rechmod($name . '/' . $f['filename'], $chmod[1]);
            } else {
                $this->rechmod($name . '/' . $f['filename'], $chmod[0]);
            }
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName, $chmod[1]);
            $this->ftpMoveFiles($ftp_name, $sysName, $chmod[0], $chmod[1], $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($name)) {
            $this->rechmod($name, $chmod[1]);
            return Errors::message(Language::get('extract_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractZipFile
     * 
     * @param string $current
     * @param string $name
     * @param mixed  $chmod
     * @param string $fl
     * @param bool   $overwrite
     * @return string
     */
    public function extractZipFile ($current = '', $name = '', $chmod = '', $fl = '', $overwrite = false)
    {
        $err = '';
        if ($overwrite) {
            $ext = & $fl;
        } else {
            $ext = array();
            foreach ($fl as $f) {
                if ($this->file_exists($name . '/' . $f)) {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')<br/>';
                } else {
                    $ext[] = $f;
                }
            }
            unset($fl);
        }

        if (!$ext) {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        }

        $sysName = IOWrapper::set($name);

        if (Config::$mode == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_current = Config::$temp . '/GmanagerFtpZipArchive' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerFtpZipFile' . $_SERVER['REQUEST_TIME'] . '.tmp';
            file_put_contents($ftp_current, $this->file_get_contents($current));
        }

        $zip = $this->_pclZip(Config::$mode == 'FTP' ? $ftp_current : $current);
        $res = $zip->extract(PCLZIP_OPT_PATH, Config::$mode == 'FTP' ? $ftp_name : $sysName, PCLZIP_OPT_BY_NAME, $ext, PCLZIP_OPT_REPLACE_NEWER);

        foreach ($res as $status) {
            if ($status['status'] != 'ok') {
                $err .= str_replace('%file%', htmlspecialchars($status['stored_filename'], ENT_NOQUOTES), Language::get('extract_file_false_ext')) . ' (' . $status['status'] . ')<br/>';
            }
        }

        if (!$res) {
            if (Config::$mode == 'FTP') {
                unlink($ftp_current);
            }
            return Errors::message(Language::get('extract_file_false') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL);
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName);
            $this->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($name)) {
            if ($chmod) {
                $this->rechmod($name, $chmod);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractRarFile
     * 
     * @param string $current
     * @param string $name
     * @param mixed  $chmod
     * @param string $fl
     * @param bool   $overwrite
     * @return string
     */
    public function extractRarFile ($current = '', $name = '', $chmod = '', $ext = '', $overwrite = false)
    {
        $tmp = array();
        $err = '';
        foreach ($ext as $f) {
            if ($this->file_exists($name . '/' . $f)) {
                if ($overwrite) {
                    unlink($name . '/' . $f);
                    $tmp[] = $f;
                } else {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')<br/>';
                }
            } else {
                $tmp[] = $f;
            }
        }
        $ext = & $tmp;

        if (!$ext) {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        }

        $sysName = IOWrapper::set($name);

        if (Config::$mode == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_current = Config::$temp . '/GmanagerFtpRarArchive' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerFtpRarFile' . $_SERVER['REQUEST_TIME'] . '.tmp';
            file_put_contents($ftp_current, $this->file_get_contents($current));
        }

        $rar = $this->_rarOpen(Config::$mode == 'FTP' ? $ftp_current : $current);

        foreach ($ext as $var) {
            $entry = rar_entry_get($rar, $var);
            if (!$entry->extract(Config::$mode == 'FTP' ? $ftp_name : $sysName)) {
                if (Config::$mode == 'FTP') {
                    unlink($ftp_current);
                }
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            } else if (!$this->file_exists((Config::$mode == 'FTP' ? $ftp_name : $name) . '/' . $var)) {
                // fix bug in rar extension
                // method extract alredy returned "true"
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            }
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName);
            $this->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($name)) {
            if ($chmod) {
                $this->rechmod($name, $chmod);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractTarFile
     * 
     * @param string $current
     * @param string $name
     * @param mixed  $chmod
     * @param string $fl
     * @param bool   $overwrite
     * @return string
     */
    public function extractTarFile ($current = '', $name = '', $chmod = '', $ext = '', $overwrite = false)
    {
        $tmp = array();
        $err = '';
        foreach ($ext as $f) {
            if ($this->file_exists($name . '/' . $f)) {
                if ($overwrite) {
                    unlink($name . '/' . $f);
                    $tmp[] = $f;
                } else {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')<br/>';
                }
            } else {
                $tmp[] = $f;
            }
        }
        $ext = & $tmp;

        if (!$ext) {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        }

        $sysName = IOWrapper::set($name);

        if (Config::$mode == 'FTP') {
               $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
               $ftp_current = Config::$temp . '/GmanagerFtpTarArchive' . $_SERVER['REQUEST_TIME'] . '.tmp';
               $ftp_name = Config::$temp . '/GmanagerFtpTarFile' . $_SERVER['REQUEST_TIME'] . '.tmp';
               file_put_contents($ftp_current, $this->file_get_contents($current));
        }

        $tgz = $this->_archiveTar(Config::$mode == 'FTP' ? $ftp_current : $current);

        if (!$tgz->extractList($ext, Config::$mode == 'FTP' ? $ftp_name : $sysName)) {
            if (Config::$mode == 'FTP') {
                unlink($ftp_current);
            }
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }

        if (Config::$mode == 'FTP') {
            $this->createDir($sysName);
            $this->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            unlink($ftp_current);
        }

        if (Config::$mode == 'FTP' || $this->is_dir($name)) {
            if ($chmod) {
                $this->rechmod($name, $chmod);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * delZipArchive
     * 
     * @param string $current
     * @param string $f
     * @return string
     */
    public function delZipArchive ($current = '', $f = '')
    {
        $zip = $this->_pclZip($current);
        //    $comment = $zip->properties();
        //    $comment = $comment['comment'];
        //  TODO: сохранение комментариев

        // fix del directory
        foreach ($zip->listContent() as $index) {
            if ($index['stored_filename'] == $f) {
                break;
            }
        }

        $list = $zip->delete(PCLZIP_OPT_BY_INDEX, $index['index']);


        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd($current);
        }

        if ($list != 0) {
            return Errors::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('del_file_false') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * delTarArchive
     * 
     * @param string $current
     * @param string $f
     * @return string
     */
    public function delTarArchive ($current = '', $f = '')
    {
        $tgz = $this->_archiveTar($current);

        $list = $tgz->listContent();

        $new_tar = array();
        $s = sizeof($list);
        for ($i = 0; $i < $s; ++$i) {
            if ($list[$i]['filename'] == $f) {
                continue;
            } else {
                $new_tar[] = $list[$i]['filename'];
            }
        }

        $tmp_name = Config::$temp . '/GmanagerTar' . $_SERVER['REQUEST_TIME'] . '/';
        $tgz->extractList($new_tar, $tmp_name);

        $this->unlink($current);
        $list = $tgz->createModify($tmp_name, '.', $tmp_name);
        $this->clean($tmp_name);

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd($current);
        }

        if ($list) {
            return Errors::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('del_file_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * addZipArchive
     * 
     * @param string $current
     * @param mixed  $ext
     * @param string $dir
     * @return string
     */
    public function addZipArchive ($current = '', $ext = array(), $dir = '')
    {
        if (Config::$mode == 'FTP') {
            $tmpCurrent = Config::$temp . '/GmanagerFtpZip' . $_SERVER['REQUEST_TIME'] . '.tmp';
            file_put_contents($tmpCurrent, $this->file_get_contents($current));
        } else {
            $tmpCurrent = $current;
        }
        $tmpFolder = Config::$temp . '/GmanagerFtpZip' . $_SERVER['REQUEST_TIME'];
        mkdir($tmpFolder, 0777);


        $tmp = array();
        foreach ($ext as $v) {
            $b = IOWrapper::set(basename($v));
            $tmp[] = $tmpFolder . '/' . $b;
            if ($this->is_dir($v)) {
                mkdir($tmpFolder . '/' . $b, 0777, true);
            } else {
                file_put_contents($tmpFolder . '/' . $b, $this->file_get_contents($v));
            }
        }


        $zip = $this->_pclZip($tmpCurrent);
        $add = $zip->add($tmp, PCLZIP_OPT_ADD_PATH, IOWrapper::set($dir), PCLZIP_OPT_REMOVE_PATH, iconv_substr($tmpFolder, iconv_strlen(dirname(dirname($tmpFolder))) - 1));

        if (Config::$mode == 'FTP') {
            $this->file_put_contents($current, file_get_contents($tmpCurrent));
            unlink($tmpCurrent);
        }
        $this->clean($tmpFolder);

        if ($add) {
            return Errors::message(Language::get('add_archive_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('add_archive_false') . '<br/>' . $zip->errorInfo(true), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * addTarArchive
     * 
     * @param string $current
     * @param mixed  $ext
     * @param string $dir
     * @return string
     */
    public function addTarArchive ($current = '', $ext = array(), $dir = '')
    {
        if (Config::$mode == 'FTP') {
            $ftp_current = Config::$temp . '/GmanagerFtpTar' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $ftp_name = Config::$temp . '/GmanagerFtpTar' . $_SERVER['REQUEST_TIME'] . '/';
            mkdir($ftp_name, 0777);

            file_put_contents($ftp_current, $this->file_get_contents($current));
            $tmp = array();
            foreach ($ext as $v) {
                $b = IOWrapper::set(basename($v));
                $tmp[] = $ftp_name . $b;
                file_put_contents($ftp_name . $b, $this->file_get_contents($v));
            }
            $ext = $tmp;
            unset($tmp);
        }

        $tgz = $this->_archiveTar(Config::$mode == 'FTP' ? $ftp_current : $current);

        foreach ($ext as $v) {
            $add = $tgz->addModify($v, $dir, dirname($v));
        }

        if (Config::$mode == 'FTP') {
            $this->file_put_contents($current, file_get_contents($ftp_current));
            unlink($ftp_current);
            $this->clean($ftp_name);
        }

        if ($add) {
            return Errors::message(Language::get('add_archive_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('add_archive_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * createZipArchive
     * 
     * @param string $name
     * @param mixed  $chmod
     * @param array  $ext
     * @param string $comment
     * @param bool   $overwrite
     * @return string
     */
    public function createZipArchive ($name = '', $chmod = 0644, $ext = array(), $comment = '', $overwrite = false)
    {
        if (!$overwrite && $this->file_exists($name)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($name, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        $this->createDir(iconv_substr($name, 0, strrpos($name, '/')));

        if (Config::$mode == 'FTP') {
             $ftp_name = Config::$temp . '/GmanagerFtpZip' . $_SERVER['REQUEST_TIME'] . '.tmp';
             $ftp = array();
             $temp = Config::$temp . '/GmanagerFtpZip' . $_SERVER['REQUEST_TIME'];
             mkdir($temp, 0755, true);
             foreach ($ext as $f) {
                 $ftp[] = $tmp = $temp . '/' . IOWrapper::get(basename($f));
                 if ($this->is_dir($f)) {
                    mkdir($tmp, 0755, true);
                    $this->ftpCopyFiles($f, $tmp);
                 } else {
                    file_put_contents($tmp, $this->file_get_contents($f));
                 }
            }
            $ext = $ftp;
            unset($ftp);
        } else {
            $temp = Config::$current;
            $ext = array_map(array('IOWrapper', 'set'), $ext);
        }

        //TODO:empty directories
        $zip = $this->_pclZip(Config::$mode == 'FTP' ? $ftp_name : $name);
        if ($comment != '') {
            $zip->create($ext, PCLZIP_OPT_REMOVE_PATH, IOWrapper::set($temp), PCLZIP_OPT_COMMENT, $comment);
        } else {
            $zip->create($ext, PCLZIP_OPT_REMOVE_PATH, IOWrapper::set($temp));
        }

        if ($zip->errorCode()) {
            $err = true;
        } else {
            $err = false;
        }

        if (!$err && Config::$mode == 'FTP') {
            if (!$this->file_put_contents($name, file_get_contents($ftp_name))) {
                $zip->error_string = Errors::get();
            }
            unlink($ftp_name);
            $this->clean($temp);
        }

        if ($err === false && $this->is_file($name)) {
            if ($chmod) {
                $this->rechmod($name, $chmod);
            }
            return Errors::message(Language::get('create_archive_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('create_archive_false') . '<br/>' . htmlspecialchars($zip->errorInfo(true), ENT_NOQUOTES), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * gz
     * 
     * @param string $c
     * @return string
     */
    public function gz ($c = '')
    {
        $data = Config::$mode == 'FTP' ? $this->ftpArchiveStart($c) : IOWrapper::set($c);

        $fo = fopen($data, 'rb');
        fseek($fo, -4, SEEK_END);
        $len = end(@unpack('V', fread($fo, 4)));
        fseek($fo, 10, SEEK_SET);
        $gz = strtok(fread($fo, 1024), chr(0));
        if ($gz == '') {
            $gz = basename($c, '.gz');
        }
        fclose($fo);

        $ext = implode('', gzfile($data));

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd();
        }

        if ($ext) {
            return Errors::message(Language::get('name') . ': ' . htmlspecialchars($gz, ENT_NOQUOTES) . '<br/>' . Language::get('archive_size') . ': ' . $this->formatSize($this->size($c)) . '<br/>' . Language::get('real_size') . ': ' . $this->formatSize($len) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::$date_format, $this->filemtime($c)), Errors::MESSAGE_OK) . $this->code(trim($ext));
        } else {
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * gzExtract
     * 
     * @param string $c
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function gzExtract ($c = '', $name = '', $chmod = array(), $overwrite = false)
    {
        $this->createDir($name, $chmod[1]);

        $tmp = (Config::$mode == 'FTP' ? $this->ftpArchiveStart($c) : IOWrapper::set($c));

        $fo = fopen($tmp, 'rb');
        fseek($fo, 10, SEEK_SET);
        $gz = IOWrapper::get(strtok(fread($fo, 1024), chr(0)));
        if ($gz == '') {
            $gz = IOWrapper::get(basename($c, '.gz'));
        }
        fclose($fo);

        $data = null;
        if ($overwrite || !$this->file_exists($name . '/' . $gz)) {
            if (!$this->file_put_contents($name . '/' . $gz, implode('', gzfile($tmp)))) {
                $data = Errors::message(Language::get('extract_file_false') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
            }
        } else {
            $data = Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($name . '/' . $gz, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if (Config::$mode == 'FTP') {
            $this->ftpArchiveEnd();
        }
        if ($data) {
            return $data;
        }

        if ($this->is_file($name . '/' . $gz)) {
            if ($chmod[0]) {
                $this->rechmod($name, $chmod[0]);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * getArchiveFile
     * 
     * @param string $archive
     * @param string $f
     * @return string
     */
    public function getArchiveFile ($archive = '', $f = '')
    {
        $tmp = $this->isArchive($this->getType(basename($archive)));
        if ($tmp == 'ZIP') {
            $zip = $this->_pclZip($archive);
            $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

            if (Config::$mode == 'FTP') {
                $this->ftpArchiveEnd('');
            }

            return $ext[0]['content'];
        } else if ($tmp == 'TAR') {
            $tgz = $this->_archiveTar($archive);
            return $tgz->extractInString($f);
        } else if ($tmp == 'BZ2' && extension_loaded('bz2')) {
            $tgz = $this->_archiveTar($archive);
            return $tgz->extractInString($f);
        } else if ($tmp == 'RAR' && extension_loaded('rar')) {
            $rar = $this->_rarOpen($archive);
            $entry = rar_entry_get($rar, $f);

            // создаем временный файл
            $tmp = Config::$temp . '/GmanagerRAR' . $_SERVER['REQUEST_TIME'] . '.tmp';
            $entry->extract(true, $tmp); // запишет сюда данные

            $ext = file_get_contents($tmp);
            unlink($tmp);
            return $ext;
        }
    }


    /**
     * uploadFiles
     * 
     * @param string $tmp
     * @param string $name
     * @param string $dir
     * @param mixed  $chmod
     * @return string
     */
    public function uploadFiles ($tmp = '', $name = '', $dir = '', $chmod = 0644)
    {
        $fname = $name;

        if (substr($dir, -1) != '/') {
            $name = basename($dir);
            $dir = dirname($dir) . '/';
        }

        if ($this->file_put_contents($dir . $name, file_get_contents($tmp))) {
            if ($chmod) {
                $this->rechmod($dir . $name, $chmod);
            }
            unlink($tmp);
            return Errors::message(Language::get('upload_true') . ' -&gt; ' . htmlspecialchars($fname . ' -> ' .$dir . $name, ENT_NOQUOTES), Errors::MESSAGE_OK);
        } else {
            $error = Errors::get();
            unlink($tmp);
            return Errors::message(Language::get('upload_false') . ' -&gt; ' . htmlspecialchars($fname . ' x ' .$dir . $name, ENT_NOQUOTES) . '<br/>' . $error, Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * uploadUrl
     * 
     * @param string $url
     * @param string $name
     * @param mixed  $chmod
     * @param string $headers
     * @param mixed  $set_time_limit
     * @param bool   $ignore_user_abort
     * @return string
     */
    public function uploadUrl ($url = '', $name = '', $chmod = 0644, $headers = '', $set_time_limit = false, $ignore_user_abort = false)
    {
        if ($set_time_limit !== false) {
            set_time_limit($set_time_limit);
        }
        if ($ignore_user_abort) {
            ignore_user_abort(true);
        }

        $tmp = array();
        $url = trim($url);

        if (strpos($url, "\n") !== false) {
            foreach (explode("\n", $url) as $v) {
                $v = trim($v);
                $tmp[] = array($v, $name . basename($v));
            }
        } else {
            $last = substr($name, -1);
            $temp = false;
            if ($last != '/' && $this->is_dir($name)) {
                $name .= '/';
                $temp = true;
            }

            if ($last != '/' && !$temp) {
                $name = dirname($name) . '/' . basename($name);
            } else {
                $h = @get_headers($url, 1);
                $temp = false;
                if (isset($h['Content-Disposition'])) {
                    preg_match('/.+;\s+filename=(?:")?([^"]+)/i', $h['Content-Disposition'], $arr);
                    if (isset($arr[1])) {
                        $temp = true;
                        $name = $name . basename($arr[1]);
                    }
                }
                if (!$temp) {
                    $name = $name . rawurldecode(basename(parse_url($url, PHP_URL_PATH)));
                }
            }
            $tmp[] = array($url, $name);
        }

        $out = '';
        foreach ($tmp as $v) {
            $dir = dirname($v[1]);
            if (!$this->is_dir($dir)) {
                $this->mkdir($dir, 0755);
            }

            if (Config::$mode == 'FTP') {
                $tmp = $this->getData($v[0], $headers);
                $r = $this->file_put_contents($v[1], $tmp['body']);
                $this->chmod($v[1], $chmod);
            } else {
                ini_set('user_agent', str_ireplace('User-Agent:', '', trim($headers)));
                $r = $this->copy($v[0], $v[1], $chmod);
            }

            if ($r) {
                $out .= Errors::message(Language::get('upload_true') . ' -&gt; ' . htmlspecialchars($v[0] . ' -> ' . $v[1], ENT_NOQUOTES), Errors::MESSAGE_OK);
            } else {
                $out .= Errors::message(Language::get('upload_false') . ' -&gt; ' . htmlspecialchars($v[0] . ' x ' . $v[1], ENT_NOQUOTES) . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
            }
        }

        return $out;
    }


    /**
     * sendMail
     * 
     * @param string $theme
     * @param string $mess
     * @param string $to
     * @param string $from
     * @return string
     */
    public function sendMail ($theme = '', $mess = '', $to = '', $from = '')
    {
        if (mail($to, '=?UTF-8?B?' . base64_encode($theme) . '?=', wordwrap($mess, 70), 'From: ' . $from . "\r\nContent-type: text/plain; charset=UTF-8\r\nX-Mailer: Gmanager " . Config::$version)) {
            return Errors::message(Language::get('send_mail_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('send_mail_false') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * showEval
     * 
     * @param string $eval
     * @return string
     */
    public function showEval ($eval = '')
    {
        if (ob_start()) {
            $info['time'] = microtime(true);
            $info['ram'] = memory_get_usage(false);

            eval($eval);

            $info['time'] = round(microtime(true) - $info['time'], 6);
            $info['ram'] = $this->formatSize(memory_get_usage(false) - $info['ram'], 6);
            $buf = ob_get_contents();
            ob_end_clean();


            if (iconv_substr($buf, 0, iconv_strlen(ini_get('error_prepend_string'))) == ini_get('error_prepend_string')) {
                $buf = iconv_substr($buf, iconv_strlen(ini_get('error_prepend_string')));
            }
            if (iconv_substr($buf, -iconv_strlen(ini_get('error_append_string'))) == ini_get('error_append_string')) {
                $buf = iconv_substr($buf, 0, -iconv_strlen(ini_get('error_append_string')));
            }


            $rows = sizeof(explode("\n", $buf)) + 1;
            if ($rows < 3) {
                $rows = 3;
            }
            return '<div class="input">' . Language::get('result') . '<br/><textarea cols="48" rows="' . $rows . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea><br/>' . str_replace('%time%', $info['time'], Language::get('microtime')) . '<br/>' . Language::get('memory_get_usage') . ' ' . $info['ram'] . '<br/></div>';
        } else {
            echo '<div class="input">' . Language::get('result') . '<pre class="code"><code>';

            $info['time'] = microtime(true);
            $info['ram'] = memory_get_usage(false);

            eval($eval);

            $info['time'] = round(microtime(true) - $info['time'], 6);
            $info['ram'] = $this->formatSize(memory_get_usage(false) - $info['ram'], 6);

            echo '</code></pre>';
            echo str_replace('%time%', $info['time'], Language::get('microtime')) . '<br/>' . Language::get('memory_get_usage') . ' ' . $info['ram'] . '<br/></div>';
        }
    }


    /**
     * showCmd
     * 
     * @param string $cmd
     * @return string
     */
    public function showCmd ($cmd = '')
    {
        /*
            $h = popen($cmd, 'r');
            $buf = '';
            while (!feof($h)) {
                $buf .= fgets($h, 4096);
            }
            pclose($h);
        */

        if (Config::$sysType == 'WIN') {
            $cmd = iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $cmd);
        }

        if ($h = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w')), $pipes)) {
            //fwrite($pipes[0], '');
            fclose($pipes[0]);

            $buf = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            proc_close($h);

            $rows = sizeof(explode("\n", $buf)) + 1;
            if ($rows < 3) {
                $rows = 3;
            }

            if (iconv('UTF-8', 'UTF-8', $buf) != $buf) {
                $buf = iconv(Config::$consencoding, 'UTF-8//TRANSLIT', $buf);
            }
        } else {
            return '<div class="red">' . Language::get('cmd_error') . '<br/></div>';
        }
        return '<div class="input">' . Language::get('result') . '<br/><textarea cols="48" rows="' . $rows . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea></div>';
    }


    /**
     * replace
     * 
     * @param string $current
     * @param string $from
     * @param string $to
     * @param bool   $regexp
     * @return string
     */
    public function replace ($current = '', $from = '', $to = '', $regexp = false)
    {
        if (!$from) {
            return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
        }
        $c = $this->file_get_contents($current);

        if ($regexp) {
            preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
            $all = sizeof($all[0]);
            if (!$all) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }
            $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
            if ($str) {
                if (!$this->file_put_contents($current, $str)) {
                    return Errors::message(Language::get('replace_false_file') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
                }
            } else {
                return Errors::message(Language::get('regexp_error'), Errors::MESSAGE_FAIL);
            }
        } else {
            $all = substr_count($c, $from);
            if (!$all) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }

            if (!$this->file_put_contents($current, str_replace($from, $to, $c))) {
                return Errors::message(Language::get('replace_false_file') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
            }

            $str = true;
        }

        if ($str) {
            return Errors::message(Language::get('replace_true') . $all, Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('replace_false_file'), Errors::MESSAGE_FAIL);
        }
    }


    /**
     * zipReplace
     * 
     * @param string $current
     * @param string $f
     * @param string $from
     * @param string $to
     * @param bool   $regexp
     * @return string
     */
    public function zipReplace ($current = '', $f = '', $from = '', $to = '', $regexp = false)
    {
        if (!$from) {
            return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
        }

        $c = $this->editZipFile($current, $f);
        $c = $c['text'];

        if ($regexp) {
            preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
            if (!sizeof($all[0])) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }
            $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
            if ($str) {
                return $this->editZipFileOk($current, $f, $str);
            } else {
                return Errors::message(Language::get('regexp_error'), Errors::MESSAGE_FAIL);
            }
        } else {
            if (!substr_count($c, $from)) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }

            return $this->editZipFileOk($current, $f, str_replace($from, $to, $c));
        }
    }


    /**
     * gzdecode
     * 
     * @param string $data
     * @return string
     */
    public function gzdecode ($data)
    {
        if (function_exists('gzdecode')) {
            return gzdecode($data);
        } else {
            file_put_contents(Config::$temp . '/GmanagerArchiveSearch' . $_SERVER['REQUEST_TIME'] . '.tmp', $data);
            $gz = implode('', gzfile(Config::$temp . '/GmanagerArchiveSearch' . $_SERVER['REQUEST_TIME'] . '.tmp'));
            unlink(Config::$temp . '/GmanagerArchiveSearch' . $_SERVER['REQUEST_TIME'] . '.tmp');
            return $gz;
        }
    }


    /**
     * search
     * 
     * @param string $c
     * @param string $s
     * @param bool   $w
     * @param bool   $r
     * @param bool   $h
     * @param int    $limit
     * @param bool   $archive
     * @return string
     */
    public function search ($c = '', $s = '', $w = false, $r = false, $h = false, $limit = 8388608, $archive = false)
    {
        $html = ListData::getListSearchData($this, $c, $s, $w, $r, $h, $limit, $archive);
        if ($html) {
            return $html;
        } else {
            return ListData::getListEmptySearchData();
        }
    }


    /**
     * fname
     * 
     * @param string $f
     * @param string $name
     * @param int    $register
     * @param int    $i
     * @param bool   $overwrite
     * @return string
     */
    public function fname ($f = '', $name = '', $register = 0, $i = 0, $overwrite = false)
    {
        // [replace=from,to] - replace
        // [n=0] - meter
        // [f] - type
        // [name] - name
        // [date] - date
        // [rand=8,16] - random

        // $f = rawurldecode($f);

        $info = pathinfo($f);

        if (preg_match_all('/\[replace=([^,]),([^\]])/U', $name, $arr, PREG_SET_ORDER)) {
            foreach ($arr as $var) {
                $name = str_replace($var[1], $var[2], $info['filename'] . '.' . $info['extension']);
            }
        }
        if (preg_match_all('/\[n=*(\d*)\]/U', $name, $arr, PREG_SET_ORDER)) {
            foreach ($arr as $var) {
                $name = str_replace($var[0], $var[1] + $i, $name);
            }
        }
        if (preg_match_all('/\[rand=*(\d*),*(\d*)\]/U', $name, $arr, PREG_SET_ORDER)) {
            foreach ($arr as $var) {
                $name = str_replace($var[0], iconv_substr(str_shuffle(Config::$rand), 0, mt_rand((!empty($var[1]) ? $var[1] : 8), (!empty($var[2]) ? $var[2] : 16))), $name);
            }
        }
        $name = str_replace('[f]', $info['extension'], $name);
        $name = str_replace('[name]', $info['filename'], $name);
        $name = str_replace('[date]', strftime('%d_%m_%Y'), $name);

        if ($register == 1) {
            $tmp = strtolower($name);
            if (!iconv_strlen($tmp)) {
                $tmp = iconv(Config::$altencoding, 'UTF-8//TRANSLIT', strtolower(iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $name)));
            }
        } else if ($register == 2) {
            $tmp = strtoupper($name);
            if (!iconv_strlen($tmp)) {
                $tmp = iconv(Config::$altencoding, 'UTF-8//TRANSLIT', strtoupper(iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $name)));
            }
        } else {
            $tmp = $name;
        }

        if (!$overwrite && $$this->file_exists($info['dirname'] . '/' . $tmp)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($info['dirname'] . '/' . $tmp, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if ($this->rename($f, $info['dirname'] . '/' . $tmp)) {
            return Errors::message($info['basename'] . ' - ' . $tmp, Errors::MESSAGE_OK);
        } else {
            return Errors::message(Errors::get() . ' ' . $info['basename'] . ' -&gt; ' . $tmp, Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * sqlInstaller
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $sql
     * @return string
     */
    public function sqlInstaller ($host = '', $name = '', $pass = '', $db = '', $charset = '', $sql = '')
    {
        $SQL = SQL::main($this);
        if (!$SQL) {
            return Errors::message(Language::get('mysql_connect_false'), Errors::MESSAGE_FAIL);
        } else {
            return $SQL->installer($host, $name, $pass, $db, $charset, $sql);
        }
    }


    /**
     * sqlBackup
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param array  $tables
     * @return mixed
     */
    public function sqlBackup ($host = '', $name = '', $pass = '', $db = '', $charset = '', $tables = array())
    {
        $SQL = SQL::main($this);
        if (!$SQL) {
            return Errors::message(Language::get('mysql_connect_false'), Errors::MESSAGE_FAIL);
        } else {
            return $SQL->backup($host, $name, $pass, $db, $charset, $tables);
        }
    }


    /**
     * sqlQuery
     * 
     * @param string $host
     * @param string $name
     * @param string $pass
     * @param string $db
     * @param string $charset
     * @param string $data
     * @return string
     */
    public function sqlQuery ($host = '', $name = '', $pass = '', $db = '', $charset = '', $data = '')
    {
        $SQL = SQL::main($this);
        if (!$SQL) {
            return Errors::message(Language::get('mysql_connect_false'), Errors::MESSAGE_FAIL);
        } else {
            return $SQL->query($host, $name, $pass, $db, $charset, $data);
        }
    }


    /**
     * strLink
     * 
     * @param string $str
     * @param bool   $sub
     * @return string
     */
    public function strLink ($str = '', $sub = false)
    {
        if (!$sub) {
            return $str;
        }

        $len = @iconv_strlen($str);

        if ($len > Config::$link) {
            $s = intval(Config::$link / 2);
            return iconv_substr($str, 0, $s) . ' ... ' . iconv_substr($str, ($len - $s));
        }

        return $str;
    }


    /**
     * getData
     * 
     * @param string $url
     * @param string $headers
     * @param bool   $only_headers
     * @param string $post
     * @return array
     */
    public function getData ($url = '', $headers = '', $only_headers = false, $post = '')
    {
        $u = parse_url($url);

        $host = $u['host'];
        $path = isset($u['path']) ? $u['path'] : '/';
        $port = isset($u['port']) ? $u['port'] : 80;

        if (isset($u['query'])) {
            $path .= '?' . $u['query'];
        }
        if (isset($u['fragment'])) {
            $path .= '#' . $u['fragment'];
        }

        $path = str_replace(' ', '%20', $path);

        $fp = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$fp) {
            return false;
        } else {
            $out = 'Host: ' . $host . "\r\n";

            if ($headers) {
                $out .= trim($headers) . "\r\n";
            } else {
                $out .= 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
                $out .= 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . "\r\n";
                $out .= 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\r\n";
                $out .= 'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'] . "\r\n";
                //$out .= 'TE: deflate, gzip, chunked, identity, trailers' . "\r\n";
                $out .= 'Connection: Close' . "\r\n";
            }

            if ($post) {
                $out .= 'Content-type: application/x-www-form-urlencoded' . "\r\n";
                $out .= 'Content-Length: ' . strlen($post) . "\r\n";
                $out = 'POST ' . $path . ' HTTP/1.0' . "\r\n" . $out . "\r\n" . $post;
            } else {
                $out = 'GET ' . $path . ' HTTP/1.0' . "\r\n" . $out . "\r\n";
            }

            fwrite($fp, $out);
            $headers = $body = '';
            while ($str = trim(fgets($fp, 512))) {
                $headers .= $str . "\r\n";
            }
            if (!$only_headers) {
                while (!feof($fp)) {
                    $body .= fgets($fp, 4096);
                }
            }
            fclose($fp);
        }

        return array('headers' => $headers, 'body' => $body);
    }


    /**
     * encoding
     * 
     * @param string $text
     * @param string $charset
     * @return array
     */
    public function encoding ($text = '', $charset)
    {
        $ch = explode(' -> ', $charset);
        if ($text) {
            $text = iconv($ch[0], $ch[1] . '//TRANSLIT', $text);
        }
        return array(0 => $ch[0], 1 => $ch[1], 'text' => $text);
    }


    /**
     * ftpMoveFiles
     * 
     * @param string $from
     * @param string $to
     * @param int    $chmodf
     * @param int    $chmodd
     * @param bool   $overwrite
     * @return void
     */
    public function ftpMoveFiles ($from = '', $to = '', $chmodf = 0644, $chmodd = 0755, $overwrite = false)
    {
        $h = opendir($from);
        while (($f = readdir($h)) !== false) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            if (is_dir($from . '/' . $f)) {
                $this->mkdir($to . '/' . $f, $chmodd);
                $this->ftpMoveFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
                rmdir($from . '/' . $f);
            } else {
                if ($overwrite || !$this->file_exists($to . '/' . $f)) {
                    $this->file_put_contents($to . '/' . $f, file_get_contents($from . '/' . $f));
                }

                $this->rechmod($to . '/' . $f, $chmodf);
                unlink($from . '/' . $f);
            }
        }
        closedir($h);
        rmdir($from);
    }


    /**
     * ftpCopyFiles
     * 
     * @param string $from
     * @param string $to
     * @param int    $chmodf
     * @param int    $chmodd
     * @param bool   $overwrite
     * @return void
     */
    public function ftpCopyFiles ($from = '', $to = '', $chmodf = 0644, $chmodd = 0755, $overwrite = false)
    {
        foreach ($this->iterator($from) as $f) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            if ($this->is_dir($from . '/' . $f)) {
                mkdir($to . '/' . $f, $chmodd);
                $this->ftpCopyFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
            } else {
                if ($overwrite || !file_exists($to . '/' . $f)) {
                    file_put_contents($to . '/' . $f, $this->file_get_contents($from . '/' . $f));
                }
            }
        }
    }


    /**
     * ftpArchiveStart
     * 
     * @param string $current
     * @return string
     */
    public function ftpArchiveStart ($current = '')
    {
        self::$_ftpArchive = Config::$temp . '/GmanagerFtpArchive' . $_SERVER['REQUEST_TIME'] . '.tmp';
        file_put_contents(self::$_ftpArchive, $this->file_get_contents($current));
        return self::$_ftpArchive;
    }


    /**
     * ftpArchiveEnd
     * 
     * @param string $current
     * @return void
     */
    public function ftpArchiveEnd ($current = '')
    {
        if ($current != '') {
            $this->file_put_contents($current, file_get_contents(self::$_ftpArchive));
        }
        unlink(self::$_ftpArchive);
    }


    /**
     * getType
     * 
     * @param string $f
     * @return string
     */
    public function getType ($f)
    {
        $type = array_reverse(explode('.', strtoupper($f)));
        if ((isset($type[1]) && $type[1] != '') && ($type[1] . '.' . $type[0] == 'TAR.GZ' || $type[1] . '.' . $type[0] == 'TAR.BZ' || $type[1] . '.' . $type[0] == 'TAR.GZ2' || $type[1] . '.' . $type[0] == 'TAR.BZ2')) {
            return $type[1] . '.' . $type[0];
        }

        return $type[0];
    }


    /**
     * isArchive
     * 
     * @param string $type
     * @return string
     */
    public function isArchive ($type)
    {
        if ($type == 'ZIP' || $type == 'JAR' || $type == 'AAR' || $type == 'WAR') {
            return 'ZIP';
        } else if ($type == 'TAR' || $type == 'TGZ' || $type == 'TGZ2' || $type == 'TAR.GZ' || $type == 'TAR.GZ2') {
            return 'TAR';
        } else if ($type == 'GZ' || $type == 'GZ2') {
            return 'GZ';
        } else if (($type == 'TBZ' || $type == 'TBZ2' || $type == 'TAR.BZ' || $type == 'TAR.BZ2' || $type == 'BZ' || $type == 'BZ2') && extension_loaded('rar')) {
            return 'BZ2';
        } else if ($type == 'RAR' && extension_loaded('rar')) {
            return 'RAR';
        }

        return '';
    }


    /**
     * id2name
     * 
     * @param int    $id
     * @return string
     */
    public static function id2name ($id = 0)
    {
        if (Config::$sysType == 'WIN') {
            return '';
        } else {
            if (function_exists('posix_getpwuid') && $name = @posix_getpwuid($id)) {
                return $name['name'];
            } else if ($name = @exec('perl -e \'($login, $pass, $uid, $gid) = getpwuid(' . @escapeshellcmd($id) . ');print $login;\'')) {
                return $name;
            } else {
                return $id;
            }
        }
    }


    /**
     * getPHPUser
     * 
     * @return array
     */
    public function getPHPUser ()
    {
        if (function_exists('posix_getpwuid')) {
            return posix_getpwuid(posix_geteuid());
        }

        return array();
    }


    /**
     * clean
     * 
     * @param string $name
     * @return void
     */
    public function clean ($dir = '')
    {
        $h = @opendir($dir);
        if (!$h) {
            return;
        }

        while (($f = readdir($h)) !== false) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            if (is_dir($dir . '/' . $f)) {
                @rmdir($dir . '/' . $f);
                $this->clean($dir . '/' . $f);
            } else {
                unlink($dir . '/' . $f);
            }
        }
        closedir($h);
        rmdir($dir);
    }


    /**
     * PclZip
     * 
     * @param string $file
     * @return object
     */
    private function _pclZip($file)
    {
        return new PclZip(Config::$mode == 'FTP' ? $this->ftpArchiveStart($file) : IOWrapper::set($file));
    }


    /**
     * Archive_Tar
     * 
     * @param string $file
     * @return object
     */
    private function _archiveTar($file)
    {
        return new Archive_Tar(Config::$mode == 'FTP' ? $this->ftpArchiveStart($file) : IOWrapper::set($file));
    }


    /**
     * rar_open
     * 
     * @param string $file
     * @return object
     */
    private function _rarOpen($file)
    {
        return rar_open(Config::$mode == 'FTP' ? $this->ftpArchiveStart($file) : IOWrapper::set($file));
    }


    /**
     * phpinfo
     * 
     * @param int $what
     * @return void
     */
    public function phpinfo($what = -1)
    {
        header('Content-Type: text/html; charset=UTF-8');

        if (Config::$sysType == 'WIN' && ob_start()) {
            phpinfo($what);
            $phpinfo = iconv(Config::$altencoding, 'UTF-8', ob_get_contents());
            ob_end_clean();
            echo $phpinfo;
        } else {
            phpinfo($what);
        }
        exit;
    }
}

?>
