<?php

class OfferPages
{
    public $Items;
    public $HomeOfferPage;
    private $AllOffers;
    private static $CONFIG_DIR = 'next_inc/proj';

    public function  __construct($homeServer)
    {
		global $I18N;
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
			die($I18N->msg( 'proj_not_supported', array('variables' => array( $this->server ))));
			}
			else
			{
			include($ConfigFile);
			}
			$this->Items[]= $createdPage;
			if($oneServer == $homeServer)
			{
			$this->HomeOfferPage = $createdPage;
			}
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
	global $I18N;
	echo '<table border="1">';
	echo "<tr>";
	echo "<th>" . $I18N->msg( 'column_user') ."</th>";
	echo "<th>" . $I18N->msg( 'column_distance') ."</th>";
	echo "<th>" . $I18N->msg( 'column_wiki') ."</th>";
	echo "</tr>";
	foreach($this->AllOffers as $usr)
	{
	    echo "<tr>";
	     $resLine = "<td>" . $usr->Link;
	
	    if($usr->IsInRange())
	    {
		    echo "<b>$resLine</b>";
	    }
	    else
	    {
		    echo "$resLine";
	    }
	    
	    if($usr->HasDuration())
	    {
			echo "<br/><small>";
			$now = time();

			if($usr->dateFrom < $now)
			{
				if($usr->dateTo < $now)
				{
				echo $I18N->msg( 'until_date_over', array('variables' => array(strftime("%x", $usr->dateTo))));
				}
				else
				{
				echo $I18N->msg( 'until_date',  array('variables' => array(strftime("%x", $usr->dateTo))));
				}
			}
			else
			{
				echo $I18N->msg( 'between_dates',  array('variables' => array(strftime("%x", $usr->dateFrom), strftime("%x", $usr->dateTo))));
			}
			echo "</small>";
	    }
		echo '</td>';
		echo "<td>" . sprintf("%01.1f",$usr->distance)  . " km</td>";
	    echo ' <td>' . $usr->LinkHome . "</td>";
	    echo "</tr>";
	}
	echo "</table>";
    }
}
