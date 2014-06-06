{% block content %}
<div class='row'>
    <div class='col-xs-12'>
        {{ Form.open({'route': ['projects.update', project.id], 'method': 'PUT', 'class': 'form-horizontal'}) }}
            <div class="form-group">
                {{ Form.label('name', 'Name', {'class': 'col-xs-2 control-label'}) }}
                <div class="col-xs-10">
                    {{ Form.input('text', 'name', project.name, {'id':"name", 'class': "form-control"}) }}
                </div>
            </div>
            <div class="form-group">
                {{ Form.label('description', 'Description', {'class': 'col-xs-2 control-label'}) }}
                <div class="col-xs-10">
                    <!-- editor plugin -->
                    {# editor.render('description', project.description, {'id': "description", 'rows': "3", 'class': "form-control col-xs-12"}) #}
                    {{ Form.textarea('description', project.description, {'id': "description", 'rows': "3", 'class': "form-control col-xs-12"}) }}
                </div>
            </div>
            <div class="form-group">
                <div class='col-xs-offset-2'>
    	           <div class='col-xs-8'>
        	           {{ Form.submit('Update', {'class': "btn btn-primary"}) }}&nbsp; {{ HTML.link(URL.previous(), 'Cancel', {'class': 'btn'}) }}
                    </div>
                </div>
            </div>
        {{ Form.close() }}
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        {{ Form.open({'route': ['projects.destroy', project.id], 'method': 'DELETE', 'class': 'form-horizontal'}) }}
            <div class="form-group">
                <div class='col-xs-12'>
                    {{ Form.submit('Delete', {'class': 'btn btn-danger pull-right'}) }}
                </div>
            </div>
        {{ Form.close() }}
    </div>
</div>
{% endblock %}