// ----------------------------------------------------------------------------
// Prevents submit by pressing enter
// ----------------------------------------------------------------------------
$(document).on('keypress', '.raidmanager_form input,select', function(e) {
    if (e.keyCode == 13) {
        return false;
    }
});

// ----------------------------------------------------------------------------
// Copies selected user name on playercreation into field fopr the first char
// ----------------------------------------------------------------------------
$(document).on('change select dblclick', '#web_raidmanager_player_id_player', function() {
    $('#web_raidmanager_player_char_name').val($('#web_raidmanager_player_id_player option:selected').text());
});
