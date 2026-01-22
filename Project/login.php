<?php
session_start();
include "db.php";

$error = '';


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}


if (isset($_SESSION['user_type']) && !isset($_POST['login'])) {
    
    if ($_SESSION['user_type'] == 'student') {
        header("Location: student.php");
    } elseif ($_SESSION['user_type'] == 'company') {
        header("Location: company.php");
    } elseif ($_SESSION['user_type'] == 'admin') {
        header("Location: admin.php");
    }
    exit();
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

   
    if ($email === 'admin@internconnectbd.com' && $password === 'admin@123') {
        
        session_destroy();
        session_start();
        
              $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['email'] = 'admin@internconnectbd.com';
        $_SESSION['user_type'] = 'admin';
        $_SESSION['login_time'] = time();
        
        
        recordUserLogin($conn, 'admin@internconnectbd.com', 'admin', 1);
        
        header("Location: admin.php");
        exit();
    }

    // Regular user login logic
    if ($user_type == 'student') {
        // Check student table
        $query = "SELECT * FROM student_reg WHERE Email='$email'";
        $result = mysqli_query($conn, $query);
        
        $redirect = 'student.php';
        $id_field = 'Student_id';
        $name_field = 'Full_name';
        $role = 'student';
    } else {
        // Check company table
        $query = "SELECT * FROM company_reg WHERE email='$email'";
        $result = mysqli_query($conn, $query);
        
        $redirect = 'company.php';
        $id_field = 'company_id';
        $name_field = 'company_name';
        $role = 'company';
    }

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Get password based on user type
        if ($user_type == 'student') {
            $db_password = isset($row['PASSWORD']) ? $row['PASSWORD'] : (isset($row['password']) ? $row['password'] : '');
        } else {
            $db_password = isset($row['password']) ? $row['password'] : '';
        }
        
        if ($db_password) {
            // Check password
            $password_valid = false;
            
           
            if (password_verify($password, $db_password)) {
                $password_valid = true;
            }
            
            elseif ($password === $db_password) {
                $password_valid = true;
            }
            
            if ($password_valid) {
              
                session_destroy();
                session_start();
                
                
                $_SESSION['user_id'] = $row[$id_field];
                $_SESSION['user_name'] = $row[$name_field];
                $_SESSION['email'] = $row['email'] ?? $row['Email'] ?? '';
                $_SESSION['user_type'] = $user_type;
                
               
                $_SESSION['login_time'] = time();
                
               
                recordUserLogin($conn, $email, $role, $row[$id_field]);
                
               
                header("Location: $redirect");
                exit();
            } else {
                $error = "Invalid email or password!";
            }
        } else {
            $error = "Password not found in database!";
        }
    } else {
        $error = "User not found! Please check your email.";
    }
}


