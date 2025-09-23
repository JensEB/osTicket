<?php
/*********************************************************************
    class.addfunctions.php

    Enthält:
    class jstreeElement - helptopics drop down tree

    Jens Eberle <jens@isohd.net>
    Copyright (c)  2006-2023 osTicket.com.de
    http://www.osticket.com.de

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

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
