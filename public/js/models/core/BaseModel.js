define([
  'underscore',
  'backbone'
], function(_, Backbone) {
  
  var BaseModel = Backbone.Model.extend({

  	initialize: function (options) {
      this.set('url', '#/projects/' + options.id + '/view');
  	},

    /*
     * Abstracted fxn to make a POST request to the auth endpoint
     * This takes care of the CSRF header for security, as well as
     * updating the user and session after receiving an API response
     */
    postAuth: function(opts, callback, args){
        var self = this;
        var postData = _.omit(opts, 'method');
        if(typeof DEBUG != 'undefined' && DEBUG) console.log(postData);
        $.ajax({
            url: self.url() + '/' + opts.method,
            contentType: 'application/json',
            dataType: 'json',
            type: 'POST',
            beforeSend: function(xhr) {
                // Set the CSRF Token in the header for security
                var token = $('meta[name="csrf-token"]').attr('content');
                if (token) xhr.setRequestHeader('X-CSRF-Token', token);

                // Set the API version
                // TODO: get api tree and sub application name from config
                xhr.setRequestHeader('Accept', 'application/vnd.nestorqa.v1+json');
            },
            data:  JSON.stringify( _.omit(opts, 'method') ),
            success: function(res){
                if( !res.error ){
                    if(_.indexOf(['login', 'signup'], opts.method) !== -1) {
                        self.updateSessionUser( res.user || {} );
                        self.set({ user_id: res.user.id, logged_in: true });
                    } else {
                        self.set({ logged_in: false });
                    }

                    if(callback && 'success' in callback) callback.success(res);
                } else {
                    if(callback && 'error' in callback) callback.error(res);
                }
            },
            error: function(mod, res){
                if(callback && 'error' in callback) {
                  callback.error(mod.responseText);
                }
            }
        }).complete( function(){
            if(callback && 'complete' in callback) callback.complete(res);
        });
    },

  });

  return BaseModel;

});