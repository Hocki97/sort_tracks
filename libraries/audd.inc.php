<?php
function audd_api_request ($api_paramters)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://api.audd.io/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_paramters);
    $response_json = curl_exec($ch);
    curl_close($ch);

    $response_raw = json_decode ($response_json, true);

    return $response_raw;
}

function audd_track_search ($api_token, $file_path)
{
    $api_paramters = [];
    $api_paramters['api_token'] = $api_token;
    $api_paramters['file']      = curl_file_create ($file_path, 'application/octet-stream', 'file');
    $api_paramters['return']    = 'apple_music,spotify';

    $response = audd_api_request ($api_paramters);
    if (empty ($response['result']['title']))
        return null;
    
    $track_information = [];
    $track_information['title']        = $response['result']['title'];
    $track_information['artist']       = $response['result']['artist'];
    $track_information['album']        = $response['result']['album'];
    $track_information['release_date'] = $response['result']['release_date'];
    $track_information['duration']     = $response['result']['timecode'];

    // add spotify_id if available TODO: could be used with spotify api to get other information?
    if (!empty ($response['result']['spotify']['id']))
        $track_information['spotify_id'] = $response['result']['spotify']['id'];

    // add apple_music genre if available
    if (!empty ($response['result']['apple_music']['genreNames']))
        $track_information['genre'] = $response['result']['apple_music']['genreNames'][0];

    return $track_information;
}
?>