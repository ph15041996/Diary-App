<?php include("include/header.php")?>
<?php include("include/nav.php")?>

	<div class="" >
		<h1 class="text-center"><?php 
		if(logged_in()) {
			?>
			<div>
			<h4>Welcome <?php fullname();
            $row = editnotes();
            updatenotes();
			?></h4>
			</div>
			<form method="post" role="form" style="">
			<div class="form-group">
			<textarea class="form-control" name="notes" aria-label="With textarea" aria-describedby="inputGroup-sizing-lg" rows='10' placeholder="Write here ......" ><?php echo $row[notes]?></textarea>
			</div>
			
			<div class="form-group">
				<div class="row">
					<div class="col-sm-6 col-sm-offset-3">
						<input type="submit"  class="form-control btn btn-primary" value="SUBMIT">
					</div>
				</div>
			</div>
			<input type="hidden" class="hide" name="email" value="<?php echo $_SESSION['email']?>">
			<input type="hidden" class="hide" name="name" value="<?php fullname(); ?>">
		</form>
		<?php	
		}else{
			redirect("login.php");
		}
			?></h1>
	</div>





<?php include("include/footer.php")?>