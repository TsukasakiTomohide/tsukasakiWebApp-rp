<?php

    // File name to be downloaded
    $filepath = 'test.csv';
      
    // File name after $filepath is downloaded
    $filename = 'test.csv';
      
    // File type
    header('Content-Type: application/force-download');
      
    // Get the file size to show the progress of download
    header('Content-Length: '.filesize($filepath));
      
    // Request download and rename
    header('Content-Disposition: attachment; filename="'.$filename.'"');
      
    // Read the file and start download
    readfile($filepath);

?>