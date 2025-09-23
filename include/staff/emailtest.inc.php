<?php
if(!defined('OSTADMININC') || !$thisstaff->isAdmin()) die('Access Denied');

$info=array();
$info['subj']='osTicket test email';
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info, true);
?>
<form action="emailtest.php" method="post" class="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <h2><?php echo __('Test Outgoing Email');?></h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <em><?php echo __('Use the following form to test whether your <strong>Outgoing Email</strong> settings are properly established.');
                    ?>&nbsp;<i class="help-tip icon-question-sign" href="#test_outgoing_email"></i></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="120" class="required">
                <?php echo __('From');?>:
            </td>
            <td>
                <select name="email_id">
                    <option value="0">&mdash; <?php echo __('Select FROM Email');?> &mdash;</option>
                    <?php

                    $emails = Email::objects()->values_flat('email_id',
                            'email', 'name', 'smtp__active')
                    ->order_by('name');
                    foreach ($emails as $row) {
                        list($id,$email,$name,$smtp) = $row;
                        $selected = ($info['email_id'] && $id == $info['email_id']) ? 'selected="selected"' : '';
                        if ($name)
                            $email = Format::htmlchars("$name <$email>");
                        if ($smtp)
                            $email .= ' ('.__('SMTP').')';
                        echo sprintf('<option value="%d" %s>%s</option>',
                            $id, $selected, $email);
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['email_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                <?php echo __('To');?>:
            </td>
            <td>
                <input type="text" size="60" name="email" value="<?php echo $info['email']; ?>"
                    autofocus>
                &nbsp;<span class="error"><?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="120" class="required">
                <?php echo __('Subject');?>:
            </td>
            <td>
                <input type="text" size="60" name="subj" value="<?php echo $info['subj']; ?>">
                &nbsp;<span class="error"><?php echo $errors['subj']; ?></span>
            </td>
        </tr>
        <tr>
            <td colspan=2>
                <div style="padding-top:0.5em;padding-bottom:0.5em">
                <em><strong><?php echo __('Message');?></strong>: <?php echo __('email message to send.');?></em>&nbsp;<span class="error"><?php echo $errors['message']; ?></span></div>
                <textarea class="richtext draft draft-delete" name="body" cols="21"
                    rows="10" style="width: 90%;" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('email.diag', false, $info['body']);
    echo $attrs; ?>><?php echo $draft ?: $info['body'];
                 ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo __('Send Message');?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="emails.php"'>
</p>
</form>
