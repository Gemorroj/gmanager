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
class Registry
{
    /**
     * @var array
     */
    private static $_data = [];

    /**
     * set.
     *
     * @param string $key
     * @param mixed  $val
     */
    public static function set($key, $val)
    {
        self::$_data[$key] = $val;
    }

    /**
     * get.
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        return self::$_data[$key];
    }

    /**
     * remove.
     *
     * @param string $key
     */
    public static function remove($key)
    {
        unset(self::$_data[$key]);
    }

    /**
     * exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function exists($key)
    {
        return \array_key_exists($key, self::$_data);
    }
}
