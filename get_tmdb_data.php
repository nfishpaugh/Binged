<?php
$read = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiYjgxNTZhZTA2YTM5NWVkODlmZmViODY2Y2I2MjE0NCIsInN1YiI6IjY1NjE0N2Q1NDk3NTYwMDExZGIxMjAzNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.v9LT-PZDYY4uhAH-ojAG79SLI1BbBP_gIYBkfHwAGRM';
$key = 'bb8156ae06a395ed89ffeb866cb62144';
$pages = 100;
$url_str = 'https://api.themoviedb.org/3/discover/tv?include_adult=true&include_null_first_air_dates=false&language=en-US&sort_by=popularity.desc&vote_count.gte=300&api_key=' . $key;
$temp_str = '';
$page_array = array();
$img_url = 'https://www.themoviedb.org/t/p/original';

// loop through url pages since can't get multiple at once - need to sleep to avoid going over API limit/sec
for ($i = 1; $i <= $pages; $i++) {
    if ($i % 40 == 0) {
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
    if (!$api_data['results']) {
        break;
    }
    array_push($page_array, $api_data);
}

foreach ($page_array as $page) {
    echo '<pre>';
    print_r($page);
    $img_url = $img_url . $page['results'][0]['poster_path'];
    //TODO
    echo '</pre>';
}


