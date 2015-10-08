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
	    
	    $indexOfTemplateEnd = strpos($listEntry, "}}");
	    $userName = substr($listEntry, 0, $indexOfTemplateEnd);
	    print_debug("index of template end=$indexOfTemplateEnd");
	    
	    $beginningOfDescription = $indexOfTemplateEnd;
	    if(stristr($listEntry, "aka"))
	    {
		$indexOfEndOfAka = strpos($listEntry, "]])")  + strlen("]])");
		$beginningOfDescription = $indexOfEndOfAka;
		print_debug("aka found. index of end of aka is $indexOfEndOfAka <br>");
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
		$usr->IsBeingRetrieved();
		$location = new GeoLocation($locName, $this->server);
		$usr->SetLocation($location);
		$this->userOffers[] = $usr;
	    }
	}
    }
}
	
