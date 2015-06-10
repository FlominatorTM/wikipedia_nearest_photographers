<?php

class OfferPageTable extends OfferPage
{
    private $UserPrefixMale;
    private $UserPrefixFemale;
    private $IndexUserColumn;
    private $IndexLocationColumn;

    public static $CACHE_DIR = 'next_inc/cached';
    function __construct($theServer, $OfferPageName) 
    {
	$this->Init($theServer, $OfferPageName);
    }
    
    function InitSpecificVariables() 
    {
	global $UserPrefixMale, $UserPrefixFemale, $IndexLocationColumn, $IndexUserColumn;
	$this->UserPrefixMale = $UserPrefixMale;
	$this->UserPrefixFemale = $UserPrefixFemale;
	$this->IndexLocationColumn = $IndexLocationColumn;
	$this->IndexUserColumn = $IndexUserColumn;
    }
    
    function GenerateUsers($page_src)
    {
	$page_parts = explode("|-", $page_src);
	 print_debug("page_parts:" . count($page_parts));
	foreach($page_parts as $table_row)
	{
	    print_debug("<b>row</b>:" . $table_row . "<br>");
	    $cols = explode("||", $table_row);

	    print_debug("cols has " . count($cols));
	    if(count($cols)>= max($this->IndexUserColumn, $this->IndexLocationColumn) )
	    {
		print_debug("cols is big enough");
		$user_column_small = strtolower($cols[$this->IndexUserColumn]);
		print_debug("user_column_small:" . $user_column_small  . "<br>");

		if( strlen($user_column_small) > 0 
		    && 
			(   stristr($user_column_small, "user")
			    || stristr($user_column_small, strtolower($this->UserPrefixMale) )
			    || stristr($user_column_small, strtolower($this->UserPrefixFemale) )
			)
		    ) 
		{
		    $usr_name = extract_link_target($cols[$this->IndexUserColumn], true);
		    $loc_name = extract_link_target($cols[$this->IndexLocationColumn]);
		    print_debug("usr:" . $usr_name ." loc:". $loc_name);
		    if($usr_name && $loc_name)
		    {
			    $usr = new OfferingUser($usr_name);
			    $location = new GeoLocation($loc_name, $this->server);
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

    }
}
	
