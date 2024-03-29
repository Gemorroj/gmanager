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
class Patterns
{
    public const Htaccess = 'Htaccess';
    public const HTML = 'HTML';
    public const MySQL = 'MySQL';
    public const PHP = 'PHP';
    public const PostgreSQL = 'PostgreSQL';
    public const SQLite = 'SQLite';
    public const WML = 'WML';
    public const XHTML = 'XHTML';

    /**
     * @var array
     */
    private $_patterns = [];

    /**
     * Get patterns.
     *
     * @param array $patterns
     *
     * @return Patterns
     */
    public function set($patterns = [])
    {
        foreach ($patterns as $pattern) {
            $this->_patterns[$pattern] = \call_user_func('Patterns_'.$pattern.'::get');
        }

        return $this;
    }

    /**
     * Get pattern.
     *
     * @param string $pattern
     * @param string $name
     *
     * @return string
     */
    public function getPattern($pattern = null, $name = null)
    {
        if (isset($this->_patterns[$pattern][$name])) {
            return $this->_patterns[$pattern][$name];
        }

        return null;
    }

    /**
     * Get patterns.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->_patterns;
    }

    /**
     * Get patterns.
     *
     * @return string
     */
    public function getOptions()
    {
        $out = '';
        foreach ($this->_patterns as $key => $val) {
            $out .= '<optgroup label="'.\htmlspecialchars($key).'">';
            foreach ($val as $k => $v) {
                $out .= '<option value="'.\rawurlencode($v).'">'.\htmlspecialchars($k, \ENT_NOQUOTES).'</option>';
            }
            $out .= '</optgroup>';
        }

        return $out;
    }
}
