<?php

/*
 * this page is shown, if staff members click on Application in the Staff / Admin navigation
 */

require('staff.inc.php');
require_once INCLUDE_DIR.'class.plugin.php';

$nav->setActiveTab('apps', -1);
    $applications = new Application();
    $staffapps = $applications->getStaffApps();
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.'apps.inc.php');
include(STAFFINC_DIR.'footer.inc.php');
