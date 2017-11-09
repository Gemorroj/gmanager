<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2017 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class Helper_System
{
    /**
     * Multibyte basename
     *
     * @param string    $path
     * @param string    $suffix
     * @return string
     */
    public static function basename ($path, $suffix = '')
    {
        $file = explode('/', $path);
        return rtrim(end($file), $suffix);
    }


    /**
     * id2user
     *
     * @param int    $id
     * @return string
     */
    public static function id2user ($id = 0)
    {
        if (Registry::get('sysType') === 'WIN') {
            return '';
        } else {
            if (function_exists('posix_getpwuid') && $name = posix_getpwuid($id)) {
                return $name['name'];
            }

            $processBuilder = new \Symfony\Component\Process\ProcessBuilder();
            $processBuilder->setPrefix('id');
            $processBuilder->setArguments(array('-n', '-u', $id));
            $processBuilder->getProcess()->run();
            if ($processBuilder->getProcess()->isSuccessful()) {
                return $processBuilder->getProcess()->getOutput();
            }

            $processBuilder = new \Symfony\Component\Process\ProcessBuilder();
            $processBuilder->setPrefix('getent');
            $processBuilder->setArguments(array('passwd', $id));
            $processBuilder->getProcess()->run();
            if ($processBuilder->getProcess()->isSuccessful()) {
                $tmp = explode(':', $processBuilder->getProcess()->getOutput(), 2);
                return trim($tmp[0]);
            }
        }

        return $id;
    }


    /**
     * id2group
     *
     * @param int    $id
     * @return string
     */
    public static function id2group ($id = 0)
    {
        if (Registry::get('sysType') === 'WIN') {
            return '';
        } else {
            if (function_exists('posix_getgrgid') && $name = posix_getgrgid($id)) {
                return $name['name'];
            }

            $processBuilder = new \Symfony\Component\Process\ProcessBuilder();
            $processBuilder->setPrefix('getent');
            $processBuilder->setArguments(array('group', $id));
            $processBuilder->getProcess()->run();
            if ($processBuilder->getProcess()->isSuccessful()) {
                $tmp = explode(':', $processBuilder->getProcess()->getOutput(), 2);
                return trim($tmp[0]);
            }
        }

        return $id;
    }


    /**
     * getType
     *
     * @param string $f
     * @return string
     */
    public static function getType ($f)
    {
        $type = array_reverse(explode('.', mb_strtoupper($f)));
        if (isset($type[1]) && $type[1] === 'TAR') {
            return $type[1] . '.' . $type[0];
        }

        return $type[0];
    }


    /**
     * clean
     *
     * @param string $dir
     */
    public static function clean ($dir = '')
    {
        $h = @opendir($dir);
        if (!$h) {
            return;
        }

        while (($f = readdir($h)) !== false) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            if (is_dir($dir . '/' . $f)) {
                self::clean($dir . '/' . $f);
            } else {
                unlink($dir . '/' . $f);
            }
        }
        closedir($h);
        rmdir($dir);
    }


    /**
     * @param string $output
     * @return string
     */
    public static function makeConsoleOutput($output)
    {
        $isUtf8 = mb_convert_encoding($output, 'UTF-8', 'UTF-8') === $output;

        if ($isUtf8) {
            return $isUtf8;
        }

        if (Registry::get('sysType') === 'WIN') {
            return mb_convert_encoding($output, 'UTF-8', 'CP866');
        }

        return mb_convert_encoding($output, 'UTF-8');
    }
}
