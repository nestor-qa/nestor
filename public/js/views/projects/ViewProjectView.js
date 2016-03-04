define([
    'jquery',
    'underscore',
    'backbone',
    'views/projects/NavigationTreeView',
    'views/projects/NodeItemView',
    'models/project/ProjectModel',
    'text!templates/projects/viewProjectTemplate.html',
    'text!templates/projects/projectNodeItemTemplate.html'
], function($, _, Backbone, NavigationTreeView, NodeItemView, ProjectModel, viewProjectTemplate, projectNodeItemTemplate) {

    /**
     * Displays the navigation tree.
     */
    var ViewProjectView = Backbone.View.extend({
        el: $("#page"),

        events: {},

        initialize: function() {
            this.navigationTreeView = new NavigationTreeView();
            this.nodeItemView = new NodeItemView();

            // GC
            this.subviews = new Object();
            this.subviews.navigationTreeView = this.navigationTreeView;
            this.subviews.nodeItemView = this.nodeItemView;
        },

        render: function() {
            $('.menu a').removeClass('active');
            $('.menu a[href="#/projects"]').addClass('active');

            var compiledTemplate = _.template(viewProjectTemplate, {});
            this.$el.html(compiledTemplate);

            var navigationTreeContent = this.navigationTreeView.render();
            this.$el.find('#navigation-tree').html(navigationTreeContent);
        },

        /**
         * Display project node item on the right panel of the screen.
         */
        displayProject: function(projectId) {
            this.projectModel = new ProjectModel();
            this.projectModel.id = projectId;
            var self = this;
            this.projectModel.fetch({
                success: function(data) {
                    var data = {
                        project: self.projectModel,
                        _: _
                    };

                    var compiledTemplate = _.template(projectNodeItemTemplate, data);
                    self.nodeItemView.$el.html(compiledTemplate);
                    self.$('#content-area').replaceWith(self.nodeItemView.el);
                },
                error: function() {
                    throw new Error("Failed to fetch project");
                }
            });
        },

        rendered: function() {
            return !$("#navigation-tree").length == 0;
        }

    });

    return ViewProjectView;

});