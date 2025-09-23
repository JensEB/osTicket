<?php
/*********************************************************************
    class.addfunctions.php

    Enthält:
    class ds            - department selector functions
    class ddl           - due date light functions
    class jstreeElement - helptopics drop down tree

    Jens Eberle <jens@isohd.net>
    Copyright (c)  2006-2023 osTicket.com.de
    http://www.osticket.com.de

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

// Anpassung Anfang: Abteilungsauswahl
class ds {

    private $isEnabled       = true;    // display department selector
    private $autoHideEnabled = true;    // hide selector or display every time
    private $deptAlignment   = 'h';     // direction of dept list h=horizontal, v=vertical

    private $currentDept     = false;   // 'id'
    private $depts           = [];      // available Depts (ID=>name)
    private $deptStats       = [];      // Stats (ID=>array(name,count))
    private $deptChanged     = false;   // has current dept changed
    private $currentQueue    = null;   // selected Queue
    private $hiddenDepts     = [];      // hidden Depts (IDs), set if queue selected
    private $restrictToDepts = [];      // show only this Depts (IDs), set if queue selected

    function __construct($queue = null) {
        global $cfg;

        // set current queue
        if($queue && is_object($queue))
            $this->queue = $queue;

        // set config
        if($cfg && $cfg->get('dsSelector_isEnabled') != null) {
            $this->setConfig('isEnabled', $cfg->get('dsSelector_isEnabled'));
        }
        if($cfg && $cfg->get('dsSelector_autoHideEnabled') != null) {
            $this->setConfig('autoHideEnabled', $cfg->get('dsSelector_autoHideEnabled'));
        }
        if($cfg && $cfg->get('dsSelector_deptAlignment') != null) {
            $this->setConfig('deptAlignment', $cfg->get('dsSelector_deptAlignment'));
        }

        if($this->isEnabled()) {
            $this->init();
        }
    }

    /* Setter functions */

    private function init() {
        global $thisstaff;


        $savedDsId = $_SESSION['dsId'] ?: 0;
        $submittedDsId = isset($_REQUEST['dsId'])?intval($_REQUEST['dsId']):false;
        $dsId = $submittedDsId!==false ? $submittedDsId : $savedDsId;

        $allDepts=Dept::getDepartments();
        $staffDepts = $thisstaff?$thisstaff->getDepts():[];

        // set available depts
        $availableDepts = [0=>__('All')];
        foreach($allDepts as $id => $name) {
            if($staffDepts && !in_array($id, $staffDepts) )
                continue;
            if(!in_array($id, $this->hiddenDepts)) {
                $availableDepts[$id] = $name;
            }
        }
        $this->depts = $availableDepts;

        // set current dept
        $this->setCurrentDept($dsId);
    }

    public function setCurrentDept($deptID) {
        if($deptID === $this->currentDept) {
            return true;
        }
        $dsId = ($deptID && in_array($deptID, array_keys($this->depts)) )? $deptID : 0;
        $this->currentDept = intval($dsId);
        $_SESSION['dsId'] = $this->currentDept;

        // set changed flag, if dept has changed
        return $this->deptChanged = true;
    }

    public function setCurrentQueue($queue) {
        $this->currentQueue = (is_object($queue)  && ($queue instanceof CustomQueue) ) ? $queue : null;
        return $this->setQueueDepts();
    }

    public function resetCurrentQueue() {
        $this->currentQueue = false;
        return $this->setQueueDepts();
    }

    private function setQueueDepts() {
        $this->hiddenDepts = [];
        $this->restrictToDepts = [];
        $queue = $this->currentQueue;

        if(!$queue) {
            return false;
        }

        if(($crit = $queue->getCriteria(true))) {
            foreach($crit AS $c) {
                list($field,$operant,$value) = $c;
                if($field=='dept_id' && $operant=='includes') {
                    $this->restrictToDepts = array_keys($value);
                    $this->restrictToDepts[] = 0;
                }elseif($field=='dept_id' && $operant=='!includes') {
                    $this->hiddenDepts = array_keys($value);
                }
            }
        }

        return true;
    }

    public function setConfig($var, $value = '', $save = false) {
        global $cfg;

        switch ($var) {
            case 'isEnabled':
                if(!($cfg??null) || !is_object($cfg)) {
                    return $this->isEnabled;
                }
                $val = $value ? 1 : 0;
                if(!$save || $cfg->update('dsSelector_isEnabled', $val)) {
                    $this->isEnabled = $val ? true : false;
                }
                return $this->isEnabled;
                break;
            case 'autoHideEnabled':
                if(!($cfg??null) || !is_object($cfg)) {
                    return $this->autoHideEnabled;
                }
                $val = $value ? 1 : 0;
                if(!$save || $cfg->update('dsSelector_autoHideEnabled', $val)) {
                    $this->autoHideEnabled = $val ? true : false;
                }
                return $this->autoHideEnabled;
                break;
            case 'deptAlignment':
                if(   !($cfg??null) || !is_object($cfg) // no config obj
                   || !in_array($value, ['h','v'])      // invalid input
                   || $value == $this->deptAlignment    // nothing to do, already set
                  ) {
                    return $this->autoHideEnabled;
                }
                if(!$save || $cfg->update('dsSelector_deptAlignment', $value)) {
                    $this->deptAlignment = $value;
                }
                return $this->deptAlignment;
                break;
        }
        return false;
    }

    /* Getter functions */

    public function isEnabled() {
        return $this->isEnabled ? true : false;
    }

    public function isAutoHideEnabled() {
        return $this->autoHideEnabled ? true : false;
    }

    public function getDeptAlignment() {
        return $this->deptAlignment == 'v' ? 'v' : 'h';
    }

    public function getCurrentDept() {
        // returns $currentDeptId, if set and available otherwise 0
        $cdid =$this->currentDept;
        if( !$cdid
           || $this->hiddenDepts && in_array($cdid, $this->hiddenDepts)
           || $this->restrictToDepts && !in_array($cdid, $this->restrictToDepts)
          ) {
            $cdid = 0;
        }

        return $cdid;
    }

    public function deptChanged() {
        return $this->deptChanged;
    }

    /* Helper functions */

    private function getFilteredDepts() {
        $depts = [];
        foreach($this->depts as $id=>$name) {
            if(   $this->hiddenDepts && in_array($id, $this->hiddenDepts)
               || $this->restrictToDepts && !in_array($id, $this->restrictToDepts)
            ) continue;
            $depts[$id] = $name;
        }
        return $depts;
    }

    private function createDeptStats ($source) {
        $counter = array(0=>array('name'=>__('All'), 'count'=>0));
        $depts = $this->getFilteredDepts();
        
        $source->distinct = $source->annotations = $source->values = $source->ordering = [];
        $source->values('dept_id');
        $source->filter(['dept_id__in' => array_keys($depts)]);
        $source->distinct('dept_id');
        $sql = preg_replace('/SELECT/', 'SELECT COUNT(DISTINCT A1.ticket_id) AS `total`,', $source->getQuery(), 1);

        $ticketCounted = [];
        if (($res=db_query($sql, false))) {
            while (list($tcount, $did) = db_fetch_row($res)) {
                $ticketCounted[$did] = $tcount;
            }
        }

        foreach($depts as $id=>$name) {
            $counter[0]['count']   = $counter[0]['count'] + $ticketCounted[$id];
            $counter[$id] = array('name'=>$name, 'count'=>$ticketCounted[$id]?:0);
        }
        $this->deptStats = $counter;
        return true;
    }

    public function printSelector ($source) {
        $this->createDeptStats($source);
        
        $deptStats = $this->deptStats;
        $allDepts = $deptStats[0];
        $currentDept = $deptStats[$this->getCurrentDept()];
        $return = '<div id="deptSelector">';
        if($this->isAutoHideEnabled()) {
            $return .= '<div class="noteCurrentDept">'.__('Current department').':&nbsp;<strong>&raquo;&nbsp;'
                    .$currentDept['name'].' ('.$currentDept['count'].')&nbsp;&laquo;</strong>';
            $return .= ' &nbsp;-&nbsp; ( '.__('klick here to change').' )';
            $return .= '</div>';
        }
        $return .= '<table><tr><td class="DSlabel">'.__('Department selection').':</td><td>';
        $returnArray = [];
        foreach($deptStats AS $id=>$data) {
            $classDScurrent  = ($id == $this->getCurrentDept())?' DSitemCurrent':'';
            $styleAlignment  = ($this->getDeptAlignment() == 'v') ?' style="display: block; width: unset;"' : '';
            $returnArrayItem =  '<div class="DSitem'.$classDScurrent.'"'.$styleAlignment
                    .' onclick="window.location=\'tickets.php?dsId='.$id.'\'">'
                    .$data['name'].'&nbsp;('.$data['count'].')</div>';
            if($id == '0') {
                $returnArrayAll = $returnArrayItem;
            } else {
                $returnArray[$data['name']] = $returnArrayItem;
            }
        };
        ksort($returnArray);
        if(isset($returnArrayAll)) {
            $return .= $returnArrayAll;
        }
        foreach($returnArray AS $item) {
            $return .=  $item;
        };
        $return .= '</td></tr></table></div>';
        if($this->isAutoHideEnabled()) {
            $return .= '<script type="text/javascript">
                            $(document).ready(function(){
                                var dsDiv = $("#deptSelector");
                                var dsTable = $("#deptSelector table");
                                var timeToSlide = 300;
                                dsTable.hide();
                                $(".noteCurrentDept").click(function() {
                                    dsTable.show(timeToSlide);
                                });
                                $("#nav, #sub_nav").click(function() {
                                    dsTable.hide(timeToSlide);
                                });
                                dsDiv.hover(function() {
                                                <!-- dsTable.show(timeToSlide); -->
                                            }, function() {
                                                dsTable.hide(timeToSlide);
                                        }
                                );
                            });
                        </script>';
        }
        echo $return;
    }
}
// Anpassung Ende: Abteilungsauswahl
// Anpassung Anfang: Fälligkeitsampel
class ddl { //due date lights - Fälligkeitsampel
    
