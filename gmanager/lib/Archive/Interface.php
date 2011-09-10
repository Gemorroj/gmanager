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


interface Archive_Interface
{
    public function renameFile ($current, $name, $arch_name, $del = false, $overwrite = false);
    public function listArchive ($current, $down = '');
    public function getEditFile ($current, $f = '');
    public function setEditFile ($current, $f = '', $text = '');
    public function lookFile ($current, $f = '', $str = null);
    public function extractArchive ($current, $name = '', $chmod = array(), $overwrite = false);
    public function extractFile ($current, $name = '', $chmod = '', $ext = '', $overwrite = false);
    public function delFile ($current, $f = '');
    public function addFile ($current, $ext = array(), $dir = '');
    public function createArchive ($name, $chmod = 0644, $ext = array(), $comment = '', $overwrite = false);
}

?>
