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
$(document).on('change', '#raidmanager_player_smfuser', function() {
    $('#raidmanager_char_name').val($('#raidmanager_player_smfuser option:selected').text());
});
