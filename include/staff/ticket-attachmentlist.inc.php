<?php
global $thisstaff;

$entries = $ticket->getThreadEntries();
?>

<?php

$sort = 'id';
if ($options['sort'] && !strcasecmp($options['sort'], 'DESC'))
    $sort = '-id';

$cmp = function ($a, $b) use ($sort) {
    return ($sort == 'id')
        ? ($a < $b) : $a > $b;
};
$htmlId = $options['html-id'] ?: ('thread-'.$ticket->getThread()->getId());

$thread_attachments = array();
foreach (Attachment::objects()->filter(array(
    'thread_entry__thread__id' => $ticket->getThread()->getId(),
))->select_related('thread_entry', 'file') as $att) {
    $thread_attachments[$att->object_id][] = $att;
}
?>
<div id="<?php echo $htmlId; ?>">
    <div id="thread-items" data-thread-id="<?php echo $ticket->getThread()->getId(); ?>" style="padding-bottom: 0px;">
    <?php
    if ($entries->exists(true)) {
        // Go through all the entries and bucket them by time frame
        $buckets = array();
        $rel = 0;
        $eCount = 0;
        foreach ($entries as $i=>$E) {
            // First item _always_ shows up
            if ($i != 0)
                // Set relative time resolution to 12 hours
                $rel = Format::relativeTime(Misc::db2gmtime($E->created, false, 43200));
            // only entries with attachments
            $acount = 0;
            foreach ($E->attachments as $att ) {
                if ($att->inline) continue;
                $acount++;
            }
            if($acount) {
                $buckets[$rel][] = $E;
                $eCount++;
            }
        }

        // Go back through the entries and render them on the page
        foreach ($buckets as $rel=>$entries) {
            // TODO: Consider adding a date boundary to indicate significant
            //       changes in dates between thread items.
            foreach ($entries as $entry) {
                ?><div id="thread-entry-<?php echo $entry->getId(); ?>"><?php
                include STAFFINC_DIR . 'templates/ticket-attachmentlist-item.tmpl.php';
                ?></div><?php
            }
        }
    }
   // This should never happen
    if ($eCount == 0) {
        echo '<p><em>'.__('No attachments have been posted to this thread.').'</em></p>';
    }
    ?>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        var container = '<?php echo $htmlId; ?>';

        // Set inline image urls.
        <?php
        $urls = array();
        foreach ($thread_attachments as $eid=>$atts) {
            foreach ($atts as $A) {
                if (!$A->inline)
                    continue;
                $urls[strtolower($A->file->getKey())] = array(
                    'download_url' => $A->file->getDownloadUrl(),
                    'filename' => $A->getFilename(),
                );
            }
        }
        ?>
        $('#'+container).data('imageUrls', <?php echo JsonDataEncoder::encode($urls); ?>);
    });
</script>
