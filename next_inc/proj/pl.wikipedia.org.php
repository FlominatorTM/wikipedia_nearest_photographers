<?php

$config['UserPrefixMale'] = 'wikipedysta';
$config['UserPrefixFemale'] = 'wikipedystka';
$config['IndexUserColumn'] = 1; //zero indexed
$config['IndexLocationColumn'] = 2; //zero indexed

$createdPage = new OfferPageTable("pl.wikipedia.org", 'Wikiprojekt:Fotografia/Uczestnicy', $config);
