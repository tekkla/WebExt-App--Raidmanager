$(document).ready(function() {
	raidmanager_form_funcs();

	if (typeof addWebResponseCallAfter !== 'undefined')
		addWebResponseCallAfter('raidmanager_form_funcs');
});

function raidmanager_form_funcs() {

	$(".raidmanager_form input,select").on("keypress", function(e){
	    if (e.keyCode == 13) {
	        return false;
	    }
	});

	$('#raidmanager_player_smfuser').off('change').on('change',function() {
		$('#raidmanager_char_name').val($('#raidmanager_player_smfuser option:selected').text());
	});
}