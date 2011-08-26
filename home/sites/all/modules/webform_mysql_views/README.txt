/* $Id: README.txt,v 1.1.2.2 2011/01/14 19:37:45 usonian Exp $ */

-- SUMMARY --

The Webform MySQL Views builds flattened, read-only MySQL views of webform
submission data. These views may be useful when you need to access this data
from an external application in an automated fashion without exporting,
importing, or the use of a web-based API.

-- INTEGRATION WITH DATA MODULE & VIEWS MODULE

Johan Falk (Itangalo) had the idea to use the Data module to expose the MySQLw
views created by this module to the Drupal Views module, and Frega provided a
patch that forces the Data and Views modules to recognize the `sid` field of the
MySQL views as the primary key (See http://drupal.org/node/889306).

If you have the Data and Schema modules installed on your site, your Webform
MySQL views should automatically become available for use in the Views module.

-- REQUIREMENTS --

* Your Drupal database must be using the MySQL backend.

* Your MySQL server must be version 5.0 or later

* The MySQL user specified in your Drupal settings.php file must have permission
  to create views.

* Webform Module

* Elements Module

-- OPTIONAL --

* Data Module (Makes it possible to use the MySQL views created by Webform
  MySQL Views with the Drupal Views module.)


-- INSTALLATION --

* Install as usual, see http://drupal.org/node/70151 for further information.


-- CONFIGURATION --

* To manage MySQL views for your webforms, log in as an administrator and go to
  the Administer > Content Management > Web Forms page and click on the MySQL
  Views tab.


-- KNOWN ISSUES --

Data/Views module integration does not work if your Drupal database is
configured to use a table prefix. (See http://drupal.org/node/894074).

-- CONTACT --

Current maintainers:
* Andy Chase (usonian) - http://drupal.org/user/164378


This project has been sponsored by:
* The Proof Group LLC
  Visit http://proofgroup.com for more information.
