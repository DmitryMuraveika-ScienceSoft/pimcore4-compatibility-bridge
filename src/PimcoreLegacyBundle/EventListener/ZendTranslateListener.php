<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Bundle\PimcoreBundle\EventListener;

use Pimcore\Tool;
use Pimcore\Translate;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Initializes Zend_Translate
 *
 * TODO this can be removed as soon as we use Symfony Translations
 */
class ZendTranslateListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || $this->initialized) {
            return;
        }

        $this->initTranslation($event->getRequest()->getLocale());
    }

    protected function initTranslation($locale)
    {
        try {
            $locale    = new \Zend_Locale($locale);
            $translate = new Translate\Website($locale);

            if (Tool::isValidLanguage($locale)) {
                $translate->setLocale($locale);
            } else {
                $this->logger->error('You want to use an invalid language which is not defined in the system settings: ' . $locale);
                // fall back to the first (default) language defined
                $languages = Tool::getValidLanguages();

                if ($languages[0]) {
                    $this->logger->error(sprintf(
                        'Using "%s" as a fallback, because the language "%s" is not defined in system settings',
                        $languages[0],
                        $locale
                    ));

                    $translate = new Translate\Website($languages[0]); // reinit with new locale
                    $translate->setLocale($languages[0]);
                } else {
                    throw new \Exception('You have not defined a language in the system settings (Website -> Frontend-Languages), please add at least one language.');
                }
            }

            // register the translator in \Zend_Registry with the key "\Zend_Translate" to use the translate helper for \Zend_View
            \Zend_Registry::set('Zend_Locale', $locale);
            \Zend_Registry::set('Zend_Translate', $translate);
        } catch (\Exception $e) {
            $this->logger->error('Initialization of Pimcore_Translate failed');
            $this->logger->error($e);
        }
    }
}
