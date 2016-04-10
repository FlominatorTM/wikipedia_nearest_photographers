<?php
//exports all photographers with valid coordinates to JSON

//underscore stuff is legacy, camel-cased is php 5

require_once __DIR__ . '/vendor/autoload.php';
include("shared_inc/language.inc.php");
include("shared_inc/wiki_functions.inc.php");
include('next_inc/OfferingUser.php');	
include('next_inc/OfferPage.php');	
include('next_inc/OfferPageTemplate.php');	
include('next_inc/OfferPageList.php');	
include('next_inc/OfferPageTable.php');	
include('next_inc/OfferPageTableFromFile.php');	
include('next_inc/OfferPages.php');	
include('next_inc/GeoLocation.php');

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
echo json_encode($users);
?>