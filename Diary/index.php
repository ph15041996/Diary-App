<?php include("include/header.php")?>
<?php include("include/nav.php")?>

<?php 
		if(logged_in()) {
        showdiary();
        }
        else{
        redirect("login.php");
        }
?>
<?php include("include/footer.php")?>