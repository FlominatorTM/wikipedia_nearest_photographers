<?php

setlocale(LC_TIME, 'en_US');

//form
$messages['ui_lang'] = 'Display language';
$messages['headline'] = 'Nearest photographers';
$messages['lang'] = 'Language';
$messages['lang_example'] = 'en, commons, …';
$messages['project'] = 'Project';
$messages['project_example'] = 'wikipedia, wikisource, wikimedia, …';
$messages['article_to'] = 'Article';
$messages['article_to_descr'] = 'Wikipedia article with coordinates where picture should be taken at (e.g. some city, sight, place etc.)';
$messages['find_next'] = 'Find closest photographers';
$messages['manual'] = 'Manual';
$messages['issues'] = 'issues + feedback ';
$messages['manual_link'] = 'https://en.wikipedia.org/wiki/User:Flominator/Find_Nearest_Photographer';
$messages['issue_link'] = 'https://github.com/FlominatorTM/wikipedia_nearest_photographers/issues';


//template
$messages['template_offer'] = 'Bilderangebot';
$messages['template_user'] = 'Benutzer';
$messages['template_location'] = 'Standort';
$messages['template_range'] = 'Aktionsradius';

//result
$messages['cache_outdated'] = "Pulling a fresh version of __PAGE__ and its entries, because cached version is outdated. This might take some minutes. Please be patient.";
$messages['user_retrieval'] = "__USER__ is being retrieved";
$messages['distance_to'] = 'Photographers distance to coordinates from [[_ARTICLE_TO_]] ';
$messages['no_coordinates'] = 'Location _LOCATION_ is invalid/does not have coordinates.';
$messages['proj_not_supported'] = 'project _PROJECT_ is currently not supported, please contact :de:user:Flominator for details';
$messages['new_request'] = 'Perform new search';
$messages['column_user'] = 'User';
$messages['column_distance'] = 'Distance';
$messages['column_wiki'] = 'Wiki';
$messages['between_dates'] = 'only between _FIRST_DATE_ and _SECOND_DATE_';
$messages['until_date']= 'only until _DATE_';
$messages['until_date_over']= 'was there until _DATE_ :(';
$messages['you_on_list']= 'You want to be on this list? Please your entry to _OFFER_PAGE_. Is your project missing? File an issue with a link to the photographers list to be included here.';

$messages['column_location'] = 'Location detected';
$messages['column_problem'] = 'Problem found';
$messages['problem_no_article'] = 'no article';
$messages['problem_no_coordinates'] = 'coordinates missing';	