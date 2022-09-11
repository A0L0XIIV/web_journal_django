<?php
    // AJAX dynamically load content
    if($_SERVER["REQUEST_METHOD"] === "GET"
        && isset($_GET["page"])){
        
        // Session start for getting name from the session
        session_start();
        // Check if the session variable name is empty or not and redirect
        if (!isset($_SESSION['name'])) {
            $addEntertainmentErrorText = "You cannot access this page!";
            http_response_code(401);
            exit($addEntertainmentErrorText); 
        }

        // Variables
        $perPage = 10;
        $page = 1;
        $start = ($page-1)*$perPage;
        $AJAXoutput = "";
        $date = "";

        /* Get page number from request */
        if(!empty($_GET["page"])) {
            $page = test_input($_GET["page"]);
        }
        else{
            $AJAXoutput = "<div class=\"error\">Yüklerken sayfa sayısı hatası.</div>";
            exit($AJAXoutput);
        }
        /* Get date from request */
        if(!empty($_GET["date"])){
            $date = test_input($_GET["date"]);
        }
        else{
            $AJAXoutput = "<div class=\"error\">Yüklerken tarih hatası.</div>";
            exit($AJAXoutput);
        }

        // Get name from session
        $name = $_SESSION['name'];
        // Check if name is empty or not and redirect
        if($name == "" || $name == NULL || $_SERVER["REQUEST_METHOD"] !== "GET")      
            echo("<script>location.href = './index.php';</script>"); 
    
        // Database connection
        require "./mysqli_connect.php";

        // Start variable for SQL limit
        $start = ($page-1)*$perPage;
        if($start < 0) 
            $start = 0;

        // SQL operations for AJAX call
        $sql = "SELECT id, work_happiness, daily_happiness, total_happiness, content, date
                FROM gunluk WHERE name=? AND date LIKE ? ORDER BY date DESC LIMIT ?, ?";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)){
            $AJAXoutput = "<div class=\"error\">Yüklerken günlük hazırlama hatası.</div>";
            exit($AJAXoutput);
        }
        else{
            // Preparing the park name for LIKE query 
            $param = '%'.$date.'%';
            // Bind inputs to query parameters
            mysqli_stmt_bind_param($stmt, "ssii", $name, $param, $start, $perPage);
            // Execute sql statement
            if(!mysqli_stmt_execute($stmt)){
                $AJAXoutput = "<div class=\"error\">Yüklerken günlük yürütme hatası.</div>";
                exit($AJAXoutput);
            }
            // Bind result variables
            mysqli_stmt_bind_result($stmt, $journal_id, $work_happiness, $daily_happiness, $total_happiness, $content, $journal_date);
            mysqli_stmt_store_result($stmt);
            // Journal Results fetched below...
            while (mysqli_stmt_fetch($stmt)) {
                // journal_date containes time as well, split that part
                $just_journal_date = explode(' ', $journal_date)[0];
                $AJAXoutput .=
                '<div class="card"  id="'.$just_journal_date.'" style="margin-top:15px;">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-xs-6 col-sm-2 px-0">
                                <button type="button" class="add-btn bg-password p-0" onclick="goToPreviousDay(\''.$date.'\', \''.$just_journal_date.'\')">
                                    Önceki
                                </button>
                                <button type="button" class="add-btn bg-edit p-0">
                                    <a href="edit.php?edit-date='.$just_journal_date.'" class="btn">Güncelle</a>
                                </button>
                                <button type="button" class="add-btn bg-password p-0" onclick="goToNextDay(\''.$date.'\', \''.$just_journal_date.'\')">
                                    Sonraki
                                </button>
                            </div>
                            <div class="col-xs-6 col-sm-3 px-0">
                                <p class="orange-text">Tarih:</p>
                                <p>'.$journal_date.'</p>
                            </div>
                            <div class="col-xs-4 col-sm-2 px-0">
                                <p class="orange-text">İş/Okul:</p>';
                                switch($work_happiness){
                                    case 10:
                                        $AJAXoutput .= '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                        break;
                                    case 9:
                                        $AJAXoutput .= '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                        break;
                                    case 8:
                                        $AJAXoutput .= '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                        break;
                                    case 7:
                                        $AJAXoutput .= '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                        break;
                                    case 6:
                                        $AJAXoutput .= '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                        break;
                                    case 5:
                                        $AJAXoutput .= '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                        break;
                                    case 4:
                                        $AJAXoutput .= '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                        break;
                                    case 3:
                                        $AJAXoutput .= '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                        break;
                                    case 2:
                                        $AJAXoutput .= '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                        break;
                                    case 1:
                                        $AJAXoutput .= '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                        break;
                                    case 0:
                                    default:
                                        $AJAXoutput .= '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                        break;
                                }
                            $AJAXoutput .=
                            '</div>
                            <div class="col-xs-4 col-sm-3 px-0">
                                <p class="orange-text">İş/Okul dışı:</p>';
                                switch($daily_happiness){
                                    case 10:
                                        $AJAXoutput .= '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                        break;
                                    case 9:
                                        $AJAXoutput .= '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                        break;
                                    case 8:
                                        $AJAXoutput .= '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                        break;
                                    case 7:
                                        $AJAXoutput .= '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                        break;
                                    case 6:
                                        $AJAXoutput .= '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                        break;
                                    case 5:
                                        $AJAXoutput .= '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                        break;
                                    case 4:
                                        $AJAXoutput .= '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                        break;
                                    case 3:
                                        $AJAXoutput .= '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                        break;
                                    case 2:
                                        $AJAXoutput .= '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                        break;
                                    case 1:
                                        $AJAXoutput .= '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                        break;
                                    case 0:
                                    default:
                                        $AJAXoutput .= '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                        break;
                                }
                            $AJAXoutput .=
                            '</div>
                            <div class="col-xs-4 col-sm-2 px-0">
                                <p class="orange-text">Genel:</p>';
                                switch($total_happiness){
                                    case 10:
                                        $AJAXoutput .= '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                        break;
                                    case 9:
                                        $AJAXoutput .= '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                        break;
                                    case 8:
                                        $AJAXoutput .= '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                        break;
                                    case 7:
                                        $AJAXoutput .= '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                        break;
                                    case 6:
                                        $AJAXoutput .= '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                        break;
                                    case 5:
                                        $AJAXoutput .= '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                        break;
                                    case 4:
                                        $AJAXoutput .= '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                        break;
                                    case 3:
                                        $AJAXoutput .= '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                        break;
                                    case 2:
                                        $AJAXoutput .= '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                        break;
                                    case 1:
                                        $AJAXoutput .= '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                        break;
                                    case 0:
                                    default:
                                        $AJAXoutput .= '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                        break;
                                }
                            $AJAXoutput .=
                            '</div>
                        </div>
                    </div>
                    <div class="card-body"> 
                        <div class="text-center">
                            <p class="mb-0">'.(!empty($content) ? $content : "").'</p>
                        </div>
                    </div>
                    <div class="card-footer">';

                    //mysqli_stmt_free_result ($stmt);

                        // Get daily game data from DB
                        $sql_game = "SELECT name, duration
                                    FROM daily_game
                                    INNER JOIN game ON daily_game.game_id=game.id 
                                    WHERE gunluk_id=? ORDER BY daily_game.id ASC";
                        $stmt_game = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt_game, $sql_game)){
                            $AJAXoutput .= "<div class=\"error\">Yüklerken oyun hazırlama hatası.".mysqli_error($conn)."</div>";
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt_game, "i", $journal_id);
                            // Execute sql statement
                            if(!mysqli_stmt_execute($stmt_game)){
                                $AJAXoutput .= "<div class=\"error\">Yüklerken oyun yürütme hatası.</div>";
                            }
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt_game, $game_name, $game_duration);
                            // Game Results fetched below...
                            if(mysqli_stmt_store_result($stmt_game)){
                                // Check if DB returned any result
                                if(mysqli_stmt_num_rows($stmt_game) > 0){
                                    $AJAXoutput .= '<table class="table table-bordered table-hover table-sm table-striped">';
                                    $AJAXoutput .= '<tr class="bg-game"><th>Oyun</th><th>Süre</th></tr>';
                                    // Fetch values
                                    while (mysqli_stmt_fetch($stmt_game)) {
                                        $AJAXoutput .= '<tr><td>'.$game_name.'</td><td>'.$game_duration.' Saat</td></tr>';
                                    }
                                    $AJAXoutput .= '</table>';
                                }
                            }
                            else{
                                $AJAXoutput .= '<div class=\"error\">Yüklerken oyun saklama hatası.</div>';
                            }
                        }

                        // Get daily series data from DB
                        $sql_series = "SELECT name, begin_season, begin_episode, end_season, end_episode
                                    FROM daily_series
                                    INNER JOIN series ON daily_series.series_id=series.id 
                                    WHERE gunluk_id=? ORDER BY daily_series.id ASC";
                        $stmt_series = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt_series, $sql_series)){
                            $AJAXoutput .= "<div class=\"error\">Yüklerken dizi hazırlama hatası.</div>";
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt_series, "i", $journal_id);
                            // Execute sql statement
                            if(!mysqli_stmt_execute($stmt_series)){
                                $AJAXoutput .= "<div class=\"error\">Yüklerken dizi yürütme hatası.</div>";
                            }
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt_series, $series_name, $series_begin_season, $series_begin_episode, $series_end_season, $series_end_episode);
                            // Series Results fetched below...
                            if(mysqli_stmt_store_result($stmt_series)){
                                // Check if DB returned any result
                                if(mysqli_stmt_num_rows($stmt_series) > 0){
                                    $AJAXoutput .= '<table class="table table-bordered table-hover table-sm table-striped">';
                                    $AJAXoutput .= '<tr class="bg-series">
                                            <th>Dizi</th>
                                            <th>İlk sezon</th>
                                            <th>İlk bölüm</th>
                                            <th>Son sezon</th>
                                            <th>Son bölüm</th></tr>';
                                    // Fetch values
                                    while (mysqli_stmt_fetch($stmt_series)) {
                                        $AJAXoutput .= '<tr><td>'.$series_name.'</td>
                                                <td>'.$series_begin_season.'</td>
                                                <td>'.$series_begin_episode.'</td>
                                                <td>'.$series_end_season.'</td>
                                                <td>'.$series_end_episode.'</td></tr>';
                                    }
                                    $AJAXoutput .= '</table>';
                                }
                            }
                            else{
                                $AJAXoutput .= "<div class=\"error\">Yüklerken dizi saklama hatası.</div>";
                            }
                        }

                        // Get daily movie data from DB
                        $sql_movie = "SELECT name, duration
                                    FROM daily_movie
                                    INNER JOIN movie ON daily_movie.movie_id=movie.id 
                                    WHERE gunluk_id=? ORDER BY daily_movie.id ASC";
                        $stmt_movie = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt_movie, $sql_movie)){
                            $AJAXoutput .= "<div class=\"error\">Yüklerken film hazırlama hatası.</div>";
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt_movie, "i", $journal_id);
                            // Execute sql statement
                            if(!mysqli_stmt_execute($stmt_movie)){
                                $AJAXoutput .= "<div class=\"error\">Yüklerken film yürütme hatası.</div>";
                            }
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt_movie, $movie_name, $movie_duration);
                            // Movie Results fetched below...
                            if(mysqli_stmt_store_result($stmt_movie)){
                                // Check if DB returned any result
                                if(mysqli_stmt_num_rows($stmt_movie) > 0){
                                    $AJAXoutput .= '<table class="table table-bordered table-hover table-sm table-striped">';
                                    $AJAXoutput .= '<tr class="bg-movie"><th>Film</th><th>Süre</th></tr>';
                                    // Fetch values
                                    while (mysqli_stmt_fetch($stmt_movie)) {
                                        $AJAXoutput .= '<tr><td>'.$movie_name.'</td><td>'.$movie_duration.' Saat</td></tr>';
                                    }
                                    $AJAXoutput .= '</table>';
                                }
                            }
                            else{
                                $AJAXoutput .= "<div class=\"error\">Yüklerken film saklama hatası.</div>";
                            }
                        }

                        // Get daily book data from DB
                        $sql_book = "SELECT name, duration
                                    FROM daily_book
                                    INNER JOIN book ON daily_book.book_id=book.id 
                                    WHERE gunluk_id=? ORDER BY daily_book.id ASC";
                        $stmt_book = mysqli_stmt_init($conn);
                        if(!mysqli_stmt_prepare($stmt_book, $sql_book)){
                            $AJAXoutput .= "<div class=\"error\">Yüklerken kitap hazırlama hatası.</div>";
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt_book, "i", $journal_id);
                            // Execute sql statement
                            if(!mysqli_stmt_execute($stmt_book)){
                                $AJAXoutput .= "<div class=\"error\">Yüklerken kitap yürütme hatası.</div>";
                            }
                            // Bind result variables
                            mysqli_stmt_bind_result($stmt_book, $book_name, $book_duration);
                            // Book Results fetched below...
                            if(mysqli_stmt_store_result($stmt_book)){
                                // Check if DB returned any result
                                if(mysqli_stmt_num_rows($stmt_book) > 0){
                                    $AJAXoutput .= '<table class="table table-bordered table-hover table-sm table-striped">';
                                    $AJAXoutput .= '<tr class="bg-book"><th>Kitap</th><th>Süre</th></tr>';
                                    // Fetch values
                                    while (mysqli_stmt_fetch($stmt_book)) {
                                        $AJAXoutput .= '<tr><td>'.$book_name.'</td><td>'.$book_duration.' Saat</td></tr>';
                                    }
                                    $AJAXoutput .= '</table>';
                                }
                            }
                            else{
                                $AJAXoutput .= "<div class=\"error\">Yüklerken kitap saklama hatası.</div>";
                            }
                        }
                        $AJAXoutput .= '
                    </div>
                </div>';
            }

            // Check if AJAXoutput is empty or not
            if($AJAXoutput == ""){
                $AJAXoutput = "Finished";
            }
        }

        // Return output to AJAX client-side
        exit($AJAXoutput);
    }
