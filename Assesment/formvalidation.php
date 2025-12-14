<!DOCTYPE html>
<html>
<head>
    <style>
        .error {
            color: #FF0000;
        }
    </style>
</head>
<body>

<?php
// Function to sanitize input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!-- FORM 1: NAME VALIDATION -->
<h2>1. Name Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    NAME: <input type="text" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_name"])) {
            if (empty($_POST["name"])) {
                echo "Name is required";
            } else {
                $name = test_input($_POST["name"]);
                // Contains at least two words
                if (str_word_count($name) < 2) {
                    echo "Name must contain at least two words";
                }
                // Must start with a letter
                elseif (!preg_match("/^[a-zA-Z]/", $name)) {
                    echo "Name must start with a letter";
                }
                // Can contain a-z, A-Z, period, dash only
                elseif (!preg_match("/^[a-zA-Z\.\-\s]+$/", $name)) {
                    echo "Only letters, period, dash and spaces allowed";
                }
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_name" value="Submit">
</form>

<?php
// Display result for Name
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_name"])) {
    if (!empty($_POST["name"])) {
        $name = test_input($_POST["name"]);
        if (str_word_count($name) >= 2 && preg_match("/^[a-zA-Z]/", $name) && preg_match("/^[a-zA-Z\.\-\s]+$/", $name)) {
            echo "<h3>Your Input:</h3>";
            echo "Name: " . $name;
        }
    }
}
?>

<hr>

<!-- FORM 2: EMAIL VALIDATION -->
<h2>2. Email Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    EMAIL: <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_email"])) {
            if (empty($_POST["email"])) {
                echo "Email is required";
            } else {
                $email = test_input($_POST["email"]);
                // Must be a valid email address
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo "Invalid email format (i.e anything@example.com)";
                }
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_email" value="Submit">
</form>

<?php
// Display result for Email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_email"])) {
    if (!empty($_POST["email"])) {
        $email = test_input($_POST["email"]);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<h3>Your Input:</h3>";
            echo "Email: " . $email;
        }
    }
}
?>

<hr>

<!-- FORM 3: DATE OF BIRTH VALIDATION -->
<h2>3. Date of Birth Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    DATE OF BIRTH: 
    <input type="text" name="dd" size="2" placeholder="dd" value="<?php echo isset($_POST['dd']) ? $_POST['dd'] : ''; ?>"> /
    <input type="text" name="mm" size="2" placeholder="mm" value="<?php echo isset($_POST['mm']) ? $_POST['mm'] : ''; ?>"> /
    <input type="text" name="yyyy" size="4" placeholder="yyyy" value="<?php echo isset($_POST['yyyy']) ? $_POST['yyyy'] : ''; ?>">
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_dob"])) {
            $dd = isset($_POST["dd"]) ? test_input($_POST["dd"]) : "";
            $mm = isset($_POST["mm"]) ? test_input($_POST["mm"]) : "";
            $yyyy = isset($_POST["yyyy"]) ? test_input($_POST["yyyy"]) : "";
            
            if (empty($dd) || empty($mm) || empty($yyyy)) {
                echo "Date of Birth is required";
            } else {
                if (!is_numeric($dd) || !is_numeric($mm) || !is_numeric($yyyy)) {
                    echo "Must be valid numbers";
                } else {
                    $dd_int = (int)$dd;
                    $mm_int = (int)$mm;
                    $yyyy_int = (int)$yyyy;
                    
                    if ($dd_int < 1 || $dd_int > 31) {
                        echo "Day must be 1-31";
                    } elseif ($mm_int < 1 || $mm_int > 12) {
                        echo "Month must be 1-12";
                    } elseif ($yyyy_int < 1953 || $yyyy_int > 1998) {
                        echo "Year must be 1953-1998";
                    } elseif (!checkdate($mm_int, $dd_int, $yyyy_int)) {
                        echo "Invalid date";
                    }
                }
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_dob" value="Submit">
</form>

<?php
// Display result for Date of Birth
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_dob"])) {
    $dd = isset($_POST["dd"]) ? test_input($_POST["dd"]) : "";
    $mm = isset($_POST["mm"]) ? test_input($_POST["mm"]) : "";
    $yyyy = isset($_POST["yyyy"]) ? test_input($_POST["yyyy"]) : "";
    
    if (!empty($dd) && !empty($mm) && !empty($yyyy)) {
        if (is_numeric($dd) && is_numeric($mm) && is_numeric($yyyy)) {
            $dd_int = (int)$dd;
            $mm_int = (int)$mm;
            $yyyy_int = (int)$yyyy;
            
            if ($dd_int >= 1 && $dd_int <= 31 && 
                $mm_int >= 1 && $mm_int <= 12 && 
                $yyyy_int >= 1953 && $yyyy_int <= 1998 && 
                checkdate($mm_int, $dd_int, $yyyy_int)) {
                echo "<h3>Your Input:</h3>";
                echo "Date of Birth: " . $dd . "/" . $mm . "/" . $yyyy;
            }
        }
    }
}
?>

