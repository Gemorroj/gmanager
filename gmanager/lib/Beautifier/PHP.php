<?php
// encoding = 'utf-8'
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


class Beautifier_PHP
{
    public $tab = '    ';


    public function beautify($str)
    {
        $out = null;
        $tab = 0;
        $str = str_split(str_replace("\r", '', $str), 1);
        $all = sizeof($str);
        $block = array(false, false, false, false, false);
        $array = false;

        for ($i = 0; $i < $all; ++$i) {
            switch ($str[$i]) {
                case "'":
                    $prev = iconv_substr($out, -1);
                    if ($prev != '\\' && $block[0] === false && $block[1] === false && $block[2] === false && $block[3] === false && $block[4] === false) {
                        $block[0] = true;
                        if (!in_array($prev, array('[', '(', ' ', "\n"))) {
                            $out .= ' ';
                        }
                    } else {
                        $block[0] = false;
                    }
                    $out .= $str[$i];
                    break;


                case '"':
                    $prev = iconv_substr($out, -1);
                    if ($prev != '\\' && $block[0] === false && $block[1] === false && $block[2] === false && $block[3] === false && $block[4] === false) {
                        $block[1] = true;
                        if (!in_array($prev, array('[', '(', ' ', "\n"))) {
                            $out .= ' ';
                        }
                    } else {
                        $block[1] = false;
                    }
                    $out .= $str[$i];
                    break;


                case '#':
                    if ($block[0] === false && $block[1] === false && $block[2] === false && $block[3] === false) {
                        $block[4] = true;
                    }
                    $out .= $str[$i];
                    break;


                case '*':
                    $prev = iconv_substr($out, -1);
                    if ($prev == '/' && $block[0] === false && $block[1] === false && $block[3] === false && $block[4] === false) {
                        $block[2] = true;
                    }
                    $out .= $str[$i];
                    break;


                case '/':
                    $prev = iconv_substr($out, -1);
                    if ($prev == '*' && $block[0] === false && $block[1] === false && $block[2] === true && $block[3] === false && $block[4] === false) {
                        $block[2] = false;
                    }

                    if ($prev == '/' && $block[0] === false && $block[1] === false && $block[2] === false && $block[4] === false) {
                        $block[3] = true;
                    }
                    $out .= $str[$i];
                    break;


                case "\t":
                    $out .= $this->tab;
                    break;


                case "\n":
                    $block[3] = $block[4] = false;
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3]) {
                        $rep = str_repeat($this->tab, $tab);
                        $len = strlen($rep);
                        if (iconv_substr($out, -$len) != $rep) {
                            $out .= $str[$i] . $rep;
                            while (true) {
                                if (@$str[$i + 1] != ' ' && @$str[$i + 1] != "\n") {
                                    break;
                                }
                                $i++;
                            }
                        }
                    } else if ($block[0] || $block[1] || $block[2] || $block[3] || $block[4] || iconv_substr($out, -(2 + iconv_strlen(str_repeat($this->tab, $tab)))) != '{' . "\n" . str_repeat($this->tab, $tab)) {
                        $out .= $str[$i];
                    }
                    break;


                case ' ':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4] && (iconv_substr($out, -2) == ', ') || iconv_substr($out, -3) == ' . ' || iconv_substr($out, -3) == ' = ') {
                        break;
                    } else if ($block[0] || $block[1] || $block[2] || $block[3] || $block[4] || (iconv_substr($out, -(2 + iconv_strlen(str_repeat($this->tab, $tab)))) != '{' . "\n" . str_repeat($this->tab, $tab) && iconv_substr($out, -(2 + iconv_strlen(str_repeat($this->tab, $tab)))) != '(' . "\n" . str_repeat($this->tab, $tab))) {
                        $out .= $str[$i];
                    }
                    break;


                case '{':
                    if ($block[0] || $block[1] || $block[2] || $block[3] || $block[4]) {
                        $out .= $str[$i];
                        break;
                    } else if (!in_array(iconv_substr($out, -1), array(' ', "\n"))) {
                        $out .= ' ';
                    }
                    $tab++;
                    $out .= $str[$i] . "\n" . str_repeat($this->tab, $tab);
                    break;


                case '}':
                    if (!$block[0] && !$block[1] && !$block[2] || $block[3] || $block[4]) {
                        $tab--;
                        $out = rtrim($out) . "\n" . str_repeat($this->tab, $tab) . $str[$i] . "\n";
                    } else {
                        $out .= $str[$i];
                    }
                    break;


                case '(':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4]) {
                        $out = rtrim($out);
                        if (strtoupper(iconv_substr($out, -5)) == 'ARRAY') {
                            $tab++;
                            $out .= ' ' . $str[$i] . "\n" . str_repeat($this->tab, $tab);
                            $array = true;
                        } else {
                            $out .= ' ' . $str[$i];
                        }
                    } else {
                        $out .= $str[$i];
                    }
                    break;


                case ')':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4] && $array) {
                        $tab--;
                        $array = false;
                        $out = rtrim($out) . "\n" . str_repeat($this->tab, $tab) . $str[$i];
                    } else {
                        $out .= $str[$i];
                    }
                    break;


                case ',':
                    $out .= $str[$i];
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4]) {
                        $out .= ' ';
                    }
                    break;


                case '.':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4]) {
                        if (iconv_substr($out, -1) != ' ') {
                            $out .= ' ';
                        }
                        $out .= $str[$i] . ' ';
                    } else {
                        $out .= $str[$i];
                    }
                    break;


                case '=':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4]) {
                        $prev = iconv_substr($out, -1);
                        if ($prev != ' ' && $prev != '=') {
                            $out .= ' ';
                        }
                        $out .= $str[$i];
                        if ($str[$i + 1] != '=' && $str[$i + 1] != ' ') {
                            $out .= ' ';
                        }
                    } else {
                        $out .= $str[$i];
                    }
                    break;


                case 'E':
                case 'e':
                    if (!$block[0] && !$block[1] && !$block[2] && !$block[3] && !$block[4]) {
                        if (strtoupper(iconv_substr($out, -3)) == 'ELS' && in_array(iconv_substr($out, -4, 1), array("\n", ' '))) {
                            $out = rtrim(iconv_substr($out, 0, -4)) . ' ' . iconv_substr($out, -3);
                        }
                    }
                    $out .= $str[$i];
                    break;


                default:
                    $out .= $str[$i];
                    break;
            }
        }
        return $out;
    }
}

?>
