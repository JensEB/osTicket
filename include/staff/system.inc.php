<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$commit = GIT_VERSION != '$git' ? GIT_VERSION : (
    @shell_exec('git rev-parse HEAD | cut -b 1-8') ?: '?');

$extensions = array(
        'gd' => array(
            'name' => 'gdlib',
            'desc' => __('Used for image manipulation and PDF printing')
            ),
        'iconv' => array(
            'name' => 'iconv',
            'desc' => __('Useful for email processing')
            ),
        'imap' => array(
            'name' => 'imap',
            'desc' => __('Useful for email processing')
            ),
        'ctype' => array(
            'name' => 'ctype',
            'desc' => __('Required for email fetching')
            ),
        'xml' => array(
            'name' => 'xml',
            'desc' => __('XML API')
            ),
        'dom' => array(
            'name' => 'xml-dom',
            'desc' => __('Used for HTML email processing')
            ),
        'json' => array(
            'name' => 'json',
            'desc' => __('Improves performance creating and processing JSON')
            ),
        'mbstring' => array(
            'name' => 'mbstring',
            'desc' => __('Highly recommended for non western european language content')
            ),
        'phar' => array(
            'name' => 'phar',
            'desc' => __('Highly recommended for plugins and language packs')
            ),
        'intl' => array(
            'name' => 'intl',
            'desc' => __('Highly recommended for non western european language content')
            ),
        'fileinfo' => array(
            'name' => 'fileinfo',
            'desc' => __('Used to detect file types for uploads')
            ),
        'zip' => array(
            'name' => 'zip',
            'desc' => __('Used for ticket and task exporting')
            ),
// Anpassung Anfang: check zlib php extension for mPDF
        'zlib' => array(
            'name' => 'zlib',
            'desc' => __('Required for generating pdf files')
            ),
// Anpassung Ende: check zlib php extension for mPDF
        'apcu' => array(
            'name' => 'APCu',
            'desc' => __('Improves overall performance')
            ),
        'Zend Opcache' => array(
            'name' => 'Zend Opcache',
            'desc' => __('Improves overall performance')
            ),
// Anpassung Anfang: check ldap php extension
        'ldap' => array(
            'name' => 'LDAP',
            'desc' => __('Optional - used for ldap plugin')
            ),
// Anpassung Ende: check ldap php extension
        );

?>
<h2><?php echo __('About this osTicket Installation'); ?></h2>
<table class="list" width="100%";>
<thead>
    <tr><th colspan="2"><?php echo __('Server Information'); ?></th></tr>
</thead>
<tbody>
    <tr><td><?php echo __('osTicket Version'); ?></td>
        <td><span class="ltr"><?php
/* Anpassung Anfang: set de-Version
            echo sprintf("%s (%s)", THIS_VERSION, trim($commit)); ?></span>
<?php
*/
            echo 'osTicket '.DE_VERSION_TYPE.' ';
            echo sprintf("%s (%s)", THIS_VERSION, trim($commit)).' '.__('Patch').'-'.DE_VERSION.' — '.__('German Version').'</span>';

// Anpassung Ende:  set de-Version
/* Anpassung Anfang: display osTicket.com.de-Update-Button
$lv = $ost->getLatestVersion('core', MAJOR_VERSION);
$tv = THIS_VERSION;
$gv = (GIT_VERSION == '$git') ? substr(@`git rev-parse HEAD`, 0, 7) : (false ?: GIT_VERSION);
if ($lv && $tv[0] == 'v' ? version_compare(THIS_VERSION, $lv, '>=') : $lv == $gv) { ?>
    — <span style="color:green"><i class="icon-check"></i> <?php echo __('Up to date'); ?></span>
<?php
}
else {
    // Report current version (v1.9.x ?: deadbeef ?: $git)
    $cv = $tv[0] == 'v' ? $tv : $gv;
?>
      <a class="green button action-button pull-right"
         href="https://osticket.com/download?cv=<?php echo $cv; ?>"><i class="icon-rocket"></i>
        <?php echo __('Upgrade'); ?></a>
<?php if ($lv) { ?>
      <strong> — <?php echo str_replace(
          '%s', $lv, __("%s is available")
      ); ?></strong>
<?php }
}
if (!$lv) { ?>
    <strong> — <?php echo __('This osTicket version is no longer supported. Please consider upgrading');
        ?></strong>
<?php
}
*/
    if($updateData = addFunc::getUpdateData()) {
        echo '<a class="green button action-button pull-right"href="'.$updateData['link']
            .'" target="_blank" style="margin:-5px 0px;"><i class="icon-rocket"></i>'. __('Upgrade').'</a>'
            .'<div style="margin:0px 4px;color:red; font-weight:normal; float:right;"> '
            .sprintf(__("%s is available"), $updateData['version']).' >>> </div>';
        
    } else {
        echo ' — <span style="color:green"><i class="icon-check"></i> '.__('Up to date').'</span>';
    }
// Anpassung Ende: display osTicket.com.de-Update-Button
?>
    </td></tr>
    <tr><td><?php echo __('Web Server Software'); ?></td>
        <td><span class="ltr"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></td></tr>
    <tr><td><?php echo __('MySQL Version'); ?></td>
        <td><span class="ltr"><?php echo db_version(); ?></span></td></tr>
    <tr><td><?php echo __('PHP Version'); ?></td>
        <td><span class="ltr"><?php echo phpversion(); ?></span></td></tr>
