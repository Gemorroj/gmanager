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


abstract class Gmanager
{
    /**
     * @var string
     */
    private $_ftpArchiveTmp = '';
    /**
     * @var HTTP|FTP
     */
    private static $_instance = null;

    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}


    /**
     * getInstance
     *
     * @return HTTP|FTP
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            if (Config::get('Gmanager', 'mode') === 'FTP') {
                self::$_instance = new FTP(
                    Config::get('FTP', 'user'),
                    Config::get('FTP', 'pass'),
                    Config::get('FTP', 'host'),
                    Config::get('FTP', 'port')
                );
            } else {
                self::$_instance = new HTTP;
            }
        }

        return self::$_instance;
    }


    /**
     * init
     */
    public function init ()
    {
        $this->_setCurrent();

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
        } elseif (isset($_COOKIE['Gmanager_lineEditor'])) {
            Registry::set('lineEditor', (bool)$_COOKIE['Gmanager_lineEditor']);
        } else {
            Registry::set('lineEditor', Config::get('LineEditor', 'defaultEnable'));
        }
    }


    /**
     * _setCurrent
     */
    private function _setCurrent ()
    {
        $c = !empty($_POST['c']) ? rawurldecode($_POST['c']) : (!empty($_GET['c']) ? $_GET['c'] : (!empty($_GET['get']) ? $_GET['get'] : rawurldecode($_SERVER['QUERY_STRING'])));

        if ($c) {
            if ($c == '/') {
                $c = '.';
            }

            Registry::set('current', str_replace('\\', '/', $c));
            Registry::set('currentType', self::$_instance->filetype(Registry::get('current')));
            Registry::set('currentTypeLink', null);

            if (Registry::get('currentType') == 'link') {
                $link = self::$_instance->readlink(Registry::get('current'));
                Registry::set('currentTypeLink', self::$_instance->filetype($link[1]));
            } elseif (Registry::get('currentType') == 'dir') {
                if (mb_substr(Registry::get('current'), -1) != '/') {
                    Registry::set('current', Registry::get('current') . '/');
                }
            }
        } else {
            if (mb_substr(Config::get('Gmanager', 'defaultDirectory'), -1) != '/') {
                Registry::set('current', Config::get('Gmanager', 'defaultDirectory') . '/');
            } else {
                Registry::set('current', Config::get('Gmanager', 'defaultDirectory'));
            }
            Registry::set('currentType', 'dir');
        }

        Registry::set('hCurrent', htmlspecialchars(Registry::get('current'), ENT_COMPAT));
        Registry::set('rCurrent', Helper_View::getRawurl(Registry::get('current')));
    }


    /**
     * sendHeader
     */
    public function sendHeader ()
    {
        //header('Content-type: text/html; charset=UTF-8');
        header('Content-Type: ' . Config::getContentType() . '; charset=UTF-8');
        header('Cache-Control: no-cache');
    }


    /**
     * head
     *
     * @return string
     */
    public function head ()
    {
        if (Config::get('Gmanager', 'mode') != 'FTP') {
            $realpath = self::$_instance->realpath(Registry::get('current'));
            $realpath = $realpath ? $realpath : Registry::get('current');
        } else {
            $realpath = Registry::get('current');
        }
        $chmod = $this->lookChmod(Registry::get('current'));
        $chmod = $chmod ? $chmod : (isset($_POST['chmod'][0]) ? htmlspecialchars($_POST['chmod'][0], ENT_NOQUOTES) : (isset($_POST['chmod']) ? htmlspecialchars($_POST['chmod'], ENT_NOQUOTES) : 0));

        $d = dirname(str_replace('\\', '/', $realpath));
        $archive = Helper_Archive::isArchive(Helper_System::getType(Helper_System::basename(Registry::get('current'))));

        if (Registry::get('currentType') == 'dir' || Registry::get('currentTypeLink') == 'dir') {
            if (Registry::get('current') == '.') {
                return '<div class="border">' . Language::get('dir') . ' <a href="index.php">' . htmlspecialchars(Helper_View::strLink(self::$_instance->getcwd()), ENT_NOQUOTES) . '</a> (' . $this->lookChmod(self::$_instance->getcwd()) . ')<br/></div>';
            } else {
                return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . Helper_View::getRawurl($d) . '">' . $d . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', Helper_View::strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
            }
        } elseif (Registry::get('currentType') == 'file' && $archive) {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . Helper_View::getRawurl($up) . '">' . htmlspecialchars(Helper_View::strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . Helper_View::getRawurl($d) . '">' . htmlspecialchars(Helper_View::strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="index.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', Helper_View::strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
        } else {
            $up = dirname($d);
            return '<div class="border">' . Language::get('back') . ' <a href="index.php?' . Helper_View::getRawurl($up) . '">' . htmlspecialchars(Helper_View::strLink($up), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($up) . ')<br/></div><div class="border">' . Language::get('dir') . ' <a href="index.php?' . Helper_View::getRawurl($d) . '">' . htmlspecialchars(Helper_View::strLink($d), ENT_NOQUOTES) . '</a> (' . $this->lookChmod($d) . ')<br/></div><div class="border">' . Language::get('file') . ' <a href="edit.php?' . Registry::get('rCurrent') . '">' . htmlspecialchars(str_replace('\\', '/', Helper_View::strLink($realpath)), ENT_NOQUOTES) . '</a> (' . $chmod . ')<br/></div>';
        }
    }


    /**
     * langJS
     *
     * @return string
     */
    public function langJS ()
    {
        return '<div style="display:none;"><span id="chF">' . Language::get('check_form') . '</span><span id="delN">' . Language::get('del_notify') . '</span></div>';
    }


    /**
     * _staticName
     * 
     * @param string $current
     * @param string $dest
     * @return string
     */
    private function _staticName ($current = '', $dest = '')
    {
        $len = mb_strlen($current);

        if (mb_substr($dest, 0, $len) == $current) {
            $static = mb_substr($dest, $len);

            if (mb_strpos($static, '/')) {
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
        if (self::$_instance->is_link($current)) {
            $link = self::$_instance->readlink($current);
            $current = $link[1] . '/';
        }

        if (!self::$_instance->is_dir($current) || !self::$_instance->is_readable($current)) {
            return ListData::getListDenyData();
        }

        $add  = (isset($_GET['add_archive']) ? $_GET['add_archive'] : '');
        $pg   = (isset($_GET['pg']) ? $_GET['pg'] : 1);
        $html = ListData::getListData($current, $itype, $down, $pg, $add);
        if ($html) {
            if ($itype == 'time') {
                $out = '&amp;time';
            } elseif ($itype == 'type') {
                $out = '&amp;type';
            } elseif ($itype == 'size') {
                $out = '&amp;size';
            } elseif ($itype == 'chmod') {
                $out = '&amp;chmod';
            } elseif ($itype == 'uid') {
                $out = '&amp;uid';
            } elseif ($itype == 'gid') {
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

            if (!self::$_instance->is_dir($tmpSet)) {
                self::$_instance->mkdir($tmpSet, $instSource !== null ? $this->lookChmod($tmpSource) : null);
            }
        }

        if ($tmpAdd) {
            $checkTo = $to;
            foreach ($tmpAdd as $v) {
                $checkTo .= '/' . $v['dir'];

                if (!self::$_instance->is_dir($checkTo)) {
                    self::$_instance->mkdir($checkTo, $v['chmod']);
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

        foreach (self::$_instance->iterator($d) as $file) {
            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if (self::$_instance->is_dir($d . '/' . $file)) {

                if (self::$_instance->mkdir($dest . '/' . $file, $ch)) {
                    self::$_instance->chmod($dest . '/' . $file, $ch);
                    $this->copyFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !self::$_instance->file_exists($dest . '/' . $file)) {
                    if (Registry::get('sysType') != 'WIN' && self::$_instance->is_link($d . '/' . $file)) {
                        if (!self::$_instance->symlink($d . '/' . $file, $dest . '/' . $file, $ch)) {
                            $error[] = str_replace('%file%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_file_false')) . ' (' . Errors::get() . ')';
                        }
                    } else {
                        if (!self::$_instance->copy($d . '/' . $file, $dest . '/' . $file, $ch)) {
                            $error[] = str_replace('%file%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('copy_file_false')) . ' (' . Errors::get() . ')';
                        }
                    }
                } else {
                    $error[] = Language::get('overwrite_false') . ' (' . htmlspecialchars($dest . '/' . $file, ENT_NOQUOTES) . ')';
                }

            }
        }

        if ($error) {
            return Helper_View::message(implode('<br/>', $error), Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            return Helper_View::message(str_replace('%title%', htmlspecialchars($dest, ENT_NOQUOTES), Language::get('copy_files_true')), Helper_View::MESSAGE_SUCCESS);
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

        foreach (self::$_instance->iterator($d) as $file) {
            if ($file == $static) {
                continue;
            }
            if ($d == $dest) {
                break;
            }

            $ch = $this->lookChmod($d . '/' . $file);

            if (self::$_instance->is_dir($d . '/' . $file)) {

                if (self::$_instance->mkdir($dest . '/' . $file, $ch)) {
                    self::$_instance->chmod($dest . '/' . $file, $ch);
                    $this->moveFiles($d . '/' . $file, $dest . '/' . $file, $static, $overwrite);
                } else {
                    $error[] = str_replace('%title%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('move_files_false')) . ' (' . Errors::get() . ')';
                }

            } else {

                if ($overwrite || !self::$_instance->file_exists($dest . '/' . $file)) {
                    if (!self::$_instance->rename($d . '/' . $file, $dest . '/' . $file)) {
                        $error[] = str_replace('%file%', htmlspecialchars($d . '/' . $file, ENT_NOQUOTES), Language::get('move_file_false')) . ' (' . Errors::get() . ')';
                    }
                } else {
                    $error[] = Language::get('overwrite_false') . ' (' . htmlspecialchars($dest . '/' . $file, ENT_NOQUOTES) . ')';
                }

            }
        }

        if ($error) {
            return Helper_View::message(implode('<br/>', $error), Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            self::$_instance->rmdir($d);
            return Helper_View::message(str_replace('%title%', htmlspecialchars($dest, ENT_NOQUOTES), Language::get('move_files_true')), Helper_View::MESSAGE_SUCCESS);
        }
    }


    /**
     * Check access copy or move file
     *
     * @param string $source
     * @param string $dest
     * @param mixed  $chmod
     * @param bool   $overwrite
     * @return string   Error string or empty string
     */
    private function _checkChangeFile ($source, $dest, $chmod, $overwrite)
    {
        if (!$overwrite && self::$_instance->file_exists($dest)) {
            return Helper_View::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($dest, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_ERROR);
        }

        if ($source == $dest) {
            if ($chmod) {
                $this->rechmod($dest, $chmod);
            }
            return Helper_View::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Helper_View::MESSAGE_SUCCESS);
        }

        return '';
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
        $err = $this->_checkChangeFile($source, $dest, $chmod, $overwrite);
        if ($err) {
            return $err;
        }

        $this->copyD(dirname($source), dirname($dest));

        if (self::$_instance->copy($source, $dest)) {
            if (!$chmod) {
                $chmod = $this->lookChmod($source);
            }
            $this->rechmod($dest, $chmod);

            return Helper_View::message(htmlspecialchars(str_replace('%file%', $source, Language::get('copy_file_true'))), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(htmlspecialchars(str_replace('%file%', $source, Language::get('copy_file_false'))) . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $err = $this->_checkChangeFile($source, $dest, $chmod, $overwrite);
        if ($err) {
            return $err;
        }

        $this->copyD(dirname($source), dirname($dest));

        if (self::$_instance->rename($source, $dest)) {
            if (!$chmod) {
                $chmod = $this->lookChmod($source);
            }
            $this->rechmod($dest, $chmod);

            return Helper_View::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_true'))), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(htmlspecialchars(str_replace('%file%', $source, Language::get('move_file_false'))) . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        if (self::$_instance->unlink($f)) {
            return Helper_View::message(Language::get('del_file_true') . ' -&gt; ' . htmlspecialchars($f, ENT_NOQUOTES), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('del_file_false') . ' -&gt; ' . htmlspecialchars($f, ENT_NOQUOTES) . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        self::$_instance->chmod($d, 0777);

        foreach (self::$_instance->iterator($d) as $f) {
            $realpath = self::$_instance->realpath($d . '/' . $f);
            $f = $realpath ? str_replace('\\', '/', $realpath) : str_replace('//', '/', $d . '/' . $f);
            self::$_instance->chmod($f, 0777);

            if (self::$_instance->is_dir($f) /*&& !self::$_instance->rmdir($f)*/) {
                $this->delDir($f . '/');
            } elseif (self::$_instance->file_exists($f)) {
                if (!self::$_instance->unlink($f)) {
                    $err .= $f . '<br/>';
                }
            }
        }

        if (!self::$_instance->rmdir($d)) {
            $err .= Errors::get() . '<br/>';
        }
        if ($err) {
            return Helper_View::message(Language::get('del_dir_false') . '<br/>' . $err, Helper_View::MESSAGE_ERROR);
        }
        return Helper_View::message(Language::get('del_dir_true') . ' -&gt; ' . htmlspecialchars($d, ENT_NOQUOTES), Helper_View::MESSAGE_SUCCESS);
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
                foreach (self::$_instance->iterator($d) as $file) {
                    if (self::$_instance->is_dir($d . '/' . $file)) {
                        $ds[] = $d . '/' . $file;
                    } else {
                        $sz += self::$_instance->filesize($d . '/' . $file);
                    }
                }
            } while (sizeof($ds) > 0);

            return $sz;
        }

        return self::$_instance->filesize($source);
    }


    /**
     * lookChmod
     * 
     * @param string $file
     * @return string
     */
    public function lookChmod ($file = '')
    {
        return substr(sprintf('%o', self::$_instance->fileperms($file)), -4);
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

        if (self::$_instance->file_put_contents($file, $text)) {
            return Helper_View::message(Language::get('fputs_file_true'), Helper_View::MESSAGE_SUCCESS) . $this->rechmod($file, $chmod);
        } else {
            return Helper_View::message(Language::get('fputs_file_false') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $g = explode(DIRECTORY_SEPARATOR, self::$_instance->getcwd());

        foreach (explode('/', $dir) as $d) {
            $tmp .= $d . '/';
            if (isset($g[$i])) {
                $tmp2 .= $g[$i] . '/';
            }

            if ($tmp == $tmp2 || self::$_instance->is_dir($tmp)) {
                $i++;
                continue;
            }
            if (!self::$_instance->mkdir($tmp, $chmod)) {
                $err .= Errors::get() . ' -&gt; ' . htmlspecialchars($tmp, ENT_NOQUOTES) . '<br/>';
            }
            $i++;
        }

        if ($err) {
            return Helper_View::message(Language::get('create_dir_false') . '<br/>' . $err, Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            return Helper_View::message(Language::get('create_dir_true'), Helper_View::MESSAGE_SUCCESS);
        }
    }


    /**
     * rechmod
     * 
     * @param string     $current
     * @param int|string $chmod
     * @return string
     */
    public function rechmod ($current = '', $chmod = 0755)
    {
        $chmod = $this->_chmoder($chmod);

        if ($chmod === false) {
            return Helper_View::message(Language::get('chmod_mode_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        if (self::$_instance->chmod($current, $chmod)) {
            return Helper_View::message(Language::get('chmod_true') . ' -&gt; ' . htmlspecialchars($current, ENT_NOQUOTES) . ' : ' . (is_int($chmod) ? decoct($chmod) : $chmod), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('chmod_false') . ' -&gt; ' . htmlspecialchars($current, ENT_NOQUOTES) . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        if (self::$_instance->is_dir($current)) {
            $this->copyD($current, $to);

            if ($del) {
                return $this->moveFiles($current, $name, $this->_staticName($current, $name), $overwrite);
            } else {
                return $this->copyFiles($current, $name, $this->_staticName($current, $name), $overwrite);
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
            return Helper_View::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR);
        }
        fputs($fp, $content);
        fclose($fp); 

        exec(escapeshellcmd(Config::get('PHP', 'path')) . ' -c -f -l ' . escapeshellarg($tmp), $rt, $v);
        unlink($tmp);
        $error = Errors::get();
        $size = sizeof($rt);

        if (!$size) {
            return Helper_View::message(Language::get('syntax_not_check') . '<br/>' . $error, Helper_View::MESSAGE_ERROR_EMAIL);
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
            $content = mb_convert_encoding($content, $charset[1], $charset[0]);
        }

        return Helper_View::message($pg, $erl ? Helper_View::MESSAGE_ERROR : Helper_View::MESSAGE_SUCCESS) . $this->code($content, $erl);
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
            return Helper_View::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR);
        }

        $content = rawurlencode($content);

        $wr = fwrite(
            $fp,
            'POST /syntax2/index.php HTTP/1.0' . "\r\n" .
            'Content-type: application/x-www-form-urlencoded; charset=' . $charset[0] . "\r\n" .
            'Content-length: ' . (mb_strlen($content) + 2) . "\r\n" .
            'Host: wapinet.ru' . "\r\n" .
            'Connection: close' . "\r\n" .
            'User-Agent: GManager ' . Config::getVersion() . "\r\n\r\n" .
            'f=' . $content . "\r\n\r\n"
        );
        if ($wr === false) {
            return Helper_View::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR);
        }

        $r = '';
        while ($r !== "\r\n") {
            $r = fgets($fp);
            if ($r === false) {
                return Helper_View::message(Language::get('syntax_not_check') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR);
            }
        }
        $r = '';
        while (!feof($fp)) {
            $r .= fread($fp, 1024);
        }
        fclose($fp);
        return $r;
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
            return Helper_View::message(Language::get('disable_function') . ' (xml)', Helper_View::MESSAGE_ERROR);
        }

        $fl = self::$_instance->file_get_contents($current);
        if ($charset[0]) {
            $fl = mb_convert_encoding($fl, $charset[1], $charset[0]);
        }

        $xml_parser = xml_parser_create();
        if (!xml_parse($xml_parser, $fl)) {
            $err = xml_error_string(xml_get_error_code($xml_parser));
            $line = xml_get_current_line_number($xml_parser);
            $column = xml_get_current_column_number($xml_parser);
            xml_parser_free($xml_parser);
            return Helper_View::message('Error [Line ' . $line . ', Column ' . $column . ']: ' . $err, Helper_View::MESSAGE_ERROR) . $this->code($fl, $line);
        } else {
            xml_parser_free($xml_parser);
            return Helper_View::message(Language::get('validator_true'), Helper_View::MESSAGE_SUCCESS) . $this->code($fl);
        }
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
        $array = $url ? Helper_View::urlHighlight($fl) : Helper_View::xhtmlHighlight($fl);
        $all = sizeof($array);
        $len = mb_strlen($all);
        $pg = '';
        for ($i = 0; $i < $all; ++$i) {
            $next = $i + 1;
            $l = mb_strlen($next);
            $pg .= '<span class="' . ($line == $next ? 'fail_code' : 'true_code') . '">' . ($l < $len ? str_repeat('&#160;', $len - $l) : '') . $next . '</span> ' . $array[$i] . "\n";
        }

        return '<div class="code"><pre><code>' . $pg . '</code></pre></div>';
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
            } elseif ($i == 1) {
                $name .= $tmp;
            }
            if ($i > 2) {
                break;
            }
        }

        if ($name == '') {
            $name = Helper_System::basename($file, '.gz');
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
            return Helper_View::message(Language::get('name') . ': ' . htmlspecialchars($info['name'], ENT_NOQUOTES) . '<br/>' . Language::get('archive_size') . ': ' . Helper_View::formatSize($this->size($c)) . '<br/>' . Language::get('real_size') . ': ' . Helper_View::formatSize($info['length']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), self::$_instance->filemtime($c)), Helper_View::MESSAGE_SUCCESS) . $this->code(trim($ext));
        } else {
            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
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
        if ($overwrite || !self::$_instance->file_exists($name . '/' . $info['name'])) {
            if (!self::$_instance->file_put_contents($name . '/' . $info['name'], $this->getGzContent($tmp))) {
                $data = Helper_View::message(Language::get('extract_file_false') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
            }
        } else {
            $data = Helper_View::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($name . '/' . $info['name'], ENT_NOQUOTES) . ')', Helper_View::MESSAGE_ERROR);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $this->ftpArchiveEnd();
        }
        if ($data) {
            return $data;
        }

        if (self::$_instance->is_file($name . '/' . $info['name'])) {
            if ($chmod[0]) {
                $this->rechmod($name . '/' . $info['name'], $chmod[0]);
            }
            return Helper_View::message(Language::get('extract_file_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
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

        if (mb_substr($dir, -1) != '/') {
            $name = Helper_System::basename($dir);
            $dir = dirname($dir) . '/';
        }

        if (self::$_instance->file_put_contents($dir . $name, file_get_contents($tmp))) {
            if ($chmod) {
                $this->rechmod($dir . $name, $chmod);
            }
            unlink($tmp);
            return Helper_View::message(Language::get('upload_true') . ' -&gt; ' . htmlspecialchars($fname . ' -> ' .$dir . $name, ENT_NOQUOTES), Helper_View::MESSAGE_SUCCESS);
        } else {
            $error = Errors::get();
            unlink($tmp);
            return Helper_View::message(Language::get('upload_false') . ' -&gt; ' . htmlspecialchars($fname . ' x ' .$dir . $name, ENT_NOQUOTES) . '<br/>' . $error, Helper_View::MESSAGE_ERROR_EMAIL);
        }
    }


    /**
     * _setIniHeaders
     *
     * @param array $headers
     * @return string
     */
    private function _setIniHeaders ($headers)
    {
        $out = array(0);

        foreach (explode("\n", trim($headers)) as $v) {
            if (mb_strripos($v, 'User-Agent:') === 0) {
                $out[0] = trim(mb_substr($v, 11));
            } else {
                $out[] = trim($v);
            }
        }

        return ini_set('user_agent', implode("\r\n", $out));
    }


    /**
     * _getUrlName
     *
     * @param string $url
     * @return string
     */
    private function _getUrlName ($url)
    {
        $name = '';

        $h = @get_headers($url, 1);
        if (isset($h['Content-Disposition'])) {
            preg_match('/.+;\s+filename=(?:")?([^"]+)/i', $h['Content-Disposition'], $arr);
            if (isset($arr[1])) {
                $name = Helper_System::basename($arr[1]);
            }
        }

        return ($name != '' ? $name : rawurldecode(Helper_System::basename(parse_url($url, PHP_URL_PATH))));
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
            @set_time_limit($set_time_limit);
        }
        if ($ignore_user_abort) {
            ignore_user_abort(true);
        }

        $this->_setIniHeaders($headers);

        $tmp = array();
        $url = trim($url);

        if (mb_strpos($url, "\n") !== false) {
            foreach (explode("\n", $url) as $v) {
                $v = trim($v);
                $tmp[] = array($v, $name . Helper_System::basename($v));
            }
        } else {
            $last = mb_substr($name, -1);
            $temp = false;
            if ($last != '/' && self::$_instance->is_dir($name)) {
                $name .= '/';
                $temp = true;
            }

            if ($last != '/' && !$temp) {
                $name = dirname($name) . '/' . Helper_System::basename($name);
            } else {
                $name .= $this->_getUrlName($url);
            }
            $tmp[] = array($url, $name);
        }

        $out = '';
        foreach ($tmp as $v) {
            $dir = dirname($v[1]);
            if (!self::$_instance->is_dir($dir)) {
                self::$_instance->mkdir($dir, 0755);
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                if ($tmp) {
                    $r = self::$_instance->file_put_contents($v[1], $tmp['body']);
                    self::$_instance->chmod($v[1], $chmod);
                } else {
                    $r = false;
                }
            } else {
                $r = self::$_instance->copy($v[0], $v[1], $chmod);
            }

            if ($r) {
                $out .= Helper_View::message(Language::get('upload_true') . ' -&gt; ' . htmlspecialchars($v[0] . ' -> ' . $v[1], ENT_NOQUOTES), Helper_View::MESSAGE_SUCCESS);
            } else {
                $out .= Helper_View::message(Language::get('upload_false') . ' -&gt; ' . htmlspecialchars($v[0] . ' x ' . $v[1], ENT_NOQUOTES) . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        if (mail($to, '=?UTF-8?B?' . base64_encode($theme) . '?=', wordwrap($mess, 70, "\n"), 'From: ' . $from . "\r\nContent-type: text/plain; charset=UTF-8\r\nX-Mailer: Gmanager " . Config::getVersion())) {
            return Helper_View::message(Language::get('send_mail_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('send_mail_false') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
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
        ob_start();
        $evalFunction = create_function('', $eval . "\n");

        $info = array(
            'time' => microtime(true),
            'ram' => memory_get_usage(false)
        );

        //eval($eval);
        $evalFunction();

        $info = array(
            'ram' => Helper_View::formatSize(memory_get_usage(false) - $info['ram'], 6),
            'time' => round(microtime(true) - $info['time'], 6),
        );

        $buf = ob_get_contents();
        ob_end_clean();


        if (mb_substr($buf, 0, mb_strlen(ini_get('error_prepend_string'))) == ini_get('error_prepend_string')) {
            $buf = mb_substr($buf, mb_strlen(ini_get('error_prepend_string')));
        }
        if (mb_substr($buf, -mb_strlen(ini_get('error_append_string'))) == ini_get('error_append_string')) {
            $buf = mb_substr($buf, 0, -mb_strlen(ini_get('error_append_string')));
        }

        return '<div class="input">' . Language::get('result') . '<br/><textarea class="lines" cols="48" rows="' . Helper_View::getRows($buf) . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea><br/>' . str_replace('%time%', $info['time'], Language::get('microtime')) . '<br/>' . Language::get('memory_get_usage') . ' ' . $info['ram'] . '<br/></div>';
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

        if (Config::get('Gmanager', 'altEncoding') != 'UTF-8') {
            $cmd = mb_convert_encoding($cmd, Config::get('Gmanager', 'altEncoding'), 'UTF-8');
        }

        if ($h = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w')), $pipes)) {
            //fwrite($pipes[0], '');
            fclose($pipes[0]);

            $buf = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            proc_close($h);

            if (Config::get('Gmanager', 'consoleEncoding') != 'UTF-8') {
                $buf = mb_convert_encoding($buf, 'UTF-8', Config::get('Gmanager', 'consoleEncoding'));
            }
        } else {
            return '<div class="red">' . Language::get('cmd_error') . '<br/></div>';
        }
        return '<div class="input">' . Language::get('result') . '<br/><textarea class="lines" cols="48" rows="' . Helper_View::getRows($buf) . '">' . htmlspecialchars($buf, ENT_NOQUOTES) . '</textarea></div>';
    }


    /**
     * _replace
     *
     * @param string $content
     * @param string $from
     * @param string $to
     * @param bool   $regexp
     * @param bool   $caseLess
     * @return array          array('message' => '', 'content' => '')
     */
    private function _replace ($content = '', $from = '', $to = '', $regexp = false, $caseLess = true)
    {
        $output = array(
            'content' => $content,
            'message' => ''
        );

        if ($from == '') {
            $output['message'] = Helper_View::message(Language::get('replace_false_str'), Helper_View::MESSAGE_ERROR);
        } else {
            $pattern = '/' . ($regexp ? str_replace('/', '\/', $from) : preg_quote($from, '/')) . '/u'; // always Unicode
            $pattern = $caseLess ? $pattern : $pattern . 'i';

            $out = preg_replace($pattern, $to, $content, -1, $count);
            if ($out === null || preg_last_error() !== PREG_NO_ERROR) {
                $output['message'] = Helper_View::message(Language::get('regexp_error'), Helper_View::MESSAGE_ERROR);
            } else {
                $output['message'] = Helper_View::message(Language::get('replace_true') . $count, Helper_View::MESSAGE_SUCCESS);
                $output['content'] = $out;
            }
        }

        return $output;
    }


    /**
     * replace
     * 
     * @param string $current
     * @param string $from
     * @param string $to
     * @param bool   $regexp
     * @param bool   $caseLess
     * @return array          array('message' => '', 'content' => '')
     */
    public function replace ($current = '', $from = '', $to = '', $regexp = false, $caseLess = true)
    {
        return $this->_replace(
            self::$_instance->file_get_contents($current),
            $from,
            $to,
            $regexp,
            $caseLess
        );
    }


    /**
     * replaceZip
     * 
     * @param string $current
     * @param string $f
     * @param string $from
     * @param string $to
     * @param bool   $regexp
     * @param bool   $caseLess
     * @return array          array('message' => '', 'content' => '')
     */
    public function replaceZip ($current = '', $f = '', $from = '', $to = '', $regexp = false, $caseLess = true)
    {
        $archive = new Archive;
        $c = $archive->setFormat(Archive::FORMAT_ZIP)->setFile($current)->factory()->getEditFile($f);

        return $this->_replace(
            $c['text'],
            $from,
            $to,
            $regexp,
            $caseLess
        );
    }


    /**
     * search
     * 
     * @param string $where    where
     * @param string $search   search string
     * @param bool   $inText   in text
     * @param bool   $caseLess register
     * @param bool   $regexp   regexp
     * @param int    $limit    max file size
     * @param bool   $archive  in gz archives
     * @return string
     */
    public function search ($where = '', $search = '', $inText = false, $caseLess = false, $regexp = false, $limit = 8388608, $archive = false)
    {
        $html = ListData::getListSearchData($where, $search, $inText, $caseLess, $regexp, $limit, $archive);
        return $html ? $html : ListData::getListEmptySearchData();
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
        $info['dirname'] = IOWrapper::get($info['dirname']);
        $info['basename'] = IOWrapper::get($info['basename']);
        $info['extension'] = IOWrapper::get($info['extension']);
        $info['filename'] = IOWrapper::get($info['filename']);


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
                $name = str_replace($var[0], mb_substr(str_shuffle(Config::get('Gmanager', 'rand')), 0, mt_rand((!empty($var[1]) ? $var[1] : 8), (!empty($var[2]) ? $var[2] : 16))), $name);
            }
        }
        $name = str_replace('[f]', $info['extension'], $name);
        $name = str_replace('[name]', $info['filename'], $name);
        $name = str_replace('[date]', strftime('%d_%m_%Y'), $name);

        if ($register == 1) {
            $tmp = mb_strtolower($name);
        } elseif ($register == 2) {
            $tmp = mb_strtoupper($name);
        } else {
            $tmp = $name;
        }

        if (!$overwrite && self::$_instance->file_exists($info['dirname'] . '/' . $tmp)) {
            return Helper_View::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($info['dirname'] . '/' . $tmp, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_ERROR);
        }

        if (self::$_instance->rename($f, $info['dirname'] . '/' . $tmp)) {
            return Helper_View::message($info['basename'] . ' - ' . $tmp, Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Errors::get() . ' ' . $info['basename'] . ' -&gt; ' . $tmp, Helper_View::MESSAGE_ERROR_EMAIL);
        }
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
                $out .= (isset($_SERVER['HTTP_USER_AGENT']) ? 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n" : '');
                $out .= (isset($_SERVER['HTTP_ACCEPT']) ? 'Accept: ' . $_SERVER['HTTP_ACCEPT'] . "\r\n" : '');
                $out .= (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\r\n" : '');
                $out .= (isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? 'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'] . "\r\n" : '');
                //$out .= 'TE: deflate, gzip, chunked, identity, trailers' . "\r\n";
                //$out .= 'Accept-Encoding: deflate, gzip, chunked, identity, trailers' . "\r\n";
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
     * ftpMoveFiles
     * 
     * @param string $from
     * @param string $to
     * @param int    $chmodf
     * @param int    $chmodd
     * @param bool   $overwrite
     */
    public function ftpMoveFiles ($from = '', $to = '', $chmodf = 0644, $chmodd = 0755, $overwrite = false)
    {
        foreach (self::$_instance->iterator($from) as $f) {
            if (self::$_instance->is_dir($from . '/' . $f)) {
                self::$_instance->mkdir($to . '/' . $f, $chmodd);
                $this->ftpMoveFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
            } else {
                if ($overwrite || !self::$_instance->file_exists($to . '/' . $f)) {
                    self::$_instance->file_put_contents($to . '/' . $f, self::$_instance->file_get_contents($from . '/' . $f));
                    $this->rechmod($to . '/' . $f, $chmodf);
                }

                self::$_instance->unlink($from . '/' . $f);
            }
        }

        self::$_instance->rmdir($from);
    }


    /**
     * ftpCopyFiles
     * 
     * @param string $from
     * @param string $to
     * @param int    $chmodf
     * @param int    $chmodd
     * @param bool   $overwrite
     */
    public function ftpCopyFiles ($from = '', $to = '', $chmodf = 0644, $chmodd = 0755, $overwrite = false)
    {
        foreach (self::$_instance->iterator($from) as $f) {
            if (self::$_instance->is_dir($from . '/' . $f)) {
                self::$_instance->mkdir($to . '/' . $f, $chmodd);
                $this->ftpCopyFiles($from . '/' . $f, $to . '/' . $f, $chmodf, $chmodd, $overwrite);
            } else {
                if ($overwrite || !self::$_instance->file_exists($to . '/' . $f)) {
                    self::$_instance->file_put_contents($to . '/' . $f, self::$_instance->file_get_contents($from . '/' . $f));
                    $this->rechmod($to . '/' . $f, $chmodf);
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
        $this->_ftpArchiveTmp = Config::getTemp() . '/GmanagerFtpArchive' . GMANAGER_REQUEST_TIME . '.tmp';
        file_put_contents($this->_ftpArchiveTmp, self::$_instance->file_get_contents($current));
        return $this->_ftpArchiveTmp;
    }


    /**
     * ftpArchiveEnd
     * 
     * @param string $current
     * @return bool
     */
    public function ftpArchiveEnd ($current = '')
    {
        $result = ($current != '') ? self::$_instance->file_put_contents($current, file_get_contents($this->_ftpArchiveTmp)) : true;
        unlink($this->_ftpArchiveTmp);
        return (bool)$result;
    }


    /**
     * getUname
     * 
     * @return string
     */
    public function getUname ()
    {
        $uname = php_uname();
        if (Config::get('Gmanager', 'altEncoding') != 'UTF-8') {
            $uname = mb_convert_encoding($uname, 'UTF-8', Config::get('Gmanager', 'altEncoding'));
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
     * Valid chmod
     *
     * @param int|string $chmod
     * @return int|bool
     * @access protected
     */
    protected function _chmoder ($chmod)
    {
        $chmod = decoct(intval($chmod, 8)); // string to integer, integer to string

        if (strlen($chmod) != 3) {
            return false;
        }

        return octdec('0' . $chmod);
    }
}
