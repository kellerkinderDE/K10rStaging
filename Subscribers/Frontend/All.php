<?php

namespace Shopware\Plugins\K10rStaging\Subscribers\Frontend;

use Enlight\Event\SubscriberInterface;

class All implements SubscriberInterface
{
    /** @var \Shopware_Plugins_Core_K10rStaging_Bootstrap */
    protected $bootstrap;

    /** @var \Enlight_Template_Manager */
    protected $templateManager;

    /** @var \Shopware_Components_Snippet_Manager */
    protected $snippetManager;

    /**
     * All constructor.
     *
     * @param \Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap
     */
    public function __construct(
        \Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap,
        \Enlight_Template_Manager $templateManager,
        \Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->bootstrap       = $bootstrap;
        $this->templateManager = $templateManager;
        $this->snippetManager  = $snippetManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Plugins_ViewRenderer_FilterRender' => 'addStagingBadge',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addStagingBadge(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Plugins_ViewRenderer_Bootstrap $viewRendererBootstrap */
        $viewRendererBootstrap = $args->get('subject');
        $module                = strtolower($viewRendererBootstrap->Front()->Request()->getModuleName());

        if ($module === 'widget') {
            return;
        }

        $message = $this->snippetManager->getNamespace('frontend/plugins/k10r_staging/notice')->get(
            'K10rStagingNotice',
            'Sie befinden sich aktuell im STAGING-System.',
            true
        );

        if ($module === 'backend') {
            $stagingBadge = sprintf("<div style='position: absolute; top: 15px; right: 15px; background: red; padding: 15px; color: #fff;'>%s</div>", $message);
        } else {
            $this->templateManager->assign('K10rStagingNotice', $message);
            $stagingBadge = $this->templateManager->fetch(
                'string:' . file_get_contents(__DIR__ . '/../../Views/frontend/plugins/k10r_staging/notice.tpl')
            );
        }

        $args->setReturn(
            preg_replace('/^<body([^>]*)>/im', '<body$1>' . $stagingBadge, $args->getReturn())
        );
    }
}
