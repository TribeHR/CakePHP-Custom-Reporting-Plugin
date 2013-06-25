# Custom Reporting Plugin for CakePHP 2.x  
The report manager plugin can help users to create reports based on the application's models.

## Installation  
_Option 1: Manual_

1. Download this: http://github.com/TribeHR/CakePHP-Ad-Hoc-Reporting-Plugin/zipball/master
2. Unzip that download.
3. Copy the resulting folder to `app/Plugin`
4. Rename the folder you just copied to `AdHocReporting`

_Option 2: GIT Submodule_

In your app directory type:
```bash
git submodule add git://github.com/TribeHR/CakePHP-Ad-Hoc-Reporting-Plugin.git Plugin/AdHocReporting
git submodule init
git submodule update
```

_Option 3: GIT Clone_

In your plugin directory type
```bash
git clone git://github.com/TribeHR/CakePHP-Ad-Hoc-Reporting-Plugin.git AdHocReporting
```

## Setup

In `app/Config/bootstrap.php` add:
```php
CakePlugin::load('AdHocReporting', array('bootstrap' => true, 'routes' => true));
````

Setup the `AdHocReport` table by running the SQL found in  `Config/Schema/ad_hoc_reports.sql`

If you are using an ACL, add ACO entries for
- AdHocReporting/AdHocReports/index
- AdHocReporting/AdHocReports/add
- AdHocReporting/AdHocReports/delete
- AdHocReporting/AdHocReports/edit
- AdHocReporting/AdHocReports/wizard
- AdHocReporting/AdHocReports/view
- AdHocReporting/AdHocReports/copy

## Credits

This plugin is a fork of the plugin written by Luis Dias from March 11, 2013
http://github.com/luisdias/CakePHP-Report-Manager-Plugin

It was originally inspired by the Report Creator Component by Gene Kelly from Nov 9th 2006.  
http://bakery.cakephp.org/articles/Gkelly/2006/11/09/report-creator-component  

It also uses a Jquery plugin called SmartWizard by Tech Laboratory.  
http://techlaboratory.net/products.php?product=smartwizard  

