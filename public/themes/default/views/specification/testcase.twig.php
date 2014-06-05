<div>
<h4>Test Case {{ node.display_name }}</h4>
<label>Name</label>
<p>{{ testcase.testCaseVersions.first.name }}</p>
<label>Description</label>
<p>{{ testcase.testCaseVersions.first.description }}</p>
<label>Prerequisite</label>
<p>{{ testcase.testCaseVersions.first.prerequisite }}</p>
<label>Execution Type</label>
<p>{{ testcase.testCaseVersions.first.executionType.name }}</p>
<hr/>
<label>Test Steps</label>
{% set steps = testcase.testCaseVersions.sortedSteps() %}
{% if steps.count() > 0 %}
	{% for step in steps %}
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
			<td>{{ step.executionStatus.first.name }}</td>
		</tr>
	</tbody>
</table>
	{% endfor %}
{% else %}
<p>No steps defined</p>
{% endif %}
<hr/>
{{ HTML.linkRoute('testcases.edit', 'Edit', [testcase.id], {'class': 'btn btn-primary'}) }}
<hr/>
</div>