    static function getDdlGraficCode($dueDate = NULL, $isoverdue = 0, $isclosed = 0) {
        
        if($isclosed) {
            return '<img src="'.ROOT_PATH.'scp/images/dot_grey15x15.png" alt="" title="'.__('Ticket').': '.__('Closed').'">';
        } elseif($isoverdue) {
            $ddtext = $dueDate ? ' ('.__('Due date').': '.Format::datetime($dueDate).')':'';
            return '<span title="'.__('Ticket is already overdue').$ddtext.'" class="Icon overdueTicket"></span>';
        }elseif($dueDate == NULL) {
            return '<img src="'.ROOT_PATH.'scp/images/dot_grey15x15.png" alt="" title="'.__('no Due date').'">';
        }
        $tz = new DateTimeZone($GLOBALS['thisstaff']? $GLOBALS['cfg']->getDbTimezone($GLOBALS['thisstaff']) : 'UTC');
        $curDate = new DateTime('now',$tz);
        $dueDateTs = strtotime($dueDate);
        $displayDueDate = Format::datetime($dueDate);
        if($dueDateTs < strtotime($curDate->format('Y-m-d H:i:s'))) { // überfällig -> rot blinkend
            return '<img src="'.ROOT_PATH.'scp/images/dot_red_blink15x15.gif" alt="" style="border:1px solid red;" title="'
                .__('Ticket is already overdue').' ('.$displayDueDate.') '.' '.__('however, not marked as overdue')
                .' &#10;'.__('The option - Mark only unanswered tickets as overdue - may be selected').'">';
        } elseif($dueDateTs < strtotime($curDate->add(new DateInterval('PT1H'))->format('Y-m-d H:i:s'))) { // unter 1 Std -> rot blinkend
            return '<img src="'.ROOT_PATH.'scp/images/dot_red_blink15x15.gif" alt="" title="'
                .sprintf(__('Due in less than %s hour(s)'),'1').' ('.$displayDueDate.')">';
        } elseif($dueDateTs < strtotime($curDate->add(new DateInterval('PT3H'))->format('Y-m-d H:i:s'))) { // unter 3 Std -> rot statisch
            return '<img src="'.ROOT_PATH.'scp/images/dot_red15x15.png" alt="" title="'
                .sprintf(__('Due in less than %s hour(s)'),'3').' ('.$displayDueDate.')">';
        } elseif($dueDateTs < strtotime($curDate->add(new DateInterval('PT12H'))->format('Y-m-d H:i:s'))) { // unter 12 Std -> gelb statisch
            return '<img src="'.ROOT_PATH.'scp/images/dot_yellow15x15.png" alt="" title="'
                .sprintf(__('Due in less than %s hour(s)'),'12').' ('.$displayDueDate.')">';
        } else { // mehr als 12 Std -> grün statisch
            return '<img src="'.ROOT_PATH.'scp/images/dot_green15x15.png" alt="" title="'
                .sprintf(__('Due in more than %s hour(s)'),'12').' ('.$displayDueDate.')">';
        }
    }

