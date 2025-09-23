<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$BUTTONS = false;
include CLIENTINC_DIR.'templates/sidebar.tmpl.php';
?>
<div class="main-content">
<?php
print $selected_page->getBodyWithImages();
?>
</div>

