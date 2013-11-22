<?php
namespace MarijnKoesen\PhpDebugUtils;

class Dump
{
    /**
     * var_dump on steroids
     *
     * returns the $input as a highlighted var_export string.
     *
     * @param mixed $input
     * @param boolean $return
     * @return string
     */
    public static function dump($input, $return = false)
    {
        if (is_resource($input)) {
            $var = "<?PHP\r\n" . $input . "\r\n?>";
        } else {
            $var = "<?PHP\r\n" . var_export(self::remove_recursion($input), true) . "\r\n?>";
        }

        $var = str_replace('array (', 'array(', $var);
        $var = str_replace(" => \n", ' => ', $var);
        $str = highlight_string($var, $return);
        if ($return) {
            $str .= "<br />";
        } else {
            echo "<br />";
        }
        return $str;
    }

    /** 
     * Stored log, stack trace, or getTrace, could contain recursions.
     * This callback aim is to remove recursions from a generic var.
     * This function is based on native serialize one.
     *
     * @param mixed $o generic variable to manage
     * @return mixed same var without recursion problem
     */
    private static function remove_recursion($o)
    {
        static $replace;

        if (!isset($replace)) {
            $replace = create_function(
                '$m',
                '$r="\x00{$m[1]}ecursion_";return \'s:\'.strlen($r.$m[2]).\':"\'.$r.$m[2].\'";\';'
            );
        }

        if (is_array($o) || is_object($o)) {
            $re = '#(r|R):([0-9]+);#';
            $serialize = serialize($o);
            if (preg_match($re, $serialize)) {
                $last = $pos = 0;
                while (false !== ($pos = strpos($serialize, 's:', $pos))) {
                    $chunk = substr($serialize, $last, $pos - $last);
                    if (preg_match($re, $chunk)) {
                        $length = strlen($chunk);
                        $chunk = preg_replace_callback($re, $replace, $chunk);
                        $serialize = substr($serialize, 0, $last) . $chunk . substr($serialize, $last + ($pos - $last));
                        $pos += strlen($chunk) - $length;
                    }
                    $pos += 2;
                    $last = strpos($serialize, ':', $pos);
                    $length = substr($serialize, $pos, $last - $pos);
                    $last += 4 + $length;
                    $pos = $last;
                }
                $serialize = substr($serialize, 0, $last) . preg_replace_callback(
                        $re,
                        $replace,
                        substr($serialize, $last)
                    );
                $o = unserialize($serialize);
            }
        }
        return $o;
    }
}
