<?php

	if(isset($_POST)){

		$home = $_SERVER['DOCUMENT_ROOT'];
		include $home.'/app/connect.php';
		include $home.'/app/functions.php';
		$array = array();
		$errors = array();

		$email = strtolower(mysqli_real_escape_string($con, $_POST['email']));
		$password = md5(mysqli_real_escape_string($con, $_POST['password']));

		$emailQuery = mysqli_query($con, "SELECT * FROM accounts WHERE email = '".$email."' OR username = '".$email."'");
		$sql = mysqli_fetch_array($emailQuery);
		if(mysqli_num_rows($emailQuery) == 0)
			$errors[] = "Invalid Email or Username";
		else {
			$pass = $sql['password'];
			if(!($pass == $password))
				$errors[] = "Your Password is incorrect";
		}
        
        $array['noError'] = empty($errors);
		if (empty($errors)) {
			$array['myId'] = (string) $sql['id'];
			$array['name'] = $sql['fName'].' '.$sql['lName'];
			$array['username'] = $sql['username'];
			$array['verified'] = filter_var($sql['verified'], FILTER_VALIDATE_BOOLEAN);
		} else
			$array['dataStr'] = $errors[0];

		echo json_encode($array);
	}

?>