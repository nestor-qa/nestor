<script type='text/x-template' id='test-case-step-template'>
<div class='step'>
    {% verbatim %}
    <input type='hidden' name='step_id[]' value='-1' />
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Order</label>
      <div class='col-xs-10'>
        <input type='text' name='step_order[]' readonly='readonly' class='required-2 form-control' />
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Description</label>
      <div class='col-xs-10'>
        <textarea name='step_description[]' class='required-2 form-control' rows='4'></textarea>
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Expected Result</label>
      <div class='col-xs-10'>
        <textarea name='step_expected_result[]' class='required-2 form-control' rows='4'></textarea>
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Execution Status</label>
      <div class='col-xs-10'>
        <select name='step_execution_status[]' class='required-2 form-control'>
          <% Y.Array.each(data.execution_statuses, function (item) { %>
            <option value='<%= item.id %>'><%= item.name %></option>
          <% }); %>
        </select>
      </div>
    </div>
    {% endverbatim %}
    <div class='form-group'>
      <div class='col-xs-10 col-xs-offset-2'>
        <a href="#" class='btn btn-danger btn-remove-test-case-step'><span class='icon-minus'></span> Remove step</a>
      </div>
    </div>
</div>
</script>

<script type='text/x-template' id='existing-test-case-step-template'>
<div class='step'>
    {% verbatim %}
    <input type='hidden' name='step_id[]' value='<%= data.id %>' />
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Order</label>
      <div class='col-xs-10'>
        <input type='text' name='step_order[]' value="<%= data.order %>" readonly="readonly" class="required-2 form-control" />
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Description</label>
      <div class='col-xs-10'>
        <textarea name='step_description[]' class='required-2 form-control' rows='4'><%= data.description %></textarea>
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Expected Result</label>
      <div class='col-xs-10'>
        <textarea name='step_expected_result[]' class='required-2 form-control' rows='4'><%= data.expected_result %></textarea>
      </div>
    </div>
    <div class='form-group'>
      <label class='col-xs-2 control-label'>Execution Status</label>
      <div class='col-xs-10'>
        <select name='step_execution_status[]' class='required-2 form-control'>
          <% Y.Array.each(data.execution_statuses, function (item) { %>
            <option value='<%= item.id %>'<%= item.id == data.execution_status_id ? 'selected="selected"' : '' %>><%= item.name %></option>
          <% }); %>
        </select>
      </div>
    </div>
    {% endverbatim %}
    <div class='form-group'>
      <div class='col-xs-10 col-xs-offset-2'>
        <a href="#" class='btn btn-danger btn-remove-test-case-step'><span class='icon-minus'></span> Remove step</a>
      </div>
    </div>
</div>
</script>

{{ Form.open({'route': ['testcases.update', testcase.id], 'method': 'PUT', 'class': 'form-horizontal'}) }}
{{ Form.hidden('project_id', testcase.project_id) }}
{{ Form.hidden('test_suite_id', testcase.test_suite_id) }}
<div class="form-group">
    {{ Form.label('name', 'Name', {'class': 'control-label col-xs-2'}) }}
    <div class="col-xs-10">
      {{ Form.input('text', 'name', testcase.latestVersion.name, {'id':"name", 'class': "form-control"}) }}
    </div>
</div>
<div class="form-group">
    {{ Form.label('description', 'Description', {'class': 'control-label col-xs-2'}) }}
    <div class="col-xs-10">
      {{ Form.textarea('description', testcase.latestVersion.description, {'id': "description", 'rows': "3",'class': "form-control"}) }}
    </div>
</div>
<div class="form-group">
    {{ Form.label('prerequisite', 'Prerequisite', {'class': 'control-label col-xs-2'}) }}
    <div class="col-xs-10">{{ Form.textarea('prerequisite', testcase.latestVersion.prerequisite, {'id': "prerequisite", 'rows': "3",
        'class': "form-control"}) }}</div>
