define([
    'underscore',
    'backbone',
    'models/core/BaseModel',
], function(_, Backbone, BaseModel) {

    var TestRunModel = BaseModel.extend({

        defaults: {
            project_id: 0,
            name: 'No execution name set',
            description: 'No description set',
            url: '#/404'
        },

        initialize: function(options) {
            _.bindAll(this, 'parse', 'url');
        },

        url: function() {
            return 'api/testplans/' + this.testPlanId + '/testruns';
        },

        parse: function(obj) {
            if (typeof(obj.test_plan) != 'undefined')
                return obj.test_plan;
            return obj;
        }

    });

    return TestRunModel;

});