<?php
/*************************Helper Function********************************/
function clean($string){
  return htmlentities($string);
}

function redirect($location){
  return header("Location: {$location}");
}

function set_message($message){
  if(!empty($message))
  {
    $_SESSION['message']=$message;
  }else{
    $message="";
  }
}

function display_message(){
  if(isset($_SESSION['message'])){
    echo $_SESSION['message'];
    unset($_SESSION['message']);
  }
}

function token_generator(){
  $token= $_SESSION['token']= md5(uniqid(mt_rand(),true));
  return $token;  
}

function email_exist($email){
  $sql="select id from users where email='$email'";
  $result=query($sql);
  if(row_count($result) == 1){
    return true;
  }else{
    return FALSE;
  }
}

function username_exist($username){
  $sql="select id from users where username='$username'";
  $result=query($sql);
  if(row_count($result)==1){
    return true;
  }else{
    return FALSE;
  }
}

function form_error($err_msg){
  $err_msg=<<<xyz
  <div class="alert alert-danger  alert-dismissible" role="alert">
 <strong> $err_msg </strong>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
xyz;
return $err_msg;
}

function send_email($email,$subject,$msg,$headers){
  return mail($email,$subject,$msg,$headers);
}


/*************************Registration Validation Function********************************/

function validate_user_registration(){
  $min=3;
  $max=20;
  $errors=[];
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $first_name       = clean($_POST['first_name']);
    $last_name        = clean($_POST['last_name']);
    $username         = clean($_POST['username']);
    $email            = clean($_POST['email']);
    $password         = clean($_POST['password']);
    $confirm_password = clean($_POST['confirm_password']);
  }
  if($_SERVER['REQUEST_METHOD']=="POST"){

    if(strlen($first_name) < $min ){
      $errors[]="First Name can not be less than {$min} characters";
    }
    if(strlen($first_name) > $max){
      $errors[]="First Name can not be more than {$max} characters";
    }

    if(strlen($last_name) < $min){
      $errors[]="Last Name can not be less than {$min} characters";
    }
    if(strlen($last_name) > $max){
      $errors[]="Last Name can not be more than {$max} characters";
    }

    if(strlen($username) < $min ){
      $errors[]="User Name can not be less than {$min} characters";
    }
    if(strlen($username) > $max){
      $errors[]="User Name can not be more than {$max} characters";
    }

    if($password !== $confirm_password){
      $errors[]="Your Password do not match";
    }
    if(email_exist($email)){
      $errors[]="The Email is already registered";
    }
    if(username_exist($username)){
      $errors[]="The Username is already registered";
    }

    if(!empty($errors)){
      foreach ($errors as $error) {
        echo form_error($error);
      }
    } else {
      if(register_user($first_name,$last_name,$username,$email,$password)){
        set_message("<p class='bg-success text-center'>Please check your email</p>");
        // echo "Regitered";
        redirect("index.php");
      } else {
        set_message("<p class='bg-success text-center'>Sorry we could not register you</p>");
      }
    }
  }
}

function register_user($first_name,$last_name,$username,$email,$password){
  $first_name = escape($first_name);
  $last_name = escape($last_name);
  $username = escape($username);
  $email = escape($email);
  $password = escape($password);

  if(email_exist($email)){
    return false;
  } elseif(username_exist($last_name)){
    return false;
  } else{
    $password = md5($password);
    $validation = md5($username);
    $sql = "INSERT INTO users(first_name,last_name,username,email,password,validation_code,active) VALUES('$first_name','$last_name','$username','$email','$password','$validation',0)";
    $result = query($sql);
    confirm($result);

    $subject="Activated";
    $msg="Please click the link
    http://localhost/login/activate.php?email=$email&code=$validation
    ";
    $header="From: noreply@website.com";
    send_email($email,$subject,$msg,$headers);
    return true;
  }
} 


/*************************Activate user Function********************************/

