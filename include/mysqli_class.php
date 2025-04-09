<?php

/*********************************************************************
 * /*## Portal class extends mysqli */
class mysqli_class extends mysqli
{
    public function __construct()
    {
        require "../private/dbconf.php";
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        @parent::__construct(DBHost, DBUser, DBPass, DBName, DBPort);
        // check if connect errno is set

        //IF THE CONNECTION DOES NOT WORK - REDIRECT TO OUR "DB DOWN" PAGE, BUT PASS THE URL TO THE APPLICATION
        if (mysqli_connect_error()) {
            trigger_error(mysqli_connect_error(), E_USER_WARNING);
            echo mysqli_connect_error();
            exit;
        }
    }

    /**** LOGIN ******************************************************************
     * /*## Checks login credentials */
    public function login($email, $password)
    {

        $query = "SELECT * FROM users WHERE email = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $x[$key] = $val;
            }
            $stmt->close();

            if ($x['email'] == $email && password_verify($password, $x['user_password'])) {
                try {
                    $this->logins_insert($x['user_id']);
                    return array(1, $x);
                } catch (mysqli_sql_exception $e) {
                    $this->login_remove($x['user_id']);
                    $this->logins_insert($x['user_id']);
                    return array(1, $x);
                }
            } else {
                return array(0, $x);
            }
            //return password_verify($password, $x['user_password']);

        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    /*** LOG LOGINS ******************************************************************
     * /*## Logs user logins  */
    public
    function logins_insert($user_id)
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0];
        $refer = $_SERVER['HTTP_REFERER'];
        $query = "
			INSERT INTO logins 
				(user_id,
				login_ip,
				login_browser)	
			VALUES
				(?,?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("iss", $user_id, $ip, $agent);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $last_id;
    }

    public function login_remove($id)
    {
        $query = "DELETE FROM logins WHERE user_id = ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    //ADD actions logging
    public
    function actions_insert($action, $user_id)
    {
        $page = $_SERVER['REQUEST_URI'];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        $refer = $_SERVER['HTTP_REFERER'];
        $query = "
			INSERT INTO actions 
				(user_id,
				action_desc,
				action_page,
				action_ip,
				action_browser,
				action_refer)	
			VALUES
				(?,?,?,?,?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("ssssss", $user_id,
                $action, $page, $ip, $agent, $refer);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $last_id;
    }

//////////////
//USERS
/////////////

    /*** INFO ******************************************************************
     * /*## Gets info for a row */
    public
    function user_info($user_id)
    {

        $results = array();
        $query = "
			SELECT
				*
			FROM
				users
			WHERE
				user_id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    public function user_pf_info($user_id)
    {
        $results = array();
        $query = "
			SELECT
				*
			FROM
				user_pf_data
			WHERE
				user_id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    public function user_pf_info_lim($user_id, $limit)
    {
        $results = array();
        $query = "
			SELECT
				*
			FROM
				user_pf_data
			WHERE
				user_id = ?
			LIMIT ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("ii", $user_id, $limit);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    /*** USER LIST ******************************************************************
     * /*## List all data */
    public
    function user_list()
    {
        $results = array();
        $query = "
			SELECT
				*
			FROM
				users,
				user_levels
			WHERE users.user_level_id = user_levels.user_level_id
			ORDER BY user_id";
        //echo $query;
        if ($stmt = parent::prepare($query)) {
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    /*** USER ADD  ******************************************************************
     * /*## adds row  data */
    public
    function user_insert($email, $name, $password, $level)
    {
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $query = "
			INSERT INTO users
				(email,
				 user_name,
				 user_password,
				 user_level_id)
			VALUES
				(?,?,?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sssi", $email, $name, $pass_hash, $level);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
                $last_id = 0;
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
            $last_id = 0;
        }

        return $last_id;

    }

    public function user_pf_insert($user_id, $join_date)
    {
        $query = "
            INSERT INTO user_pf_data(user_id, user_description, user_join_date) 
            VALUES (?, ?, ?)
        ";

        if ($stmt = parent::prepare($query)) {
            $desc = "This user has not added a description yet.";
            $stmt->bind_param("iss", $user_id, $desc, $join_date);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    public function user_field_check($field, $column): bool
    {
        $query = "SELECT email FROM users WHERE " . $column . " = ?";
        $results = [];

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $field);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $result = $stmt->num_rows;
            $exists = (bool)$result;
            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return (bool)count($results);
    }

    /*** USER EDIT  ******************************************************************
     * /*## Updates row */
    public
    function user_edit($user_id, $email, $name, $password, $level)
    {
        // if password is given via the function parameter above, hash it,
        // otherwise, get already hashed password from sql table
        if ($password) {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $pass_hash = $this->user_info($user_id)['user_password'];
        }
        $query = "
			UPDATE users SET
				email = ?,
				user_name = ?,
				user_password = ?,
				user_level_id = ?
			WHERE
				user_id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sssii", $email, $name, $pass_hash, $level, $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

    }

    /*** USER REMOVE  ******************************************************************
     * /*## removes row */
    public
    function user_remove($user_id)
    {

        $query = "
			DELETE FROM
				users
			WHERE
				user_id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    /*** USER LEVEL LIST ******************************************************************
     * /*## List all data */
    public
    function user_level_list()
    {
        $results = array();
        $query = "
			SELECT
				*
			FROM
				user_levels
			ORDER BY user_level_id";

        if ($stmt = parent::prepare($query)) {
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();

        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    /** SHOWS START HERE */
    public function show_list()
    {
        $results = array();
        $query = "
			SELECT 
				*	
			FROM 
				shows
			ORDER BY show_votes DESC
			LIMIT 24";

        if ($stmt = parent::prepare($query)) {
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    public function show_list_genre($genre, $limit)
    {
        $results = array();
        $query = "
            SELECT 
                shows.id, 
                shows.api_id, 
                shows.show_poster_path, 
                shows.show_name, 
                genres.genre_name
            FROM show_genres
            JOIN genres ON show_genres.genre_id = genres.genre_id
            JOIN shows ON show_genres.show_id = shows.id
            WHERE genres.genre_name = ?
            LIMIT ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("si", $genre, $limit);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    public function show_genres($id)
    {
        $results = array();
        // limit results just in case a show has like 20 genres
        $query = "
            SELECT genres.genre_name
            FROM show_genres
            JOIN genres ON show_genres.genre_id = genres.genre_id
            JOIN shows ON show_genres.show_id = shows.id
            WHERE shows.id = ?
            LIMIT 5 ";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    public function show_insert($apiid, $showname, $lang, $overview, $vote_avg, $votes, $poster, $air_date, $orig_lang, $pop, $back_path)
    {
        $query = "
			INSERT INTO shows
				(api_id,
				show_name,
				show_language,
				show_overview,
				show_vote_average,
				show_votes,
				show_poster_path,
				show_air_date,
				show_original_lang,
				show_popularity,
				show_backdrop_path)
			VALUES
				(?,?,?,?,?,?,?,?,?,?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("isssdisssds", $apiid, $showname, $lang, $overview, $vote_avg, $votes, $poster, $air_date, $orig_lang, $pop, $back_path);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $last_id;

    }

    public function show_update_desc($show_id, $desc)
    {
        $query = "
			UPDATE shows SET 
				show_overview = ?
			WHERE
				api_id=?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("si", $desc, $show_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    public function show_update_back($show_id, $back)
    {
        $query = "
			UPDATE shows SET 
				show_backdrop_path = ?
			WHERE
				api_id=?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("si", $back, $show_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    public function show_genre_insert($show_id, $genre_id)
    {
        $query = "
			INSERT INTO show_genres
				(show_id,
				 genre_id)
			VALUES
				(?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("ii", $show_id, $genre_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $last_id;

    }

    public function show_info($id)
    {

        $results = array();
        $query = "
			SELECT 
				*
			FROM 
				shows
			WHERE
				id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    public function show_info_name($name)
    {
        $results = array();
        $query = "
            SELECT * FROM shows
            WHERE show_name = ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $name);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    public function show_edit($id, $showname, $api_id = null, $lang = null, $overview = null, $vote_avg = null, $votes = null, $poster = null, $air_date = null, $orig_lang = null, $pop = null)
    {

        $query = "
			UPDATE shows SET 
				show_name = ?,
				api_id = ?,
				show_language = ?,
				show_overview = ?,
				show_vote_average = ?,
				show_votes = ?,
				show_poster_path = ?,
				show_air_date = ?,
				show_original_lang = ?,
				show_popularity = ?
			WHERE
				id=?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sissdisssdi", $showname, $api_id, $lang, $overview, $vote_avg, $votes, $poster, $air_date, $orig_lang, $pop, $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

    }

    public function show_search($searchstr)
    {
        $results = [];
        $searchstr = "%" . $searchstr . "%";
        $query = "
            SELECT *
            FROM shows 
            WHERE show_name 
            LIKE ?
            ORDER BY show_votes DESC
            LIMIT 72";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $searchstr);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        /*
        $result = parent::query($query);
        $rows = mysqli_num_rows($result);
        $i = 0;

        if ($rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $results[$i]['id'] = $row['id'];
                $results[$i]['show_name'] = $row['show_name'];
                $results[$i]['year'] = $row['year'];
                //$results[$i]['runtime'] = $row['runtime'];
                //$results[$i]['votes'] = $row['votes'];
                $results[$i]['genres'] = $row['genres'];
                $results[$i]['description'] = $row['description'];
                $i++;
            }
        } else {
            $results = 0;
        }
        */
        return $results;
    }

    public function tmdb_api($showname)
    {
        $page_name = $showname;
        $url_str = urlencode($page_name);
        $url = 'https://api.themoviedb.org/3/search/tv?query=' . $url_str . '&include_adult=true&language=en-US&page=1&api_key=' . $key;
        $img_url = 'https://www.themoviedb.org/t/p/w600_and_h900_bestv2';

        //CURL REQUEST START
        $cin = curl_init();
        curl_setopt($cin, CURLOPT_URL, $url);
        //curl_setopt($cin, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cin, CURLOPT_TIMEOUT, 30);
        curl_setopt($cin, CURLOPT_RETURNTRANSFER, true);
        $rstr = curl_exec($cin);
        curl_close($cin);
        //CURL REQUEST END

        $api_data = json_decode($rstr, 1);

        if (count($api_data['results']) === 0) {
            $img_url = 'images/qmark.jpg';
        } else {
            $img_url = $img_url . $api_data['results'][0]['poster_path'];

            // if img_url is not a valid url, display placeholder img
            if (!filter_var($img_url, FILTER_VALIDATE_URL)) {
                $img_url = 'images/qmark.jpg';
            }
        }

        return $img_url;
    }

//    public function show_list_home()
//    {
//        $results = array();
//        $query = "
//			SELECT
//				*
//			FROM
//				shows
//			ORDER BY show_votes DESC
//			LIMIT 10";
//
//        if ($stmt = parent::prepare($query)) {
//            if (!$stmt->execute()) {
//                trigger_error($this->error, E_USER_WARNING);
//            }
//            $meta = $stmt->result_metadata();
//            while ($field = $meta->fetch_field()) {
//                $parameters[] = &$row[$field->name];
//            }
//            call_user_func_array(array($stmt, 'bind_result'), $parameters);
//
//            while ($stmt->fetch()) {
//                $x = array();
//                foreach ($row as $key => $val) {
//                    $x[$key] = $val;
//                }
//                $results[] = $x;
//            }
//            $stmt->close();
//        }//END PREPARE
//        else {
//            trigger_error($this->error, E_USER_WARNING);
//        }
//        return $results;
//    }

    public function review_insert($review_value, $review_content, $show_id, $user_id)
    {
        $query = "
			INSERT INTO reviews
				(review_value,
				 review_content,
				 review_date,
				 show_id,
				 user_id)	
			VALUES
				(?,?,CURDATE(),?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("isii", $review_value, $review_content, $show_id, $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $last_id = $this->insert_id;

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $last_id;
    }

    public
    function user_review_info($user_id)
    {

        $results = array();
        $query = "
			SELECT shows.show_name, 
			       shows.id,
			       shows.show_poster_path,
			       reviews.review_id,
			       reviews.review_content, 
			       reviews.review_value, 
			       reviews.review_date ,
			       reviews.user_id
            FROM reviews
            JOIN shows ON reviews.show_id = shows.id
            WHERE reviews.user_id = ?
            ORDER BY review_date DESC";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

//    public
//    function show_review_info($show_id)
//    {
//
//        $results = array();
//        $query = "
//			SELECT shows.show_name,
//			       shows.id,
//			       shows.show_poster_path,
//			       reviews.review_id,
//			       reviews.review_content,
//			       reviews.review_value,
//			       reviews.review_date ,
//			       reviews.user_id
//            FROM reviews
//            JOIN shows ON reviews.show_id = shows.id
//            WHERE reviews.show_id = ?
//            ORDER BY review_date DESC";
//        if ($stmt = parent::prepare($query)) {
//            $stmt->bind_param("i", $show_id);
//            if (!$stmt->execute()) {
//                trigger_error($this->error, E_USER_WARNING);
//            }
//            $meta = $stmt->result_metadata();
//            while ($field = $meta->fetch_field()) {
//                $parameters[] = &$row[$field->name];
//            }
//            call_user_func_array(array($stmt, 'bind_result'), $parameters);
//
//            while ($stmt->fetch()) {
//                $x = array();
//                foreach ($row as $key => $val) {
//                    $x[$key] = $val;
//                }
//                $results[] = $x;
//            }
//            $stmt->close();
//        }//END PREPARE
//        else {
//            trigger_error($this->error, E_USER_WARNING);
//        }
//        return $results;
//    }

    public
    function user_review_info_lim($user_id, $limit)
    {

        $results = array();
        $query = "
			SELECT shows.show_name, 
			       shows.id,
			       shows.show_poster_path,
			       reviews.review_id,
			       reviews.review_content, 
			       reviews.review_value, 
			       reviews.review_date ,
			       reviews.user_id
            FROM reviews
            JOIN shows ON reviews.show_id = shows.id
            WHERE reviews.user_id = ?
            ORDER BY review_date DESC
            LIMIT ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("ii", $user_id, $limit);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    public function review_info($id)
    {
        $results = array();
        $query = "
			SELECT 
				*	
			FROM 
				reviews
			WHERE
				review_id = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            $stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $results[$key] = $val;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $results;
    }

    public function show_reviews($id, $limit)
    {
        $results = array();
        $query = "
            SELECT * 
            FROM reviews 
            WHERE show_id=?
            ORDER BY review_date DESC
            LIMIT ?";
        if ($stmt = parent::prepare($query)) {
            if ($limit > 0) {
                $stmt->bind_param("ii", $id, $limit);
            } else {
                $z = 99;
                $stmt->bind_param("ii", $id, $z);
            }

            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }
            call_user_func_array(array($stmt, 'bind_result'), $parameters);

            while ($stmt->fetch()) {
                $x = array();
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

}//END CLASS
