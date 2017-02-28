<?php

namespace Shopware\Plugins\K10rStaging\Subscribers\Frontend;

use Enlight\Event\SubscriberInterface;

class All implements SubscriberInterface
{
    /** @var \Shopware_Plugins_Core_K10rStaging_Bootstrap */
    protected $bootstrap;

    /**
     * All constructor.
     * @param \Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap
     */
    public function __construct(\Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onFrontendPostDispatch',
        ];
    }

    /**
     * @param \Enlight_Controller_EventArgs|\Enlight_Event_EventArgs|\Enlight_Controller_ActionEventArgs $args
     */
    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        $controller->View()->addTemplateDir($this->bootstrap->Path() . '/Views');
    }
}
