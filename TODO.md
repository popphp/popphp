COMPONENTS
----------
 - Archive
 - Auth
 - Cache [DONE]
 - Code
 - Color [DONE]
 - Crypt [DONE]
 - Curl [DONE]
 - Data
 - Db
 - Dom
 - Event [DONE]
 - Feed
 - File [DONE]
 - Filter
 - Font
 - Form
 - Ftp [DONE]
 - Geo [DONE]
 - Graph
 - Http [DONE]
 - I18n
 - Image
 - Log
 - Mail
 - Mvc
 - Nav
 - Paginator
 - Payment [DONE -> w/ deps: Curl]
 - Pdf
 - Project
 - Service [DONE]
 - Shipping [DONE -> w/ deps: Dom, Curl]
 - Validator [DONE]
 - Web [DONE]
 - Config [DONE]
 - Version [DONE]


REVISIONS
---------
 - PHP 5.4+ Only


REMOVED
-------
 - Compress
 - Loader
 - File
     + Completely stripped down to only the upload and checkDuplicate static methods
     + Trimmed Dir class, removed references to old File class