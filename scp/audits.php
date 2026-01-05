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

// Anpassung Anfang: Pruefen ob das Plugin eine Phar datei oder ein Ordner ist
// Pfade definieren
$plugin_path = INCLUDE_DIR . 'plugins/audit';
$phar_path = INCLUDE_DIR . 'plugins/audit.phar';

// Pruefen, ob der entpackte Ordner oder die Phar-Datei existiert
if (is_dir($plugin_path)) {
    $base_path = $plugin_path . '/';
} else {
    $base_path = 'phar://' . $phar_path . '/';
}
if (PluginManager::auditPlugin()) {
    require_once($base_path . 'class.audit.php');
}

$page = $base_path . 'templates/auditlogs.tmpl.php';

// Anpassung Ende: Pruefen ob das Plugin eine Phar datei oder ein Ordner ist

$page = 'phar://' . INCLUDE_DIR . '/plugins/audit.phar/templates/auditlogs.tmpl.php';
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.audit_logs" />',
    "$('#content').data('tipNamespace', 'dashboard.audit_logs');");
require(STAFFINC_DIR.'header.inc.php');
require($page);
include(STAFFINC_DIR.'footer.inc.php');
?>
