<?php

class OfferPages
{
    public $Items;
    public $HomeOfferPage;
    private static $CONFIG_DIR = 'next_inc/proj';

    public function  __construct($homeServer)
    {
	$allServers = get_language_list(self::$CONFIG_DIR);
	//if the following line is not commented out,
	//then I fucked up by checking in development code
	//$allServers = array("fr.wikipedia.org");
	
	$indexOfMyServer = -1;
	$indexOfEnWp = -1;
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
    
	    if($oneServer == $homeServer)
	    {
		$indexOfMyServer = $i;
	    }
	    
	    if($oneServer == "en.wikipedia.org")
	    {
		$indexOfEnWp = $i;
	    }
	    $i++;
	}
	$this->PutHomeServerFirst($indexOfMyServer, $indexOfEnWp);
    }
    
    function PutHomeServerFirst ($indexOfMyServer, $indexOfEnWp)
    {	if($indexOfMyServer==-1)
	{
	    $indexOfMyServer = $indexOfEnWp;
	}
	
	if($indexOfMyServer!=-1)
	{
	    $firstServer = $this->Items[0];
	    $this->Items[0] = $this->Items[$indexOfMyServer];
	    $this->Items[$indexOfMyServer] = $firstServer;
	}
    }


}
