<?php
// =============================================
// DATABASE CONFIGURATION & FUNCTIONS
// =============================================
$host = 'localhost';
$dbname = 'survey_app';
$username = 'root';
$password = '';
// Connect to MySQL
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
// Create table if it doesn't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS surveys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        date_of_birth DATE NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        favorite_food VARCHAR(50) NOT NULL,
        movies_rating INT NOT NULL,
        radio_rating INT NOT NULL,
        eat_out_rating INT NOT NULL,
        tv_rating INT NOT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_survey'])) {
    $errors = [];
    // Validate inputs
    if (empty($_POST['full_name'])) $errors['full_name'] = 'Full name is required';
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    $dob = $_POST['date_of_birth'];
    if (empty($dob)) {
        $errors['date_of_birth'] = 'Date of birth is required';
    } else {
        $age = (new DateTime())->diff(new DateTime($dob))->y;
        if ($age < 5 || $age > 120) $errors['date_of_birth'] = 'Age must be between 5 and 120';
    }
    if (empty($_POST['contact_number'])) $errors['contact_number'] = 'Contact number is required';
    if (empty($_POST['favorite_food'])) $errors['favorite_food'] = 'Favorite food is required';
    if (empty($_POST['movies_rating'])) $errors['movies_rating'] = 'Movie rating is required';
    if (empty($_POST['radio_rating'])) $errors['radio_rating'] = 'Radio rating is required';
    if (empty($_POST['eat_out_rating'])) $errors['eat_out_rating'] = 'Eat out rating is required';
    if (empty($_POST['tv_rating'])) $errors['tv_rating'] = 'TV rating is required';
    // Save to database if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO surveys (full_name, email, date_of_birth, contact_number, favorite_food, movies_rating, radio_rating, eat_out_rating, tv_rating)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['full_name'],
            $_POST['email'],
            $dob,
            $_POST['contact_number'],
            $_POST['favorite_food'],
            $_POST['movies_rating'],
            $_POST['radio_rating'],
            $_POST['eat_out_rating'],
            $_POST['tv_rating']
        ]);
        $success = "Survey submitted successfully!";
    }
}
// Check if we should show results
$show_results = isset($_GET['view']) && $_GET['view'] === 'results';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            align-content: flex-start;
        }

        h1 {
            text-align: center;
            font-size: medium;
        }



        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            transition: color 0.3s;
        }

        .nav-links a:visited {
            color: black;
        }

        .nav-links a:active {
            color: black;
        }

        .nav-links a:focus {
            outline: none;
            color: black;
        }

        .nav-title {
            font-weight: bold;
            font-size: 16px;
        }

        .nav-links a:hover {
            color: #1e90ff;

        }






        .form-section {
            display: flex;
            align-items: flex-start;
            margin: 40px;
        }

        .form-label {
            width: 150px;
            padding-top: 0px;
            margin-right: 80px;

        }

        .form-fields {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .form-group input {
            width: 300px;
            padding: 8px;
            border: 1px solid #1e90ff;
            border-radius: 1px;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        .favorite-food-group {
            display: flex;
            align-items: center;
            gap: 20px;
            /* space between question and options */
            margin: 20px 0;
        }

        .favorite-food-group label {
            display: flex;
            align-items: center;
            /*   margin-right: 20px;
            font-weight: normal;
            display: flex;
            align-items: flex-end;
            font-size: small;*/
        }

        .favorite-food-group .form-label {
            font-weight: 500;
            margin-right: 20px;
            /* space between label and first radio */
            white-space: nowrap;
        }

        .favorite-food-group input[type="radio"] {
            margin-right: 5px;
            display: inline-block;
            appearance: none;
            width: 15px;
            height: 15px;
            border: 1px solid #2980b9;
            border-radius: 1px;
            background: #fff;
            vertical-align: auto;
            cursor: pointer;
            position: relative;

        }

        .favorite-food-group input[type="radio"]:checked::before {
            content: "";
            display: block;
            width: 10px;
            height: 10px;
            background: #2980b9;
            position: absolute;
            top: 3px;
            left: 3px;
            border-radius: 1px;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td,
        th {
            padding: 5px;
            text-align: left;
        }

        .rating-options th {
            background-color: lightgray;
            border: 1px solid black;
            text-align: center;
        }

        .rating-options td {
            text-align: center;
            padding: 2px;
            border: 1px solid #1e90ff;
            ;
        }

        .rating-options td input[type="radio"]:checked::before {
            content: "";
            display: grid;
            width: 10px;
            height: 10px;
            background: #1e90ff;
            position: absolute;
            top: 2px;
            left: 2px;
            border-radius: 50%;
        }

        /*.rating-options td input[type="radio"]:checked::after {
            content: "";
            display: grid;
            width: 16px;
            height: 16px;
            border: 1px solid #1e90ff;
            border-radius: 50%;
            position: relative;
            top: 2px;
            left: 2px
        }*/



        .rating-options td input[type="radio"] {
            appearance: none;
            width: 16px;
            height: 16px;
            background-color: #fff;
            border: 1px solid #1e90ff;
            border-radius: 50%;
            position: relative;
            cursor: pointer;
        }




        button[type="submit"] {
            display: block;
            margin: 30px auto 0 auto;
            padding: 10px 30px;
            font-size: 1rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 0px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button[type="submit"]:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="nav-bar">
            <div class="nav-title">_Surveys</div>
            <div class="nav-links">
                <a href="?">Fill Out Survey</a>
                <a href="?view=results">View Results</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <?php if (!$show_results): ?>
            <!-- SURVEY FORM -->

            <form method="post">
                <div class="form-section">
                    <div class="form-label">Personal Details:</div>

                    <div class="form-fields">

                        <div class="form-group">
                            <label>Full Name:</label>
                            <input type="text" name="full_name" value="<?= $_POST['full_name'] ?? '' ?>">
                            <?php if (!empty($errors['full_name'])): ?><span class="error"><?= $errors['full_name'] ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= $_POST['email'] ?? '' ?>">
                            <?php if (!empty($errors['email'])): ?><span class="error"><?= $errors['email'] ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Date of Birth:</label>
                            <input type="date" name="date_of_birth" value="<?= $_POST['date_of_birth'] ?? '' ?>">
                            <?php if (!empty($errors['date_of_birth'])): ?><span class="error"><?= $errors['date_of_birth'] ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Contact Number:</label>
                            <input type="tel" name="contact_number" value="<?= $_POST['contact_number'] ?? '' ?>">
                            <?php if (!empty($errors['contact_number'])): ?><span class="error"><?= $errors['contact_number'] ?></span><?php endif; ?>
                        </div>

                    </div>
                </div>




                <div class="form-group favorite-food-group">
                    <div class="form-label">What is your favorite food?</div>
                    <div class="radio-options"></div>
                    <label>
                        <input type="radio" name="favorite_food" value="Pizza" <?= (isset($_POST['favorite_food']) && $_POST['favorite_food'] === 'Pizza') ? 'checked' : '' ?>> Pizza
                    </label>
                    <label>
                        <input type="radio" name="favorite_food" value="Pasta" <?= (isset($_POST['favorite_food']) && $_POST['favorite_food'] === 'Pasta') ? 'checked' : '' ?>> Pasta
                    </label>
                    <label>
                        <input type="radio" name="favorite_food" value="Pap and Wors" <?= (isset($_POST['favorite_food']) && $_POST['favorite_food'] === 'Pap and Wors') ? 'checked' : '' ?>> Pap and Wors
                    </label>
                    <label>
                        <input type="radio" name="favorite_food" value="Other" <?= (isset($_POST['favorite_food']) && $_POST['favorite_food'] === 'Other') ? 'checked' : '' ?>> Other
                    </label>
                    <?php if (!empty($errors['favorite_food'])): ?><span class="error"><?= $errors['favorite_food'] ?></span><?php endif; ?>
                </div>

                </style>
                <p>Please rate your level of agreement on a scale from 1 to 5, with 1 being "strongly agree, and 5 being "strongly disagree".</p>
                <table class="rating-options">
                    <tr>
                        <th></th>
                        <th><small>Strongly Agree</small></th>
                        <th><small>Agree</small></th>
                        <th><small>Neutral</small></th>
                        <th><small>Disagree</small></th>
                        <th><small>Strongly Disagree</small></th>
                    </tr>
                    <tr>
                        <td>I like to watch movies</td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <td><input type="radio" name="movies_rating" value="<?= $i ?>" <?= (isset($_POST['movies_rating']) && $_POST['movies_rating'] == $i) ? 'checked' : '' ?>></td>
                        <?php endfor; ?>
                    </tr>
                    <tr>
                        <td>I like to listen to radio</td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <td><input type="radio" name="radio_rating" value="<?= $i ?>" <?= (isset($_POST['radio_rating']) && $_POST['radio_rating'] == $i) ? 'checked' : '' ?>></td>
                        <?php endfor; ?>
                    </tr>
                    <tr>
                        <td>I like to eat out</td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <td><input type="radio" name="eat_out_rating" value="<?= $i ?>" <?= (isset($_POST['eat_out_rating']) && $_POST['eat_out_rating'] == $i) ? 'checked' : '' ?>></td>
                        <?php endfor; ?>
                    </tr>
                    <tr>
                        <td>I like to watch TV</td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <td><input type="radio" name="tv_rating" value="<?= $i ?>" <?= (isset($_POST['tv_rating']) && $_POST['tv_rating'] == $i) ? 'checked' : '' ?>></td>
                        <?php endfor; ?>
                    </tr>
                </table>
                <button type="submit" name="submit_survey">SUBMIT</button>
            </form>
        <?php else: ?>
            <!-- SURVEY RESULTS -->
            <h1>Survey Results</h1>
            <?php
            $total = $pdo->query("SELECT COUNT(*) FROM surveys")->fetchColumn();
            if ($total == 0) {
                echo "<p>No surveys available yet.</p>";
            } else {
                // Age calculations
                $ageStats = $pdo->query("
                SELECT
                    AVG(DATEDIFF(CURRENT_DATE, date_of_birth)/365) AS avg_age,
                    MIN(DATEDIFF(CURRENT_DATE, date_of_birth)/365) AS min_age,
                    MAX(DATEDIFF(CURRENT_DATE, date_of_birth)/365) AS max_age
                FROM surveys
            ")->fetch();
                // Food preferences
                $foodStats = $pdo->query("
                SELECT
                    ROUND(COUNT(CASE WHEN favorite_food = 'Pizza' THEN 1 END) / COUNT(*) * 100, 1) AS pizza_percent,
                    ROUND(COUNT(CASE WHEN favorite_food = 'Pasta' THEN 1 END) / COUNT(*) * 100, 1) AS pasta_percent,
                    ROUND(COUNT(CASE WHEN favorite_food = 'Pap and Wors' THEN 1 END) / COUNT(*) * 100, 1) AS pap_wors_percent
                FROM surveys;
            ")->fetch();
                // Average ratings
                $ratings = $pdo->query("
                SELECT
                    ROUND(AVG(movies_rating), 1) AS avg_movies,
                    ROUND(AVG(radio_rating), 1) AS avg_radio,
                    ROUND(AVG(eat_out_rating), 1) AS avg_eat_out,
                    ROUND(AVG(tv_rating), 1) AS avg_tv
                FROM surveys;
            ")->fetch();
            ?>
                <table>

                    <tr>
                        <td>Total number of surveys:</td>
                        <td><?= $total ?></td>
                    </tr>
                    <tr>
                        <td>Average age:</td>
                        <td><?= round($ageStats['avg_age'], 1) ?> years</td>
                    </tr>
                    <tr>
                        <td>Oldest person who participated in survey:</td>
                        <td><?= round($ageStats['max_age'], 1) ?> years</td>
                    </tr>
                    <tr>
                        <td>Youngest person who participated in survey:</td>
                        <td><?= round($ageStats['min_age'], 1) ?> years</td>
                    </tr>
                    <tr>
                        <td>Percentage of people who like Pizza:</td>
                        <td><?= $foodStats['pizza_percent'] ?>%</td>
                    </tr>
                    <tr>
                        <td>Percentage of people who like Pasta:</td>
                        <td><?= $foodStats['pasta_percent'] ?>%</td>
                    </tr>
                    <tr>
                        <td>Percentage of people who like Pap and Wors</td>
                        <td><?= $foodStats['pap_wors_percent'] ?>%</td>
                    </tr>
                    <tr>
                        <td>People who like to watch movies:</td>
                        <td><?= $ratings['avg_movies'] ?></td>
                    </tr>
                    <tr>
                        <td>People who like to listen to radio</td>
                        <td><?= $ratings['avg_radio'] ?></td>
                    </tr>
                    <tr>
                        <td>People who like to eat out:</td>
                        <td><?= $ratings['avg_eat_out'] ?></td>
                    </tr>
                    <tr>
                        <td>People who like to watch TV:</td>
                        <td><?= $ratings['avg_tv'] ?></td>
                    </tr>
                </table>
            <?php } ?>
        <?php endif; ?>
</body>

</html>