function activate_user(){
  if($_SERVER['REQUEST_METHOD'] == "GET") {
    if(isset($_GET['email'])) {
      $email=clean($_GET['email']);
      $validation=clean($_GET['code']);
      $sql ="SELECT id FROM users WHERE email='$email' AND validation_code='$validation' ";
      // http://localhost/index/activate.php?email=asds@gmail.com&code=e10adc3949ba59abbe56e057f20f883e 	
      $result = query($sql);
      // confirm($result); 
      if(row_count($result) == 1){
        $sql2="UPDATE users SET active=1,validation_code=0 WHERE email='".escape($_GET['email'])."' AND validation_code='".escape($_GET['code'])."' ";
        $result2 = query($sql2);
        // confirm($result2); 
        set_message("<p class='bg-success'>Your account is activated</p>");
        redirect('login.php');
      }
      else{
        set_message("<p class='bg-success'>Sorry Your account is not activated</p>");
        redirect('login.php');
      }
    }
  }
}


/*************************Login Validation Function********************************/
function validate_user_login(){
  $min=3;
  $max=20;
  $errors=[];
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $email            = clean($_POST['email']);
    $password         = clean($_POST['password']);
    $remember         =isset($_POST['remember']);
    if(empty($email)){
      $errors[]="Email Field Can not be empty";
    }
    if(empty($password)){
      $errors[]="Password Field Can not be empty";
    }

    if(!empty($errors)){
      foreach ($errors as $error) {
        echo form_error($error);
      }
    } else {
      if(login_user($email,$password,$remember)){
        redirect('index.php');
      }else{
        echo form_error("Enter Email and Password Correctly.");
      }

    }
  }
}


/*************************User Login Function********************************/
function login_user($email,$password,$remember){
  $email = escape($email);
  $sql="SELECT password,id FROM users WHERE email='$email' ";
  $result = query($sql);
  if(row_count($result) == 1){
    $row= fetch_array($result);
    $db_password=$row['password'];
    if(md5($password) == $db_password){
      if($remember == 'on'){
          setcookie(email,$email,time()+60);
      }
      $_SESSION['email']=$email;
      return true;
    }else {
      return false;
    }
  }
  else{
    return false;
  }
}


/*************************Logged_in Function********************************/
function logged_in(){
  if(isset($_SESSION['email']) || isset($_COOKIE['email'])){
    return true;
  }else{
    return false;
  }
}


/*************************Recovery Function********************************/
function recover_password(){
  if($_SERVER['REQUEST_METHOD'] == "POST"){
    if(isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token']){
      $email=clean($_POST['email']);
      if(email_exist($email)){
        $validation =md5($email);
        setcookie('temp_access_code',$validation,time()+600);
        $validation= escape($validation);
        $email = escape($email);
        $sql="UPDATE users SET validation_code='$validation' WHERE email='$email'";
        $result=query($sql);
        // confirm($result);
        $subject ="Email recovery";
        $message ='
        Here is the code to your email {$validation}
        click here http://localhost/login/code.php?email=$email&code=$validation
        ';
        $header="From: noreply@yourwebsite.com";
        if(!send_email($email,$subject,$message,$header)){
            echo form_error("this email not sent");
          }
        set_message("<p class='bg-success'>Plese check your email</p>");
        redirect("index.php");
      }else{
        echo form_error("this email does not exists");
      }
    }else{
      redirect("login.php");
    }
    if(isset($_POST['cancel-submit'])){
      redirect('login.php');
    }

  }
}


/*************************Code ********************************/
function validate_code(){
  if(isset($_COOKIE['temp_access_code'])){
      if(!isset($_GET['email']) && !isset($_GET['code'])){
        redirect("index.php");

      }elseif(empty($_GET['email']) && $_GET['code']){
        redirect("index.php");
      }else{
        if(isset($_POST['code'])){
          $validation=clean($_POST['code']);
          $validation=escape($validation);
          $email=clean($_GET['email']);
          $email=escape($email);
          $sql="SELECT id FROM users WHERE validation_code='$validation' AND email='$email' ";
          $result=query($sql);
          // confirm($result);
          if(row_count($result) == 1){
            setcookie('temp_access_code',$validation,time()+600);
            redirect("reset.php?=$email&code=$validation");
          }else{
            echo form_error("Sorry worng validation code");
          }
        }
      }
  }else{
    set_message("<p class='bg-success'>Sorry your validation cookie expired</p>");

    redirect("recover.php");

  }
}


