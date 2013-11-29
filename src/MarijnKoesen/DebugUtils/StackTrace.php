<?php
namespace MarijnKoesen\DebugUtils;

class StackTrace
{
    /**
     * Get a stacktrace in a HTML table, it also outputs javascript that enables integration with phpstorm via the
     * "Remote call" plugin.
     * This means that you can click on the files in the table to open them in phpstorm.
     *
     * @param bool $returnHTML (default false). true: return's the HTML, false: prints the HTML, returns print's return.
     * @return int|string
     */
    public static function get($returnHTML = false)
    {
        $callstack = debug_backtrace();

        $html = "
                <script language=\"Javascript\" type=\"text/javascript\">
                <!--
                openfileInPhpStorm = function (file, line)
                {
                    try {
                        var xhr = window.XMLHttpRequest
                            ? new XMLHttpRequest()
                            : (window.ActiveXObject
                                ? new ActiveXObject('Microsoft.XMLHTTP')
                                : null);

                        if (!xhr)
                            return false;

                        xhr.open('GET', 'http://localhost:8091/?message=' + file + ':' + line);
                        xhr.onreadystatechange = function () {};
                        xhr.send(null);
                    }
                    catch (e) {}
                    return false;
                }
                -->
                </script>
                <pre>
                <p>
                    <table border='1'>
                    <tr>
                        <th>file</th>
                        <th>line</th>
                        <th>object</th>
                        <th>class::function</th>
                    </tr>
        ";

        foreach ($callstack as $item) {
            $item['file']  = isset($item['file']) ? str_replace('/vagrant/', '', $item['file']) : '[PHP Kernel]';
            $className     = isset($item["class"]) ? $item["class"] : '*global*';
            $objectpart    = !empty($item["object"]) ? get_class($item["object"]) : '';
            $link          = sprintf("openfileInPhpStorm('%s', '%s')", $item['file'], $item['line']);
            $item['file'] .= isset($item['line']) && !empty($item["line"]) ? ':' . $item["line"] : '';

            $html .= "
                    <tr>
                        <td><a href=\"javascript: void(0);\" onclick=\"{$link}\">{$item["file"]}</a></td>
                        <td>{$item["line"]}</td>
                        <td>{$objectpart}</td>
                        <td>{$className}::{$item["function"]}</td>
                    </tr>
            ";
        }
        $html .= "
                    </table>
                    <hr />
                </p>
                </pre>
        ";

        return $returnHTML ? $html : print($html);
    }
}
