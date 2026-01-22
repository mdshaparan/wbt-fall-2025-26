<?php
// Start session and include database connection
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'] ?? 1;
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $required_skills = mysqli_real_escape_string($conn, $_POST['skills'] ?? '');
    $start_month = $_POST['start_date'] ?? '';
    $end_month = $_POST['end_date'] ?? '';
    $application_deadline = $_POST['deadline'] ?? '';
    
    // Validate required fields
    if (empty($title) || empty($description) || empty($required_skills) || 
        empty($start_month) || empty($end_month) || empty($application_deadline)) {
        $_SESSION['error_message'] = "All fields are required!";
        header('Location: company.php');
        exit();
    }
    
    // Calculate duration
    try {
        $start = new DateTime($start_month);
        $end = new DateTime($end_month);
        
        if ($start >= $end) {
            $_SESSION['error_message'] = "End date must be after start date!";
            header('Location: company.php');
            exit();
        }
        
        $interval = $start->diff($end);
        $duration_months = ($interval->y * 12) + $interval->m + ($interval->d / 30);
        
        // Validate deadline
        $deadline_date = new DateTime($application_deadline);
        $today = new DateTime();
        if ($deadline_date <= $today) {
            $_SESSION['error_message'] = "Application deadline must be in the future!";
            header('Location: company.php');
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Invalid date format!";
        header('Location: company.php');
        exit();
    }
    
    // Insert into database
    $sql = "INSERT INTO internships (company_id, title, description, required_skills, 
            start_month, end_month, application_deadline, duration_months, status) 
            VALUES ('$company_id', '$title', '$description', '$required_skills', 
            '$start_month', '$end_month', '$application_deadline', '$duration_months', 'open')";
    
    if (mysqli_query($conn, $sql)) {
        // Store success message in session
        $_SESSION['success_message'] = "Internship posted successfully!";
        // Redirect back to company dashboard
        header('Location: company.php');
    } else {
        // Store error message in session
        $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        // Redirect back to company dashboard
        header('Location: company.php');
    }
    exit();
}

// If not POST request, redirect to dashboard
header('Location: company.php');
exit();
?>