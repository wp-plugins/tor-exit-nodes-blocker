=== Tor Blocker ===
Contributors: hqpeak
Donate link: http://hqpeak.com/
Tags: Tor, spam, security
Requires at least: 3.8.1
Tested up to: 4.2.3
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Tor Blocker stands for limiting actions to the users that came from Tor nodes. 
 
== Description ==

Most of the time Tor exit nodes and another .onion web proxies are used to enumerate vulnerabilities of our online product, to perform attack or to be used as a spam source.
This plugin allow us to limit the actions that coud be performed by the users that are coming from a Tor nodes using http://pike.hqpeak.com free service.
Could be upgraded to premium (from September) or could be set up any url to service that will give you response in the described json format. 
Premium list is updated on every 5 minutes, free on 5 h and has its own caching mechanism so isn't affect the speed of the WP instance.  

With this plugin you can apply following constraints to the Tor visitors:

- Visits   (Tor users can read only public content on the site)
- Comments   (Tor users can post comments)
- Registration   (Tor users can register for the site)
- Subscription   (Tor users can subscribe)
- Administration   (Tor users can access administration panel)
- Request   (Tor users can send POST requests)

Or to ban any action by its name / key e.g. not allow accessing resources defined by some GET or POST key.
Un checking all of the boxes will block all of the requests to your wordpress. 

Update: Now you can show user friendly message to the Tor visitor and/or you can
log all of their actions.

== Installation ==

1. Extract `TorBlocker`  archive to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the administration area Dashboard -> Settings -> Tor Blocker


== Frequently Asked Questions ==

= What if I have problems activating the plugin? =
For any problem you face with the plugin activation, please visit support forums or contact us at contact@hqpeak.com.

= Does this plugin work with newest version of WordPress and also with older versions? =
Yes, this plugin works really fine with WordPress 3.8.1!
It should also work with earlier versions, but the testing was done at the latest stable version and that is 3.8.1,
so you always should run the latest WordPress version to escape possible problems.

= What if a user using tor is not in the list and happens to come to the site? =
No worries. The tor exit list is updated frequently each 5 minutes or 5 hours, depending on which version is used,
so users using tor that are not on the list will be updated after this amount of time and thus will be denied by the plugin.

= Do I have to set up the settings every time I activate the plugin? =
Yes. Every time the plugin is activated its options are set to default values, so it means you have to set them up again.

= How many request parameters can I put in the textarea to limit the user by request? =
No limit at all. You can put as many parameters in the textarea as you want. The plugin will recognize any request parameter in the URL
and stop the user immediately.

= What is allowed to the tor users by default? =
By default, tor users are allowed just to visit the site and read its public content. As you might guess, you can deny this too,
so the tor user is stopped before reaching your site. 


== Screenshots ==

1. TorBlocker Settings panel at its default state (running with WordPress 3.8.1 here)
2. TorBlocker Settings panel with some options changed (running with WordPress 3.8.1 here)
3. TorBlocker Settings panel with some other options changed (running with WordPress 3.8.1 here)
4. TorBlocker plugin in action - comments denied (running with WordPress 3.8.1 here)
5. TorBlocker plugin in action - Admin panel access denied (running with WordPress 3.8.1 here)
6. TorBlocker plugin in action - custom GET request denied (running with WordPress 3.8.1 here)


== Changelog ==

= 1.0 =
This is the initial released version.
= 1.1 =
Free service improved to cover all of the known active Tor nodes seen on the network 5 hours ago. Premnium service delivers realtime results. 

== Upgrade Notice ==

= 1.0 =
Just released in public.
= 1.1 =
Improved service, logging features, widget, user friendly custom message