/*************************Reset Password ********************************/
function password_reset(){
  if(isset($_COOKIE['temp_access_code'])){
    if(isset($_GET['email']) && isset($_GET['code'])){
      if(isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] === $_SESSION['token']){
        if($_POST['password'] == $_POST['confirm_password']){
          $password=escape($_POST['password']);
          $email=escape($_GET['email']);
          $updated_password = md5($password);
          $sql = "UPDATE users SET password='$updated_password',validation_code =0  WHERE email='$email' "; 
          query($sql);
          set_message("<p class='bg-success'>Please login in</p>");
          redirect('login.php');
        }else{
          echo "Not Done";
        }
      }else{
        echo "pro";
      }
    }
  }else{
    set_message("<p class='bg-success'>Sorry your validation cookie expired</p>");

    redirect("recover.php");

  }
}


/******Get Full name ***********/
function fullname(){
  if(isset($_SESSION['email'])){
    $email = $_SESSION['email'];
    $sql = "SELECT first_name,last_name FROM users WHERE email='$email'";
    $result = query($sql);
    confirm($result);
    $row= fetch_array($result);
    echo "$row[first_name] $row[last_name]";
    
  }
}

/******Upload Notes********/
function uploadnotes(){
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $notes = clean($_POST['notes']);
    $email = clean($_POST['email']);
    $name = clean($_POST['name']);
    $notes = escape($_POST['notes']);
    $email= escape($_POST['email']);
    $name= escape($_POST['name']);
  } 
  if($_SERVER['REQUEST_METHOD']=="POST"){
    $sql = "INSERT INTO data(email,notes,name,created_at) VALUES('$email','$notes','$name',now())";
    $result = query($sql);
    confirm($result);
    redirect('index.php');
  }
}
/*********Show Diary**********/

function showdiary(){
  if(isset($_SESSION['email'])){
    $email = $_SESSION['email'];
    $sql = "SELECT * FROM data WHERE email='$email'";
    $result = query($sql);
    confirm($result);
    while($row = mysqli_fetch_array($result)){
    
    $value=<<<abc
    <div class="card">
    <div class="card-header">
        
    </div>
    <div class="card-body">
        <blockquote class="blockquote mb-0">
        <p>$row[notes]</p>
        <footer class="blockquote-footer">Written by $row[name] created at $row[created_at] updated at $row[updated_at]</footer>
        </blockquote>
        <a href="edit.php?id=$row[id]" class="btn btn-primary">Edit</a>
        <a href="delete.php?id=$row[id]" class="btn btn-danger">Delete</a>
    </div>
    </div>
abc;
echo $value;
    }
  }
}
/***************** Edit Notes ****************/
function editnotes(){
  if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sql = "SELECT * FROM data where id='$id'";
    $result = query($sql);
    confirm($result);
    $row = fetch_array($result);
    return $row;
  }
}

/************************** update diary ********************/
function updatenotes(){

  if(isset($_GET['id'])){
    $id = $_GET['id'];
  }

  if($_SERVER['REQUEST_METHOD']=="POST"){
    $notes = clean($_POST['notes']);
    $notes = escape($_POST['notes']);
  } 

  if($_SERVER['REQUEST_METHOD']=="POST"){
    $sql = "UPDATE data SET notes = '$notes',updated_at = now() WHERE id='$id'";
    $result = query($sql);
    confirm($result);
    redirect('index.php');
  }
}


/***************Delete Notes****************/
function deletenotes(){
  if(isset($_GET['id']))
  $id = $_GET['id'];
  $sql = "DELETE FROM data where id='$id'";
  $result = query($sql);
  confirm($result);
  redirect('index.php');
}


?>