    static function getDdlDescTable() {
        $desc = array();
        $desc['G'] = array( 'img' => '<img src="'.ROOT_PATH.'scp/images/dot_green15x15.png" alt="" title="'
                                .sprintf(__('Due in more than %s hour(s)'),'12').'">',
                            'desc' => sprintf(__('Due in more than %s hour(s)'),'12'));
        $desc['Y'] = array( 'img' => '<img src="'.ROOT_PATH.'scp/images/dot_yellow15x15.png" alt="" title="'
                                .sprintf(__('Due in less than %s hour(s)'),'12').'">',
                            'desc' => sprintf(__('Due in less than %s hour(s)'),'12'));
        $desc['R'] = array( 'img' => '<img src="'.ROOT_PATH.'scp/images/dot_red15x15.png" alt="" title="'
                                .sprintf(__('Due in less than %s hour(s)'),'3').'">',
                            'desc' => sprintf(__('Due in less than %s hour(s)'),'3'));
        $desc['R0'] = array('img' => '<img src="'.ROOT_PATH.'scp/images/dot_red_blink15x15.gif" alt="" title="'
                                .sprintf(__('Due in less than %s hour(s)'),'1').'">',
                            'desc' => sprintf(__('Due in less than %s hour(s)'),'1'));
        $desc['R1'] = array('img' => '<img src="'.ROOT_PATH.'scp/images/dot_red_blink15x15.gif" alt="" style="border:1px solid red;" title="'
                                .__('Ticket is already overdue').' '.__('however, not marked as overdue')
                                .' &#10;'.__('The option - Mark only unanswered tickets as overdue - may be selected').'">',
                            'desc' => __('Ticket is already overdue').' '.__('however, not marked as overdue'));
        $desc['O'] = array( 'img' => '<img src="./images/icons/overdue_tickets.gif" alt="" title="'
                                .__('Ticket is already overdue').'">',
                            'desc' => __('Ticket is already overdue'));
        $desc['No'] = array('img' => '<img src="'.ROOT_PATH.'scp/images/dot_grey15x15.png" alt="" title="'.__('no Due date').' / '.__('Ticket').': '.__('Closed').'">',
                            'desc' => __('no Due date').' / '.__('Ticket').': '.__('Closed'));

        echo '<h3 id="ddlDescHL" style="font-size:smaller;">'.__('Due date').': <a>'.__('Icon description').'</a></h3>';
        $counter=0;
        echo '<div id="ddlDescInfoDiv" style="width: 400px; padding: 5px; background-color: white; border: 2px solid #CCCCCC; display:none; position:absolute;">';
        echo '<table id="ddlDescTable">';
        foreach($desc AS $v) {
            echo '<tr>';
            #echo '<td style="'.($counter%2?'':'border:1px solid #CCC;')
            #        .'padding:5px 0px 0px 0px;width:30px;height:25px;text-align:center;">'
            #        .$v['img'].'</td>';
            echo '<td>'.$v['img'].'</td>';
            echo '<td style="font-size:smaller;">'.$v['desc'].'</td>';
            echo '<tr>';
            $counter++;
        }
        echo '</tr></table></div>';
        echo '<script type="text/javascript">'
        . '$("#ddlDescHL").click(function() {$("#ddlDescInfoDiv").toggle(200);});'
        . '</script>';
    }
}
// Anpassung Ende: Fälligkeitsampel
// Anpassung Anfang: jstree for elements
class jstreeElement {
    /* required files for jstree elements
       ./js/jstree.min.js
       ./js/jstree.sort.js
       ./js/jstree.wholerow.js
       ./js/jstree_element.js
       ./css/jstree_element.css
    */
    /* generate PHP data array
        $phpData[] = [ 'id'=>1,                 // int (required)
                       'pid'=>0,                // int (required)
                       'value'=>'field_value',  // string (required)
                       'text'=>'display text',  // string (required)
                       'selectable'=>true,      // boolean
                       // states (optional)
                       'opended'=>false,        // boolean
                       'disabled'=>false,       // boolean
                       'selected'=>false,       // boolean
                       // attributes (optional)
                       'li_attr'=>'{}',         // attributes for the generated LI node
                       'a_attr'=>{},            // attributes for the generated A node
                     ];
    */
    /* generate jsTree data from PHP data array
        $initId = '';                   // preselected itemId
        $initText = __('Select Item');  // initial text displayed
        $opts = ['parent'=>0,           // parentId
                 'depth'=>0,            // current level
                 // fromString generates id and pid by split text string using a delimiter
                 'fromString'=>true,    // generate tree from String
                 'delimiter'=>'/'       // delimiter for fromString function
                ];
        $jsData = jstreeElement::generateTree($data, $opts);
    */
    /* instanciate jsTree
    <script type="text/javascript">
    $(function() {
        var selector = 'element selector like #id or .class';
        var jsTreeOpts = [];
        jsTreeOpts['initId']='<?php echo $initId;?>';
        jsTreeOpts['initText']='— <?php echo $initText; ?> —';
        jsTreeOpts['elemData'] = <?php echo $jsData; ?>;
        jsTreeOpts['parentSelectable'] = false;
       // define onChange handler (optional)
        jsTreeOpts['onChange']=function(obj) {
            // define code here. Don't use variable this!
            // replace this with obj.
        };
        initJsTreeForElement(selector, jsTreeOpts);
    });
    </script>
    */

