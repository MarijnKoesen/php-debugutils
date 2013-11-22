<?php
namespace MarijnKoesen\DebugUtils;

/**
 * Debug helper functions to make those nasty problems on nasty places better debuggable
 *
 * @author Marijn Koesen
 */

class DebugUtil
{
    public static function registerFunctions()
    {
        eval('function dump($input, $return=false) { return \MarijnKoesen\DebugUtils\Dump::dump($input, $return); }');
    }
}

