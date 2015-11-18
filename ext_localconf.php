<?php
defined('TYPO3_MODE') or die();

/**
 * Registering class to scheduler
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][SourceLounge\DisableFeuser\Task\DisableFeuserTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => 'Disable Frontend user',
    'description' => 'Disable frontend user after inactive time',
    'additionalFields' => SourceLounge\DisableFeuser\Task\DisableFeuserAdditionalFields::class
);
