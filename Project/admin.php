<?php
session_start();
include 'db.php';
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

//  total students 
$student_query = "SELECT COUNT(*) as total FROM student_reg";
$student_result = mysqli_query($conn, $student_query);
$student_row = mysqli_fetch_assoc($student_result);
$total_students = $student_row['total'];

// total companies 
$company_query = "SELECT COUNT(*) as total FROM company_reg";
$company_result = mysqli_query($conn, $company_query);
$company_row = mysqli_fetch_assoc($company_result);
$total_companies = $company_row['total'];


$pending_query = "SELECT COUNT(*) as total FROM company_reg WHERE LOWER(status) NOT IN ('verified', 'rejected')";
$pending_result = mysqli_query($conn, $pending_query);
$pending_row = mysqli_fetch_assoc($pending_result);
$pending_companies = $pending_row['total'];


$verified_query = "SELECT COUNT(*) as total FROM company_reg WHERE LOWER(status) = 'verified'";
$verified_result = mysqli_query($conn, $verified_query);
$verified_row = mysqli_fetch_assoc($verified_result);
$verified_companies = $verified_row['total'];



$recent_students_query = "SELECT * FROM student_reg LIMIT 5";
$recent_students_result = mysqli_query($conn, $recent_students_query);


$recent_companies_query = "SELECT * FROM company_reg ORDER BY created_at DESC LIMIT 5";
$recent_companies_result = mysqli_query($conn, $recent_companies_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

    
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #34495e;
        }

        .menu {
            list-style: none;
        }

        .menu li {
            padding: 15px 25px;
            border-left: 4px solid transparent;
        }

        .menu li:hover {
            background-color: #34495e;
            border-left: 4px solid #3498db;
        }

        .menu li.active {
            background-color: #34495e;
            border-left: 4px solid #3498db;
        }

        .menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .menu i {
            margin-right: 10px;
            width: 20px;
        }

        .logout {
            margin-top: 30px;
            border-top: 1px solid #34495e;
            padding-top: 20px;
        }

      
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header p {
            color: #7f8c8d;
        }

       
        .stats {
            margin-bottom: 30px;
        }

        .stats h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-box {
            background-color: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #3498db;
        }

        .stat-box:nth-child(2) {
            border-top-color: #2ecc71;
        }

        .stat-box:nth-child(3) {
            border-top-color: #f39c12;
        }

        .stat-box:nth-child(4) {
            border-top-color: #e74c3c;
        }

        .stat-box h3 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .stat-box p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .stat-box i {
            font-size: 24px;
            color: #3498db;
            margin-bottom: 15px;
        }

        .stat-box:nth-child(2) i {
            color: #2ecc71;
        }

        .stat-box:nth-child(3) i {
            color: #f39c12;
        }

        .stat-box:nth-child(4) i {
            color: #e74c3c;
        }


        .recent-activity {
            margin-bottom: 30px;
        }

        .recent-activity h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .activity-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .activity-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .activity-list {
            list-style: none;
        }

        .activity-list li {
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            align-items: center;
        }

        .activity-list li:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
        }

        .activity-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .activity-info p {
            color: #7f8c8d;
            font-size: 12px;
        }

        .activity-time {
            margin-left: auto;
            color: #95a5a6;
            font-size: 11px;
            white-space: nowrap;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-left: 8px;
        }

        .status-active {
            background-color: #3498db;
            color: white;
        }

        .status-verified {
            background-color: #2ecc71;
            color: white;
        }

        .status-rejected {
            background-color: #e74c3c;
            color: white;
        }

       
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #95a5a6;
            font-style: italic;
        }

       
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .activity-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .menu {
                display: flex;
                overflow-x: auto;
            }
            
            .menu li {
                white-space: nowrap;
            }
            
            .logout {
                margin-top: 0;
                border-top: none;
                border-left: 1px solid #34495e;
                padding-top: 15px;
                padding-left: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul class="menu">
                <li class="active">
                    <a href="admin.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="company-verification.php">
                        <i class="fas fa-building"></i>
                        Company Verification
                    </a>
                </li>
                <li class="logout">
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Admin Dashboard</h1>
                <p>Welcome to the internship management system</p>
            </div>

            <!-- Stats Section -->
            <div class="stats">
                <h2>System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-box">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-building"></i>
                        <h3><?php echo $total_companies; ?></h3>
                        <p>Total Companies</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-clock"></i>
                        <h3><?php echo $pending_companies; ?></h3>
                        <p>Pending Verification</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-check-circle"></i>
                        <h3><?php echo $verified_companies; ?></h3>
                        <p>Verified Companies</p>
                    </div>
                </div>
            </div>

          
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-grid">
                 
                    <div class="activity-card">
                        <h3>Recent Students</h3>
                        <?php if (mysqli_num_rows($recent_students_result) > 0): ?>
                            <ul class="activity-list">
                                <?php while($student = mysqli_fetch_assoc($recent_students_result)): 
                                    // Try to find name and email columns dynamically
                                    $student_name = 'Student';
                                    $student_email = 'No email';
                                    
                                    // Check common name column names
                                    if (isset($student['name'])) {
                                        $student_name = htmlspecialchars($student['name']);
                                    } elseif (isset($student['student_name'])) {
                                        $student_name = htmlspecialchars($student['student_name']);
                                    } elseif (isset($student['full_name'])) {
                                        $student_name = htmlspecialchars($student['full_name']);
                                    } elseif (isset($student['username'])) {
                                        $student_name = htmlspecialchars($student['username']);
                                    }
                                    
                                    // Check common email column names
                                    if (isset($student['email'])) {
                                        $student_email = htmlspecialchars($student['email']);
                                    } elseif (isset($student['student_email'])) {
                                        $student_email = htmlspecialchars($student['student_email']);
                                    } elseif (isset($student['email_address'])) {
                                        $student_email = htmlspecialchars($student['email_address']);
                                    }
                                    
                                    // Try to find an ID for display
                                    $student_id = '';
                                    if (isset($student['student_id'])) {
                                        $student_id = $student['student_id'];
                                    } elseif (isset($student['id'])) {
                                        $student_id = $student['id'];
                                    } elseif (isset($student['user_id'])) {
                                        $student_id = $student['user_id'];
                                    }
                                ?>
                                    <li>
                                        <div class="activity-icon" style="background-color: #3498db;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="activity-info">
                                            <h4><?php echo $student_name; ?></h4>
                                            <p><?php echo $student_email; ?></p>
                                        </div>
                                        <?php if (!empty($student_id)): ?>
                                            <div class="activity-time">
                                                ID: <?php echo $student_id; ?>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-state">No students found</p>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Companies -->
                    <div class="activity-card">
                        <h3>Recent Companies</h3>
                        <?php if (mysqli_num_rows($recent_companies_result) > 0): ?>
                            <ul class="activity-list">
                                <?php while($company = mysqli_fetch_assoc($recent_companies_result)): 
                                    $status = strtolower($company['status']);
                                    $status_class = 'status-active';
                                    $status_text = 'Active';
                                    
                                    if($status == 'verified') {
                                        $status_class = 'status-verified';
                                        $status_text = 'Verified';
                                    } elseif($status == 'rejected') {
                                        $status_class = 'status-rejected';
                                        $status_text = 'Rejected';
                                    }
                                ?>
                                    <li>
                                        <div class="activity-icon" style="background-color: #2ecc71;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="activity-info">
                                            <h4>
                                                <?php echo htmlspecialchars($company['company_name']); ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </h4>
                                            <p><?php echo htmlspecialchars($company['email']); ?></p>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('M d', strtotime($company['created_at'])); ?>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-state">No recent companies</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
<?php
mysqli_close($conn);