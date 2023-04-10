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
interface Archive_Interface
{
    public function __construct($name);

    public function renameFile($new_name, $arch_name, $del = false, $overwrite = false);

    public function listArchive($down = '');

    public function getEditFile($f = '');

    public function setEditFile($f = '', $text = '');

    public function lookFile($f = '', $str = null);

    public function extractArchive($name = '', $chmod = [], $overwrite = false);

    public function extractFile($name = '', $chmod = '', $ext = [], $overwrite = false);

    public function delFile($f = '');

    public function addFile($ext = [], $dir = '');

    public function createArchive($chmod = 0644, $ext = [], $comment = '', $overwrite = false);
}
