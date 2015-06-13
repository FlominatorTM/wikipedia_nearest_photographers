<?php

// not intended for productive usage but nice for development anyway
class OfferPageTableFromFile extends OfferPageTable
{
   
    function GetPageSource()
    {
	echo getcwd();
	return file_get_contents("./cached/it.txt");
    }
	
    
}
	
