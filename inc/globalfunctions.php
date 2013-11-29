<?php
function dump($input, $return=false) { 
    return \MarijnKoesen\DebugUtils\Dump::dump($input, $return); 
}

function printstacktrace($return=false) {
    return \MarijnKoesen\DebugUtils\StackTrace::get($return);
}
