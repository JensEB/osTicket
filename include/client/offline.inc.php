<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');
?>
<div id="landing_page">
<?php
if(($page=$cfg->getOfflinePage())) {
    echo $page->getBodyWithImages();
} else {
    echo '<h1>'.__('Support Ticket System Offline').'</h1>';
}
?>
</div>

