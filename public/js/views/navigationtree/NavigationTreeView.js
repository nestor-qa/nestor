define([
    'jquery',
    'fancytree',
    'underscore',
    'backbone',
    'app',
    'collections/navigationtree/NavigationTreeCollection',
    'views/navigationtree/NavigationTreeView',
    'text!templates/navigationtree/navigationTreeTemplate.html'
], function($, fancytree, _, Backbone, app, NavigationTreeCollection, NavigationTreeView, navigationTreeTemplate) {

    var NavigationTreeView = Backbone.View.extend({

        initialize: function(options) {
            _.bindAll(this, 'render', 'convertToTree');

            if (typeof options.draggable !== typeof undefined) {
                this.draggable = options.draggable;
            } else {
                this.draggable = false;
            }

            this.collection = new NavigationTreeCollection();
            //this.listenTo(this.collection, 'reset', this.render);

            this.$el.attr('id', 'navigation-tree');
            this.$el.attr('class', 'ui list');
        },

        events: {

        },

        convertToTree: function(parent, children) {
            for (idx in children) {
                var child = children[idx];
                var node = {
                    title: child.display_name,
                    key: child.descendant,
                    folder: true,
                    children: []
                };
                parent.children.push(node);
                this.convertToTree();
            }
        },

        render: function() {
            console.log('Rendering navigation tree for project ID [' + this.projectId + ']');
            var self = this;
            this.collection.setProjectId(this.projectId);
            this.collection.fetch({
                reset: true,
                success: function(results) {
                    var models = self.collection.models;
                    var model = null;
                    if (models.length > 0) {
                        model = models[0].toJSON();
                    }

                    tree = [];
                    var node = {
                        title: model.display_name,
                        key: model.descendant,
                        folder: true,
                        children: []
                    };
                    self.convertToTree(node, model.children);
                    tree.push(node);

                    var data = {
                        project_id: self.projectId
                    };
                    console.log(tree);
                    var compiledTemplate = _.template(navigationTreeTemplate, data);
                    //self.$el.html(compiledTemplate);

                    // enable drag and drop
                    if (self.draggable) {
                        console.log(self.$('#navigation-tree'));
                        self.$el.fancytree({
                            source: tree,
                            extensions: ["dnd"],
                            activeVisible: true, // Make sure, active nodes are visible (expanded).
                            aria: false, // Enable WAI-ARIA support.
                            autoActivate: true, // Automatically activate a node when it is focused (using keys).
                            autoCollapse: false, // Automatically collapse all siblings, when a node is expanded.
                            autoScroll: false, // Automatically scroll nodes into visible area.
                            clickFolderMode: 1, // 1:activate, 2:expand, 3:activate and expand, 4:activate (dblclick expands)
                            checkbox: false, // Show checkboxes.
                            debugLevel: 1, // 0:quiet, 1:normal, 2:debug
                            disabled: false, // Disable control
                            generateIds: false, // Generate id attributes like <span id='fancytree-id-KEY'>
                            idPrefix: "ft_", // Used to generate node id´s like <span id='fancytree-id-<key>'>.
                            icons: true, // Display node icons.
                            keyboard: true, // Support keyboard navigation.
                            keyPathSeparator: "/", // Used by node.getKeyPath() and tree.loadKeyPath().
                            minExpandLevel: 2, // 1: root node is not collapsible
                            selectMode: 1, // 1:single, 2:multi, 3:multi-hier
                            tabbable: true, // Whole tree behaves as one single control
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
                                    window.open(node.data.href, node.data.target);
                                }
                            },
                            click: function(e, data) { // allow re-loads
                                var node = data.node;
                                if(node.isActive() && node.data.href){
                                    // TODO: data.tree.reactivate();
                                }
                            },
                            dnd: {
                                preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
                                preventRecursiveMoves: true, // Prevent dropping nodes on own descendants
                                autoExpandMS: 400,
                                dragStart: function(node, data) {
                                    // Defines whether this node is draggable or not
                                    // The top level project should have dnd disabled
                                    if (node.data.nodeType == 1) { 
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
                                    //  Any other return value will calc the hitMode from the cursor position.
                                    // Prevent dropping a parent below another parent (only sort
                                    // nodes under the same parent)
                                    // if(node.parent !== data.otherNode.parent){
                                    //   return false;
                                    // }
                                    // Don't allow dropping *over* a node (would create a child)
                                    // return ["before", "after"];

                                    var selectedNode = data.otherNode; // the selected node
                                    if (selectedNode.data.nodeType == 3) {
                                        // Test cases should not be dnd onto projects or test cases, only test suites
                                        if (node.data.nodeType != 2) {
                                            return false;
                                        }
                                        // Test cases should not be dnd onto its own parent
                                        if (node == selectedNode.parent) {
                                            return false;
                                        }
                                    }
                                    if (selectedNode.data.nodeType == 2) {
                                        // Test suites should not be dnd onto test cases
                                        if (node.data.nodeType == 3) {
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
                                    var descendant = '' + selectedNode.data.nodeType + '-' + selectedNode.data.nodeId;
                                    var ancestor = '' + node.data.nodeType + '-' + node.data.nodeId;
                                    var url = '{{ URL.to("api/v1/nodes/move") }}';
                                    $.ajax({
                                      type: "POST",
                                      url: url,
                                      data: {descendant: descendant, ancestor: ancestor},
                                      success: function(jqXHR, textStatus, data) {
                                        selectedNode.moveTo(node, jqXHR.hitMode);
                                        var rootNode = node.tree.rootNode;
                                        rootNode.sortChildren(sortCmp, true);

                                        var $messagesHolder = jQuery("#messages-holder");
                                        $messagesHolder.empty();
                                        console.log($messagesHolder);
                                        $messagesHolder.append("<div class='alert alert-success'>\
                                        <button type='button' class='close' data-dismiss='alert'>&times;</button>\
                                        <p>Node moved successfully!</p>\
                                        </div>");
                                      },
                                      error: function(jqXHR, textStatus, data) {
                                        var responseText = jQuery.parseJSON(jqXHR.responseText);
                                        
                                        //alert(responseText.error.message);
                                        var $messagesHolder = jQuery("#messages-holder");
                                        $messagesHolder.empty();
                                        console.log($messagesHolder);
                                        $messagesHolder.append("<div class='alert alert-danger'>\
                                        <button type='button' class='close' data-dismiss='alert'>&times;</button>\
                                        <p>"+responseText.error.message+"</p>\
                                        </div>");
                                      }
                                    });
                                },
                                dragLeave: function(node, data) {
                                }
                            }
                        });
                        console.log('drag...');
                    }
                },
                error: function(collection, response, options) {
                    throw new Error("Failed to fetch projects");
                }
            });
        }

    });

    return NavigationTreeView;

});