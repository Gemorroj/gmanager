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
class Archive_Rar implements Archive_Interface
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var RarArchive
     */
    private $_archive;

    /**
     * Constructor.
     *
     * @param string $name Archive filename
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (null !== $this->_archive) {
            $this->_archive->close();
        }
    }

    /**
     * Open Archive.
     *
     * @return RarArchive
     */
    private function _open()
    {
        if (null === $this->_archive) {
            $this->_archive = RarArchive::open('FTP' == Config::get('mode') ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : $this->_name);
        }

        return $this->_archive;
    }

    /**
     * createArchive.
     *
     * @param mixed  $chmod
     * @param array  $ext
     * @param string $comment
     * @param bool   $overwrite
     *
     * @return string
     */
    public function createArchive($chmod = 0644, $ext = [], $comment = '', $overwrite = false)
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }

    /**
     * addFile.
     *
     * @param mixed  $ext
     * @param string $dir
     *
     * @return string
     */
    public function addFile($ext = [], $dir = '')
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }

    /**
     * delFile.
     *
     * @param string $f
     *
     * @return string
     */
    public function delFile($f = '')
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }

    /**
     * extractFile.
     *
     * @param string $name
     * @param mixed  $chmod
     * @param array  $ext
     * @param bool   $overwrite
     *
     * @return string
     */
    public function extractFile($name = '', $chmod = '', $ext = [], $overwrite = false)
    {
        $tmp = [];
        $err = '';
        foreach ($ext as $f) {
            if (Gmanager::getInstance()->file_exists(\str_replace('//', '/', $name.'/'.$f))) {
                if ($overwrite) {
                    \unlink($name.'/'.$f);
                    $tmp[] = $f;
                } else {
                    $err .= Language::get('overwrite_false').' ('.\htmlspecialchars($f, \ENT_NOQUOTES).')<br/>';
                }
            } else {
                $tmp[] = $f;
            }
        }
        $ext = &$tmp;

        if (!$ext) {
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR).($err ? Helper_View::message(\rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        }

        $sysName = $name;

        if ('FTP' == Config::get('mode')) {
            $sysName = ('/' == $sysName[0] ? $sysName : \dirname($this->_name.'/').'/'.$sysName);
            $ftp_name = Config::getTemp().'/GmanagerFtpRarFile'.GMANAGER_REQUEST_TIME.'.tmp';
        }

        $rar = $this->_open();

        foreach ($ext as $var) {
            $entry = $rar->getEntry($var);
            if (!$entry->extract('FTP' == Config::get('mode') ? $ftp_name : $sysName)) {
                $err .= \str_replace('%file%', \htmlspecialchars($var, \ENT_NOQUOTES), Language::get('extract_file_false_ext')).'<br/>';
            } elseif (!Gmanager::getInstance()->file_exists(('FTP' == Config::get('mode') ? $ftp_name : $name).'/'.$var)) {
                // fix bug in rar extension
                // method extract already returned "true"
                $err .= \str_replace('%file%', \htmlspecialchars($var, \ENT_NOQUOTES), Language::get('extract_file_false_ext')).'<br/>';
            }
        }

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->createDir($sysName);
            Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if ('FTP' == Config::get('mode') || Gmanager::getInstance()->is_dir($name)) {
            if ($chmod) {
                Gmanager::getInstance()->rechmod($name, $chmod);
            }

            return Helper_View::message(Language::get('extract_file_true'), Helper_View::MESSAGE_SUCCESS).($err ? Helper_View::message(\rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        }

        return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
    }

    /**
     * extractArchive.
     *
     * @param string $name
     * @param array  $chmod
     * @param bool   $overwrite
     *
     * @return string
     */
    public function extractArchive($name = '', $chmod = [], $overwrite = false)
    {
        $sysName = $name;

        if ('FTP' == Config::get('mode')) {
            $sysName = ('/' == $sysName[0] ? $sysName : \dirname($this->_name.'/').'/'.$sysName);
            $ftp_name = Config::getTemp().'/GmanagerFtpRar'.GMANAGER_REQUEST_TIME;
            \mkdir($ftp_name, 0777);
        }

        $rar = $this->_open();
        $err = '';
        foreach ($rar->getEntries() as $entry) {
            $n = $entry->getName();

            if (!$overwrite && Gmanager::getInstance()->file_exists($name.'/'.$n)) {
                $err .= Language::get('overwrite_false').' ('.\htmlspecialchars($n, \ENT_NOQUOTES).')<br/>';
            } else {
                if (!$entry->extract('FTP' == Config::get('mode') ? $ftp_name : $sysName)) {
                    if ('FTP' == Config::get('mode')) {
                        Gmanager::getInstance()->ftpArchiveEnd();
                        \rmdir($ftp_name);
                    }
                    $err .= \str_replace('%file%', \htmlspecialchars($n, \ENT_NOQUOTES), Language::get('extract_file_false_ext')).'<br/>';
                }
            }

            if (Gmanager::getInstance()->is_dir($name.'/'.$n)) {
                Gmanager::getInstance()->rechmod($name.'/'.$n, $chmod[1]);
            } else {
                Gmanager::getInstance()->rechmod($name.'/'.$n, $chmod[0]);
            }
        }

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->createDir($sysName, $chmod[1]);
            Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, $chmod[0], $chmod[1], $overwrite);
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if ('FTP' == Config::get('mode') || Gmanager::getInstance()->is_dir($name)) {
            Gmanager::getInstance()->rechmod($name, $chmod[1]);

            return Helper_View::message(Language::get('extract_true'), Helper_View::MESSAGE_SUCCESS).($err ? Helper_View::message(\rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        }

        return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR_EMAIL);
    }

    /**
     * lookFile.
     *
     * @param string $f
     * @param string $str
     *
     * @return string
     */
    public function lookFile($f = '', $str = null)
    {
        $rar = $this->_open();
        $entry = $rar->getEntry($f);
        $stream = $entry->getStream();
        $ext = \stream_get_contents($stream);

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (!$ext) {
            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
        }
        if ($str) {
            return $ext;
        }

        return Helper_View::message(Language::get('archive_size').': '.Helper_View::formatSize($entry->getPackedSize()).'<br/>'.Language::get('real_size').': '.Helper_View::formatSize($entry->getUnpackedSize()).'<br/>'.Language::get('archive_date').': '.\date(Config::get('dateFormat'), \strtotime($entry->getFileTime())), Helper_View::MESSAGE_SUCCESS).Gmanager::getInstance()->code(\trim($ext));
    }

    /**
     * getEditFile.
     *
     * @param string $f
     *
     * @return array
     */
    public function getEditFile($f = '')
    {
        return ['text' => Language::get('not_supported'), 'size' => 0, 'lines' => 0];
    }

    /**
     * setEditFile.
     *
     * @param string $f
     * @param string $text
     *
     * @return string
     */
    public function setEditFile($f = '', $text = '')
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }

    /**
     * listArchive.
     *
     * @todo refactoring to ListData
     *
     * @param string $down
     *
     * @return string
     */
    public function listArchive($down = '')
    {
        $rar = $this->_open();
        $list = $rar->getEntries();

        if (!$list) {
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return '<tr class="border"><td colspan="'.(\array_sum(Config::getSection('Display')) + 1).'">'.Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL).'</td></tr>';
        }
        $r_current = Helper_View::getRawurl($this->_name);
        $l = '';
        $i = 0;

        if ($down) {
            $list = \array_reverse($list);
        }

        foreach ($list as $entry) {
            $r_name = Helper_View::getRawurl($entry->getName());

            if ($entry->isDirectory()) {
                $type = 'DIR';
                $name = \htmlspecialchars($entry->getName(), \ENT_NOQUOTES);
                $size = ' ';
                $down = ' ';
            } else {
                $type = \htmlspecialchars(Helper_System::getType($entry->getName()), \ENT_NOQUOTES);
                $name = '<a href="?c='.$r_current.'&amp;f='.$r_name.'">'.\htmlspecialchars(Helper_View::strLink($entry->getName(), true), \ENT_NOQUOTES).'</a>';
                $size = Helper_View::formatSize($entry->getUnpackedSize());
                $down = '<a href="?gmanager_action=change&amp;get='.$r_current.'&amp;f='.$r_name.'">'.Language::get('get').'</a>';
            }

            $l .= '<tr class="border"><td class="check"><input name="check[]" type="checkbox" value="'.$r_name.'"/></td>';
            if (Config::get('name', 'Display')) {
                $l .= '<td>'.$name.'</td>';
            }
            if (Config::get('down', 'Display')) {
                $l .= '<td>'.$down.'</td>';
            }
            if (Config::get('type', 'Display')) {
                $l .= '<td>'.$type.'</td>';
            }
            if (Config::get('size', 'Display')) {
                $l .= '<td>'.$size.'</td>';
            }
            if (Config::get('change', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('del', 'Display')) {
                $l .= '<td>'.Language::get('dl').'</td>';
            }
            if (Config::get('chmod', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('date', 'Display')) {
                $l .= '<td>'.\date(Config::get('dateFormat'), \strtotime($entry->getFileTime())).'</td>';
            }
            if (Config::get('uid', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('gid', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('n', 'Display')) {
                $l .= '<td>'.(++$i).'</td>';
            }

            $l .= '</tr>';
        }

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        return $l;
    }

    /**
     * renameFile.
     *
     * @param string $new_name
     * @param string $arch_name
     * @param bool   $del
     * @param bool   $overwrite
     *
     * @return string
     */
    public function renameFile($new_name, $arch_name, $del = false, $overwrite = false)
    {
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR);
    }
}
