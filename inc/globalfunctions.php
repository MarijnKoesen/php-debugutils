<?php
function dump($input, $return=false) { 
    if ( ! $return) {
       krumo($input);
       return;
    }

    ob_start();
    krumo($input); 
    return ob_get_clean();
}

function printstacktrace($return=false) {
    return \MarijnKoesen\DebugUtils\StackTrace::get($return);
}
