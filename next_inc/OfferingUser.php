<?php

class OfferingUser
{
    public $location;
    public $range =-1;
    public $name ="";
    public $distance =-1; 
    public $dateFrom;
    public $dateTo;
    public $Link;
    public $LinkHome;

    public function ToString() {
		$loc = $this->location;
        return "<b>User $this->name at ". $loc->name  ." has range of $this->range <br>" .$loc->ToString() . "</b>";
    }
	
	public function SetLocation($loc_in)
	{
		$this->location = $loc_in;
	}
	
	public function SetRange($ran_in)
	{
		$this->range = $ran_in;
	}
	
	public function SetDistance($loc_to)
	{
		$this->distance = $this->location->GetDistanceTo($loc_to);
	}
	
	
	function __construct($name_in) 
	{
		$this->name = strip_tags($name_in);
	}
	
	public function IsValid()
	{
		$loc = $this->location;
		return $loc->IsValid();
	}
	
	public function IsBeingRetrieved()
	{
	    global $I18N;
	    print_debug($I18N->msg( 'user_retrieval', array("variables" => array($this->name))).'<br>');
	}
	
	public function IsInRange()
	{
		return $this->range >= $this->distance;
	}
	// http://php.net/manual/de/function.usort.php
	static function CompareDistance($a, $b)
    {
        if ($a->distance == $b->distance) {
            return 0;
        }
        return ($a->distance > $b->distance) ? +1 : -1;
    }
	
	public function LinkToUser($server, $homeLink)
	{
	    $this->Link = "<a href=\"http://$server/wiki/User:" . $this->name . "\">" . $this->name . "</a>";
	    $this->LinkHome = $homeLink;
	}
	
	
	public function SetDateRangeISO($from, $to)
	{
		$fromParts = explode('-', $from);
		$toParts =  explode('-', $to);
		
		if(count($fromParts) == 3 && count($toParts)==3)
		{
			$fromDate = $this->generateDateFromISO($fromParts);
			$toDate = $this->generateDateFromISO($toParts);
			if($fromDate)
			{
				$this->dateTo = $toDate;
			}
			if($toDate)
			{
				$this->dateFrom = $fromDate;
			}
		}
	}
	
	public function HasDuration()
	{
		return ($this->dateFrom + $this->dateTo > 0);
	}
	
	private function generateDateFromISO($dateParts)
	{
		print_debug( "trying to generate date out of $dateParts[1], $dateParts[2], $dateParts[0]");
		return mktime(12, 0, 0, $dateParts[1], $dateParts[2], $dateParts[0]);
	}
	
}