?>

<?php 
    require "header.php";
    // Check if the session variable name is empty or not and redirect
    if ($_SERVER["REQUEST_METHOD"] === "GET"
        && !isset($_SESSION['name'])) {
        exit("<script>location.href = './index.php';</script>");
    }
    // GET request check and exit if not
    else if($_SERVER["REQUEST_METHOD"] !== "GET"){
        exit();
    }
?>

<?php 
    // define variables and set to empty values
    $journal_id = "";
    $work_happiness = $daily_happiness = $total_happiness = $content = "";
    $date = $date_type = "";
    $game_id = $game_name = $game_duration = "";
    $series_id =  $series_name = $series_begin_season = $series_begin_episode = $series_end_season = $series_end_episode = "";
    $movie_id = $movie_name = $movie_duration = "";
    $book_id = $book_name = $book_duration = "";
    $gameArray = $seriesArray = $movieArray = $bookArray = [];
    $total_duration = $average_duration = $min_duration = $max_duration = 0;
    $error = false;
    $errorText = "";
    $showSection = 0;

    // Get name from session
    $name = $_SESSION['name'];
    // Check if name is empty or not and redirect
    if($name == "" || $name == NULL || $_SERVER["REQUEST_METHOD"] !== "GET")      
        echo("<script>location.href = './index.php';</script>"); 
  
    // Database connection
    require "./mysqli_connect.php";

    // Date form handler for journal
    if (isset($_GET["journal-date"])
        || isset($_GET["journal-month"])
        || isset($_GET["journal-year"])) {

        if(!empty($_GET["journal-date"])){
            $date = test_input($_GET["journal-date"]);
            $date_type = "day";
        }
        else if(!empty($_GET["journal-month"])){
            $date = test_input($_GET["journal-month"]);
            $date_type = "month";
        }
        else if(!empty($_GET["journal-year"])){
            $date = test_input($_GET["journal-year"]);
            $date_type = "year";
        }
        else{
            $error = true;
            $errorText = "Üç tarihte boş!";
        }

        if(!empty($date)){
            // Show section update
            $showSection = 1;

            // Check DB for picked date
            $sql = "SELECT id, work_happiness, daily_happiness, total_happiness, content, date
                    FROM gunluk WHERE name=? AND date LIKE ? ORDER BY date DESC LIMIT 10";
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
            }
            else{
                // Preparing the park name for LIKE query 
                $param = '%'.$date.'%';
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "ss", $name, $param);
                // Execute sql statement
                if(!mysqli_stmt_execute($stmt)){
                    $error = true;
                    $errorText = mysqli_error($conn);
                }
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $journal_id, $work_happiness, $daily_happiness, $total_happiness, $content, $journal_date);
                // Journal Results fetched below...
            }
        }
        else{
            $error = true;
            $errorText = "Gün, ay ya da yıl seçip gönderin.";
        }
    }

    // Next and Previos button handler
    else if (isset($_GET["next"])
            || isset($_GET["prev"])) {

        // IsNext parameter --> Next or Previos day requested
        $isNext = false;
        if(!empty($_GET["next"])) {
            $isNext = true;
            $date = test_input($_GET["next"]);
        } 
        else if(!empty($_GET["prev"])) {
            $isNext = false;
            $date = test_input($_GET["prev"]);
        }
        else {
            $error = true;
            $errorText = "Günlük bulunamadı!";
        }

        // Day, month and year operation
        $firstDate = "";
        $lastDate = "";
        $splitDate = explode('-', $date);
        switch (count($splitDate)) {
            // Year
            case 1:
                $firstDate = ($isNext ? $date : $date-1) + "-01-01";
                $lastDate = ($isNext ? $date+1 : $date) + "-01-01";
            // Month
            case 2:
                $firstDate = $splitDate[0] + ($isNext ? $splitDate[1] : $splitDate[1]-1) + "-01";
                $lastDate = $splitDate[0] + ($isNext ? $splitDate[1]+1 : $splitDate[1]) + "-01";
            // Day
            case 2:
                $firstDate = $splitDate[0] + $splitDate[1] + ($isNext ? $splitDate[2] : $splitDate[2]-1);
                $lastDate = $splitDate[0] + $splitDate[1] + ($isNext ? $splitDate[2]+1 : $splitDate[2]);
            default:
                $error = true;
                $errorText = "Yanlış tarih tipi!";
        }

        if(!empty($date)){
            // Show section update
            $showSection = 1;

            // Create SQL query for next or previos day
            if($isNextDay){
                $sql = "SELECT id, work_happiness, daily_happiness, total_happiness, content, date
                        FROM gunluk WHERE name=? AND date < ? AND date > ? ORDER BY date ASC LIMIT 1";
            }
            else {
                $sql = "SELECT id, work_happiness, daily_happiness, total_happiness, content, date
                        FROM gunluk WHERE name=? AND date < ? AND date > ? ORDER BY date DESC LIMIT 1";
            }
            $stmt = mysqli_stmt_init($conn);
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
            }
            else{
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "sss", $name, $firstDate, $lastDate);
                // Execute sql statement
                if(!mysqli_stmt_execute($stmt)){
                    $error = true;
                    $errorText = mysqli_error($conn);
                }
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $journal_id, $work_happiness, $daily_happiness, $total_happiness, $content, $journal_date);
                // Update date as well
                $date = $journal_date;
                // Journal Results fetched below...
            }
        }
        else{
            $error = true;
            $errorText = "Önceki ya da sonraki gün bulunamadı.";
        }
    }

    // Game name form handler for showing game data
    else if(isset($_GET["game"])){
        // Get game id from request
        $game_id = test_input($_GET["game"]);
        // Check id for emptiness
        if(empty($game_id)){
            $error = true;
            $errorText = "Oyun seçimi boş!";
        }
        // ID is not empty
        else{
            // Check DB for user play
            $sql = "SELECT DATE(gunluk.date), daily_game.duration FROM daily_game 
                    INNER JOIN game ON game_id=game.id 
                    INNER JOIN gunluk ON gunluk_id=gunluk.id
                    WHERE gunluk.name=? AND game_id=?
                    ORDER BY (gunluk.date) DESC";
            $stmt = mysqli_stmt_init($conn);
            // Prepare statement
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
                $errorText = mysqli_error($conn);
            }
            else{
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "si", $name, $game_id);
                // Execute sql statement
                mysqli_stmt_execute($stmt);
                // Bind results
                mysqli_stmt_bind_result($stmt, $date, $game_duration);
                // Store results
                if(mysqli_stmt_store_result($stmt)){ 
                    // Check if DB returned any result
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        // Show section update
                        $showSection = 2;
                        // Fetch values
                        while (mysqli_stmt_fetch($stmt)) {
                            // Push query results into array
                            array_push($gameArray, array('date' => $date, 'duration' => $game_duration));
                        }
                        // Query name, total, average, min and max game duration
                        $sql = "SELECT game.name, SUM(duration), AVG(duration), MIN(duration), MAX(duration) 
                                FROM daily_game 
                                INNER JOIN gunluk ON gunluk_id=gunluk.id
                                INNER JOIN game ON game_id=game.id
                                WHERE gunluk.name=? AND game_id=?";
                        $stmt = mysqli_stmt_init($conn);
                        // Prepare statement
                        if(!mysqli_stmt_prepare($stmt, $sql)){
                            $error = true;
                            $errorText = mysqli_error($conn);
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt, "si", $name, $game_id);
                            // Execute sql statement
                            mysqli_stmt_execute($stmt);
                            // Bind results
                            mysqli_stmt_bind_result($stmt, $game_name, $total_duration, $average_duration, $min_duration, $max_duration);
                            // Store results
                            mysqli_stmt_store_result($stmt);
                            // Fetch results
                            mysqli_stmt_fetch($stmt);
                        }
                    }
                    else{
                        $error = true;
                        $errorText = "Bu oyunu hiç oynamamışsın.";
                    }
                }
                else{
                    $error = true;
                    $errorText = mysqli_error($conn); 
                }   
            }
        }
    }
    
    // Series name form handler for showing series data
    else if(isset($_GET["series"])){
        // Get series id from request
        $series_id = test_input($_GET["series"]);
        // Check id for emptiness
        if(empty($series_id)){
            $error = true;
            $errorText = "Dizi seçimi boş!";
        }
        // ID is not empty
        else{
            // Check DB for user watch
            $sql = "SELECT DATE(gunluk.date), begin_season, begin_episode, end_season, end_episode
                    FROM daily_series 
                    INNER JOIN series ON series_id=series.id 
                    INNER JOIN gunluk ON gunluk_id=gunluk.id
                    WHERE gunluk.name=? AND series_id=?
                    ORDER BY (gunluk.date) DESC";
            $stmt = mysqli_stmt_init($conn);
            // Prepare statement
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
                $errorText = mysqli_error($conn);
            }
            else{
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "si", $name, $series_id);
                // Execute sql statement
                mysqli_stmt_execute($stmt);
                // Bind results
                mysqli_stmt_bind_result($stmt, $date, $series_begin_season, $series_begin_episode, $series_end_season, $series_end_episode);
                // Store results
                if(mysqli_stmt_store_result($stmt)){ 
                    // Check if DB returned any result
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        // Show section update
                        $showSection = 3;
                        // Fetch values
                        while (mysqli_stmt_fetch($stmt)) {
                            // Push query results into array
                            // If begin and end is in the same season, calculate the watched episode number
                            if ($series_begin_season === $series_end_season){
                                array_push($seriesArray, array('date' => $date, 
                                                                'season' => $series_begin_season,
                                                                // If only one episode print it, if not print "begin - end"
                                                                'episode' => ($series_begin_episode===$series_end_episode) ? $series_begin_episode : $series_begin_episode.' - '.$series_end_episode,
                                                                // Substract end - begin and add one
                                                                'duration' => ($series_end_episode-$series_begin_episode + 1)));
                            }
                            // Begin and end seasons different, push both of them
                            else {
                                array_push($seriesArray, array('date' => $date, 
                                                                'season' => $series_begin_season.' - '.$series_end_season,
                                                                // If only one episode print it, if not print "begin - end"
                                                                'episode' => ($series_begin_episode===$series_end_episode) ? $series_begin_episode : $series_begin_episode.' - '.$series_end_episode,
                                                                'duration' => 'S'.$series_begin_season.'E'.$series_begin_episode.' - S'.$series_end_season.'E'.$series_end_episode));
                            }
                        }
                        // Query name, total, average, min and max series duration
                        $sql = "SELECT series.name, 
                                        SUM(end_episode - begin_episode + 1), 
                                        AVG(end_episode - begin_episode + 1), 
                                        MIN(end_episode - begin_episode + 1), 
                                        MAX(end_episode - begin_episode + 1) 
                                FROM daily_series 
                                INNER JOIN gunluk ON gunluk_id=gunluk.id
                                INNER JOIN series ON series_id=series.id
                                WHERE gunluk.name=? AND series_id=?
                                AND begin_season = end_season";
                        $stmt = mysqli_stmt_init($conn);
                        // Prepare statement
                        if(!mysqli_stmt_prepare($stmt, $sql)){
                            $error = true;
                            $errorText = mysqli_error($conn);
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt, "si", $name, $series_id);
                            // Execute sql statement
                            mysqli_stmt_execute($stmt);
                            // Bind results
                            mysqli_stmt_bind_result($stmt, $series_name, $total_duration, $average_duration, $min_duration, $max_duration);
                            // Store results
                            mysqli_stmt_store_result($stmt);
                            // Fetch results
                            mysqli_stmt_fetch($stmt);
                        }
                    }
                    else{
                        $error = true;
                        $errorText = "Bu oyunu hiç oynamamışsın.";
                    }
                }
                else{
                    $error = true;
                    $errorText = mysqli_error($conn); 
                }   
            }
        }
    }

    // Movie name form handler for showing movie data
    else if(isset($_GET["movie"])){
        // Get movie id from request
        $movie_id = test_input($_GET["movie"]);
        // Check id for emptiness
        if(empty($movie_id)){
            $error = true;
            $errorText = "Film seçimi boş!";
        }
        // ID is not empty
        else{
            // Check DB for user watch
            $sql = "SELECT DATE(gunluk.date), daily_movie.duration FROM daily_movie 
                    INNER JOIN movie ON movie_id=movie.id 
                    INNER JOIN gunluk ON gunluk_id=gunluk.id
                    WHERE gunluk.name=? AND movie_id=?
                    ORDER BY (gunluk.date) DESC";
            $stmt = mysqli_stmt_init($conn);
            // Prepare statement
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
                $errorText = mysqli_error($conn);
            }
            else{
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "si", $name, $movie_id);
                // Execute sql statement
                mysqli_stmt_execute($stmt);
                // Bind results
                mysqli_stmt_bind_result($stmt, $date, $movie_duration);
                // Store results
                if(mysqli_stmt_store_result($stmt)){ 
                    // Check if DB returned any result
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        // Show section update
                        $showSection = 4;
                        // Fetch values
                        while (mysqli_stmt_fetch($stmt)) {
                            // Push query results into array
                            array_push($movieArray, array('date' => $date, 'duration' => $movie_duration));
                        }
                        // Query name, total movie duration
                        $sql = "SELECT movie.name, SUM(duration) FROM daily_movie 
                                INNER JOIN gunluk ON gunluk_id=gunluk.id
                                INNER JOIN movie ON movie_id=movie.id
                                WHERE gunluk.name=? AND movie_id=?";
                        $stmt = mysqli_stmt_init($conn);
                        // Prepare statement
                        if(!mysqli_stmt_prepare($stmt, $sql)){
                            $error = true;
                            $errorText = mysqli_error($conn);
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt, "si", $name, $movie_id);
                            // Execute sql statement
                            mysqli_stmt_execute($stmt);
                            // Bind results
                            mysqli_stmt_bind_result($stmt, $movie_name, $total_duration);
                            // Store results
                            mysqli_stmt_store_result($stmt);
                            // Fetch results
                            mysqli_stmt_fetch($stmt);
                        }
                    }
                    else{
                        $error = true;
                        $errorText = "Bu filmi hiç izlememişsin.";
                    }
                }
                else{
                    $error = true;
                    $errorText = mysqli_error($conn); 
                }   
            }
        }
    }

    // Book name form handler for showing book data
    else if(isset($_GET["book"])){
        // Get book id from request
        $book_id = test_input($_GET["book"]);
        // Check id for emptiness
        if(empty($book_id)){
            $error = true;
            $errorText = "Kitap seçimi boş!";
        }
        // ID is not empty
        else{
            // Check DB for user read
            $sql = "SELECT DATE(gunluk.date), daily_book.duration FROM daily_book 
                    INNER JOIN book ON book_id=book.id 
                    INNER JOIN gunluk ON gunluk_id=gunluk.id
                    WHERE gunluk.name=? AND book_id=?
                    ORDER BY (gunluk.date) DESC";
            $stmt = mysqli_stmt_init($conn);
            // Prepare statement
            if(!mysqli_stmt_prepare($stmt, $sql)){
                $error = true;
                $errorText = mysqli_error($conn);
            }
            else{
                // Bind inputs to query parameters
                mysqli_stmt_bind_param($stmt, "si", $name, $book_id);
                // Execute sql statement
                mysqli_stmt_execute($stmt);
                // Bind results
                mysqli_stmt_bind_result($stmt, $date, $book_duration);
                // Store results
                if(mysqli_stmt_store_result($stmt)){ 
                    // Check if DB returned any result
                    if(mysqli_stmt_num_rows($stmt) > 0){
                        // Show section update
                        $showSection = 5;
                        // Fetch values
                        while (mysqli_stmt_fetch($stmt)) {
                            // Push query results into array
                            array_push($bookArray, array('date' => $date, 'duration' => $book_duration));
                        }
                        // Query name, total, average, min and max book duration
                        $sql = "SELECT book.name, SUM(duration), AVG(duration), MIN(duration), MAX(duration) 
                                FROM daily_book 
                                INNER JOIN gunluk ON gunluk_id=gunluk.id
                                INNER JOIN book ON book_id=book.id
                                WHERE gunluk.name=? AND book_id=?";
                        $stmt = mysqli_stmt_init($conn);
                        // Prepare statement
                        if(!mysqli_stmt_prepare($stmt, $sql)){
                            $error = true;
                            $errorText = mysqli_error($conn);
                        }
                        else{
                            // Bind inputs to query parameters
                            mysqli_stmt_bind_param($stmt, "si", $name, $book_id);
                            // Execute sql statement
                            mysqli_stmt_execute($stmt);
                            // Bind results
                            mysqli_stmt_bind_result($stmt, $book_name, $total_duration, $average_duration, $min_duration, $max_duration);
                            // Store results
                            mysqli_stmt_store_result($stmt);
                            // Fetch results
                            mysqli_stmt_fetch($stmt);
                        }
                    }
                    else{
                        $error = true;
                        $errorText = "Bu kitabı hiç okumamışsın.";
                    }
                }
                else{
                    $error = true;
                    $errorText = mysqli_error($conn); 
                }   
            }
        }
    }

    // Not found error GET request handler
    else if (isset($_GET["error"])){
        $showSection = 0;
        $error = true;
        if($_GET["error"] === "not-found")
            $errorText = "Bu tarihli günlük bulunamadı.";
    }

    // Initial GET request to load page: load select-options from DB
    if($_SERVER["REQUEST_METHOD"] === "GET"
        && $showSection === 0) {
        // Show section update
        $showSection = 0;

        // Get game names
        $sql_game = "SELECT name, id FROM game";
        $stmt_game = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt_game, $sql_game)){
            $error = true;
            $errorText = "Hata: ".mysqli_error($conn);
        }
        else{
            // Execute sql statement
            mysqli_stmt_execute($stmt_game);
            // Execute sql statement
            if(!mysqli_stmt_execute($stmt_game)){
                $error = true;
                $errorText = "Oyunları yükleme hatası ".mysqli_error($conn);
            }
            // Bind result variables
            mysqli_stmt_bind_result($stmt_game, $game_name, $game_id);
            // Game name results fetched below...
            if(mysqli_stmt_store_result($stmt_game)){
                // Check if DB returned any result
                if(mysqli_stmt_num_rows($stmt_game) > 0){
                    // Fetch values
                    while (mysqli_stmt_fetch($stmt_game)) {
                        $gameArray [htmlspecialchars($game_id)] =  $game_name;
                    }
                }
            }
        }

        // Get series names
        $sql_series = "SELECT name, id FROM series";
        $stmt_series = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt_series, $sql_series)){
            $error = true;
            $errorText = "Hata: ".mysqli_error($conn);
        }
        else{
            // Execute sql statement
            mysqli_stmt_execute($stmt_series);
            // Execute sql statement
            if(!mysqli_stmt_execute($stmt_series)){
                $error = true;
                $errorText = "Dizileri yükleme hatası ".mysqli_error($conn);
            }
            // Bind result variables
            mysqli_stmt_bind_result($stmt_series, $series_name, $series_id);
            // Series name results fetched below...
            if(mysqli_stmt_store_result($stmt_series)){
                // Check if DB returned any result
                if(mysqli_stmt_num_rows($stmt_series) > 0){
                    // Fetch values
                    while (mysqli_stmt_fetch($stmt_series)) {
                        $seriesArray [htmlspecialchars($series_id)] =  $series_name;
                    }
                }
            }
        }

        // Get movie names
        $sql_movie = "SELECT name, id FROM movie";
        $stmt_movie = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt_movie, $sql_movie)){
            $error = true;
            $errorText = "Hata: ".mysqli_error($conn);
        }
        else{
            // Execute sql statement
            mysqli_stmt_execute($stmt_movie);
            // Execute sql statement
            if(!mysqli_stmt_execute($stmt_movie)){
                $error = true;
                $errorText = "Filmleri yükleme hatası ".mysqli_error($conn);
            }
            // Bind result variables
            mysqli_stmt_bind_result($stmt_movie, $movie_name, $movie_id);
            // Mvoie name results fetched below...
            if(mysqli_stmt_store_result($stmt_movie)){
                // Check if DB returned any result
                if(mysqli_stmt_num_rows($stmt_movie) > 0){
                    // Fetch values
                    while (mysqli_stmt_fetch($stmt_movie)) {
                        $movieArray [htmlspecialchars($movie_id)] =  $movie_name;
                    }
                }
            }
        }

        // Get book names
        $sql_book = "SELECT name, id FROM book";
        $stmt_book = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt_book, $sql_book)){
            $error = true;
            $errorText = "Hata: ".mysqli_error($conn);
        }
        else{
            // Execute sql statement
            mysqli_stmt_execute($stmt_book);
            // Execute sql statement
            if(!mysqli_stmt_execute($stmt_book)){
                $error = true;
                $errorText = "Kitapları yükleme hatası ".mysqli_error($conn);
            }
            // Bind result variables
            mysqli_stmt_bind_result($stmt_book, $book_name, $book_id);
            // Book name results fetched below...
            if(mysqli_stmt_store_result($stmt_book)){
                // Check if DB returned any result
                if(mysqli_stmt_num_rows($stmt_book) > 0){
                    // Fetch values
                    while (mysqli_stmt_fetch($stmt_book)) {
                        $bookArray [htmlspecialchars($book_id)] =  $book_name;
                    }
                }
            }
        }
    }

    // Custom function for SQL injection and other security checks
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
?>

