<?php

/*

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>

    <style>
        
        .pic {
            width: 50px;
            height: 50px;

            border: solid 2px #02455F;

            border-radius: 25px;
            background-size: 50px;
            background-position: center;

            -moz-filter: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\'><filter id=\'grayscale\'><feColorMatrix type=\'matrix\' values=\'0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0\'/></filter></svg>#grayscale");
         -o-filter: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\'><filter id=\'grayscale\'><feColorMatrix type=\'matrix\' values=\'0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0\'/></filter></svg>#grayscale");
         -webkit-filter: grayscale(100%);
         filter: gray;
         filter: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\'><filter id=\'grayscale\'><feColorMatrix type=\'matrix\' values=\'0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0\'/></filter></svg>#grayscale");

        }

    </style>


</head>
<body>
    
*/


	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	$json = file_get_contents('legislators_2019.js');
	$legislators = json_decode($json);





	// LOAD HOUSE VOTES


		$csv = file_get_contents('house.csv');
		$house_votes = readFileAsCSV($csv);

		foreach($house_votes as $h){
			$house_hash[$h['district']] = $h;
		}

		foreach($legislators as &$l){
			if($l -> legislative_chamber == 'House'){
				$votes = $house_hash[$l -> districtNum];


				$lds = array('LD1177','LD1658','LD1459','LD1282','LD1232','LD240','LD1564','LD1524','LD1560','LD369',);

				foreach($votes as $k => $v){
					if(in_array($k, $lds)){
						if($v == 'Y') $v = 'Supported';
						if($v == 'A') $v = 'Absent';
						if($v == 'N') $v = 'Opposed';
						if($v == 'E') $v = 'Excused';

						if($k == 'LD1232') {
							if($v == 'Supported') $v = 'Opposed';
							else if($v == 'Opposed') $v = 'Supported';
						}

						$l -> votes[$k] = $v;
					}
				}

				$l -> orgscore = explode('%', $votes['rating'])[0];


			}
		}




	// LOAD SENATE VOTES


		$csv = file_get_contents('senate.csv');
		$senate_votes = readFileAsCSV($csv);

		foreach($senate_votes as $h){
			$senate_hash[$h['district']] = $h;
		}


		foreach($legislators as &$l){
			if($l -> legislative_chamber == 'Senate'){
				$votes = $senate_hash[$l -> districtNum];

				$lds = array('LD1177','LD1658','LD1459','LD1282','LD1232','LD240','LD1564','LD1524','LD1674','LD777');

				foreach($votes as $k => $v){
					if(in_array($k, $lds)){
						if($v == 'Y') $v = 'Supported';
						if($v == 'A') $v = 'Absent';
						if($v == 'N') $v = 'Opposed';
						if($v == 'E') $v = 'Excused';

						if($k == 'LD1232') {
							if($v == 'Supported') $v = 'Opposed';
							else if($v == 'Opposed') $v = 'Supported';
						}

						$l -> votes[$k] = $v;
					}
				}

				$l -> orgscore = explode('%', $votes['rating'])[0];

			}
		}

    

	print_r($legislators);



	$myfile = fopen("legislators.json", "w") or die("Unable to open file!");
	$txt = json_encode($legislators, JSON_PRETTY_PRINT);
	fwrite($myfile, $txt);
	fclose($myfile);



    function readFileAsCSV($csv, $unique_fields = array(), $primaryKey = ''){
		$data = array();
		$uniques = array();

		$file_str = $csv;
		$rows = explode("\n", $file_str);

		foreach($rows as $k => $row){
			$fields = str_getcsv(trim($row));

			// get field names from the first row
			if($k == 0) {
				foreach($fields as $f) $headers[] = trim($f);
				continue;
			}

			if(count($fields) == 1) continue;

			$rowData = array_combine($headers, $fields);


			// compile a list of unique values ()
			foreach($unique_fields as $u){
				if($rowData[$u] != ''){
					$uniques[$u][$rowData[$u]] = ''; //$rowData[$primaryKey];
				}


			}

			$data[] = $rowData;

		}

		if(count($uniques) > 0) print_r($uniques);

		return $data;
	}





/*


MAKE THIS:

 [id] => ocd-person/82b51bea-5f96-46d6-bbcf-4d973f701b27
[name] => Allison Hepler
[current_party] => Democratic
[current_district] => 53
[current_chamber] => lower
[given_name] => 
[family_name] => 
[image] => https://legislature.maine.gov/house/house/Repository/MemberProfiles/a75da791-060e-45cd-b1bd-95bc7c00c6e8_hepler.jpg
[sources] => https://legislature.maine.gov/house/house/MemberProfiles/Details/1353
[district_address] => 417 Montsweag Road, Woolwich, ME 04579
[district_email] => Allison.Hepler@legislature.maine.gov


	

LOOK LIKE THIS:

	{
		
        // DISTRICT INFO

		"ocdId": "ocd-division/country:us/state:me/sldl:1",
		"legislative_chamber": "House",
		"districtNum": 1,
		"towns": "Kittery (part)",
		"party": "Democrat",



        // SPECIFIC TO PERSON

        "name": {
            "firstName": "Deane",
            "lastName": "Rykerson",
            "fullName": "Deane Rykerson"
        },

		"legal_residence": "Kittery",
		"address": "1 Salt Marsh Lane, Kittery Point, ME	03905",
		"email": "Deane.Rykerson@legislature.maine.gov",
		"phone": "(207) 439-8755",
		"photo_url": "https://legislature.maine.gov/house/photo128/ryked_.jpg",
		"url": "http://legislature.maine.gov/house/hsebios/ryked_.htm",
		"political_party": "Democrat",
		"seeking_reelection": true,


		"votes": {
			"LD 31": "Opposed",
			"LD 1444": "Supported",
			"LD 390": "Opposed",
			"LD 673": "Opposed",
			"LD 1210": "Supported",
			"LD 1476": "Supported",
			"LD 1566": "Supported",
			"LD 1609": "Opposed",
			"LD 1625": "Opposed",
			"LD 1646": "Opposed",
			"LD 1707": "Supported",
			"LD 1757": "Opposed",
			"LD 1833": "Opposed",
			"LD 1864": "Supported",
			"LD 1865": "Supported",
			"LD 1904": "Opposed",
			"LD 873": "Supported",
			"LD 1684": "Supported"
		},
		"mpaScore": 92,
		"voterScore": 91,
		"term_limited": 2020
	}

*/
?>

</body>
</html>

