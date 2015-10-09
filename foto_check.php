<?php
//finds next photographer in range
//underscore stuff is legacy, camel-cased is php 5

//copied from http://www.andrew-kirkpatrick.com/2013/08/output-csv-straight-to-browser-using-php/
//prevent apache from buffering it for deflate/gzip
header('Content-Type: text/html; charset=utf-8'); 
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

// Turn off output buffering
ini_set('output_buffering', 'off');
// Turn off PHP output compression
ini_set('zlib.output_compression', false);
         
//Flush (send) the output buffer and turn off output buffering
//ob_end_flush();
while (@ob_end_flush());
         
// Implicitly flush the buffer(s)
ini_set('implicit_flush', true);
ob_implicit_flush(true);
 
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

$is_debug = false;
if(isset($_REQUEST['debug']))
{
    $is_debug = ($_REQUEST['debug']=="on" || $_REQUEST['debug']=="true" );
}



if(isset($_REQUEST['purge']))
{
   $whatToPurge = trim($_REQUEST['purge']);
   if($whatToPurge[0]!='.')
   {
       $cacheFile = './cached/' . $whatToPurge . '.rev';
       if(file_exists($cacheFile))
       {
	   unlink($cacheFile);
       }
   }  
}

$server = "$lang.$project.org";
$allOfferPages = new OfferPages($server);

echo '<h1>'. $messages['foto_check'].'</h1>';
echo '<a href="foto_range.php">'. $messages['back_to_range'] .'</a>';

foreach($allOfferPages->Items as $oneOfferPage)
{
    echo "<h2> $oneOfferPage->Link </h2>"; 
    echo '<table border="1">';
    echo '  <colgroup>';
    echo '<col width="100">';
    echo '<col width="100">';
    echo '<col width="100">';
    echo '</colgroup>';
    echo '<tr>';
    echo '<th>' .$messages['column_user'].'</th>';
    echo '<th>' .$messages['column_location'].'</th>';
    echo '<th>' .$messages['column_problem'].'</th>';
    echo '</tr>';
    
    $count= 0;
    for($i=0;$i<$oneOfferPage->GetNumberOfUsers();$i++)
    {
	
	$usr = $oneOfferPage->GetUserAt($i);
	if(!$usr->IsValid())
	{
	    $count++;
	    echo '<tr>';
	    echo '<td>'.  $usr->Link . '</td>';
	    echo '<td>'.  '<a href="https://' . $oneOfferPage->server . '/wiki/' . $usr->location->name . '">' . $usr->location->name . '</a>' . '</td>';
	    echo '<td>';
	    if(!$usr->location->exists)
	    {
		echo $messages['problem_no_article'];
	    }
	    else
	    {
		if(!$usr->location->hasCoordinates)
		{
		    echo $messages['problem_no_coordinates'];
		}
	    }
	    echo '</td>';
	    echo '</tr>';
	}
	
    }
    echo '</table>';
    echo str_replace('__NUMBER_OF_ITEMS__', $count, $messages['items_found']);
    echo ' - <a href="?purge=' . $oneOfferPage->server . '">' .$messages['purge_cache'] . '</a>';    
}

function print_debug($str)
{
    global $is_debug;
    if(isset($is_debug) && $is_debug)
    {
	echo $str."\n";
    }
}


?>