=== DeMomentSomTres MailChimp Immediate Send ===
Contributors: marcqueralt
Tags: mailchimp, email, newsletter, notification
Requires at least: 3.7
Tested up to: 3.7.1
Stable tag: head

== Description ==

The DeMomentSomTres Mailchimp Immediate Send plugin allows you to send an automatic message to all the subscribers of some list on content publication.

This plugin is not an alternative to Mandrill the MailChimp platform for transactional email.

= Features =

* Selection based on post type (post, page, custom post)
* Selection based on categories, post tags and any other taxonomy terms
* Template Support.

= History & Raison d’être =

While working for Consorci Administració Oberta de Catalunya we integrated Mailchimp and WordPress to perform RSS Campaigns.

Having them on operation the customer faced the need of sending immediate messages when a content was published on a specific category. However, Mailchimp RSS Campaings doesn't allow this because they are launched time based.

So we decide tu build this component that creates an adhoc campaign "regular campaing" every time a content in certain taxonomies is published.

== Installation ==

This portfolio plugin can be installed as any other WordPress plugin. 

= Requirements =

* This plugin uses the MailChimp api by Drew McLellan that requires Curl. Your must have it installed in your server. The plugin itself checks if the curl extension is installed.

== Frequently Asked Questions ==

= The post type I want to use does not appear on the admin page =

Check if this post type:

* has any taxonomy.
* the taxonomy has values set (with or without elements).

= Why pages are not shown in the admin page =

In default WordPress install classes won't be displayed because pages don't have any taxonomy.

= The content does not appear if I use a template =

This plugin uses the 'std_content00' section (mc:edit) in the MailChimp template.

= Where do I configure the mail contents? =

You can setup a template to make the mails look as you want.

Some of the parameters from the mail are taken from the MailChimp List defaults:

* Subject
* From name
* From email

= Where is my message stored in MailChimp? =

The message is stored as a campaign named as the list where it is sent with a YYYY/MM/DD HH:MM:SS suffix.

== Screenshots ==

TBD

== Changelog ==

= 1.0.3 =
* catalan translation

= 1.0.2 =
* remove internal git references

= 1.0.1 = 
* Template support error solved

= 1.0 =
* Initial version translation ready

= Next Steps =

* Translate it
* freely choose an edit area for every template