<?php
/*********************************************************************
    dispatcher.php

    Dispatcher for client applications

    Jens Eberle <support@osticket.com.de>
    Copyright (c)  2024 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');

//TODO: disable direct access via the browser? i,e All request must have REFER?
if(!defined('INCLUDE_DIR'))	Http::response(500, 'Server configuration error');

require_once INCLUDE_DIR.'class.dispatcher.php';
$dispatcher = new Dispatcher();

$PI = Osticket::get_path_info();
Signal::send('apps.client', $dispatcher);

# Call the respective function
print $dispatcher->resolve($PI);
