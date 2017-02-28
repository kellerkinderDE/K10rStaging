# K10rStaging

__This plugin is only designed for use in staging/pre-live environments!__

K10rStaging makes you aware, that you are currently working in a staging environment by showing a message on every page.
It also prevents shopware from sending mails to customers, by using [MailTrap.io](https://mailtrap.io/) to catch all outgoing e-mails.

## Installation and usage
* Download the ZIP or clone this repository into your `engine/Shopware/Plugins/Local/Core/` folder.
* Activate the plugin via PluginManager
* Create a new [MailTrap.io](https://mailtrap.io/) inbox
* Set the [MailTrap.io](https://mailtrap.io/) username and password for your inbox in the plugin settings (you can find these in Mailtrap under `SMTP Settings`)

## License
MIT licensed, see `LICENSE.md`
