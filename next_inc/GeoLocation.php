<?php

class GeoLocation
{
	public $lon = -1;
	public $lat = -1;
	public $iso = "";
	public $name = "";
	public $server;
	public $exists;
	public $hasCoordinates;
	public $onlyFallback;
	public $wasChecked; //for coordinates
	static private  $allWithArticle = array();
	
	function __construct()
	{
		//doesn't do a thing, the static functions are used as "constructors"
	}
	public static function FromArticle($article, $server_in)
	{
		if(array_key_exists ($article, self::$allWithArticle) )
		{
			echo "$article exists => returning it<br>";
			$locFound = self::$allWithArticle[$article];
			if($locFound->server == $server_in)
			{
				return $locFound;
			}
		}

		echo "$article exists not => creating it<br>";
		$instance = new self();
		$instance->server = $server_in;
		$instance->name = $article;
		self::$allWithArticle[$instance->name] = $instance;		
		$instance->wasChecked = false;
		$instance->exists = false;
		echo "Locations: " . count(self::$allWithArticle);
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

	private function getAllCoordinatesXml($server, $offset)
	{
		//50 als const
		echo "getAllCoordinates";
		$request_url="http://".$server."/w/api.php?action=query&prop=coordinates&colimit=500&format=xml&redirects&titles=";
		$end = min(count(self::$allWithArticle), $offset+50);
		echo "end:" . $end;
		$i = $offset;
		foreach(self::$allWithArticle as $loc)
		{
			if($i == $end) break;
			if(!$loc->wasChecked && $loc->server == $server)
			{
				$request_url.=$loc->name.'|';
				// $request_url.=name_in_url($loc->name).'|';
				$i++;
			}
		}

		$request_url = substr($request_url, 0, strlen($request_url)-1); //remove trailing pipe
		echo "<br>".$request_url;
		set_time_limit(1200);
		$xml = simplexml_load_file($request_url);
		return $xml;
	}
	
	private function processCoordinates($xml)
	{
		if($xml)
		{
			echo "<b>has xml with len" .strlen($xml)." </b>";
			echo '<pre>'.htmlspecialchars("".$xml).'</pre>';
			
			foreach ($xml->query->pages->page as $onePageNode)
			{
				echo "<br>title". $onePageNode['title'] . "found";
				$location;
				if(array_key_exists ( "".$onePageNode['title'], self::$allWithArticle) )
				{
					$location = self::$allWithArticle[ "".$onePageNode['title']];
				}
				else //try redirects
				{
					$found = false;
					foreach($xml->query->redirects->r as $oneRedirect)
					{

						echo "<br>redir: ". $oneRedirect['to'];
						if("".$oneRedirect['to'] == "".$onePageNode['title'])
						{
							echo "<br>title". $onePageNode['title'] . "found as redirect";
							$location = self::$allWithArticle[ "".$oneRedirect['from']];
							$found = true;
						}
					}
					if(!$found) 
					{
						echo "<br>title". $onePageNode['title'] . "found not even as redirect";
						continue;
					}

				}
				
				echo ($onePageNode->coordinates->co['lon'].""); //print_debug
				$location->exists = true;
				
				if($onePageNode->coordinates->co['lon']!="")
				{
					echo "has coordinates";
					$location->hasCoordinates = true;
					$location->lon = "".$onePageNode->coordinates->co['lon']; //without "" some XML object would be linked
					$location->lat = "".$onePageNode->coordinates->co['lat'];
				}
				else
				{
					echo "has no coordinates";
					$location->hasCoordinates = false;
					// if($server!='de.wikipedia.org')
					// {
						// $location->tryGetCoordinates('de.wikipedia.org');
						// if($location->hasCoordinates)
						// {
							// $location->onlyFallback = true;
						// }
						
						// if(!$location->exists)
						// {
							// $location->exists = true;
						// }
					// }
				}

				//put remaining locations to was checked true
			}
		}
	}
	private function tryGetCoordinates($server)
	{
	    echo "tryGetCoordinates";
		if($this->name != "")
		{	
			$coordsPerApi = 50;
			$apiRounds = ceil(count(self::$allWithArticle)/$coordsPerApi ); 
			for($i=0;$i<$apiRounds;$i++)
			{
				echo "<br>processing batch from " . $i*$coordsPerApi;
				$xml = $this->getAllCoordinatesXml($server, $i*$coordsPerApi);
				$this->processCoordinates($xml);
			}
			
			echo "setting rest as checked";
			foreach(self::$allWithArticle as $oneLoc)
			{
				// var_dump($oneLoc);
				if($oneLoc->server == $server)
				{
					echo "loc $oneLoc->name was marked checked";
					$oneLoc->wasChecked = true;
				}
				// var_dump(self::$allWithArticle[$oneLoc->name]);
			}
			
		}
	}
	
	function ToString()
	{
	    return "Location $this->name at $this->lat/$this->lon";
	}
	
	function IsValid()
	{
		echo "<br>asking if $this->name was checked";
		if(!$this->wasChecked)
		{
			echo " and it wasn't";
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
	  echo "CALC: " .$this->name . " calculateDistance($startLat, $startLon, $endLat, $endLon) " ;
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