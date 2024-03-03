<?php
include_once '../private/dbconf.php';
$pages = 100;
$url_str = 'https://api.themoviedb.org/3/discover/tv?include_adult=true&include_null_first_air_dates=false&language=en-US&sort_by=popularity.desc&vote_count.gte=300&api_key=' . key;
$temp_str = '';
$page_array = array();
$img_url = 'https://www.themoviedb.org/t/p/original';
// loop through url pages since can't get multiple at once
for ($i = 1; $i <= $pages; $i++) {
    // need to sleep to avoid going over 50 API request/sec limit
    if ($i % 45 == 0) {
        sleep(2);
    }
    $temp_str = $url_str . '&page=' . $i;

    //CURL REQUEST START
    $cin = curl_init();
    curl_setopt($cin, CURLOPT_URL, $temp_str);
    //curl_setopt($cin, CURLOPT_HTTPHEADER, $header);
    curl_setopt($cin, CURLOPT_TIMEOUT, 30);
    curl_setopt($cin, CURLOPT_RETURNTRANSFER, true);
    $rstr = curl_exec($cin);
    curl_close($cin);
    //CURL REQUEST END

    $api_data = json_decode($rstr, true);

    // checks if results subarray is empty and breaks if it is
    if (!$api_data['results']) {
        break;
    }
    array_push($page_array, $api_data);
}

foreach ($page_array as $page) {
    echo '<pre>';
    print_r($page);
    $temp_url = $img_url . $page['results'][0]['poster_path'];
    echo '<img src=' . $temp_url . ' width="333" height="500" alt=""/>';
    echo '</pre>';
}


