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


define('GMANAGER_START', microtime(true));

require 'lib/Config.php';
$Gmanager = new Gmanager;


if (Config::$current == '.') {
    Config::$hCurrent = htmlspecialchars($Gmanager->getcwd(), ENT_COMPAT);
}


$type = $Gmanager->getType(basename(Config::$hCurrent));
$archive = $Gmanager->isArchive($type);
$f = 0;
$if = isset($_GET['f']);
$ia = isset($_GET['add_archive']);

$Gmanager->sendHeader();

echo str_replace('%title%', Config::$hCurrent, Config::$top) . '<div class="w2">' . $GLOBALS['lng']['title_index'] . '<br/></div>' . $Gmanager->head() . $Gmanager->langJS();


if (Config::$addressBar) {
    echo '<div class="edit"><form action="index.php?" method="get"><div>';
    if ($ia) {
        echo '<input type="hidden" name="add_archive" value="' . htmlspecialchars($_GET['add_archive']) . '"/><input type="hidden" name="go" value="1"/>';
    }
    echo '<input type="text" name="c" value="' . Config::$hCurrent . '"/> <input type="submit" value="' . $GLOBALS['lng']['go'] . '"/></div></form></div>';
}

if ($idown = isset($_GET['down'])) {
    $down = '&amp;up';
    $mnem = '&#171;';
} else {
    $down = '&amp;down';
    $mnem = '&#187;';
}

