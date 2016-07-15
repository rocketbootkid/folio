<?php

$databases_root = "../db";
$datafiles_root = "datagen/files";
$query_history_length = 5;

# -------------------------------------------------------------------------------------------------------
#											C O M M A N D   F U N C T I O N S
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
				echo "<p>Create Schema: ERROR: Missing parameters, or command malformed.";
			}
		}
		
		# Create Database | create database	<db_name> in <schema_name>
		if ($commands[1] == "database") {
			if ($elements == 5 && $commands[3] == "in") {
				$status = createDatabase($commands[2], $commands[4]);
				echo "<p>Create Database: " . $status;
			} else {
				echo "<p>Create Database: ERROR: Missing parameters, or command malformed.";
			}
		}
		
		# Create Table | create table <table_name> in <schema_name>.<db_name>
		if ($commands[1] == "table") {
			if ($elements == 5 && $commands[3] == "in") {
				$status = createTable($commands[2], $commands[4]);
				echo "<p>Create Table: " . $status;
			} else {
				echo "<p>Create Table: ERROR: Missing parameters, or command malformed.";
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
		# Delete Schema | delete schema <schema_name>
		if ($commands[1] == "schema") {
			if ($elements == 3) {
				$status = deleteSchema($commands[2]);
				echo "<p>Delete Schema: " . $status;
			} else {
				echo "<p>Delete Schema: ERROR: Missing parameters, or command malformed.";
			}
		}

		# Delete Database | delete database <database_name> from <schema_name>
		if ($commands[1] == "database") {
			if ($elements == 5 && $commands[3] == "from") {
				$status = deleteDatabase($commands[2], $commands[4]);
				echo "<p>Delete Database: " . $status;
			} else {
				echo "<p>Delete Database: ERROR: Missing parameters, or command malformed.";
			}
			
		}
		
	} else {
		echo "<p>Invalid: Specify schema, database or table.";
	}
	
	
}

# -------------------------------------------------------------------------------------------------------
#											D I S P L A Y   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function displayStructure($mode) {
	
	# Expect input in three forms;
	# 1. Schemas: [blank]
	# 2. Databases: <schema_name>
	# 3. Tables: <schema_name>.<database_name>
	
	$count = substr_count($mode, ".");
	
	if ($mode == "") { # Schema
		$table = displaySchemas();
	} elseif ($count == 0) { # Database
		$table = displayDatabases($mode);
	} elseif ($count == 1) { # Table
		$table = displayTables($mode);
	} elseif ($count == 2) { # Table Contents
		$table = displayTableContents($mode);
	}else { # Error
		$table = "No such schema, database, or table.";
	}
	
	echo $table;
	
}

function displaySchemas() {
	
	# List of schemas
	$schemas = scandir($GLOBALS['databases_root']);
	$folders = count($schemas);
	
	if ($folders > 0) {
		$text = "<table cellspacing=1 cellpadding=3 border=1>";
		$text = $text . "<tr><td>Schemas</tr>";
		for ($f = 3; $f < $folders; $f++) {
			$text = $text . "<tr><td valign=top><a href='client.php?mode=" . $schemas[$f] . "'>" . $schemas[$f] . "</a></tr>";
		}
		$text = $text . "</table>";
	} else {
		$text = "There are no schemas available. Create one using: create schema [schema_name]";
	}
	
	# Provide form for creating a new schema
	
	$text = $text . "<p><strong>Create Schema</strong><P>";
	$text = $text . "<form id='create_schema' method='post' action='client.php'>
				<input id='create' type='text' name='create_schema_command' size='30'></input>
				<input name='button' type='submit' value='Create'/> 
			</form>";
	
	return $text;
	
}

