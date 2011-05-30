<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();

	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		header("HTTP/1.0 500 Internal Server Error");
		echo "There was a problem with the upload";
		exit(0);
	} else {
		header("HTTP/1.0 500 Internal Server Error");
		echo "Flash requires that we output something or it won't fire the uploadSuccess event";

		exit(0);
	}
?>