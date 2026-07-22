<?php

require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $storeName = trim($_POST["storeName"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm-password"];

    //check if password match confirm match password
    if ($password != $confirm) {
        $error = "Passwords do not match.";
        
    }else{
        //if both password match.
        //check if email is taken
        $check = $conn->prepare("SELECT user_id FROM user_tbl WHERE email = ?");
        $check -> bind_param("s", $email);
        $check -> execute();
        $result = $check->get_result();
        if($result->num_rows > 0){
            $error = "Email already registered!";
            
        }else{
            //if email is not taken, insert user
            // hash password
            $hashedPassword = password_hash($password,PASSWORD_DEFAULT);

            // insert user to db
            $insertUser = $conn->prepare("INSERT INTO user_tbl(email,password) VALUES (?,?)");
            $insertUser -> bind_param("ss",$email,$hashedPassword);
            

            if ($insertUser->execute()) {
                
                $userID = $conn->insert_id;
                $insertStore = $conn->prepare("INSERT INTO vendor_tbl(user_id, store_name)VALUES (?, ?)");

                $insertStore->bind_param("is", $userID, $storeName);
                $insertStore->execute();
                header("Location: ./login.php?registered=success"); //redirect after successful registration
            } else {
                $error = "Error registering user";
            }
        }

    
    }
    
    

}





?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href='./css/login.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <title>Login</title>
</head>
<body>
    <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertBar">
                <?= htmlspecialchars($error) ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <div class="container">
        
        <div class="login-container">
            <h1 class="d-flex justify-content-center mb-4">Register</h1>
            <form class="pt-3" method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control login-input" id="storeNameField" name="storeName" placeholder="jaundelacruz@example.com">
                    <label for="storeNameField">Store name</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control login-input" id="emailField" name="email" placeholder="jaundelacruz@example.com">
                    <label for="emailField">Email address</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control login-input" id="passwordField" name="password" placeholder="Password">
                    <label for="passwordField">Password</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control login-input" id="confirmPasswordField" name="confirm-password" placeholder="Password">
                    <label for="confirmPasswordField">Confirm password</label>
                </div>

                <div class="button-container mt-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary px-5">Confirm</button>
                </div>
            </form>
        </div>
        
    </div>
    
</body>
</html>