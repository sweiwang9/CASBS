<?php
// === CONFIGURATION ===
$access_password = "Gold"; // The password to login
$file_path = 'words.json';

// Initialize variables
$message = "";
$msg_type = ""; // 'success' or 'error'

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_password = $_POST['password'] ?? '';

    // 1. Check Password
    if ($user_password !== $access_password) {
        $message = "Incorrect Password. Access Denied.";
        $msg_type = "error";
    } else {
        // 2. Load current words
        $json_content = file_get_contents($file_path);
        $data = json_decode($json_content, true);

        // 3. Add new words if they aren't empty
        $added_count = 0;

        if (!empty(trim($_POST['wordA']))) {
            $data['bucketA'][] = htmlspecialchars(trim($_POST['wordA']));
            $added_count++;
        }
        if (!empty(trim($_POST['wordB']))) {
            $data['bucketB'][] = htmlspecialchars(trim($_POST['wordB']));
            $added_count++;
        }
        if (!empty(trim($_POST['wordC']))) {
            $data['bucketC'][] = htmlspecialchars(trim($_POST['wordC']));
            $added_count++;
        }

        if ($added_count > 0) {
            // 4. Save back to file
            if(file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT))) {
                $message = "Success! $added_count new word(s) saved immediately.";
                $msg_type = "success";
            } else {
                $message = "Error: Could not write to file. Check permissions.";
                $msg_type = "error";
            }
        } else {
            $message = "You didn't type any words!";
            $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Words</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            border-top: 6px solid #009A49;
        }
        h1 { margin-top: 0; color: #004d25; }
        label { display: block; margin-top: 15px; font-weight: bold; font-size: 0.9rem; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background: #009A49;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
            text-transform: uppercase;
        }
        button:hover { background: #004d25; }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="container">
        <h1>Add New Words</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo $msg_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Committee Password:</label>
            <input type="password" name="password" required placeholder="Enter password">

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <label>Bucket A (Vibe/Adjective)</label>
            <input type="text" name="wordA" placeholder="e.g. electric">

            <label>Bucket B (Descriptor)</label>
            <input type="text" name="wordB" placeholder="e.g. fast-paced">

            <label>Bucket C (Noun)</label>
            <input type="text" name="wordC" placeholder="e.g. tiger">

            <button type="submit">Save Updates</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.html" style="color: #666; text-decoration: none; font-size: 0.8rem;">&larr; Back to Generator</a>
        </p>
    </div>

</body>
</html>
