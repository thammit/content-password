<?php
namespace Qbus\ContentPassword\Controller;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Benjamin Franzke <bfr@qbus.de>, Qbus GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class ContentPasswordController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action main
     *
     * @return void
     */
    public function mainAction()
    {
        $cObj = $this->configurationManager->getContentObject();
        $until = (int) $cObj->data['flexform_protection_until'];

        if ($cObj->data['flexform_password'] === '') {
            return $cObj->data['tx_gridelements_view_column_0'];
        }

        if ($until) {
            $timeout = $until - time();
            if ($timeout <= 0) {
                return $cObj->data['tx_gridelements_view_column_0'];
            }
            $this->setCacheMaxExpiry($timeout);
        }

        if ($cObj->data['CType'] !== 'gridelements_pi1' ||
            $cObj->data['tx_gridelements_backend_layout'] !== 'content_password') {
            // throw exception?
            // this controller should only be used inside the gridelement "content_password"
        }

        $this->view->assign('contentObject', $cObj->data);
    }

    /**
     * action unlock
     *
     * @param  string $password
     * @param  int    $unlockid
     * @return void
     */
    public function unlockAction($password = '', $unlockid = 0)
    {
        $cObj = $this->configurationManager->getContentObject();
        if ($cObj->data['CType'] !== 'gridelements_pi1' ||
            $cObj->data['tx_gridelements_backend_layout'] !== 'content_password') {
            // throw exception?
            // this controller should only be used inside the gridelement "content_password"
        }

        if ($unlockid != $cObj->data['uid']) {
            // render main, another content_password element one the same page was triggered
            $this->forward('main');
        }

        $desired_password = $cObj->data['flexform_password'];

        $hash = password_hash($desired_password, defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT);
        if (!password_verify($password, $hash)) {
            $message = LocalizationUtility::translate('password_incorrect', 'content_password');
            $this->addFlashMessage($message, '', AbstractMessage::ERROR, false);
            $this->forward('main');
        }

        return $cObj->data['tx_gridelements_view_column_0'];
    }

    /* FIXME: This function is a HACK
     *
     * we modify the TSFE cache_timeout value as soon as we are rendered, but the cache_timeout may have been requested earlier.
     * (see typo3/sysext/frontend/Classes/ContentObject/Menu/AbstractMenuContentObject.php)
     */
    protected function setCacheMaxExpiry($timeout)
    {
        $current_page_timeout = (int)$GLOBALS['TSFE']->page['cache_timeout'];
        if ($current_page_timeout > $timeout || $current_page_timeout == 0) {
            $GLOBALS['TSFE']->page['cache_timeout'] = $timeout;
        }

        /** @var $runtimeCache \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend */
        $runtimeCache = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('cache_runtime');
        $cachedCacheLifetimeIdentifier = 'core-tslib_fe-get_cache_timeout';
        $cachedCacheLifetime = $runtimeCache->get($cachedCacheLifetimeIdentifier);

        /* if the page timeout was cached already, overwrite the cached value as well,
         * see: typo3/sysext/frontend/Classes/Controller/TypoScriptFrontendController.php->get_cache_timeout */
        if ($cachedCacheLifetime !== false) {
            if ($cachedCacheLifetime > $timeout) {
                $runtimeCache->set($cachedCacheLifetimeIdentifier, $timeout);
            }
        }
    }
}
