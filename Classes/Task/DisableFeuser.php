<?php
namespace SourceLounge\DisableFeuser\Task;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Fluid\View\StandaloneView;

class DisableFeuser
{
    protected $disabledUser = array();

    public function run($time, $notificationEmail)
    {
        $returnValue = true;
        $timestamp = $this->convertToTimeStamp($time);
        // update alle frontend user
        // und einen lastlogin kleiner/gleich $timestamp haben
        // und lastlogin NICHT 0 ist -> die haben sich noch nicht eingeloggt
        $normalUser = ' donotdisable=0'
                    . '	AND lastLogin <=' . (int)$timestamp
                    . ' AND lastLogin!=0'
                    . BackendUtility::deleteClause('fe_users')
                    . BackendUtility::BEenableFields('fe_users');

        $this->disableUser($normalUser, $notificationEmail);

        // update alle user
        // und einen lastlogin GLEICH 0 haben -> die haben sich noch nicht eingeloggt
        // UND ein Erstellungsdatum kleiner/gleich $timestamp haben
        $userNeverLoggedIn = ' lastLogin = 0'
                            . ' AND donotdisable=0'
                            . ' AND crdate <=' . (int)$timestamp
                            . BackendUtility::deleteClause('fe_users')
                            . BackendUtility::BEenableFields('fe_users');

        $this->disableUser($userNeverLoggedIn, $notificationEmail);

        if (!empty($notificationEmail) && !empty($this->disabledUser)) {
            $returnValue = $this->sendEmail($notificationEmail);
        }
        return $returnValue;
    }

    public function convertToTimeStamp($time)
    {
        $dateTime = new \DateTime();
        return $dateTime->modify('-' . $time)->getTimeStamp();
    }

    public function disableUser($where, $notificationEmail)
    {
        if (!empty($notificationEmail)) {
            $rows = array();
            $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                'username,lastlogin',
                'fe_users',
                $where
            );
            $this->disabledUser = array_merge($this->disabledUser, $rows);
        }

        $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
            'fe_users',
            $where,
            array('disable' => '1')
        );
    }

    public function sendEmail($notificationEmail)
    {
        $success = false;
        if (!GeneralUtility::validEmail($notificationEmail)) {
            return $success;
        }

        $mailBody = $this->getMailBody();

        // Prepare mailer and send the mail
        try {
            $mailer = GeneralUtility::makeInstance(MailMessage::class);
            $mailer->setFrom($notificationEmail);
            $mailer->setSubject('SCHEDULER-Task DisableFeuser:' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
            $mailer->setBody($mailBody, 'text/html');
            $mailer->setTo($notificationEmail);
            $mailsSend = $mailer->send();
            $success = $mailsSend > 0;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $success;
    }

    public function getMailBody()
    {
        $extensionConfig = array();
        $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['disable_feuser']);

        if (empty($extensionConfig)) {
            $extensionConfig['templatePath'] = 'EXT:disable_feuser/Resources/Private/Templates/emailTemplate.html';
        }

        $templateFile = GeneralUtility::getFileAbsFileName($extensionConfig['templatePath']);
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($templateFile);


        $view->assignMultiple(array(
            'disabledUser' => $this->disabledUser,
        ));

        return $view->render();
    }
}
