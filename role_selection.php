<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selected_roles = $_POST['roles'] ?? [];
        $user_id = $_SESSION['user_id'];
        
        // Validate roles
        $allowed_roles = ['artist', 'enthusiast'];
        $selected_roles = array_intersect($selected_roles, $allowed_roles);
        
        // Determine role enum value
        if (count($selected_roles) === 2) {
            $role = 'both';
        } elseif (!empty($selected_roles)) {
            $role = $selected_roles[0];
        } else {
            $role = 'enthusiast'; // Default
        }

        // Update user role
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$role, $user_id]);
        
        // Insert into appropriate tables
        if (in_array('artist', $selected_roles) || $role === 'both') {
            $stmt = $conn->prepare("INSERT INTO artists (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $_SESSION['artist_id'] = $conn->lastInsertId();
        }
        
        if (in_array('enthusiast', $selected_roles) || $role === 'both') {
            $stmt = $conn->prepare("INSERT INTO enthusiasts (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            $_SESSION['enthusiast_id'] = $conn->lastInsertId();
        }
        
        $conn->commit();
        
        // Redirect to appropriate dashboard
        if ($role === 'artist') {
            header("Location: home2.php");
        } elseif ($role === 'both') {
            header("Location: home2.php");
        } else {
            header("Location: home2.php");
        }
        exit();
        
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        die("Error: " . $e->getMessage());
    }
}

// Get current role to pre-select checkboxes
$current_role = 'enthusiast'; // Default
if (isset($_SESSION['role'])) {
    $current_role = $_SESSION['role'];
}
$is_artist = in_array($current_role, ['artist', 'both']);
$is_enthusiast = in_array($current_role, ['enthusiast', 'both']);
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
        <form method="POST">
            <p>Select your roles:</p>
            <div class="options">
                <label class="option">
                    <input type="checkbox" name="roles[]" value="artist" <?= $is_artist ? 'checked' : '' ?>>
                    Artist
                </label>
                <label class="option">
                    <input type="checkbox" name="roles[]" value="enthusiast" <?= $is_enthusiast ? 'checked' : '' ?>>
                    Enthusiast
                </label>
            </div>
            <button type="submit" class="button button-primary">Save Roles</button>
        </form>
    </div>
</body>
</html>