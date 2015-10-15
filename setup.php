<?php
require_once __DIR__ . '/vendor/autoload.php';

//$I18N = new Intuition( 'next_photographer' );
//$I18N->registerDomain( 'next_photographer', __DIR__ . '/next_inc/lang/' );

forward_output_directly();
 
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


//$inc_dir = "next_inc/lang";
//get the language file 
$user_lang = read_language();
get_language('en', $inc_dir); //not translated messages will be printed in English
get_language($user_lang, $inc_dir);

// die("done");
$is_debug = false;
if(isset($_REQUEST['debug']))
{
    $is_debug = ($_REQUEST['debug']=="on" || $_REQUEST['debug']=="true" );
}

$server = "$lang.$project.org";


function forward_output_directly()
{
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
}
	

	
