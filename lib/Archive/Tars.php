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


class Archive_Tars implements Archive_Interface
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var Archive_Tar
     */
    private $_archive;


    /**
     * Constructor
     *
     * @param string $name Archive filename
     */
    public function __construct ($name)
    {
        $this->_name = $name;
    }


    /**
     * Open Archive
     *
     * @return Archive_Tar
     */
    private function _open()
    {
        if ($this->_archive === null) {
            require_once 'Archive/Tar.php';
            $this->_archive = new Archive_Tar(Config::get('Gmanager', 'mode') == 'FTP' ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : IOWrapper::set($this->_name));
        }
        return $this->_archive;
    }


    /**
     * createArchive
     *
     * @param mixed  $chmod
     * @param array  $ext
     * @param string $comment
     * @param bool   $overwrite
     * @return string
     */
    public function createArchive ($chmod = 0644, $ext = array(), $comment = '', $overwrite = false)
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }


    /**
     * addFile
     *
     * @param mixed  $ext
     * @param string $dir
     * @return string
     */
    public function addFile ($ext = array(), $dir = '')
    {
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $ftp_name = Config::getTemp() . '/GmanagerFtpTar' . GMANAGER_REQUEST_TIME . '/';
            mkdir($ftp_name, 0777);

            $tmp = array();
            foreach ($ext as $v) {
                $b = IOWrapper::set(Helper_System::basename($v));
                $tmp[] = $ftp_name . $b;
                file_put_contents($ftp_name . $b, Gmanager::getInstance()->file_get_contents($v));
            }
            $ext = $tmp;
            unset($tmp);
        }

        $tgz = $this->_open();

        $add = true;
        foreach ($ext as $v) {
            $add = $tgz->addModify($v, $dir, dirname($v));
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            if (!Gmanager::getInstance()->ftpArchiveEnd($this->_name)) {
                $add = false;
            }
            Helper_System::clean($ftp_name);
        }

        if ($add) {
            return Helper_View::message(Language::get('add_archive_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('add_archive_false'), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $tgz = $this->_open();

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

        $tmp_name = Config::getTemp() . '/GmanagerTar' . GMANAGER_REQUEST_TIME . '/';
        $tgz->extractList($new_tar, $tmp_name);

        Gmanager::getInstance()->unlink($this->_name);
        $list = $tgz->createModify($tmp_name, '.', $tmp_name);
        Helper_System::clean($tmp_name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($list) {
            return Helper_View::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('del_file_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_ERROR_EMAIL);
        }
    }


    /**
     * extractFile
     *
     * @param string $name
     * @param mixed  $chmod
     * @param array $ext
     * @param bool   $overwrite
     * @return string
     */
    public function extractFile ($name = '', $chmod = '', $ext = array(), $overwrite = false)
    {
        $tmp = array();
        $err = '';
        foreach ($ext as $f) {
            if (Gmanager::getInstance()->file_exists(str_replace('//', '/', $name . '/' . $f))) {
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
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR) . ($err ? Helper_View::message(rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        }

        $sysName = IOWrapper::set($name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
               $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($this->_name) . '/') . '/' . $sysName);
               $ftp_name = Config::getTemp() . '/GmanagerFtpTarFile' . GMANAGER_REQUEST_TIME . '.tmp';
        }

        $tgz = $this->_open();

        if (!$tgz->extractList($ext, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->createDir($sysName);
            Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (Config::get('Gmanager', 'mode') == 'FTP' || Gmanager::getInstance()->is_dir($name)) {
            if ($chmod) {
                Gmanager::getInstance()->rechmod($name, $chmod);
            }
            return Helper_View::message(Language::get('extract_file_true'), Helper_View::MESSAGE_SUCCESS) . ($err ? Helper_View::message(rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        } else {
            return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }
    }


    /**
     * extractArchive
     *
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function extractArchive ($name = '', $chmod = array(), $overwrite = false)
    {
        $sysName = IOWrapper::set($name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($this->_name) . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpTar' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }

        $tgz = $this->_open();
        $extract = $tgz->listContent();
        $err = '';

        if ($overwrite) {
            $res = $tgz->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName);
        } else {
            $list = array();
            foreach ($extract as $f) {
                if (Gmanager::getInstance()->file_exists($name . '/' . IOWrapper::get($f['filename']))) {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f['filename'], ENT_NOQUOTES) . ')<br/>';
                } else {
                    $list[] = $f['filename'];
                }
            }
            if (!$list) {
                Gmanager::getInstance()->ftpArchiveEnd();
                return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR) . ($err ? Helper_View::message(rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
            }
    
            $res = $tgz->extractList($list, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName);
        }

        if (!$res) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
                rmdir($ftp_name);
            }
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        foreach ($extract as $f) {
            if (Gmanager::getInstance()->is_dir($name . '/' . IOWrapper::get($f['filename']))) {
                Gmanager::getInstance()->rechmod($name . '/' . IOWrapper::get($f['filename']), $chmod[1]);
            } else {
                Gmanager::getInstance()->rechmod($name . '/' . IOWrapper::get($f['filename']), $chmod[0]);
            }
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->createDir($sysName, $chmod[1]);
            Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, $chmod[0], $chmod[1], $overwrite);
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (Config::get('Gmanager', 'mode') == 'FTP' || Gmanager::getInstance()->is_dir($name)) {
            Gmanager::getInstance()->rechmod($name, $chmod[1]);
            return Helper_View::message(Language::get('extract_true'), Helper_View::MESSAGE_SUCCESS) . ($err ? Helper_View::message(rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        } else {
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }
    }


    /**
     * lookFile
     *
     * @param string $f
     * @param string $str
     * @return string
     */
    public function lookFile ($f = '', $str = null)
    {
        $tgz = $this->_open();
        $ext = $tgz->extractInString($f);

        if (!$ext) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            $list = $tgz->listContent();

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                if ($list[$i]['filename'] != $f) {
                    continue;
                } else {
                    if ($str) {
                        return $ext;
                    } else {
                        return Helper_View::message(Language::get('real_size') . ': ' . Helper_View::formatSize($list[$i]['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), $list[$i]['mtime']), Helper_View::MESSAGE_SUCCESS) . Gmanager::getInstance()->code(trim($ext));
                    }
                }
            }
        }
    }


    /**
     * getEditFile
     *
     * @param string $f
     * @return array
     */
    public function getEditFile ($f = '')
    {
        return array('text' => Language::get('not_supported'), 'size' => 0, 'lines' => 0);
    }


    /**
     * setEditFile
     *
     * @param string $f
     * @param string $text
     * @return string
     */
    public function setEditFile ($f = '', $text = '')
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR_EMAIL);
    }


    /**
     * listArchive
     *
     * @todo refactoring to ListData
     * @param string $down
     * @return string
     */
    public function listArchive ($down = '')
    {
        $tgz = $this->_open();
        $list = $tgz->listContent();

        if (!$list) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL) . '</td></tr>';
        } else {
            $r_current = Helper_View::getRawurl($this->_name);
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                $r_name = Helper_View::getRawurl($list[$i]['filename']);
    
                if ($list[$i]['typeflag']) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]['filename'], ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars(Helper_System::getType($list[$i]['filename']), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(Helper_View::strLink($list[$i]['filename'], true), ENT_NOQUOTES) . '</a>';
                    $size = Helper_View::formatSize($list[$i]['size']);
                    $down = '<a href="change.php?get=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('get') . '</a>';
                }
                $l .= '<tr class="border"><td class="check"><input name="check[]" type="checkbox" value="' . $r_name . '"/></td>';
                if (Config::get('Display', 'name')) {
                    $l .= '<td>' . $name . '</td>';
                }
                if (Config::get('Display', 'down')) {
                    $l .= '<td>' . $down . '</td>';
                }
                if (Config::get('Display', 'type')) {
                    $l .= '<td>' . $type . '</td>';
                }
                if (Config::get('Display', 'size')) {
                    $l .= '<td>' . $size . '</td>';
                }
                if (Config::get('Display', 'change')) {
                    $l .= '<td><a href="change.php?c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::get('Display', 'del')) {
                    $l .= '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del_tar_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('dl') . '</a></td>';
                }
                if (Config::get('Display', 'chmod')) {
                    $l .= '<td> </td>';
                }
                if (Config::get('Display', 'date')) {
                    $l .= '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), $list[$i]['mtime']) . '</td>';
                }
                if (Config::get('Display', 'uid')) {
                    $l .= '<td> </td>';
                }
                if (Config::get('Display', 'gid')) {
                    $l .= '<td> </td>';
                }
                if (Config::get('Display', 'n')) {
                    $l .= '<td>' . ($i + 1) . '</td>';
                }

                $l .= '</tr>';
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return $l;
        }
    }


    /**
     * renameFile
     *
     * @param string $name
     * @param string $arch_name
     * @param bool   $del
     * @param bool   $overwrite
     * @return string
     */
    public function renameFile ($name, $arch_name, $del = false, $overwrite = false)
    {
        $tmp        = Config::getTemp() . '/GmanagerTar' . GMANAGER_REQUEST_TIME;
        $tgz        = $this->_open();
        $sysName    = IOWrapper::set($name);

        $folder = '';
        foreach ($tgz->listContent() as $f) {
            if ($arch_name == $f['filename']) {
                $folder = $f['typeflag'] == 5 ? 1 : 0;
                break;
            }
        }

        if (!$tgz->extract($tmp)) {
            Helper_System::clean($tmp);
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR);
        }

        if (file_exists($tmp . '/' . $sysName)) {
            if ($overwrite) {
                if ($folder) {
                    Helper_System::clean($tmp . '/' . $sysName);
                } else {
                    unlink($tmp . '/' . $sysName);
                }
            } else {
                Helper_System::clean($tmp);
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                    Gmanager::getInstance()->ftpArchiveEnd();
                }
                return Helper_View::message(Language::get('overwrite_false'), Helper_View::MESSAGE_ERROR);
            }
        }

        if ($folder) {
            @mkdir($tmp . '/' . $sysName, 0755, true);
        } else {
            @mkdir($tmp . '/' . dirname($sysName), 0755, true);
        }

        if ($folder) {
            if ($del) {
                $result = Gmanager::getInstance()->moveFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            } else {
                $result = Gmanager::getInstance()->copyFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            }
        } else {
            if ($del) {
                $result = rename($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            } else {
                $result = copy($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            }
        }

        if (!$result) {
            Helper_System::clean($tmp);
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            if ($folder) {
                if ($del) {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Helper_View::MESSAGE_ERROR);
                } else {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Helper_View::MESSAGE_ERROR);
                }
            } else {
                if ($del) {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Helper_View::MESSAGE_ERROR);
                } else {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Helper_View::MESSAGE_ERROR);
                }
            }
        }

        $result = $tgz->createModify($tmp, '.', $tmp);

        Helper_System::clean($tmp);
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($result) {
            if ($folder) {
                if ($del) {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_true')), Helper_View::MESSAGE_SUCCESS);
                } else {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_true')), Helper_View::MESSAGE_SUCCESS);
                }
            } else {
                if ($del) {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_true')), Helper_View::MESSAGE_SUCCESS);
                } else {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_true')), Helper_View::MESSAGE_SUCCESS);
                }
            }
        } else {
            if ($folder) {
                if ($del) {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_false')), Helper_View::MESSAGE_ERROR);
                } else {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_false')), Helper_View::MESSAGE_ERROR);
                }
            } else {
                if ($del) {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_false')), Helper_View::MESSAGE_ERROR);
                } else {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_false')), Helper_View::MESSAGE_ERROR);
                }
            }
        }
    }
}
