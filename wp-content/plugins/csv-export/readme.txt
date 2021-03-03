=== Tickera - CSV Export Add-on ===
Contributors: tickera, freemius
Requires at least: 4.1
Tested up to: 5.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CSV Data Export add-on is a user-friendly exporter which will export Attendee List with a minimum of hassle.

== Description ==

CSV Data Export add-on is a user-friendly exporter which will export Attendee List with a minimum of hassle.

It allows you to use the list for external accounting system or other third party systems. The add-on will simplified the data transfer to other systems by providing a data exporter that outputs CSV formatted files.
csv-option-screen


== Changelog ==

= 1.2.5.6 XX/XX/XXXX =
- Additional order status (Refunded) in filter
- Bug Fixed: Unable to export csv file for some sites
- Bug fixed: Missing options in Ticket Type Filter
- Better license update / license check handling

= 1.2.5.5 - 07/04/2020 =
- Added api key in csv export feature
- Added ticket type in csv export feature
- Added csv export from-to date 
- Added keep selection of the exporting fields in csv export feature
- Added woocommerce's discount code export with bridge for woocommerce
- Move csv export into main menu
- Added export price in csv export feature
- Remember export fields in csv export addon

= 1.2.5.4 =
- Fixed issue with order date format and timezones

= 1.2.5.3 =
- Small performance improvements

= 1.2.5.2 =
- Fixed issue with Multisite site wide activation
- Added improved plugin update option
- Added compatibility with Tickera 3.4

= 1.2.5.1 - 03/12/2018 =
- Fixed order total (shown once) for WooCommerce
- Added hooks for developers

= 1.2.5.0 - 15/10/2018 =
- Added hooks for developers
- Added better debug mode (when TC_DEBUG is on)

= 1.2.4.9 - 12/09/2018 =
- Added new admin styling to support latest Custom Forms add-on changes
- Added event date indication in the select box

= 1.2.4.8 - 29/AVG/2018 =
- Added more hooks for developers
- Add-on DOM update

= 1.2.4.7 - 18/JULY/2018 =
- Updated language file

= 1.2.4.6 - 05/JULY/2018 =
- Fixed issue with date time zones
- Added select all option
- Added more parameters for ticket type

= 1.2.4.5 - 21/NOV/2017 =
- WordPress 4.9 Compatibility

= 1.2.4.4 - 02/SEP/2017 =
- Added Order Total that is shown only once in the column
- Additional argument added to the query

= 1.2.4.3 - 06/JUN/2017 =
- Added translation files

= 1.2.4.2 - 27/APR/2017 =
- Added API Key name information for checkins column

= 1.2.4.1 - 13/MAR/2017 =
- Fixed issue with "checked-in" column

= 1.2.4 - 19/JAN/2017 =
- Performance improvements

= 1.2.3.9 - 29/NOV/2016 =
- Added Ticket ID

= 1.2.3.8 - 21/APR/2016 =
- Added list of check-ins to the CSV Export

= 1.2.3.7 - 04/APR/2016 =
- Added additional columns (attendee first name, attendee last name, buyer first name and buyer last name)
- Added plugin updater support for new licensing server

= 1.2.3.6 - 16/MAR/2016 =
- Fixed issue with WooCommerce variations when Bridge for WooCommerce add-on is active
- Added support for PHP 7

= 1.2.3.5 - 23/FEB/2016 =
- Fixed possible issues with plugins and/or themes which modify admin_url via filters

= 1.2.3.4 - 06/JAN/2016 =
- Added owner email column

= 1.2.3.3 - 23/NOV/2015 =
- Added automatic updater

= 1.2.3.2 - 06/NOV/2015 =
- Added order total column

= 1.2.3.1 - 05/NOV/2015 =
- Added additional hooks for developers
- Fixes issues with export when notice in the WP occurs

= 1.2.3 - 17/SEP/2015 =
- Added additional columns for the export (Order Number, Payment Gateway, Discount Code, Checked-in)

= 1.2.2 - 09/SEP/2015 =
- Added ajax export functionality to avoid various server issues, memory leaks and limited execution time on some servers
