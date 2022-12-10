<?php
class tracks 
{
    private $tracks;

    function __construct ()
    {   $this->tracks = [];
    }

    function track_add ($track_id)
    {
        $file_name = explode ('-', $track_id);
        if (empty ($file_name[1]))
            return null;
    
        $track_data = [];
        $track_data['artist'] = trim ($file_name[0]);
        $track_data['name']   = preg_replace ('/\W(.){3,4}$/', '', trim ($file_name[1]));
        $track_data['file_name'] = $track_id;

        $track = new track ($track_data['name'], $track_data['artist'], $track_data['file_name']);
        $this->tracks[] = $track;

        return $track;
    }

    function tracks_get ()
    {   return $this->tracks;
    }

    function tracks_sort_by_bpm ()
    {
        $path = PATH.'Sortierte Tracks\ Nach BPM';

        foreach ($this->tracks as $track)
        {
            if (!is_dir ($path.'\\'.$track->track_bpm_get ()))
                mkdir ($path.'\\'.$track->track_bpm_get ());

            $file_name_old = PATH.$track->track_file_name_get ();
            $file_name_new = $path.'\\'.$track->track_bpm_get ().'\\'.$track->track_file_name_get ();

            link ($file_name_old, $file_name_new);
        }
    }

    function tracks_sort_by_genre ()
    {
        $path = PATH.'Sortierte Tracks\ Nach Genre';

        foreach ($this->tracks as $track)
        {
            if (!is_dir ($path.'\\'.$track->track_genre_get ()))
                mkdir ($path.'\\'.$track->track_genre_get ());

            $file_name_old = PATH.$track->track_file_name_get ();
            $file_name_new = $path.'\\'.$track->track_genre_get ().'\\'.$track->track_file_name_get ();

            link ($file_name_old, $file_name_new);
        }
    }

    function tracks_sort_by_key ()
    {
        $path = PATH.'Sortierte Tracks\ Nach Key';

        foreach ($this->tracks as $track)
        {
            if (!is_dir ($path.'\\'.$track->track_key_get ()))
                mkdir ($path.'\\'.$track->track_key_get ());

            $file_name_old = PATH.$track->track_file_name_get ();
            $file_name_new = $path.'\\'.$track->track_key_get ().'\\'.$track->track_file_name_get ();

            link ($file_name_old, $file_name_new);
        }
    }
}

class track
{
    private $track_name;
    private $track_artist;
    private $track_file_name;
    private $track_length;
    private $track_date;
    private $track_bpm;
    private $track_key;
    private $track_genre;

    function __construct ($track_name, $track_artist, $track_file_name)
    {   
        $this->track_name      = $track_name;
        $this->track_artist    = $track_artist;
        $this->track_file_name = $track_file_name;
        $this->track_length    = null;
        $this->track_date      = null;
        $this->track_bpm       = null;
        $this->track_key       = null;
        $this->track_genre     = null;
    }

    function track_information_add ($track_length, $track_date, $track_bpm, $track_key, $track_genre)
    {   
        $this->track_length = $track_length;
        $this->track_date   = $track_date;
        $this->track_bpm    = $track_bpm;
        $this->track_key    = $track_key;
        $this->track_genre  = $str = str_replace ('/', '-', $track_genre);
    }

    function track_name_get ()
    {   return $this->track_name;
    }

    function track_artist_get ()
    {   return $this->track_artist;
    }

    function track_file_name_get ()
    {   return $this->track_file_name;
    }

    function track_length_get ()
    {   return $this->track_length;
    }

    function track_date_get ()
    {   return $this->track_date;
    }

    function track_bpm_get ()
    {   return $this->track_bpm;
    }

    function track_key_get ()
    {   return $this->track_key;
    }

    function track_genre_get ()
    {   return $this->track_genre;
    }
}

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


//define path
define ('PATH', 'C:\Users\tobia\Music\Test\\');

//load all tracks of one folder
$tracks = new tracks;

$dir = dir_list_all_tracks (PATH); 
if (empty ($dir))
    exit;

foreach ($dir as $track_id)
    $tracks->track_add ($track_id);

// parse beatport and get all information about the tracks
foreach ($tracks->tracks_get () as $track)
{
    $track_url = beatport_track_url_get ($track->track_artist_get (), $track->track_name_get ());
    if (empty ($track_url))
        continue;

    $track_information = beatport_track_information_get ($track_url);
    if (empty ($track_information))
        continue;

    $track->track_information_add ($track_information['length'], $track_information['date'], 
                                   $track_information['bpm'], $track_information['key'], 
                                   $track_information['genre']);
}

print_r ($tracks);

//build folder structure
if (!is_dir (PATH.'Sortierte Tracks'))
    mkdir (PATH.'Sortierte Tracks');

// for bpm
if (!is_dir (PATH.'Sortierte Tracks\ Nach BPM'))
    mkdir (PATH.'Sortierte Tracks\ Nach BPM');

// for genre
if (!is_dir (PATH.'Sortierte Tracks\ Nach Genre'))
    mkdir (PATH.'Sortierte Tracks\ Nach Genre');

// for key
if (!is_dir (PATH.'Sortierte Tracks\ Nach Key'))
    mkdir (PATH.'Sortierte Tracks\ Nach Key');


// execute sort functions
$tracks->tracks_sort_by_bpm ();
$tracks->tracks_sort_by_genre ();
$tracks->tracks_sort_by_key ();


?>
