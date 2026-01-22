<?php
session_start();
include 'db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'close' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "UPDATE internships SET status = 'closed' WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Internship closed successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
    }
    header('Location: company.php');
    exit();
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $required_skills = mysqli_real_escape_string($conn, $_POST['skills'] ?? '');
    
    if ($id > 0 && !empty($title) && !empty($description) && !empty($required_skills)) {
        $sql = "UPDATE internships SET 
                title = '$title',
                description = '$description',
                required_skills = '$required_skills'
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success_message'] = "Internship updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "All fields are required!";
    }
    header('Location: company.php');
    exit();
}


header('Location: company.php');
exit();
?>