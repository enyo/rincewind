<?php

function snap_encrypt($string, $key) {
    $string = ($string != SNAP_WI_TEST_PATH) ? preg_replace('#'.SNAP_WI_TEST_PATH.'#', '', $string, 1) : '??? [HIDDEN]';
    return $string;
}

function snap_decrypt($string, $key) {
    return SNAP_WI_TEST_PATH . $string;
}
