<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2011 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class Gmanager
{
    private static $_ftpArchive;


    /**
     * main
     * 
     * @return void
     */
    public function main ()
    {
        $c = isset($_POST['c']) ? $_POST['c'] : (isset($_GET['c']) ? rawurlencode($_GET['c']) : (isset($_GET['get']) ? rawurlencode($_GET['get']) : ''));

        if ($c) {
            Registry::set('current',  str_replace('\\', '/', rawurldecode($c)));
            Registry::set('currentType', Registry::getGmanager()->filetype(Registry::get('current')));

            if (Registry::get('currentType') == 'dir' || Registry::get('currentType') == 'link') {
                if (substr(Registry::get('current'), -1) != '/') {
                    Registry::set('current', Registry::get('current') . '/');
                }
            }
        } else if (Registry::getGmanager()->file_exists(rawurldecode($_SERVER['QUERY_STRING']))) {
            Registry::set('current',  str_replace('\\', '/', rawurldecode($_SERVER['QUERY_STRING'])));
            Registry::set('currentType', Registry::getGmanager()->filetype(Registry::get('current')));

            if (Registry::get('currentType') == 'dir' || Registry::get('currentType') == 'link') {
                if (substr(Registry::get('current'), -1) != '/') {
                    Registry::set('current', Registry::get('current') . '/');
                }
            }
        } else {
            if (substr(Config::get('Gmanager', 'defaultDirectory'), -1) != '/') {
                Registry::set('current', Config::get('Gmanager', 'defaultDirectory') . '/');
            } else {
                Registry::set('current', Config::get('Gmanager', 'defaultDirectory'));
            }
            Registry::set('currentType', 'dir');
        }

        Registry::set('hCurrent', htmlspecialchars(Registry::get('current'), ENT_COMPAT));
        Registry::set('rCurrent', str_replace('%2F', '/', rawurlencode(Registry::get('current'))));


        // кол-во файлов на странице
        $ip = isset($_POST['limit']);
        $ig = isset($_GET['limit']);
        Registry::set('limit', abs($ip ? $_POST['limit'] : ($ig ? $_GET['limit'] : (isset($_COOKIE['gmanager_limit']) ? $_COOKIE['gmanager_limit'] : Config::get('Gmanager', 'defaultLimit')))));

        if ($ip || $ig) {
            setcookie('gmanager_limit', Registry::get('limit'), 2592000 + GMANAGER_REQUEST_TIME, str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), $_SERVER['HTTP_HOST']);
        }

        // построчный редактор
        if (isset($_GET['lineEditor'])) {
            $_GET['lineEditor'] ? Registry::set('lineEditor', true) : Registry::set('lineEditor', false);

            setcookie('Gmanager_lineEditor', (int)Registry::get('lineEditor'), 2592000 + GMANAGER_REQUEST_TIME, str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), $_SERVER['HTTP_HOST']);
        } else if (isset($_COOKIE['Gmanager_lineEditor'])) {
            Registry::set('lineEditor', (bool)$_COOKIE['Gmanager_lineEditor']);
        } else {
            Registry::set('lineEditor', Config::get('LineEditor', 'defaultEnable'));
        }
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
    }


    /**
     * head
     * 
     * @param void
     * @return string
     */
    public function head ()
    {
        if (Config::get('Gmanager', 'mode') != 'FTP') {
            $realpath = Registry::getGmanager()->realpath(Registry::get('current'));
            $realpath = $realpath ? $realpath : Registry::get('current');
        } else {
            $realpath = Registry::get('current');
        }
        $chmod = $this->lookChmod(Registry::get('current'));
        $chmod = $chmod ? $chmod : (isset($_POST['chmod'][0]) ? htmlspecialchars($_POST['chmod'][0], ENT_NOQUOTES) : (isset($_POST['chmod']) ? htmlspecialchars($_POST['chmod'], ENT_NOQUOTES) : 0));

        $d = dirname(str_replace('\\', '/', $realpath));
        $archive = $this->isArchive($this->getType(basename(Registry::get('current'))));

        if (Registry::get('currentType') == 'dir' || Registry::get('currentType') == 'link') {
            if (Registry::get('current') == '.') {
                return '<div class="border">' . Language::get('dir') . ' <a href="index.php">' . htmlspecialchars($this->strLink(Registry::getGmanager()->getcwd()), ENT_NOQUOTES) . '</a> (' . $this->lookChmod(Registry::getGmanager()->getcwd()) . ')<br/></div>';
            } else {
                return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . $d . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
            }
        } else if (Registry::get('currentType') == 'file' && $archive) {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($this->strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . htmlspecialchars($this->strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="index.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
        } else {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($up)) . '">' . htmlspecialchars($this->strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . str_replace('%2F', '/', rawurlencode($d)) . '">' . htmlspecialchars($this->strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="edit.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', $this->strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
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
        if (!($len = iconv_strlen($current))) {
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
        if (!Registry::getGmanager()->is_dir($current) || !Registry::getGmanager()->is_readable($current)) {
            return ListData::getListDenyData();
        }

        $add  = (isset($_GET['add_archive']) ? $_GET['add_archive'] : '');
        $pg   = (isset($_GET['pg']) ? $_GET['pg'] : 1);
        $html = ListData::getListData($current, $itype, $down, $pg, $add);
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
        $arrSource = explode('/', $source);
        $tmpSet = $tmpSource = '';
        $tmpAdd = array();
        $i = 0;

        foreach (explode('/', $to) as $var) {
            $instSource = isset($arrSource[$i]) ? $arrSource[$i] : null;
            $i++;

            $tmpSet .= $var . '/';
            if ($instSource !== null) {
                $tmpSource .= $instSource . '/';

                if ($var != $instSource) {
                    $tmpAdd[] = array('dir' => $instSource, 'chmod' => $this->lookChmod($tmpSource));
                }
            }

            if (!Registry::getGmanager()->is_dir($tmpSet)) {
                Registry::getGmanager()->mkdir($tmpSet, $instSource !== null ? $this->lookChmod($tmpSource) : null);
            }
        }

        if ($tmpAdd) {
            $checkTo = $to;
            foreach ($tmpAdd as $v) {
                $checkTo .= '/' . $v['dir'];

                if (!Registry::getGmanager()->is_dir($checkTo)) {
                    Registry::getGmanager()->mkdir($checkTo, $v['chmod']);
                }
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

        foreach (Registry::getGmanager()->iterator($d) as $file) {

            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if (Registry::getGmanager()->is_dir($d . '/' . $file)) {

                if (Registry::getGmanager()->mkdir($dest . '/' . $file, $ch)) {
                    Registry::getGmanager()->chmod($dest, $ch);
                    $this->copyFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !Registry::getGmanager()->file_exists($dest . '/' . $file)) {
                    if (!Registry::getGmanager()->copy($d . '/' . $file, $dest . '/' . $file, $ch)) {
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

        foreach (Registry::getGmanager()->iterator($d) as $file) {
            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if (Registry::getGmanager()->is_dir($d . '/' . $file)) {

                if (Registry::getGmanager()->mkdir($dest . '/' . $file, $ch)) {
                    Registry::getGmanager()->chmod($dest . '/' . $file, $ch);
                    $this->moveFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                    Registry::getGmanager()->rmdir($d . '/' . $file);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('move_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !Registry::getGmanager()->file_exists($dest . '/' . $file)) {
                    if (Registry::getGmanager()->rename($d . '/' . $file, $dest . '/' . $file)) {
                        Registry::getGmanager()->chmod($dest . '/' . $file, $ch);
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
            Registry::getGmanager()->rmdir($d);
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
        if (!$overwrite && Registry::getGmanager()->file_exists($dest)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($dest, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if ($source == $dest) {
            if ($chmod) {
                $this->rechmod($dest, $chmod);
            }
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Errors::MESSAGE_OK);
        }

        $this->copyD(dirname($source), dirname($dest));

        if (Registry::getGmanager()->copy($source, $dest)) {
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
        if (!$overwrite && Registry::getGmanager()->file_exists($dest)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($dest, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if ($source == $dest) {
            if ($chmod) {
                $this->rechmod($dest, $chmod);
            }
            return Errors::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Errors::MESSAGE_OK);
        }

        $this->copyD(dirname($source), dirname($dest));

        if (Registry::getGmanager()->rename($source, $dest)) {
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
        if (Registry::getGmanager()->unlink($f)) {
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
        Registry::getGmanager()->chmod($d, 0777);

        foreach (Registry::getGmanager()->iterator($d) as $f) {
            $realpath = Registry::getGmanager()->realpath($d . '/' . $f);
            $f = $realpath ? str_replace('\\', '/', $realpath) : str_replace('//', '/', $d . '/' . $f);
            Registry::getGmanager()->chmod($f, 0777);

            if (Registry::getGmanager()->is_dir($f) /*&& !Registry::getGmanager()->rmdir($f)*/) {
                $this->delDir($f . '/');
                Registry::getGmanager()->rmdir($f);
            } else if (Registry::getGmanager()->file_exists($f)) {
                if (!Registry::getGmanager()->unlink($f)) {
                    $err .= $f . '<br/>';
                }
            }
        }

        if (!Registry::getGmanager()->rmdir($d)) {
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
                foreach (Registry::getGmanager()->iterator($d) as $file) {
                    if (Registry::getGmanager()->is_dir($d . '/' . $file)) {
                        $ds[] = $d . '/' . $file;
                    } else {
                        $sz += Registry::getGmanager()->filesize($d . '/' . $file);
                    }
                }
            } while (sizeof($ds) > 0);

            return $sz;
        }

        return Registry::getGmanager()->filesize($source);
    }


    /**
     * formatSize
     * 
     * @param int   $size
     * @param int   $int
     * @return string
     */
    public function formatSize ($size = false, $int = 2)
    {
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
        return substr(sprintf('%o', Registry::getGmanager()->fileperms($file)), -4);
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

        if (Registry::getGmanager()->file_put_contents($file, $text)) {
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
        $g = explode(DIRECTORY_SEPARATOR, Registry::getGmanager()->getcwd());

        foreach (explode('/', $dir) as $d) {
            $tmp .= $d . '/';
            if (isset($g[$i])) {
                $tmp2 .= $g[$i] . '/';
            }

            if ($tmp == $tmp2 || Registry::getGmanager()->is_dir($tmp)) {
                $i++;
                continue;
            }
            if (!Registry::getGmanager()->mkdir($tmp, $chmod)) {
                $err .= Errors::get() . ' -&gt; ' . htmlspecialchars($tmp, ENT_NOQUOTES) . '<br/>';
            }
            $i++;
        }

        if ($err) {
            return Errors::message(Language::get('create_dir_false') . '<br/>' . $err, Errors::MESSAGE_EMAIL);
        } else {
            return Errors::message(Language::get('create_dir_true'), Errors::MESSAGE_OK);
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

        if (Registry::getGmanager()->chmod($current, $chmod)) {
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
        if (Registry::getGmanager()->is_dir($current)) {
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
     * @param string $content
     * @param array  $charset
     * @return string
     */
    public function syntax ($content = '', $charset = array())
    {
        $tmp = Config::getTemp() . '/GmanagerSyntax' . GMANAGER_REQUEST_TIME . '.tmp';
        $fp = fopen($tmp, 'w');
        if (!$fp) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Errors::MESSAGE_FAIL);
        }
        fputs($fp, $content);
        fclose($fp); 

        exec(escapeshellcmd(Config::get('PHP', 'path')) . ' -c -f -l ' . escapeshellarg($tmp), $rt, $v);
        unlink($tmp);
        $error = Errors::get();
        $size = sizeof($rt);

        if (!$size) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . $error, Errors::MESSAGE_EMAIL);
        }

        $erl = false;
        if ($v == 255 || $size > 2) {
            if ($st = trim(strip_tags($rt[1]))) {
                $erl = preg_replace('/.*\s(\d*)$/', '$1', $st, 1);
                $pg = str_replace($tmp, '...', $st);
            } else {
                $pg = Language::get('syntax_unknown');
            }
        } else {
            $pg = Language::get('syntax_true');
        }

        if ($charset[0]) {
            $content = iconv($charset[0], $charset[1] . '//TRANSLIT', $content);
        }

        return Errors::message($pg, $erl ? Errors::MESSAGE_FAIL : Errors::MESSAGE_OK) . $this->code($content, $erl);
    }


    /**
     * syntaxWapinet
     * 
     * @param string $content
     * @param array  $charset
     * @return string
     */
    public function syntaxWapinet ($content = '', $charset = array())
    {
        if (!$charset[0]) {
            $charset[0] = 'UTF-8';
        }
        $fp = fsockopen('wapinet.ru', 80, $er1, $er2, 10);
        if (!$fp) {
            return Errors::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Errors::MESSAGE_FAIL);
        }

        $content = rawurlencode(trim($content));

        fputs($fp, 'POST /syntax2/index.php HTTP/1.0' . "\r\n" .
            'Content-type: application/x-www-form-urlencoded; charset=' . $charset[0] . "\r\n" .
            'Content-length: ' . (iconv_strlen($content) + 2) . "\r\n" .
            'Host: wapinet.ru' . "\r\n" .
            'Connection: close' . "\r\n" .
            'User-Agent: GManager ' . Config::getVersion() . "\r\n\r\n" .
            'f=' . $content . "\r\n\r\n");

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

        $fl = Registry::getGmanager()->file_get_contents($current);
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
     * getGzInfo
     * 
     * @param string $file
     * @return array
     */
    public function getGzInfo ($file)
    {
        $fo = fopen($file, 'rb');
        fseek($fo, -4, SEEK_END);
        $length = end(@unpack('V', fread($fo, 4)));

        rewind($fo);
        $name = '';
        $i = 0;
        $null = chr(0);
        while (($tmp = fread($fo, 1)) !== '') {
            if ($tmp == $null) {
                $i++;
            } else if ($i == 2) {
                $name .= $tmp;
            }
            if ($i > 2) {
                break;
            }
        }

        if ($name == '') {
            $name = basename($file, '.gz');
        }
        fclose($fo);


        return array('name' => IOWrapper::get($name), 'length' => $length);
    }


    /**
     * getGzContent
     * 
     * @param string $file
     * @return string
     */
    public function getGzContent ($file)
    {
        ob_start();
        readgzfile($file);
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }


    /**
     * gz
     * 
     * @param string $c
     * @return string
     */
    public function gz ($c = '')
    {
        $data = Config::get('Gmanager', 'mode') == 'FTP' ? $this->ftpArchiveStart($c) : IOWrapper::set($c);

        $info = $this->getGzInfo($data);
        $ext = $this->getGzContent($data);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $this->ftpArchiveEnd();
        }

        if ($ext) {
            return Errors::message(Language::get('name') . ': ' . htmlspecialchars($info['name'], ENT_NOQUOTES) . '<br/>' . Language::get('archive_size') . ': ' . $this->formatSize($this->size($c)) . '<br/>' . Language::get('real_size') . ': ' . $this->formatSize($info['length']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), Registry::getGmanager()->filemtime($c)), Errors::MESSAGE_OK) . $this->code(trim($ext));
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

        $tmp = (Config::get('Gmanager', 'mode') == 'FTP' ? $this->ftpArchiveStart($c) : IOWrapper::set($c));

        $info = $this->getGzInfo($tmp);

        $data = null;
        if ($overwrite || !Registry::getGmanager()->file_exists($name . '/' . $info['name'])) {
            if (!Registry::getGmanager()->file_put_contents($name . '/' . $info['name'], $this->getGzContent($tmp))) {
                $data = Errors::message(Language::get('extract_file_false') . '<br/>' . Errors::get(), Errors::MESSAGE_EMAIL);
            }
        } else {
            $data = Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($name . '/' . $info['name'], ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $this->ftpArchiveEnd();
        }
        if ($data) {
            return $data;
        }

        if (Registry::getGmanager()->is_file($name . '/' . $info['name'])) {
            if ($chmod[0]) {
                $this->rechmod($name . '/' . $info['name'], $chmod[0]);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
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

        if (Registry::getGmanager()->file_put_contents($dir . $name, file_get_contents($tmp))) {
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
            if ($last != '/' && Registry::getGmanager()->is_dir($name)) {
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
            if (!Registry::getGmanager()->is_dir($dir)) {
                Registry::getGmanager()->mkdir($dir, 0755);
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                $tmp = $this->getData($v[0], $headers);
                $r = Registry::getGmanager()->file_put_contents($v[1], $tmp['body']);
                Registry::getGmanager()->chmod($v[1], $chmod);
            } else {
                ini_set('user_agent', str_ireplace('User-Agent:', '', trim($headers)));
                $r = Registry::getGmanager()->copy($v[0], $v[1], $chmod);
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
        if (mail($to, '=?UTF-8?B?' . base64_encode($theme) . '?=', wordwrap($mess, 70), 'From: ' . $from . "\r\nContent-type: text/plain; charset=UTF-8\r\nX-Mailer: Gmanager " . Config::getVersion())) {
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

        if (Registry::get('sysType') == 'WIN') {
            $cmd = iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//TRANSLIT', $cmd);
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
                $buf = iconv(Config::get('Gmanager', 'consoleEncoding'), 'UTF-8//TRANSLIT', $buf);
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
        $c = Registry::getGmanager()->file_get_contents($current);

        if ($regexp) {
            preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
            $all = sizeof($all[0]);
            if (!$all) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }
            $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
            if ($str) {
                if (!Registry::getGmanager()->file_put_contents($current, $str)) {
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

            if (!Registry::getGmanager()->file_put_contents($current, str_replace($from, $to, $c))) {
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

        Registry::set('archiveDriver', 'zip');
        $c = Archive::main()->getEditFile($current, $f);
        $c = $c['text'];

        if ($regexp) {
            preg_match_all('/' . str_replace('/', '\/', $from) . '/', $c, $all);
            if (!sizeof($all[0])) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }
            $str = preg_replace('/' . str_replace('/', '\/', $from) . '/', $to, $c);
            if ($str) {
                return Archive::main()->setEditFile($current, $f, $str);
            } else {
                return Errors::message(Language::get('regexp_error'), Errors::MESSAGE_FAIL);
            }
        } else {
            if (!substr_count($c, $from)) {
                return Errors::message(Language::get('replace_false_str'), Errors::MESSAGE_FAIL);
            }

            return Archive::main()->setEditFile($current, $f, str_replace($from, $to, $c));
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
        $html = ListData::getListSearchData($c, $s, $w, $r, $h, $limit, $archive);
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
                $name = str_replace($var[0], iconv_substr(str_shuffle(Config::get('Gmanager', 'rand')), 0, mt_rand((!empty($var[1]) ? $var[1] : 8), (!empty($var[2]) ? $var[2] : 16))), $name);
            }
        }
        $name = str_replace('[f]', $info['extension'], $name);
        $name = str_replace('[name]', $info['filename'], $name);
        $name = str_replace('[date]', strftime('%d_%m_%Y'), $name);

        if ($register == 1) {
            $tmp = strtolower($name);
            if (!iconv_strlen($tmp)) {
                $tmp = iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8//TRANSLIT', strtolower(iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//TRANSLIT', $name)));
            }
        } else if ($register == 2) {
            $tmp = strtoupper($name);
            if (!iconv_strlen($tmp)) {
                $tmp = iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8//TRANSLIT', strtoupper(iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//TRANSLIT', $name)));
            }
        } else {
            $tmp = $name;
        }

        if (!$overwrite && Registry::getGmanager()->file_exists($info['dirname'] . '/' . $tmp)) {
            return Errors::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($info['dirname'] . '/' . $tmp, ENT_NOQUOTES) . ')', Errors::MESSAGE_FAIL);
        }

        if (Registry::getGmanager()->rename($f, $info['dirname'] . '/' . $tmp)) {
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
        $SQL = SQL::main(true);
        if (!$SQL) {
            return '';
        }
        return $SQL->installer($host, $name, $pass, $db, $charset, $sql);
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
        $SQL = SQL::main();
        if (!$SQL) {
            return Errors::message(Language::get('sql_connect_false'), Errors::MESSAGE_FAIL);
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
        $SQL = SQL::main();
        if (!$SQL) {
            return Errors::message(Language::get('sql_connect_false'), Errors::MESSAGE_FAIL);
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
        $maxLen = Config::get('Gmanager', 'maxLinkSize');

        if ($len > $maxLen) {
            $start = ceil($maxLen / 2);
            $end = $len - $start;
            if ($maxLen % 2) {
                $end += 1;
            }
            return iconv_substr($str, 0, $start) . ' ... ' . iconv_substr($str, $end);
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
                Registry::getGmanager()->mkdir($to . '/' . $f, $chmodd);
                $this->ftpMoveFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
                rmdir($from . '/' . $f);
            } else {
                if ($overwrite || !Registry::getGmanager()->file_exists($to . '/' . $f)) {
                    Registry::getGmanager()->file_put_contents($to . '/' . $f, file_get_contents($from . '/' . $f));
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
        foreach (Registry::getGmanager()->iterator($from) as $f) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            if (Registry::getGmanager()->is_dir($from . '/' . $f)) {
                mkdir($to . '/' . $f, $chmodd);
                $this->ftpCopyFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
            } else {
                if ($overwrite || !file_exists($to . '/' . $f)) {
                    file_put_contents($to . '/' . $f, Registry::getGmanager()->file_get_contents($from . '/' . $f));
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
        self::$_ftpArchive = Config::getTemp() . '/GmanagerFtpArchive' . GMANAGER_REQUEST_TIME . '.tmp';
        file_put_contents(self::$_ftpArchive, Registry::getGmanager()->file_get_contents($current));
        return self::$_ftpArchive;
    }


    /**
     * ftpArchiveEnd
     * 
     * @param string $current
     * @return bool
     */
    public function ftpArchiveEnd ($current = '')
    {
        if ($current != '') {
            $result = Registry::getGmanager()->file_put_contents($current, file_get_contents(self::$_ftpArchive));
        }
        unlink(self::$_ftpArchive);
        return (bool)$result;
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
        if ($type === 'ZIP' || $type === 'JAR' || $type === 'AAR' || $type === 'WAR') {
            return 'ZIP';
        } else if ($type === 'TAR' || $type === 'TGZ' || $type === 'TGZ2' || $type === 'TAR.GZ' || $type === 'TAR.GZ2') {
            return 'TAR';
        } else if ($type === 'GZ' || $type === 'GZ2') {
            return 'GZ';
        } else if (($type === 'TBZ' || $type === 'TBZ2' || $type === 'TAR.BZ' || $type === 'TAR.BZ2' || $type === 'BZ' || $type === 'BZ2') && extension_loaded('rar')) {
            return 'BZ2';
        } else if ($type === 'RAR' && extension_loaded('rar')) {
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
        if (Registry::get('sysType') == 'WIN') {
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
     * getUname
     * 
     * @return string
     */
    public function getUname ()
    {
        $uname = php_uname();
        if (Registry::get('sysType') == 'WIN') {
            $uname = iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8', $uname);
        }
        return $uname;
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

        return array('name' => '', 'passwd' => '', 'uid' => '', 'gid' => '', 'gecos' => '', 'dir' => '', 'shell' => '');
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
     * phpinfo
     * 
     * @param int $what
     * @return void
     */
    public function phpinfo($what = -1)
    {
        header('Content-Type: text/html; charset=UTF-8');

        if (Registry::get('sysType') == 'WIN' && ob_start()) {
            phpinfo($what);
            $phpinfo = iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8', ob_get_contents());
            ob_end_clean();
            echo $phpinfo;
        } else {
            phpinfo($what);
        }
        exit;
    }
}

?>
