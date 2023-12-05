<?php
include "include/config.inc";

$_SESSION[PREFIX . "_ppage"] = $_SERVER['REQUEST_URI'];
if ($_SESSION[PREFIX . '_username'] == "") {
    header("Location: login.php");
    exit;
}

$in_id = (int)$_GET['id'];
if (!$in_id) {
    header("location: show_list.php");
    exit;
}
$show_info = $mysqli->show_info($in_id);

$page_name = $show_info['show_name'];
$read = 'eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiYjgxNTZhZTA2YTM5NWVkODlmZmViODY2Y2I2MjE0NCIsInN1YiI6IjY1NjE0N2Q1NDk3NTYwMDExZGIxMjAzNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.v9LT-PZDYY4uhAH-ojAG79SLI1BbBP_gIYBkfHwAGRM';
$key = 'bb8156ae06a395ed89ffeb866cb62144';
$url_str = urlencode($page_name);
$url = 'https://api.themoviedb.org/3/search/tv?query=' . $url_str . '&include_adult=true&language=en-US&page=1&api_key=' . $key;
$img_url = 'https://www.themoviedb.org/t/p/w600_and_h900_bestv2';
$cin = curl_init();
/*
$header = array(
    'Authorization' => 'Bearer ' . $read,
    'accept' => 'application/json'
);
*/
curl_setopt($cin, CURLOPT_URL, $url);
//curl_setopt($cin, CURLOPT_HTTPHEADER, $header);
curl_setopt($cin, CURLOPT_TIMEOUT, 30);
curl_setopt($cin, CURLOPT_RETURNTRANSFER, true);
$rstr = curl_exec($cin);

$api_data = json_decode($rstr, 1);

$img_url = $img_url . $api_data['results'][0]['poster_path'];

/*
$response = $client->request('GET', $url, [
    'headers' => [
        'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiYjgxNTZhZTA2YTM5NWVkODlmZmViODY2Y2I2MjE0NCIsInN1YiI6IjY1NjE0N2Q1NDk3NTYwMDExZGIxMjAzNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.v9LT-PZDYY4uhAH-ojAG79SLI1BbBP_gIYBkfHwAGRM',
        'accept' => 'application/json',
    ],
]);
*/

//print_r($response->getBody());
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $app_name; ?> - <?php echo $page_name; ?></title>
    <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="vendors/base/vendor.bundle.base.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="images/favicon.png"/>
</head>
<body>
<div class="container-scroller">

    <?php require_once 'partials/_navbar.php'; ?>
    <div class="container-fluid page-body-wrapper">
        <?php require_once 'partials/_sidebar.php'; ?>
        <div class="main-panel">
            <div class="content-wrapper">

                <div class="row">
                    <div class="col-md-12 grid-margin">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div class="d-flex align-items-end flex-wrap">
                                <div class="me-md-3 me-xl-5">
                                    <div style="float:left">
                                        <img src="<?php echo $img_url ?>" width="333" height="500"
                                             style="margin-right:50px" alt="images/qmark.jpg"/>
                                    </div>
                                    <div style="margin:50px"
                                    <h2 style="horiz-align: right"><?php echo $page_name; ?></h2>
                                    <p style="horiz-align: right"><?php echo $show_info['description']; ?></p>
                                    <p>Year released: <?php echo $show_info['year']; ?></p>
                                    <p>Runtime: <?php
                                        if (!is_null($show_info['runtime'])) {
                                            echo $show_info['runtime'];
                                        } else {
                                            echo "Not Available";
                                        } ?></p>
                                    <p>Votes: <?php echo $show_info['votes'] ?></p>
                                    <!--<p>Path: <?php echo "<pre>" . print_r($api_data) . "</pre>" ?></pre></p>
                                    <p>Poster: </p><?php echo $api_data['results'][0]['poster_path']; ?></p>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>


