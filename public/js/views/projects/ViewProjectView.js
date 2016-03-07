define([
    'jquery',
    'underscore',
    'backbone',
    'views/navigationtree/NavigationTreeView',
    'views/projects/ViewNodeItemView',
    'views/testsuites/NewTestSuiteView',
    'models/project/ProjectModel',
    'text!templates/projects/viewProjectTemplate.html',
    'text!templates/projects/projectNodeItemTemplate.html',
    'text!templates/testsuites/newTestSuiteTemplate.html',
], function($, _, Backbone, NavigationTreeView, ViewNodeItemView, NewTestSuiteView, ProjectModel, viewProjectTemplate, projectNodeItemTemplate, newTestSuiteTemplate) {

    /**
     * Displays the navigation tree.
     */
    var ViewProjectView = Backbone.View.extend({
        el: $("#page"),

        events: {},

        initialize: function(options) {
            _.bindAll(this, 'render', 'displayProject', 'displayNewTestSuite');

            this.projectId = options.projectId;

            // Views
            this.navigationTreeView = new NavigationTreeView({
                projectId: this.projectId
            });
            this.viewNodeItemView = new ViewNodeItemView();
            this.newTestSuiteView = new NewTestSuiteView();

            // For GC
            this.subviews = new Object();
            this.subviews.navigationTreeView = this.navigationTreeView;
            this.subviews.viewNodeItemView = this.viewNodeItemView;
            this.subviews.newTestSuiteView = this.newTestSuiteView;
        },


        render: function() {
            $('.menu a').removeClass('active');
            $('.menu a[href="#/projects"]').addClass('active');

            var compiledTemplate = _.template(viewProjectTemplate, {});
            this.$el.html(compiledTemplate);

            var navigationTreeContent = this.navigationTreeView.render({
                element: this.$el.find('#navigation-tree')
            });
        },

        /**
         * Display project node item on the right panel of the screen.
         */
        displayProject: function(projectId) {
            this.navigationTreeView.projectId = projectId;
            
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
                    self.viewNodeItemView.$el.html(compiledTemplate);
                    self.$('#content-area').replaceWith(self.viewNodeItemView.el);
                },
                error: function() {
                    throw new Error("Failed to fetch project");
                }
            });
        },

        /**
         * Display new test suite form.
         */
        displayNewTestSuite: function() {
            var compiledTemplate = _.template(newTestSuiteTemplate, {});
            this.viewNodeItemView.$el.html(compiledTemplate);
            this.$('#content-area').replaceWith(this.viewNodeItemView.el);
        },

        /**
         * Display project node item on the right panel of the screen.
         */
        displayTestSuite: function(projectId, testSuiteId) {
            this.navigationTreeView.projectId = projectId;
            
            this.testSuiteModel = new TestSuiteModel();
            this.testSuiteModel.projectId = projectId;
            this.testSuiteModel.id = testSuiteId;
            var self = this;
            this.testSuiteModel.fetch({
                success: function(data) {
                    var data = {
                        project: self.testSuiteModel,
                        _: _
                    };

                    var compiledTemplate = _.template(projectNodeItemTemplate, data);
                    self.viewNodeItemView.$el.html(compiledTemplate);
                    self.$('#content-area').replaceWith(self.viewNodeItemView.el);
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