<?php
// Start output buffering to prevent premature output
ob_start();

// Enable error reporting (during development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $name     = htmlspecialchars(trim($_POST['name']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $rating   = (int) trim($_POST['rating']);
    $comments = htmlspecialchars(trim($_POST['comments']));

    // Validate fields
    if (empty($name) || empty($email) || empty($rating) || empty($comments)) {
        echo "<h1>Error:</h1> <p>All fields are required.</p>";
        exit();
    }

    // Save to database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "feedbackdb";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        echo "<h1>Database Connection Failed:</h1><p>" . $conn->connect_error . "</p>";
        exit();
    }

    $sql = "INSERT INTO feedback (name, email, rating, comments) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<h1>SQL Error:</h1><p>" . $conn->error . "</p>";
        $conn->close();
        exit();
    }

    $stmt->bind_param("ssis", $name, $email, $rating, $comments);
    $stmt->execute();

    // Optional: Check success
    if ($stmt->affected_rows <= 0) {
        echo "<h1>Failed to save feedback.</h1>";
        $stmt->close();
        $conn->close();
        exit();
    }

    // Also save to local file
    $entry = "Name: $name\nEmail: $email\nRating: $rating\nComments: $comments\n---\n";
    file_put_contents("feedbacks.txt", $entry, FILE_APPEND | LOCK_EX);

    // Close connections
    $stmt->close();
    $conn->close();

    // Redirect to thank you page
    header("Location: thankyou.html");
    exit();
} else {
    echo "<h1>Invalid Access</h1>";
    exit();
}

// Flush buffer
ob_end_flush();
?>
