define([
    'jquery',
    'underscore',
    'backbone',
    'app',
    'models/project/ProjectModel'
], function($, _, Backbone, app, ProjectModel){
    var ProjectsCollection = Backbone.Collection.extend({
        model: ProjectModel,
        models: [],
        
        initialize: function(options){
            _.bindAll(this, 'setPage', 'url', 'fetch', 'parse', 'position');
            this.page = 0;
            this.perPage = 0;
            this.currentPage = 0;
            this.lastPage = 0;
            this.nextPageUrl = '';
            this.previousPageUrl = '';
            this.from = 0;
            this.to = 0;
        },

        setPage: function(page) {
            this.page = page;
        },

        url: function() {
            return 'api/projects/?page=' + this.page;
        },

        fetchError: function(collection, response) {
            throw new Error("Projects fetch error");
        },

        parse: function(response) {
            this.perPage = response.per_page;
            this.currentPage = response.current_page;
            this.lastPage = response.last_page;
            this.nextPageUrl = response.next_page_url;
            this.previousPageUrl = response.previous_page_url;
            this.from = response.from;
            this.to = response.to;
            return response ? response.data : [];
        },

        position: function(projectId, reload) {
            projectId = parseInt(projectId);
            var url = 'api/projects/' + projectId + '/position';
            $.ajax({
                url: url,
                contentType: 'application/json',
                dataType: 'json',
                type: 'GET',
                beforeSend: function(xhr) {
                    // Set the CSRF Token in the header for security
                    var token = $('meta[name="csrf-token"]').attr('content');
                    if (token) xhr.setRequestHeader('X-CSRF-Token', token);

                    // Set the API version
                    // TODO: get api tree and sub application name from config
                    xhr.setRequestHeader('Accept', 'application/vnd.nestorqa.v1+json');
                },
                success: function(data, textStatus, request) {
                    console.log('Positioned project ' + projectId);
                    Backbone.trigger('project:position', [data.project, reload]);
                },
                error: function(data, textStatus, request) {
                    app.showAlert('Error positioning project', request, 'error');
                }
            });
        }

    });
 
    return ProjectsCollection;
});
