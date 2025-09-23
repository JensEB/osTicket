<?php
class ticket_merge_quick {
    static public function merge($ticket, $vars) {
        if(is_numeric($ticket) && preg_match('/^\d+$/', $ticket)){
            $ticket = Ticket::lookup($ticket);
        }
        if(get_class($ticket) != 'Ticket') {
            return __('No such ticket');
        }
        $pTid = $vars['parentTicket']?:$vars['manParentTicket']?:0;
        $parentTicket = Ticket::lookup($pTid);
        if(!$parentTicket || get_class($parentTicket) != 'Ticket') {
            return __('No such ticket').' (ID: '.$pTid.')';
        }

        if(   ($perms = self::checkPermissions($ticket, $parentTicket))
           && $perms !== true
          ) {
            return $perms; // return error message
        }

        if(!($vars['ignoreDiffOwner']??0) && $ticket->getUserId() != $parentTicket->getUserId() ) {
            return sprintf('%s. %s: "%s"',
                           __('Ticket owners do not match'),
                           __('If you still want to continue, mark the checkbox'),
                           __('Allow different ticket owner')
                   );
        }
        $mVars = [
            'tids'          => [
                $parentTicket->getNumber(),
                $ticket->getNumber()
            ],
            'participants'  => 'all',
            'childStatusId' => $vars['clientStatusId']?:self::getFirstClosedStatusId(),
            'parentStatusId'=> $vars['parentStatusId']??'',
            'combine'       => 1,
            'delete-child'  => $vars['deleteTicket']??''?1:0,
            'move-tasks'    => 1
        ];
        return $ticket->merge($mVars)?$parentTicket->getId():__('Error merging tickets');
    }

    static public function getFirstClosedStatusId() {
        // get first closed state which is not reopenable, if possible
        $statusId='';
        foreach (TicketStatusList::getStatuses(['states' => ['closed']]) as $s) {
            $statusId = !$statusId ?: $s->getId();
            if(!$s->isReopenable()) {
                $statusId = $s->getId();
                break;
            }
        }
        return $statusId;
    }

    static private function checkPermissions($ticket, $parentTicket) {
        global $thisstaff;
        if(get_class($ticket) != 'Ticket' || get_class($parentTicket) != 'Ticket') {
            return __('No valid tickets to merge');
        }

        // is child ticket?
        if($ticket->isChild()) {
            return sprintf( __('One or more Tickets selected is a merged child. %1$s cannot be %2$s.'),
                            __('The selected Tickets'),
                            __('merged')
                            );
        }

        // can staff merge both tickets?
        $tRole = $ticket->getRole($thisstaff);
        $ptRole = $parentTicket->getRole($thisstaff);
        if(!$tRole->hasPerm(Ticket::PERM_MERGE) || !$ptRole->hasPerm(Ticket::PERM_MERGE)) {
            return __('Permission Denied. You are not allowed to merge one of these tickets');
        }

        // are both tickets not locked by another agents?
        $tLock = $ticket->isLocked()?$ticket->getLock():false;
        $ptLock = $parentTicket->isLocked()?$parentTicket->getLock():false;
        if(   ($tLock && !$tLock->isExpired() && $tLock->getStaffId() != $thisstaff->getId())
           || ($ptLock && !$ptLock->isExpired() && $ptLock->getStaffId() != $thisstaff->getId())
          ) {
            return __('Permission Denied. At least One of these tickets are locked by another agent');
        }
        // renew the Locks
        if(is_object($tLock)) {$tLock->renew();}
        if(is_object($ptLock)) {$ptLock->renew();}

        return true;
    }
}

