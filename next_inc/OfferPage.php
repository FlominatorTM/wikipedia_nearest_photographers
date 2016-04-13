<?php

abstract class OfferPage
{
	protected $userOffers;
	public $server;
	private $pageEncoded;
	private $EncodedOfferPage;
	public $revisionCurrent;
	public $OfferPageName;
	public $Link;

	public static $CACHE_DIR = 'cached';
	abstract function __construct($theServer, $OfferPageName,$MoreConfig) ;
	abstract function  GenerateUsers($page_src);
	function Init($theServer, $OfferPageName)
	{
	    global $is_debug;
	    //echo "page=".$page;
	    $this->server = $theServer;
	    $this->OfferPageName = $OfferPageName;
	    $this->pageEncoded = name_in_url($OfferPageName);
	    $this->EncodedOfferPage = "https://".$this->server."/wiki/".$this->pageEncoded;
	    $this->Link = '<a href="'.$this->EncodedOfferPage.'">'.urldecode($this->server).'</a>';

	    $cacheFile = self::$CACHE_DIR . '/' . $this->server . '.cache';
	    if(!file_exists(self::$CACHE_DIR))
	    {
		mkdir(self::$CACHE_DIR, "0777");
	    }

	    $request_url="https://".$this->server."/w/api.php?action=query&prop=revisions&titles=".name_in_url($this->OfferPageName)."&format=xml";
	    //echo "<br>reqiest". $request_url;
	    $xml = simplexml_load_file($request_url);
	    $this->revisionCurrent = $xml->query->pages->page->revisions->rev['revid'];
	   // echo "opm:" . $OfferPageName;

	    $useCache = $this->IsCachedVersionUpToDate();

	    if($is_debug)
	    {
		//print_debug("debugging ... skipping cache");
		//$useCache = false;
	    }
	    $cacheIsFine = false;
	    if($useCache)
	    {
		print_debug("cache will be used");
		if(file_exists($cacheFile) && $fCacheHandle = fopen($cacheFile, "r"))
		{	
		    // include_once("next_inc/GeoLocation.php");
		    // include_once("next_inc/OfferingUser.php");
		    print_debug("cache found and opened");
		    if($this->userOffers = unserialize(fread($fCacheHandle, filesize($cacheFile))))
		    {
			print_debug("cache read");
			fclose($fCacheHandle);

			foreach($this->userOffers as $cachedUser )
			{
			    print_debug("user $cachedUser->name is valid: ".$cachedUser->ToString());
			}
			$cacheIsFine = true;
		    }
		}			

	    }

	    if(!$cacheIsFine)
	    {
		$this->BuildNewCache($cacheFile);	
	    }
	    foreach($this->userOffers as $cachedUser )
	    {
		$cachedUser->LinkToUser($theServer, $this->Link);
	    }

	}

	protected function BuildNewCache($cacheFile)
	{
	    global $I18N;
	    print_debug("cache is not fine");

	    echo $I18N->msg( 'cache_outdated', array('variables' => array($this->Link))).'<br>'; 
		    
	    if(file_exists($cacheFile))
	    {
		print_debug("deleting cache");
		unlink($cacheFile);
	    }
	    $this->GenerateUsers($this->GetPageSource());
	    if(count($this->userOffers)==0)
	    {
		die("no user offers");
	    }
	    if($handleCacheFile = fopen($cacheFile, "w"))
	    {
		print_debug("attempting to write cache");
		
		$this->userOffers[1]->IsValid(); //dummy call to retrieve coordinates
		if(fputs($handleCacheFile, serialize($this->userOffers)))
		{
			print_debug("cache written");
			if(fclose($handleCacheFile))
			{
				$this->UpdateCachedRevision($this->revisionCurrent);
			}

		}
	    }
	    else
	    {
		print_debug("no cache written");
	    }
	}
	
	protected function GetPageSource()
	{
	    $page = "https://".$this->server."/w/index.php?action=raw&title=".$this->pageEncoded;
	    print_debug($page);

	    $page_src = file_get_contents($page);

	    if($page_src == "")
	    {
		return "problem retrieving $page";
	    }
	    print_debug("page_src=".$page_src);
	    print_debug("<hr><hr>");
	    return $page_src;
	}
	
	function SetConfigValue($configArray, $index, &$valueToBeSet, $isRequired)
	{
	    if(array_key_exists($index, $configArray))
	    {
		$valueToBeSet = $configArray [$index];
	    }
	    else
	    {
		if($isRequired)
		{
		    die("Required parameter " . $index . "not set");
		}
	    }
	}
	
	function UpdateCachedRevision($rev)
	{
	    print_debug("updating cache revision file with $rev");
	    $revisionFile = self::$CACHE_DIR. '/' . $this->server . '.rev';
	    if($revFileHandle = fopen($revisionFile, "w"))
	    {
		print_debug("file $revisionFile exists");
		if(fputs($revFileHandle, $rev))
		{
		    fclose($revFileHandle);
		    print_debug("file $revisionFile written");

		}
	    }
	}
	function IsCachedVersionUpToDate()
	{
		print_debug("checking for state of cache");
		$isCacheUpToDate = false;
		$revFileHandle;
		$revisionFile = self::$CACHE_DIR . '/' . $this->server . '.rev';
		if(file_exists($revisionFile))
		{
			print_debug("file $revisionFile exists");
			if($revFileHandle = fopen($revisionFile, "r+"))
			{	
				print_debug("file $revisionFile was opened");
				$revisionOld = fgets($revFileHandle, 20);
				print_debug("file $revisionFile was read: $revisionOld");
				fclose($revFileHandle);
				
				$isCacheUpToDate = $revisionOld >= $this->revisionCurrent;
				print_debug("old: $revisionOld  new:  $this->revisionCurrent");
			}
		}
		return $isCacheUpToDate ;
	}
	
	public function HasUsers()
	{
	    return $this->GetNumberOfUsers() > 0;
	}
	
	public function CalculateDistance($locTo)
	{
	    print_debug("locTo->ToString()=>" . $locTo->ToString());
	    foreach($this->userOffers as $usr)
	    {
		$usr->SetDistance($locTo);
	    }
	}
	
	public function GetNumberOfUsers()
	{
	    return count($this->userOffers);
	}
	
	public function GetUserAt($i)
	{
	    return $this->userOffers[$i];
	}

}