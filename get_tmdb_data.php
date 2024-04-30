<?php
require '../private/dbconf.php';
include 'include/config.inc';

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
    for ($i = 0; $i < 20; $i++) {
        // variables for show_insert
        /*
        $poster = $page['results'][$i]['poster_path'];
        $orig_lang = $page['results'][$i]['original_language'];
        */
        //$over = $page['results'][$i]['overview'];
        $back_path = $page['results'][$i]['backdrop_path'];
        $api_id = $page['results'][$i]['id'];
        $mysqli->show_update_back($api_id, $back_path);
        /*
        $pop = $page['results'][$i]['popularity'];
        $date = $page['results'][$i]['first_air_date'];
        $name = $page['results'][$i]['name'];
        $vote_avg = $page['results'][$i]['vote_average'];
        $votes = $page['results'][$i]['vote_count'];

        $mysqli->show_insert($api_id, $name, $orig_lang, $over, $vote_avg, $votes, $poster, $date, $orig_lang, $pop);
        */
        // variables for genre_insert
        /*
        $genres = $page['results'][$i]['genre_ids'];
        $page_num = $page['page'];
        $show_id = (($page_num - 1) * 20) + $i + 1;

        // need to loop since genre_ids is an array
        foreach ($genres as $genre) {
            $mysqli->show_genre_insert($show_id, $genre);
        }
        */
    }
}


