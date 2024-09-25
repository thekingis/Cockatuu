<?php
	date_default_timezone_set('Africa/Lagos'); // change to America/
	$con = mysqli_connect('localhost', 'root', '') or die ('error');
	mysqli_select_db($con, 'pixtanta') or die ('error');
?>