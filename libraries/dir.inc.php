<?php
function dir_list_all_tracks ($dir) 
{
    $result = array_diff (scandir($dir), array ('.', '..'));
   
    foreach ($result as $key => $item) 
    {   
        if (is_dir ($dir.$item))
        {   unset ($result[$key]);
            //$result = array_merge ($result, dir_list_all_tracks ($dir.$item)); TODO: add function with sub folders
        }
    }

    foreach ($result as $key => $item)
    {
        if ((!strstr ($item, 'mp3')) &&
            (!strstr ($item, 'aiff')) &&
            (!strstr ($item, 'wav')))
            unset ($result[$key]);
    }
    return $result;
}
?>