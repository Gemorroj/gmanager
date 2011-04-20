<?php
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.4 beta
 * 
 * PHP version >= 5.2.1
 * 
 */

//TODO: create method getHeader (creahe header for ListData)
class ListData
{
    /**
     * @var int $getListCountPages
     * @access public
     */
    public static $getListCountPages = 0;


    /**
     * getListArray
     * 
     * @param object $Gmanager
     * @param string $current
     * @param string $itype
     * @param string $down
     * @param string $addArchive
     * @return array
     */
    private static function _getListArray (Gmanager $Gmanager, $current = '', $itype = '', $down = '', $addArchive = '')
    {
        $type   = $isize = $uid = $gid = $chmod = $name = $time = '';
        $page   = $page0 = $page1 = $page2 = array();
        $i      = 0;

        $t      = (Config::$target ? ' target="_blank"' : '');
        $add    = ($addArchive ? '&amp;go=1&amp;add_archive=' . str_replace('%2F', '/', rawurlencode($addArchive)) : '');


        if ($itype == 'time') {
            $key = & $time;
        } else if ($itype == 'type') {
            $key = & $type;
        } else if ($itype == 'size') {
            $key = & $isize;
        } else if ($itype == 'chmod') {
            $key = & $chmod;
        } else if ($itype == 'uid') {
            $key = & $uid;
        } else if ($itype == 'gid') {
            $key = & $gid;
        } else {
            $key = & $name;
        }


        foreach ($Gmanager->iterator($current) as $file) {
            $i++;
            $pname = $pdown = $ptype = $psize = $pchange = $pdel = $pchmod = $pdate = $puid = $uid = $pgid = $gid = $name = $size = $isize = $chmod = '';

            /*
            if (substr($file, -1) == '/') {
                $file = iconv_substr($file, 0, iconv_strlen($file)-1);    
            }
            */

            if ($current != '.') {
                $file = $current . $file;
            }

            $basename = basename($file);
            $r_file = str_replace('%2F', '/', rawurlencode($file));
            $stat = $Gmanager->stat($file);
            $time = $stat['mtime'];
            $uid  = $stat['owner'];
            $gid  = $stat['group'];


            if ($Gmanager->is_link($file)) {
                $type = 'LINK';
                $tmp = $Gmanager->readlink($file);
                $r_file = str_replace('%2F', '/', rawurlencode($tmp[1]));

                if (Config::$index['name']) {
                    $name = htmlspecialchars($Gmanager->strLink($tmp[0], true), ENT_NOQUOTES);
                    $pname = '<td><a href="index.php?c=' . $r_file . '/' . $add . '">' . $name . '/</a></td>';
                }
                if (Config::$index['down']) {
                    $pdown = '<td> </td>';
                }
                if (Config::$index['type']) {
                    $ptype = '<td>LINK</td>';
                }
                if (Config::$index['size']) {
                    $isize = $stat['size'];
                    $size = $Gmanager->formatSize($isize);
                    $psize = '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $pchange = '<td><a href="change.php?' . $r_file . '/">' . Language::get('ch') . '</a></td>';
                }
                if (Config::$index['del']) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '/">' . Language::get('dl') . '</a></td>';
                }
                if (Config::$index['chmod']) {
                    $chmod = $Gmanager->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::$index['date']) {
                    $pdate = '<td>' . strftime(Config::$date_format, $time) . '</td>';
                }
                if (Config::$index['uid']) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::$index['gid']) {
                    $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
                }
                $page0[$key . '_' . $i][$i] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate. $puid . $pgid;
            } else if ($Gmanager->is_dir($file)) {
                $type = 'DIR';
                if (Config::$index['name']) {
                    if (Config::$realname == 1) {
                        $realpath = $Gmanager->realpath($file);
                        $name = $realpath ? str_replace('\\', '/', $realpath) : $file;
                    } else if (Config::$realname == 2) {
                        $name = $basename;
                    } else {
                        $name = $file;
                    }
                    $name = htmlspecialchars($Gmanager->strLink($name, true), ENT_NOQUOTES);
                    $pname = '<td><a href="index.php?c=' . $r_file . '/' . $add . '">' . $name . '/</a></td>';
                }
                if (Config::$index['down']) {
                    $pdown = '<td> </td>';
                }
                if (Config::$index['type']) {
                    $ptype = '<td>DIR</td>';
                }
                if (Config::$index['size']) {
                    if (Config::$dir_size) {
                        $isize = $Gmanager->size($file, true);
                        $size = $Gmanager->formatSize($isize);
                    } else {
                        $isize = $size = Language::get('unknown');
                    }
                        $psize = '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $pchange = '<td><a href="change.php?' . $r_file . '/">' . Language::get('ch') . '</a></td>';
                }
                if (Config::$index['del']) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '/">' . Language::get('dl') . '</a></td>';
                }
                if (Config::$index['chmod']) {
                    $chmod = $Gmanager->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::$index['date']) {
                    $pdate = '<td>' . strftime(Config::$date_format, $time) . '</td>';
                }
                if (Config::$index['uid']) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::$index['gid']) {
                    $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
                }
                $page1[$key . '_' . $i][$i] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate . $puid . $pgid;
            } else {
                $type = htmlspecialchars($Gmanager->getType($basename), ENT_NOQUOTES);
                $archive = $Gmanager->isArchive($type);

                if (Config::$index['name']) {
                    if (Config::$realname == 1) {
                        $realpath = $Gmanager->realpath($file);
                        $name = $realpath ? str_replace('\\', '/', $realpath) : $file;
                    } else if (Config::$realname == 2) {
                        $name = $basename;
                    } else {
                        $name = $file;
                    }
                    $name = htmlspecialchars($Gmanager->strLink($name, true), ENT_NOQUOTES);

                    if ($archive) {
                        $pname = '<td><a href="index.php?' . $r_file . '">' . $name . '</a><br/><a class="submit" href="change.php?go=1&amp;c=' . $r_file . '&amp;mega_full_extract=1">' . Language::get('extract_archive') . '</a></td>';
                    } else {
                        if ($type == 'SQL') {
                            $pname = '<td><a href="edit.php?' . $r_file . '"' . $t . '>' . $name . '</a><br/><a class="submit" href="change.php?go=sql_tables&amp;c=' . $r_file . '">' . Language::get('tables') . '</a><br/><a class="submit" href="change.php?go=sql_installer&amp;c=' . $r_file . '">' . Language::get('create_sql_installer') . '</a></td>';
                        } else {
                            $pname = '<td><a href="edit.php?' . $r_file . '"' . $t . '>' . $name . '</a></td>';
                        }
                    }
                }
                if (Config::$index['down']) {
                    $pdown = '<td><a href="change.php?get=' . $r_file . '">' . Language::get('get') . '</a></td>';
                }
                if (Config::$index['type']) {
                    $ptype = '<td>' . $type . '</td>';
                }
                if (Config::$index['size']) {
                    $isize = $stat['size'];
                    $size = $Gmanager->formatSize($stat['size']);
                    $psize = '<td>' . $size . '</td>';
                }
                if (Config::$index['change']) {
                    $pchange = '<td><a href="change.php?' . $r_file . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::$index['del']) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '">' . Language::get('dl') . '</a></td>';
                }
                if (Config::$index['chmod']) {
                    $chmod = $Gmanager->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::$index['date']) {
                    $pdate = '<td>' . strftime(Config::$date_format, $time) . '</td>';
                }
                if (Config::$index['uid']) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::$index['gid']) {
                    $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
                }
                $page2[$key . '_' . $i][$i] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate . $puid . $pgid;
            }
        }


        $p = array_merge($page0, $page1, $page2);

        $a = array_keys($page0);
        $b = array_keys($page1);
        $c = array_keys($page2);
        unset($page0, $page1, $page2);

        natcasesort($a);
        natcasesort($b);
        natcasesort($c);
        if ($down) {
            $a = array_reverse($a, false);
            $b = array_reverse($b, false);
            $c = array_reverse($c, false);
        }

        foreach (array_merge($a, $b, $c) as $var) {
            foreach ($p[$var] as $f) {
                $page[] = $f;
            }
        }
        unset($p, $a, $b, $c);

        return $page;
    }


    /**
     * getListSearchArray
     * 
     * @param object $Gmanager
     * @param string $c
     * @param string $s
     * @param bool   $w
     * @param bool   $r
     * @param bool   $h
     * @param int    $limit
     * @param bool   $archive
     * @param string $t
     * @return array
     */
    private static function _getListSearchArray (Gmanager $Gmanager, $c = '', $s = '', $w = false, $r = false, $h = false, $limit = 8388608, $archive = false, $t = '')
    {
        static $count = 0;
        static $page  = array();

        $c = str_replace('//', '/', $c . '/');

        foreach ($Gmanager->iterator($c) as $f) {
            if ($Gmanager->is_dir($c . $f)) {
                self::_getListSearchArray($Gmanager, $c . $f . '/', $s, $w, $r, false, $limit, $archive, $t);
            }

            //$h_file = htmlspecialchars($c . $f, ENT_COMPAT);
            $r_file = str_replace('%2F', '/', rawurlencode($c . $f));
            $type = htmlspecialchars($Gmanager->getType(basename($f)), ENT_NOQUOTES);
            $arch = $Gmanager->isArchive($type);
            $stat = $Gmanager->stat($c . $f);
            $name = htmlspecialchars($Gmanager->strLink($c . $f, true), ENT_NOQUOTES);

            $pname = $pdown = $ptype = $psize = $pchange = $pdel = $pchmod = $pdate = $puid = $pgid = $pn = $in = null;

            if ($w) {
                if ($stat['size'] > $limit || ($arch && !$archive) || ($arch && $archive && $type != 'GZ')) {
                    continue;
                }

                if ($type == 'GZ') {
                    $fl = $Gmanager->getGzContent($c . $f);
                } else {
                    $fl = $Gmanager->file_get_contents($c . $f);
                }

                // Fix for PHP < 6.0
                if (!$r && !$h) {
                    if (@iconv('UTF-8', 'UTF-8', $fl) == $fl) {
                        $fl = strtolower(@iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $fl));
                    } else {
                        $fl = strtolower($fl);
                    }
                }
                if (!$in = substr_count($fl, $s)) {
                    continue;
                }
                $in = ' (' . $in . ')';
            } else {
                if ($r || $h) {
                    $fs = $f;
                } else {
                    // Fix for PHP < 6.0
                    if (@iconv('UTF-8', 'UTF-8', $f) == $f) {
                        $fs = strtolower(@iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $f));
                    } else {
                        $fs = strtolower($f);
                    }
                }
                if (strpos($fs, $s) === false) {
                    continue;
                }
            }

            $count++;

            if (Config::$index['name']) {
                if ($arch) {
                    $pname = '<td><a href="index.php?' . $r_file . '">' . $name . '</a>' . $in . '</td>';
                } else {
                    $pname = '<td><a href="edit.php?' . $r_file . '"' . $t . '>' . $name . '</a>' . $in . '</td>';
                }
            }
            if (Config::$index['down']) {
                $pdown = '<td><a href="change.php?get=' . $r_file . '">' . Language::get('get') . '</a></td>';
            }
            if (Config::$index['type']) {
                $ptype = '<td>' . $type . '</td>';
            }
            if (Config::$index['size']) {
                $psize = '<td>' . $Gmanager->formatSize($stat['size']) . '</td>';
            }
            if (Config::$index['change']) {
                $pchange = '<td><a href="change.php?' . $r_file . '">' . Language::get('ch') . '</a></td>';
            }
            if (Config::$index['del']) {
                $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '">' . Language::get('dl') . '</a></td>';
            }
            if (Config::$index['chmod']) {
                $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $Gmanager->lookChmod($c . $f) . '</a></td>';
            }
            if (Config::$index['date']) {
                $pdate = '<td>' . strftime(Config::$date_format, $stat['mtime']) . '</td>';
            }
            if (Config::$index['uid']) {
                $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
            }
            if (Config::$index['gid']) {
                $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
            }
            if (Config::$index['n']) {
                $pn = '<td>' . $count . '</td>';
            }

            $page[$f] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate . $puid . $pgid . $pn;
        }

        //natcasesort($page);
        return $page;
    }


    /**
     * getListData
     * 
     * @param object $Gmanager
     * @param string $current
     * @param string $itype
     * @param string $down
     * @param int    $pg
     * @param string $addArchive
     * @return string
     */
    public static function getListData(Gmanager $Gmanager, $current = '', $itype = '', $down = '', $pg = 1, $addArchive = '')
    {
        $html = '';
        $data = self::_getListArray($Gmanager, $current, $itype, $down, $addArchive);

        if ($data) {
            self::$getListCountPages = ceil(sizeof($data) / Config::$limit);
            $pg   = $pg < 1 ? 1 : $pg;
            $data = array_slice($data, ($pg * Config::$limit) - Config::$limit, Config::$limit);

            $i    = 1;
            $line = false;

            if (Config::$index['n']) {
                foreach ($data as $var) {
                    $line = !$line;
                    if ($line) {
                        $html .= '<tr class="border">' . $var . '<td>' . ($i++) . '</td></tr>';
                    } else {
                        $html .= '<tr class="border2">' . $var . '<td>' . ($i++) . '</td></tr>';
                    }
                }
            } else {
                foreach ($data as $var) {
                    $line = !$line;
                    if ($line) {
                        $html .= '<tr class="border">' . $var . '</tr>';
                    } else {
                        $html .= '<tr class="border2">' . $var . '</tr>';
                    }
                }
            }
        }

        return $html;
    }


    /**
     * getListSearchData
     * 
     * @param object $Gmanager
     * @param string $c
     * @param string $s
     * @param bool   $w
     * @param bool   $r
     * @param bool   $h
     * @param int    $limit
     * @param bool   $archive
     * @return string
     */
    public static function getListSearchData(Gmanager $Gmanager, $c = '', $s = '', $w = false, $r = false, $h = false, $limit = 8388608, $archive = false)
    {
        $html = '';

        if ($h) {
            $s = implode('', array_map('chr', str_split($s, 4)));
        } else if (!$r) {
            // Fix for PHP < 6.0
            $s = strtolower(@iconv('UTF-8', Config::$altencoding . '//TRANSLIT', $s));
        }

        $data = self::_getListSearchArray($Gmanager, $c, $s, $w, $r, $h, $limit, $archive, (Config::$target ? ' target="_blank"' : ''));

        if ($data) {
            $line = false;
            foreach ($data as $var) {
                $line = !$line;
                $html .= $line ? '<tr class="border">' . $var . '</tr>' : '<tr class="border2">' . $var . '</tr>';
            }
        }

        return $html;
    }


    /**
     * getListEmptyData
     * 
     * @return string
     */
    public static function getListEmptyData ()
    {
        return '<tr class="border"><th colspan="' . (array_sum(Config::$index) + 1) . '">' . Language::get('dir_empty') . '</th></tr>';
    }


    /**
     * getListEmptySearchData
     * 
     * @return string
     */
    public static function getListEmptySearchData ()
    {
        return '<tr class="border"><th colspan="' . (array_sum(Config::$index) + 1) . '">' . Language::get('empty_search') . '</th></tr>';
    }


    /**
     * getListDenyData
     * 
     * @return string
     */
    public static function getListDenyData ()
    {
        return '<tr><td class="red" colspan="' . (array_sum(Config::$index) + 1) . '">' . Language::get('permission_denided') . '</td></tr>';
    }
}

?>
