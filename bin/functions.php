<?php

$databases_root = "../db";
$query_history_length = 5;

# -------------------------------------------------------------------------------------------------------
#											Q U E R Y   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function storeQuery($query) {
	
	if (strlen($query) > 0) {
	
		$query_history = file('../meta/query_history.txt');
		$rows = count($query_history);
		
		if ($rows >= $GLOBALS['query_history_length']) {
			# Pop last value off the end of the array
			array_pop($query_history);
			
			# Unshift the new value on the front		
			array_unshift($query_history, $query . "\n");
			
		} else {
			# Unshift the new value on the front		
			array_unshift($query_history, $query . "\n");
		}
		
		# Overwrite query history into file
		file_put_contents('../meta/query_history.txt', ''); # Clears file
		file_put_contents('../meta/query_history.txt', $query_history); # Writes array to file
	
	}
	
}

function showQueryHistory() {
	
	echo "<p><b>Query History</b><br/>";
	$query_history = file('../meta/query_history.txt');
	$rows = count($query_history);
	
	for ($r = 0; $r < $rows; $r++) {
		echo $query_history[$r] . "<br/>";	
	}
	
}

function parseQuery($query) {
	
	$commands = explode(" ", $query);
	$elements = count($commands);
	
	# Create Commands
	if ($commands[0] == "create") {
		create($query);

	} elseif ($commands[0] == "delete") {
		delete($query);

	} else {
		echo "<p>Invalid: No command found.";
	}
	
}

function create($query) {
	
	$commands = explode(" ", $query);
	$elements = count($commands);
	
	if ($elements >= 2) {
		# Create Schema | create schema <schema_name>
		if ($commands[1] == "schema") {
			if ($elements == 3) {
				$status = createSchema($commands[2]);
				echo "<p>Create Schema: " . $status;
			} else {
				echo "<p>Invalid: No schema name specified.";
			}
		}
		
		# Create Database | create database	<db_name> in <schema_name>
		if ($commands[1] == "database") {
			if ($elements == 5 && $commands[3] == "in") {
				$status = createDatabase($commands[2], $commands[4]);
				echo "<p>Create Database: " . $status;
			} else {
				echo "<p>Invalid: Missing parameters, or command malformed.";
			}
		}
		
		
	} else {
		echo "<p>Invalid: Specify schema, database or table.";
	}

}

function delete($query) {

	$commands = explode(" ", $query);
	$elements = count($commands);

	if ($elements >= 2) {
		# Delete Schema
		if ($commands[1] == "schema") {
			if ($elements == 3) {
				$status = deleteSchema($commands[2]);
				echo "<p>Delete Schema: " . $status;
			} else {
				echo "<p>Invalid: No schema name specified.";
			}
		
		}
		
	} else {
		echo "<p>Invalid: Specify schema, database or table.";
	}
	
	
}

# -------------------------------------------------------------------------------------------------------
#											D I S P L A Y   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function displayStructure() {
	
	# List of schemas
	$schemas = scandir($GLOBALS['databases_root']);
	$folders = count($schemas);
	
	echo "<p><b>Schemas</b><P>";
	
	for ($f = 3; $f < $folders; $f++) {
		echo $schemas[$f] . "<br/>";
	
		# List of databases
		
			# List of tables
	
	}
	
}


# -------------------------------------------------------------------------------------------------------
#											S C H E M A   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function createSchema($schema_name) {
	
	#echo "Create Schema: " . $schema_name;
	
	$status = "";
	$schema_path = "../db/" . $schema_name;
	
	# Check for only ASCII alphanumerics
	$schema_name_characters = str_split($schema_name);
	foreach ($schema_name_characters as $character) {
		#echo $character . ": " . ord($character) . "<br/>";
		if (ord($character) >= 97 && ord($character) <= 122) {
			# Do nothing; character is valid between a-z
		} else {
			$status = "Only alpha characters between a-z accepted.<br/>";
		}
	}
	
	if ($status == "") { # Characters in schema name are fine
		# Check if schema directory already exists
		if (file_exists($schema_path)) {
			$status = "Schema with that name already exists.<br/>";
			
		} else {

			# If not, create it
			mkdir($schema_path);
			
			# Check schema folder created
			if (file_exists($schema_path)) {
				$status = "Schema '" . $schema_name . "' created.<br/>";
			} else {
				$status = "ERROR: Schema '" . $schema_name . "' not created.<br/>";
			}		

		}		
		
	}
	
	return $status;
	
}

function backupSchema($schema_name) {
	
	
	
	
	
}

function deleteSchema($schema_name) {

	$schema_path = "../db/" . $schema_name;
	$status = "";
	
	# Check for only ASCII alphanumerics
	$schema_name_characters = str_split($schema_name);
	foreach ($schema_name_characters as $character) {
		#echo $character . ": " . ord($character) . "<br/>";
		if (ord($character) >= 97 && ord($character) <= 122) {
			# Do nothing; character is valid between a-z
		} else {
			$status = "Only alpha characters between a-z accepted.<br/>";
		}
	}
	
	if ($status == "") { # Characters in schema name are fine
	
		if (file_exists($schema_path)) {
			rmdir($schema_path);		
			$status = "Schema deleted.<br/>";
		} else {
			$status = "ERROR: Schema does not exist.<br/>";
		}

	}
		
	return $status;
	
}

# -------------------------------------------------------------------------------------------------------
#										D A T A B A S E   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function createDatabase($database_name, $schema_name) {
	
	$schema_path = "../db/" . $schema_name;
	$status = "";
	
	# Check database name is valid
	$database_name_characters = str_split($database_name);
	foreach ($database_name_characters as $character) {
		if (ord($character) >= 97 && ord($character) <= 122) {
			# Do nothing; character is valid between a-z
		} else {
			$status = "Only alpha characters between a-z accepted.<br/>";
		}
	}
	
	if ($status == "" && file_exists($schema_path)) {
		# If so, create database
		$database_path = $schema_path . "/" . $database_name;
		mkdir($database_path);

		# Check database exists
		if (file_exists($database_path)) {
			$status = "Database '" . $database_name . "' created in schema '" . $schema_name . "'.<br/>";
		} else {
			$status = "ERROR: Database '" . $database_name . "' not created.<br/>";
		}		

	}
	
	return $status;
	
}

function deleteDatabase($database_name, $schema_name) {
	
	
	
	
}

function listDatabase($schema_name) {
	
	
	
	
}

# -------------------------------------------------------------------------------------------------------
#										T A B L E   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function createTable($table_name, $database_name, $schema_name) {
	
	
	
	
}

function deleteTable($table_name, $database_name, $schema_name) {
	
	
	
	
}

function listContentsTable($table_name, $database_name, $schema_name) {
	
	
	
}

# -------------------------------------------------------------------------------------------------------
#										R E C O R D   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------


function addRecord($record_data, $table_name, $database_name, $schema_name) {
	
	
	
	
}

function editRecord() {
	
	
	
	
}

function deleteRecord() {
	
	
	
	
	
}







?>