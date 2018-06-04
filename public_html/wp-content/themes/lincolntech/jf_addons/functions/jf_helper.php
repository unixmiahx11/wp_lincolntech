<?php
/**
 * Check if the environment is development or not
 * @author Michael Brose <michael.brose@jellyfish.net>
 * @return boolean
 */
function is_development() {
    if (isset($_SERVER['SERVER_NAME']) &&
        (fnmatch('*.dev.jellyfish.*', $_SERVER['SERVER_NAME']) ||
        fnmatch('*.dev.*.jellyfish.*', $_SERVER['SERVER_NAME'])
        ))
    {
        return true;
    }
    return false;
}
/**
 * Check if the debug request variable exists and is set to 1, true, or enable
 * only if the current machine is development
 * @author Michael Brose <michael.brose@jellyfish.net>
 * @return boolean
 */
function is_debug() {
    if (is_development() && !empty($_REQUEST['debug']) &&
        ($_REQUEST['debug'] == '1' ||
         $_REQUEST['debug'] == 'true' ||
         $_REQUEST['debug'] == 'enable'
        )
    ) {
        return true;
    }
    return false;
}