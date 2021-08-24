<?php
include("config.php");
session_start();
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $query = "SELECT COUNT(*) as num from owners where user_name=? and user_password = ?";
   $statement = $conn->prepare($query);
   $statement->bindParam(1, $_POST['username']);
   $statement->bindParam(2, $_POST['password']);
   $statement->execute();
   $num_of_rows = $statement->fetchColumn();

   if ($num_of_rows == 1) {
      $_SESSION["login_user"] = $_POST['username'];
      $_SESSION["login_password"] = $_POST['password'];
      header("location: index.php");
   } else {
      echo '<script>alert("Your Login name or password is invalid");</script>';
   }
}


?>

<html lang="en">

<head>
   <title>Login</title>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
   <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
   <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
   <style>
      .login-form {
         width: 340px;
         margin: 50px auto;
         font-size: 15px;
      }

      .login-form form {
         margin-bottom: 15px;
         background: #f7f7f7;
         box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
         padding: 30px;
      }

      .form-control,
      .btn {
         min-height: 38px;
         border-radius: 2px;
      }

   </style>
</head>

<body>
   <div class="login-form" id="app">
      <form action="" method="post">
         <h2 class="text-center">Log in</h2>
         <div class="form-group">
            <input type="text" class="form-control" placeholder="Username" name="username" required="required" autocomplete="off">
         </div>
         <div class="form-group">
            <input type="password" class="form-control" placeholder="Password" name="password" required="required" autocomplete="off">
         </div>
         <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
         </div>
         <div class="clearfix">
            <a href="ForgetPassword.php" class="float-right">Forgot Password?</a>
         </div>
      </form>
   </div>
</body>

</html>
<script>
   var applications = new Vue({
      el: '#app',
      data: {
         username: '',
         password: ''
      },
      methods: {
         Login: async function() {
            await axios.post('action.php', {
               action: 'Login',
               username: this.username,
               password: this.password
            }).then(function(response) {
               console.log(response.data);
               if (response.data == 0) {
                  alert("username or password is invaild");
               } else {
                  window.location.href = "index.php";
               }

            });
         }
      }

   });
</script>