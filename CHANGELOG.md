Pop PHP Framework Changelog
===========================

2.0.0a
------
As of May 17, 2014

* PHP 5.4+ Only
* Composer Support
* PSR-4
* New
     - Application (replaced Project)
     - Acl (separated from Auth)
* Revised
     - Auth
         + Separated the Acl component and moved to its own folder
         + Added
     - File
         + Completely stripped down to only the upload and checkDuplicate static methods
         + Trimmed Dir class, removed references to old File class
* Removed
    - Compress
    - Db\Adapter\Mysql
    - Loader
    - Project
