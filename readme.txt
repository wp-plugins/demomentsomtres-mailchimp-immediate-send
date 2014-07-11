=== MailChimp Immediate Send ===
Contributors: marcqueralt
Tags: mailchimp, email, newsletter, notification
Donate link: http://DeMomentSomTres.com
Requires at least: 3.7
Tested up to: 3.9.1
Stable tag: head

== Description ==

The DeMomentSomTres Mailchimp Immediate Send plugin allows you to send an automatic message to all the subscribers of some list on content publication.

This plugin is **not** an alternative to Mandrill the MailChimp platform for transactional email.

You can get more information at [DeMomentSomTres Digital Marketing Agency](http://demomentsomtres.com/english/wordpress-plugins/mailchimp-immediate-send/).

= Features =

* Selection based on post type (post, page, custom post)
* Selection based on categories, post tags and any other taxonomy terms
* Selection based on multiple taxonomy terms.
* Template Support.
* Edit area configuration.

= History & Raison d’être =

While working for Consorci Administració Oberta de Catalunya we integrated Mailchimp and WordPress to perform RSS Campaigns.

Having them on operation the customer faced the need of sending immediate messages when a content was published on a specific category. However, Mailchimp RSS Campaings doesn't allow this because they are launched time based.

So we decide tu build this component that creates an adhoc campaign "regular campaing" every time a content in certain taxonomies is published.

== Installation ==

This portfolio plugin can be installed as any other WordPress plugin. 

= Requirements =

* Uses [DeMomentSomTresTools Plugin](http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-tools/).

== Frequently Asked Questions ==

= I've updated a content and I want to send it again =

You have to check the Force Resend in the top right area called 'Send'.

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

= 2.0 =
* DeMomentSomTres Tools compatibility
* Administration optimization and redesign
* Groups of interest management
* Multiple terms (and) query to activate
* Prevent sending when quick edit is used

= 1.2.5 =
* compatibility upgrade admin helper library

= 1.2 =
* Post title as subject of the campaign
* Post title included in post as h1

= 1.1 = 
* Metabox added to force resend after publishing.
* Bug Fix: some posttypes sharing taxonomies where not shown.

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