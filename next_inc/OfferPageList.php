<?php

class OfferPageList extends OfferPage
{
    function __construct($theServer, $OfferPageName, $conf) 
    {
//	$this->SetConfigValue($conf, 'UserPrefixMale', $this->UserPrefixMale, true);
	$this->Init($theServer, $OfferPageName);
    }

//    function GetPageSource()
//    {
//	echo getcwd();
//	return file_get_contents("./cached/fr.txt");
//    }
    
    function GenerateUsers($page_src)
    {
	
	$page_parts = explode("{{u|", $page_src);
	print_debug("page_parts:" . count($page_parts));
	$isFirstItem = true;
	foreach($page_parts as $listEntry)
	{
	    if($isFirstItem)
	    {
		$isFirstItem = false;
		continue;
	    }
	    print_debug("<b>row</b>:" . $listEntry . "<br>");
	    
	    $indexOfFirstPipe = strpos($listEntry, "}}");
	    $userName = substr($listEntry, 0, $indexOfFirstPipe);
	    
	    
	    $beginningOfDescription = $indexOfFirstPipe;
	    if(stristr($listEntry, "aka"))
	    {
		$indexOfEndOfAka = strpos($listEntry, "]])");
		$beginningOfDescription = $indexOfEndOfAka;
	    }
	    print_debug("<b>$userName</b>");
	    if($locName = extract_link_target(substr($listEntry, $beginningOfDescription)))
	    {
		if(stristr($locName, ":"))
		{
		   $locName = false; 
		}
	    }
	    
	    if($userName && $locName)
	    {
		$usr = new OfferingUser($userName);
		$location = new GeoLocation($locName, $this->server);
		$usr->SetLocation($location);
		if($usr->IsValid())
		{
		    print_debug("user $usr->name is valid: ".$usr->ToString());
		    $this->userOffers[] = $usr;
		}
		else
		{
		    print_debug("user $usr->name is <b>not</b> valid: ".$usr->ToString());
		}

	    }
	}
    }
}
	
