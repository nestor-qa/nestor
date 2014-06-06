{% block content %}
{% set project = testsuite.project.first %}

<div class='row'>
    <div class='col-xs-12'>
        {{ Form.open({'route': ['testsuites.update', testsuite.id], 'method': 'PUT', 'class': 'form-horizontal'}) }}
            {{ Form.hidden('project_id', project.id) }}
            <div class='form-group'>
                {{ Form.label('project', 'Project', {'class': 'col-xs-2 control-label'}) }}
                <div class='col-xs-10'>
                    {{ Form.input('text', 'project', project.name, {'id':"project", 'class': "form-control", 'readonly': 'readonly'}) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form.label('name', 'Name', {'class': 'col-xs-2 control-label'}) }}
                <div class="col-xs-10">
                    {{ Form.input('text', 'name', testsuite.name, {'id':"name", 'class': "form-control"}) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form.label('description', 'Description', {'class': 'col-xs-2 control-label'}) }}
                <div class="col-xs-10">
                    {{ Form.textarea('description', testsuite.description, {'id': "description", 'rows': "3", 'class': "form-control"}) }}
                </div>
            </div>
            <div class="form-group">
                <div class='controls'>
                	<div class='col-xs-10 col-xs-offset-2'>
                    	{{ Form.submit('Update', {'class': "btn btn-primary"}) }}&nbsp; {{ HTML.link(URL.previous(), 'Cancel', {'class': 'btn'}) }}
                    </div>
                </div>
            </div>
        {{ Form.close() }}
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        {{ Form.open({'route': ['testsuites.destroy', testsuite.id], 'method': 'DELETE', 'class': 'form-horizontal'}) }}
        <div class="form-group">
            <div class='col-xs-12'>
        	{{ Form.submit('Delete', {'class': 'btn btn-danger pull-right'}) }}
        	</div>
        </div>
        {{ Form.close() }}
    </div>
</div>

{% endblock %}