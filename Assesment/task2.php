<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <!-- If there's an error message, it appears here -->
    <!-- <p style='color:red;'>Invalid username or password!</p> -->
    
    <form action="loginCheck.php" method="post">
        <fieldset>
            <legend>Signin</legend>
            <table>
                <tr>
                    <td>Username</td>
                    <td><input type="text" name="username"></td>
                </tr>

                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password"></td>
                </tr>

                <tr>
                    <td></td>
                    <td><input type="submit" name="submit" value="Submit"></td>
                </tr>
            </table>
        </fieldset>
    </form>
</body>
</html>