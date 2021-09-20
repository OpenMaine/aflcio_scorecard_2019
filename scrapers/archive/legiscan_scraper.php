<?php

	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	


/*
	- GET LIST OF LEGISLATORS

	- GET LIST OF ROLL CALL VOTES

	- 
*/


// LD # => Vote #s that we're reporting on
$bills = array(
	"LD1770" => array(714, 629),
	"LD1704" => array(513, 710),
	"LD837"  => array(680, 669),
	"LD1757" => array(581, 572),
	"LD1913" => array(677),
	"LD1769" => array(543, 541),
	"LD31" 	 => array(635),
	"LD1880" => array(637),
	"LD521"  => array(547),
	"LD1846" => array(634),
	"LD1566" => array(685, 594),
	"LD1912" => array(740)
);



// TAKE ALL MAINE DATA FROM LEGISCAN - MAKE A LIST OF TARGET BILLS
$votes = (array) json_decode(file_get_contents('master_list.js'));

// GO THROUGH EVERY BILL
foreach($votes as $v){
	
	// If the bill # is one that we're looking for...
	$ldnum = $v -> number;
	if(isset($bills[$ldnum])) {


		// rewrite the bill object on the hash as...
		$bill = array(

			// our vote numbers (from above)
			"votes" => $bills[$ldnum],

			// the new data from LegiScan on the bill
			"bill_num" => $v -> bill_id
		);
		$bills[$ldnum] = $bill;
	}
}


// AT THIS POINT: WE HAVE INDEX #s FOR ALL THE BILLS WE'RE INTERESTED IN


// NOW GO THROUGH THAT LIST OF BILLS, AND GET THE LIST OF VOTES
foreach($bills as $ldnum => $bill_meta){
	$url = 'https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getBill&id=' . $bill_meta['bill_num'];
	$bill = json_decode(file_get_contents($url));

//	if($ldnum == "LD31") print_r($bill);

	// MOS BILLS DON'T HAVE VOTE INFO, MAKE SURE THIS ONE DOES
	if(isset($bill -> bill -> votes)){
		$votes = $bill -> bill -> votes;
		foreach($votes as $v){

			// IF THE VOTE # is IN THE LIST OF VOTES (AT TOP)
			$num = explode('#', $v -> desc)[1];
			if(in_array($num, $bill_index['votes']))

				// GET THE LEGISCAN ROLL CALL ID FOR THE VOTE
				$bills[$ldnum]['vote_index'][$num] = $v -> roll_call_id;
		}
	}

	else {
		echo "bad bill!";
		print_r($bill);
	}

}



// USE ROLL CALL INDEXES TO GET VOTES
$people = array();

foreach($bills as $ldnum => $bill){

	if(!isset($bill['vote_index'])) {
		continue;
	}

	foreach($bill['vote_index'] as $vote_id => $roll_call_id){

		$url = 'https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getRollCall&id=' . $roll_call_id;
		$roll_call = json_decode(file_get_contents($url));
		$votes = $roll_call -> roll_call -> votes;
		foreach($votes as $v){
			if(!isset($people[$v -> people_id])) $people[$v -> people_id] = array();
			$people[$v -> people_id][$ldnum] = $v -> vote_text;
		}

	}
}

echo json_encode($people, JSON_PRETTY_PRINT);



// GET LEGISCAN INFORMATION ON THE LEGISLATORS

$people = json_decode(file_get_contents('votes.js'));
$legislators = array();
foreach($people as $person_id => $votes){
	$url = 'https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getPerson&id=' . $person_id;
	$person = json_decode(file_get_contents($url));
	$legislators[] = array(
		"votes" => $votes,
		"person" => $person -> person
	);
}

 echo json_encode($legislators, JSON_PRETTY_PRINT);











