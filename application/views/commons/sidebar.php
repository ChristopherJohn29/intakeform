<section class="sidebar">
	<!-- sidebar menu: : style can be found in sidebar.less -->
        
	<ul class="sidebar-menu" data-widget="tree">
		<li class="header">MAIN NAVIGATION</li>

		<!-- <li>
			<a href="{{ site_url('dashboard') }}">
				<i class="fa fa-home"></i>
				<span>Home</span>
			</a>
		</li> -->

		{% if roles_permission_entity.has_permission_module(['PRSM']) %}

			<li class="treeview">
				<a href="#">
					<i class="fa fa-car"></i>
					<span>Route Sheet</span>
					<span class="pull-right-container">
						<i class="fa fa-angle-left pull-right"></i>
					</span>
				</a>
				<ul class="treeview-menu">

					{% if roles_permission_entity.has_permission_name(['add_prs']) %}

						<li><a href="{{ site_url('provider_route_sheet_management/route_sheet/generate') }}"><i class="fa fa-angle-right"></i> Generate</a></li>

					{% endif %}

				</ul>
			</li>

		{% endif %}

		<li class="treeview">
			<a href="#">
				<i class="fa fa-users"></i>
				<span>Users</span>
				<span class="pull-right-container">
					<i class="fa fa-angle-left pull-right"></i>
				</span>
			</a>

			<ul class="treeview-menu">

				{% if roles_permission_entity.has_permission_name(['view_user']) %}

					<li><a href="{{ site_url('user_management/profile') }}"><i class="fa fa-angle-right"></i> View</a></li>

				{% endif %}

				{% if roles_permission_entity.has_permission_name(['add_user']) %}

					<li><a href="{{ site_url('user_management/profile/add') }}"><i class="fa fa-angle-right"></i> Add</a></li>

				{% endif %}

			</ul>
		</li>
		
	</ul>
</section>