</div>
<div class="form-group">
    {{ Form.label('execution_type_id', 'Execution Type', {'class': 'control-label col-xs-2'}) }}
    <div class="col-xs-10">{{ Form.select('execution_type_id', execution_type_ids, testcase.latestVersion.execution_type_id, {'id': "execution_type_id",
        'class': "form-control"}) }}</div>
</div>
<div class="form-group">
  {{ Form.label('add_step', 'Test Case steps', {'class': 'control-label col-xs-2'}) }}
  <div class="col-xs-10">
    <div id="test-case-steps">
    <!-- Test Case steps go here -->
    </div>
    <button id='add-test-case-step' class='btn'><i class='icon-plus'></i> Add Step</button>
  </div>
</div>
<div class="form-group">
    <div class='col-xs-10 col-xs-offset-2'>
    	<div class='span8'>
        	{{ Form.submit('Update', {'class': "btn btn-primary"}) }}&nbsp; {{ HTML.link(URL.previous(), 'Cancel', {'class': 'btn'}) }}
        </div>
    </div>
</div>
{{ Form.close() }}

{{ Form.open({'route': ['testcases.destroy', testcase.id], 'method': 'DELETE', 'class': 'form-horizontal pull-right'}) }}
	{{ Form.submit('Delete', {'class': 'btn btn-danger'}) }}
{{ Form.close() }}

<br class="clearfix" />

<script type='text/javascript'>

templatecallback = function() {
    var opts = {
        absoluteURLs: false,
        cssClass : 'el-rte',
        lang     : 'en',
        height   : 100,
        toolbar  : 'normal',
        cssfiles : ['{{ URL.to('/themes/default/assets/css/plugins/elrte/elrte-inner.css') }}']
    }
    $("#description").elrte(opts);
    $("#prerequisite").elrte(opts);
}

YUI().use('node', 'sortable', 'template', 'dd-delegate', 'transition', function(Y) {
  // drag and drop of test case steps
  var sortable = new Y.Sortable({
    container: '#test-case-steps',
    nodes: 'div.step',
    opacity: '.1'
  });

  sortable.delegate.after('drag:end', function(e) {
    // update order
    fixOrder();
  });

  var micro = new Y.Template();

  // adds a new test step
  Y.one('#add-test-case-step').on('click', function(e) {
    e.preventDefault();
    addStep();
  });

  var addStep = function() {
    var newStep = micro.compile(Y.one('#test-case-step-template').getHTML());
    var o = Y.one('#test-case-steps').appendChild(newStep({
      'execution_statuses': {{ execution_statuses }}
    }));
    o.one('.btn-remove-test-case-step').on('click', removeTestCaseStep);
    // update order
    fixOrder();
  }

  var addExistingStep = function(options) {
    var newStep = micro.compile(Y.one('#existing-test-case-step-template').getHTML());
    var o = Y.one('#test-case-steps').appendChild(newStep(options));
    o.one('.btn-remove-test-case-step').on('click', removeTestCaseStep);
    // update order
    //fixOrder();
  }

  var fixOrder = function() {
    var order = 1;
    Y.all('.step').each(function(o) {
      o.one('input[name="step_order[]"]').set('value', order);
      order += 1;
    });
  };

  var removeTestCaseStep = function(e) { 
    e.preventDefault();
    var o2 = e.target.ancestor();
    var o1 = o2.ancestor();
    var o = o1.ancestor();
    o.remove();
    fixOrder();
    e.stopPropagation();
  };

  {% for step in testcase.latestVersion.sortedSteps() %}
  addExistingStep({
    'id': '{{ step.id }}',
    'order': "{{ step.order }}",
    'description': "{{ step.description}}",
    'expected_result': "{{ step.expected_result }}",
    'execution_statuses': {{ execution_statuses }},
    'execution_status_id': "{{ step.executionStatus.first.id }}"
   });
  console.log("{{ step.executionStatus.first.id }}");
  {% endfor %}

});

</script>