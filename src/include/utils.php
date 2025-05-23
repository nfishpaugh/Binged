<?php
include "config.inc";

/** Finds the position of the $num-th occurrence of a substring in a string, e.g. $hay = "Hello!!", $needle = "!", $num = 2 -> returns 6
 * @param string $hay - The string to be searched in
 * @param string $needle - The substring to find in $hay
 * @param int $num - The n-th occurrence that you wish to find
 */
function strposX(string $hay, string $needle, int $num): bool|int
{
    if ($num == 1) {
        return strpos($hay, $needle);
    } elseif ($num > 1) {
        return strpos($hay, $needle, strposX($hay, $needle, $num - 1) + strlen($needle));
    } else {
        return error_log("Error: Value for $num is out of bounds");
    }
}

/** Translates a numeric review value into a score out of 5 stars, e.g. 3 -> ★★★☆☆
 * @param int $value - The integer (between 1 and 5) to translate into stars
 * @return string
 */
function starify(int $value): string
{
    return match ($value) {
        1 => "★",
        2 => "★★",
        3 => "★★★",
        4 => "★★★★",
        5 => "★★★★★",
        default => "No Rating",
    };
}

/** Sends param data to browser console
 * @param float|int|array|string|null $data - The data to be sent to the console
 * @return void
 */
