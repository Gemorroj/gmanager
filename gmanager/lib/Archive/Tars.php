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


class Archive_Tars implements Archive_Interface
{
    /**
     * _archiveTar
     * 
     * @param string $file
     * @return object
     */
    private function _archiveTar($file)
    {
        return new Archive_Tar(Config::get('Gmanager', 'mode') == 'FTP' ? Registry::getGmanager()->ftpArchiveStart($file) : IOWrapper::set($file));
    }


    /**
     * createArchive
     * 
     * @param string $name
     * @param mixed  $chmod
     * @param array  $ext
     * @param string $comment
     * @param bool   $overwrite
     * @return string
     */
    public function createArchive ($name, $chmod = 0644, $ext = array(), $comment = '', $overwrite = false)
    {
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_FAIL);
    }


    /**
     * addFile
     * 
     * @param string $current
     * @param mixed  $ext
     * @param string $dir
     * @return string
     */
    public function addFile ($current, $ext = array(), $dir = '')
    {
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $ftp_name = Config::getTemp() . '/GmanagerFtpTar' . GMANAGER_REQUEST_TIME . '/';
            mkdir($ftp_name, 0777);

            $tmp = array();
            foreach ($ext as $v) {
                $b = IOWrapper::set(basename($v));
                $tmp[] = $ftp_name . $b;
                file_put_contents($ftp_name . $b, Registry::getGmanager()->file_get_contents($v));
            }
            $ext = $tmp;
            unset($tmp);
        }

        $tgz = $this->_archiveTar($current);

        foreach ($ext as $v) {
            $add = $tgz->addModify($v, $dir, dirname($v));
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            if (!Registry::getGmanager()->ftpArchiveEnd($current)) {
                $add = false;
            }
            Registry::getGmanager()->clean($ftp_name);
        }

        if ($add) {
            return Errors::message(Language::get('add_archive_true'), Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('add_archive_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * delFile
     * 
     * @param string $current
     * @param string $f
     * @return string
     */
    public function delFile ($current, $f = '')
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

        $tmp_name = Config::getTemp() . '/GmanagerTar' . GMANAGER_REQUEST_TIME . '/';
        $tgz->extractList($new_tar, $tmp_name);

        Registry::getGmanager()->unlink($current);
        $list = $tgz->createModify($tmp_name, '.', $tmp_name);
        Registry::getGmanager()->clean($tmp_name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Registry::getGmanager()->ftpArchiveEnd($current);
        }

        if ($list) {
            return Errors::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Errors::MESSAGE_OK);
        } else {
            return Errors::message(Language::get('del_file_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractFile
     * 
     * @param string $current
     * @param string $name
     * @param mixed  $chmod
     * @param string $ext
     * @param bool   $overwrite
     * @return string
     */
    public function extractFile ($current, $name = '', $chmod = '', $ext = '', $overwrite = false)
    {
        $tmp = array();
        $err = '';
        foreach ($ext as $f) {
            if (Registry::getGmanager()->file_exists($name . '/' . $f)) {
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

        if (Config::get('Gmanager', 'mode') == 'FTP') {
               $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
               $ftp_name = Config::getTemp() . '/GmanagerFtpTarFile' . GMANAGER_REQUEST_TIME . '.tmp';
        }

        $tgz = $this->_archiveTar($current);

        if (!$tgz->extractList($ext, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Registry::getGmanager()->createDir($sysName);
            Registry::getGmanager()->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            Registry::getGmanager()->ftpArchiveEnd();
        }

        if (Config::get('Gmanager', 'mode') == 'FTP' || Registry::getGmanager()->is_dir($name)) {
            if ($chmod) {
                Registry::getGmanager()->rechmod($name, $chmod);
            }
            return Errors::message(Language::get('extract_file_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_file_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * extractArchive
     * 
     * @param string $current
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     * @return string
     */
    public function extractArchive ($current, $name = '', $chmod = array(), $overwrite = false)
    {
        $sysName = IOWrapper::set($name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($current) . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpTar' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }

        $tgz = $this->_archiveTar($current);
        $extract = $tgz->listContent();
        $err = '';

        if ($overwrite) {
            $res = $tgz->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName);
        } else {
            $list = array();
            foreach ($extract as $f) {
                if (Registry::getGmanager()->file_exists($name . '/' . IOWrapper::get($f['filename']))) {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f['filename'], ENT_NOQUOTES) . ')<br/>';
                } else {
                    $list[] = $f['filename'];
                }
            }
            if (!$list) {
                Registry::getGmanager()->ftpArchiveEnd();
                return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
            }
    
            $res = $tgz->extractList($list, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName);
        }

        if (!$res) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
                rmdir($ftp_name);
            }
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }

        foreach ($extract as $f) {
            if (Registry::getGmanager()->is_dir($name . '/' . IOWrapper::get($f['filename']))) {
                Registry::getGmanager()->rechmod($name . '/' . IOWrapper::get($f['filename']), $chmod[1]);
            } else {
                Registry::getGmanager()->rechmod($name . '/' . IOWrapper::get($f['filename']), $chmod[0]);
            }
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Registry::getGmanager()->createDir($sysName, $chmod[1]);
            Registry::getGmanager()->ftpMoveFiles($ftp_name, $sysName, $chmod[0], $chmod[1], $overwrite);
            Registry::getGmanager()->ftpArchiveEnd();
        }

        if (Config::get('Gmanager', 'mode') == 'FTP' || Registry::getGmanager()->is_dir($name)) {
            Registry::getGmanager()->rechmod($name, $chmod[1]);
            return Errors::message(Language::get('extract_true'), Errors::MESSAGE_OK) . ($err ? Errors::message(rtrim($err, '<br/>'), Errors::MESSAGE_FAIL) : '');
        } else {
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_EMAIL);
        }
    }


    /**
     * lookFile
     * 
     * @param string $current
     * @param string $f
     * @param string $str
     * @return string
     */
    public function lookFile ($current, $f = '', $str = null)
    {
        $tgz = $this->_archiveTar($current);
        $ext = $tgz->extractInString($f);

        if (!$ext) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        } else {
            $list = $tgz->listContent();

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                if ($list[$i]['filename'] != $f) {
                    continue;
                } else {
                    if ($str) {
                        return $ext;
                    } else {
                        return Errors::message(Language::get('real_size') . ': ' . Registry::getGmanager()->formatSize($list[$i]['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), $list[$i]['mtime']), Errors::MESSAGE_OK) . Registry::getGmanager()->code(trim($ext));
                    }
                }
            }
        }
    }


    /**
     * getEditFile
     * 
     * @param string $current
     * @param string $f
     * @return array
     */
    public function getEditFile ($current, $f = '')
    {
        return array('text' => Language::get('not_supported'), 'size' => 0, 'lines' => 0);
    }


    /**
     * setEditFile
     * 
     * @param string $current
     * @param string $f
     * @param string $text
     * @return string
     */
    public function setEditFile ($current, $f = '', $text = '')
    {
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_EMAIL);
    }


    /**
     * listArchive
     * 
     * @param string $current
     * @param string $down
     * @return string
     */
    public function listArchive ($current, $down = '')
    {
        $tgz = $this->_archiveTar($current);

        if (!$list = $tgz->listContent()) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL) . '</td></tr>';
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
                    $type = htmlspecialchars(Registry::getGmanager()->getType($list[$i]['filename']), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(Registry::getGmanager()->strLink($list[$i]['filename'], true), ENT_NOQUOTES) . '</a>';
                    $size = Registry::getGmanager()->formatSize($list[$i]['size']);
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
                Registry::getGmanager()->ftpArchiveEnd();
            }

            return $l;
        }
    }


    /**
     * renameFile
     * 
     * @param string $current
     * @param string $name
     * @param string $arch_name
     * @param bool   $del
     * @param bool   $overwrite
     * @return string
     */
    public function renameFile ($current, $name, $arch_name, $del = false, $overwrite = false)
    {
        $tmp        = Config::getTemp() . '/GmanagerTar' . GMANAGER_REQUEST_TIME;
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
            Registry::getGmanager()->clean($tmp);
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }
            return Errors::message(Language::get('extract_false'), Errors::MESSAGE_FAIL);
        }

        if (file_exists($tmp . '/' . $sysName)) {
            if ($overwrite) {
                if ($folder) {
                    Registry::getGmanager()->clean($tmp . '/' . $sysName);
                } else {
                    unlink($tmp . '/' . $sysName);
                }
            } else {
                Registry::getGmanager()->clean($tmp);
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                    Registry::getGmanager()->ftpArchiveEnd();
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
            if ($del) {
                $result = Registry::getGmanager()->moveFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            } else {
                $result = Registry::getGmanager()->copyFiles($tmp . '/' . $name, $tmp . '/' . $arch_name);
            }
        } else {
            if ($del) {
                $result = rename($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            } else {
                $result = copy($tmp . '/' . $arch_name, $tmp . '/' . $sysName);
            }
        }

        if (!$result) {
            Registry::getGmanager()->clean($tmp);
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
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

        Registry::getGmanager()->clean($tmp);
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Registry::getGmanager()->ftpArchiveEnd($current);
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
}

?>
