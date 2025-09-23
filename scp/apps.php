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
?>
<style>
    #AppOverviewContainer .group {
        display: grid;
        padding: 2rem;
        justify-content: space-around;
        border: 1px solid #aaa;
        margin-bottom: 1rem;
    }

    #AppOverviewContainer a.AppCard {
        display: flex;
        flex-direction: column;
        width: 15rem;
        height: 5rem;
        justify-content: center;
        align-items: center;
        text-align: center;
        border: 1px solid #999;
        background: #ccc;
        border-radius: 1rem;
        padding: 1rem;
        text-decoration: none;
    }

    #AppOverviewContainer a.AppCard span {
        font-size: 160%;
    }

    #AppOverviewContainer a.AppCard em {
        font-size: 110%;
    }
</style>
<div id="AppOverviewContainer">
    <h2><?php echo sprintf(__('Currently Installed <b>%s</b> Applications'), __('Staff')).' ('.count($staffapps).')'; ?></h2>

    <div class="group">
        <?php
        if($staffapps) {
            foreach($staffapps AS $app) {
                echo '<a class="AppCard no-pjax" href="'.$app['href'].'" title="'.$app['title'].'">'
                    .'<span>'.$app['desc'].'</span><br>'
                    .'<em>'.$app['title'].'</em>'
                    .'</a>';
            }
        } else {
            echo '<div>'.__('No Applications installed for this category').'</div>';
        }
        ?>
    </div>

<?php if($thisstaff->isAdmin()) {
    $adminapps = $applications->getAdminApps();
?>
    <h2><?php echo sprintf(__('Currently Installed <b>%s</b> Applications'), __('Admin')).' ('.count($adminapps).')'; ?></h2>

    <div class="group">
        <?php
        if($adminapps) {
            foreach($adminapps AS $app) {
                echo '<a class="AppCard no-pjax" href="'.$app['href'].'" title="'.$app['title'].'">'
                    .'<span>'.$app['desc'].'</span><br>'
                    .'<em>'.$app['title'].'</em>'
                    .'</a>';
            }
        } else {
            echo '<div>'.__('No Applications installed for this category').'</div>';
        }
        ?>
    </div>
<?php } ?>
</div>
<?php
include(STAFFINC_DIR.'footer.inc.php');