if (!$if) {
    if (!$archive) {

        $itype = '';

        if (isset($_GET['time'])) {
            $itype = 'time';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;time' . $down . '">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else if (isset($_GET['type'])) {
            $itype = 'type';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;type' . $down . '">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else if (isset($_GET['size'])) {
            $itype = 'size';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;size' . $down . '">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else if (isset($_GET['chmod'])) {
            $itype = 'chmod';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;chmod' . $down . '">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else if (isset($_GET['uid'])) {
            $itype = 'uid';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;uid' . $down . '">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else if (isset($_GET['gid'])) {
            $itype = 'gid';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th><a href="?c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . '&amp;gid' . $down . '">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        } else {
            $itype = '';
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . $down . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;type">' . $GLOBALS['lng']['type'] . '</a></th>' : '') . (Config::$index['size'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;size">' . $GLOBALS['lng']['size'] . '</a></th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;chmod">' . $GLOBALS['lng']['chmod'] . '</a></th>' : '') . (Config::$index['date'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;time">' . $GLOBALS['lng']['date'] . '</a></th>' : '') . (Config::$index['uid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;uid">' . $GLOBALS['lng']['uid'] . '</a></th>' : '') . (Config::$index['gid'] ? '<th><a href="?c=' . Config::$rCurrent . '&amp;gid">' . $GLOBALS['lng']['gid'] . '</a></th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
        }
    } else if ($archive != 'GZ') {
        echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th>' . $mnem . ' <a href="?c=' . Config::$rCurrent . $down . '">' . $GLOBALS['lng']['name'] . '</a></th>' : '') . (Config::$index['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . (Config::$index['type'] ? '<th>' . $GLOBALS['lng']['type'] . '</th>' : '') . (Config::$index['size'] ? '<th>' . $GLOBALS['lng']['size'] . '</th>' : '') . (Config::$index['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . (Config::$index['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . (Config::$index['chmod'] ? '<th>' . $GLOBALS['lng']['chmod'] . '</th>' : '') . (Config::$index['date'] ? '<th>' . $GLOBALS['lng']['date'] . '</th>' : '') . (Config::$index['uid'] ? '<th>' . $GLOBALS['lng']['uid'] . '</th>' : '') . (Config::$index['gid'] ? '<th>' . $GLOBALS['lng']['gid'] . '</th>' : '') . (Config::$index['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>';
    }
}

if ($archive == 'ZIP') {
    if ($if) {
        echo $Gmanager->lookZipFile(Config::$current, $_GET['f']);
    } else {
        echo $Gmanager->listZipArchive(Config::$current, $idown);
        $f = 1;
    }
} else if ($archive == 'TAR') {
    if ($if) {
        echo $Gmanager->lookTarFile(Config::$current, $_GET['f']);
    } else {
        echo $Gmanager->listTarArchive(Config::$current, $idown);
        $f = 1;
    }
} else if ($archive == 'GZ') {
    echo $Gmanager->gz(Config::$current) . '<div class="ch"><form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div><input type="submit" name="gz_extract" value="' . $GLOBALS['lng']['extract_archive'] . '"/></div></form></div>';
    $if = true;
} else if ($archive == 'BZ2' && extension_loaded('bz2')) {
    if ($if) {
        echo $Gmanager->lookTarFile(Config::$current, $_GET['f']);
    } else {
        echo $Gmanager->listTarArchive(Config::$current, $idown);
        $f = 1;
    }
} else if ($archive == 'RAR' && extension_loaded('rar')) {
    if ($if) {
        echo $Gmanager->lookRarFile(Config::$current, $_GET['f']);
    } else {
        echo $Gmanager->listRarArchive(Config::$current, $idown);
        $f = 1;
    }
} else {
    echo $Gmanager->look(Config::$current, $itype, $idown);
}

if ($Gmanager->file_exists(Config::$current) || $Gmanager->is_link(Config::$current)) {
    if ($archive) {
        $d = str_replace('%2F', '/', rawurlencode(dirname(Config::$current)));
        $found = '<div class="rb">' . $GLOBALS['lng']['create'] . ' <a href="change.php?go=create_file&amp;c=' . $d . '">' . $GLOBALS['lng']['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . $d . '">' . $GLOBALS['lng']['dir'] . '</a><br/></div><div class="rb"><a href="change.php?go=upload&amp;c=' . $d . '">' . $GLOBALS['lng']['upload'] . '</a><br/></div><div class="rb"><a href="change.php?go=mod&amp;c=' . $d . '">' . $GLOBALS['lng']['mod'] . '</a><br/></div>';
    } else {
        $found = '<form action="' . $_SERVER['PHP_SELF'] . '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_COMPAT, 'UTF-8') . '" method="post"><div><input name="limit" value="' . Config::$limit . '" type="text" onkeypress="return number(event)" style="-wap-input-format:\'*N\';width:2%;"/><input type="submit" value="' . $GLOBALS['lng']['limit'] . '"/></div></form><div class="rb">' . $GLOBALS['lng']['create'] . ' <a href="change.php?go=create_file&amp;c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['dir'] . '</a><br/></div><div class="rb"><a href="change.php?go=upload&amp;c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['upload'] . '</a><br/></div><div class="rb"><a href="change.php?go=mod&amp;c=' . Config::$rCurrent . '">' . $GLOBALS['lng']['mod'] . '</a><br/></div>';
    }
} else {
    $found = '<div class="red">' . $GLOBALS['lng']['not_found'] . '(' . Config::$hCurrent . ')' . '<br/></div>';
}


$tm = '<div class="rb">' . round(microtime(true) - GMANAGER_START, 4) . '<br/></div>';

if (!$if && !$f && !$ia) {
    echo '</table><div class="ch"><input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="full_chmod" value="' .$GLOBALS['lng']['chmod'] . '"/> <input onclick="return (checkForm(document.forms[1],\'check[]\') &amp;&amp; delNotify());" type="submit" name="full_del" value="' . $GLOBALS['lng']['del'] . '"/> <input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="full_rename" value="' . $GLOBALS['lng']['change'] . '"/> <input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="fname" value="' . $GLOBALS['lng']['rename'] . '"/> <input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="create_archive" value="' . $GLOBALS['lng']['create_archive'] . '"/></div></div></form>' . $found . $tm . Config::$foot;
} else if ($f) {
    echo '</table><div class="ch"><input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="full_extract" value="' . $GLOBALS['lng']['extract_file'] . '"/> <input type="submit" name="mega_full_extract" value="' . $GLOBALS['lng']['extract_archive'] . '"/>';
    if ($type != 'RAR') {
        echo ' <input type="submit" name="add_archive" value="' . $GLOBALS['lng']['add_archive'] . '"/> <input onclick="return (checkForm(document.forms[1],\'check[]\') &amp;&amp; delNotify());" type="submit" name="del_archive" value="' . $GLOBALS['lng']['del'] . '"/>';
    }
    echo '</div></div></form>' . $found . $tm . Config::$foot;
} else if ($ia) {
    echo '</table><div class="ch"><input type="hidden" name="add_archive" value="' . rawurlencode($_GET['add_archive']) . '"/><input onclick="return checkForm(document.forms[1],\'check[]\');" type="submit" name="name" value="' . $GLOBALS['lng']['add_archive'] . '"/></div></div></form>' . $found . $tm . Config::$foot;
} else {
    echo $found . $tm . Config::$foot;
}

?>