function debug_to_console(float|int|array|string|null $data): void
{
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

/** Returns the weighted total of a star column array, e.g if there are 5 1 star reviews and 10 3 star reviews,
 * total = (5 * 1) + (10 * 3). Based on the names of the array keys, so beware of changing the column names in the DB
 * @param array $arr - The array of the show to retrieve the weighted total from
 * @return int
 */
function weighted_amt(array $arr): int
{
    if (!is_numeric(substr(array_key_first($arr), 0, 1))) trigger_error("Array key does not start with a numeric value. Key: " . array_key_first($arr));

    $total = 0.0;
    foreach ($arr as $key => $val) {
        // Get first character of the key to determine the multiplier, e.g. '5_stars' -> mult = 5.0
        $mult = (int)(substr($key, 0, 1));
        $total += ($mult * $val);
    }
    debug_to_console("Total: " . $total);
    return $total;
}

/** Calculates and updates the average for a show based on params
 * @param int $show_id - ID of the show to update
 * @param int $rating - The user's rating that needs to be added to the average (1-5)
 * @param int $review_count - The total amount of reviews (not including the newly added review) of the show
 * @param mysqli_class $mysqli - The MySqli object, needed to call mysqli methods
 * @param bool $remove - Optional boolean, true = remove a value from the average, false (default) = add a value to the average
 * @param bool $edit - Optional boolean, true = do not add to the review count, false (default) = add to the review count
 * @param int|null $old_rating - Optional int, used if a review is updated with a new rating
 * @return void
 */
function update_avg(int $show_id, int $rating, int $review_count, mysqli_class $mysqli, bool $remove = false, bool $edit = false, int $old_rating = null): void
{
    // only change review count if a new review has been inserted
    if (!$edit) {
        // subtract/add one from/to related star column
        $mysqli->star_update($show_id, $rating, $remove);

        $remove ? $review_count-- : $review_count++;
        $mysqli->update_show_column($show_id, $review_count, "review_amt");
    } else {
        // remove old rating, add new one
        $mysqli->star_update($show_id, $rating, false, true, $old_rating);
    }

    // place all star columns in an array
    $star_arr = $mysqli->get_star_cols($show_id);

    // re-sum all star columns - if 0/null, default to 1
    $star_sum = array_sum($star_arr[0]) ?: 1.0;

    $weighted = weighted_amt($star_arr[0]);
    $avg = (float)$weighted / $star_sum;
    debug_to_console("Average: " . $avg);

    $mysqli->update_show_column($show_id, $avg, "review_avg", "d");
    $_SESSION[$show_id . "_avg"] = $avg;
}

/** Heredoc HTML template for review tabs
 * @param array $review - Review data array
 * @param array $user_pf - User profile array
 * @param int $i - Iteration variable, needed for array indexing in main doc
 * @param array $user_info - User info array
 * @param int $in_id - Show id (int)
 * @param string $r_str - Review content string
 * @param boolean $is_user - Boolean that determines if the current user is allowed to see the edit/delete buttons for the review
 * @return string
 */
function review_template(array $review, array $user_pf, int $i, array $user_info, int $in_id, string $r_str, bool $is_user): string
{
    $rev_id = $review['review_id'];
    $str_len = 75;
    $rev_content = substr($review['review_content'], 0, $str_len);
    if (strlen($rev_content) >= $str_len) {
        $rev_content = $rev_content . '...';
    }

    $pfp = $user_pf["profile_pic_src"] ?? "dummy_pfp.jpg";
    $user_name = $user_info['user_name'];
    $user_id = $user_info['user_id'];
    $buttonstr = $i . '-' . $review['review_id'] . '-' . $user_info['user_id'];

    // Need an anon function as an alternative to if conditionals inside HEREDOC templates
    $hereif = function ($condition, $true, $false) {
        return $condition ? $true : $false;
    };

    $set = <<<TEMPLATE
    <form action="" method="POST">
        <input type="hidden" name="modify" id="modify-field-hidden" value="1">
        <button class="d-inline btn btn-primary" type="submit"
            name="edit$buttonstr">
            Edit
        </button>
        <button class="d-inline btn btn-secondary" type="button"
            id="modal_open_alert$i"
            name="delete$i-$rev_id-$user_id"
            data-bs-toggle="modal"
            data-bs-target="#alert-modal">
          Delete
        </button>
    </form>
    TEMPLATE;

    $empty = <<<TEMPLATE
    
    TEMPLATE;

    return <<<TEMPLATE
    <p>
        <b><a class="one" style="color: #282f3a"
        href="review_page.php?rid=$rev_id&sid=$in_id&uid=$user_id">
        <img src="images/faces/$pfp"
            style="width: 50px; height: 50px; border-radius: 100%;"/>
        <span>$user_name's review</span>
        <span style="color: #0072ff">$r_str</span></a></b>
    </p>
    <p>$rev_content</p>
        {$hereif($is_user, $set, $empty)}
    <p style="padding-bottom:10px; border-bottom: 2px solid grey;"></p>
    TEMPLATE;
}

/** Heredoc template for pagination buttons
 * @param int $page - The current page number
 * @param int $num_pages - The total number of pages
 * @param int $in_id - Optional, ID of the show, only used for the review template
 * @param int $uid - Optional, user ID, only used for user-review template
 * @param string $type - Determines the template string returned:
 *
 *- review - returns the review pagination template
 *
 *- search - returns the search pagination template
 *
 *- user-review - returns the user-review pagination template
 *
 *- genre - returns the genre pagination template
 *
 *- none - default pagination template with inactive hrefs
 * @param string $searchstr - The string used if type = search
 * @param string $genre - The string used if type = genre
 * @return string
 */
function pagination_template(int $page, int $num_pages, int $in_id = 0, int $uid = 0, string $type = "none", string $searchstr = "", string $genre = ""): string
{
    if ($page > $num_pages) $page = $num_pages;
    elseif ($page < 1) $page = 1;

    $prev = $page - 1;
    $next = $page + 1;

    $url = match ($type) {
        "review" => array("show_page.php?id=" . $in_id . "&page=", "&all=1"),
        "search" => array("show_search.php?searchbar=" . $searchstr . "&sub=Submit+Query&page=", ""),
        "user-review" => array("user_reviews.php?id=" . $uid . "&page=", ""),
        "genre" => array("genre_page.php?genre=" . $genre . "&page=", ""),
        default => array("#", "")
    };

    if ($page > 1) {
        $previous = <<<TEMPLATE
            <li class="page-item">
                <a class="page-link" href="$url[0]$prev$url[1]">Previous</a>
            </li>
            TEMPLATE;
    } else {
        $previous = <<<TEMPLATE
            <li class="page-item disabled">
              <a class="page-link">Previous</a>
            </li>
            TEMPLATE;
    }

    if ($page === $num_pages) {
        $next_page = <<<TEMPLATE
                <li class="page-item disabled">
                  <a class="page-link">Next</a>
                </li>
            TEMPLATE;
    } else {
        $next_page = <<<TEMPLATE
                <li class="page-item">
                  <a class="page-link" href="$url[0]$next$url[1]">Next</a>
                </li>
            TEMPLATE;
    }

    $pages = <<<TEMPLATE
        
        TEMPLATE;

    if ($num_pages < 10) {
        $i = 1;
        while ($i <= $num_pages) {
            if ($i === $page) {
                $pages = $pages . <<<TEMPLATE
                <li class="page-item disabled"><a class="page-link" href="$url[0]$i$url[1]">$i</a></li>
                TEMPLATE;
            } else {
                $pages = $pages . <<<TEMPLATE
                <li class="page-item"><a class="page-link" href="$url[0]$i$url[1]">$i</a></li>
                TEMPLATE;
            }
            $i++;
        }

        // pagination template
        return <<<TEMPLATE
        <nav aria-label="Review pagination">
          <ul class="pagination justify-content-center">
            {$previous}
            {$pages}
            {$next_page}
          </ul>
        </nav>
        TEMPLATE;
    } else {
        if ($page === $num_pages) {
            $last = <<<TEMPLATE
            <li class="page-item disabled"><a class="page-link">Last</a></li>
            TEMPLATE;
        } else {
            $last = <<<TEMPLATE
            <li class="page-item"><a class="page-link" href="$url[0]$num_pages$url[1]">Last</a></li>
            TEMPLATE;
        }

        if ($page === 1) {
            $first = <<<TEMPLATE
            <li class="page-item disabled"><a class="page-link">First</a></li>
            TEMPLATE;
        } else {
            $first = <<<TEMPLATE
            <li class="page-item"><a class="page-link" href="$url[0]1$url[1]">First</a></li>
            TEMPLATE;
        }

        $btn_amt = 10;
        $midpoint = $btn_amt / 2;

        if ($page <= $midpoint) {
            $page_start = 1;
        } elseif ($page >= $num_pages - $midpoint) {
            $page_start = $num_pages - $btn_amt;
        } else {
            $page_start = $page - $midpoint;
        }

        $page_end = $page_start + $btn_amt;

        for ($i = $page_start; $i <= $page_end; $i++) {
            if ($i === $page) {
                $pages = $pages . <<<TEMPLATE
            <li class="page-item disabled"><a class="page-link" href="$url[0]$i$url[1]">$i</a></li>
            TEMPLATE;
            } else {
                $pages = $pages . <<<TEMPLATE
            <li class="page-item"><a class="page-link" href="$url[0]$i$url[1]">$i</a></li>
            TEMPLATE;
            }
        }

        // pagination template
        return <<<TEMPLATE
        <nav aria-label="Review pagination">
          <ul class="pagination justify-content-center">
            {$first}
            {$previous}
            {$pages}
            {$next_page}
            {$last}
          </ul>
        </nav>
        TEMPLATE;
    }
}