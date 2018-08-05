<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class Archive_Zip implements Archive_Interface
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var ZipArchive
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
        $this->_archive = new ZipArchive();
    }


    /**
     * Open archive
     *
     * @param int $mode
     * @return ZipArchive
     * @throws Exception
     */
    private function _open($mode = null)
    {
        $result = $this->_archive->open(Config::get('Gmanager', 'mode') == 'FTP' ? Gmanager::getInstance()->ftpArchiveStart($this->_name) : $this->_name, $mode);
        if (true !== $result) {
            throw new Exception('Error. Code: ' . $result);
        }

        return $this->_archive;
    }


    /**
     * Close archive
     */
    private function _close()
    {
        if (Config::get('Gmanager', 'mode') == 'FTP') {
            Gmanager::getInstance()->ftpArchiveEnd();
        }

        if ($this->_archive) {
            $this->_archive->close();
        }
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

        $dirname = mb_substr($this->_name, 0, mb_strrpos($this->_name, '/'));
        if (!Gmanager::getInstance()->is_dir($dirname)) {
            Gmanager::getInstance()->createDir($dirname);
        }

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $temp = Config::getTemp() . '/GmanagerFtpZip' . GMANAGER_REQUEST_TIME;
            mkdir($temp, 0755, true);
            foreach ($ext as $f) {
                $tmp = $temp . '/' . Helper_System::basename($f);
                if (Gmanager::getInstance()->is_dir($f)) {
                    mkdir($tmp, 0755, true);
                    Gmanager::getInstance()->ftpCopyFiles($f, $tmp);
                } else {
                    file_put_contents($tmp, Gmanager::getInstance()->file_get_contents($f));
                }
            }
        }

        try {
            $zip = $this->_open(ZipArchive::CREATE);
            if (!$zip->addGlob($dirname . '/*', GLOB_BRACE, array('add_path' => basename($dirname) . '/', 'remove_all_path' => true))) {
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                    Helper_System::clean($temp);
                }
                throw new Exception($zip->getStatusString());
            }

            if ($comment != '') {
                $zip->setArchiveComment($comment);
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Helper_System::clean($temp);
            }

            if ($chmod) {
                Gmanager::getInstance()->rechmod($this->_name, $chmod);
            }

            $this->_close();
            return Helper_View::message(Language::get('create_archive_true'), Helper_View::MESSAGE_SUCCESS);
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('create_archive_false') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
            $b = Helper_System::basename($v);
            $tmp[] = $tmpFolder . '/' . $b;
            if (Gmanager::getInstance()->is_dir($v)) {
                mkdir($tmpFolder . '/' . $b, 0777, true);
            } else {
                file_put_contents($tmpFolder . '/' . $b, Gmanager::getInstance()->file_get_contents($v));
            }
        }

        try {
            $zip = $this->_open();
            if (!$zip->addGlob($tmpFolder . '/*', GLOB_BRACE, array('add_path' => $dir . '/', 'remove_all_path' => true))) {
                throw new Exception($zip->getStatusString());
            }

            Helper_System::clean($tmpFolder);

            $this->_close();
            return Helper_View::message(Language::get('add_archive_true'), Helper_View::MESSAGE_SUCCESS);
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('add_archive_false') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
        try {
            $zip = $this->_open();

            if (!$zip->deleteName($f)) {
                throw new Exception($zip->getStatusString());
            }

            $this->_close();
            return Helper_View::message(Language::get('del_file_true') . ' (' . htmlspecialchars($f, ENT_NOQUOTES) . ')', Helper_View::MESSAGE_SUCCESS);
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('del_file_false') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $err = array();
        if ($overwrite) {
            $fl = $ext;
        } else {
            $fl = array();
            foreach ($ext as $f) {
                if (Gmanager::getInstance()->file_exists(str_replace('//', '/', $name . '/' . $f))) {
                    $err[] = Language::get('overwrite_false') . ' (' . $f . ')';
                } else {
                    $fl[] = $f;
                }
            }
        }
        unset($ext);

        if (!$fl) {
            return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR) . ($err ? Helper_View::message(nl2br(implode("\n", $err)), Helper_View::MESSAGE_ERROR) : '');
        }

        $sysName = $name;

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname($this->_name . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpZipFile' . GMANAGER_REQUEST_TIME;
        }

        try {
            $zip = $this->_open();
            if (!$zip->extractTo(Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName, $fl)) {
                throw new Exception($zip->getStatusString());
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->createDir($sysName);
                Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, $overwrite);
            }

            $this->_close();
            if (Config::get('Gmanager', 'mode') == 'FTP' || Gmanager::getInstance()->is_dir($name)) {
                if ($chmod) {
                    Gmanager::getInstance()->rechmod($name, $chmod);
                }
                return Helper_View::message(Language::get('extract_file_true'), Helper_View::MESSAGE_SUCCESS) . ($err ? Helper_View::message(nl2br(implode("\n", $err)), Helper_View::MESSAGE_ERROR) : '');
            } else {
                return Helper_View::message(Language::get('extract_file_false'), Helper_View::MESSAGE_ERROR_EMAIL);
            }
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('extract_file_false') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
        $sysName = $name;
        Registry::set('extractArchiveDirectoryChmod', $chmod[1]);
        Registry::set('extractArchiveFileChmod', $chmod[0]);

        if (Config::get('Gmanager', 'mode') == 'FTP') {
            $sysName = ($sysName[0] == '/' ? $sysName : dirname($this->_name . '/') . '/' . $sysName);
            $ftp_name = Config::getTemp() . '/GmanagerFtpZip' . GMANAGER_REQUEST_TIME;
            mkdir($ftp_name, 0777);
        }


        try {
            $zip = $this->_open();

            $err = array();
            $res = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $f = $zip->getNameIndex($i);

                if (!$overwrite && Gmanager::getInstance()->file_exists(str_replace('//', '/', $name . '/' . $f))) {
                    $err[] = Language::get('overwrite_false') . ' (' . $f . ')';
                } else {
                    $dir = Config::get('Gmanager', 'mode') == 'FTP' ? $ftp_name : $sysName;
                    if ($zip->extractTo($dir, array($f))) {
                        $res = true;

                        if (Gmanager::getInstance()->is_dir($dir . '/' . $f)) {
                            Gmanager::getInstance()->rechmod($dir . '/' . $f, Registry::get('extractArchiveDirectoryChmod'));
                        } elseif (Config::get('Gmanager', 'mode') != 'FTP') {
                            Gmanager::getInstance()->rechmod($dir . '/' . $f, Registry::get('extractArchiveFileChmod'));
                        }

                    } else {
                        $err[] = str_replace('%file%', $f, Language::get('extract_file_false_ext')) . ' (' . $zip->getStatusString() . ')';
                    }
                }
            }

            if (!$res) {
                if (Config::get('Gmanager', 'mode') == 'FTP') {
                    rmdir($ftp_name);
                }
                throw new Exception(implode("\n", $err));
            }

            if (Config::get('Gmanager', 'mode') == 'FTP') {
                Gmanager::getInstance()->createDir($sysName, Registry::get('extractArchiveDirectoryChmod'));
                Gmanager::getInstance()->ftpMoveFiles($ftp_name, $sysName, Registry::get('extractArchiveFileChmod'), Registry::get('extractArchiveDirectoryChmod'), $overwrite);
            }

            $this->_close();

            if (Config::get('Gmanager', 'mode') == 'FTP' || Gmanager::getInstance()->is_dir($sysName)) {
                if ($chmod) {
                    Gmanager::getInstance()->rechmod($sysName, $chmod[1]);
                }
                return Helper_View::message(Language::get('extract_true'), Helper_View::MESSAGE_SUCCESS) . ($err ? Helper_View::message(nl2br(implode("\n", $err)), Helper_View::MESSAGE_ERROR) : '');
            } else {
                return Helper_View::message(Language::get('extract_false'), Helper_View::MESSAGE_ERROR_EMAIL);
            }
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('extract_false') . '<br/>' . nl2br(htmlspecialchars($e->getMessage(), ENT_NOQUOTES)), Helper_View::MESSAGE_ERROR_EMAIL);
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

        try {
            $zip = $this->_open();
            $stat = $zip->statName($f);

            $content = $zip->getFromName($f);
            if (false === $content) {
                throw new Exception($zip->getStatusString());
            }

            $this->_close();
            if ($str) {
                return $content;
            } else {
                return Helper_View::message(Language::get('archive_size') . ': ' . Helper_View::formatSize($stat['comp_size']) . '<br/>' . Language::get('real_size') . ': ' . Helper_View::formatSize($stat['size']) . '<br/>' . Language::get('archive_date') . ': ' . strftime(Config::get('Gmanager', 'dateFormat'), $stat['mtime']) . '<br/>&#187;<a href="?gmanager_action=edit&amp;c=' . $r_current . '&amp;f=' . $r_f . '">' . Language::get('edit') . '</a>', Helper_View::MESSAGE_SUCCESS) . Gmanager::getInstance()->code($content);
            }
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
        try {
            $zip = $this->_open();
            $stat = $zip->statName($f);
            $content = $zip->getFromName($f);
            if (false === $content) {
                throw new Exception($zip->getStatusString());
            }

            $this->_close();
           return array('text' => $content, 'size' => Helper_View::formatSize($stat['size']), 'lines' => substr_count($content, "\n"));
        } catch (Exception $e) {
            $this->_close();
            return array('text' => $e->getMessage(), 'size' => 0, 'lines' => 0);
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
        try {
            $zip = $this->_open();
            if (!$zip->addFromString($f, $text)) {
                throw new Exception($zip->getStatusString());
            }

            $this->_close();
            return Helper_View::message(Language::get('fputs_file_true'), Helper_View::MESSAGE_SUCCESS);
        } catch (Exception $e) {
            $this->_close();
            return Helper_View::message(Language::get('fputs_file_false') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL);
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
        try {
            $zip = $this->_open();

            $list = array();
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $list[] = $zip->statIndex($i);
            }

            $r_current = Helper_View::getRawurl($this->_name);
            $l = '';

            if ($down) {
                $list = array_reverse($list);
            }

            $s = count($list);
            for ($i = 0; $i < $s; ++$i) {
                $r_name = Helper_View::getRawurl($list[$i]['name']);

                if (!$list[$i]['crc']) {
                    $type = 'DIR';
                    $name = htmlspecialchars($list[$i]['name'], ENT_NOQUOTES);
                    $size = ' ';
                    $down = ' ';
                } else {
                    $type = htmlspecialchars(Helper_System::getType($list[$i]['name']), ENT_NOQUOTES);
                    $name = '<a href="?c=' . $r_current . '&amp;f=' . $r_name . '">' . htmlspecialchars(Helper_View::strLink($list[$i]['name'], true), ENT_NOQUOTES) . '</a>';
                    $size = Helper_View::formatSize($list[$i]['size']);
                    $down = '<a href="?gmanager_action=change&amp;get=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('get') . '</a>';
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
                    $l .= '<td><a href="?gmanager_action=change&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::get('Display', 'del')) {
                    $l .= '<td><a onclick="return Gmanager.delNotify();" href="?gmanager_action=change&amp;go=del_zip_archive&amp;c=' . $r_current . '&amp;f=' . $r_name . '">' . Language::get('dl') . '</a></td>';
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

            $comment = $zip->getArchiveComment();
            if ($comment != '') {
                $l .= '<tr class="border"><td>' . Language::get('comment_archive') . '</td><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '"><pre>' . htmlspecialchars($comment, ENT_NOQUOTES) . '</pre></td></tr>';
            }

            $this->_close();
            return $l;
        } catch (Exception $e) {
            $this->_close();
            return '<tr class="border"><td colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Helper_View::message(Language::get('archive_error') . '<br/>' . htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR_EMAIL) . '</td></tr>';
        }
    }


    /**
     * renameFile
     *
     * @param string $new_name Новое имя файла
     * @param string $arch_name Оригинальное имя файла в архиве
     * @param bool   $del Удалить исходный файл
     * @param bool   $overwrite Перезаписать файл, если такой уже есть
     * @return string
     */
    public function renameFile ($new_name, $arch_name, $del = false, $overwrite = false)
    {
        try {
            $zip = $this->_open();

            $newFileStat = $zip->statName($new_name);
            $archFileStat = $zip->statName($arch_name);

            // если не разрешена перезапись и конечный файл или директория уже существуют
            if ($newFileStat && !$overwrite) {
                throw new Exception(Language::get('overwrite_false'));
            }

            // если разрешена перезапись и конечный файл или директория уже существуют
            if ($newFileStat && $overwrite) {
                if (!$zip->deleteName($new_name)) {
                    throw new Exception($zip->getStatusString());
                }
            }

            // если разрешено удаление исходного файла, то просто переименовываем
            if ($del) {
                if (!$zip->renameName($arch_name, $new_name)) {
                    throw new Exception($zip->getStatusString());
                }
            } else { // не разрешено удаление исходного файла, нужно копирование
                if ($archFileStat['crc']) { // копируем файл
                    $tmp = $zip->getFromName($arch_name);
                    if (!$tmp) {
                        throw new Exception($zip->getStatusString());
                    }
                    if (!$zip->addFromString($new_name, $tmp)) {
                        throw new Exception($zip->getStatusString());
                    }
                } else { // копируем директорию

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        if (mb_strpos($stat['name'], $arch_name) !== 0) { // если имя файла или директории в архиве не начинается с переименовываемой
                            continue;
                        }

                        $copyName = str_replace('//', '/', $new_name . '/' . mb_substr($stat['name'], mb_strlen($arch_name)));
                        if (!$stat['crc']) { // это директория
                            if (!$zip->addEmptyDir($copyName)) {
                                throw new Exception($zip->getStatusString());
                            }
                        } else {
                            $tmp = $zip->getFromName($stat['name']);
                            if (!$tmp) {
                                throw new Exception($zip->getStatusString());
                            }
                            if (!$zip->addFromString($copyName, $tmp)) {
                                throw new Exception($zip->getStatusString());
                            }
                        }
                    }
                }
            }

            if (!$archFileStat['crc']) { // переименовывали или копировали директорию
                if ($del) {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_files_true')), Helper_View::MESSAGE_SUCCESS);
                } else {
                    return Helper_View::message(str_replace('%title%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_files_true')), Helper_View::MESSAGE_SUCCESS);
                }
            } else { // переименовывали или копировали файл
                if ($del) {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('move_file_true')), Helper_View::MESSAGE_SUCCESS);
                } else {
                    return Helper_View::message(str_replace('%file%', htmlspecialchars($arch_name, ENT_NOQUOTES), Language::get('copy_file_true')), Helper_View::MESSAGE_SUCCESS);
                }
            }
        } catch (Exception $e) {
            return Helper_View::message(htmlspecialchars($e->getMessage(), ENT_NOQUOTES), Helper_View::MESSAGE_ERROR);
        }
    }
}
