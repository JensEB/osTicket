<!-- Anpassung Anfang: Ticket merge -->
    <form id="merge" class="hidden tab_content spellcheck exclusive"
        data-lock-object-id="ticket/<?php echo $ticket->getId(); ?>"
        data-lock-id="<?php echo $mylock ? $mylock->getId() : ''; ?>"
        action="tickets.php?id=<?php
        echo $ticket->getId(); ?>#merge" name="merge" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="mergeticket">
        <input type="hidden" name="lockCode" value="<?php echo $mylock ? $mylock->getCode() : ''; ?>">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if ($errors['merge']) {?>
            <tr>
                <td width="120px">&nbsp;</td>
                <td class="error"><?php echo $errors['merge']; ?>&nbsp;</td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120px" style="vertical-align:top">
                    <label>
                    <?php echo __('Information'); ?>:
                    </label>
                </td>
                <td>
                <?php
                $canMerge = $role && !$role->hasPerm(Ticket::PERM_MERGE) ? 0 : 1;
                if(!$canMerge) {
                    echo __('You may not merge this ticket');
                } elseif($ticket->isParent()) {
                    echo __('parent tickets cannot be attached to other tickets');
                } else {
                    echo __('Select another ticket from this user to merge this ticket into it'); ?>.
                    <p>
                    <?php echo __('The following conditions must be met'); ?>:
                    </p>
                    <ul>
                        <li><?php echo __('You can edit both tickets'); ?></li>
                        <li><?php echo __('The selected ticket is open or can be opened again'); ?></li>
                        <li><?php echo __('Both tickets are not locked by another agent'); ?></li>
                    </ul>
                <?php }
                if($canMerge) { ?>
                    <p>
                        <?php echo __('For more options, please click here'); ?>:&nbsp;
                        <a href="#ajax.php/tickets/<?php echo $ticket->getId();
                             ?>/merge" onclick="javascript:
                             $.dialog($(this).attr('href').substr(1), 201);
                             return false"
                        ><i class="icon-code-fork"></i> <strong><?php echo __('Merge Tickets'); ?></strong></a>
                    </p>
                <?php } ?>
                </td>
            </tr>
        <?php if($canMerge && !$ticket->isParent()) { ?>
            <tr>
                <td width="120px" style="vertical-align:top">
                    <label><strong><?php echo __('Select Ticket'); ?>:</strong></label>
                </td>
                <td>
                    <select id="parentTicket" name="parentTicket" style="min-width: 450px">
                        <option value="">&mdash;  <?php echo __('Select Ticket'); ?> &mdash; </option>
                        <?php
                        if($user) {
                            $userTickets = Ticket::objects()->values('ticket_id', 'number', 'cdata__subject')
                                ->filter(array('user_id' => $user->getId(), 'status__state' => 'open'));
                            foreach($userTickets as $T) {
                                if($T['ticket_id'] == $ticket->getId())
                                    continue;

                                $selected = $info['parentTicket'] == $T['ticket_id'] ? ' selected' : '';
                                echo '<option value="'.$T['ticket_id'].'"'.$selected.'>'
                                    .$T['number'].': '.$T['cdata__subject']
                                    .'</option>';
                            }
                        } ?>
                    </select><span class='error'>&nbsp;*</span>
                    <p>
                        <?php echo __('or select by ticket number'); ?>:
                    </p>
                    <p>
                        <select id="manParentTicket" name="manParentTicket"
                                data-placeholder="<?php echo __('Select Ticket'); ?>">
                        </select>
                    </p>
                    <p id="mergeTicketPreview" style="display: none;">
                        <strong><?php echo __('Preview Ticket'); ?>: &nbsp;</strong>
                        <a style="display: inline" class="preview" data-preview="#tickets/0/preview" href="#">
                            <i class="icon-info-sign" style="opacity: .9;"></i>
                            #123456
                        </a>
                    </p>
                </td>
            </tr>
            <tr>
                <td width="120px" style="vertical-align:top">
                    <label><strong><?php echo __('Settings'); ?>:</strong></label>
                </td>
                <td>
                    <div>
                        <label class="inline checkbox">
                            <input type="checkbox" name="ignoreDiffOwner" <?php echo $info['ignoreDiffOwner']?'checked="checked"':''; ?> >
                            <?php echo __('Allow different ticket owner'); ?>.
                        </label>
                    </div>
                    <div>
                        <label class="inline checkbox">
                            <input type="checkbox" name="deleteTicket" <?php echo $info['deleteTicket']?'checked="checked"':''; ?> >
                            <?php echo __('Delete this Ticket after merging'); ?>.
                        </label>
                    </div>
                    <div>
                        <label class="inline checkbox">
                            <?php echo __('Parent Status');?>
                            <select id="parentStatusId" name="parentStatusId">
                            <option value="">— <?php echo __('Select'); ?> —</option>
                            <?php
                            $states = array('open', 'closed');
                            foreach (TicketStatusList::getStatuses(['states' => $states]) as $s) {
                                if (!$s->isEnabled()) continue;
                                echo sprintf('<option value="%d">%s</option>',
                                        $s->getId(),
                                        $s->getLocalName());
                            }
                            ?>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="inline checkbox">
                            <?php echo __('Client Status');?>
                            <select id="clientStatusId" name="clientStatusId">
                            <option value="">— <?php echo __('Select'); ?> —</option>
                            <?php
                            require_once(INCLUDE_DIR.'class.ticket-merge.inc.php');
                            $preSelectedState = ticket_merge_quick::getFirstClosedStatusId();
                            foreach (TicketStatusList::getStatuses(['states' => $states]) as $s) {
                                if (!$s->isEnabled()) continue;
                                $selected = ($s->getId()==$preSelectedState)?' selected':'';
                                echo sprintf('<option value="%d"%s>%s</option>',
                                        $s->getId(),
                                        $selected,
                                        $s->getLocalName());
                            }
                            ?>
                            </select>
                        </label>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </table>
        <p  style="text-align:center;">
            <input class="save pending" type="submit" value="<?php echo __('Merge Tickets') ;?>">
            <input class="" type="reset" value="<?php echo __('Reset');?>">
        </p>
    </form>
<script type="text/javascript">
$(document).ready(function() {
    $('#parentTicket').on('change', function (e) {
        $('#manParentTicket').val(null).trigger('change');
        // show ticket preview
        var value = $('#parentTicket').val();
        var text = $('#parentTicket  option:selected').text();
        if (value) {
            $('#mergeTicketPreview a').attr( "data-preview", "#tickets/"+value+"/preview");
            $('#mergeTicketPreview a').text(text);
            $('#mergeTicketPreview').show();
        } else {
            $('#mergeTicketPreview').hide();
        }
    });
    $('#manParentTicket').select2({
      width: '450px',
      minimumInputLength: 3,
      ajax: {
        url: "ajax.php/tickets/number-lookup",
        dataType: 'json',
        data: function (params) {
          return {
            q: params.term,
          };
        },
        processResults: function (data) {
          return {
            results: $.map(data, function (item) {
                subject = (item.subject.length > 35) ? item.subject.substring(0, 35) + '...' : item.subject;
              return {
                text: item.id + ' - (' + item.user  +  ') - ' + subject,
                user: item.user,
                id: item.ticket_id,
                number: item.id,
                tasks: item.tasks,
                thread_id: item.thread_id,
                spaces: '\xa0\xa0\xa0\xa0\xa0\xa0\xa0',
                mergeType: item.mergeType,
                mergePrev: item.children,
                collaborators: item.collaborators,
                entries: item.entries,
                subject: subject,
              }
            })
          };
        }
      }
    });

<?php if(($mPTicket = Ticket::lookup($info['manParentTicket']??0))) {
    $mPTicketNumber = $mPTicket->getNumber();
    $mPTicketOwnerName = $mPTicket->getOwner()->getName();
    $mPTicketSubject = $mPTicket->getSubject();
    if(strlen($mPTicketSubject) > 35) {
        $mPTicketSubject = substr($mPTicketSubject, 0, 35).'...';
    }
?>
    var selectedMPID = <?php echo $mPTicket->getId() ; ?>;
    var selectedMPLabel = <?php echo "'".$mPTicketNumber.' - ('.$mPTicketOwnerName.') - '.$mPTicketSubject."'"; ?>;
    if(selectedMPID) {
        // create the option, append to Select2 and select it
        var option = new Option(selectedMPLabel, selectedMPID, true, true);
        $('#manParentTicket').append(option).trigger('change').trigger('select2:select');
    }
<?php } ?>

    $('#manParentTicket').on('select2:select', function (e) {
        $('#parentTicket').val('');
        // show ticket preview
        var data = e.params.data;
        $('#mergeTicketPreview a').attr( "data-preview", "#tickets/"+data.id+"/preview");
        $('#mergeTicketPreview a').text(data.text);
        $('#mergeTicketPreview').show();
    });
});



</script>
