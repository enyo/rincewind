<?php

// getfiles module. gets a list of files and returns them

// generate list of files to test
if (is_dir(SNAP_WI_TEST_PATH)) {
    $file_list = SNAP_recurse_directory(SNAP_WI_TEST_PATH, SNAP_WI_TEST_MATCH);
}
else {
    $file_list = array(SNAP_WI_TEST_PATH);
}

if (SNAP_WI_CRYPT) {
    foreach ($file_list as $idx => $file) {
        $file_list[$idx] = snap_encrypt($file, SNAP_WI_CRYPT);
    }
}

echo json_encode($file_list);

exit;
