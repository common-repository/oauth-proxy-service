=== Infusionsoft Proxy Service ===
Contributors: trainingbusinesspros
Donate link: https//formlift.net/donate
Tags: infusionsoft, proxy, oauth
Requires at least: 4.0
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is for Infusionsoft Wordpress plugin developers only. Setup your WordPress as a super fast Proxy service for your wordpress based Infusionsoft plugins.

== Description ==

Install this plugin to turn your WP installation into a proxy service to connect your WordPress plugins to Infusionsoft using their Oauth API.
This plugin was created to create an Oauth connect for FormLift and is now being released for global use for other developers.

This plugin is only for developers who plan on distributing their own plugins among the WordPress community with the need of connecting those plugins via Oauth to Infusionsoft.

Because of the limitations of Oauth, a proxy server is needed to keep your Infusionsoft Developer App's client_id and client_secret protected as you do not want to place those in your
plugins files as that could create a massive breach of security, and Infusionsoft might revoke your developer priviledges.

So, follow the setup procedure on [This Page](https://oauth.formlift.net) to setup your plugin to use this plugin on your proxy site to connect to Infusiosnoft.

To see how to integrate this with your WordPress plugins, visit the plugin homepage at [oauth.formlift.net](https://oauth.formlift.net).

== Disclaimers ==

By using this plugin in conjuction with your own, you are required to tell your users the following in your reamdme.txt for compliance reasons in all parts of the world:

By using the Ouathentication method for [Your Plugin Here], you consent to anonymous API usage statatics being collected by infusionsoft for the following reasons.
* To provide API usage information to help us create a better plugin.
* To engage API throttling in the event too many API requests are made within a short period of time.

By using [Your Plugin Here] you also consent to the use of [Your Proxy Site] as a connection service between [Your Plugin Here] and Infusionsoft. We reserve the right
to refuse any API authentication requests made and collect anonymous usage statistics.

However, any API requests made by [Your Plugin Here] will communicate directly with Infusionsoft and forgo [Your Proxy Site]. [Your Proxy Site] only acts as a communication
service between [Your Plugin Here] and Infusionsoft during the initial Authentication procedure and subsequent refreshing of autentication tokens. No client information of any kind, such as names, email addresses, or phone numbers
is ever sent through [Your Proxy Site].

== Installation ==

1. Download.
2. Install the regular way.
3. Activate.
4. Enter your Infusionsoft Developer keys into the settings area.
5. Create an integration in your plugin.

Then you are good to go! To get help with this process, the FormLift team may be able to assist, make a support request or visit [oauth.formlift.net](https://oauth.formlift.net).

== Frequently Asked Questions ==

= I'm not a developer, do I need this? =
No.

= As a developer, why do I need this? =
Infusionsoft is sunsetting their API key method, so you will eventually need Oauth to use the API.

= Does this support any SDKS? =
You may have to custom make your own SDK depending on your needs, but their is a planned boilerplate for the i2SDK created by David Bullock.

== Changelog ==
= 1.0.1=
*
= 1.0 =
* Initial Release.
