<?php
include "include/config.inc";

$results = $mysqli->show_list();

foreach ($results as $result) {

    $genre_array = explode(" ", trim($result['genres']));

    foreach ($genre_array as $genre) {
        //TODO - ADD SHOWS_GENRE_INSERT
        $mysqli->shows_genre_insert($result['id'], $genre);
    }
}