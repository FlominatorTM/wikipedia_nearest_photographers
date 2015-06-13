<?php

class OfferPageTemplate extends OfferPage
{
    public $TemplateUser;
    public $TemplateLocation;
    public $TemplateRange;
    public $TemplateDateFrom;
    public $TemplateDateUntil;
    public $TemplateName;


    public static $CACHE_DIR = 'next_inc/cached';
    function __construct($theServer, $OfferPageName) 
    {
	$this->Init($theServer, $OfferPageName);
    }
    
        function InitSpecificVariables() 
	    
    {
	
	return;
	echo "TamplateName=". $TemplateName;
	$this->TemplateName = $TemplateName;
	$this->TemplateUser = $TemplateUser;
	$this->TemplateLocation = $TemplateLocation;
	$this->TemplateRange = $TemplateRange;

	if(isset($TemplateDateFrom))
	{
	  $this->TemplateDateFrom = $TemplateDateFrom;
	}

	if(isset($TemplateDateUntil))
	{
		 $this->TemplateDateUntil = $TemplateDateUntil;
	}
	$this->Init($theServer, $OfferPageName);
    }
    

    
    function GenerateUsers($page_src)
    {
	$page_parts = explode('{{'.$this->TemplateName, $page_src);

	foreach($page_parts as $template)
	{
	    print_debug("<hr>");
	    print_debug("<h1>template</h1>$template");

	    $usr = new OfferingUser(extract_template_parameter($template, $this->TemplateUser));

	    $location = new GeoLocation(extract_template_parameter($template, $this->TemplateLocation), $this->server);
	    $usr->SetLocation($location);
	    print_debug("<b>".$location->name."</b>");
	    $range = extract_template_parameter($template, $this->TemplateRange);
	    $dateFrom = trim(extract_template_parameter($template, $this->TemplateDateFrom));
	    $dateUntil = trim(extract_template_parameter($template, $this->TemplateDateUntil));

	    //echo "Ö $dateFrom Ö ! Ö $dateUntil Ö";
	    $usr->SetDateRangeISO($dateFrom, $dateUntil);

	    // print_debug("<b>".$range."</b><br />");
	    $usr->SetRange($range);

	    //print_debug($usr->ToString());

	    if($usr->IsValid())
	    {
		print_debug("user $usr->name is valid: ".$usr->ToString());
		$this->userOffers[] = $usr;
	    }
	}
    }
	
}
	
