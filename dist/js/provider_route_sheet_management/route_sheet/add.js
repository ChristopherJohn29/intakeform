var Mobiledrs =  Mobiledrs || {};

Mobiledrs.Routesheet_form_patient_details_add = (function() {
	var init = function() {
		$('#prs_ehr_systems').select2();
	};

	return {
		init: init
	};
})();

Mobiledrs.Routesheet_form_patient_details_add.init();