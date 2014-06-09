{% block content %}
<div class='row'>
	<div class='col-xs-12'>
		<div id="projects">
			{% if testcases[0] is defined %}
			<div class='row'>
				<div class='col-xs-4'>
					<div id='navigation_tree_panel'>
						<p>Navigation tree</p>
						{{ navigation_tree_html }}
					</div>
					<br/>
				</div>
				<div class="col-xs-8" id="test_specification">
					<div id='nodes_panel'>
						<h4>{{ testcaseVersion.name }}</h4>
						<h5>View execution history <button class='btn' id='sub-executions-btn'>&#x25BC;</button></h5>

						<div id='sub-executions'>
							{% if executions.count() > 0 %}
							<table class='table table-hover table-bordered'>
								<colgroup>
									<col width="20%" />
									<col width="60%" />
									<col width="20%" />
								</colgroup>
						    	<thead>
						    		<tr>
						    			<th>Execution status</th>
						    			<th>Notes</th>
						    			<th>Date</th>
						    		</tr>
						    	</thead>
						    	<tbody>
						    {% for execution in executions.reverse() %}
						    		<tr>
						    			<td>{{ execution.executionStatus.first.name }}</td>
						    			<td>{{ execution.notes }}</td>
						    			<td>{{ execution.created_at }}</td>
						    		</tr>
						    {% endfor %}
						    	</tbody>
						    </table>
						    {% else %}
						    <p>This test hasn't been executed yet.</p>
						    {% endif %}
						</div>
						<hr />
						<p>Execution Type: {{ testcaseVersion.executionType.first.name }}</p>
						<br/>
						<h4>Description</h4>
						<p>
							{{ testcaseVersion.description|default('No description provided') }}
						</p>
						<br/>
						<h4>Prerequisite</h4>
						<p>
							{{ testcaseVersion.prerequisite|default('No prerequisites provided') }}
						</p>
						<br/>

						{{ Form.open({'action': Request.url(), 'method': 'post', 'class': 'form-vertical'}) }}
						
							<h4>Test Case Steps</h4>

							{% set testCaseSteps = steps %}

							{% if testCaseSteps[0] is defined %}
								{% for step in testCaseSteps %}
								<table class='table table-bordered table-hover'>
									<thead>
										<colgroup>
											<col style="width: 20%;" />
											<col style="width: 80%;" />
										</colgroup>
									</thead>
									<tbody>
										<tr>
											<th colspan='2'>Step #{{ step.order }}</th>
										</tr>
										<tr>
											<th>Description</th>
											<td>{{ step.description }}</td>
										</tr>
										<tr>
											<th>Expected Result</th>
											<td>{{ step.expected_result }}</td>
										</tr>
										<tr>
											<th>Execution Status</th>
											<td>
												{% if step.lastExecutionStatusId is defined %}
													{% set lastExecutionStatusId = step.lastExecutionStatusId %}
												{% else %}
													{% set lastExecutionStatusId = 1 %} {# FIXME magic number, 1 is NOT RUN #}
												{% endif %}
												{% for executionStatus in executionStatuses %}
												<div class='radio col-xs-12'>
													<label>
														{{ Form.radio('step_execution_status_id_' ~ step.order, executionStatus.id, lastExecutionStatusId == executionStatus.id) }} {{ executionStatus.name }}
													</label>
												</div>
												{% endfor %}
											</td>
										</tr>
									</tbody>
								</table>
								{% endfor %}
							{% else %}
							<p>This case step has no steps</p>
							{% endif %}

							<h4>Execution Status</h4>

							<div class="form-group">
							    {{ Form.label('notes', 'Notes', {'class': 'control-label col-xs-12'}) }}
							    <div class="col-xs-12">
							    	{{ Form.textarea('notes', '', {'id': "notes", 'rows': "3", 'class': "form-control"}) }}
							    </div>
							</div>

							{% set lastExecutionStatusId = last_execution_status_id %}
							{% for executionStatus in executionStatuses %}
							<div class='radio col-xs-12'>
								<label>
									{{ Form.radio('execution_status_id', executionStatus.id, lastExecutionStatusId == executionStatus.id) }} {{ executionStatus.name }}
								</label>
							</div>
							{% endfor %}
							{{ Form.submit('Save', {'class': 'btn btn-primary'}) }}
						{{ Form.close() }}
					</div>
				</div>
			</div>
			{% else %}
			<p>
				This test run is empty.
			</p>
			{% endif %}
		</div>
	</div>
</div>
<script type='text/javascript'>
YUI().use('node', 'sortable', 'template', 'dd-delegate', 'transition', function(Y) {
	// used to toggle the test suite form visibility
	var set_executions_btn = Y.one('#sub-executions-btn');
	var sub_executions_div = Y.one('#sub-executions');
	set_executions_btn.on('click', function(e) {
	    e.preventDefault();
	    sub_executions_div.toggleView();
	    e.stopPropagation();
	});
	// Hide the test suites form
    sub_executions_div.hide();
});

var templatecallback = function() {
	$("#navigation_tree_panel").fancytree({
		imagePath: "{{ URL.to('/themes/default/assets/icons/32x32') }}/",
		extensions: [],
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
	    minExpandLevel: 0, // 1: root node is not collapsible
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
	    activate: function(e, data){
			var node = data.node;
			if(node.data.href){
				window.open(node.data.href, node.data.target);
			}
		},
		click: function(e, data){ // allow re-loads
			var node = data.node;
			if(node.isActive() && node.data.href){
				// TODO: data.tree.reactivate();
			}
		}
	});
	$("#navigation_tree_panel").fancytree("getRootNode").visit(function(node){
        node.setExpanded(true);
    });
	var tree = $("#navigation_tree_panel").fancytree("getTree");
	tree.setFocus(true);
}
</script>
{% endblock %}