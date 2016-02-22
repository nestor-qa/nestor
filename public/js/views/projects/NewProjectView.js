define([
  'jquery',
  'underscore',
  'backbone',
  'app',
  'models/project/ProjectModel',
  'collections/projects/ProjectsCollection',
  'views/projects/ProjectsListView',
  'text!templates/projects/newProjectTemplate.html'
], function($, _, Backbone, app, ProjectModel, ProjectsCollection, ProjectsListView, newProjectTemplate){

  var NewProjectView = Backbone.View.extend({
    el: $("#page"),

    events: {
      'click #new-project-btn': 'onSaveAttempt'
    },

    render: function() {
      $('.menu a').removeClass('active');
      $('.menu a[href="#/projects"]').addClass('active');

      this.$el.html(newProjectTemplate);

      var projectsCollection = new ProjectsCollection();
      var projectsListView = new ProjectsListView({collection: projectsCollection}); 
    },

    onSaveAttempt: function(event) {
      if(event) event.preventDefault();
      if(this.$("#new-project-form").parsley().validate()) {
        console.log('Saving project...');
        var project = new ProjectModel();
        project.save({
          name: this.$("#project-name-input").val(),
          description: this.$("#project-description-input").val(),
        }, {
          success: function(mod, res){
            if(typeof DEBUG != 'undefined' && DEBUG) console.log("SUCCESS", mod, res);
            console.log('Success!')
          },
          error: function(err){
            if(typeof DEBUG != 'undefined' && DEBUG) console.log("ERROR", err);
            app.showAlert('Error saving the project', err, 'error');
          }
        });
      } else {
        // Invalid clientside validations thru parsley
        if(typeof DEBUG != 'undefined' && DEBUG) console.log("Did not pass clientside validation");
      }
    }

  });

  return NewProjectView;
  
});