    static function generateTree($data, $options=array()) {
        $parent = $options['parent']?:0;
        $depth = $options['depth']?:0;
        if($options['fromString'] && $options['fromString']==TRUE) {
            $delimiter = $options['delimiter']?:'/';
            $data = self::generateDataArrayFromString($data,$delimiter);
        }

        #echo'<pre>';print_r($data);echo '</pre>';
        if($depth > 100 || !is_array($data)) return '[]'; // Make sure not to have an endless recursion
        $tree = '[';
        $i=0;
        foreach($data AS $item) {
            if($item['pid'] == $parent) {
                $treeItem = '{';
                    $treeItem .= '"id" : "'.$item['id'].'",';
                    $treeItem .= '"text" : "'.$item['text'].'",';
                    $treeItem .= '"value" : "'.$item['value'].'",';
                    $treeItem .= '"selectable" : '.(isset($item['selectable']) && !$item['selectable']?'false':'true').',';
                    //Add folder icon
                    if($item['id'] > 0) {
                        $opts['parent'] = $item['id'];
                        $opts['depth'] = $depth + 1;
                        $children = self::generateTree($data, $opts);
                    } else {
                        $children = "[]";
                    }
                    $treeItem .= '"children" : '.$children.',';
                    // add states (opended, disabled, selected)
                    $treeItem .= '"state" : {';
                        $treeItem .= 'opened:'.($item['opended']?'true':'false').',';
                        $treeItem .= 'disabled:'.($item['disabled']?'true':'false').',';
                        $treeItem .= 'selected:'.($item['selected']?'true':'false').',';
                    $treeItem .= '},';

                    // additional attributes for the generated li element
                    $treeItem .= $item['li_attr']?'"li_attr" : "'.$item['li_attr'].'",':'';
                    // additional attributes for the generated a element
                    $treeItem .= $item['a_attr']?'"a_attr" : "'.$item['a_attr'].'",':'';
                $treeItem .= '},';
                $tree .= $treeItem;
            }
        }
        //remove trailing comma
        $tree = rtrim($tree, ',');
        $tree .= ']';
        return $tree;
    }

