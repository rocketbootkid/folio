<html>

<head>
	<title>
		FolioDB
	</title>
</head>

<body>

	<form id="query" method="post" action="client.php">
		<input id="query" type="text" name="query" size="100"></input>
		<input name="button" type="submit"value="Query"/> 
	</form>	

<hr/>
	
<?php

	include 'functions.php';

	if (isset($_POST['query'])) {
		$query = $_POST['query'];
			
		storeQuery($query);
		
		#showQueryHistory();
		
		parseQuery($query);
		
	}

	
	echo "<hr/>";
	
	if (isset($_GET['mode'])) {
		$mode = $_GET['mode'];
	} else {
		$mode = "";
	}
	displayStructure($mode);

?>

<hr>

<p><a href='datagen/datagen.php'>Generate Data</a>

</body>

</html>