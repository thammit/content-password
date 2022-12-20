<?php
namespace Qbus\ContentPassword\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\DataProcessing\FlexFormProcessor;
use B13\Container\DataProcessing\ContainerProcessor;


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
    public function mainAction(bool $notResponsible = false): ResponseInterface
    {
        $flexFormData = $this->getFlexFormData();
        $until = (int)($flexFormData['protection_until'] ?? 0);
        $flexFormPassword = $flexFormData['password'] ?? '';

        if ($flexFormPassword === '') {
            return $this->renderContainerContent();
        }

        if ($until) {
            $timeout = $until - time();
            if ($timeout <= 0) {
                return $this->renderContainerContent();
            }
            $this->setCacheMaxExpiry($timeout);
        }

        $cObj = $this->configurationManager->getContentObject();
        $this->view->assign('contentObject', $cObj->data);
        $this->view->assign('flexFormData', $flexFormData);

        $requestParameters = $this->request->getAttribute('extbase');
        if ($notResponsible && $requestParameters->getOriginalRequest() !== null) {
            // extbase f:form.*** viewhelpers will treat a "original request" as a validation error,
            // while it is just a "we are not responsible" forward in this case.
            \Closure::bind(function() use ($requestParameters) {
                $requestParameters->originalRequest = null;
                $requestParameters->originalRequestMappingResults = null;
            }, null, get_class($requestParameters))();
        }

        return $this->htmlResponse();
    }

    public function unlockAction(string $password = '', int $unlockid = 0): ResponseInterface
    {
        $cObj = $this->configurationManager->getContentObject();

        if ($unlockid !== $cObj->data['uid']) {
            // render main, another content_password element one the same page was triggered
            return (new ForwardResponse('main'))->withArguments(['notResponsible' => true]);
        }

        $flexFormData = $this->getFlexFormData();
        $flexFormPassword = $flexFormData['password'] ?? '';

        $hash = password_hash($flexFormPassword, defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT);
        if (!password_verify($password, $hash)) {
            $message = LocalizationUtility::translate('password_incorrect', 'content_password');
            $this->addFlashMessage($message, '', AbstractMessage::ERROR, false);
            return new ForwardResponse('main');
        }

        return $this->renderContainerContent();
    }

    protected function renderContainerContent(): ResponseInterface
    {
        $data = $this->executeContainerDataProcessor();
        $children = $data['children_200'] ?? [];
        return $this->htmlResponse(implode('', array_column($children, 'renderedContent')));
    }


    /* FIXME: This function is a HACK
     *
     * we modify the TSFE cache_timeout value as soon as we are rendered, but the cache_timeout may have been requested earlier.
     * (see typo3/sysext/frontend/Classes/ContentObject/Menu/AbstractMenuContentObject.php)
     */
    protected function setCacheMaxExpiry($timeout)
    {
        $tsfe = $this->getTSFE();

        $current_page_timeout = (int)$tsfe->page['cache_timeout'];
        if ($current_page_timeout > $timeout || $current_page_timeout == 0) {
            $tsfe->page['cache_timeout'] = $timeout;
        }

        /** @var $runtimeCache \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend */
        $runtimeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('runtime');
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

    protected function getFlexFormData(): array
    {
        return $this->executeFlexFormProcessor()['flexFormData'] ?? [];
    }

    protected function executeContainerDataProcessor()
    {
        return $this->executeProcessor(ContainerProcessor::class);
    }

    protected function executeFlexFormProcessor(): array
    {
        return $this->executeProcessor(FlexFormProcessor::class);
    }

    protected function executeProcessor(string $processor, array $processorConfiguration = []): array
    {
        $cObj = $this->configurationManager->getContentObject();
        $variables = [
            'data' => $cObj->data,
            'current' => $cObj->data[$cObj->currentValKey ?? null] ?? null,
        ];

         return GeneralUtility::makeInstance($processor)->process(
            $this->configurationManager->getContentObject(),
            [],
            $processorConfiguration,
            $variables
        );
    }

    protected function getTSFE(): TypoScriptFrontendController
    {
        return $this->request->getAttribute('frontend.controller');
    }
}
