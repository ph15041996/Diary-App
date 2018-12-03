<?php
$host="localhost";
$user="root";
$password="ph15041996";
$dbname="diary";
$con=mysqli_connect($host,$user,$password,$dbname);

function row_count($result){
  return mysqli_num_rows($result);
}

function escape($string){
  global $con;
  return mysqli_real_escape_string($con,$string);
}


function query($query){
  global $con;
  return mysqli_query($con,$query);
  confirm($result);
}

function confirm($result){
  global $con;
  if(!$result){
    die("Query Failed ".mysqli_error($con));
  }
}

function fetch_array($result){
  global $con;
  return mysqli_fetch_array($result);
}


?>