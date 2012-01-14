<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8
 * 
 * PHP version >= 5.2.1
 * 
 */


class Archive_Rar implements Archive_Interface
{
    private $_name;
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
     * @return RarArchive
     */
    private function _open()
    {
        if ($this->_archive === null) {
            $this->_archive = rar_open(Config::get('Gmanager', 'mode') == 'FTP' ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : IOWrapper::set($this->_name));
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
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }


    /**
     * delFile
     *
     * @param string $f
     * @return string
     */
    public function delFile ($f = '')
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
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
            $ftp_name = Config::getTemp() . '/GmanagerFtpRarFile' . GMANAGER_REQUEST_TIME . '.tmp';
        }

        $rar = $this->_open();

        foreach ($ext as $var) {
            $entry = rar_entry_get($rar, $var);
            if (!$entry->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                }
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            } else if (!Gmanager::getInstance()->file_exists((Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $name) . '/' . $var)) {
                // fix bug in rar extension
                // method extract alredy returned "true"
                $err .= str_replace('%file%', htmlspecialchars($var, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
            }
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
            $ftp_name = Config::getTemp() . '/GmanagerFtpRar' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }

        $rar = $this->_open();
        $err = '';
        foreach (rar_list($rar) as $f) {
            $n = $f->getName();

            if (!$overwrite && Gmanager::getInstance()->file_exists($name . '/' . IOWrapper::get($n))) {
                $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($n, ENT_NOQUOTES) . ')<br/>';
            } else {
                $entry = rar_entry_get($rar, $n);
                if (!$entry->extract(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName)) {
                    if (Config::get('Gmanager', 'mode') == 'FTP') {
                        Gmanager::getInstance()->ftpArchiveEnd();
                        rmdir($ftp_name);
                    }
                    $err .= str_replace('%file%', htmlspecialchars($n, ENT_NOQUOTES), Language::get('extract_file_false_ext')) . '<br/>';
                }
            }

            if (Gmanager::getInstance()->is_dir($name . '/' . IOWrapper::get($n))) {
                Gmanager::getInstance()->rechmod($name . '/' . IOWrapper::get($n), $chmod[1]);
            } else {
                Gmanager::getInstance()->rechmod($name . '/' . IOWrapper::get($n), $chmod[0]);
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
        $rar = $this->_open();
        $entry = rar_entry_get($rar, $f);

        // создаем временный файл
        $tmp = Config::getTemp() . '/GmanagerRAR' . GMANAGER_REQUEST_TIME . '.tmp';
        $entry->extract(true, $tmp); // запишет сюда данные

        $ext = file_get_contents($tmp);
        unlink($tmp);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (!$ext) {
            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            if ($str) {
                return $ext;
            } else {
                return Helper_View::message(Language::get('archive_size') . ': ' . Helper_View::formatSize($entry->getPackedSize()) . '<br/>' . Language::get('real_size') . ': ' . Helper_View::formatSize($entry->getUnpackedSize()) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), strtotime($entry->getFileTime())), Helper_View::MESSAGE_SUCCESS) . Gmanager::getInstance()->code(trim($ext));
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
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
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
        $rar = $this->_open();
        $list = rar_list($rar);

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

                $r_name = Helper_View::getRawurl($list[$i]->getName());

                if (!$list[$i]->getCrc()) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]->getName(), ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars(Helper_System::getType($list[$i]->getName()), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(Helper_View::strLink($list[$i]->getName(), true), ENT_NOQUOTES) . '</a>';
                    $size = Helper_View::formatSize($list[$i]->getUnpackedSize());
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
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }
}

?>
