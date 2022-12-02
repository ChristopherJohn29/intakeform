{% extends "main.php" %}

{% set page_title = 'Route Details' %}

{% block content %}
	 <div class="row">
        <div class="col-md-12">
          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body">

                {{ form_open("provider_route_sheet_management/route_sheet/form") }}
              
                 	<section class="xrx-info">
                 		
                 		<div class="row">
                            <div class="col-xs-12">
                              {% if states %}
                                {{ include('commons/alerts.php') }}
                              {% endif %}
                            </div>
                            
                 			<div class="col-md-12">
                 				<h1 class="name rs">{{ record['providerName'] }}<small>Provider Name</small></h1>
                 			</div>
                 		</div>
                        
                        <div class="row spacer-bottom">
                            <div class="col-md-12">
                                <h4>Date of Service: {{ record['dateOfService'] }}</h4>
                            </div>
                        </div>
                 		
                 		<div class="row">
                 			<div class="col-md-12">
                 				
                 				<p class="lead">Route Sheet</p>
                                
                 				<div class="table-responsive">
                 				   <table id="" class="table no-margin table-striped route-sheet">
    								<thead>
    									<tr>
    										<th width="12%">Time</th>
                                            <th width="12%">Company</th>
    										<th width="22%">Patient's Info</th>
    										<th width="22%">Home Health Info</th>
    										<th>Notes</th>
    									</tr>
    								</thead>
    								
    								<tbody>

    									{% for list in lists %}

    										<tr>
    											<td>
                                                    {{ list['time'] }}
                                                </td>
                                                <td>
                                                    {{ list['company'] }}
                                                </td>
    											<td>
                                                    <p>
                                                        {{ list['patientName'] }}
                                                        <span>
                                                            {{ list['patientAddress'] }}<br>
                                                            {{ list['patientPhoneNum'] }}<br><br>
                                                            <strong>Supervising MD:</strong> {{ list['supervisingMD_firstname'] ~ ' ' ~ list['supervisingMD_lastname'] }}
                                                        </span>
                                                    </p>
                                                </td>
    	                                        <td>
                                                    <p>
                                                        {{ list['homeHealthName'] }}
                                                        <span>
                                                            {{ list['homeHealthContactName'] }}<br>
                                                            {{ list['homeHealthPhoneNum'] }}
                                                        </span>
                                                    </p>
                                                </td>
    											<td>
                                                    <p>
                                                        Type of Visit : {{ list['tovName'] }}
                                                        <span>
                                                            Other Notes: <br>
                                                            {{ list['notes']|nl2br }}
                                                        </span>
                                                    </p>
                                                </td>
    										</tr>

    									{% endfor %}

    								</tbody>
    							</table>
                                </div>
                 			</div>
                 		</div>
                 		
                 
    					<div class="row no-print">
              	
        					<div class="col-xs-12 xrx-btn-handler">

                                {% if roles_permission_entity.has_permission_name(['print_prs']) %}
        						  
                                  <a href="{{ site_url("provider_route_sheet_management/route_sheet/print") }}" target="_blank" class="btn btn-primary xrx-btn"><i class="fa fa-print"></i> Print</a>

                                {% endif %}
        						
                                {% if roles_permission_entity.has_permission_name(['download_prs']) %}
        				            
                                    <button type="submit" name="submit_type" value="pdf" class="btn btn-primary xrx-btn" style="margin-right: 5px;">
        				                <i class="fa fa-download"></i> Generate PDF
        				            </button>

                                {% endif %}
                                
                                {% if roles_permission_entity.has_permission_name(['email_prs']) %}

                                    <button type="submit" name="submit_type" value="email" class="btn btn-primary xrx-btn" style="margin-right: 5px;">
                                    	<i class="fa fa-envelope-o"></i> Email to Provider
                                    </button>

                                {% endif %}
                                
        					</div>
        		          
        		        </div>
                 		
                 	</section>

                </form>
              
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
          </div>

      </div>
{% endblock %}