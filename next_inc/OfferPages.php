<?php

class OfferPages
{
    public $Items;
    public $HomeOfferPage;
    private static $CONFIG_DIR = 'next_inc/proj';

    public function  __construct($homeServer)
    {
	$allServers = get_language_list(self::$CONFIG_DIR);
	//$allServers = array("en.wikipedia.org");
	
	$indexOfMyServer = -1;
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
	    $i++;
	}
	
	$this->PutHomeServerFirst($indexOfMyServer);
    }
    
    function PutHomeServerFirst ($indexOfMyServer)
    {	if($indexOfMyServer!=-1)
	{
	    $firstServer = $this->Items[0];
	    $this->Items[0] = $this->Items[$indexOfMyServer];
	    $this->Items[$indexOfMyServer] = $firstServer;
	}
    }


}
