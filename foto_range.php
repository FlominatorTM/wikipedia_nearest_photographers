
<?php
//finds next photographer in range
//underscore stuff is legacy, camel-cased is php 5

header('Content-Type: text/html; charset=utf-8'); 
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';

include("shared_inc/language.inc.php");
include("shared_inc/wiki_functions.inc.php");
include('next_inc/OfferingUser.php');	
include('next_inc/OfferPage.php');	
include('next_inc/OfferPageTemplate.php');	
include('next_inc/OfferPageList.php');	
include('next_inc/OfferPageTable.php');	
include('next_inc/OfferPageTableFromFile.php');	
include('next_inc/OfferPages.php');	
include('next_inc/GeoLocation.php');	


$inc_dir = "next_inc/lang";
//get the language file 
$user_lang = read_language();
get_language('en', $inc_dir); //not translated messages will be printed in English
get_language($user_lang, $inc_dir);

// $usr = new OfferingUser("Flominator");
// $usr->SetDateRangeISO("2013-12-20", "2014-04-01");

// die("done");
$is_debug = false;
if(isset($_REQUEST['debug']))
{
    $is_debug = ($_REQUEST['debug']=="on" || $_REQUEST['debug']=="true" );
}

$server = "$lang.$project.org";

$article_to = "";
//$article_to = "Hinterzarten";
if(isset($_REQUEST['article_to']))
{
    $article_to = $_REQUEST['article_to'];
}
if($article_to == "")
{
        echo '<h1>' . $messages['headline'] . '</h1>';
	echo '<form>';
	echo $messages['lang'] . ': <input name="lang" value="' . $lang .'"/> ' . $messages['lang_example'] .'<br>';
	echo $messages['project'] . ': <input name="project" value="' . $project .'"/>' . $messages['project_example'] .'<br>';
	echo $messages['article_to'] . ': <input name="article_to" value="' . $article_to .'"/>' . $messages['article_to_descr'] .'<br>';
	echo '<input type="submit" value="'. $messages['find_next'] .'"/>';
	echo '</form>';
}
else
{
    log_search();
    $footNote = "";
    $linkToArticleTo = "<a href=\"https://$server/wiki/".name_in_url($article_to)."\">$article_to</a>";
    echo '<h1>' . str_replace('_ARTICLE_TO_', $linkToArticleTo, $messages['distance_to']) .'</h1>';
    $locTo = new GeoLocation($article_to, $server);
    if($locTo->IsValid())
    {
	$allOfferPages = new OfferPages($server);
	
	foreach($allOfferPages->Items as $offerPage)
	{
	    echo "<h2><a href=\"$offerPage->EncodedOfferPage\">$offerPage->server</a></h2>";
	    if($offerPage->HasUsers())
	    {
		$offerPage->ListUsersToRequest($locTo);
	    }
	    else
	    {
		echo "no users found";
	    }
	}
	
	$homeOfferPage = $allOfferPages->Items[0];
	$linkOfferpage = "<a href=\"$homeOfferPage->EncodedOfferPage\">".urldecode($homeOfferPage->pageEncoded)."</a>"; 
	$footNote = str_replace('_OFFER_PAGE_', $linkOfferpage, $messages['you_on_list']);
    }
    else
    {
	echo str_replace('_LOCATION_', $linkToArticleTo, $messages['no_coordinates']);
    }

    echo "<br><br><a href=\"?lang=$lang&project=$project\">".$messages['new_request']."</a>";
    echo "<br><hr>$footNote";
}

    echo "<br><hr>";
    echo '<a href="' . $messages['manual_link'] . '">'. $messages['manual']  . '</a>';
    echo " - ";
    echo '<a href="' . $messages['issue_link'] . '">'. $messages['issues']  . '</a>';
    echo ' - by <a href="http://de.wikipedia.org/wiki/Benutzer:Flominator">Flominator</a>';

function print_debug($str)
{
    global $is_debug;
    if(isset($is_debug) && $is_debug)
    {
	echo $str."\n";
    }
}

function log_search ()
{
	global $article_to, $lang, $project, $server, $user_lang;
	$logfile = "log/nearest_".strftime("%Y-%m-%d").".csv";

	$header = "";
	if(!file_exists($logfile))
	{
		$header="Day;";
		$header.="Time;";
		$header.="IP (Client);";
		$header.="UI Language;";
		$header.="Article;";
		$header.="Language;";
		$header.="Project;";
		$header.="User-Agent;";
		$header.="\n";
	}
	
	if($file = fopen($logfile, "a"))
	{
		fputs($file, $header);
		fputs($file, strftime("%Y-%m-%d").";");
		fputs($file, strftime("%H:%M").";");
		fputs($file, "\"".$_SERVER['REMOTE_ADDR']."\";");
		fputs($file, $user_lang.";");
		fputs($file, "\"".$article_to."\";");
		fputs($file, $lang.";");
		fputs($file, $project.";");
		fputs($file, '"'.$_SERVER['HTTP_USER_AGENT'].'"'.";");
		fputs($file, "\n");
		fclose($file);
	}
	
}


?>