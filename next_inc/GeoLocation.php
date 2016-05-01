<?php

class GeoLocation
{
	public $lon = -1;
	public $lat = -1;
	public $iso = "";
	public $name = "";
	public $server;
	public $exists; //article exists
	public $hasCoordinates; //article has coordinates
	public $onlyFallback;
	public $wasChecked; //article was checked for coordinates
	static private  $allWithArticle = array();
	
	function __construct()
	{
		//doesn't do a thing, the static functions are used as "constructors"
	}
	public static function FromArticle($article, $server_in)
	{
		$article = str_replace('_', ' ', $article);
		$instance = new self();
		if(array_key_exists ($article, self::$allWithArticle) )
		{
			print_debug( "$article exists => returning it<br>");
			$locFound = self::$allWithArticle[$article];
			if($locFound->wasChecked && $locFound->hasCoordinates)
			{
				return $locFound;
			}
			else
			{
				$instance = $locFound;
			}
		}

		print_debug( "$article exists not => creating it<br>");
		
		$instance->server = $server_in;
		$instance->name = $article;
		self::$allWithArticle[$instance->name] = $instance;
		$instance->wasChecked = false;
		$instance->exists = false;
		print_debug( "Locations: " . count(self::$allWithArticle));
		return $instance;
		
	}	
	
	public static function Direct($lat, $lon, $name)
	{
		$instance = new self();
		$instance->lat = $lat;
		$instance->lon = $lon;
		$instance->name = $name;
		$instance->wasChecked = true;
		return $instance;
	}
	
	private function getCoordinates()
	{

		$this->tryGetCoordinates($this->server);

	}

	private function getAllCoordinatesXml(&$allFromHere)
	{
		if(count($allFromHere)==0) return false;
			
		$coordsPerApiCall = 45;
		$request_url="http://".$this->server."/w/api.php?action=query&prop=coordinates&colimit=500&format=xml&redirects&titles=";
		$i=0;

		//var_dump($allFromHere);
		while($i<$coordsPerApiCall&& count($allFromHere)>0)
		{
			$loc = array_shift($allFromHere);
			//var_dump($loc);
			if(!$loc->wasChecked)
			{
				$request_url.=$loc->name.'|';
				$loc->wasChecked = true;
				$i++;
			}
		}
		$request_url = substr($request_url, 0, strlen($request_url)-1); //remove trailing pipe
		print_debug("<br>".$request_url);
		set_time_limit(1200);
		$xml = simplexml_load_file($request_url);
		return $xml;
	}
	
	private function processCoordinates($xml)
	{
		if($xml)
		{					
			foreach ($xml->query->pages->page as $onePageNode)
			{
				print_debug( "<br>title". $onePageNode['title'] . "found");
				$location;
				$addionalRedirects = array();
				$found = false;
				if(array_key_exists ( "".$onePageNode['title'], self::$allWithArticle) )
				{
					$location = self::$allWithArticle[ "".$onePageNode['title']];
					$found = true;
				}
				//try redirects
				if(isset($xml->query->redirects))
				{
					//use array_shift in order to get rid of the redirect location 
					//if both are present

					foreach($xml->query->redirects->r as $oneRedirect)
					{

						print_debug( "<br>redir: ". $oneRedirect['to']);
						if("".$oneRedirect['to'] == "".$onePageNode['title'])
						{
							$found = true;
							print_debug( "<br>title". $onePageNode['title'] . "found as redirect");
							if(array_key_exists ( "".$onePageNode['title'], self::$allWithArticle) ) 
							{
								print_debug("is additional");
								$addionalRedirects[] = self::$allWithArticle[ "".$oneRedirect['from']];
							}
							else
							{
								print_debug("is the only one");
								$location = self::$allWithArticle[ "".$oneRedirect['from']];
							}
						}
					}
					if(!$found) 
					{
						print_debug( "<br>title". $onePageNode['title'] . "found not even as redirect");
						continue;
					}

				}
				
				print_debug ($onePageNode->coordinates->co['lon']."");
				$location->exists = true;
				
				if($onePageNode->coordinates->co['lon']!="")
				{
					print_debug( "has coordinates");
					$location->hasCoordinates = true;
					$location->lon = "".$onePageNode->coordinates->co['lon']; //without "" some XML object would be linked
					$location->lat = "".$onePageNode->coordinates->co['lat'];
				}
				else
				{
					print_debug( "has no coordinates");
					$location->hasCoordinates = false;
				}
				
				foreach($addionalRedirects as $addionalRedirect)
				{
					$addionalRedirect->hasCoordinates = $location->hasCoordinates;
					$addionalRedirect->lon = $location->lon;
					$addionalRedirect->lat = $location->lat;
					$addionalRedirect->exists = $location->exists;
					
				}
			}
		}
	}
	