function displayDatabases($schema_name) {

	# List of databases
	$schema_path = $GLOBALS['databases_root'] . "/" . $schema_name;
	$databases = scandir($schema_path);
	$db_count = count($databases);

	if ($db_count > 2) {
		$text = "<strong>" . ucwords($schema_name) . " Schema | Databases</strong><p>";
		$text = $text . "<table cellspacing=1 cellpadding=3 border=1 width=200px>";
		
		for ($d = 2; $d < $db_count; $d++) {
			$text = $text . "<td valign=top width=100px><a href='client.php?mode=" . $schema_name . "." . $databases[$d] . "'>" . $databases[$d] . "</a></tr>";
		}
		$text = $text . "</table>";
	
	} else {
		$text = "There are no databases in this schema. Create using: create database [database_name] in [schema_name]";
	}
	
	$text = $text . "<p><strong>Create Database</strong><P>";
	$text = $text . "<form id='create_database' method='post' action='client.php'>
				<input id='database_name' type='text' name='create_database_command' size='30'></input>
				<input type='hidden' name='schema_name' value='" . $schema_name . "'></input>
				<input name='button' type='submit' value='Create'/> 
			</form>";
	
	$text = $text . "<p><a href='client.php?mode='>Back</a>";
	
	return $text;
	
}

function displayTables($database_schema_name) {

	# List of tables
	$components = explode(".", $database_schema_name);
	
	$database_path = $GLOBALS['databases_root'] . "/" . $components[0] . "/" . $components[1];
	$tables = scandir($database_path);
	$table_count = count($tables);
	
	if ($table_count > 2) {

		$text = "<strong>" . ucwords($components[1]) . " Database | Tables</strong><p>";
		$text = $text . "<table cellspacing=1 cellpadding=3 border=1 width=200px>";
		
		for ($t = 2; $t < $table_count; $t++) {
			$table_name = str_replace(".txt", "", $tables[$t]);
			$path = $database_schema_name . "." . $table_name;
			
			$file = file($GLOBALS['databases_root'] . "/" . $components[0] . "/" . $components[1] . "/" . $tables[$t]);
			$records = count($file);
			
			$text = $text . "<tr><td><a href='client.php?mode=" . $path . "'>" . $table_name . "</a> (" . $records . ")</tr>";
		}
			
		$text = $text . "</table>";
	
	} else {
		$text = "There are no tables in this database. Create using: create table [table_name] in [schema_name].[database_name]";
	}

	$text = $text . "<p><strong>Create Table</strong><P>";
	$text = $text . "<form id='create_table' method='post' action='client.php'>
				<input id='table_name' type='text' name='create_table_command' size='30'></input>
				<input type='hidden' name='schema_name' value='" . $components[0] . "'></input>
				<input type='hidden' name='database_name' value='" . $components[1] . "'></input>
				<input name='button' type='submit' value='Create'/> 
			</form>";
	
	$text = $text . "<p><a href='client.php?mode=" . $components[0] . "'>Back</a>";
	
	return $text;
	
}

function displayTableContents($table_name) {

	$components = explode(".", $table_name);
	$file = $GLOBALS['databases_root'] . "/" . $components[0] . "/" . $components[1] . "/" . $components[2] . ".txt";
	
	# Handle Data Import
	if (isset($_GET['import'])) {
		importData($components[2], $file, $_GET['import']);
	}
	
	# View File Contents

	$table = file($file);
	$rows = count($table);
	$cols = 0;

	$text = "<strong>" . ucwords($components[2]) . " Table | Contents</strong>";
	$text = $text . "<p><a href='client.php?mode=" . $components[0] . "." . $components[1] . "'>Back</a><p>";
	
	if ($rows > 0) {
	
		$text = $text . "<table cellspacing=1 cellpadding=3 border=1>";
		
		for ($r = 0; $r < $rows; $r++) {
			$columns = explode(",", $table[$r]);
			$cols = count($columns);
			$text = $text . "<tr>";
			for ($c = 0; $c < $cols; $c++) {
				$text = $text . "<td>" . $columns[$c];
			}
			$text = $text . "</tr>";
		}
		$text = $text . "<tr colspan=" . $cols . "><td>" . $rows . " records</tr>";
		$text = $text . "</table>";
	
	} else {
		$text = $text . "There are no records in this table.";
		
		$text = $text . datafileList();
		
	}
	
	$text = $text . "<p><a href='client.php?mode=" . $components[0] . "." . $components[1] . "'>Back</a>";
	
	return $text;

}