<hr>

<!-- FORM 4: GENDER VALIDATION -->
<h2>4. Gender Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <input type="radio" name="gender" value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender']=="Male") ? "checked" : ""; ?>> Male<br>
    <input type="radio" name="gender" value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender']=="Female") ? "checked" : ""; ?>> Female<br>
    <input type="radio" name="gender" value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender']=="Other") ? "checked" : ""; ?>> Other<br>
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_gender"])) {
            if (empty($_POST["gender"])) {
                echo "Gender is required";
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_gender" value="Submit">
</form>

<?php
// Display result for Gender
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_gender"])) {
    if (!empty($_POST["gender"])) {
        $gender = test_input($_POST["gender"]);
        echo "<h3>Your Input:</h3>";
        echo "Gender: " . $gender;
    }
}
?>

<hr>

<!-- FORM 5: DEGREE VALIDATION -->
<h2>5. Degree Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <input type="checkbox" name="degree[]" value="SSC" <?php echo (isset($_POST['degree']) && in_array("SSC", $_POST['degree'])) ? "checked" : ""; ?>> SSC<br>
    <input type="checkbox" name="degree[]" value="HSC" <?php echo (isset($_POST['degree']) && in_array("HSC", $_POST['degree'])) ? "checked" : ""; ?>> HSC<br>
    <input type="checkbox" name="degree[]" value="BSc" <?php echo (isset($_POST['degree']) && in_array("BSc", $_POST['degree'])) ? "checked" : ""; ?>> BSc<br>
    <input type="checkbox" name="degree[]" value="MSc" <?php echo (isset($_POST['degree']) && in_array("MSc", $_POST['degree'])) ? "checked" : ""; ?>> MSc<br>
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_degree"])) {
            if (!isset($_POST["degree"])) {
                echo "At least two degrees must be selected";
            } else {
                $degrees = $_POST["degree"];
                if (count($degrees) < 2) {
                    echo "At least two degrees must be selected";
                }
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_degree" value="Submit">
</form>

<?php
// Display result for Degree
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_degree"])) {
    if (isset($_POST["degree"])) {
        $degrees = $_POST["degree"];
        if (count($degrees) >= 2) {
            echo "<h3>Your Input:</h3>";
            echo "Degrees: " . implode(", ", $degrees);
        }
    }
}
?>

<hr>

<!-- FORM 6: BLOOD GROUP VALIDATION -->
<h2>6. Blood Group Validation</h2>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <select name="blood_group">
        <option value="">Select</option>
        <option value="A+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="A+") ? "selected" : ""; ?>>A+</option>
        <option value="A-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="A-") ? "selected" : ""; ?>>A-</option>
        <option value="B+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="B+") ? "selected" : ""; ?>>B+</option>
        <option value="B-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="B-") ? "selected" : ""; ?>>B-</option>
        <option value="AB+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="AB+") ? "selected" : ""; ?>>AB+</option>
        <option value="AB-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="AB-") ? "selected" : ""; ?>>AB-</option>
        <option value="O+" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="O+") ? "selected" : ""; ?>>O+</option>
        <option value="O-" <?php echo (isset($_POST['blood_group']) && $_POST['blood_group']=="O-") ? "selected" : ""; ?>>O-</option>
    </select>
    <span class="error">* 
        <?php 
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_blood"])) {
            if (empty($_POST["blood_group"])) {
                echo "Blood group is required";
            }
        }
        ?>
    </span>
    <br><br>
    <input type="submit" name="submit_blood" value="Submit">
</form>

<?php
// Display result for Blood Group
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_blood"])) {
    if (!empty($_POST["blood_group"])) {
        $blood_group = test_input($_POST["blood_group"]);
        echo "<h3>Your Input:</h3>";
        echo "Blood Group: " . $blood_group;
    }
}
?>

</body>
</html>