

HELLO. HERE'S WHAT YOU NEED TO KNOW:



------------------------

PROJECT HISTORY

------------------------

This is the original version of the project:
- https://github.com/pmlord/legislative-scorecard

It was originally written for the MPA in April-July of 2018.

Written by:
- Peter M. Lord
- pmlord@gmail.com


Designed by (I believe):
- Shannon McHarg
- shannon@efficientinteraction.com


Project Manged by:
- Em Burnett
- elburnett@gmail.com


---


Rob Korobkin forked it for the ME AFL-CIO later that year:
https://github.com/OpenMaine/aflcio_scorecard/commits/master

What changed (primarily)?
- New template skin for the new organization.
- New data files for new scorecard.


Rob Korobkin
- Rob.Korobkin@gmail.com


--


Rob forked it again a year later (2019) to do the ME AFL CIO's subsequent card:
https://github.com/OpenMaine/aflcio_scorecard_2019


That's the code that's currently running at:
www.OpenScorecard.org



------------------------

HOW DO YOU UPDATE IT?

------------------------


The app consists primarily of a series of views (that have no data):
- src/components

And a series of data files (JSON) that provide the content for the views:
- src/data
	- content pages (aboutSections, FAQ)
	- a reference list of Maine towns (townNames.json)
	- bill information (bills.json)
	- legislator / vote information (legislators.json)

There's also a collection of headshots of legislators:
- public/legislator-photos

And some skinning stuff:
- public/theme
- src/App.css

--

CONTENT
-
The content pages likely won't need to change much, and if they do, they're simple enough that you can probably just update the JSON files yourself.


BILLS
-
To update the bills.json file, I wrote a thing that takes a simple text file (bills.txt) and runs a PHP parser against it (bill_parser.php), that reads that text file into an array, opens a target file (bills.json), and JSON-ENCODES it into there. As long as you format the bills.txt file correctly, you should be able to copy and paste everything into there, and then parse it fairly straightforwardly, either by putting it into your htdocs and invoking your PHP interpretor from the browser, or just by running the parser from the CLI with the PHP utility.



LEGISLATORS
-
Which brings us to the legislator info (legislators.json)... This is the doozy haha. 

I'd encourage you to start by looking at the file itself:


// this is junk

"ocdId": "ocd-division\/country:us\/state:me\/sldl:1", 




// BASIC: this you need to build (you can skip the blank fields)

"legislative_chamber": "House",
"districtNum": 1,
"towns": "Kittery (part)",
"name": {
	"firstName": "",
	"lastName": "",
	"fullName": "Deane Rykerson"
},
"party": "Democratic",
"legal_residence": "",
"address": "1 Salt Marsh Lane, Kittery Point, ME 03905",
"email": "Deane.Rykerson@legislature.maine.gov",
"phone": "",
"political_party": "Democratic",
"seeking_reelection": "",
"term_limited": "",



// PHOTO / LINK: these point to the official state website (images should maybe be copied over)

"url": "https:\/\/legislature.maine.gov\/house\/house\/MemberProfiles\/Details\/136",

"photo_url": "https:\/\/legislature.maine.gov\/house\/house\/Repository\/MemberProfiles\/fd4f5f00-174b-478a-97ee-37f9c5eb84ff_ryked_[1].jpg",

- Note: the images are actually varying size and in color. CSS styles grayscale them and display them at the same size.



// VOTES

"orgscore": "80",
"votes": {
	"LD1177": "Supported",
	"LD1658": "Supported",
	"LD1459": "Absent",
	"LD1282": "Supported",
	"LD1232": "Opposed",
	"LD240": "Absent",
	"LD1564": "Supported",
	"LD1524": "Supported",
	"LD1560": "Supported",
	"LD369": "Supported"
}


--

Here's how I assembled this file:

- So your first problem is that the last JSON file was before the 2020 election, so it needs to be replaced with new info.

- To begin, you want a correctly formatted JSON file of all of the legislators (votes will come later). OpenStates.org has changed a bit since I first worked on this project, but I was able to get JSON for both houses buried in the source of these two webpages:
https://openstates.org/me/legislators/?chamber=upper
https://openstates.org/me/legislators/?chamber=lower