	private function getUncheckedLocationsByServer($server)
	{
		$allRet = array();
		foreach(self::$allWithArticle as $oneOfAll)
		{
			if($oneOfAll->server == $server && !$oneOfAll->wasChecked)
			{
				$allRet[$oneOfAll->name] = $oneOfAll;
			}
		}
		return $allRet;
	}
	private function tryGetCoordinates($server)
	{
		$allFromHere = $this->getUncheckedLocationsByServer($server);
		
		while($xml = $this->getAllCoordinatesXml($allFromHere))
		{
			$this->processCoordinates($xml);
		}
	}
	
	function ToString()
	{
	    return "Location $this->name at $this->lat/$this->lon";
	}
	
	function IsValid()
	{
		print_debug("<br>asking if $this->name was checked");
		if(!$this->wasChecked)
		{
			print_debug(" and it wasn't");
			$this->getCoordinates($this->server);
		}
	    return $this->name != "" && $this->lat != -1 && $this->lon != -1;
	}
	
	function GetDistanceTo($locTo)
	{
		$this->IsValid();
	    return $this->calculateDistance($this->lat, $this->lon, $locTo->lat, $locTo->lon);
	}
	
		/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
	/*::                                                                         :*/
	/*::  This routine calculates the distance between two points (given the     :*/
	/*::  latitude/longitude of those points). It is being used to calculate     :*/
	/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
	/*::                     													 :*/
	/*::  Definitions:                                                           :*/
	/*::    South latitudes are negative, east longitudes are positive           :*/
	/*::                                                                         :*/
	/*::  Passed to function:                                                    :*/
	/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
	/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
	/*::    unit = the unit you desire for results                               :*/
	/*::           where: 'M' is statute miles                                   :*/
	/*::                  'K' is kilometers (default)                            :*/
	/*::                  'N' is nautical miles                                  :*/
	/*::  Worldwide cities and other features databases with latitude longitude  :*/
	/*::  are available at http://www.geodatasource.com                          :*/
	/*::                                                                         :*/
	/*::  For enquiries, please contact sales@geodatasource.com                  :*/
	/*::                                                                         :*/
	/*::  Official Web site: http://www.geodatasource.com                        :*/
	/*::                                                                         :*/
	/*::         GeoDataSource.com (C) All Rights Reserved 2013		   		     :*/
	/*::                                                                         :*/
	/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
	private function calculateDistanceCommercial($lat1, $lon1, $lat2, $lon2, $unit="K") {

	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "K") {
		return ($miles * 1.609344);
	  } else if ($unit == "N") {
		  return ($miles * 0.8684);
		} else {
			return $miles;
		  }
	}
	
	private function calculateDistance($startLat, $startLon, $endLat, $endLon)
	{
	  print_debug( "CALC: " .$this->name . " calculateDistance($startLat, $startLon, $endLat, $endLon) " );
		//http://www.kurztutorial.info/php5/spezial/geokoordinaten/geokoordinaten.php
		print_debug("calculateDistance($startLat, $startLon, $endLat, $endLon)");
		$dist = 0.0;
		$x1 =  doubleval($startLon);
		$x2 = doubleval($endLon);
		$y1 = doubleval($startLat);
		$y2 = doubleval($endLat);
		// e = ARCCOS[ SIN(Breite1)*SIN(Breite2) + COS(Breite1)*COS(Breite2)*COS(Länge2-Länge1) ]
		
		print_debug("dist = acos(sin($x1=deg2rad($x1))*sin($x2=deg2rad($x2))+cos($x1)*cos($x2)*cos(deg2rad($y2) - deg2rad($y1)))*(6378.137);  ");
		$dist = acos(sin($x1=deg2rad($x1))*sin($x2=deg2rad($x2))+cos($x1)*cos($x2)*cos(deg2rad($y2) - deg2rad($y1)))*(6378.137);  
		print_debug("dist: $dist");
		return $dist;
	}
	
}