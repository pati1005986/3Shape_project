<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../Configuration/logo.ico" type="image/x-icon">
    <title>Restore password</title>
    <link rel="stylesheet" href="../Configuration/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../View/Css/dark-mode.css">
    <link rel="stylesheet" href="../View/Css/Form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
         body {
  background-color:rgb(239, 232, 233);
}
    </style>
</head>

<body>
    <div id="FormRestore" class="col-md-10 mx-auto align-items-center d-flex justify-content-center">
        <!-- Formulario de restablecer contraseña -->
        <form action="../Controller/update_password.php" method="post"
            class="shadow p-3 mb-5 my-5 rounded needs-validation bg-light">

            <center><h2 id="totalC" class="text-center mb-5">Restore password</h2></center>
          
            <div class="form-group">
                <label for="Password2" class="col-form-label"><h5>Enter your new password</h5></label>
                <input type="password" name="NewPassword" id="NewPassword" placeholder="New Password" required
                    oninput="validatePassword()" class="shadow-sm form-control">
                <br>
            </div>
            <div class="form-group">
                <label for="Password3" class="col-form-label"><h5>Repeat your new password</h5></label>
                <input type="password" name="Confirmpassword" id="Confirmpassword" oninput="validatePassword()"
                    placeholder="Repeat Password" required class="shadow-sm form-control">
                <br>
                <label for="securityCode" class="col-form-label"><h5>Enter your security code</h5></label>
                <small id="passwordHelpBlock" class="alert alert-info" style="display: none;">Please enter your
                    secondary password (e.g. 121312@).</small>
                <input type="password" name="securityCode" id="securityCode" pattern="121312@"
                    placeholder="Security Code" required class="shadow-sm form-control"
                    onfocus="document.getElementById('passwordHelpBlock').style.display = 'block'; this.placeholder=''"
                    onblur="document.getElementById('passwordHelpBlock').style.display = 'none'; this.placeholder='Security Code'"
                    title="Please enter the correct security code (121312@)">
            </div>
            <br>
            <button type="submit" class="btn btn-primary mb-5 my-4"
                onclick="return confirm('Are you sure you want to send this information?')">Send<i class="fas fa-paper-plane"></i></button>
            <div class="invalid-feedback">Please fill out this field.</div>
            <a href="../index.php" class="btn btn-danger mb-5 my-4">Back
            <i class="fas fa-arrow-left"></i>
            </a>
        </form>
    </div>
    <script src="../Configuration/bootstrap/js/bootstrap.min.js"></script>
</body>
<footer class="container align-items-center text-center mt-5">
    <div class="row">
        <div class="col-md-4">
            <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
            <p>Page created on: <?php echo date("F j, Y", filectime(__FILE__)); ?></p>
        </div>
        <div class="col-md-4">
            <p>Developed by: Daniel Patiño</p>
            <p>Contact: <a href="mailto:Daniel.Cortes@3Shape.com">Daniel.Cortes@3Shape.com</a></p>
        </div>
        <div class="col-md-4">
            <p class="text-muted">Version: 3.0.0</p>
            <p class="text-muted">Last updated: <?php echo date("F j, Y", filemtime(__FILE__)); ?></p>
        </div>
    </div>
</footer>

</html>