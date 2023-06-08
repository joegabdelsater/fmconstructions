<?php

/**
* CMSGEN Version variabesl
* Name of the CMSGEN , displayed in the admin area header 
* 
* Revision 368
* added table relationship class that has a Containable behavior
* Revision 307
* - table name hashing added
* - dynamic salt
* - sitemap bug fixes
* - delete thumbnail added
* - view on website is off by default
* 
* Revision 349
* Added ajax foreign key search which speeds up pageload daramatically 
* 
* Revision 299
* Security fixes
* Listing all entries edit fixed(checkboxes were resetting)
* CSRF protection added
* 
* Revision 284
* Fixes multi foreign key bulk edit
* 
* Revision 258
* Added license details to noficiation file & installation
* 
* Revision 254, Version 3.4.1
* Caching using APC introduced
* Content history now shows who edited each entry
*/
      defined('CMSGEN_REVISION')           ? null : define('CMSGEN_REVISION', '400');
      defined('CMSGEN_VERSION')           ? null : define('CMSGEN_VERSION', '4.0');
      defined('CMSGEN_TITLE')           ? null : define('CMSGEN_TITLE', 'XTND CMS');
?>