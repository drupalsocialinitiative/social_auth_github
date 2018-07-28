CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * How it works
 * Support requests
 * Maintainers


INTRODUCTION
------------

Social Auth GitHub a GitHub Authentication integration for Drupal. It is based
on the Social Auth and Social API projects

It adds to the site:
 * A new url: /user/login/github.
 * A settings form on /admin/config/social-api/social-auth/github.
 * A GitHub logo in the Social Auth Login block.


REQUIREMENTS
------------

This module requires the following modules:

 * Social Auth (https://drupal.org/project/social_auth)
 * Social API (https://drupal.org/project/social_api)


INSTALLATION
------------

 * Run composer to install the dependencies.
   composer require "drupal/social_auth_github:^2.0"

 * Install the dependencies: Social API and Social Auth.

 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-8
   for further information.


CONFIGURATION
-------------

 * Add your GitHub project OAuth information in
   Configuration » User Authentication » GitHub.

 * Place a Social Auth Login block in Structure » Block Layout.

 * If you already have a Social Auth Login block in the site, rebuild the cache.


HOW IT WORKS
------------

User can click on the GitHub logo on the Social Auth Login block
You can also add a button or link anywhere on the site that points
to /user/login/github, so theming and customizing the button or link
is very flexible.

When the user opens the /user/login/github link, it automatically takes the user
to GitHub for authentication. After GitHub has returned the user to your site,
the module compares the user id or email address provided by GitHub. If the user
has previously registered using GitHub or your site already has an account with
the same email address, the user is logged in. If not, a new user account is
created. Also, a GitHub account can be associated to an authenticated user.


SUPPORT REQUESTS
----------------

Before posting a support request, carefully read the installation
instructions provided in module documentation page.

Before posting a support request, check Recent log entries at
admin/reports/dblog

Once you have done this, you can post a support request at module issue queue:
https://www.drupal.org/social_auth_github/issues

When posting a support request, please inform what does the status report say
at admin/reports/dblog and if you were able to see any errors in
Recent log entries.


MAINTAINERS
-----------

Current maintainers:
 * Getulio Sánchez (gvso) - https://www.drupal.org/u/gvso
 * Himanshu Dixit (himanshu-dixit) - https://www.drupal.org/u/himanshu-dixit
