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
     * @param string $current
     * @param string $itype
     * @param string $down
     * @param string $addArchive
     * @return array
     */
    private static function _getListArray ($current = '', $itype = '', $down = '', $addArchive = '')
    {
        $type   = $isize = $uid = $gid = $chmod = $name = $time = '';
        $page   = $page0 = $page1 = $page2 = array();
        $i      = 0;

        $t      = (Config::get('Editor', 'target') ? ' target="_blank"' : '');
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


        foreach (Registry::getGmanager()->iterator($current) as $file) {
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
            $stat = Registry::getGmanager()->stat($file);
            $time = $stat['mtime'];
            $uid  = $stat['owner'];
            $gid  = $stat['group'];


            if (Registry::getGmanager()->is_link($file)) {
                $type = 'LINK';
                $tmp = Registry::getGmanager()->readlink($file);
                $r_file = str_replace('%2F', '/', rawurlencode($tmp[1]));

                if (Config::get('Display', 'name')) {
                    $name = htmlspecialchars(Registry::getGmanager()->strLink($tmp[0], true), ENT_NOQUOTES);
                    $pname = '<td><a href="index.php?c=' . $r_file . '/' . $add . '">' . $name . '/</a></td>';
                }
                if (Config::get('Display', 'down')) {
                    $pdown = '<td> </td>';
                }
                if (Config::get('Display', 'type')) {
                    $ptype = '<td>LINK</td>';
                }
                if (Config::get('Display', 'size')) {
                    $isize = $stat['size'];
                    $size = Registry::getGmanager()->formatSize($isize);
                    $psize = '<td>' . $size . '</td>';
                }
                if (Config::get('Display', 'change')) {
                    $pchange = '<td><a href="change.php?' . $r_file . '/">' . Language::get('ch') . '</a></td>';
                }
                if (Config::get('Display', 'del')) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '/">' . Language::get('dl') . '</a></td>';
                }
                if (Config::get('Display', 'chmod')) {
                    $chmod = Registry::getGmanager()->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::get('Display', 'date')) {
                    $pdate = '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), $time) . '</td>';
                }
                if (Config::get('Display', 'uid')) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::get('Display', 'gid')) {
                    $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
                }
                $page0[$key . '_' . $i][$i] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate. $puid . $pgid;
            } else if (Registry::getGmanager()->is_dir($file)) {
                $type = 'DIR';
                if (Config::get('Display', 'name')) {
                    if (Config::get('Gmanager', 'realName') == Config::REALNAME_FULL) {
                        $realpath = Registry::getGmanager()->realpath($file);
                        $name = $realpath ? str_replace('\\', '/', $realpath) : $file;
                    } else if (Config::get('Gmanager', 'realName') == Config::REALNAME_RELATIVE_HIDE) {
                        $name = $basename;
                    } else {
                        $name = $file;
                    }
                    $name = htmlspecialchars(Registry::getGmanager()->strLink($name, true), ENT_NOQUOTES);
                    $pname = '<td><a href="index.php?c=' . $r_file . '/' . $add . '">' . $name . '/</a></td>';
                }
                if (Config::get('Display', 'down')) {
                    $pdown = '<td> </td>';
                }
                if (Config::get('Display', 'type')) {
                    $ptype = '<td>DIR</td>';
                }
                if (Config::get('Display', 'size')) {
                    if (Config::get('Gmanager', 'dirSize')) {
                        $isize = Registry::getGmanager()->size($file, true);
                        $size = Registry::getGmanager()->formatSize($isize);
                    } else {
                        $isize = $size = Language::get('unknown');
                    }
                        $psize = '<td>' . $size . '</td>';
                }
                if (Config::get('Display', 'change')) {
                    $pchange = '<td><a href="change.php?' . $r_file . '/">' . Language::get('ch') . '</a></td>';
                }
                if (Config::get('Display', 'del')) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '/">' . Language::get('dl') . '</a></td>';
                }
                if (Config::get('Display', 'chmod')) {
                    $chmod = Registry::getGmanager()->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::get('Display', 'date')) {
                    $pdate = '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), $time) . '</td>';
                }
                if (Config::get('Display', 'uid')) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::get('Display', 'gid')) {
                    $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
                }
                $page1[$key . '_' . $i][$i] = '<td class="check"><input name="check[]" type="checkbox" value="' . $r_file . '"/></td>' . $pname . $pdown . $ptype . $psize . $pchange . $pdel . $pchmod . $pdate . $puid . $pgid;
            } else {
                $type = htmlspecialchars(Registry::getGmanager()->getType($basename), ENT_NOQUOTES);
                $archive = Registry::getGmanager()->isArchive($type);

                if (Config::get('Display', 'name')) {
                    if (Config::get('Gmanager', 'realName') == Config::REALNAME_FULL) {
                        $realpath = Registry::getGmanager()->realpath($file);
                        $name = $realpath ? str_replace('\\', '/', $realpath) : $file;
                    } else if (Config::get('Gmanager', 'realName') == Config::REALNAME_RELATIVE_HIDE) {
                        $name = $basename;
                    } else {
                        $name = $file;
                    }
                    $name = htmlspecialchars(Registry::getGmanager()->strLink($name, true), ENT_NOQUOTES);

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
                if (Config::get('Display', 'down')) {
                    $pdown = '<td><a href="change.php?get=' . $r_file . '">' . Language::get('get') . '</a></td>';
                }
                if (Config::get('Display', 'type')) {
                    $ptype = '<td>' . $type . '</td>';
                }
                if (Config::get('Display', 'size')) {
                    $isize = $stat['size'];
                    $size = Registry::getGmanager()->formatSize($stat['size']);
                    $psize = '<td>' . $size . '</td>';
                }
                if (Config::get('Display', 'change')) {
                    $pchange = '<td><a href="change.php?' . $r_file . '">' . Language::get('ch') . '</a></td>';
                }
                if (Config::get('Display', 'del')) {
                    $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '">' . Language::get('dl') . '</a></td>';
                }
                if (Config::get('Display', 'chmod')) {
                    $chmod = Registry::getGmanager()->lookChmod($file);
                    $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . $chmod . '</a></td>';
                }
                if (Config::get('Display', 'date')) {
                    $pdate = '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), $time) . '</td>';
                }
                if (Config::get('Display', 'uid')) {
                    $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
                }
                if (Config::get('Display', 'gid')) {
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
    private static function _getListSearchArray ($c = '', $s = '', $w = false, $r = false, $h = false, $limit = 8388608, $archive = false, $t = '')
    {
        static $count = 0;
        static $page  = array();

        $c = str_replace('//', '/', $c . '/');

        foreach (Registry::getGmanager()->iterator($c) as $f) {
            var_dump($c . $f);
            var_dump(Registry::getGmanager()->is_dir($c . $f));
            if (Registry::getGmanager()->is_dir($c . $f)) {
                self::_getListSearchArray($c . $f . '/', $s, $w, $r, false, $limit, $archive, $t);
                continue;
            }

            $type = htmlspecialchars(Registry::getGmanager()->getType(basename($f)), ENT_NOQUOTES);
            $arch = Registry::getGmanager()->isArchive($type);
            $stat = Registry::getGmanager()->stat($c . $f);

            $pname = $pdown = $ptype = $psize = $pchange = $pdel = $pchmod = $pdate = $puid = $pgid = $pn = $in = null;

            if ($w) {
                if ($stat['size'] > $limit || ($arch && !$archive) || ($arch && $archive && $type != 'GZ')) {
                    continue;
                }

                $fl = ($type == 'GZ') ? Registry::getGmanager()->getGzContent($c . $f) : Registry::getGmanager()->file_get_contents($c . $f);

                // Fix for PHP < 6.0
                if (!$r && !$h) {
                    if (@iconv('UTF-8', 'UTF-8//IGNORE', $fl) == $fl) {
                        $fl = strtolower(@iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//TRANSLIT', $fl));
                    } else {
                        $fl = strtolower($fl);
                    }
                }
                if (($in = substr_count($fl, $s)) === 0) {
                    continue;
                }
                $in = ' (' . $in . ')';
            } else {
                if ($r || $h) {
                    $fs = $f;
                } else {
                    // Fix for PHP < 6.0
                    if (@iconv('UTF-8', 'UTF-8//IGNORE', $f) == $f) {
                        $fs = strtolower(@iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//TRANSLIT', $f));
                    } else {
                        $fs = strtolower($f);
                    }
                }
                if (strpos($fs, $s) === false) {
                    continue;
                }
            }

            $count++;

            //$h_file = htmlspecialchars($c . $f, ENT_COMPAT);
            $r_file = str_replace('%2F', '/', rawurlencode($c . $f));

            if (Config::get('Display', 'name')) {
                $name = htmlspecialchars(Registry::getGmanager()->strLink($c . $f, true), ENT_NOQUOTES);
                if ($arch) {
                    $pname = '<td><a href="index.php?' . $r_file . '">' . $name . '</a>' . $in . '</td>';
                } else {
                    $pname = '<td><a href="edit.php?' . $r_file . '"' . $t . '>' . $name . '</a>' . $in . '</td>';
                }
            }
            if (Config::get('Display', 'down')) {
                $pdown = '<td><a href="change.php?get=' . $r_file . '">' . Language::get('get') . '</a></td>';
            }
            if (Config::get('Display', 'type')) {
                $ptype = '<td>' . $type . '</td>';
            }
            if (Config::get('Display', 'size')) {
                $psize = '<td>' . Registry::getGmanager()->formatSize($stat['size']) . '</td>';
            }
            if (Config::get('Display', 'change')) {
                $pchange = '<td><a href="change.php?' . $r_file . '">' . Language::get('ch') . '</a></td>';
            }
            if (Config::get('Display', 'del')) {
                $pdel = '<td><a onclick="return Gmanager.delNotify();" href="change.php?go=del&amp;c=' . $r_file . '">' . Language::get('dl') . '</a></td>';
            }
            if (Config::get('Display', 'chmod')) {
                $pchmod = '<td><a href="change.php?go=chmod&amp;c=' . $r_file . '">' . Registry::getGmanager()->lookChmod($c . $f) . '</a></td>';
            }
            if (Config::get('Display', 'date')) {
                $pdate = '<td>' . strftime(Config::get('Gmanager', 'dateFormat'), $stat['mtime']) . '</td>';
            }
            if (Config::get('Display', 'uid')) {
                $puid = '<td>' . htmlspecialchars($stat['owner'], ENT_NOQUOTES) . '</td>';
            }
            if (Config::get('Display', 'gid')) {
                $pgid = '<td>' . htmlspecialchars($stat['group'], ENT_NOQUOTES) . '</td>';
            }
            if (Config::get('Display', 'n')) {
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
     * @param string $current
     * @param string $itype
     * @param string $down
     * @param int    $pg
     * @param string $addArchive
     * @return string
     */
    public static function getListData($current = '', $itype = '', $down = '', $pg = 1, $addArchive = '')
    {
        $html = '';
        $data = self::_getListArray($current, $itype, $down, $addArchive);

        if ($data) {
            self::$getListCountPages = ceil(sizeof($data) / Registry::get('limit'));
            $pg   = $pg < 1 ? 1 : $pg;
            $data = array_slice($data, ($pg * Registry::get('limit')) - Registry::get('limit'), Registry::get('limit'));

            $i    = 1;
            $line = false;

            if (Config::get('Display', 'n')) {
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
     * @param string $c
     * @param string $s
     * @param bool   $w
     * @param bool   $r
     * @param bool   $h
     * @param int    $limit
     * @param bool   $archive
     * @return string
     */
    public static function getListSearchData($c = '', $s = '', $w = false, $r = false, $h = false, $limit = 8388608, $archive = false)
    {
        $html = '';

        if ($h) {
            $s = implode('', array_map('chr', str_split($s, 4)));
        } else if (!$r) {
            // Fix for PHP < 6.0
            $s = strtolower(@iconv('UTF-8', Config::get('Gmanager', 'altEncoding') . '//IGNORE', $s));
        }

        $data = self::_getListSearchArray($c, $s, $w, $r, $h, $limit, $archive, (Config::get('Editor', 'target') ? ' target="_blank"' : ''));

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
        return '<tr class="border"><th colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Language::get('dir_empty') . '</th></tr>';
    }


    /**
     * getListEmptySearchData
     * 
     * @return string
     */
    public static function getListEmptySearchData ()
    {
        return '<tr class="border"><th colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Language::get('empty_search') . '</th></tr>';
    }


    /**
     * getListDenyData
     * 
     * @return string
     */
    public static function getListDenyData ()
    {
        return '<tr><td class="red" colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">' . Language::get('permission_denided') . '</td></tr>';
    }
}

?>
