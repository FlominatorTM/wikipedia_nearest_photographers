<?php
//exports all photographers with valid coordinates to JSON

//underscore stuff is legacy, camel-cased is php 5
require_once("setup.php");

$allOfferPages = new OfferPages($server);

$users = array();
print_debug ("start");

foreach($allOfferPages->Items as $oneOfferPage)
{
    for($i=0;$i<$oneOfferPage->GetNumberOfUsers();$i++)
    {
		$usr = $oneOfferPage->GetUserAt($i);
		if($usr->IsValid() && $usr->location->hasCoordinates)
		{
			$users[] = array("user" => $usr->name,
						"latitude" =>  $usr->location->lat,
						"longitude" =>  $usr->location->lon,
						"project" => $oneOfferPage->server,
						"page" => $oneOfferPage->OfferPageName);
		}
    }
}
 print_debug("ende");
//var_dump($users);
var_dump(json_encode($users));
?>