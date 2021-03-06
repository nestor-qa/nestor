define([
    'jquery',
    'fancytree',
    'uicontextmenu',
    'underscore',
    'backbone',
    'app',
    'views/navigationtree/NavigationTreeView'
], function($, fancytree, uicontextmenu, _, Backbone, app, NavigationTreeView) {

    var NavigationTreeView = Backbone.View.extend({

        initialize: function(options) {
            _.bindAll(this, 'render', 'convertToTree', 'sortCmp', 'getNodeHref', 'getNodeIcon', 'isNodeLocked');

            this.projectId = 0;

            if (typeof options !== typeof undefined && typeof options.draggable !== typeof undefined) {
                this.draggable = options.draggable;
            } else {
                this.draggable = false;
            }

            if (typeof options !== typeof undefined && typeof options.checkboxes !== typeof undefined) {
                this.checkboxes = options.checkboxes;
            } else {
                this.checkboxes = false;
            }

            this.rootNodeUrl = options.rootNodeUrl;
            this.nodeUrlPrefix = options.nodeUrlPrefix;

            this.nodeUrlSuffix = '/view';
            if (typeof options !== typeof undefined && typeof options.nodeUrlSuffix !== typeof undefined) {
                this.nodeUrlSuffix = options.nodeUrlSuffix;
            }

            this.collection = options.collection;
        },

        events: {

        },

        getNodeHref: function(node, parent, nodeUrlPrefix) {
            var url = nodeUrlPrefix + '/testsuites/';
            if (parseInt(node.node_type_id) === 2) {
                url += node.node_id + this.nodeUrlSuffix;
            } else {
                url += parent.node_id + '/testcases/' + node.node_id + this.nodeUrlSuffix;
            }
            return url;
        },

        getNodeIcon: function(node, attributes) {
            if (typeof attributes !== typeof undefined && attributes !== '{}') {
                if (typeof attributes.execution_type_id !== typeof undefined) {
                    if (parseInt(attributes.execution_type_id) === 2) {
                        return '/icons/robot-icon.png';
                    }
                }
            }
            // FIXME: yuck, let's return a simple type per function...
            return true;
        },

        isNodeLocked: function(node, attributes) {
            if (typeof attributes !== typeof undefined && attributes !== '{}') {
                if (typeof attributes.locked !== typeof undefined) {
                    if (attributes.locked === true) {
                        return true;
                    }
                }
            }
            return false;
        },

        convertToTree: function(parent, children, nodeUrlPrefix, selected) {
            for (idx in children) {
                var child = children[idx];
                var folder = parseInt(child.node_type_id) === 3 ? false : true;
                var attributes = JSON.parse(child.attributes);
                var isLocked = this.isNodeLocked(child, attributes);
                var node = {
                    title: child.display_name,
                    key: child.descendant,
                    folder: folder,
                    expanded: true,
                    children: [],
                    node_id: child.node_id,
                    node_type_id: child.node_type_id,
                    locked: isLocked,
                    extraClasses: isLocked ? '' : 'editable',
                    href: this.getNodeHref(child, parent, nodeUrlPrefix),
                    icon: this.getNodeIcon(child, attributes)
                };
                parent.children.push(node);
                this.convertToTree(node, child.children, nodeUrlPrefix,selected);
            }

            // Only test cases can be selected... in the database at least.
            if (parent.node_type_id == 3) {
                for (entry in selected) {
                    if (selected[entry]['test_cases_id'] == parent.node_id) {
                        parent.selected = true;
                        parent.preselected = true;
                    }
                }
            }
        },

        /**
         * Used by FancyTree, after a node is moved. This is the sort comparator to organise
         * the nodes within the tree.
         */
        sortCmp: function(a, b) {
            var x = a.data.node_type_id + a.title.toLowerCase(),
                y = b.data.node_type_id + b.title.toLowerCase();
            return x === y ? 0 : x > y ? 1 : -1;
        },

        render: function(options) {
            console.log('Rendering navigation tree for project ID [' + this.projectId + ']');
            var el = options.el;
            var selected = [];
            if (typeof options.selected !== typeof undefined) {
                selected = options.selected;
            }
            var self = this;
            var nodeObject = this.collection.get(this.projectId);
            var model = nodeObject.toJSON();

            tree = [];
            var node = {
                title: model.display_name,
                key: model.descendant,
                folder: true,
                expanded: true,
                children: [],
                node_id: model.node_id,
                node_type_id: model.node_type_id,
                href: self.rootNodeUrl,
                icon: true,
                selected: false,
                preselected: false
            };
            self.convertToTree(node, model.children, self.nodeUrlPrefix, selected);
            tree.push(node);

            // --- check boxes
            var checkbox = self.checkboxes;
            var selectMode = 1;
            if (checkbox) {
                selectMode = 3;
            }
            // --- end check boxes

            // --- drag and drop
            var extensions = [];
            var dnd = {};
            // enable drag and drop
            if (self.draggable) {
                extensions.push('dnd');
                dnd = {
                    preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
                    preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
                    autoExpandMS: 400,
                    dragStart: function(node, data) {
                        // Defines whether this node is draggable or not
                        // The top level project should have dnd disabled
                        if (node.data.node_type_id == 1) { 
                            return false;
                        }
                        return true;
                    },
                    dragStop: function(node, data) {
                        
                    },
                    dragOver: function(node, data) {
                    },
                    dragEnter: function(node, data) {
                        // Defines whether another node can be dragged on this node
                        // Return ['before', 'after'] to restrict available hitModes.
                        // Any other return value will calc the hitMode from the cursor position.
                        // Prevent dropping a parent below another parent (only sort
                        // nodes under the same parent)
                        // if(node.parent !== data.otherNode.parent){
                        //   return false;
                        // }
                        // Don't allow dropping *over* a node (would create a child)
                        // return ["before", "after"];

                        var selectedNode = data.otherNode; // the selected node
                        if (selectedNode.data.node_type_id == 3) {
                            // Test cases should not be dnd onto projects or test cases, only test suites
                            if (node.data.node_type_id != 2) {
                                return false;
                            }
                            // Test cases should not be dnd onto its own parent
                            if (node == selectedNode.parent) {
                                return false;
                            }
                        }
                        if (selectedNode.data.node_type_id == 2) {
                            // Test suites should not be dnd onto test cases
                            if (node.data.node_type_id == 3) {
                                return false;
                            }
                            // Test suites should not be dnd onto its own parent
                            if (node == selectedNode.parent) {
                                return false;
                            }
                        }
                        return "over";
                    },
                    dragDrop: function(node, data) {
                        var selectedNode = data.otherNode; // the selected node
                        var descendant = '' + selectedNode.data.node_type_id + '-' + selectedNode.data.node_id;
                        var ancestor = '' + node.data.node_type_id + '-' + node.data.node_id;
                        var url = "/api/navigationtree/move";
                        $.ajax({
                          type: "POST",
                          url: url,
                          data: {descendant: descendant, ancestor: ancestor},
                          success: function(jqXHR, textStatus, data) {
                            selectedNode.moveTo(node, jqXHR.hitMode);
                            var rootNode = node.tree.rootNode;
                            rootNode.sortChildren(self.sortCmp, true);
                            app.showAlert('Success!', 'Moved successfully!', 'success');
                          },
                          error: function(jqXHR, textStatus, data) {
                            var responseText = jQuery.parseJSON(jqXHR.responseText);
                            var message = responseText.error.message;
                            app.showAlert('Error moving!', message, 'error');
                          }
                        });
                    },
                    dragLeave: function(node, data) {
                    }
                }
            };
            // --- end of drag and drop

            el.fancytree('destroy');
            el.fancytree({
                source: tree,
                extensions: extensions,
                activeVisible: true, // Make sure, active nodes are visible (expanded).
                aria: false, // Enable WAI-ARIA support.
                autoActivate: true, // Automatically activate a node when it is focused (using keys).
                autoCollapse: false, // Automatically collapse all siblings, when a node is expanded.
                autoScroll: false, // Automatically scroll nodes into visible area.
                clickFolderMode: 1, // 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
                checkbox: checkbox, // Show checkboxes.
                debugLevel: 1, // 0:quiet, 1:normal, 2:debug
                disabled: false, // Disable control
                generateIds: true, // Generate id attributes like <span id='fancytree-id-KEY'>
                idPrefix: "ft_", // Used to generate node id´s like <span id='fancytree-id-<key>'>.
                icon: true, // Display node icons.
                keyboard: true, // Support keyboard navigation.
                keyPathSeparator: "/", // Used by node.getKeyPath() and tree.loadKeyPath().
                minExpandLevel: 2, // 1: root node is not collapsible
                selectMode: selectMode, // 1:single, 2:multi, 3:multi-hier
                tabindex: 0, // Whole tree behaves as one single control
                childcounter: {
                    deep: true,
                    hideZeros: true,
                    hideExpanded: true
                },
                focus: function(e, data) {
                    var node = data.node;
                    // Auto-activate focused node after 1 second
                    if(node.data.href){
                        node.scheduleAction("activate", 100000);
                    }
                },
                blur: function(e, data) {
                    data.node.scheduleAction("cancel");
                },
                activate: function(e, data) {
                    if (data.draggable)
                        return; // prevent false hits
                    var node = data.node;
                    if(node.data.href){
                        Backbone.history.navigate(node.data.href, { trigger: false });
                    }
                },
                click: function(e, data) { // allow re-loads
                    var node = data.node;
                    if(node.isActive() && node.data.href){
                        // TODO: data.tree.reactivate();
                    }
                },
                // https://github.com/mar10/fancytree/issues/581
                init: function (e, data) {
                    data.tree.getRootNode().visit(function (node) {
                        if (node.data.preselected) node.setSelected(true);
                    });
                },
                dnd: dnd
            });
            // end
            var rootNode = el.fancytree('getRootNode');
            if (typeof rootNode.sortChildren !== typeof undefined) {
                rootNode.sortChildren(self.sortCmp, true);
            }

            // context menu enabled only when the draggable mode is enabled as well
            if (self.draggable) {
                el.contextmenu({
                    delegate: ".editable",
                    menu: [
                        {
                            title: "Edit", 
                            cmd: "edit", 
                            uiIcon: "ui-icon-pencil"
                        },
                        {
                            title: "Delete",
                            cmd: "delete",
                            uiIcon: "ui-icon-trash"
                        }
                    ],
                    select: function(event, ui) {
                        var nodeId = ui.target[0].parentNode.parentNode.id;
                        // fancy tree HTML elements have the key as ft_$KEY
                        var underscoreIndex = nodeId.indexOf('_');
                        if (underscoreIndex > 0) {
                            var length = nodeId.length;
                            var key = nodeId.substring(underscoreIndex+1, length);
                            var node = el.fancytree('getNodeByKey', key);
                            var href = node.data.href;
                            if (ui.cmd == 'edit') {
                                var editHref = href.substring(0, href.length - 5);
                                Backbone.history.navigate(editHref, {
                                    trigger: false
                                });
                            } else if (ui.cmd == 'delete') {
                                var editHref = href.substring(0, href.length - 5);
                                var deleteHref = editHref + '/confirmDelete';
                                Backbone.history.navigate(deleteHref, {
                                    trigger: false
                                });
                            } else {
                                console.log('Command ' + ui.cmd + ' not recognized!');
                            }
                        }
                    }
                });
            }

            this.delegateEvents();
        }

    });

    return NavigationTreeView;

});