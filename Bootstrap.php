<?php

class Shopware_Plugins_Core_K10rStaging_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var array
     */
    protected $pluginInfo = [];

    /**
     * @var Enlight_Controller_Request_Request
     */
    protected $request;

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'install' => true,
            'enable'  => true,
            'update'  => true,
        ];
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'version'     => $this->getVersion(),
            'author'      => $this->getPluginInfo()['author'],
            'label'       => $this->getLabel(),
            'description' => str_replace('%label%', $this->getLabel(), file_get_contents(sprintf('%s/plugin.txt', __DIR__))),
            'copyright'   => $this->getPluginInfo()['copyright'],
            'support'     => $this->getPluginInfo()['support'],
            'link'        => $this->getPluginInfo()['link'],
        ];
    }

    /**
     * @return array
     */
    protected function getPluginInfo()
    {
        if ($this->pluginInfo === []) {
            $file = sprintf('%s/plugin.json', __DIR__);

            if (!file_exists($file) || !is_file($file)) {
                throw new \RuntimeException('The plugin has an invalid version file.');
            }

            $this->pluginInfo = json_decode(file_get_contents($file), true);
        }

        return $this->pluginInfo;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string)$this->getPluginInfo()['label']['de'];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->getPluginInfo()['currentVersion'];
    }

    /**
     * @return bool
     */
    public function install()
    {
        return $this->createEvents();
    }

    /**
     * @param string $oldVersion
     *
     * @return bool
     */
    public function update($oldVersion)
    {
        return $this->createEvents($oldVersion);
    }

    /**
     * @return array
     */
    public function enable()
    {
        return [
            'success' => parent::enable(),
            'invalidateCache' => [
                'config',
            ],
        ];
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    public function afterInit()
    {
        $this->get('Loader')->registerNamespace(
            'Shopware\Plugins\K10rStaging',
            $this->Path()
        );
    }

    /**
     * @param null|string $oldVersion
     *
     * @return bool
     */
    private function createEvents($oldVersion = null)
    {
        $versionClosures = [

            '0.0.1' => function (Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap) {
                $form = $bootstrap->Form();

                $form->addElement('text', 'mailtrap_username', [
                    'label'    => 'Mailtrap Benutzername',
                    'required' => true,
                    'scope'    => \Shopware\Models\Config\Element::SCOPE_SHOP,
                ]);

                $form->addElement('text', 'mailtrap_password', [
                    'label'    => 'Mailtrap Passwort',
                    'required' => true,
                    'scope'    => \Shopware\Models\Config\Element::SCOPE_SHOP,
                ]);

                $bootstrap->subscribeEvent(
                    'Enlight_Components_Mail_Send',
                    'sendMail'
                );

                return true;
            },

            '0.0.2' => function (Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap) {
                $bootstrap->subscribeEvent(
                    'Enlight_Controller_Front_DispatchLoopStartup',
                    'registerSubscribers'
                );
                $bootstrap->subscribeEvent(
                    'Shopware_Console_Add_Command',
                    'registerSubscribers'
                );

                return true;
            },

            '1.0.3' => function (Shopware_Plugins_Core_K10rStaging_Bootstrap $bootstrap) {
                $form = $bootstrap->Form();

                $form->addElement('boolean', 'show_notice', [
                    'label'    => 'Staging-Hinweis im Frontend anzeigen',
                    'value' => 1,
                    'scope'    => \Shopware\Models\Config\Element::SCOPE_LOCALE,
                ]);

                return true;
            },
        ];

        foreach ($versionClosures as $version => $versionClosure) {
            if ($oldVersion === null || (version_compare($oldVersion, $version, '<') && version_compare($version, $this->getVersion(), '<='))) {
                if (!$versionClosure($this)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sets the default mail-transport to mailtrap using Zend_Mail_Transport_Smtp
     */
    public function sendMail()
    {
        $options = [
            'host'     => 'smtp.mailtrap.io',
            'username' => $this->Config()->get('mailtrap_username'),
            'password' => $this->Config()->get('mailtrap_password'),
            'auth'     => 'login',
            'port'     => 465,
        ];

        $transport = \Enlight_Class::Instance('Zend_Mail_Transport_Smtp', [$options['host'], $options]);
        Enlight_Components_Mail::setDefaultTransport($transport);
    }

    /**
     * Add subscribers
     */
    public function registerSubscribers()
    {
        $subscribers = [
            new \Shopware\Plugins\K10rStaging\Subscribers\Frontend\All(
                $this,
                $this->get('template'),
                $this->get('snippets')
            ),
        ];

        foreach ($subscribers as $subscriber) {
            $this->Application()->Events()->addSubscriber($subscriber);
        }
    }
}