<!-- Main center div-->
<main class="main" style="min-height: 89vh;">
    <br>
    <?php
    // Error
    if($error) {
        // Print error message
        require "./msg/error.php";
    }

    // Section 0 of 6, submit forms
    if($showSection === 0){ 
        echo '<div id="section0" class="section">
                <!-- Get journal by date  -->
                <form
                    name="date-form"
                    id="date-form"
                    action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'"
                    method="get"
                    onsubmit="return journalDateSubmit()">

                    <h1>Günlük görüntüleme tarihi seçiniz:</h1>
                    <!--Input for date, type=date-->
                    <div class="input-group mb-3 justify-content-center">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="day-label">Gün</span>
                        </div>
                        <input type="date" name="journal-date" id="journal-date-input">
                    </div>
                    <!--Input for month, type=month-->
                    <div class="input-group mb-3 justify-content-center">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="month-label">Ay</span>
                        </div>
                        <input type="month" name="journal-month" id="journal-month-input">
                    </div>
                    <!--Input for year, type=number-->
                    <div class="input-group mb-3 justify-content-center">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="year-label">Yıl</span>
                        </div>
                        <input type="number" name="journal-year" id="journal-year-input" placeholder="yyyy" min="1000" max="9999" title="Sadece 4 rakam">
                    </div>

                    <br>

                    <!--Button for submitting the form-->
                    <div>
                        <button
                            type="submit"
                            id="date-picker-submit"
                            class="sbmt-btn bg-show"
                            aria-pressed="false"
                        >
                        Göster
                        </button>
                    </div>

                    <hr>
                </form> 

                <!-- Get game by name -->
                <form 
                    name="game-form"
                    id="game-form"
                    action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" 
                    method="get">
                    <h3>Oyun seçiniz:</h3>

                    <!--Select for game, hidden input for request-->
                    <div class="mb-3 justify-content-center">
                        <select name="game"
                                id="game-select" 
                                class="custom-select"
                                required>
                            <option value="-1" hidden selected>Oyun seç</option>';

                            if(empty($gameArray)){
                                echo '<option value="" class="error">Oyun bulunamadı</option>';
                            }
                            else{
                                foreach($gameArray as $key => $value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                        echo '
                        </select>
                    </div>

                    <!--Button for submitting the form-->
                    <div>
                        <button
                            type="submit"
                            id="game-submit"
                            class="sbmt-btn bg-game"
                            aria-pressed="false"
                        >
                        Göster
                        </button>
                    </div>

                    <hr>
                </form>

                <!-- Get series by name -->
                <form 
                    name="series-form"
                    id="series-form"
                    action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" 
                    method="get">
                    <h3>Dizi seçiniz:</h3>

                    <!--Select for series, hidden input for request-->
                    <div class="mb-3 justify-content-center">
                        <select name="series"
                                id="series-select" 
                                class="custom-select"
                                required>
                            <option value="-1" hidden selected>Dizi seç</option>';
                            
                            if(empty($seriesArray)){
                                echo '<option value="" class="error">Dizi bulunamadı</option>';
                            }
                            else{
                                foreach($seriesArray as $key => $value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                        echo '
                        </select>
                    </div>

                    <!--Button for submitting the form-->
                    <div>
                        <button
                            type="submit"
                            id="series-submit"
                            class="sbmt-btn bg-series"
                            aria-pressed="false"
                        >
                        Göster
                        </button>
                    </div>

                    <hr>
                </form>

                <!-- Get movie by name -->
                <form 
                    name="movie-form"
                    id="movie-form"
                    action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" 
                    method="get">
                    <h3>Film seçiniz:</h3>

                    <!--Select for movie, hidden input for request-->
                    <div class="mb-3 justify-content-center">
                        <select name="movie"
                                id="movie-select" 
                                class="custom-select"
                                required>
                            <option value="-1" hidden selected>Film seç</option>';
                            
                            if(empty($movieArray)){
                                echo '<option value="" class="error">Dizi bulunamadı</option>';
                            }
                            else{
                                foreach($movieArray as $key => $value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                        echo '
                        </select>
                    </div>

                    <!--Button for submitting the form-->
                    <div>
                        <button
                            type="submit"
                            id="movie-submit"
                            class="sbmt-btn bg-movie"
                            aria-pressed="false"
                        >
                        Göster
                        </button>
                    </div>

                    <hr>
                </form>

                <!-- Get book by name -->
                <form 
                    name="book-form"
                    id="book-form"
                    action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" 
                    method="get">
                    <h3>Kitap seçiniz:</h3>

                    <!--Select for book, hidden input for request-->
                    <div class="mb-3 justify-content-center">
                        <select name="book"
                                id="book-select" 
                                class="custom-select"
                                required>
                            <option value="-1" hidden selected>Kitap seç</option>';
                            
                            if(empty($bookArray)){
                                echo '<option value="" class="error">Kitap bulunamadı</option>';
                            }
                            else{
                                foreach($bookArray as $key => $value)
                                    echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                        echo '
                        </select>
                    </div>

                    <!--Button for submitting the form-->
                    <div>
                        <button
                            type="submit"
                            id="book-submit"
                            class="sbmt-btn bg-book"
                            aria-pressed="false"
                        >
                        Göster
                        </button>
                    </div>
                </form>

                <br>

            </div>';
    }

    // Section 1 of 6, show journal data
    else if($showSection === 1){ 
        echo '<div id="section1" class="section">
            <h1>';
            if(isset($_SESSION['name'])){
                echo $_SESSION['name'].', ';
            }
            echo '<span id="date">'.$date.'</span> tarihili günlüklerin';
        echo'</h1>
            <div class="mx-auto">
                <button type="button" class="btn bg-password" onclick="goToPreviousDay(\''.$date.'\', \''.$just_journal_date.'\')">
                    <i class="fas fa-arrow-alt-circle-left"></i>
                </button>
                <button type="button" class="ent-btn bg-graph p-0">
                    <a href="graph.php?'.$date_type.'='.$date.'" class="btn">Grafik</a>
                </button>
                <button type="button" class="btn bg-password" onclick="goToNextDay(\''.$date.'\', \''.$just_journal_date.'\')">
                    <i class="fas fa-arrow-alt-circle-right"></i>
                </button>
            </div>
            <div id="journals">';

        if(mysqli_stmt_store_result($stmt)){
            // Check if DB returned any result
            if(mysqli_stmt_num_rows($stmt) > 0){
                // Fetch values
                while (mysqli_stmt_fetch($stmt)) {
                    // journal_date containes time as well, split that part
                    $just_journal_date = explode(' ', $journal_date)[0];
                    echo '<div class="card" id="'.$just_journal_date.'" style="margin-top:15px;">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-xs-6 col-sm-2 px-0">
                                        <button type="button" class="ent-btn bg-edit p-0">
                                            <a href="edit.php?edit-date='.$just_journal_date.'" class="btn">Güncelle</a>
                                        </button>
                                    </div>
                                    <div class="col-xs-6 col-sm-3 px-0">
                                        <p class="orange-text">Tarih:</p>
                                        <p>'.$journal_date.'</p>
                                    </div>
                                    <div class="col-xs-4 col-sm-2 px-0">
                                        <p class="orange-text">İş/Okul:</p>';
                                        switch($work_happiness){
                                            case 10:
                                                echo '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                                break;
                                            case 9:
                                                echo '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                                break;
                                            case 8:
                                                echo '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                                break;
                                            case 7:
                                                echo '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                                break;
                                            case 6:
                                                echo '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                                break;
                                            case 5:
                                                echo '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                                break;
                                            case 4:
                                                echo '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                                break;
                                            case 3:
                                                echo '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                                break;
                                            case 2:
                                                echo '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                                break;
                                            case 1:
                                                echo '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                                break;
                                            case 0:
                                            default:
                                                echo '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                                break;
                                        }
                                    echo
                                    '</div>
                                    <div class="col-xs-4 col-sm-3 px-0">
                                        <p class="orange-text">İş/Okul dışı:</p>';
                                        switch($daily_happiness){
                                            case 10:
                                                echo '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                                break;
                                            case 9:
                                                echo '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                                break;
                                            case 8:
                                                echo '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                                break;
                                            case 7:
                                                echo '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                                break;
                                            case 6:
                                                echo '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                                break;
                                            case 5:
                                                echo '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                                break;
                                            case 4:
                                                echo '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                                break;
                                            case 3:
                                                echo '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                                break;
                                            case 2:
                                                echo '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                                break;
                                            case 1:
                                                echo '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                                break;
                                            case 0:
                                            default:
                                                echo '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                                break;
                                        }
                                    echo
                                    '</div>
                                    <div class="col-xs-4 col-sm-2 px-0">
                                        <p class="orange-text">Genel:</p>';
                                        switch($total_happiness){
                                            case 10:
                                                echo '<p class="opt10"><i class="far fa-grin-stars"></i> Muhteşem</p>';
                                                break;
                                            case 9:
                                                echo '<p class="opt9"><i class="far fa-laugh-beam"></i> Şahane</p>';
                                                break;
                                            case 8:
                                                echo '<p class="opt8"><i class="far fa-grin-beam"></i> Baya iyi</p>';
                                                break;
                                            case 7:
                                                echo '<p class="opt7"><i class="far fa-grin"></i> Gayet iyi</p>';
                                                break;
                                            case 6:
                                                echo '<p class="opt6"><i class="far fa-smile"></i> Fena değil</p>';
                                                break;
                                            case 5:
                                                echo '<p class="opt5"><i class="far fa-meh"></i> Normal</p>';
                                                break;
                                            case 4:
                                                echo '<p class="opt4"><i class="far fa-frown"></i> Biraz kötü</p>';
                                                break;
                                            case 3:
                                                echo '<p class="opt3"><i class="far fa-sad-tear"></i> Kötü</p>';
                                                break;
                                            case 2:
                                                echo '<p class="opt2"><i class="far fa-sad-cry"></i> Berbat</p>';
                                                break;
                                            case 1:
                                                echo '<p class="opt1"><i class="far fa-dizzy"></i> Berbat ötesi</p>';
                                                break;
                                            case 0:
                                            default:
                                                echo '<p class="opt0"><i class="far fa-meh-blank"></i> Yorum Yok</p>';
                                                break;
                                        }
                                    echo
                                    '</div>
                                </div>
                            </div>
                            <div class="card-body"> 
                                <div class="text-center">
                                    <p class="mb-0">'.(!empty($content) ? $content : "").'</p>
                                </div>
                            </div>
                            <div class="card-footer">';
                                // Get daily game data from DB
                                $sql_game = "SELECT name, duration
                                            FROM daily_game
                                            INNER JOIN game ON daily_game.game_id=game.id 
                                            WHERE gunluk_id=? ORDER BY daily_game.id ASC";
                                $stmt_game = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt_game, $sql_game)){
                                    $error = true;
                                }
                                else{
                                    // Bind inputs to query parameters
                                    mysqli_stmt_bind_param($stmt_game, "i", $journal_id);
                                    // Execute sql statement
                                    if(!mysqli_stmt_execute($stmt_game)){
                                        $error = true;
                                        $errorText = mysqli_error($conn);
                                    }
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt_game, $game_name, $game_duration);
                                    // Game Results fetched below...
                                    if(mysqli_stmt_store_result($stmt_game)){
                                        // Check if DB returned any result
                                        if(mysqli_stmt_num_rows($stmt_game) > 0){
                                            echo '<table class="table table-bordered table-hover table-sm table-striped">';
                                            echo '<tr class="bg-game"><th>Oyun</th><th>Süre</th></tr>';
                                            // Fetch values
                                            while (mysqli_stmt_fetch($stmt_game)) {
                                                echo '<tr><td>'.$game_name.'</td><td>'.$game_duration.' Saat</td></tr>';
                                            }
                                            echo '</table>';
                                        }
                                    }
                                    else{
                                        echo'<!--Error-->
                                            <div class="error" id="dbError">
                                                <p>Oyunlar için veritabanı \'store\' hatası.
                                                    <button type="button"
                                                        class="fas fa-times-circle btn text-danger" 
                                                        aria-hidden="true" 
                                                        onclick="$(\'#dbError\').hide()">
                                                    </button>
                                                </p> 
                                            </div>';
                                    }
                                }

                                // Get daily series data from DB
                                $sql_series = "SELECT name, begin_season, begin_episode, end_season, end_episode
                                            FROM daily_series
                                            INNER JOIN series ON daily_series.series_id=series.id 
                                            WHERE gunluk_id=? ORDER BY daily_series.id ASC";
                                $stmt_series = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt_series, $sql_series)){
                                    $error = true;
                                }
                                else{
                                    // Bind inputs to query parameters
                                    mysqli_stmt_bind_param($stmt_series, "i", $journal_id);
                                    // Execute sql statement
                                    if(!mysqli_stmt_execute($stmt_series)){
                                        $error = true;
                                        $errorText = mysqli_error($conn);
                                    }
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt_series, $series_name, $series_begin_season, $series_begin_episode, $series_end_season, $series_end_episode);
                                    // Series Results fetched below...
                                    if(mysqli_stmt_store_result($stmt_series)){
                                        // Check if DB returned any result
                                        if(mysqli_stmt_num_rows($stmt_series) > 0){
                                            echo '<table class="table table-bordered table-hover table-sm table-striped">';
                                            echo '<tr class="bg-series">
                                                    <th>Dizi</th>
                                                    <th>İlk sezon</th>
                                                    <th>İlk bölüm</th>
                                                    <th>Son sezon</th>
                                                    <th>Son bölüm</th></tr>';
                                            // Fetch values
                                            while (mysqli_stmt_fetch($stmt_series)) {
                                                echo '<tr><td>'.$series_name.'</td>
                                                        <td>'.$series_begin_season.'</td>
                                                        <td>'.$series_begin_episode.'</td>
                                                        <td>'.$series_end_season.'</td>
                                                        <td>'.$series_end_episode.'</td></tr>';
                                            }
                                            echo '</table>';
                                        }
                                    }
                                    else{
                                        echo'<!--Error-->
                                            <div class="error" id="dbError">
                                                <p>Diziler için veritabanı \'store\' hatası.
                                                    <button type="button"
                                                        class="fas fa-times-circle btn text-danger" 
                                                        aria-hidden="true" 
                                                        onclick="$(\'#dbError\').hide()">
                                                    </button>
                                                </p> 
                                            </div>';
                                    }
                                }

                                // Get daily movie data from DB
                                $sql_movie = "SELECT name, duration
                                            FROM daily_movie
                                            INNER JOIN movie ON daily_movie.movie_id=movie.id 
                                            WHERE gunluk_id=? ORDER BY daily_movie.id ASC";
                                $stmt_movie = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt_movie, $sql_movie)){
                                    $error = true;
                                }
                                else{
                                    // Bind inputs to query parameters
                                    mysqli_stmt_bind_param($stmt_movie, "i", $journal_id);
                                    // Execute sql statement
                                    if(!mysqli_stmt_execute($stmt_movie)){
                                        $error = true;
                                        $errorText = mysqli_error($conn);
                                    }
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt_movie, $movie_name, $movie_duration);
                                    // Movie Results fetched below...
                                    if(mysqli_stmt_store_result($stmt_movie)){
                                        // Check if DB returned any result
                                        if(mysqli_stmt_num_rows($stmt_movie) > 0){
                                            echo '<table class="table table-bordered table-hover table-sm table-striped">';
                                            echo '<tr class="bg-movie"><th>Film</th><th>Süre</th></tr>';
                                            // Fetch values
                                            while (mysqli_stmt_fetch($stmt_movie)) {
                                                echo '<tr><td>'.$movie_name.'</td><td>'.$movie_duration.' Saat</td></tr>';
                                            }
                                            echo '</table>';
                                        }
                                    }
                                    else{
                                        echo'<!--Error-->
                                            <div class="error" id="dbError">
                                                <p>Filmler için veritabanı \'store\' hatası.
                                                    <button type="button"
                                                        class="fas fa-times-circle btn text-danger" 
                                                        aria-hidden="true" 
                                                        onclick="$(\'#dbError\').hide()">
                                                    </button>
                                                </p> 
                                            </div>';
                                    }
                                }

                                // Get daily book data from DB
                                $sql_book = "SELECT name, duration
                                            FROM daily_book
                                            INNER JOIN book ON daily_book.book_id=book.id 
                                            WHERE gunluk_id=? ORDER BY daily_book.id ASC";
                                $stmt_book = mysqli_stmt_init($conn);
                                if(!mysqli_stmt_prepare($stmt_book, $sql_book)){
                                    $error = true;
                                }
                                else{
                                    // Bind inputs to query parameters
                                    mysqli_stmt_bind_param($stmt_book, "i", $journal_id);
                                    // Execute sql statement
                                    if(!mysqli_stmt_execute($stmt_book)){
                                        $error = true;
                                        $errorText = mysqli_error($conn);
                                    }
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt_book, $book_name, $book_duration);
                                    // Book Results fetched below...
                                    if(mysqli_stmt_store_result($stmt_book)){
                                        // Check if DB returned any result
                                        if(mysqli_stmt_num_rows($stmt_book) > 0){
                                            echo '<table class="table table-bordered table-hover table-sm table-striped">';
                                            echo '<tr class="bg-book"><th>Kitap</th><th>Süre</th></tr>';
                                            // Fetch values
                                            while (mysqli_stmt_fetch($stmt_book)) {
                                                echo '<tr><td>'.$book_name.'</td><td>'.$book_duration.' Saat</td></tr>';
                                            }
                                            echo '</table>';
                                        }
                                    }
                                    else{
                                        echo'<!--Error-->
                                            <div class="error" id="dbError">
                                                <p>Kitaplar için veritabanı \'store\' hatası.
                                                    <button type="button"
                                                        class="fas fa-times-circle btn text-danger" 
                                                        aria-hidden="true" 
                                                        onclick="$(\'#dbError\').hide()">
                                                    </button>
                                                </p> 
                                            </div>';
                                    }
                                }
                            echo '
                            </div>
                        </div>';
                }
            }
            else{
                exit("<script>location.href = './show.php?error=not-found';</script>");
            }
        }
        else{
            echo'<!--Error-->
                <div class="error" id="dbError">
                    <p>Veritabanı \'store\' hatası.
                        <button type="button"
                            class="fas fa-times-circle btn text-danger" 
                            aria-hidden="true" 
                            onclick="$(\'#dbError\').hide()">
                        </button>
                    </p> 
                </div>';
        }
        // End of journals div
        echo '</div>';
        // If page shows more than one journal, put AJAX call button and loading icon at the end of the journals div
        if(mysqli_stmt_num_rows($stmt) > 1){
            echo '
                <button type="button" id="load-more-btn" class="add-btn bg-show" onclick="getContentCall()">
                    Daha fazla yükle
                </button>
                <div id="loader-icon"></div>';
        }
        // End of section1 div
        echo '</div>';
    }

    // Section 2 of 6, show game data
    else if($showSection === 2){
        echo '<div id="section2" class="section">
                <h2>'.$game_name.'</h2>
                <hr>
                <button type="button" class="bg-graph p-0">
                    <a href="./graph.php?game='.$game_id.'" class="bg-graph p-3">Grafik</a>
                </button>
                <hr>
                <table id="game-info-table" class="table table-borderless col-12 col-sm-10 col-md-8 col-xl-6 mx-auto">
                    <tr>
                        <td>Toplam bu oyunu oynama süresi:</td>
                        <td>'.$total_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Günlük ortalama oyun oynama süresi:</td>
                        <td>'.$average_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Bir günde en fazla oyun oynama süresi:</td>
                        <td>'.$max_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Bir günde en az oyun oynama süresi:</td>
                        <td>'.$min_duration.' Saat</td>
                    </tr>
                </table>         
                <br>
                <table id="game-table" class="table table-bordered table-hover table-sm table-striped">
                    <tr class="bg-game">
                        <th>Tarih</th>
                        <th>Süre</th>
                    </tr>';
                    foreach($gameArray as $game){
                        echo '<tr>';
                        echo '<td>'.$game['date'].'</td>';
                        echo '<td>'.$game['duration'].' Saat</td>';
                        echo '</tr>';
                    }
                    echo '
                </table>
            </div>';
    }

    // Section 3 of 6, show series data
    else if($showSection === 3){
        echo '<div id="section3" class="section">
                <h2>'.$series_name.'</h2>
                <hr>
                <button type="button" class="bg-graph p-0">
                    <a href="./graph.php?series='.$series_id.'" class="bg-graph p-3">Grafik</a>
                </button>
                <hr>
                <table id="series-info-table" class="table table-borderless col-12 col-sm-10 col-md-8 col-xl-6 mx-auto">
                    <tr>
                        <td>Toplam bu diziyi izleme süresi:</td>
                        <td>'.$total_duration.' Bölüm</td>
                    </tr>
                    <tr>
                        <td>Günlük ortalama dizi izleme süresi:</td>
                        <td>'.$average_duration.' Bölüm</td>
                    </tr>
                    <tr>
                        <td>Bir günde en fazla dizi izleme süresi:</td>
                        <td>'.$max_duration.' Bölüm</td>
                    </tr>
                    <tr>
                        <td>Bir günde en az dizi izleme süresi:</td>
                        <td>'.$min_duration.' Bölüm</td>
                    </tr>
                </table>         
                <br>
                <table id="game-table" class="table table-bordered table-hover table-sm table-striped">
                    <tr class="bg-series">
                        <th>Tarih</th>
                        <th>Sezon</th>
                        <th>Bölümler</th>
                        <th>Bölüm Sayısı</th>
                    </tr>';
                    foreach($seriesArray as $series){
                        echo '<tr>';
                        echo '<td>'.$series['date'].'</td>';
                        echo '<td>'.$series['season'].'</td>';
                        echo '<td>'.$series['episode'].'</td>';
                        echo '<td>'.$series['duration'].'</td>';
                        echo '</tr>';
                    }
                    echo '
                </table>
            </div>';
    }

    // Section 4 of 6, show movie data
    else if($showSection === 4){
        echo '<div id="section4" class="section">
                <h2>'.$movie_name.'</h2>
                <br>
                <table id="movie-info-table" class="table table-borderless col-12 col-sm-10 col-md-8 col-xl-6 mx-auto">
                    <tr>
                        <td>Toplam bu filmi izleme süresi:</td>
                        <td>'.$total_duration.' Saat</td>
                    </tr>
                </table>         
                <br>
                <table id="game-table" class="table table-bordered table-hover table-sm table-striped">
                    <tr class="bg-movie">
                        <th>Tarih</th>
                        <th>Süre</th>
                    </tr>';
                    foreach($movieArray as $movie){
                        echo '<tr>';
                        echo '<td>'.$movie['date'].'</td>';
                        echo '<td>'.$movie['duration'].' Saat</td>';
                        echo '</tr>';
                    }
                    echo '
                </table>
            </div>';
    }

    // Section 5 of 6, show book data
    else if($showSection === 5){
        echo '<div id="section5" class="section">
                <h2>'.$book_name.'</h2>
                <hr>
                <button type="button" class="bg-graph p-0">
                    <a href="./graph.php?book='.$book_id.'" class="bg-graph p-3">Grafik</a>
                </button>
                <hr>
                <table id="book-info-table" class="table table-borderless col-12 col-sm-10 col-md-8 col-xl-6 mx-auto">
                    <tr>
                        <td>Toplam bu kitabı okuma süresi:</td>
                        <td>'.$total_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Günlük ortalama kitap okuma süresi:</td>
                        <td>'.$average_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Bir günde en fazla kitap okuma süresi:</td>
                        <td>'.$max_duration.' Saat</td>
                    </tr>
                    <tr>
                        <td>Bir günde en az kitap okuma süresi:</td>
                        <td>'.$min_duration.' Saat</td>
                    </tr>
                </table>         
                <br>
                <table id="game-table" class="table table-bordered table-hover table-sm table-striped">
                    <tr class="bg-book">
                        <th>Tarih</th>
                        <th>Süre</th>
                    </tr>';
                    foreach($bookArray as $book){
                        echo '<tr>';
                        echo '<td>'.$book['date'].'</td>';
                        echo '<td>'.$book['duration'].' Saat</td>';
                        echo '</tr>';
                    }
                    echo '
                </table>
            </div>';
    }

    // Date Error
    else{
        // Print error message
        require "./msg/date-error.php";
    }
    ?>

</main>

<?php
    require "footer.php";
?>