<?php
session_start();
include "db.php";
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Handle form actions
$message = '';

// Handle verify action
if(isset($_GET['verify'])) {
    $id = intval($_GET['verify']);
    $sql = "UPDATE company_reg SET status = 'verified' WHERE company_id = $id";
    if(mysqli_query($conn, $sql)) {
        $message = "Company verified successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
    header("Location: company-verification.php?message=" . urlencode($message));
    exit();
}

// Handle reject action
if(isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $sql = "UPDATE company_reg SET status = 'rejected' WHERE company_id = $id";
    if(mysqli_query($conn, $sql)) {
        $message = "Company rejected successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
    header("Location: company-verification.php?message=" . urlencode($message));
    exit();
}

// Handle delete action
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM company_reg WHERE company_id = $id";
    if(mysqli_query($conn, $sql)) {
        $message = "Company deleted successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
    header("Location: company-verification.php?message=" . urlencode($message));
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
    
    if ($company_id > 0) {
        // Update existing company
        $sql = "UPDATE company_reg SET 
                company_name = '$company_name',
                email = '$email',
                phone = '$phone',
                location = '$location',
                description = '$description'
                WHERE company_id = $company_id";
        $action_message = "Company updated successfully!";
    } else {
        // Add new company 
        $sql = "INSERT INTO company_reg (company_name, email, phone, location, description, status) 
                VALUES ('$company_name', '$email', '$phone', '$location', '$description', 'active')";
        $action_message = "Company added successfully!";
    }
    
    if (mysqli_query($conn, $sql)) {
        $message = $action_message;
        header("Location: company-verification.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Get company details for edit
$edit_company = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM company_reg WHERE company_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    if (mysqli_num_rows($edit_result) > 0) {
        $edit_company = mysqli_fetch_assoc($edit_result);
    }
}


$query = "SELECT * FROM company_reg ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Verification</title>
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

   
        .alert {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

      
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

  
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .table-container h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: bold;
            white-space: nowrap;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

       
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            display: inline-block;
        }

        .status-active {
            background-color: #3498db;
            color: white;
            border: 1px solid #2980b9;
        }

        .status-verified {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

   
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

    
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
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
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
      
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul class="menu">
                <li>
                    <a href="admin.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="active">
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

   
        <div class="main-content">
         
            <div class="header">
                <h1>Company Verification</h1>
                <p>Manage company registrations and verification status</p>
            </div>

           
            <?php if (isset($_GET['message'])): ?>
                <div class="alert"><?php echo htmlspecialchars($_GET['message']); ?></div>
            <?php endif; ?>

          
            <div class="form-container">
                <h2><?php echo $edit_company ? 'Edit Company' : 'Add New Company'; ?></h2>
                <form method="POST" action="">
                    <?php if ($edit_company): ?>
                        <input type="hidden" name="company_id" value="<?php echo $edit_company['company_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" required 
                               value="<?php echo $edit_company ? htmlspecialchars($edit_company['company_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $edit_company ? htmlspecialchars($edit_company['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required 
                               value="<?php echo $edit_company ? htmlspecialchars($edit_company['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location"
                               value="<?php echo $edit_company ? htmlspecialchars($edit_company['location']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo $edit_company ? htmlspecialchars($edit_company['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $edit_company ? 'Update Company' : 'Add Company'; ?>
                        </button>
                        
                        <?php if ($edit_company): ?>
                            <a href="company-verification.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Company Table -->
            <div class="table-container">
                <h2>Company Registrations</h2>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                $status = $row['status'];
                                $status_lower = strtolower($status);
                            ?>
                                <tr>
                                    <td><?php echo $row['company_id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td>
                                        <?php 
                                        if($status_lower == 'verified') {
                                            echo '<span class="status-badge status-verified">Verified</span>';
                                        } elseif($status_lower == 'rejected') {
                                            echo '<span class="status-badge status-rejected">Rejected</span>';
                                        } else {
                                            echo '<span class="status-badge status-active">Active</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php 
                                            
                                            if($status_lower != 'verified' && $status_lower != 'rejected'): ?>
                                                <a href="company-verification.php?verify=<?php echo $row['company_id']; ?>" 
                                                   class="btn btn-success" 
                                                   onclick="return confirm('Verify this company?')">
                                                    <i class="fas fa-check"></i> Accept
                                                </a>
                                                <a href="company-verification.php?reject=<?php echo $row['company_id']; ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Reject this company?')">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="company-verification.php?edit=<?php echo $row['company_id']; ?>" 
                                               class="btn btn-info">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <a href="company-verification.php?delete=<?php echo $row['company_id']; ?>" 
                                               class="btn btn-warning" 
                                               onclick="return confirm('Delete this company permanently?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-building"></i>
                        <h3>No Companies Found</h3>
                        <p>There are no company registrations to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
<?php

mysqli_close($conn);
?>