- I then formatted that JSON string (turned out both pages were loading all the data for both houses and just not showing it all), cleaned it up using a free web utility (so they'd be pretty) and copied them into: scrapers/archive/openstates_maine.json

- At this point, you have most of the data you need on the legislators (minus the votes), it's just formatted wrong for the app. So we need to write a script that reads the OpenStates file, json_decodes it, and build out a new array by iterating through the decoded data and mapping the fields to fields of the right names / paths for the app to read. Then json_encode the new array and get the new JSON into a new file.


- Here's what you can get out of that file:

"title":"Senator",
"district":14,
"name":"Craig Hickman",
"primary_party":"Democratic",
"image":"https://legislature.maine.gov/uploads/visual_edit/hickman.jpg",
"pretty_url":"/person/craig-hickman-2kLvM5S6s1xHTpko05Ee98/"
"id":"ocd-person/5a475e6f-5277-425e-bb09-1c55facbc736",


- But... here's what you can't get:

"towns": "Kittery (part)",
"address": "1 Salt Marsh Lane, Kittery Point, ME 03905",
"email": "Deane.Rykerson@legislature.maine.gov",
"phone": "",
"seeking_reelection": "",
"term_limited": "",


- Some ideas:

	- You can fill in the "town" field by merging against a previous year's legislators.json file?

	- You could query OpenStates for more info than we scraped at first by writing a crawler to query for each legislator?

		- Here's my OpenStates.org API Key: 3900df6b-e5ca-4843-a498-d91ee5128de1
	
		- Their documentation is here: https://v3.openstates.org/docs#/people/people_search_people_get
	
		- Example API call (for Craig). You should be able to just paste this into your browser: 

		https://v3.openstates.org/people?page=1&per_page=10&apikey=3900df6b-e5ca-4843-a498-d91ee5128de1&include=offices
		&id=ocd-person%2F5a475e6f-5277-425e-bb09-1c55facbc736 (id variable from above)



	- Yay! Looks like that query gets you: address, email, phone, which you can merge back in. You can loop through the json file, drill down for each legislator, and then merge the additional data you fetch on them back into your JSON file.


	- You still don't have seeking_reelection and term_limited... But I'm not sure where to find them. I believe these may have originally been keyed in manually for the last session by MPA staff? In any case, the app doesn't currently display them, and I can't seem to find an API that would provide them. 




NOW FOR THE VOTES:

- In 2019, ME AFL-CIO just provided me with CSVs (Excel files) of votes and I merged against them. Relatively easy:
	- scrapers/archive/provided_csv_house.csv
	- scrapers/archive/provided_csv_parser.csv

	- This is obviously the right way to solve this. You're just merging the CSV into your JSON file to populate the orgScore and votes hash for each legislator (example above).




LEGISCAN CLIENT
=============

- In 2018, however, I wrote a scraper to aggregate this data through the LegiScan API. 
- It was a ton of work and largely unnecessary, but LegiScan is cool.


- Code is in: scrapers/archive/legiscan_scraper.php

- Here's my LegiScan API Key: 1e80ba158a1e3d78f832609310a4ede2
- Here's their API guide: https://legiscan.com/gaits/documentation/legiscan

You can also download a bunch of stuff here:
- https://legiscan.com/ME/datasets


// GET LIST OF LEGISLATIVE SESSIONS
	https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getSessionList&state=ME


// GET "MASTER LIST" OF BILLS FROM LAST YEAR
	https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getMasterList&id=1635


// GET VOTES FOR A PARTICULAR BILL
	"An Act To Expand Options for Consumers of Cable Television in Purchasing Individual Channels and Programs" (LD832) 
	- LegiScan ID #1200320
	https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getBill&id=1200320


// GET LEGISLATORS FOR A PARTICULAR VOTE
	"Acc Maj Ought To Pass Rep RC #132" (2019-05-28) - LegiScan ROLL CALL ID: 873553
	https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getRollCall&id=873553


// GET INFO FOR A PARTICULAR LEGISLATOR
	https://api.legiscan.com/?key=1e80ba158a1e3d78f832609310a4ede2&op=getPerson&id=8744









