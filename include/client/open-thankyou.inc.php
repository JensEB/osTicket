<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');

    echo Format::viewableImages(
        $ticket->replaceVars(
            $page->getLocalBody()
        ),
        ['type' => 'P']
    );
