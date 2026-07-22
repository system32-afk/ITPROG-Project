<?php
session_start();
require_once "database.php";
$alert = "";
$alertType = "";
if(isset($_GET["registered"]) && $_GET["registered"] == "success"){
    $alertType = "success";
    $alert = "Registration successful! You may now log in.";
}

if($_SERVER["REQUEST_METHOD"]== "POST"){
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $checkEmail = $conn->prepare("
    SELECT user_id, email, password FROM user_tbl WHERE email = ?
    ");

    $checkEmail->bind_param("s",$email);
    $checkEmail->execute();

    $result = $checkEmail -> get_result();

    if($result ->num_rows == 0){
        $alertType = "danger";
        $alert = "Credentials doesn't match our records";
    }else{
        $user = $result->fetch_assoc();

        if(password_verify(($password),$user["password"])){
            $_SESSION["user_id"] = $user["user_id"];

            header("Location: ./admindashboard.php");
        }else{
            $alertType = "danger";
            $alert = "Credentials doesn't match our records";
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
    <?php if(isset($alert) && $alert != ""): ?>

    <div class="alert alert-<?php echo $alertType ?> alert-dismissible fade show" role="alert" id="alertBar">
        <?= htmlspecialchars($alert) ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <?php endif; ?>
    <div class="container">

        <div class="login-container">
            <div class="d-flex justify-content-center mb-4">
                <img src="./images/Pabili.png" alt="Pabili Logo" width="350">
            </div>
            <form class="pt-3" method="POST" action="./login.php">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control login-input" id="emailField" name="email" placeholder="jaundelacruz@example.com" required>
                    <label for="emailField">Email address</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control login-input" id="passwordField" name="password" placeholder="Password" required>
                    <label for="passwordField">Password</label>
                </div>
                <p>Don't have an account yet?
                    <a href="./register.php" class="btn btn-link p-0 pb-1 alignment-baseline">Register now!</a>
                </p>
                <div class="button-container mt-3 d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary px-5">Login</button>
                </div>
            </form>
        </div>
        
    </div>
    
</body>
</html>