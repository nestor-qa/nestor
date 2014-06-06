{% block content %}
<div class='row'>
	<div class='col-xs-2'>
		<p class='pull-right'><strong>Project</strong></p>
	</div>
	<div class='col-xs-10'>
		<p>{{ testsuite.project.first.name }}</p>
	</div>
</div>
<div class='row'>
	<div class='col-xs-2'>
		<p class='pull-right'><strong>Name</strong></p>
	</div>
	<div class='col-xs-10'>
		<p>{{ testsuite.name }}</p>
	</div>
</div>
<div class='row'>
	<div class='col-xs-2'>
		<p class='pull-right'><strong>Description</strong></p>
	</div>
	<div class='col-xs-10'>
		{{ Form.textarea('description', testsuite.description, {'id': "description", 'rows': "3", 'class': "col-xs-10 form-control", 'readonly': 'readonly'}) }}
    </div>
</div>
<div class='row'>
	<div class='col-xs-offset-2 col-xs-8'>
		{{ HTML.linkRoute('testsuites.edit', 'Edit', [testsuite.id], {'class': 'btn btn-primary'}) }}
		{{ HTML.link(URL.previous(), 'Back', {'class': 'btn'}) }}
	</div>
</div>
{% endblock %}