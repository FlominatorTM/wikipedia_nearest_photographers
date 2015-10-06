<?php

class OfferPages
{
    public $Items;
    public $HomeOfferPage;
    private $AllOffers;
    private static $CONFIG_DIR = 'next_inc/proj';

    public function  __construct($homeServer)
    {
	$allServers = get_language_list(self::$CONFIG_DIR);
	//if the following line is not commented out,
	//then I fucked up by checking in development code
	//$allServers = array("fr.wikipedia.org");
	
	$i=0;
	foreach($allServers as $oneServer)
	{
	    $ConfigFile = self::$CONFIG_DIR . '/' . $oneServer . '.php';
	    if(!file_exists($ConfigFile))
	    {
		die(str_replace('_PROJECT_', $this->server, $messages['proj_not_supported']));
	    }
	    else
	    {
		include($ConfigFile);
	    }
	    $this->Items[]= $createdPage;
	    $i++;
	}
    }
    
    function MergeOffers($locTo)
    {
	foreach($this->Items as $oneOfferPage)
	{
	    $oneOfferPage->CalculateDistance($locTo);
	    for($i=0;$i<$oneOfferPage->GetNumberOfUsers();$i++)
	    {
		$oneUser =  $oneOfferPage->GetUserAt($i);
		if($oneUser->IsValid())
		{
		    $this->AllOffers[] = $oneUser;
		}
	    }
	}
	usort($this->AllOffers , array("OfferingUser", "CompareDistance"));
    }
    
    public function ListUsersToRequest()
    {
	global $messages;
	foreach($this->AllOffers as $usr)
	{
	    $resLine = $usr->Link . "  (" . sprintf("%01.1f",$usr->distance)  . " km)";
	    
	    if($usr->IsInRange())
	    {
		    echo "<b>$resLine</b>";
	    }
	    else
	    {
		    echo "$resLine";
	    }
	    
	    echo " <small>" . $usr->LinkHome . "</small>";

	    if($usr->HasDuration())
	    {
		echo " ";
		$now = time();

		if($usr->dateFrom < $now)
		{
		    if($usr->dateTo < $now)
		    {
			echo str_replace('_DATE_', strftime("%x", $usr->dateTo), $messages['until_date_over']);
		    }
		    else
		    {
			echo str_replace('_DATE_', strftime("%x", $usr->dateTo), $messages['until_date']);
		    }
		}
		else
		{
		    $out = str_replace('_FIRST_DATE_', strftime("%x", $usr->dateFrom), $messages['between_dates']);
		    echo str_replace('_SECOND_DATE_', strftime("%x", $usr->dateTo), $out);
		}
	    }
	    echo "<br>";
	}
    }
}
