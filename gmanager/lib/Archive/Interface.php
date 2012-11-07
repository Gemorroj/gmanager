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


interface Archive_Interface
{
    public function __construct ($name);
    public function renameFile ($name, $arch_name, $del = false, $overwrite = false);
    public function listArchive ($down = '');
    public function getEditFile ($f = '');
    public function setEditFile ($f = '', $text = '');
    public function lookFile ($f = '', $str = null);
    public function extractArchive ($name = '', $chmod = array(), $overwrite = false);
    public function extractFile ($name = '', $chmod = '', $ext = array(), $overwrite = false);
    public function delFile ($f = '');
    public function addFile ($ext = array(), $dir = '');
    public function createArchive ($chmod = 0644, $ext = array(), $comment = '', $overwrite = false);
}
