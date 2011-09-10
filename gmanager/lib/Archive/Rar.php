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


class Archive_Rar implements Archive_Interface
{
    /**
     * _rarOpen
     * 
     * @param string $file
     * @return object
     */
    private function _rarOpen($file)
    {
        return rar_open(Config::get('Gmanager', 'mode') == 'FTP' ? Registry::getGmanager()->ftpArchiveStart($file) : IOWrapper::set($file));
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
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_FAIL);
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
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_FAIL);
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
            $ftp_name = Config::getTemp() . '/GmanagerFtpRarFile' . GMANAGER_REQUEST_TIME . '.tmp';
        }

        $rar = $this->_rarOpen($current);

        foreach ($ext as $var) {
            $entry = rar_entry_get($rar, $var);
            if (!$entry->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                }
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            } else if (!Registry::getGmanager()->file_exists((Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $name) . '/' . $var)) {
                // fix bug in rar extension
                // method extract alredy returned "true"
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            }
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
            $ftp_name = Config::getTemp() . '/GmanagerFtpRar' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }

        $rar = $this->_rarOpen($current);
        $err = '';
        foreach (rar_list($rar) as $f) {
            $n = $f->getName();

            if (!$overwrite && Registry::getGmanager()->file_exists($name . '/' . IOWrapper::get($n))) {
                $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($n, ENT_NOQUOTES) . ')<br/>';
            } else {
                $entry = rar_entry_get($rar, $n);
                if (!$entry->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
                    if (Config::get('Gmanager', 'mode') == 'FTP') {
                        Registry::getGmanager()->ftpArchiveEnd();
                        rmdir($ftp_name);
                    }
                    $err .= str_replace('%file%', htmlspecialchars($n, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
                }
            }

            if (Registry::getGmanager()->is_dir($name . '/' . IOWrapper::get($n))) {
                Registry::getGmanager()->rechmod($name . '/' . IOWrapper::get($n), $chmod[1]);
            } else {
                Registry::getGmanager()->rechmod($name . '/' . IOWrapper::get($n), $chmod[0]);
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
        $rar = $this->_rarOpen($current);
        $entry = rar_entry_get($rar, $f);

        // создаем временный файл
        $tmp = Config::getTemp() . '/GmanagerRAR' . GMANAGER_REQUEST_TIME . '.tmp';
        $entry->extract(true, $tmp); // запишет сюда данные

        $ext = file_get_contents($tmp);
        unlink($tmp);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Registry::getGmanager()->ftpArchiveEnd();
        }

        if (!$ext) {
            return Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL);
        } else {
            if ($str) {
                return $ext;
            } else {
                return Errors::message(Language::get('archive_size') . ': ' . Registry::getGmanager()->formatSize($entry->getPackedSize()) . '<br/>' . Language::get('real_size') . ': ' . Registry::getGmanager()->formatSize($entry->getUnpackedSize()) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), strtotime($entry->getFileTime())), Errors::MESSAGE_OK) . Registry::getGmanager()->code(trim($ext));
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
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_FAIL);
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
        return Errors::message(Language::get('not_supported'), Errors::MESSAGE_FAIL);
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
        $r_current = str_replace('%2F', '/', rawurlencode($current));
        $rar = $this->_rarOpen($current);

        if (!$list = rar_list($rar)) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Registry::getGmanager()->ftpArchiveEnd();
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Errors::message(Language::get('archive_error'), Errors::MESSAGE_EMAIL) . '</td></tr>';
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
                    $type = htmlspecialchars(Registry::getGmanager()->getType($list[$i]->getName()), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(Registry::getGmanager()->strLink($list[$i]->getName(), true), ENT_NOQUOTES) . '</a>';
                    $size = Registry::getGmanager()->formatSize($list[$i]->getUnpackedSize());
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
                    $l .= '<td> </td>';
                }
                if (Config::get('Display', 'del')) {
                    $l .= '<td>' . Language::get('dl') . '</td>';
                }
                if (Config::get('Display', 'chmod')) {
                    $l .= '<td> </td>';
                }
                if (Config::get('Display', 'date')) {
                    $l .= '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), strtotime($list[$i]->getFileTime())) . '</td>';
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
}

?>
