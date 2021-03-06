<?php

require_once("setup.php");

$article_to  = "";
if(isset($_REQUEST['article_to']))
{
	$article_to = $_REQUEST['article_to'];
}

$radius = 0;
if(isset($_REQUEST['radius']))
{
	$radius = $_REQUEST['radius']+0;
}

if(!isset($_REQUEST['go']))
{
	echo '<h1>' . $I18N->msg( 'headline' ) . '</h1>';
	echo '<form>';
	echo $I18N->msg( 'lang') . ': <input name="lang" value="' . $lang .'"/> ' . $I18N->msg( 'lang_example') .'<br>';
	echo $I18N->msg( 'project') . ': <input name="project" value="' . $project .'"/>' . $I18N->msg( 'project_example') .'<br>';
	echo $I18N->msg( 'article_to') . ': <input name="article_to" value="' . $article_to .'"/>' . $I18N->msg( 'article_to_descr') .'<br>';
	
	echo $I18N->msg( 'radius') . ': <input name="radius" value="' . $radius .'"/>' . $I18N->msg( 'radius_descr') .'<br>';
	echo '<input type="submit" name="go" value="'. $I18N->msg( 'find_next') .'"/>';
	echo '</form>';
}
else
{
	log_search();
	$locTo;
	$headLine;

	if($article_to != "")
	{
		$linkToArticleTo = "<a href=\"https://$server/wiki/".name_in_url($article_to)."\">$article_to</a>";
		$locTo = GeoLocation::FromArticle($article_to, $server);
		$headLine = $I18N->msg( 'distance_to', array('variables' =>array($linkToArticleTo)));
	}
	else if($_REQUEST['lat'] && $_REQUEST['lon'])
	{
		$lat = $_REQUEST['lat']+0;
		$lon = $_REQUEST['lon']+0;
		$name = $lat.'/'.$lon;
		$locTo = GeoLocation::Direct($lat, $lon, $name);
		$headLine = $I18N->msg( 'distance_to_coord', array('variables' =>array($name)));
	}
	else
	{
		die ("error: missing parameters");
	}

	echo '<h1>' . $headLine .'</h1>';	
    
    $footNote = "";
    if($locTo->IsValid())
    {
		$allOfferPages = new OfferPages($server);
		$allOfferPages->MergeOffers($locTo);
		$allOfferPages->ListUsersToRequest($radius);
		$homePage = $allOfferPages->HomeOfferPage;
		$footNote = str_replace('_OFFER_PAGE_', $homePage->Link, $I18N->msg( 'you_on_list'));
		$footNote = str_replace($homePage->server.'<', $homePage->OfferPageName.'<', $footNote);
    }
    else
    {
	echo $I18N->msg( 'no_coordinates', array('variables' => array($linkToArticleTo)) );
    }

    echo "<br><br><a href=\"?lang=$lang&project=$project&radius=$radius\">".$I18N->msg( 'new_request')."</a>";
    echo "<br><hr>$footNote";
}

    echo "<br><hr>";
    echo '<a href="' . $I18N->msg( 'manual_link') . '">'. $I18N->msg( 'manual')  . '</a>';
    echo " - ";
    echo '<a href="' . $I18N->msg( 'issue_link') . '">'. $I18N->msg( 'issues')  . '</a>';
    echo " - ";
    echo '<a href="foto_check.php">'. $I18N->msg( 'check_users')  . '</a>';
    echo ' - by <a href="http://de.wikipedia.org/wiki/Benutzer:Flominator">Flominator</a>';
	//echo $I18N->getFooterLine( TSINT_HELP_ALL ); 

function log_search ()
{
	global $article_to, $lang, $project, $server, $user_lang;
	if(!is_dir("log")) mkdir ("log");
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