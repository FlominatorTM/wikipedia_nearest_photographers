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


$allOfferPages = new OfferPages($server);

echo '<h1>'.  $I18N->msg( 'foto_check').'</h1>';
echo '<a href="foto_range.php">'. $I18N->msg( 'back_to_range') .'</a>';

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
    echo '<th>' .$I18N->msg( 'column_user').'</th>';
    echo '<th>' .$I18N->msg( 'column_location').'</th>';
    echo '<th>' .$I18N->msg( 'column_problem').'</th>';
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
		echo $I18N->msg( 'problem_no_article');
	    }
	    else
	    {
		if(!$usr->location->hasCoordinates)
		{
		    echo $I18N->msg( 'problem_no_coordinates');
		}
		else if($usr->location->onlyFallback)
		{
		    echo $I18N->msg( 'problem_no_coordinates').'<br>';
		    echo '<i>'.$I18N->msg( 'problem_fallback').'</i>';
		}
	    }
	    echo '</td>';
	    echo '</tr>';
	}
	
    }
    echo '</table>';
    echo $I18N->msg( 'items_found', array('variables'=> array( $count) , 'parsemag' => true ));
    echo ' - <a href="?purge=' . $oneOfferPage->server . '">' .$I18N->msg( 'purge_cache') . '</a>';    
}

?>