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
        if (file_exists(dirname(__DIR__, 2) . "/vendor/autoload.php")) {
            require dirname(__DIR__, 2) . "/vendor/autoload.php";

            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->load();
            @parent::__construct($_ENV["DBHost"], $_ENV["DBUser"], $_ENV["DBPass"], $_ENV["DBName"], (int)$_ENV["DBPort"]);
            // check if connect errno is set

            //IF THE CONNECTION DOES NOT WORK - REDIRECT TO OUR "DB DOWN" PAGE, BUT PASS THE URL TO THE APPLICATION
            if (mysqli_connect_error()) {
                trigger_error(mysqli_connect_error(), E_USER_WARNING);
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                echo mysqli_connect_error();
                exit;
            }
        } else {
            print_r("no. Dir: " . __DIR__ . ", Path: " . dirname(__DIR__, 2) . ", Root: " . $_SERVER['DOCUMENT_ROOT']);
            exit;
        }
    }

    /** Checks user login credentials
     * @param string $email - The email of the user
     * @param string $password - The hashed password of the user
     * @return array|null
     */
    public function login(string $email, string $password): ?array
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
            return null;
        }
    }

    /** Logs user logins
     * @param int $user_id - The user ID to log
     * @return int|string
     */
    public
    function logins_insert(int $user_id): int|string
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

    /*** Removes selected id from the logins table
     * @param int $id - The review ID to delete
     * @return void
     */
    public function login_remove(int $id): void
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

    /** Logs an action committed by the specified user
     * @param string $action - The description of the action to log
     * @param int $user_id - The user ID to log
     * @return int|string
     */
    public
    function actions_insert(string $action, int $user_id): int|string
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

    /** Returns info for a row
     * @param int $user_id - The user id of the row to fetch
     * @return array
     */
    public
    function user_info(int $user_id): array
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

    /** Gets a user's profile information based on their ID
     * @param int $user_id - The user id of the profile to fetch
     * @return array
     */
    public function user_pf_info(int $user_id): array
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

    /** Adds a new user
     * @param string $email - The email of the user to be added
     * @param string $name - The username of the user to be added
     * @param string $password - The hashed password of the user to be added
     * @param int $level - The security level of the user to be added
     * @return int|string
     */
    public
    function user_insert(string $email, string $name, string $password, int $level): int|string
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

    /** Creates a user's profile information
     * @param int $user_id - The ID of the user to add a description to
     * @param string $join_date - The timestamp the user was created on
     * @param string $desc - The description of the user's profile - TODO - add a modal to modify this, currently everyone's description is placeholderX
     * @return void
     */
    public function user_pf_insert(int $user_id, string $join_date, string $desc = "This user has not added a description yet."): void
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
     ** @param string $field - the email/username to check
     ** @param string $column - which column in the DB to check, e.g. 'email' to check an email and 'username' to check a username
     * @return bool
     */
    public function user_field_check(string $field, string $column): bool
    {
        if ($column !== 'email' && $column !== 'username') trigger_error("user field check column must be either 'email' or 'username'", E_USER_WARNING);
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

    /** Updates a user row
     * @param int $user_id - The ID of the user to be updated
     * @param string $email - The email of the user to be updated
     * @param string $name - The username of the user to be updated
     * @param string $password - The hashed password of the user to be updated
     * @param int $level - The security level of the user to be updated
     * @return void
     */
    public
    function user_edit(int $user_id, string $email, string $name, string $password, int $level): void
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

    /** Removes a user
     * @param int $user_id - The id of the user to be deleted
     * @return void
     */
    public
    function user_remove(int $user_id): void
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

    /** Returns the available security levels for users
     * @return array
     */
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

    /** Creates an array with the top $limit shows (stick to factors of 6 please)
     * @return array
     */
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

    /** Retrieves $limit amt of shows with the $genre genre name (stick to factors of 6 for best looking results)
     * @param string $genre - The genre to search
     * @param int $limit - The amount of results to return
     * @param int $offset - Optional, the amount of results to skip
     * @return array
     */
    public function show_list_genre(string $genre, int $limit, int $offset = 0): array
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
            LIMIT ? OFFSET ?";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("sii", $genre, $limit, $offset);
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

    /** Retrieves genre tags for the show with the specified id
     * @param int $id - The id of the show to fetch the genre tags for
     * @return array
     */
    public function show_genres(int $id): array
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

    /** Retrieves the genre ID based on the genre name
     * @param string $genre_name - The name of the genre to get the ID for
     * @return int
     */
    public function get_genre_id(string $genre_name): int
    {
        $query = "SELECT genre_id FROM genres WHERE genre_name = ?";
        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("s", $genre_name);
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

    /** Inserts show with the specified params
     * @param int $api_id - The TMDB api ID of the show
     * @param string $show_name - The name of the show
     * @param string $lang - The 2 character language of the show (e.g. 'en' = english, 'ko' = korean, etc.)
     * @param string $overview - The description of the show
     * @param string $poster - The URL fragment to the show's poster image
     * @param string $air_date - The date the show first aired
     * @param string $orig_lang - The 2 character original language of the show
     * @param string $back_path - The URL fragment to the show's backdrop image
     * @return int|string
     */
    public function show_insert(int $api_id, string $show_name, string $lang, string $overview, string $poster, string $air_date, string $orig_lang, string $back_path): int|string
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

    /** Links the specified show with the specified genre
     * @param int $show_id - The ID of the show to add a genre to
     * @param int $genre_id - The ID of the genre to be added to a show
     * @return int|string
     */
    public function show_genre_insert(int $show_id, int $genre_id): int|string
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

    /** Retrieves information about the specified show based on the id
     * @param int $id - The ID of the show to fetch
     * @return array
     */
    public function show_info(int $id): array
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

    /** Retrieves information about the specified show based on the name
     * @param string $name - The name of the show to fetch
     * @return array
     */
    public function show_info_name(string $name): array
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
     * @param int $id - The ID of the show to be updated
     * @param int $showname - The name of the show to be updated
     * @param string $overview - The description of the show to be updated
     * @param string $air_date - The air date of the show to be updated
     * @return void
     */
    public function show_edit(int $id, int $showname, string $overview, string $air_date): void
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

    /** Searches for a show based on the input string
     * @param string $searchstr - The input text to be searched
     * @param int $amt_per_page - Optional - The amount of results to be displayed per page
     * @param int $offset - Optional - The amount of results to skip
     * @return array
     */
    public function show_search(string $searchstr, int $amt_per_page = 72, int $offset = 0): array
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

    /** Returns the amount of results for a search string
     * @param string $searchstr - The input text to be searched
     * @return float|false|int|string|null
     */
    public function search_count(string $searchstr): float|false|int|string|null
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

    /** Retrieves a single column from the shows table based on the row id and column name. Returns null if the column is null/empty
     * @param int $id - The ID of the show to have a column fetched from it
     * @param string $column - The name of the column to fetch. WILL TRIGGER AN ERROR IF NOT A VALID COLUMN
     * @return float|int|string|null
     */
    public function get_show_column(int $id, string $column): int|float|string|null
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

    /** Adds one to a star column based on the review rating, e.g. a 1-star review means the column 1_stars = 1_stars + 1
     ** @param int $id - The ID of the show the review is for
     ** @param int $value - The review's rating
     ** @param bool $remove - Optional, assign false to add from the column instead of subtracting
     ** @param bool $edit - Optional, assign false for default behavior and true to add one to the new rating and subtract one from $old_rating
     * @param int $old_value - Optional, only used if editing a review. Stores the original value of the review
     * @return void
     */
    public function star_update(int $id, int $value, bool $remove = false, bool $edit = false, int $old_value = 1): void
    {
        if ($value > 5 || $value < 1) {
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

    /** Returns all star columns for the specified show
     * @param int $id - The ID of the show to get the star columns of
     * @return array
     */
    public function get_star_cols(int $id): array
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
     * @param int $id - The ID of the show to update the column of
     * @param float|int|string $newval - The value of the column to update
     * @param string $column - The column to update
     ** @param string $column_type = Optional, used to determine what data type is being inserted into the column
     *
     * 'i' -> integer
     *
     * 'd' -> double/float
     *
     * 's' -> string
     *
     * 'b' -> blob, sent in packets (not used)
     * @return void
     */
    public function update_show_column(int $id, float|int|string $newval, string $column, string $column_type = "i"): void
    {
        if (!in_array($column, $this->column_arr)) {
            trigger_error($this->error, E_USER_WARNING);
        } elseif ($column_type === 'b') {
            trigger_error("Blob is not a supported data type for this method.", E_USER_WARNING);
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

    /** Creates a review for a show by the specified user. Returns the ID of the inserted row
     * @param int $review_value - The star rating of the review
     * @param string $review_content - The text of the review
     * @param int $show_id - The ID of the show the review was made for
     * @param int $user_id - The ID of the user that made the review
     * @return int|string
     */
    public function review_insert(int $review_value, string $review_content, int $show_id, int $user_id): int|string
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

    /** Updates a review based on new content and/or value
     * @param int $review_id - The review to update
     * @param int $value - The rating value to update the review with
     * @param string $content - The review text to update the review with
     * @return void
     */
    public function review_update(int $review_id, int $value, string $content): void
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

    /** Removes a review based on its id
     * @param int $review_id - The review to fetch
     * @return void
     */
    public function review_delete(int $review_id): void
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

    /** Retrieves all reviews owned by a user
     * @param int $user_id - The ID of the user
     * @param int $limit - The amount of reviews to return
     * @param int $offset - How much offset to apply to the query, e.g. $offset = 5, $limit = 1 -> returns the sixth review of the user (if it exists)
     * @return array
     */
    public function user_review_info(int $user_id, int $limit, int $offset): array
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

    /** Retrieves a $limit amt of reviews owned by a user
     * @param int $user_id - The ID of the user
     * @param int $limit - The amount of reviews to return
     * @return array
     */
    public function user_review_info_lim(int $user_id, int $limit): array
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

    /** Retrieves a review based on its id
     * @param int $id - The ID of the review to be fetched
     * @return array
     */
    public function review_info(int $id): array
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
     * @param int $id - The ID of the show
     * @param int $limit - The amount of results to return
     * @param int $offset - Optional integer, adds an offset for pagination. Defaults to 0
     * @return array
     */
    public function show_reviews(int $id, int $limit, int $offset = 0): array
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
     * @param int $show_id - The ID of the show
     * @return float|false|int|string|null
     */
    public function review_count(int $show_id): float|false|int|string|null
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

    /** Returns the amount of reviews for a specified user
     * @param int $uid - The id of the user to query
     * @return float|false|int|string|null
     */
    public function user_review_count(int $uid): float|false|int|string|null
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

    /** Retrieves the total amount of shows
     * @return float|false|int|string|null
     */
    public function show_count(): float|false|int|string|null
    {
        $query = "
            SELECT count(id)
            FROM shows
        ";

        if ($stmt = parent::prepare($query)) {
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

    /** Returns the amount of shows that are in a specified genre
     * @param int $genre_id - The ID of the genre to get the amt of shows for
     * @return float|false|int|string|null
     */
    public function genre_show_count(int $genre_id): float|false|int|string|null
    {
        $query = "
            SELECT count(id)
            FROM show_genres
            WHERE genre_id = ?
        ";

        if ($stmt = parent::prepare($query)) {
            $stmt->bind_param("i", $genre_id);
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