<!-- Anpassung Anfang: display used php.ini -->
    <tr><td><?php echo __('php.ini loaded'); ?></td>
        <td><span class="ltr"><?php echo php_ini_loaded_file(); ?></span></td></tr>
<!-- Anpassung Ende: display used php.ini -->
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('PHP Extensions'); ?></th></tr>
</thead>
<tbody>
    <?php
    foreach($extensions as $ext => $info) { ?>
    <tr><td><?php echo $info['name']; ?></td>
        <td><?php
            echo sprintf('<i class="icon icon-%s"></i> %s',
                    extension_loaded($ext) ? 'check' : 'warning-sign',
                    $info['desc']);
            ?>
        </td>
    </tr>
    <?php
    } ?>
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('PHP Settings'); ?></th></tr>
</thead>
<tbody>
    <tr>
        <td><span class="ltr"><code>cgi.fix_pathinfo</code></span></td>
        <td><i class="icon icon-<?php
                echo ini_get('cgi.fix_pathinfo') == 1 ? 'check' : 'warning-sign'; ?>"></i>
                <span class="faded"><?php echo __('"1" is recommended if AJAX is not working'); ?></span>
        </td>
    </tr>
    <tr>
        <td><span class="ltr"><code>date.timezone</code></span></td>
        <td><i class="icon icon-<?php
                echo ini_get('date.timezone') ? 'check' : 'warning-sign'; ?>"></i>
                <span class="faded"><?php
                    echo ini_get('date.timezone')
                    ?: __('Setting default timezone is highly recommended');
                    ?></span>
        </td>
    </tr>
</tbody>
<thead>
    <tr><th colspan="2"><?php echo __('Database Information and Usage'); ?></th></tr>
</thead>
<tbody>
    <tr><td><?php echo __('Schema'); ?></td>
        <td><?php echo sprintf('<span class="ltr">%s (%s)</span>', DBNAME, DBHOST); ?> </td></tr>
    </tr>
    <tr><td><?php echo __('Schema Signature'); ?></td>
        <td><?php echo $cfg->getSchemaSignature(); ?> </td>
    </tr>
    <tr><td><?php echo __('Space Used'); ?></td>
        <td><?php
        $sql = 'SELECT sum( data_length + index_length ) / 1048576 total_size
            FROM information_schema.TABLES WHERE table_schema = '
            .db_input(DBNAME);
        $space = db_result(db_query($sql));
/* Anpassung Anfang: Format Speicherplatz Anzeige
        echo sprintf('%.2f MiB', $space); ?></td>
*/
        $spaceUnit = 'MiB';
        if($space >= 1024) {
            $space = $space/1024;
            $spaceUnit = 'GiB';
        }
        echo sprintf('%.2f %s', $space, $spaceUnit); ?>
        </td>
<!-- Anpassung Ende: Format Speicherplatz Anzeige -->
    <tr><td><?php echo __('Space for Attachments'); ?></td>
        <td><?php
        $sql = 'SELECT
                    (DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024
                FROM
                    information_schema.TABLES
                WHERE
                    TABLE_SCHEMA = "'.DBNAME.'"
                AND
                    TABLE_NAME = "'.FILE_CHUNK_TABLE.'"
                ORDER BY
                    (DATA_LENGTH + INDEX_LENGTH)
                DESC';
        $space = db_result(db_query($sql));
/* Anpassung Anfang: Format Speicherplatz Anzeige
        echo sprintf('%.2f MiB', $space); ?></td></tr>
*/
        $spaceUnit = 'MiB';
        if($space >= 1024) {
            $space = $space/1024;
            $spaceUnit = 'GiB';
        }
        echo sprintf('%.2f %s', $space, $spaceUnit); ?>
        </td></tr>
<!-- Anpassung Ende: Format Speicherplatz Anzeige -->
    <tr><td><?php echo __('Timezone'); ?></td>
        <td><?php echo $dbtz = db_timezone(); ?>
          <?php if ($cfg->getDbTimezone() != $dbtz) { ?>
            (<?php echo sprintf(__('Interpreted as %s'), $cfg->getDbTimezone()); ?>)
          <?php } ?>
        </td></tr>
</tbody>
</table>
<br/>
<h2><?php echo __('Installed Language Packs'); ?></h2>
<div style="margin: 0 20px">
<?php
    foreach (Internationalization::availableLanguages() as $info) {
        $p = $info['path'];
        if ($info['phar'])
            $p = 'phar://' . $p;
        $manifest = (file_exists($p . '/MANIFEST.php')) ? (include $p . '/MANIFEST.php') : null;
?>
    <h3><strong><?php echo Internationalization::getLanguageDescription($info['code']); ?></strong>
        <?php if ($manifest) { ?>
            &mdash; <?php echo $manifest['Language']; ?>
        <?php } ?>
<?php   if ($info['phar'])
            PluginManager::showVerificationBadge($info['path']); ?>
        </h3>
        <div><?php echo sprintf('<code>%s</code> — %s', $info['code'],
                str_replace(ROOT_DIR, '', $info['path'])); ?>
<?php   if ($manifest) { ?>
            <br/> <?php echo __('Version'); ?>: <?php echo $manifest['Version'];
                ?>, <?php echo sprintf(__('for version %s'),
                    'v'.($manifest['Phrases-Version'] ?: '1.18')); ?>
            <br/> <?php echo __('Built'); ?>: <?php echo $manifest['Build-Date']; ?>
<?php   } ?>
        </div>
<?php
    } ?>
</div>
