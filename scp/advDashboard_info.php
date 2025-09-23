<?php
require('staff.inc.php');
$nav->setTabActive('dashboard');
include(STAFFINC_DIR.'header.inc.php');
?>
<section class="private_dashboard">
    <!-- headline -->
    <h2>Advanced Dashboard Info</h2>

    <div class="widget_container">
        <h1 style="text-align: center; font-size: 3rem;">Ab sofort verfügbar</h1>
        <div style="text-align: center; font-size: 1.5rem;">
            Legen Sie verschiedene Ticketlisten als gespeicherte Suche an und lassen Sie sich diese hier in Widgets anzeigen<br>
            - als Tabelle, Balkendiagramm oder beides -
            <br><br>
            Erstellen Sie globale Widgets, die für alle Agenten auswählbar sind, und vom Agenten durch eigene Widgets ergänzt werden können.
            <br><br>
            Möchten Sie ein öffentliches Dashboard anlegen? - Auch das ist möglich....
            <br><br>
            Diese Funktion ist nun als kostenpflichtige Ergänzung hier verfügbar.
            <br><br>
            Eine ausführliche Anleitung zum Advanced Dashboard finden Sie hier:<br>
            <a href="https://osticket.com.de/support/pages/advanced-dashboard-fur-osticket" target="_blank">
                Advanced Dashboard für osTicket - Anleitung
            </a>
            <br><br>
            Bestellen können Sie das Advanced Dashboard hier:<br>
            <a href="https://osticket.com.de/products/extensions/" target="_blank">
                kostenpflichtige Erweiterungen für osTicket
            </a>
            <br><br>
            <br><br>
        </div>
    </div>
</section>
<?php
include(STAFFINC_DIR.'footer.inc.php');
