{% extends "main.php" %}

{% 
  set scripts = [
    'dist/js/home'
  ]
%}

{% set page_title = 'Welcome!' %}

{% block content %}

{% if roles_permission_entity.has_permission_module(['PRSM']) %}

<div class="row"> 
    
    <div class="col-lg-12">
        
        <div class="box">
      
        <div class="box-header with-border">
            <h3 class="box-title">Recently Added Route Sheet</h3>
        </div>
        <!-- /.box-header -->
        
        <div class="box-body">
            
            <div class="table-responsive">
                
                <table id="" class="table no-margin table-hover">
                    
                    <thead>
                        <tr>
                            <th>Date of Service</th>
                            <th>Provider</th>
                            <th width="120px">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        
                        {% if records %}

                            {% for provider_route_sheet in records %}

                                <tr>
                                   <td>{{ provider_route_sheet['dateOfService'] }}</td>
                                    <td>{{ provider_route_sheet['providerName'] }}</td>
                                    <td>
                                        
                                        {% if roles_permission_entity.has_permission_name(['view_prs']) %}

                                            <a target="_blank" href='{{ site_url("provider_route_sheet_management/route_sheet/details/#{ provider_route_sheet['routesheetID'] }") }}'><span class="label label-primary">View</span></a>
                                        
                                        {% endif %}

                                        {% if roles_permission_entity.has_permission_name(['edit_prs']) %}

                                            <a href='{{ site_url("provider_route_sheet_management/route_sheet/edit/#{ provider_route_sheet['routesheetID'] }") }}' title="Edit"><span class="label label-primary">Update</span></a>

                                        {% endif %}

                                    </td>
                                </tr>

                            {% endfor %}
                        
                        {% else %}

                            <tr>
                                <td colspan="5" class="text-center">No data available in table</td>
                            </tr>

                        {% endif %}
                        
                        
                    </tbody>
                    
                </table>
                
            </div>
            
        </div>
        <!-- /.box-body -->
        <div class="box-footer text-center">

            {% if roles_permission_entity.has_permission_module(['PRSM']) %}

                <a href="{{ site_url('provider_route_sheet_management/route_sheet/') }}">View All Route Sheet</a>

            {% endif %}

        </div>
          
        </div>

    </div>
      
</div>

{% endif %}


{% endblock %}