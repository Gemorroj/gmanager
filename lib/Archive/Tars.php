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
     * Constructor.
     *
     * @param string $name Archive filename
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Open Archive.
     *
     * @return Archive_Tar
     */
    private function _open()
    {
        if (null === $this->_archive) {
            $this->_archive = new Archive_Tar('FTP' == Config::get('mode') ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : $this->_name);
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
        if ('FTP' == Config::get('mode')) {
            $ftp_name = Config::getTemp().'/GmanagerFtpTar'.GMANAGER_REQUEST_TIME.'/';
            \mkdir($ftp_name, 0777);

            $tmp = [];
            foreach ($ext as $v) {
                $b = Helper_System::basename($v);
                $tmp[] = $ftp_name.$b;
                \file_put_contents($ftp_name.$b, Gmanager::getInstance()->file_get_contents($v));
            }
            $ext = $tmp;
            unset($tmp);
        }

        $tgz = $this->_open();

        $add = true;
        foreach ($ext as $v) {
            $add = $tgz->addModify($v, $dir, \dirname($v));
        }

        if ('FTP' == Config::get('mode')) {
            if (!Gmanager::getInstance()->ftpArchiveEnd($this->_name)) {
                $add = false;
            }
            Helper_System::clean($ftp_name);
        }

        if ($add) {
            return Helper_View::message(Language::get('add_archive_true'), Helper_View::MESSAGE_SUCCESS);
        }

        return Helper_View::message(Language::get('add_archive_false'), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $tgz = $this->_open();

        $list = $tgz->listContent();

        $new_tar = [];
        $s = \count($list);
        for ($i = 0; $i < $s; ++$i) {
            if ($list[$i]['filename'] == $f) {
                continue;
            }
            $new_tar[] = $list[$i]['filename'];
        }

        $tmp_name = Config::getTemp().'/GmanagerTar'.GMANAGER_REQUEST_TIME.'/';
        $tgz->extractList($new_tar, $tmp_name);

        Gmanager::getInstance()->unlink($this->_name);
        $list = $tgz->createModify($tmp_name, '.', $tmp_name);
        Helper_System::clean($tmp_name);

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($list) {
            return Helper_View::message(Language::get('del_file_true').' ('.\htmlspecialchars($f, \ENT_NOQUOTES).')', Helper_View::MESSAGE_SUCCESS);
        }

        return Helper_View::message(Language::get('del_file_false').' ('.\htmlspecialchars($f, \ENT_NOQUOTES).')', Helper_View::MESSAGE_ERROR_EMAIL);
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
            $ftp_name = Config::getTemp().'/GmanagerFtpTarFile'.GMANAGER_REQUEST_TIME.'.tmp';
        }

        $tgz = $this->_open();

        if (!$tgz->extractList($ext, 'FTP' == Config::get('mode') ? $ftp_name : $sysName)) {
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
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
            $ftp_name = Config::getTemp().'/GmanagerFtpTar'.GMANAGER_REQUEST_TIME;
            \mkdir($ftp_name, 0777);
        }

        $tgz = $this->_open();
        $extract = $tgz->listContent();
        $err = '';

        if ($overwrite) {
            $res = $tgz->extract('FTP' == Config::get('mode') ? $ftp_name : $sysName);
        } else {
            $list = [];
            foreach ($extract as $f) {
                if (Gmanager::getInstance()->file_exists($name.'/'.$f['filename'])) {
                    $err .= Language::get('overwrite_false').' ('.\htmlspecialchars($f['filename'], \ENT_NOQUOTES).')<br/>';
                } else {
                    $list[] = $f['filename'];
                }
            }
            if (!$list) {
                Gmanager::getInstance()->ftpArchiveEnd();

                return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR).($err ? Helper_View::message(\rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
            }

            $res = $tgz->extractList($list, 'FTP' == Config::get('mode') ? $ftp_name : $sysName);
        }

        if (!$res) {
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
                \rmdir($ftp_name);
            }

            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        foreach ($extract as $f) {
            if (Gmanager::getInstance()->is_dir($name.'/'.$f['filename'])) {
                Gmanager::getInstance()->rechmod($name.'/'.$f['filename'], $chmod[1]);
            } else {
                Gmanager::getInstance()->rechmod($name.'/'.$f['filename'], $chmod[0]);
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
        $tgz = $this->_open();
        $ext = $tgz->extractInString($f);

        if (!$ext) {
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
        }
        $list = $tgz->listContent();

        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        $s = \count($list);
        for ($i = 0; $i < $s; ++$i) {
            if ($list[$i]['filename'] != $f) {
                continue;
            }
            if ($str) {
                return $ext;
            }

            return Helper_View::message(Language::get('real_size').': '.Helper_View::formatSize($list[$i]['size']).'<br/>'.Language::get('archive_date').': '.\date(Config::get('dateFormat'), $list[$i]['mtime']), Helper_View::MESSAGE_SUCCESS).Gmanager::getInstance()->code(\trim($ext));
        }
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
        return Helper_View::message(Language::get('not_supported'), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $tgz = $this->_open();
        $list = $tgz->listContent();

        if (!$list) {
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return '<tr class="border"><td colspan="'.(\array_sum(Config::getSection('Display')) + 1).'">'.Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL).'</td></tr>';
        }
        $r_current = Helper_View::getRawurl($this->_name);
        $l = '';

        if ($down) {
            $list = \array_reverse($list);
        }

        $s = \count($list);
        for ($i = 0; $i < $s; ++$i) {
            $r_name = Helper_View::getRawurl($list[$i]['filename']);

            if ($list[$i]['typeflag']) {
                $type = 'DIR';
                $name = \htmlspecialchars($list[$i]['filename'], \ENT_NOQUOTES);
                $size = ' ';
                $down = ' ';
            } else {
                $type = \htmlspecialchars(Helper_System::getType($list[$i]['filename']), \ENT_NOQUOTES);
                $name = '<a href="?c='.$r_current.'&amp;f='.$r_name.'">'.\htmlspecialchars(Helper_View::strLink($list[$i]['filename'], true), \ENT_NOQUOTES).'</a>';
                $size = Helper_View::formatSize($list[$i]['size']);
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
                $l .= '<td><a href="?gmanager_action=change&amp;c='.$r_current.'&amp;f='.$r_name.'">'.Language::get('ch').'</a></td>';
            }
            if (Config::get('del', 'Display')) {
                $l .= '<td><a onclick="return Gmanager.delNotify();" href="?gmanager_action=change&amp;go=del_tar_archive&amp;c='.$r_current.'&amp;f='.$r_name.'">'.Language::get('dl').'</a></td>';
            }
            if (Config::get('chmod', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('date', 'Display')) {
                $l .= '<td>'.\date(Config::get('dateFormat'), $list[$i]['mtime']).'</td>';
            }
            if (Config::get('uid', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('gid', 'Display')) {
                $l .= '<td> </td>';
            }
            if (Config::get('n', 'Display')) {
                $l .= '<td>'.($i + 1).'</td>';
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
        $tmp = Config::getTemp().'/GmanagerTar'.GMANAGER_REQUEST_TIME;
        $tgz = $this->_open();
        $sysName = $new_name;

        $folder = '';
        foreach ($tgz->listContent() as $f) {
            if ($arch_name == $f['filename']) {
                $folder = 5 == $f['typeflag'] ? 1 : 0;
                break;
            }
        }

        if (!$tgz->extract($tmp)) {
            Helper_System::clean($tmp);
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }

            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR);
        }

        if (\file_exists($tmp.'/'.$sysName)) {
            if ($overwrite) {
                if ($folder) {
                    Helper_System::clean($tmp.'/'.$sysName);
                } else {
                    \unlink($tmp.'/'.$sysName);
                }
            } else {
                Helper_System::clean($tmp);
                if ('FTP' == Config::get('mode')) {
                    Gmanager::getInstance()->ftpArchiveEnd();
                }

                return Helper_View::message(Language::get('overwrite_false'), Helper_View::MESSAGE_ERROR);
            }
        }

        if ($folder) {
            @\mkdir($tmp.'/'.$sysName, 0755, true);
        } else {
            @\mkdir($tmp.'/'.\dirname($sysName), 0755, true);
        }

        if ($folder) {
            if ($del) {
                $result = Gmanager::getInstance()->moveFiles($tmp.'/'.$new_name, $tmp.'/'.$arch_name);
            } else {
                $result = Gmanager::getInstance()->copyFiles($tmp.'/'.$new_name, $tmp.'/'.$arch_name);
            }
        } else {
            if ($del) {
                $result = \rename($tmp.'/'.$arch_name, $tmp.'/'.$sysName);
            } else {
                $result = \copy($tmp.'/'.$arch_name, $tmp.'/'.$sysName);
            }
        }

        if (!$result) {
            Helper_System::clean($tmp);
            if ('FTP' == Config::get('mode')) {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            if ($folder) {
                if ($del) {
                    return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_files_false')), Helper_View::MESSAGE_ERROR);
                }

                return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_files_false')), Helper_View::MESSAGE_ERROR);
            }
            if ($del) {
                return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_file_false')), Helper_View::MESSAGE_ERROR);
            }

            return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_file_false')), Helper_View::MESSAGE_ERROR);
        }

        $result = $tgz->createModify($tmp, '.', $tmp);

        Helper_System::clean($tmp);
        if ('FTP' == Config::get('mode')) {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($result) {
            if ($folder) {
                if ($del) {
                    return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_files_true')), Helper_View::MESSAGE_SUCCESS);
                }

                return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_files_true')), Helper_View::MESSAGE_SUCCESS);
            }
            if ($del) {
                return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_file_true')), Helper_View::MESSAGE_SUCCESS);
            }

            return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_file_true')), Helper_View::MESSAGE_SUCCESS);
        }
        if ($folder) {
            if ($del) {
                return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_files_false')), Helper_View::MESSAGE_ERROR);
            }

            return Helper_View::message(\str_replace('%title%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_files_false')), Helper_View::MESSAGE_ERROR);
        }
        if ($del) {
            return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('move_file_false')), Helper_View::MESSAGE_ERROR);
        }

        return Helper_View::message(\str_replace('%file%', \htmlspecialchars($arch_name, \ENT_NOQUOTES), Language::get('copy_file_false')), Helper_View::MESSAGE_ERROR);
    }
}
