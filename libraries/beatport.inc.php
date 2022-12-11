<?php
function beatport_data_ec_information_get ($row)
{
    preg_match ('/"(.+)"/', $row, $parts);

    if (empty ($parts[1]))
            return null;
    else    return $parts[1];
}


function beatport_track_url_get ($track_artist, $track_name)
{
    $query = urlencode ($track_artist).'+'.urlencode ($track_name);

    $url = fopen ('https://www.beatport.com/search?q='.$query, 'r');

    $html = [];
    while (!feof($url)) 
       $html[] = fgets($url, 4096);

    fclose ($url);

    $result = [];
    foreach ($html as $id => $tmp)
    {
        if (strstr ($tmp, 'class="bucket-item ec-item track"'))
        {
            $result['name']   = strtoupper (beatport_data_ec_information_get ($html[$id + 2]));//FIXME: find more flexible solution
            $result['artist'] = strtoupper (beatport_data_ec_information_get ($html[$id + 11]));

            if (strstr ($result['name'], strtoupper ($track_name)) &&
                strstr ($result['artist'], strtoupper ($track_artist)))
                {   $result['url'] = beatport_data_ec_information_get ($html[$id + 21]);
                    break;
                }
        }
    }

    if (empty ($result['url']))
            return null;
    else    return $result['url'];
}

function beatport_track_information_get ($track_url)
{
    $url = fopen ('https://www.beatport.com'.$track_url, 'r');

    $html = [];
    while (!feof($url)) 
        $html[] = fgets($url, 4096);

    $result = [];
    foreach ($html as $id => $tmp)
    {
        if (strstr ($tmp, 'class="interior-track-content-list"'))
        {
            $result['length'] = trim (strip_tags ($html[$id + 3])); //FIXME: find more flexible solution
            $result['date']   = trim (strip_tags ($html[$id + 7]));
            $result['bpm']    = trim (strip_tags ($html[$id + 11]));
            $result['key']    = trim (strip_tags ($html[$id + 15]));
            $result['genre']  = trim (strip_tags ($html[$id + 22]));
        }
    }

    if (empty ($result))
            return null;
    else    return $result;
}
?>