<?php

	if(isset($_POST)){

		$home = $_SERVER['DOCUMENT_ROOT'];
		include $home.'/app/connect.php';
		include $home.'/app/functions.php';
		$array = array();
		$errors = array();

		$fName = mysqli_real_escape_string($con, $_POST['fName']);
		$lName = mysqli_real_escape_string($con, $_POST['lName']);
		$email = strtolower(mysqli_real_escape_string($con, $_POST['email']));
		$userName = strtolower(mysqli_real_escape_string($con, $_POST['userName']));
		$password = mysqli_real_escape_string($con, $_POST['password']);
		$birthDate = mysqli_real_escape_string($con, $_POST['birthDate']);
		$date = date('Y-m-d G:i:s');
        $photoUrl = '/photos/default.jpg';

		if(preg_match('/[^a-zA-Z- ]/', $fName))
			$errors[] = "Your First  name contains invalid characters";
		if(preg_match('/[^a-zA-Z- ]/', $lName))
			$errors[] = "Your Last name contains invalid characters";
		if(preg_match('/[^a-z0-9_ ]/', $userName))
			$errors[] = "Your Username contains invalid characters";
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			$errors[] = "Your Email is invalid";
		if(!preg_match("#[0-9]+#", $password))
			$errors[] = "Your Password Must Contain at least 1 Number";
		if(strlen($password) < 8)
			$errors[] = "Your Password Must be upto 8 Characters";

		$emailQuery = mysqli_query($con, "SELECT * FROM accounts WHERE email = '".$email."'");
		if(mysqli_num_rows($emailQuery) > 0)
			$errors[] = "Sorry Your Email is already registered";

        $usernameQuery = mysqli_query($con, "SELECT * FROM accounts WHERE userName = '".$userName."'");
        if(mysqli_num_rows($usernameQuery) > 0)
            $errors[] = "Sorry Your Username is already registered";

		if(!empty($errors))
			$array['dataStr'] = $errors[0];
		else {
			mysqli_query($con, "INSERT INTO accounts VALUES('0', '".$fName."', '".$lName."', '".$email."', '".$userName."', '".md5($password)."', '".$photoUrl."', '".$birthDate."', '".$date."')");
			$id = mysqli_insert_id($con);
			list($fn) = explode(' ', $fName);
			$array['myId'] = (string)$id;
		}
        $array['noError'] = empty($errors);

		echo json_encode($array);
	}

?>