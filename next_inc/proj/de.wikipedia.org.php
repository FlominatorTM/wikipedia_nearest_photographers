<?php

$config['TemplateUser'] = 'Benutzer';
$config['TemplateName'] = 'Bilderangebot';
$config['TemplateLocation'] = 'Standort';
$config['TemplateRange'] = 'Aktionsradius';
$config['TemplateDateFrom'] = 'von';
$config['TemplateDateUntil'] = 'bis';

$createdPage = new OfferPageTemplate("de.wikipedia.org", 'Wikipedia:Bilderangebote', $config);
