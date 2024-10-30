=== Minerva Wordpress ===
Contributors: mike_sapiens
Donate link:
Tags: social network, distributed network, descentralized network, widget, admin, social
Requires at least: 2.5
Tested up to: 2.5.1
Stable tag: 0.7.1

Minerva was designed with a simple goal in mind: to let anyone be a part of a global ad-hoc social network. 

== Description ==

Minerva was designed with a simple goal in mind: to let anyone be a part of a global ad-hoc social network. 
Basically once your site has Minerva capabilities you are in a social network. Your own social network! 
Your site becomes your account and your're free to do whatever you wish. With a single click!
The Minerva Wordpress plugin adds Minerva capabilities to your blog.

For more information visit http://minerva.sapiensworks.com

== Installation ==
1. You need Php 5.2.x with php-curl and php-xml extensions enabled.
2. Upload the plugin (the 'Minerva' folder) to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. From Minerva Admin list and join the network of your choice. You can create and manage your own networks too.
5. To display the Minerva Panel (that enables others to add you as friend or to join your networks) in your sidebar, just add the Minerva widget to it.
6. If you use a theme that doesn't support widgets just use the following code `<?php if (class_exists('MinervaTemplate')) {  MinervaTemplate::Panel();} ?>`


== Frequently Asked Questions ==

= What does Minerva Wordpress plugin do for me?  =

Minerva is a system that makes easy for you to be in a ad-hoc social network with anyone who has a site with Minerva capabilites.
So basically this plugin enables you to be in a descentralized social network, where your blog is your social page/profile.

= So, it connects all the wordpres blogs that use this plugin? =

Yes, but Minerva functionality isn't restricted just to Wordpress blogs. You can connect to any site that has Minerva capabilities (right now just Wordpress blogs are supported but other platforms will be supported soon). 

= Can I connect to my favourite social network <insert name here>? =

Short answer: No.

Long answer: A classic social network is centralized, meaning that there is a central authority (site) that manages the network. That's why you have to create an account to be a part of a social network.
The account on one social network isn't compatible with other social network, that's why you have to create accounts on every social network you want to join.
Communicating with such "closed" social networks is possible if the social network has API that enables this thing. But you'd still be needing to have an account with each one.
Minerva was designed to be an "open" social networking system so that you need only one account (your blog, you don't have to register to a 3rd party) that will be used to identify you in every Minerva network.

= Why should I use Minerva instead of a "classic" social network? =

1. You only need one acount, that is your blog. 
2. The profile being your own blog it means that you have **complete control** over it
3. You don't have to give private information to anyone (every social network **requires** you to tell them your full name, your birthday, where you live etc). 
With Minerva **you** decide what is required.
4. Minerva is Free and Open Source.

= Minerva Wordpress is the same thing as Minerva? =

Not quite. Minerva is the system, implemented as Minerva Core. Minerva Wordpress integrates Minerva Core into a Wordpress instalation.

= Can I use Minerva Wordpress if my blog isn't self hosted? =

Technically you can, but it depends on if your hoster allows you to install plugins.

= Does Minerva have a blog? =

Yes, you can find it here: http://minerva.sapiensworks.com/blog/ .

= Can I contribute to Minerva (Core or Wordpress plugin)? =

Of course, that's why is Open Source. Just visit http://minerva.sapiensworks.com and drop me a line.

== Change log ==

version 0.7.1
 - solved get my networks bug in template file

== Copyright ==

Minerva Wordpress is released under LGPL (see license.txt).
Minerva Wordpress uses Minerva Core that is released under GPL v3.

Copyright (c) 2008 Mike T. http://minerva.sapiensworks.com