    // specific functions to generate the data array

    private static function generateDataArrayFromString($data,$delimiter='/') {
        // function modify id, pid and text
        $id = 1;
        $jsData = [];
        $idArray = [];
        foreach ($data AS $k=>$dataItem) {
            $pid = 0;
            $text = '';
            if(strpos($dataItem['text'],$delimiter) !== false) {
                $aExpl = explode($delimiter, $dataItem['text']);
                $aExplCount = count($aExpl);
                $iPath = '';
                $level=0;
                while(isset($aExpl[$level])) {
                    if($level>=1) {
                        $pid = $idArray[$iPath];
                    }
                    $iPath .= $aExpl[$level];

                    if(!isset($idArray[$iPath])) {
                        $idArray[$iPath] = $id;
                        if($level+1 < $aExplCount) {
                            $jsData[] = array('id'=>$id,
                                              'pid'=>$pid,
                                              'value'=>'',
                                              'text'=>$aExpl[$level],
                                              'selectable'=>0,
                                              'level'=>'level:'.$level.' maxLevel:'.$aExplCount
                                             );
                            $id++;
                        }
                    }
                    $level++;
                }
                $text = $aExpl[$level-1];
            }

            $dataItem['id'] = $id;
            $dataItem['pid'] = $pid;
            if($text != '') { $dataItem['text'] = $text; }
            $jsData[] = $dataItem;
            $id++;
        }
        return $jsData;
    }
}
// Anpassung Ende: jstree for elements
