<?php
/*********************************************************************
    dispatcher.php

    Dispatcher for staff applications

    Jens Eberle <support@osticket.com.de>
    Copyright (c)  2024 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

//TODO: disable direct access via the browser? i,e All request must have REFER?
if(!defined('INCLUDE_DIR'))	Http::response(500, 'Server configuration error');

require_once INCLUDE_DIR.'class.dispatcher.php';
$dispatcher = new Dispatcher();

$PI = Osticket::get_path_info();
if (strpos(strtolower($PI), '/admin/') !== false) {
    require('admin.inc.php');
    $PI = substr($PI, 6);
    Signal::send('apps.admin', $dispatcher);
}
else {
    Signal::send('apps.scp', $dispatcher);
}

$nav->setActiveTab('apps');

# Call the respective function
print $dispatcher->resolve($PI);
