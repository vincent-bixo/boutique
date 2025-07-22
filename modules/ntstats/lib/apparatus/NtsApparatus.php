<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NtsApparatus
{
    public function __construct()
    {
    }

    /**
     * Return part of a string
     *
     * @param string    The input string
     * @param int       Start position
     * @param int       Length characters
     *
     * @return string extracted part of string; or FALSE on failure, or an empty string
     */
    public static function substr($string, $start, $length = 0)
    {
        if (!$length) {
            return substr($string, $start);
        }

        return substr($string, $start, $length);
    }

    /**
     * Get size of a String
     *
     * @param string    The input string
     *
     * @return int The size of the String
     */
    public static function strlen($string)
    {
        return strlen($string);
    }

    /**
     * Get absolute path
     *
     * @param string    The path
     *
     * @return string The absolute path
     */
    public static function getRealPath($path)
    {
        if (function_exists('stream_resolve_include_path')) {
            return stream_resolve_include_path($path);
        }

        return realpath($path);
    }

    /**
     * Check if file exists
     *
     * @param string    The path of the file
     *
     * @return bool True if the file exists false otherwise
     */
    public static function checkFileExists($path)
    {
        if (function_exists('stream_resolve_include_path')) {
            return (stream_resolve_include_path($path) !== false) ? true : false;
        }

        return file_exists($path);
    }

    /**
     * Return a correct name for a file
     *
     * @param string $name File name
     * @param string $replacement Replacement character for the forbidden one
     *
     * @return string Correct filename
     */
    public static function correctFileName($name, $replacement = '_')
    {
        return preg_replace('/[^a-zA-Z0-9-._]/i', $replacement, self::replaceAccents($name));
    }

    /**
     * Replace accents
     *
     * @param string $string String
     * @param string $charset Charset used. Default is utf-8
     *
     * @return string String without accents
     */
    public static function replaceAccents($string, $charset = 'utf-8')
    {
        $string = htmlentities($string, ENT_NOQUOTES, $charset);

        $string = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string);
        $string = preg_replace('#&[^;]+;#', '', $string);

        return $string;
    }
}
