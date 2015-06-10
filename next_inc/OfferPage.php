<?php

abstract class OfferPage
{
	private $userOffers;
	public $server;
	public $pageEncoded;
	public $EncodedOfferPage;
	public $revisionCurrent;
        
	public static $CACHE_DIR = 'next_inc/cached';
	abstract function __construct($theServer, $OfferPageName) ;
	abstract function InitSpecificVariables();
	abstract function  GenerateUsers($page_src);
	function Init($theServer, $OfferPageName)
	{
	    global $messages, $is_debug;

	    //echo "page=".$page;
	    $this->server = $theServer;
	    $this->pageEncoded = name_in_url($OfferPageName);
	    $this->EncodedOfferPage = "https://$this->server/wiki/".$this->pageEncoded;
	    $this->InitSpecificVariables();
	    
	    $cacheFile = self::$CACHE_DIR . '/' . $this->server . '.cache';
	    if(!file_exists(self::$CACHE_DIR))
	    {
		mkdir(self::$CACHE_DIR, "0777");
	    }

	    $request_url="http://".$this->server."/w/api.php?action=query&prop=revisions&titles=".name_in_url($OfferPageName)."&format=xml";
    //		echo "<br>reqiest". $request_url;
	    @$xml = simplexml_load_file($request_url);
	    $this->revisionCurrent = $xml->query->pages->page->revisions->rev['revid'];
	   // echo "opm:" . $OfferPageName;

	    $useCache = $this->IsCachedVersionUpToDate();

	    if($is_debug)
	    {
		$useCache = false;
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
		print_debug("cache is not fine");
		if(file_exists($cacheFile))
		{
			print_debug("deleting cache");
			unlink($cacheFile);
		}
		$page = "http://".$this->server."/w/index.php?action=raw&title=".$this->pageEncoded;

		$page_src = removeheaders(get_request($this->server, $page, true ));

		print_debug("page_src=".$page_src);
		print_debug("<hr><hr>");
		
		$this->GenerateUsers($page_src);
		
		    if($this->server == "pl.wikipedia.org")
		    {
			    $this->GenerateUsersUsingList($page_src);
		    }
		    else
		    {
			    $this->GenerateUsersUsingTemplate($page_src);
		    }

		    if($handleCacheFile = fopen($cacheFile, "w"))
		    {
			print_debug("attempting to write cache");
			if(fputs($handleCacheFile, serialize($this->userOffers)))
			{
				print_debug("cache written");
				if(fclose($handleCacheFile))
				{
					$this->UpdateCachedRevision($this->revisionCurrent);
				}

			}
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
	
	function ListUsersToRequest($locTo)
	{
		global $messages;
		print_debug("locTo->ToString()=>" . $locTo->ToString());
		foreach($this->userOffers as $usr)
		{
			$usr->SetDistance($locTo);
		}
		
		usort($this->userOffers , array("OfferingUser", "CompareDistance"));
		
		foreach($this->userOffers as $usr)
		{
			$resLine = $usr->LinkToUser($this->server) . "  (" . sprintf("%01.1f",$usr->distance)  . " km)";
			if($usr->IsInRange())
			{
				echo "<b>$resLine</b>";
			}
			else
			{
				echo "$resLine";
			}
			
			if($usr->HasDuration())
			{
				echo " ";
				$now = time();

				if($usr->dateFrom < $now)
				{
					if($usr->dateTo < $now)
					{
						echo str_replace('_DATE_', strftime("%x", $usr->dateTo), $messages['until_date_over']);
					}
					else
					{
						echo str_replace('_DATE_', strftime("%x", $usr->dateTo), $messages['until_date']);
					}
				}
				else
				{
					$out = str_replace('_FIRST_DATE_', strftime("%x", $usr->dateFrom), $messages['between_dates']);
					echo str_replace('_SECOND_DATE_', strftime("%x", $usr->dateTo), $out);
				}
			}
			echo "<br>";
		}
	}
	
	

}