/* dropdown_jstree.js
 * 
 * How to use jstree for elements look at definition of php class jstreeElement
 */
function initJsTreeForElement(selector, options){
    var elem = $(selector);
    if(elem.length === 0) {return false;}
    // element options
    if(options instanceof Array !== true) {options = [];}
    var initId  = typeof options['initId']  !== typeof undefined ? options['initId']  : 0;
    var initText = typeof options['initText'] !== typeof undefined ? options['initText'] : false;
    var elemData   = typeof options['elemData'] !== typeof undefined ? options['elemData'] : '';
    var parentSelectable = typeof options['parentSelectable'] !== typeof undefined ? options['parentSelectable'] : true;
    // get the event handlers
    var onChangeHandler = typeof options['onChange'] !== typeof undefined ? options['onChange'] : false;

    // prepare new element
    var elemName = elem.attr('name');
    var elemId = elem.attr('id');
    elem.after("<div id='"+elemName+"_wrapper' class='jsTreeWrapper'></div>");
    var jsTreeWrapper = elem.next('div');
    elem.remove();
    var prepHiddenFieldId = '';
    if(elemId) {
        prepHiddenFieldId = 'id="'+elemId+'"';
    }
    var prepDisplayField = '<input type="text" value="'+initText+'" id="'+elemName+'_displayField" class="jsTreeDisplayField" readonly="readonly">';
    var prepDataBox = '<div id="'+elemName+'_box" class="jsTreeBox"></div>';
    var prepHiddenField = '<input '+prepHiddenFieldId+' name="'+elemName+'" type="hidden" value="">';

    jsTreeWrapper.append(prepDisplayField,
                         prepDataBox,
                         prepHiddenField
                        );

    var jsTreeDisplayField = jsTreeWrapper.children().first();
    var jsTreeBox = jsTreeWrapper.children().eq(1);// 2. child
    var jsTreeHiddenField = jsTreeWrapper.children().eq(2);// 3. child

    if(jsTreeBox) {
        // hide dropdown box and bind click event on start
        jsTreeBox.hide();
        jsTreeDisplayField.click(function(e){
            jsTreeBox.toggle();
        });

        // instanciate jstree object
        jsTreeBox.jstree({
            'core' : {
                'multiple' : false,
                'data' : elemData,
                'themes' : {
                    'name': 'default',
                    'icons' : false,
                    'dots' : true
                }
            },
            'plugins' : [ "wholerow", "sort" ],
            'sort' : function(a, b) {
                    a1 = this.get_node(a);
                    b1 = this.get_node(b);
                    if (a1.icon == b1.icon){
                        return (a1.text.toLowerCase() > b1.text.toLowerCase()) ? 1 : -1;
                    } else {
                        return (a1.icon > b1.icon) ? 1 : -1;
                    }
            },
        });

        // jstree object - events
        jsTreeBox.on("loaded.jstree", function (node, data) {
            if(initId) {
                data.instance.open_node(initId);
                data.instance.get_node(initId).parents.forEach(function(element) {
                    if(element !== '#') {
                        data.instance.open_node(element);
                    }
                });
                jsTreeDisplayField.val(data.instance.get_path(initId,'  |  '));
                jsTreeHiddenField.val(data.instance.get_node(initId).original.value);
                // hidden fields feuern kein change-Event automatisch - also manuell feuern...
                //jsTreeHiddenField.trigger("change");
            } else {

            }
        });
        jsTreeBox.on("select_node.jstree", function (node, data) {
            var selectedNode = $("#evts").jstree("get_selected");
            var isParent = data.instance.is_parent(data.node);
            var isOpen = data.instance.is_open(data.node);
            var isClosed = data.instance.is_closed(data.node);

            var jsonNodes = jsTreeBox.jstree(true).get_json('#', { flat: true });
            $.each(jsonNodes, function (i, val) {
                element = $(val).attr('id');
                if($.inArray(element, data.node.parents) === -1) {
                    data.instance.close_node(element);
                }
            });

            if(isClosed) {
                data.instance.open_node(data.selected);
            }
            data.node.parents.forEach(function(element) {
                if(element !== '#' && data.instance.is_closed(element)) {
                    data.instance.open_node(element);
                }
            });
        });
        jsTreeBox.on("changed.jstree", function (node, data) {
            var isParent = data.instance.is_parent(data.node);
            var selectable = data.node.original.selectable;
            if(selectable !== false) {
                if(!isParent || (isParent && parentSelectable)) {
                    jsTreeDisplayField.val(data.instance.get_path(data.selected,'  |  '));
                    jsTreeHiddenField.val(data.instance.get_node(data.selected).original.value);
                    // hidden fields feuern kein change-Event automatisch - also manuell feuern...
                    jsTreeHiddenField.trigger("change");
                    if(!isParent) {
                        jsTreeBox.hide();
                    }
                }
            }
        });

        // bind Handler, if transmitted
        if(onChangeHandler) {
            jsTreeHiddenField.change(function () {
                onChangeHandler(this);
            });
        }

        // additional events to close dropdown list
        $('body').click(function(){
            var classes = $(":focus").attr('class');
            var hide = true;
            if((typeof classes !== 'undefined') 
            && (classes.indexOf('jsTree', 0) || classes.indexOf('jstree', 0)) ) {
                hide = false;
            }
            if(hide)
                jsTreeBox.hide();
        });
        jsTreeBox.dblclick(function(){
            jsTreeBox.hide();
        });
    }
}
