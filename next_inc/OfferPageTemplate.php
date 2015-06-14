<?php

class OfferPageTemplate extends OfferPage
{
    private $TemplateUser;
    private $TemplateLocation;
    private $TemplateRange;
    private $TemplateDateFrom;
    private $TemplateDateUntil;
    private $TemplateName;

    public static $CACHE_DIR = 'next_inc/cached';
    function __construct($theServer, $OfferPageName, $MoreConfig) 
    {
	$this->SetConfigValue($MoreConfig, "TemplateName", $this->TemplateName, true);
	$this->SetConfigValue($MoreConfig, "TemplateUser", $this->TemplateUser, true);
	$this->SetConfigValue($MoreConfig, "TemplateLocation", $this->TemplateLocation, true);
	$this->SetConfigValue($MoreConfig, "TemplateRange", $this->TemplateRange, true);
	$this->SetConfigValue($MoreConfig, "TemplateDateFrom", $this->TemplateDateFrom, false);
	$this->SetConfigValue($MoreConfig, "TemplateDateUntil", $this->TemplateDateUntil, false);
	$this->Init($theServer, $OfferPageName);
	
    }
    
 
    
    function GenerateUsers($page_src)
    {
	print_debug('length' . strlen($page_src));
	if(strlen($page_src)==0)
	{
	    print_debug('page handed over to GenerateUsers was empty');
	    return;
	}
	$page_parts = explode('{{'.$this->TemplateName, $page_src);

	foreach($page_parts as $template)
	{
	    print_debug("<hr>");
	    print_debug("<h1>template</h1>$template");

	    $usr = new OfferingUser(extract_template_parameter($template, $this->TemplateUser));
	    $usr->IsBeingRetrieved();
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
	
	if(count($this->userOffers))
	{
	    print_debug("<b>no users found - looks weird</b>");
	}
    }
	
}
	
