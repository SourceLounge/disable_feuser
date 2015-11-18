<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "disable_feuser".
 *
 * Auto generated 18-11-2015 10:00
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Disable FeUser Task',
	'description' => 'Scheduler task to disable inactive frontend users',
	'category' => 'be',
	'version' => '1.0.0',
	'state' => 'beta',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Simon Diercks',
	'author_email' => 'diercks@source-lounge.de',
	'author_company' => 'Source Lounge',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-7.6.99',
			'php' => '5.5.0-5.999.999',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

