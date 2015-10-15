<?php

require_once("setup.php");
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
	$allOfferPages->MergeOffers($locTo);
	$allOfferPages->ListUsersToRequest();
	$homePage = $allOfferPages->HomeOfferPage;
	$footNote = str_replace('_OFFER_PAGE_', $homePage->Link, $messages['you_on_list']);
	$footNote = str_replace($homePage->server.'<', $homePage->OfferPageName.'<', $footNote);
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
    echo " - ";
    echo '<a href="foto_check.php">'. $messages['check_users']  . '</a>';
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