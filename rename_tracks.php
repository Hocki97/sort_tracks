<?php
require_once (__DIR__.'/libraries/dir.inc.php');
require_once (__DIR__.'/libraries/audd.inc.php');

//define dir and api token
define ('PATH', 'C:\Users\tobia\Music\B2B\V1\\');
define ('AUDD_API_TOKEN', 'xx'); 

// get all tracks 
$result = dir_list_all_tracks (PATH);

// search for track with audd and rename if track has beend found
foreach ($result as $track)
{
    $file_path = PATH.$track;
    $response = audd_track_search (AUDD_API_TOKEN, $file_path);

    if (empty ($response['title']) ||
        empty ($response['artist']))
        continue;
    
    /*preg_match ('/\\W\\w{3,4}$/', $track, $parts);

    if (empty ($parts[1]))
        continue;
        
    $file_path_format = $parts[1];*/ //FIXME: search for ending to get format
    $file_path_format ='.mp3';
    
    $file_path_rename = PATH.$response['artist'].'-'.$response['title'].$file_path_format;
    rename ($file_path, $file_path_rename);
}

?>