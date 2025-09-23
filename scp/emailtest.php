<?php
/*********************************************************************
    emailtest.php

    Email Diagnostic

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
include_once(INCLUDE_DIR.'class.email.php');
include_once(INCLUDE_DIR.'class.csrf.php');

if($_POST){
    $errors=array();
    $email=null;
    if(!$_POST['email_id'] || !($email=Email::lookup($_POST['email_id'])))
        $errors['email_id']=__('Select from email address');

    if(!$_POST['email'] || !Validator::is_valid_email($_POST['email']))
        $errors['email']=__('Valid recipient email address required');

    if(!$_POST['subj'])
        $errors['subj']=__('Subject required');

    if(!$_POST['body'])
        $errors['body']=__('Message required');

    if(!$errors && $email){
        if($email->send($_POST['email'],$_POST['subj'],
                Format::sanitize($_POST['body']),
                null, array('reply-tag'=>false))) {
            $msg=Format::htmlchars(sprintf(__('Test email sent successfully to <%s>'),
                $_POST['email']));
            Draft::deleteForNamespace('email.diag');
        }
        else
            $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }elseif($errors['err']){
        $errors['err']=sprintf('%s - %s', __('Error sending email'), __('Please try again!'));
    }
}
$nav->setTabActive('emails');
$ost->addExtraHeader('<meta name="tip-namespace" content="emails.diagnostic" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.'emailtest.inc.php');
include(STAFFINC_DIR.'footer.inc.php');
?>
