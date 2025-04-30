<?php

/** Finds the position of the $num-th occurrence of a substring in a string */
function strposX($hay, $needle, $num): bool|int
{
    if ($num == 1) {
        return strpos($hay, $needle);
    } elseif ($num > 1) {
        return strpos($hay, $needle, strposX($hay, $needle, $num - 1) + strlen($needle));
    } else {
        return error_log("Error: Value for $num is out of bounds");
    }
}

/** Translates a numeric review value into a score out of 5 stars, e.g. 3 -> ★★★☆☆ */
function starify($value): string
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

/** Sends param data to browser console */
function debug_to_console($data): void
{
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

/** Returns the weighted total of a star column array, e.g if there are 5 1 star reviews and 10 3 star reviews,
 * total = (5 * 1) + (10 * 3). Based on the names of the array keys, so beware of changing the column names in the DB
 */
function weighted_amt($arr): int
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
 ** remove - Optional boolean, true = remove a value from the average, false (default) = add a value to the average
 ** edit - Optional boolean, true = do not add to the review count, false (default) = add to the review count
 */
function update_avg($show_id, $rating, $review_count, $mysqli, $remove = false, $edit = false, $old_rating = null): void
{
    // only change review count if a new review has been inserted
    if (!$edit) {
        // subtract/add one from/to related star column
        $mysqli->star_update($show_id, $rating, $remove);

        $remove ? $review_count-- : $review_count++;
        $mysqli->update_show_column($show_id, $review_count, "review_amt");
    } else {
        // subtract from
        $mysqli->star_update($show_id, $rating);
        $mysqli->star_update($show_id, $old_rating, true);
    }

    // place all star columns in an array
    $star_arr = $mysqli->get_star_cols($show_id);

    // re-sum all star columns - if 0/null, default to 1
    $star_sum = array_sum($star_arr[0]) ?: 1.0;

    $weighted = weighted_amt($star_arr[0]);
    $avg = (float)$weighted / $star_sum;
    debug_to_console("Average: " . $avg);

    $mysqli->update_show_column($show_id, $avg, "review_avg", "d");
}