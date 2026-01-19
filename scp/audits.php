<?php
/*********************************************************************
    audits.php

    Audit Logs

    Adriane Alexander
    Copyright (c)  2006-2019 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');

/* Anpassung Anfang: support audit plugin as phar and folder
if (PluginManager::auditPlugin())
*/
if (($plgId=PluginManager::auditPlugin()) && ($plg=PluginManager::lookup($plgId)))
    if(!$plg->isPhar())
        require_once(INCLUDE_DIR . '/plugins/audit/class.audit.php');
    else
// Anpassung Ende: support audit plugin as phar and folder
    require_once('phar://' . INCLUDE_DIR . '/plugins/audit.phar/class.audit.php');

$page = 'phar://' . INCLUDE_DIR . '/plugins/audit.phar/templates/auditlogs.tmpl.php';
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.audit_logs" />',
    "$('#content').data('tipNamespace', 'dashboard.audit_logs');");
require(STAFFINC_DIR.'header.inc.php');
require($page);
include(STAFFINC_DIR.'footer.inc.php');
?>
