<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selected_roles = $_POST['roles'] ?? [];
        
        // Validate and sanitize roles
        $allowed_roles = ['artist', 'enthusiast']; // Only these can be selected via form
        $selected_roles = array_intersect($selected_roles, $allowed_roles);
        
        // Determine the enum value
        if (count($selected_roles) >= 2) {
            $role = 'both';
        } elseif (!empty($selected_roles)) {
            $role = reset($selected_roles); // Get first/only selected role
        } else {
            $role = 'enthusiast'; // Default
        }

        // Validate final role value
        $allowed_enum = ['admin', 'artist', 'enthusiast', 'both'];
        if (!in_array($role, $allowed_enum)) {
            throw new Exception("Invalid role selection");
        }

        // Update database
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$role, $_SESSION['user_id']]);
        
        header("Location: home2.php");
        exit();
        
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Artistic!</title>
    <link rel="stylesheet" href="role_selectionstyle.css">
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="img/sign%20in%20signup/video_2025-04-24_13-09-37.mp4" type="video/mp4">
    </video>
    
    <div class="container">
        <h1>Welcome to Artistic!</h1>
        <form action="role_selection.php" method="POST">
    <p>Select your roles:</p>
    <div class="options">
        <label class="option">
            <input type="checkbox" name="roles[]" value="artist">
            Artist
        </label>
        <label class="option">
            <input type="checkbox" name="roles[]" value="enthusiast">
            Enthusiast
        </label>
    </div>
    <button type="submit" class="button button-primary">Save Roles</button>
</form>
    </div>
</body>
</html>