function recordUserLogin($conn, $email, $role, $original_id) {
    $current_time = date('Y-m-d H:i:s');
    
    
    $check_query = "SELECT * FROM users WHERE email = '$email' AND role = '$role'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing user's last login
        $update_query = "UPDATE users SET 
                        last_login = '$current_time',
                        updated_at = '$current_time',
                        status = 'active'
                        WHERE email = '$email' AND role = '$role'";
        mysqli_query($conn, $update_query);
    } else {
       
        $password_query = "";
        if ($role == 'student') {
            $password_query = "SELECT PASSWORD FROM student_reg WHERE Email = '$email'";
        } elseif ($role == 'company') {
            $password_query = "SELECT password FROM company_reg WHERE email = '$email'";
        } elseif ($role == 'admin') {
            $password_query = "SELECT password FROM users WHERE email = '$email' AND role = 'admin'";
        }
        
        if ($role != 'admin') {
            $password_result = mysqli_query($conn, $password_query);
            $password_row = mysqli_fetch_assoc($password_result);
            $user_password = $password_row['password'] ?? $password_row['PASSWORD'] ?? '';
            
            
            $insert_query = "INSERT INTO users (email, password, role, status, last_login, updated_at) 
                            VALUES ('$email', '$user_password', '$role', 'active', '$current_time', '$current_time')";
            mysqli_query($conn, $insert_query);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InternConnectBD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
    
        .header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: blue;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .logo span {
            color: green;
        }
        
        
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-title {
            color: darkblue;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }
        
        .error-message {
            background: #ffe6e6;
            color: #cc0000;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ff9999;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }
        
       
        .user-type-tabs {
            display: flex;
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .user-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            cursor: pointer;
            border-right: 1px solid #ddd;
            font-weight: bold;
            color: #666;
        }
        
        .user-tab:last-child {
            border-right: none;
        }
        
        .user-tab.active {
            background: #2a5298;
            color: white;
        }
        
        .user-tab input {
            display: none;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        .form-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: blue;
            box-shadow: 0 0 5px rgba(0,0,255,0.2);
        }
        
        .login-btn {
            width: 100%;
            background: blue;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            transition: background 0.3s;
        }
        
        .login-btn:hover {
            background: darkblue;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .signup-link a {
            color: blue;
            text-decoration: none;
            font-weight: bold;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .footer {
            background: rgba(77, 106, 119, 1);
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: auto;
        }
        
        .copyright {
            font-size: 14px;
        }
        
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
        
        
        .force-logout {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 5px;
        }
        
        .force-logout a {
            color: #856404;
            text-decoration: none;
            font-weight: bold;
        }
        
        .force-logout a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <div class="header">
        <a href="index.php" class="logo">InternConnect<span>BD</span></a>
        <?php if (isset($_SESSION['user_type'])): ?>
        <div style="color: #666;">
            Currently logged in as: <?php echo $_SESSION['user_type']; ?>
            <a href="login.php?logout=true" style="color: red; margin-left: 10px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="login-container">
        <h2 class="login-title">Sign In</h2>
        
        <?php if (isset($_SESSION['user_type'])): ?>
        <div class="force-logout">
            <p>You are already logged in as <strong><?php echo $_SESSION['user_type']; ?></strong></p>
            <p>Click <a href="login.php?logout=true">here to logout</a> or 
                <a href="<?php 
                    if ($_SESSION['user_type'] == 'student') echo 'student.php';
                    elseif ($_SESSION['user_type'] == 'company') echo 'company.php';
                    else echo 'admin.php';
                ?>">go to dashboard</a>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <!-- User Type Tabs -->
            <div class="user-type-tabs">
                <label class="user-tab active" id="studentTab">
                    <input type="radio" name="user_type" value="student" checked>
                    Student
                </label>
                <label class="user-tab" id="companyTab">
                    <input type="radio" name="user_type" value="company">
                    Company
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-input" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="login" class="login-btn">SIGN IN</button>
        </form>
        
        <div class="signup-link">
            <p>Don't have an account? <a href="register.php" id="signupLink">Create Account</a></p>
        </div>
    </div>

    
    <div class="footer">
        <p class="copyright">© 2025 InternConnectBD. All rights reserved.</p>
    </div>

    <script>
        // User type tabs
        const studentTab = document.getElementById('studentTab');
        const companyTab = document.getElementById('companyTab');
        
        if (studentTab) {
            studentTab.addEventListener('click', () => {
                selectUserType('student');
            });
        }
        
        if (companyTab) {
            companyTab.addEventListener('click', () => {
                selectUserType('company');
            });
        }
        
        function selectUserType(type) {
            // Update tabs
            if (studentTab) studentTab.classList.remove('active');
            if (companyTab) companyTab.classList.remove('active');
            
            if (type === 'student') {
                if (studentTab) studentTab.classList.add('active');
                document.querySelector('input[value="student"]').checked = true;
               
                document.getElementById('signupLink').href = 'register.php';
            } else {
                if (companyTab) companyTab.classList.add('active');
                document.querySelector('input[value="company"]').checked = true;
              
                document.getElementById('signupLink').href = 'register.php';
            }
        }
        
        
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput && toggleIcon) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        }
    </script>
    <script>

localStorage.removeItem('studentData');
</script>
</body>
</html>
