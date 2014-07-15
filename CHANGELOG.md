Pop PHP Framework Changelog
===========================

2.0.0a
------
As of July 14, 2014

* PHP 5.4+ Only
* Composer Support
* PSR-4
* New
    - Application (replaces Project)
    - Acl (separated from Auth)
    - Row Gateway and Table Gateway classes in the Db component
* Revised
    - Auth
        + Separated the Acl component and moved to its own folder
        + Added support for Http auth
        + Stripped out and simplified the auth functionality
    - Db
        + Removed the top-level Pop\Db\Db class in favor of direct access to the Db\Adapter classes
        + Removed Escaped adapter and support for non-prepared queries
        + Revised the join() method to be more clear in the Sql\Select class
        + Upgraded and Improved the Db\Sql component
        + Refactored the Record class to utilize the new Table Gateway and Row Gateway
    - Feed
        + Specific services and formats have been removed (Facebook, Twitter, etc.)
    - File
        + Completely stripped down to only the upload and checkDuplicate static methods
        + Trimmed Dir class, removed references to old File class
    - Filter
        + Removed unused functionality from Filter\String
    - Form
        + Refactored the Form component, including the organization of the Element classes
        + Added support for basic, most common HTML5 element types
    - Http
        + Curl moved under new Http\Client sub component
    - I18n
        + Added support for JSON language files
    - Pdf
        + Font component moved under Pdf
* Removed
    - Color
    - Compress
    - Curl (moved under the Http component)
    - Db\Adapter\Mysql (renamed old Mysqli adapter class to Mysql)
    - Filter\Search
    - Font (moved under the Pdf component)
    - Graph
    - Loader
    - Project
