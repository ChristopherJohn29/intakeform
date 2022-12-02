{% extends "basic.php" %}

{% set page_title = 'Print Provider Route sheet' %}
{% set body_class = 'print' %}

{% block content %}
 
 <script type="text/javascript">
 	window.print();
 </script>

<div class="row">
    <div class="col-md-12">
      <div class="box">

        <!-- /.box-header -->
        <div class="box-body">

            <section class="xrx-info">

                <div class="row">
                    <div class="col-md-12">
                        <h3 class="name rs">{{ record['providerName'] }}</h3>
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
                                    <th>Time</th>
                                    <th>Company</th>
                                    <th>Patient's Info</th>
                                    <th>Home Health Info</th>
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
            
            </section>

        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
      </div>

</div>

{% endblock %}