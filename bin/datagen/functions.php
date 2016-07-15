<?php

function dataGenerator($records) {
	
	$filename = time();
	$file = fopen("files/" . $filename . ".txt", "w");
	
	for ($f = 0; $f < $records; $f++) {
		
		$name = getName();
		$address = getAddress();
		
		$data = $name . "," . $address;
		
		fwrite($file, $data);
		
	}
	
	fclose($file);
	
}

function getName() {
	
	$forename = getForename();
	$surname = getSurname();
	
	return $forename . " " . $surname;
	
}



function getForename() {
	
	$forenames = file('lists/forenames.txt');
	$count = count($forenames);
	$select = rand(0, $count);
	
	return ucwords(strtolower(trim($forenames[$select])));
	
}

function getSurname() {
	
	$surnames = file('lists/surnames.txt');
	$count = count($surnames);
	$select = rand(0, $count);
	
	return trim($surnames[$select]);
	
}

function getAddress() {
	
	$addresses = file('lists/streetnames.txt');
	$count = count($addresses);
	$select = rand(0, $count);
	
	$number = rand(1, 300);
	
	return $number . " " . ucwords(strtolower($addresses[$select]));

}

?>