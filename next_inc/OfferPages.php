<?php

class OfferPages
{
    public $Items;
    public $HomeOfferPage;
    private static $CONFIG_DIR = 'next_inc/proj';

    public function  __construct($homeServer)
    {
	$allServers = get_language_list(self::$CONFIG_DIR);
	
	$indexOfMyServer = -1;
	$i=0;
	foreach($allServers as $oneServer)
	{
	    $this->Items[]= new OfferPage(self::$CONFIG_DIR, $oneServer );
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
