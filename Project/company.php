<?php
session_start();
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Destroy all session data
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

  
    session_destroy();

    // Redirect to login page
    header("Location: login.php");
    exit();
}

include 'db.php';

$success_message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); 
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}


if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'company') {
   
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['user_id']; 


if (!$conn) {
    die("Database connection failed!");
}


$company_query = "SELECT * FROM company_reg WHERE company_id = $company_id";
$company_result = mysqli_query($conn, $company_query);

if (!$company_result || mysqli_num_rows($company_result) == 0) {
   
    $_SESSION['error_message'] = 'Company not found in database';
    header("Location: login.php");
    exit();
} else {
    $company = mysqli_fetch_assoc($company_result);
}

$company_result = mysqli_query($conn, $company_query);

if (!$company_result || mysqli_num_rows($company_result) == 0) {

    $company = [
        'name' => 'Test Company',
        'email' => 'test@company.com',
        'industry' => 'Technology',
        'website' => 'https://testcompany.com'
    ];
} else {
    $company = mysqli_fetch_assoc($company_result);
   
    if (isset($company['id'])) {
        $company_id = $company['id'];
    } elseif (isset($company['company_id'])) {
        $company_id = $company['company_id'];
    }
}


$internships_query = "SELECT * FROM internships WHERE company_id = $company_id ORDER BY created_at DESC";
$internships_result = mysqli_query($conn, $internships_query);

if (!$internships_result) {
  
    $internships_result = false;
}


$stats_query = "SELECT 
    COUNT(*) as total_internships,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as active_internships
    FROM internships WHERE company_id = $company_id";
$stats_result = mysqli_query($conn, $stats_query);

if ($stats_result && mysqli_num_rows($stats_result) > 0) {
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $stats = ['total_internships' => 0, 'active_internships' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <style>
      
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .popup {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            min-width: 300px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .popup-success {
            border-top: 5px solid #2ecc71;
        }
        
        .popup-error {
            border-top: 5px solid #e74c3c;
        }
        
        .popup-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .popup-success .popup-icon {
            color: #2ecc71;
        }
        
        .popup-error .popup-icon {
            color: #e74c3c;
        }
        
        .popup h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .popup p {
            margin-bottom: 20px;
            color: #666;
        }
        
        .popup-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .popup-btn:hover {
            background: #2980b9;
        }
        
        
        * {
            margin: 0;
            padding: 0; 
            box-sizing: border-box; 
            font-family: Arial, sans-serif; 
        }
        body { 
            background: #f0f2f5;
         }
        .container { 
            display: flex; 
            min-height: 100vh;
         }
        .sidebar { 
            width: 220px;
             background: #2c3e50; 
             color: white; 
             padding: 20px 0;
             }
        .sidebar h2 { 
            padding: 0 20px; 
            margin-bottom: 30px; 
            color: white;
         }
        .sidebar ul { 
            list-style: none; 
        }
        .sidebar li {
             margin: 5px 0;
             }
        .sidebar a { 
            display: block; 
            padding: 12px 20px; 
            color: white; 
            text-decoration: none;
         }
        .sidebar a:hover, .sidebar a.active { 
            background: #34495e;
         }
        .main-content { 
            flex: 1; 
            padding: 20px;
         }
        .header {
             background: white; 
             padding: 15px 20px;
              margin-bottom: 20px;
               border-radius: 5px; 
               display: flex; 
               justify-content: space-between; 
               align-items: center;
             }
        .page {
             display: none; 
             background: white;
              padding: 20px;
               border-radius: 5px;
             }
        .page.active { 
            display: block;
         }
        .stats {
             display: grid; 
             grid-template-columns: repeat(4, 1fr);
              gap: 20px;
               margin-bottom: 20px; 
            }
        .stat-card {
             background: white; 
             padding: 20px;
              border-radius: 5px;
               box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            }
        table { 
            width: 100%;
             border-collapse: collapse; 
             background: white; 
             margin-top: 10px;
             }
        th, td { 
            padding: 12px;
             text-align: left;
              border-bottom: 1px solid #ddd; 
            }
        th { 
            background: #2c3e50; 
            color: white; 
        }
        tr:hover { 
            background: #f5f5f5; 
        }
        .form-group {
             margin-bottom: 15px;
             }
        label {
             display: block; 
             margin-bottom: 5px; 
             font-weight: bold;
             }
        input, select, textarea {
             width: 100%;
              padding: 8px;
               border: 1px solid #ddd; 
               border-radius: 4px; 
            }
        button { 
            padding: 10px 20px;
             background: #3498db; 
             color: white; 
             border: none; 
             border-radius: 4px; 
             cursor: pointer; 
            }
        button:hover {
             background: #2980b9; 
            }
        .modal { 
            display: none;
             position: fixed; 
             top: 0;
              left: 0; 
              width: 100%; 
              height: 100%; 
              background: rgba(0,0,0,0.5);
               z-index: 1000; 
            }
        .modal-content { 
            background: white;
             margin: 10% auto;
              padding: 20px;
               width: 400px; 
               border-radius: 5px; 
            }
        .btn-group {
             display: flex; 
             gap: 10px;
              margin-top: 10px; 
            }
        .btn-small { 
            padding: 5px 10px; 
            font-size: 12px; 
        }
        .success {
             background: #2ecc71;
             }
        .danger { 
            background: #e74c3c; 
        }
        .warning { 
            background: #f39c12; 
        }
        .info { 
            background: #3498db;
         }
        .status { 
            padding: 3px 8px; 
            border-radius: 3px; 
            font-size: 12px;
             color: white;
             }
        .message {
             padding: 10px; 
             margin: 10px 0;
              border-radius: 4px;
             }
        .success-message { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .error-message {
             background: #f8d7da; 
             color: #721c24;
              border: 1px solid #f5c6cb; }

                
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .profile-header h1 {
            color: #2c3e50;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .edit-profile-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .edit-profile-btn:hover {
            background: #2980b9;
        }
        
        .profile-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        /* Profile Card */
        .profile-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
        
        .profile-avatar-section {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .company-logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-basic-info h2 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 22px;
        }
        
        .profile-email {
            color: #666;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .profile-contact {
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-contact i {
            width: 16px;
            color: #3498db;
        }
        
        .profile-actions {
            display: flex;
            gap: 15px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .profile-action-btn {
            background: white;
            border: 1px solid #dee2e6;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #495057;
            transition: all 0.3s;
        }
        
        .profile-action-btn:hover {
            background: #f8f9fa;
            border-color: #3498db;
            color: #3498db;
        }
        
     
        .info-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-section h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-label i {
            width: 18px;
            color: #3498db;
        }
        
        .info-value {
            color: #212529;
            font-size: 15px;
            padding: 8px 0;
        }
        
        .info-value a {
            color: #3498db;
            text-decoration: none;
        }
        
        .info-value a:hover {
            text-decoration: underline;
        }
        
        .description-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
            line-height: 1.6;
            color: #495057;
        }
        
       
        .stats-section {
            margin-top: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .stat-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .stat-content h4 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-content p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        
        .profile-edit {
            background: white;
            border-radius: 10px;
            padding: 30px;
        }
        
        .profile-edit h3 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .form-section h4 {
            color: #495057;
            margin-bottom: 20px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .profile-edit label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .profile-edit label i {
            width: 18px;
            color: #3498db;
        }
        
        .profile-edit input,
        .profile-edit select,
        .profile-edit textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }
        
        .profile-edit input:focus,
        .profile-edit select:focus,
        .profile-edit textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        
        .logo-upload-container {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        .current-logo {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .current-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .no-logo {
            text-align: center;
            color: #6c757d;
        }
        
        .no-logo i {
            color: #adb5bd;
            margin-bottom: 10px;
        }
        
        .upload-controls {
            flex: 1;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .upload-btn:hover {
            background: #2980b9;
        }
        
        .upload-hint {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 15px;
        }
        
        .logo-preview {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            overflow: hidden;
            border: 2px solid #dee2e6;
            margin-top: 10px;
            display: none;
        }
        
        .logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
       
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .save-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .save-btn:hover {
            background: #27ae60;
        }
        
        .cancel-btn {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .cancel-btn:hover {
            background: #7f8c8d;
        }
        
    
        .modal-content {
            width: 500px;
            max-width: 90%;
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 20px;
            background: #f8f9fa;
            transition: border-color 0.3s;
        }
        
        .upload-area:hover {
            border-color: #3498db;
        }
        
        .upload-area i {
            color: #adb5bd;
            margin-bottom: 15px;
        }
        
        .upload-area p {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .upload-or {
            color: #6c757d;
            font-size: 14px;
            margin: 10px 0;
        }
        
        .browse-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .browse-btn:hover {
            background: #2980b9;
        }
        
        .modal-preview {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px auto;
            border: 3px solid #dee2e6;
            display: none;
        }
        
        .modal-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .upload-submit-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .upload-submit-btn:hover {
            background: #27ae60;
        }
        
       
        @media (max-width: 768px) {
            .profile-avatar-section {
                flex-direction: column;
                text-align: center;
            }
            
            .info-grid,
            .form-grid,
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .logo-upload-container {
                flex-direction: column;
            }
            
            .current-logo {
                width: 120px;
                height: 120px;
                margin: 0 auto;
            }
            
            .profile-actions {
                flex-direction: column;
            }
            
            .form-actions,
            .modal-actions {
                flex-direction: column;
            }
            
            .form-actions button,
            .modal-actions button {
                width: 100%;
            }
        }
       
    </style>
</head>
<body>
 
    <?php if($success_message): ?>
    <div id="successPopup" class="popup-overlay" style="display: flex;">
        <div class="popup popup-success">
            <div class="popup-icon">✓</div>
            <h3>Success!</h3>
            <p><?php echo htmlspecialchars($success_message); ?></p>
            <button class="popup-btn" onclick="closePopup('successPopup')">OK</button>
        </div>
    </div>
    <?php endif; ?>
    
  
    <?php if($error_message): ?>
    <div id="errorPopup" class="popup-overlay" style="display: flex;">
        <div class="popup popup-error">
            <div class="popup-icon">✗</div>
            <h3>Error!</h3>
            <p><?php echo htmlspecialchars($error_message); ?></p>
            <button class="popup-btn" onclick="closePopup('errorPopup')">OK</button>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
       
        <div class="sidebar">
            <h2>Company Dashboard</h2>
            <ul>
                <li><a href="#" class="active" onclick="showPage('dashboard')">Dashboard</a></li>
                <li><a href="#" onclick="showPage('post')">Post Internship</a></li>
                <li><a href="#" onclick="showPage('manage')">Manage Internships</a></li>
                <li><a href="#" onclick="showPage('applications')">Applications</a></li>
                <li><a href="#" onclick="showPage('offers')">Job Offers</a></li>
                <li><a href="#" onclick="showPage('profile')">Profile</a></li>
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </div>

       
        <div class="main-content">
            <div class="header">
                <h1 id="pageTitle">Company Dashboard</h1>
                <div>Welcome, <?php echo htmlspecialchars($company['company_name'] ?? 'Company'); ?></div>
            </div>

          
            <div id="dashboard" class="page active">
                <h2>Dashboard Overview</h2>
                <div class="stats">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_internships'] ?? 0; ?></h3>
                        <p>Total Internships Posted</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['active_internships'] ?? 0; ?></h3>
                        <p>Active Internships</p>
                    </div>
                    <div class="stat-card">
                        <h3>0</h3>
                        <p>Total Applicants</p>
                    </div>
                    <div class="stat-card">
                        <h3>0</h3>
                        <p>Offers Sent</p>
                    </div>
                </div>
                
                <h3>Recent Activities</h3>
                <table>
                    <tr><th>Activity</th><th>Date</th><th>Status</th></tr>
                    <?php if($internships_result && mysqli_num_rows($internships_result) > 0): ?>
                        <?php while($internship = mysqli_fetch_assoc($internships_result)): ?>
                            <?php 
                                $date = date('Y-m-d', strtotime($internship['created_at']));
                                $status_class = $internship['status'] == 'open' ? 'success' : 'danger';
                                $status_text = $internship['status'] == 'open' ? 'Active' : 'Closed';
                            ?>
                            <tr>
                                <td>Posted: <?php echo htmlspecialchars($internship['title']); ?></td>
                                <td><?php echo $date; ?></td>
                                <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No recent activities</td></tr>
                    <?php endif; ?>
                </table>
            </div>

       
            <div id="post" class="page">
                <h2>Post New Internship</h2>
                <form action="post_internship.php" method="POST" id="internshipForm">
                    <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                    <div class="form-group">
                        <label>Internship Title *</label>
                        <input type="text" name="title" required placeholder="Web Development Intern" id="title">
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="4" required placeholder="Describe the internship role and responsibilities" id="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Required Skills (comma separated) *</label>
                        <input type="text" name="skills" required placeholder="JavaScript, React, HTML, CSS" id="skills">
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Start Date *</label>
                            <input type="date" name="start_date" required id="start_date">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>End Date *</label>
                            <input type="date" name="end_date" required id="end_date" onchange="calculateDuration()">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Application Deadline *</label>
                        <input type="date" name="deadline" required id="deadline">
                    </div>
                    <div class="form-group">
                        <label>Duration (auto-calculated)</label>
                        <input type="text" id="duration" readonly>
                    </div>
                    <button type="submit" id="submitBtn">Post Internship</button>
                </form>
            </div>

            <div id="manage" class="page">
                <h2>Manage Internships</h2>
                <button onclick="location.reload()">Refresh</button>
                <table>
                    <tr>
                        <th>Title</th>
                        <th>Posted Date</th>
                        <th>Deadline</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php 
                    if($internships_result):
                       
                        mysqli_data_seek($internships_result, 0);
                        if(mysqli_num_rows($internships_result) > 0): 
                            while($internship = mysqli_fetch_assoc($internships_result)): 
                                $posted_date = date('Y-m-d', strtotime($internship['created_at']));
                                $deadline = date('Y-m-d', strtotime($internship['application_deadline']));
                                $status_class = $internship['status'] == 'open' ? 'success' : 'danger';
                                $status_text = $internship['status'] == 'open' ? 'Open' : 'Closed';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($internship['title']); ?></td>
                        <td><?php echo $posted_date; ?></td>
                        <td><?php echo $deadline; ?></td>
                        <td><?php echo $internship['duration_months']; ?> months</td>
                        <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <button class="btn-small warning" onclick="editInternship(<?php echo $internship['id']; ?>)">Edit</button>
                            <?php if($internship['status'] == 'open'): ?>
                                <button class="btn-small danger" onclick="closeInternship(<?php echo $internship['id']; ?>)">Close</button>
                            <?php else: ?>
                                <button class="btn-small" onclick="viewInternship(<?php echo $internship['id']; ?>)">View</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6">No internships posted yet.</td>
                    </tr>
                    <?php endif; endif; ?>
                </table>
            </div>

           
            <div id="applications" class="page">
                <h2>Applications</h2>
                <p>No applications yet. Post internships to receive applications.</p>
            </div>

            <div id="offers" class="page">
                <h2>Job Offers Sent</h2>
                <p>No job offers sent yet.</p>
            </div>

           <div id="profile" class="page">

    <div class="profile-header">
        <h1><i class="fas fa-building"></i> Company Profile</h1>
        <button class="edit-profile-btn" onclick="toggleEditProfile()">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>
    
    <div class="profile-container">
   
        <div id="profileView" class="profile-view">
            <div class="profile-card">
                <div class="profile-avatar-section">
                    <div class="profile-avatar">
                        <?php if(!empty($company['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo" class="company-logo">
                        <?php else: ?>
                            <i class="fas fa-building fa-4x"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-basic-info">
                        <h2><?php echo htmlspecialchars($company['company_name'] ?? 'Company Name'); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($company['email'] ?? 'email@company.com'); ?></p>
                        <p class="profile-contact">
                            <?php if(!empty($company['phone'])): ?>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($company['phone']); ?> &nbsp;&nbsp;
                            <?php endif; ?>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['address'] ?? 'Address not set'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="profile-action-btn" onclick="toggleEditProfile()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <button class="profile-action-btn" onclick="showLogoUpload()">
                        <i class="fas fa-camera"></i> Change Logo
                    </button>
                </div>
            </div>
            
          
            <div class="info-section">
                <h3><i class="fas fa-info-circle"></i> Company Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-industry"></i> Industry</span>
                        <span class="info-value"><?php echo htmlspecialchars($company['industry'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-users"></i> Company Size</span>
                        <span class="info-value"><?php echo htmlspecialchars($company['company_size'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Founded Year</span>
                        <span class="info-value"><?php echo htmlspecialchars($company['founded_year'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-globe"></i> Website</span>
                        <span class="info-value">
                            <?php if(!empty($company['website'])): ?>
                                <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($company['website']); ?>
                                </a>
                            <?php else: ?>
                                Not specified
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
         
            <div class="info-section">
                <h3><i class="fas fa-file-alt"></i> Company Description</h3>
                <div class="description-box">
                    <?php echo !empty($company['description']) ? nl2br(htmlspecialchars($company['description'])) : 'No description provided. Click Edit Profile to add a company description.'; ?>
                </div>
            </div>
            
  
            <div class="stats-section">
                <h3><i class="fas fa-chart-bar"></i> Company Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?php echo $stats['total_internships'] ?? 0; ?></h4>
                            <p>Total Internships</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h4><?php echo $stats['active_internships'] ?? 0; ?></h4>
                            <p>Active Internships</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <h4>0</h4>
                            <p>Hired Interns</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h4>4.5</h4>
                            <p>Company Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    
        <div id="profileEdit" class="profile-edit" style="display: none;">
            <h3><i class="fas fa-edit"></i> Edit Company Profile</h3>
            
            <form action="update_profile.php" method="POST" id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
                
                
                <div class="form-section">
                    <h4><i class="fas fa-camera"></i> Company Logo</h4>
                    <div class="logo-upload-container">
                        <div class="current-logo">
                            <?php if(!empty($company['logo'])): ?>
                                <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Current Logo">
                            <?php else: ?>
                                <div class="no-logo">
                                    <i class="fas fa-building fa-3x"></i>
                                    <p>No logo uploaded</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="upload-controls">
                            <input type="file" name="logo" id="logoUpload" accept="image/*" class="file-input" onchange="previewLogo(event)">
                            <label for="logoUpload" class="upload-btn">
                                <i class="fas fa-upload"></i> Upload New Logo
                            </label>
                            <div class="upload-hint">Max size: 2MB • Formats: JPG, PNG, GIF</div>
                            <div id="logoPreview" class="logo-preview"></div>
                        </div>
                    </div>
                </div>
                
                
                <div class="form-section">
                    <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Company Name *</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['company_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-industry"></i> Industry *</label>
                            <input type="text" name="industry" value="<?php echo htmlspecialchars($company['industry'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
               
                <div class="form-section">
                    <h4><i class="fas fa-building"></i> Company Details</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Company Size</label>
                            <select name="company_size">
                                <option value="">Select Size</option>
                                <option value="1-10" <?php echo (isset($company['company_size']) && $company['company_size'] == '1-10') ? 'selected' : ''; ?>>1-10 employees</option>
                                <option value="11-50" <?php echo (isset($company['company_size']) && $company['company_size'] == '11-50') ? 'selected' : ''; ?>>11-50 employees</option>
                                <option value="51-200" <?php echo (isset($company['company_size']) && $company['company_size'] == '51-200') ? 'selected' : ''; ?>>51-200 employees</option>
                                <option value="201-500" <?php echo (isset($company['company_size']) && $company['company_size'] == '201-500') ? 'selected' : ''; ?>>201-500 employees</option>
                                <option value="501-1000" <?php echo (isset($company['company_size']) && $company['company_size'] == '501-1000') ? 'selected' : ''; ?>>501-1000 employees</option>
                                <option value="1000+" <?php echo (isset($company['company_size']) && $company['company_size'] == '1000+') ? 'selected' : ''; ?>>1000+ employees</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Founded Year</label>
                            <input type="number" name="founded_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($company['founded_year'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-globe"></i> Website</label>
                            <input type="url" name="website" value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>" placeholder="https://example.com">
                        </div>
                    </div>
                </div>
                
              
                <div class="form-section">
                    <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
                    <div class="form-group">
                        <textarea name="address" rows="3" placeholder="Enter company address"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                    </div>
                </div>
                
              
                <div class="form-section">
                    <h4><i class="fas fa-file-alt"></i> Company Description</h4>
                    <div class="form-group">
                        <textarea name="description" rows="5" placeholder="Describe your company, mission, values, and culture"><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
         
                <div class="form-actions">
                    <button type="submit" class="save-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="cancel-btn" onclick="toggleEditProfile()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="logoUploadModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-camera"></i> Upload Company Logo</h3>
        <form action="upload_logo.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="company_id" value="<?php echo $company_id; ?>">
            
            <div class="upload-area" id="dropArea">
                <i class="fas fa-cloud-upload-alt fa-3x"></i>
                <p>Drag & drop your logo here</p>
                <p class="upload-or">or</p>
                <input type="file" name="logo" id="modalLogoUpload" accept="image/*" style="display: none;">
                <label for="modalLogoUpload" class="browse-btn">
                    <i class="fas fa-folder-open"></i> Browse Files
                </label>
                <div class="upload-hint">Supported formats: JPG, PNG, GIF • Max size: 2MB</div>
                <div id="modalLogoPreview" class="modal-preview"></div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="upload-submit-btn">
                    <i class="fas fa-upload"></i> Upload Logo
                </button>
                <button type="button" class="cancel-btn" onclick="closeLogoModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>
        </div>
    </div>


    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Internship</h3>
            <form action="manage_internship.php" method="POST">
                <input type="hidden" id="editId" name="id">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="editTitle" name="title">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="editDesc" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Skills</label>
                    <input type="text" id="editSkills" name="skills">
                </div>
                <div class="btn-group">
                    <button type="submit" name="action" value="update">Save</button>
                    <button type="button" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show page function
        function showPage(pageId) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => {
                page.classList.remove('active');
            });
            
            // Remove active from all nav links
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected page
            document.getElementById(pageId).classList.add('active');
            
            // Set active nav link
            const navLinks = {
                'dashboard': 0,
                'post': 1,
                'manage': 2,
                'applications': 3,
                'offers': 4,
                'profile': 5
            };
            document.querySelectorAll('.sidebar li a')[navLinks[pageId]].classList.add('active');
            
            // Set page title
            const titles = {
                'dashboard': 'Dashboard',
                'post': 'Post Internship',
                'manage': 'Manage Internships',
                'applications': 'Applications',
                'offers': 'Job Offers',
                'profile': 'Profile'
            };
            document.getElementById('pageTitle').textContent = titles[pageId];
            
            // Hide any popups when navigating
            closeAllPopups();
        }

        // Calculate duration
        function calculateDuration() {
            const start = document.getElementById('start_date');
            const end = document.getElementById('end_date');
            const duration = document.getElementById('duration');
            
            if (start && end && duration && start.value && end.value) {
                const startDate = new Date(start.value);
                const endDate = new Date(end.value);
                const months = (endDate.getFullYear() - startDate.getFullYear()) * 12 + 
                              (endDate.getMonth() - startDate.getMonth());
                const days = endDate.getDate() - startDate.getDate();
                const decimalMonths = months + (days / 30);
                duration.value = decimalMonths.toFixed(1) + ' months';
            }
        }

        // Show popup
        function showPopup(popupId) {
            const popup = document.getElementById(popupId);
            if (popup) {
                popup.style.display = 'flex';
            }
        }

        // Close popup
        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            if (popup) {
                popup.style.display = 'none';
                // Automatically go to dashboard after closing popup
                showPage('dashboard');
            }
        }

        // Close all popups
        function closeAllPopups() {
            document.querySelectorAll('.popup-overlay').forEach(popup => {
                popup.style.display = 'none';
            });
        }

        // Edit internship
        function editInternship(id) {
            document.getElementById('editId').value = id;
            document.getElementById('editModal').style.display = 'block';
        }

        // Close internship
        function closeInternship(id) {
            if (confirm('Are you sure you want to close this internship?')) {
                window.location.href = 'manage_internship.php?action=close&id=' + id;
            }
        }

        // View internship
        function viewInternship(id) {
            alert('Viewing internship ID: ' + id);
        }

        // Close modal
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Logout - UPDATED
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                // Redirect to current page with logout parameter
                const currentUrl = window.location.href.split('?')[0];
                window.location.href = currentUrl + '?logout=true';
            }
        }

        // Form validation
        function validateForm() {
            const title = document.getElementById('title');
            const description = document.getElementById('description');
            const skills = document.getElementById('skills');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const deadline = document.getElementById('deadline');
            const submitBtn = document.getElementById('submitBtn');
            
            if (!title || !description || !skills || !startDate || !endDate || !deadline) {
                return true; // Let server handle validation
            }
            
            // Basic validation
            if (!title.value.trim() || !description.value.trim() || !skills.value.trim() || 
                !startDate.value || !endDate.value || !deadline.value) {
                alert('Please fill all required fields');
                return false;
            }
            
            // Date validation
            const today = new Date().toISOString().split('T')[0];
            if (deadline.value < today) {
                alert('Application deadline must be in the future');
                return false;
            }
            
            if (startDate.value >= endDate.value) {
                alert('End date must be after start date');
                return false;
            }
            
            // Show loading on button
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Posting...';
            }
            
            return true;
        }

      
        window.onload = function() {
            
            const today = new Date();
            
            
            const startDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            const startInput = document.getElementById('start_date');
            if (startInput) {
                startInput.value = startDate.toISOString().split('T')[0];
            }
            
            
            const endDate = new Date(startDate.getFullYear(), startDate.getMonth() + 6, 1);
            const endInput = document.getElementById('end_date');
            if (endInput) {
                endInput.value = endDate.toISOString().split('T')[0];
            }
            
          
            const deadline = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 14);
            const deadlineInput = document.getElementById('deadline');
            if (deadlineInput) {
                deadlineInput.value = deadline.toISOString().split('T')[0];
            }
            
            calculateDuration();
            
            
            <?php if($success_message): ?>
            setTimeout(() => {
                showPage('dashboard');
            }, 100);
            <?php endif; ?>
            
            // Add form validation
            const form = document.getElementById('internshipForm');
            if (form) {
                form.onsubmit = validateForm;
            }
        };

        // Close popup 
        window.onclick = function(event) {
            if (event.target.classList.contains('popup-overlay')) {
                event.target.style.display = 'none';
                showPage('dashboard');
            }
            if (event.target.className === 'modal') {
                closeModal();
            }
        };
    </script>
</body>
</html>
<?php 

mysqli_close($conn); 
?>