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


class Archive_Zip implements Archive_Interface
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var PclZip
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
     * @return PclZip
     */
    private function _open()
    {
        if ($this->_archive === null) {
            $this->_archive = new PclZip(Config::get('Gmanager', 'mode') == 'FTP' ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : IOWrapper::set($this->_name));
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
        if (!$overwrite && Gmanager::getInstance()->file_exists($this->_name)) {
            return Helper_View::message(Language::get('overwrite_false') . ' (' . htmlspecialchars($this->_name, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_ERROR);
        }

        Gmanager::getInstance()->createDir(mb_substr($this->_name, 0, mb_strrpos($this->_name, '/')));

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $temp = Config::getTemp() . '/GmanagerFtpZip' . GMANAGER_REQUEST_TIME;
            $ftp = array();
            mkdir($temp, 0755, true);
            foreach ($ext as $f) {
                $ftp[] = $tmp = $temp . '/' . IOWrapper::get(Helper_System::basename($f));
                if (Gmanager::getInstance()->is_dir($f)) {
                    mkdir($tmp, 0755, true);
                    Gmanager::getInstance()->ftpCopyFiles($f, $tmp);
                } else {
                    file_put_contents($tmp, Gmanager::getInstance()->file_get_contents($f));
                }
            }
            $ext = $ftp;
            unset($ftp);
        } else {
            $temp = Registry::get('current');
            $ext = array_map(array('IOWrapper', 'set'), $ext);
        }

        //TODO:empty directories
        $zip = $this->_open();
        if ($comment != '') {
            $result = ($zip->create($ext, PCLZIP_OPT_REMOVE_PATH, IOWrapper::set($temp), PCLZIP_OPT_COMMENT, $comment) != 0);
        } else {
            $result = ($zip->create($ext, PCLZIP_OPT_REMOVE_PATH, IOWrapper::set($temp)) != 0);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            if (!Gmanager::getInstance()->ftpArchiveEnd($this->_name)) {
                $result = false;
                $zip->error_string = Errors::get();
            }
            Helper_System::clean($temp);
        }

        if ($result) {
            if ($chmod) {
                Gmanager::getInstance()->rechmod($this->_name, $chmod);
            }
            return Helper_View::message(Language::get('create_archive_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('create_archive_false') . '<br/>' . htmlspecialchars($zip->errorInfo(true), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
        }
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
        $tmpFolder = Config::getTemp() . '/GmanagerFtpZip' . GMANAGER_REQUEST_TIME;
        mkdir($tmpFolder, 0777);

        $tmp = array();
        foreach ($ext as $v) {
            $b = IOWrapper::set(Helper_System::basename($v));
            $tmp[] = $tmpFolder . '/' . $b;
            if (Gmanager::getInstance()->is_dir($v)) {
                mkdir($tmpFolder . '/' . $b, 0777, true);
            } else {
                file_put_contents($tmpFolder . '/' . $b, Gmanager::getInstance()->file_get_contents($v));
            }
        }


        $zip = $this->_open();
        $add = $zip->add($tmp, PCLZIP_OPT_ADD_PATH, IOWrapper::set($dir), PCLZIP_OPT_REMOVE_PATH, $tmpFolder);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            if (!Gmanager::getInstance()->ftpArchiveEnd($this->_name)) {
                $add = false;
                $zip->error_string = Errors::get();
            }
        }
        Helper_System::clean($tmpFolder);

        if ($add) {
            return Helper_View::message(Language::get('add_archive_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('add_archive_false') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $zip = $this->_open();
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


        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($list != 0) {
            return Helper_View::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('del_file_false') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $err = '';
        if ($overwrite) {
            $fl = $ext;
        } else {
            $fl = array();
            foreach ($ext as $f) {
                if (Gmanager::getInstance()->file_exists(str_replace('//', '/', $name . '/' . $f))) {
                    $err .= Language::get('overwrite_false') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')<br/>';
                } else {
                    $fl[] = $f;
                }
            }
        }
        unset($ext);

        if (!$fl) {
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR) . ($err ? Helper_View::message(rtrim($err, '<br/>'), Helper_View::MESSAGE_ERROR) : '');
        }

        $sysName = IOWrapper::set($name);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($this->_name) . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpZipFile' . GMANAGER_REQUEST_TIME . '.tmp';
        }

        $zip = $this->_open();
        $res = $zip->extract(PCLZIP_OPT_PATH, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName, PCLZIP_OPT_BY_NAME, $fl, PCLZIP_OPT_REPLACE_NEWER);

        foreach ($res as $status) {
            if ($status['status'] != 'ok') {
                $err .= str_replace('%file%', htmlspecialchars($status['stored_filename'], ENT_NOQUOTES), Language::get('extract_file_false_ext')) . ' (' . $status['status'] . ')<br/>';
            }
        }

        if (!$res) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return Helper_View::message(Language::get('extract_file_false') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL);
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
        Registry::set('extractArchiveDirectoryChmod', $chmod[1]);
        Registry::set('extractArchiveFileChmod', $chmod[0]);

        function pclzip_cb_post_extract ($p_event, &$p_header)
        {
            if (Gmanager::getInstance()->is_dir(IOWrapper::get($p_header['filename']))) {
                Gmanager::getInstance()->rechmod(IOWrapper::get($p_header['filename']), Registry::get('extractArchiveDirectoryChmod'));
            } elseif (Config::get('Gmanager', 'mode') != 'FTP') {
                Gmanager::getInstance()->rechmod(IOWrapper::get($p_header['filename']), Registry::get('extractArchiveFileChmod'));
            }
            return 1;
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname(IOWrapper::set($this->_name) . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpZip' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }


        $zip = $this->_open();


        if ($overwrite) {
            $res = $zip->extract(PCLZIP_OPT_PATH, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName, PCLZIP_CB_POST_EXTRACT, 'pclzip_cb_post_extract', PCLZIP_OPT_REPLACE_NEWER);
        } else {
            $res = $zip->extract(PCLZIP_OPT_PATH, Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName, PCLZIP_CB_POST_EXTRACT, 'pclzip_cb_post_extract');
        }


        if (!$res) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
                rmdir($ftp_name);
            }
            return Helper_View::message(Language::get('extract_false') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        $err = '';
        foreach ($res as $status) {
            if ($status['status'] != 'ok') {
                $err .= str_replace('%file%', htmlspecialchars($status['stored_filename'], ENT_NOQUOTES), Language::get('extract_file_false_ext')) . ' (' . $status['status'] . ')<br/>';
            }
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->createDir($sysName, Registry::get('extractArchiveDirectoryChmod'));
            Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, Registry::get('extractArchiveFileChmod'), Registry::get('extractArchiveDirectoryChmod'), $overwrite);
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (Config::get('Gmanager', 'mode') == 'FTP' || Gmanager::getInstance()->is_dir($sysName)) {
            if ($chmod) {
                Gmanager::getInstance()->rechmod($sysName, $chmod[1]);
            }
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
        $r_current = Helper_View::getRawurl($this->_name);
        $r_f = Helper_View::getRawurl($f);

        $zip = $this->_open();
        $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (!$ext) {
            return Helper_View::message(Language::get('archive_error'), Helper_View::MESSAGE_ERROR_EMAIL);
        } elseif ($ext[0]['status'] == 'unsupported_encryption') {
            return Helper_View::message(Language::get('archive_error_encrypt'), Helper_View::MESSAGE_ERROR_EMAIL);
        } else {
            if ($str) {
                return $ext[0]['content'];
            } else {
                return Helper_View::message(Language::get('archive_size') . ': ' . Helper_View::formatSize($ext[0]['compressed_size']) . '<br/>' . Language::get('real_size') . ': ' . Helper_View::formatSize($ext[0]['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), $ext[0]['mtime']) . '<br/>&#187;<a href="edit.php?c=' . $r_current . '&amp;f=' . $r_f . '">' . Language::get('edit') . '</a>', Helper_View::MESSAGE_SUCCESS) . Gmanager::getInstance()->code(trim($ext[0]['content']));
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
        $zip = $this->_open();
        $ext = $zip->extract(PCLZIP_OPT_BY_NAME, $f, PCLZIP_OPT_EXTRACT_AS_STRING);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if (!$ext) {
            return array('text' => Language::get('archive_error'), 'size' => 0, 'lines' => 0);
        } else {
            return array('text' => trim($ext[0]['content']), 'size' => Helper_View::formatSize($ext[0]['size']), 'lines' => sizeof(explode("\n", $ext[0]['content'])));
        }
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
        Registry::set('setEditFile', $f);

        $tmp = Config::getTemp() . '/GmanagerArchivers' . GMANAGER_REQUEST_TIME . '.tmp';

        $fp = fopen($tmp, 'w');

        if (!$fp) {
            return Helper_View::message(Language::get('fputs_file_false') . '<br/>' . Errors::get(), Helper_View::MESSAGE_ERROR_EMAIL);
        }

        fputs($fp, $text);
        fclose($fp);

        $zip = $this->_open();
        $comment = $zip->properties();
        $comment = $comment['comment'];

        if ($zip->delete(PCLZIP_OPT_BY_NAME, $f) == 0) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            unlink($tmp);
            return Helper_View::message(Language::get('fputs_file_false') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL);
        }


        function pclzip_pre_add ($p_event, &$p_header)
        {
            $p_header['stored_filename'] = Registry::get('setEditFile');
            return 1;
        }

        $fl = $zip->add($tmp, PCLZIP_CB_PRE_ADD, 'pclzip_pre_add', PCLZIP_OPT_COMMENT, $comment);

        unlink($tmp);
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd($this->_name);
        }

        if ($fl) {
            return Helper_View::message(Language::get('fputs_file_true'), Helper_View::MESSAGE_SUCCESS);
        } else {
            return Helper_View::message(Language::get('fputs_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
        }
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
        $zip = $this->_open();
        $list = $zip->listContent();

        if (!$list) {
            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->ftpArchiveEnd();
            }
            return '<tr class="border"><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Helper_View::message(Language::get('archive_error') . '<br/>' . $zip->errorInfo(true), Helper_View::MESSAGE_ERROR_EMAIL) . '</td></tr>';
        } else {
            $r_current = Helper_View::getRawurl($this->_name);
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = sizeof($list);
            for ($i = 0; $i < $s; ++$i) {
                $r_name = Helper_View::getRawurl($list[$i]['filename']);

                if ($list[$i]['folder']) {
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
                    $l .= '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del_zip_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('dl') . '</a></td>';
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

            $prop = $zip->properties();
            if (isset($prop['comment']) && $prop['comment'] != '') {

                if (mb_convert_encoding($prop['comment'], 'UTF-8', 'UTF-8') != $prop['comment']) {
                    $prop['comment'] = mb_convert_encoding($prop['comment'], 'UTF-8', Config::get('Gmanager', 'altEncoding'));
                }
                $l .= '<tr class="border"><td>' . Language::get('comment_archive') . '</td><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '"><pre>' . htmlspecialchars($prop['comment'], ENT_NOQUOTES) . '</pre></td></tr>';
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
        $tmp        = Config::getTemp() . '/GmanagerZip' . GMANAGER_REQUEST_TIME;
        $zip        = $this->_open();
        $folder     = '';
        $sysName    = IOWrapper::set($name);

        foreach ($zip->extract(PCLZIP_OPT_PATH, $tmp) as $f) {
            if ($f['status'] != 'ok') {
                Helper_System::clean($tmp);
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                    Gmanager::getInstance()->ftpArchiveEnd();
                }
                return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR);
                break;
            }
            if ($arch_name == $f['stored_filename']) {
                $folder = $f['folder'];
            }
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
        } elseif (!is_dir($tmp . '/' . dirname($sysName))) {
            @mkdir($tmp . '/' . dirname($sysName), 0755, true);
        }


        if ($folder) {
            // переделать на ftp
            if ($del) {
                $result = Gmanager::getInstance()->moveFiles($tmp . '/' . $arch_name, $tmp . '/' . $name);
            } else {
                $result = Gmanager::getInstance()->copyFiles($tmp . '/' . $arch_name, $tmp . '/' . $name);
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

        $result = ($zip->create($tmp, PCLZIP_OPT_REMOVE_PATH, mb_substr($tmp, mb_strlen(dirname(dirname($tmp))))) != 0);

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
