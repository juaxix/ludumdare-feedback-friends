<html>
<head>
 <!-- <meta http-equiv="refresh" content="1">-->
</head>
<body>

<?php

// TODO Password-protect

require_once('includes/init.php');

if (LDFF_SCRAPING_ENABLED) {

	set_time_limit(LDFF_SCRAPING_TIMEOUT + 30);

	$db = db_connect();

	echo '<pre>';
	print_r(scraping_run($db));
	echo '</pre>';

	mysqli_close($db);

}
else {
	log_info("Scraping disabled, nothing to do.");
}

?>

</body>
</html>