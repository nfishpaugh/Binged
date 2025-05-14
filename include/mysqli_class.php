<?php
// TODO optional - Install Cloudinary packages to use for user PFP storage instead of storing them on the web server

/*********************************************************************
 * /*## Portal class extends mysqli */
class mysqli_class extends mysqli
{
    // Used to whitelist columns in the show table
    private array $column_arr = array("api_id", "show_name", "show_language", "show_overview", "show_poster_path", "show_air_date", "show_original_lang", "show_backdrop_path", "review_avg", "review_amt");

    /** Constructor function */
    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $os = strtolower(php_uname('s'));
        if (str_contains($os, 'windows')) {
            $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/.env");
            @parent::__construct($env["DBHost"], $env["DBUser"], $env["DBPass"], $env["DBName"], (int)$env["DBPort"]);
        } else {
            @parent::__construct(getenv("DBHost"), getenv("DBUser"), getenv("DBPass"), getenv("DBName"), (int)getenv("DBPort"));
        }
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
    function logins_insert($user_id): int|string
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = '';
        }

        $query = "
			INSERT INTO logins 
				(user_id,
				login_ip,
				login_browser,
				timestamp)	
			VALUES
				(?,?,?, CURRENT_TIMESTAMP())";
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

    /*** Removes selected id from the logins table */
    public function login_remove($id): void
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

    /** Logs an action committed by the specified user */
    public
    function actions_insert($action, $user_id): int|string
    {
        $page = $_SERVER['REQUEST_URI'];
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = '';
        }

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
    function user_info($user_id): array
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

    /** Gets a user's profile information based on their ID */
    public function user_pf_info($user_id): array
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

    /*** USER ADD  ******************************************************************
     * /*## adds row  data */
    public
    function user_insert($email, $name, $password, $level): int|string
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

    /** Creates a user's profile information. Description is optional */
    public function user_pf_insert($user_id, $join_date, $desc = "This user has not added a description yet."): void
    {
        $query = "
            INSERT INTO user_pf_data(user_id, user_description, user_join_date) 
            VALUES (?, ?, ?)
        ";

        if ($stmt = parent::prepare($query)) {
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

    /** Checks if the email/username already exists
     ** $field - the email/username to check
     ** $column - which column in the DB to check, e.g. 'email' to check an email and 'username' to check a username
     */
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
    function user_edit($user_id, $email, $name, $password, $level): void
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
    function user_remove($user_id): void
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
    function user_level_list(): array
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

    // SHOW FUNCTIONS START HERE

    /** Creates an array with the top $limit shows (stick to factors of 6 please) */
    public function show_list(): array
    {
        $limit = 30;
        $results = array();
        $query = "
			SELECT 
				*	
			FROM 
				shows
			LIMIT " . $limit;

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

    /** Retrieves $limit amt of shows with the $genre genre name (stick to factors of 6) */
    public function show_list_genre($genre, $limit): array
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

    /** Retrieves genre tags for the show with the specified id */
    public function show_genres($id): array
    {
        $results = array();
        // limit results just in case a show has like 20 genres
        $limit = 5;
        $query = "
            SELECT genres.genre_name
            FROM show_genres
            JOIN genres ON show_genres.genre_id = genres.genre_id
            JOIN shows ON show_genres.show_id = shows.id
            WHERE shows.id = ?
            LIMIT " . $limit;

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

    /** Inserts show with the specified params */
    public function show_insert($api_id, $show_name, $lang, $overview, $poster, $air_date, $orig_lang, $back_path): int|string
    {
        $query = "
			INSERT INTO shows
				(api_id,
				show_name,
				show_language,
				show_overview,
				show_poster_path,
				show_air_date,
				show_original_lang,
				show_backdrop_path)
			VALUES
				(?,?,?,?,?,?,?,?)";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("isssssss", $api_id, $show_name, $lang, $overview, $poster, $air_date, $orig_lang, $back_path);
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

    /** Links the specified show with the specified genre */
    public function show_genre_insert($show_id, $genre_id): int|string
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

    /** Retrieves information about the specified show based on the id */
    public function show_info($id): array
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

    /** Retrieves information about the specified show based on the name */
    public function show_info_name($name): array
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

    /** Updates a show based on the params
     ** Only $id and $showname are required
     */
    public function show_edit($id, $showname, $overview, $air_date): void
    {

        $query = "
			UPDATE shows SET 
				show_name = ?,
				show_overview = ?,
				show_air_date = ?
			WHERE
				id=?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sssi", $showname, $overview, $air_date, $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        }//END PREPARE
        else {
            trigger_error($this->error, E_USER_WARNING);
        }

    }

    /** Searches for a show based on the input string */
    public function show_search($searchstr, $amt_per_page = 72, $offset = 0): array
    {
        $results = [];
        $searchstr = "%" . $searchstr . "%";
        $query = "
            SELECT *
            FROM shows 
            WHERE show_name 
            LIKE ?
            LIMIT ? OFFSET ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sii", $searchstr, $amt_per_page, $offset);
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

    /** Returns the amount of results for a search string */
    public function search_count($searchstr): float|false|int|string|null
    {
        $data = 0;
        $searchstr = "%" . $searchstr . "%";
        $query = "SELECT count(*) FROM shows WHERE show_name LIKE ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $searchstr);
            if (!$stmt->execute()) trigger_error($this->error, E_USER_WARNING);

            $result = $stmt->get_result();
            $data = $result->fetch_column(0);
            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $data;
    }

    /** Retrieves a single column from the shows table based on the row id and column name
     * Returns null if the column is null/empty
     */
    public function get_show_column($id, $column): int|float|string|null
    {
        if (!in_array($column, $this->column_arr)) {
            trigger_error("The specified column was not found in the show table", E_USER_WARNING);
        }
        $query = "
            SELECT $column
            FROM shows
            WHERE id = ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
//            $meta = $stmt->result_metadata();
//            while ($field = $meta->fetch_field()) {
//                $parameters[] = &$row[$field->name];
//            }
//            call_user_func_array(array($stmt, 'bind_result'), $parameters);
//
//            $stmt->fetch();
//            $x = array();
//            foreach ($row as $key => $val) {
//                $results[$key] = $val;
//            }
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $data[$column];
    }

    /** Adds one to a star column based on the review rating, e.g. a 1 star review means the column 1_stars = 1_stars + 1
     ** id - Int, the ID of the show the review is for
     ** value - Int, the review's rating
     ** remove - Optional boolean, assign false to subtract from the column instead of adding
     ** edit - Optional boolean, assign false for default behavior and true to add one to the new rating and subtract one from $old_rating
     */
    public function star_update($id, $value, $remove = false, $edit = false, $old_value = 1): void
    {
        if (!is_int($value) || $value > 5 || $value < 1) {
            trigger_error("Cannot insert into star column, value is out of bounds or is not an integer", E_USER_WARNING);
        }

        $starcol = $value . "_stars";
        if (!$edit) {
            $op = $remove ? '-' : '+';
            $query = "
                UPDATE shows
                SET $starcol = $starcol $op 1
                WHERE id = ?
            ";
        } else {
            $oldcol = $old_value . "_stars";
            $query = "
                UPDATE shows
                SET $starcol = $starcol + 1, $oldcol = $oldcol - 1
                WHERE id = ?
            ";
        }

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            } else {
                $stmt->close();
            }
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    /** Returns all star columns for the specified show */
    public function get_star_cols($id): array
    {
        $results = array();
        $query = "
            SELECT 1_stars, 2_stars, 3_stars, 4_stars, 5_stars
            FROM shows
            WHERE id = ?";

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
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }
        return $results;
    }

    /** Updates a single column of a show entry based on the row id and column name
     ** column_type = Optional string, used to determine what data type is being inserted into the column
     *** i -> integer
     *** d -> double/float
     *** s -> string
     *** b -> blob, sent in packets (not used)
     */
    public function update_show_column($id, $newval, $column, $column_type = "i"): void
    {
        if (!in_array($column, $this->column_arr)) {
            trigger_error($this->error, E_USER_WARNING);
        }
        $query = "
            UPDATE shows 
            SET $column = ? 
            WHERE id = ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param($column_type . "i", $newval, $id);
            if ($stmt->execute()) {
                $stmt->close();
            } else {
                trigger_error($this->error, E_USER_WARNING);
            }
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    // REVIEW FUNCTIONS START HERE

    /** Creates a review for a show by the specified user. Returns the ID of the inserted row */
    public function review_insert($review_value, $review_content, $show_id, $user_id): int|string
    {
        $query = "
			INSERT INTO reviews
				(review_value,
				 review_content,
				 review_date,
				 show_id,
				 user_id)	
			VALUES
				(?,?,CURRENT_TIMESTAMP(),?,?)";
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

    /** Updates a review based on new content and/or value */
    public function review_update($review_id, $value, $content): void
    {
        $query = "
            UPDATE reviews
            SET review_value = ?, review_content = ?, review_date = CURRENT_TIMESTAMP()
            WHERE review_id = ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("isi", $value, $content, $review_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    /** Removes a review based on its id */
    public function review_delete($review_id): void
    {
        $query = "
        DELETE FROM reviews
        WHERE review_id=?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }

            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }
    }

    /** Retrieves all reviews owned by a user */
    public function user_review_info($user_id, $limit, $offset): array
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
            LIMIT ? OFFSET ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("iii", $user_id, $limit, $offset);
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

    /** Retrieves a $limit amt of reviews owned by a user */
    public function user_review_info_lim($user_id, $limit): array
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

    /** Retrieves a review based on its id */
    public function review_info($id): array
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

    /** Retrieves $limit reviews of a show
     *** offset - Optional integer, adds an offset for pagination. Defaults to 0
     */
    public function show_reviews($id, $limit, $offset = 0): array
    {
        $results = array();
        $query = "
            SELECT * 
            FROM reviews 
            WHERE show_id=?
            ORDER BY review_date DESC
            LIMIT ? OFFSET ?";
        if ($stmt = parent::prepare($query)) {
            if ($limit > 0) {
                $stmt->bind_param("iii", $id, $limit, $offset);
            } else {
                $z = 99;
                $stmt->bind_param("iii", $id, $z, $offset);
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

    /** Returns the amount of reviews for the specified show
     */
    public function review_count($show_id): float|false|int|string|null
    {
        $query = "
        SELECT count(review_id) 
        FROM reviews 
        WHERE show_id=?
        ";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $show_id);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $result = $stmt->get_result();
            $data = $result->fetch_column(0);
            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $data;
    }

    public function user_review_count($uid): float|false|int|string|null
    {
        $query = "
            SELECT count(reviews.review_id)
            FROM reviews
            JOIN shows ON reviews.show_id = shows.id
            WHERE reviews.user_id = ?
        ";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $uid);
            if (!$stmt->execute()) {
                trigger_error($this->error, E_USER_WARNING);
            }
            $result = $stmt->get_result();
            $data = $result->fetch_column(0);
            $stmt->close();
        } else {
            trigger_error($this->error, E_USER_WARNING);
        }

        return $data;
    }

}//END CLASS
