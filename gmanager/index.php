<?php
// кодировка UTF-8
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7 alpha
 * 
 * PHP version >= 5.2.1
 * 
 */


require 'functions.php';


$current = c($_SERVER['QUERY_STRING'], $_GET['c']);
if($current == '.'){
	$h_current = htmlspecialchars($mode->getcwd(), ENT_COMPAT);
}
else{
	$h_current = htmlspecialchars($current, ENT_COMPAT);
}
$r_current = str_replace('%2F', '/', rawurlencode($current));

$type = strtoupper(strrchr($h_current, '.'));
$add_archive = $_GET['add_archive'];

send_header($_SERVER['HTTP_USER_AGENT']);

echo str_replace('%dir%', $h_current, $top) . '
<div class="w2">
' . $lng['title_index'] . '<br/>
</div>
' . this($current);

if ($string) {
    echo '<div>
<form action="index.php?" method="get">
<div>';
    if ($add_archive) {
        echo '<input type="hidden" name="add_archive" value="' . $add_archive .
            '"/><input type="hidden" name="go" value="1"/>';
    }
    echo '<input type="text" name="c" value="' . $h_current . '"/><br/>
<input type="submit" value="' . $lng['go'] . '"/>
</div>
</form>
</div>';
}


if (!$_GET['f'] && $type != '.GZ') {
    if (isset($_GET['time'])) {
        print '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
<th><a href="?c=' . $r_current . '">' . $lng['name'] . '</a></th>
<th>' . $lng['get'] . '</th>
<th>' . $lng['type'] . '</th>
<th>' . $lng['size'] . '</th>
<th>' . $lng['change'] . '</th>
<th>' . $lng['del'] . '</th>
<th>' . $lng['chmod'] . '</th>
<th class="red">' . $lng['date'] . '</th>
<th>' . $lng['n'] . '</th>
</tr>';
    }
	else {
        echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
<th class="red">' . $lng['name'] . '</th>
<th>' . $lng['get'] . '</th>
<th>' . $lng['type'] . '</th>
<th>' . $lng['size'] . '</th>
<th>' . $lng['change'] . '</th>
<th>' . $lng['del'] . '</th>
<th>' . $lng['chmod'] . '</th>
<th><a href="?c=' . $r_current . '&amp;time">' . $lng['date'] . '</a></th>
<th>' . $lng['n'] . '</th>
</tr>';
    }
}

$archive = 0;
if ($type == '.ZIP' || $type == '.JAR') {
    if ($_GET['f']) {
        echo look_zip_file($current, $_GET['f']);
    }
	else {
        echo list_zip_archive($current);
        $archive = 1;
    }
} elseif ($type == '.TAR' || $type == '.TGZ' || $type == '.BZ' || $type == '.BZ2') {
    if ($_GET['f']) {
        echo look_tar_file($current, $_GET['f']);
    }
	else {
        echo list_tar_archive($current);
        $archive = 1;
    }
} elseif ($type == '.GZ') {
    print gz($current) . '<div class="ch"><form action="change.php?c=' . $r_current .
        '&amp;go=1" method="post"><div><input type="submit" name="gz_extract" value="' .
        $lng['extract_archive'] . '"/></div></form></div>';
    $_GET['f'] = 1;
}
else {
    look($current);
}

if (!$_GET['f']) {
echo '<tr><td class="w" colspan="9" style="text-align:left;padding:0 0 0 1%;"><input type="checkbox" value="check" onclick="check(this.form,\'check[]\',this.checked)"/> '.$lng['check'].'</td></tr>';
}


if ($mode->file_exists($current) || $mode->is_link($current)) {
switch($type){
	default:
	$found = '<form action="'.$_SERVER['PHP_SELF'].'?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_COMPAT, 'UTF-8').'" method="post"><div><input name="limit" value="'.$limit.'" type="text" style="width:2%"/><input type="submit" value="'.$lng['limit'].'"/></div></form>
<div class="rb">' . $lng['create'] . ' <a href="change.php?go=create_file&amp;c=' . $r_current . '">' . $lng['file'] .
        '</a> / <a href="change.php?go=create_dir&amp;c=' . $r_current . '">' . $lng['dir'] .
        '</a><br/></div>
<div class="rb"><a href="change.php?go=upload&amp;c=' . $r_current . '">' . $lng['upload'] .
        '</a><br/></div>
<div class="rb"><a href="change.php?go=mod&amp;c=' . $r_current . '">' . $lng['mod'] .
        '</a><br/></div>';
	break;	
	
	case '.ZIP':
	case '.JAR':
	case '.GZ':
	case '.TAR':
	case '.TGZ':
	case '.BZ':
	case '.BZ2':
	$current_d = str_replace('%2F', '/', rawurlencode(dirname($current)));
	$found = '<div class="rb">' . $lng['create'] . ' <a href="change.php?go=create_file&amp;c=' . $current_d . '">' . $lng['file'] .
        '</a> / <a href="change.php?go=create_dir&amp;c=' . $current_d . '">' . $lng['dir'] .
        '</a><br/></div>
<div class="rb"><a href="change.php?go=upload&amp;c=' . $current_d . '">' . $lng['upload'] .
        '</a><br/></div>
<div class="rb"><a href="change.php?go=mod&amp;c=' . $current_d . '">' . $lng['mod'] .
        '</a><br/></div>';
	break;
}
}
else {
    $found = '<div class="red">' . $lng['not_found'] . '(' . $h_current . ')' . '<br/></div>';
}


$tm = '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>';

if (!$_GET['f'] && !$archive && !$add_archive) {
    echo '</table><div class="ch"><input type="submit" name="full_chmod" value="' .
        $lng['chmod'] . '"/> <input type="submit" name="full_del" value="' . $lng['del'] .
        '"/> <input type="submit" name="full_rename" value="' . $lng['change'] .
        '"/> <input type="submit" name="fname" value="' . $lng['rename'] .
        '"/> <input type="submit" name="create_archive" value="' . $lng['create_archive'] .
        '"/></div></div></form>' . $found . $tm . $foot;
} elseif ($archive) {
    echo '</table><div class="ch"><input type="submit" name="full_extract" value="' .
        $lng['extract_file'] .
        '"/><br/></div><div class="ch"><input type="submit" name="mega_full_extract" value="' .
        $lng['extract_archive'] .
        '"/><br/></div><div class="ch"><input type="submit" name="add_archive" value="' .
        $lng['add_archive'] . '"/></div></div></form>' . $found . $tm . $foot;
} elseif ($add_archive) {
    echo '</table><div class="ch"><input type="hidden" name="add_archive" value="' .
        rawurlencode($add_archive) . '"/><input type="submit" name="name" value="' . $lng['add_archive'] .
        '"/></div></div></form>' . $found . $tm . $foot;
}
else {
    echo $found . $tm . $foot;
}
?>