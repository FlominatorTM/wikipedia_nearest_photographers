<?php
//finds users whose coordinate articles are problematic
//underscore stuff is legacy, camel-cased is php 5
require_once("setup.php");

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
	if(!$usr->IsValid()  || $usr->location->onlyFallback)
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
		else if($usr->location->onlyFallback)
		{
		    echo $messages['problem_no_coordinates'].'<br>';
		    echo '<i>'.$messages['problem_fallback'].'</i>';
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