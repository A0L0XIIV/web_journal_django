<?php 
    require "header.php";
    // Check if the session variable name is empty or not and redirect
    if ($_SERVER["REQUEST_METHOD"] === "GET"
        && !isset($_SESSION['name'])) {
        exit("<script>location.href = './index.php';</script>"); 
    }
?>

<?php 
  // define variables and set to empty values
  $nameError = $pwError = $updateError = false;
  $successUpdate = false;
  $errorText = "";
  $name = "";
  $password = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Security operations
    $name = $_SESSION['name'];
    // Check if name is empty or not and redirect
    if($name == "" || $name == NULL)      
        echo("<script>location.href = './index.php';</script>"); 
    $password = test_input($_POST["password"]);

    // Empty check
    if(empty($password)){
      $pwError = true;
    }
    else{
      // Database connection
      require "./mysqli_connect.php";
      // Save journal into DB
      $sql = "UPDATE user SET password=? WHERE name=?";
      $stmt = mysqli_stmt_init($conn);
      if(!mysqli_stmt_prepare($stmt, $sql)){
          $error = true;
      }
      else{
          // Hash the password
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
          // Bind inputs to query parameters
          mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $name);
          // Execute sql statement
          if(mysqli_stmt_execute($stmt))
              $successUpdate = true;
          else{
              $updateError = true;
              $errorText = mysqli_error($conn);
          }
      }
    }
  }

  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

?>

<!-- Centered main-->
<main class="main" style="min-height: 90vh;">

  <form
    name="login-form"
    id="login-form"
    action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
    method="post">

    <h1>
      Şifreni buradan değiştirebilirsin
      <?php
        if(isset($_SESSION['name'])){
            echo ' '.$_SESSION['name'];
        }
      ?>
    </h1>

    <!--Input for user password, type=password-->
    <div class="input-group mb-3 justify-content-center">
      <div class="input-group-prepend">
        <span class="input-group-text" id="pw-label">Şifre</span>
      </div>
      <input
        type="password"
        name="password"
        id="password-input"
        title="Max uzunluk 50."
        maxlength="50"
        minlength="3"
        placeholder="..."
        required
      />
    </div>
    <?php if($pwError) {echo '<p id="passwordError" class="error">Şifre boş olamaz!</p>';}?>

    <!--Password update error-->
    <div>
        <?php if($updateError) {echo '<p id="authError" class="error">Şifre değiştirme başarısız! '.$errorText.'</p>';}?>
        <?php if($successUpdate) {echo '<p id="successAuth" class="success">Şifre değiştirme başarılı!</p>';}?>
    </div>

    <!--Button for submitting the form-->
    <div>
      <button
        type="submit"
        name="pw-update-submit"
        class="sbmt-btn bg-password"
        aria-pressed="false">
        Gönder
      </button>
    </div>
  </form>
  <br /><br />
</main>

<?php
    require "footer.php";
?>