function datafileList() {
	
	$datafiles_path = $GLOBALS['datafiles_root'];
	$datafiles = scandir($datafiles_path);
	$datafiles_count = count($datafiles);
	
	$text = "<p><strong>Available Datafiles for Import</strong>";
	$text = $text . "<p><table cellspacing=1 cellpadding=3 border=1>";	
	for ($t = 2; $t < $datafiles_count; $t++) {
		
		$datafile = file($GLOBALS['datafiles_root'] . "/" . $datafiles[$t]);
		$records = count($datafile);
		
		$datafile_name = str_replace(".txt", "", $datafiles[$t]);
		$text = $text . "<tr><td><a href='client.php?mode=" . $_GET['mode'] . "&import=" . $datafile_name . "'>" . $datafile_name . "</a> (" . $records . ")</tr>";
	}
	$text = $text . "</table>";
	
	return $text;
	
}


# -------------------------------------------------------------------------------------------------------
#											S C H E M A   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function createSchema($schema_name) {
	
	#echo "Create Schema: " . $schema_name;
	
	$status = "";
	$schema_path = $GLOBALS['databases_root'] . "/" . $schema_name;
	
	# Check for only ASCII alphanumerics
	$status = checkName($schema_name);
	
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
	
	$schema_path = $GLOBALS['databases_root'] . "/" . $schema_name;
	$status = "";
	
	# Check for only ASCII alphanumerics
	$status = checkName($schema_name);	
	
	
	
}

function deleteSchema($schema_name) {

	$schema_path = "../db/" . $schema_name;
	$status = "";
	
	# Check for only ASCII alphanumerics
	$status = checkName($schema_name);
	
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
	
	$schema_path = $GLOBALS['databases_root'] . "/" . $schema_name;
	$status = "";
	
	# Check database name is valid
	$status = checkName($schema_name);
	$status = checkName($database_name);
	
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
	
	$database_path = $GLOBALS['databases_root'] . "/" . $schema_name . "/" . $database_name;
	$status = "";
	
	# Check for only ASCII alphanumerics
	$status = checkName($schema_name);
	$status = checkName($database_name);
	
	if ($status == "") { # Characters in schema name are fine
	
		if (file_exists($database_path)) {
			rmdir($database_path);		
			$status = "Database deleted.<br/>";
		} else {
			$status = "ERROR: Database does not exist.<br/>";
		}

	}
		
	return $status;	
	
}

function listDatabase($schema_name) {
	
	
	
	
}

# -------------------------------------------------------------------------------------------------------
#										T A B L E   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function createTable($table_name, $schema_database_name) {
	
	$status = "";
	
	# Create file inside database folder
	$names = explode(".", $schema_database_name);
	$schema_name = $names[0];
	$database_name = $names[1];
	
	# Check for only ASCII alphanumerics
	$status = checkName($schema_name);
	$status = checkName($database_name);
	$status = checkName($table_name);	

	$table_path = $GLOBALS['databases_root'] . "/" . $schema_name . "/" . $database_name . "/" . $table_name . ".txt";

	if ($status == "" && !file_exists($table_path)) {
		# If so, create table
		$file = fopen($table_path, "w");
		fclose($file);

		# Check database exists
		if (file_exists($table_path)) {
			$status = "Table '" . $table_name . "' created in database '" . $database_name . "'.<br/>";
		} else {
			$status = "ERROR: Table '" . $table_name . "' not created.<br/>";
		}		

	}
		
	return $status;	

}

function deleteTable($table_name, $database_name, $schema_name) {
	
	
	
	
}

function listContentsTable($table_name, $database_name, $schema_name) {
	
	
	
}

function importData($target_filename, $existing_file, $source_file) {
	
	# target_filename = target table name without extension
	# existing_file = full path, including filename
	# source_file = source filename without extension
	
	# Import datafile
	
	# Delete existing file
	unlink($existing_file);
	
	# Copy data file
	$source = $GLOBALS['datafiles_root'] . "/" . $source_file . ".txt";

	copy($source, $existing_file);
	
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


# -------------------------------------------------------------------------------------------------------
#										G E N E R A L   F U N C T I O N S
# -------------------------------------------------------------------------------------------------------

function checkName($name) {
	
	$status = "";
	
	$name_characters = str_split($name);
	foreach ($name_characters as $character) {
		if (ord($character) >= 97 && ord($character) <= 122) {
			# Do nothing; character is valid between a-z
		} else {
			$status = "ERROR: Only alpha characters between a-z accepted.<br/>";
		}
	}
	
	return $status;
	
}



?>