<?php
// Version: 2.1; Raidmanager
global $scripturl;

// -------------------------------------------------------
// GLOBAL
// -------------------------------------------------------
$txt['app_raidmanager_name'] = 'Raidmanager';

// an- u. abmelden
$txt['app_raidmanager_action_sub'] = ' anmelden';
$txt['app_raidmanager_action_unsub'] = ' abmelden';

// -------------------------------------------------------
// PERMISSIONS
// -------------------------------------------------------
$txt['permissiongroup_raidmanager_classic_perm'] = 'App: Raidmanager verwalten';
$txt['permissiongroup_simple_raidmanager_simple_perm'] = 'App: Raidmanager verwalten';
$txt['permissionname_raidmanager_perm_config'] = 'Konfiguration bearbeiten ';
$txt['permissionhelp_raidmanager_perm_config'] = 'Erlaubt den Zugriff auf die Konfiguration des Raidmanagers.';
$txt['permissionname_raidmanager_perm_raid'] = 'Raids bearbeiten ';
$txt['permissionhelp_raidmanager_perm_raid'] = 'Gestattet das anlegen und ändern der Daten eines Raides.';
$txt['permissionname_raidmanager_perm_subs'] = 'Anmeldungen verwalten';
$txt['permissionhelp_raidmanager_perm_subs'] = 'Erlaubt Anmeldungen von Spieler in den jeweiligen Raids zu verändern.';
$txt['permissionname_raidmanager_perm_setup'] = 'Setup verwalten';
$txt['permissionhelp_raidmanager_perm_setup'] = 'Getattet die Basisinformationen eines Setups zu bearbeiten sowie neue Setups anzulegen.';
$txt['permissionname_raidmanager_perm_setlist'] = 'Setlisten verwalten';
$txt['permissionhelp_raidmanager_perm_setlist'] = 'Erlaubt das setzen und entfernen von Chars in den Setups der Raids.';
$txt['permissionname_raidmanager_perm_stats'] = 'Statistiken einsehen';
$txt['permissionhelp_raidmanager_perm_stats'] = 'Gestattet den Zugriff auf die statistischen Auswertungen';
$txt['permissionname_raidmanager_perm_profiles'] = 'Spielerprofile einsehen';
$txt['permissionhelp_raidmanager_perm_profiles'] = 'Gestattet es alle und nicht nur das eigene Spielerprofil einzusehen';
$txt['permissionname_raidmanager_perm_player'] = 'Spieler bearbeiten';
$txt['permissionhelp_raidmanager_perm_player'] = 'Erlaubt Spieler mit ihren Chars zu verwalten. Dazu gehört die Verwaltung der Main und Twinks, den Status der Spieler und deren Option zur Autoanmeldung.';

// -------------------------------------------------------
// MENU
// -------------------------------------------------------
$txt['app_raidmanager_raids'] = 'Raids';
$txt['app_raidmanager_playerlist'] = 'Spieler';
$txt['app_raidmanager_stats'] = 'Statistik';
$txt['app_raidmanager_autoadd'] = 'Autoraid';

// -------------------------------------------------------
// HEADLINES
// -------------------------------------------------------
$txt['app_raidmanager_headline'] = 'Raidmanager';
$txt['app_raidmanager_headline_thisraid'] = 'dieser Raid';
$txt['app_raidmanager_headline_playerlist'] = 'Spielerliste';
$txt['app_raidmanager_headline_raidplanner'] = 'Raidplaner';
$txt['app_raidmanager_headline_profile'] = 'Profil';
$txt['app_raidmanager_headline_links'] = 'Links';

// -------------------------------------------------------
// CATEGORIES
// -------------------------------------------------------
$txt['app_raidmanager_category_tank'] = 'Tank';
$txt['app_raidmanager_category_damage'] = 'Schaden';
$txt['app_raidmanager_category_heal'] = 'Heilung';

// -------------------------------------------------------
// Calendar
// -------------------------------------------------------
$txt['app_raidmanager_calendar_current'] = 'laufende Raids';
$txt['app_raidmanager_calendar_future'] = 'kommende Raids';
$txt['app_raidmanager_calendar_recent'] = 'alte Raids';
$txt['app_raidmanager_calendar_none'] = 'keine Raids vorhanden';

// -------------------------------------------------------
// RAID
// -------------------------------------------------------

/* Display */
$txt['app_raidmanager_raid_headline'] = 'Raidinfos';
$txt['app_raidmanager_raid_starttime'] = 'Start';
$txt['app_raidmanager_raid_endtime'] = 'Ende';
$txt['app_raidmanager_raid_update'] = 'letztes Update';
$txt['app_raidmanager_raid_specials'] = 'Hinweise';
$txt['app_raidmanager_raid_topiclink'] = 'zum Diskussionstopic';

/* Edit */
$txt['app_raidmanager_raid_headline_edit'] = 'Raidinfos bearbeiten';
$txt['app_raidmanager_raid_headline_new'] = 'neuer Raid';
$txt['app_raidmanager_raid_destination'] = 'Ziel des Raids';
$txt['app_raidmanager_raid_datestart'] = 'Datum, Start';
$txt['app_raidmanager_raid_dateend'] = 'Datum, Ende';
$txt['app_raidmanager_raid_timestart'] = 'Startzeit';
$txt['app_raidmanager_raid_timeend'] = 'Ende gegen';
$txt['app_raidmanager_raid_rotapoints'] ='Rotationspunkte';
$txt['app_raidmanager_raid_raidweek'] ='Raidwoche';
$txt['app_raidmanager_raid_actual'] ='aktuell';
$txt['app_raidmanager_raid_autosignon'] = 'Autoanmeldung';

// --------------------------------------------------------
// PLAYER
// --------------------------------------------------------
$txt['app_raidmanager_subscription_headline'] = 'Anmeldesituation';
$txt['app_raidmanager_subscription_resigned_headline'] = 'abgemeldet';
$txt['app_raidmanager_subscription_noresponse_headline'] = 'unbekannt';
$txt['app_raidmanager_subscription_enrolled_headline'] = 'angemeldet';
$txt['app_raidmanager_subscription_noplayer'] = 'niemand';

$txt['app_raidmanager_raid_availlist_headline'] = 'Verf&uuml;gbare Spieler';
$txt['app_raidmanager_raid_availlist_nodata'] = 'keine Spieler angemeldet';

/* Spielerstatus für den Raid */
$txt['app_raidmanager_raid_subscriptionstate_enrolled'] = 'Du bist bei diesem Raid angemeldet.';
$txt['app_raidmanager_raid_subscriptionstate_resigned'] = 'Du bist bei diesem Raid nicht angemeldet.';
$txt['app_raidmanager_raid_subscriptionstate_noresponse'] = 'Du bist bei diesem Raid weder ab- noch angemeldet.';

$txt['app_raidmanager_raid_signon_change'] = 'Anmeldung &auml;ndern';
$txt['app_raidmanager_raid_signon_confirm'] = 'Soll der Spieler wirklich angemeldet werden?';
$txt['app_raidmanager_raid_signon_title'] = 'Spieler anmelden';
$txt['app_raidmanager_raid_signoff_confirm'] = 'Soll der Spieler wirklich abgemeldet werden?';
$txt['app_raidmanager_raid_signoff_title'] = 'Spieler abmelden';

/* Autoraid */
$txt['app_raidmanager_raid_autoraid'] = 'Autoerstellung';
$txt['app_raidmanager_autoraid_headline'] = 'Autoraid wurde durchgef&uuml;hrt';
$txt['app_raidmanager_autoraid_raid_destination'] = 'Raid';
$txt['app_raidmanager_autoraid_setup_title'] = 'Autosetup #1';
$txt['app_raidmanager_autoraid_setup_description'] = 'automatisch angelegt durch Autoraid';

// -------------------------------------------------------
// SUBSCRIPTION
// -------------------------------------------------------
$txt['app_raidmanager_subslist_headline'] = 'Raidanmeldungen';

// -------------------------------------------------------
// COMMENT
// -------------------------------------------------------
$txt['app_raidmanager_comment_headline'] = 'Kommentare';
$txt['app_raidmanager_comment_enroll'] = 'anmelden';
$txt['app_raidmanager_comment_resign'] = 'abmelden';
$txt['app_raidmanager_comment_empty'] = 'Keine Kommentare vorhanden';
$txt['app_raidmanager_comment_comment'] = 'Kommentar schreiben...';
$txt['app_raidmanager_comment_placeholder'] = 'Deine Nachricht ...';

// -------------------------------------------------------
// SETUP
// -------------------------------------------------------
$txt['app_raidmanager_setup_headline'] = 'Setups';
$txt['app_raidmanager_setup_edit'] = 'Setup bearbeiten';
$txt['app_raidmanager_setup_new'] = 'Neues Setup';
$txt['app_raidmanager_setup_need_tank'] = 'Anzahl Tank';
$txt['app_raidmanager_setup_need_damage'] = 'Anzahl Schaden';
$txt['app_raidmanager_setup_need_heal'] = 'Anzahl Heilung';
$txt['app_raidmanager_setup_setlist'] = 'Aufstellung';
$txt['app_raidmanager_setup_notset'] = 'Optionen';
$txt['app_raidmanager_setup_missing_rota'] = 'Die Rotapunkte f&uuml;r dieses Setup fehlen!';
$txt['app_raidmanager_setup_rota'] = 'R';
$txt['app_raidmanager_setup_noneset'] = 'keine feste Aufstellung vorhanden';
$txt['app_raidmanager_setup_title'] = 'Bezeichnung';
$txt['app_raidmanager_setup_description'] = 'Beschreibung';
$txt['app_raidmanager_setup_points'] = 'Punktwert';
$txt['app_raidmanager_setup_points_desc'] = 'Wert in Punkten (z.B. je Farmboss 1 Pkt und Endboss 3 Pkt)';
$txt['app_raidmanager_setup_position'] = 'Position';

// -------------------------------------------------------
// SETLIST
// -------------------------------------------------------
$txt['app_raidmanager_setlist_headline'] = 'Aufstellung';
$txt['app_raidmanager_setlist_headline_avail'] = 'verf&uuml;gbar';
$txt['app_raidmanager_setlist_headline_set'] = 'gesetzt';
$txt['app_raidmanager_setlist_unset'] = ' aus Setup entfernen';
$txt['app_raidmanager_setlist_tank'] = ' als Tank setzen';
$txt['app_raidmanager_setlist_damage'] = 'als DD setzen';
$txt['app_raidmanager_setlist_heal'] = 'als Heiler setzen';
$txt['app_raidmanager_setlist_none_set'] = 'niemand gesetzt';
$txt['app_raidmanager_setlist_none_avail'] = 'niemand verf&uuml;gbar';

$txt['app_raidmanager_setup_delete_last_existing'] = 'Dies ist das einzige Setup f&uuml;r diesen Raid. Es darf nicht gel&ouml;scht werden!';

// --------------------------------------------------------
// PLAYER
// --------------------------------------------------------

/* List */
$txt['app_raidmanager_playerlist_headline'] = 'Spielerverwaltung';
$txt['app_raidmanager_playerlist_headline_create'] = 'Spieler anlegen';
$txt['app_raidmanager_playerlist_headline_active'] = 'Aktive Spieler';
$txt['app_raidmanager_playerlist_headline_inactive'] = 'Inaktive Spieler';
$txt['app_raidmanager_playerlist_headline_applicant'] = 'Bewerber';
$txt['app_raidmanager_playerlist_headline_old'] = 'Veraltete Spieler';
$txt['app_raidmanager_playerlist_empty'] = 'Keine Spieler in dieser Gruppe.';

/* Profile */
$txt['app_raidmanager_player_headline'] = 'Spielerprofil';
$txt['app_raidmanager_player_member'] = 'Member';
$txt['app_raidmanager_player_char_name'] = 'Charname';
$txt['app_raidmanager_player_id_class'] = 'Klasse';
$txt['app_raidmanager_player_id_category'] = 'Rolle';
$txt['app_raidmanager_player_autosignon'] = 'Autoanmeldung';
$txt['app_raidmanager_player_autosignon_on'] = 'an';
$txt['app_raidmanager_player_autosignon_off'] = 'aus';
$txt['app_raidmanager_player_active'] = 'Aktiv';
$txt['app_raidmanager_player_activity'] = 'Teilnahme';
$txt['app_raidmanager_player_activity_desc']  = '';
$txt['app_raidmanager_player_pm'] = 'Info PM';
$txt['app_raidmanager_player_pm_desc'] = 'Spieler m&ouml;chte Info PMs dar&uuml;ber haben, wenn er gesetzt wird.';
$txt['app_raidmanager_player_create'] = 'Neuer Spieler';
$txt['app_raidmanager_player_create_confirm'] = 'Spieler mit den angegebenen Daten jetzt anlegen?';
$txt['app_raidmanager_player_edit'] = 'Spielerdaten bearbeiten';
$txt['app_raidmanager_player_delete'] = 'Spieler l&ouml;schen';
$txt['app_raidmanager_player_gsprofile'] = 'Raidmanager Profil anzeigen';
$txt['app_raidmanager_player_smfprofile'] = 'SMF Profil anzeigen';
$txt['app_raidmanager_player_calcindes'] = 'Teilnahmeindex berechnen';
$txt['app_raidmanager_player_charlist'] = 'Charakterliste anzeigen';
$txt['app_raidmanager_player_state'] = 'Status';
$txt['app_raidmanager_player_state_old'] = 'veraltet';
$txt['app_raidmanager_player_state_applicant'] = 'Bewerber';
$txt['app_raidmanager_player_state_inactive'] = 'inaktiver Spieler';
$txt['app_raidmanager_player_state_active'] = 'aktiver Spieler';
$txt['app_raidmanager_player_smfuser'] = 'SMF User';

/* Charlist */
$txt['app_raidmanager_charlist_headline'] = 'Charakterliste';
$txt['app_raidmanager_charlist_add'] = 'neuen Charakter anlegen';

/* Charprofile */
$txt['app_raidmanager_char_headline'] = 'Chardaten';
$txt['app_raidmanager_char_char_name'] = 'Charname';
$txt['app_raidmanager_char_race'] = 'Rasse';
$txt['app_raidmanager_char_id_class'] = 'Klasse';
$txt['app_raidmanager_char_id_category'] = 'Rolle';
$txt['app_raidmanager_char_type'] = 'Type';
$txt['app_raidmanager_char_is_main'] = 'Mainchar';
$txt['app_raidmanager_char_is_main_desc'] = 'Dies ist der Mainchar des Spielers!';
$txt['app_raidmanager_char_istwink'] = 'Twink';
$txt['app_raidmanager_char_name_placeholder'] = '<< Charnamen angeben >>';
$txt['app_raidmanager_char_name_already_taken'] = 'Charname ist bereits vergeben.';
$txt['app_raidmanager_char_name_missing'] = 'Bitte einen Charakternamen angeben';
$txt['app_raidmanager_char_edit_headline'] = 'Charakter bearbeiten';
$txt['app_raidmanager_char_add_headline'] = 'Neuer Charakter';
$txt['app_raidmanager_char_mainchar_no_delete'] = 'Der Mainchar eines Spielers kann so nicht gel&ouml;scht werden. Bitte erst einen anderen Mainchar bestimmen und dann erneut den L&ouml;schvorgang starten.';
$txt['app_raidmanager_char_mainchar'] = 'Typ';

// -------------------------------------------------------
// STATS
// -------------------------------------------------------
$txt['app_raidmanager_stats_headline'] = 'Statistiken';
$txt['app_raidmanager_stats_first_raid'] = 'erster Raid';
$txt['app_raidmanager_stats_last_raid'] = 'letzter Raid';
$txt['app_raidmanager_stats_days_in_raid'] = 'Tage im Raid';

/* Subscriptions */
$txt['app_raidmanager_stats_headline_subs'] = 'Anmeldungen';
$txt['app_raidmanager_stats_num_subs'] = 'Angemeldet';
$txt['app_raidmanager_stats_num_unsubs'] = 'Abgemeldet';
$txt['app_raidmanager_stats_subindex_head'] = 'Beteiligung';
$txt['app_raidmanager_stats_subindex_30'] = '30 Tage';
$txt['app_raidmanager_stats_subindex_60'] = '60 Tage';
$txt['app_raidmanager_stats_subindex_90'] = '90 Tage';

/* Rota */
$txt['app_raidmanager_stats_rota_headline'] = 'Rota';
$txt['app_raidmanager_stats_rota_days'] = 'Tage';
$txt['app_raidmanager_stats_rota_num_setups'] = 'Setups';
$txt['app_raidmanager_stats_rota_num_set'] = 'dabei gesetzt';
$txt['app_raidmanager_stats_rota_num_avail'] = 'auf Ersatz';
$txt['app_raidmanager_stats_rota_num_maxpoints'] = 'erreichbare Punkte';
$txt['app_raidmanager_stats_rota_num_gotpoints'] = 'erhaltene Punkte';

// -------------------------------------------------------
// CONFIG
// -------------------------------------------------------

/* Group: Display */
$txt['app_raidmanager_cfg_group_display'] = 'Darstellung';
$txt['app_raidmanager_cfg_group_display_desc'] = 'Einstellungen zur optischen Darstellung des Raidmanager';
$txt['app_raidmanager_cfg_color_style'] = 'Farbstil';
$txt['app_raidmanager_cfg_color_style_desc'] = 'Hier den Namen für den zu verwendenden Farbstil CSS Datei eingeben. Diese Anwendung kann durch verschiedne Farbstile an das genutzte Theme gezielt angepasst werden. Farbstile müssen im App CSS Verzeichnis als normale CSS Datei gespeichert sein.';
$txt['app_raidmanager_cfg_datepicker_format'] = 'Format Datumsauswahl';
$txt['app_raidmanager_cfg_datepicker_format_desc'] = 'Betrifft die Datumwahl bei der Raidbearbeitung. Standardwert ist das deutsche Format(dd.mm.yy). Alternativ kann man auch das amerikanische Format wählen.';
$txt['app_raidmanager_cfg_date_format'] = 'Format Datumanzeige';
$txt['app_raidmanager_cfg_date_format_desc'] = 'Format welches bei der Darstellung von Datumsangaben verwendet werden soll.';
$txt['app_raidmanager_cfg_num_list_future_raids'] = 'kommende Raids';
$txt['app_raidmanager_cfg_num_list_future_raids_desc'] = 'Anzahl an kommenden Raids, die in der Raidliste angezeigt werden sollen.';
$txt['app_raidmanager_cfg_num_list_recent_raids'] = 'vergangener Raids';
$txt['app_raidmanager_cfg_num_list_recent_raids_desc'] = 'Anzahl an vergangenen Raids, die in der Raidliste angezeigt werden sollen.';

/* Group: Raid */
$txt['app_raidmanager_cfg_group_raid'] = 'Raid';
$txt['app_raidmanager_cfg_raid_autosignon'] = 'Autoanmeldung';
$txt['app_raidmanager_cfg_raid_autosignon_desc'] = 'Automatisches Anmelden von Spielern bei neuen Raids. Dies betrifft alle Spieler, die auf Autoanmeldung gestellt sind.';
$txt['app_raidmanager_cfg_raid_new_days_ahead'] = 'Tage voraus';
$txt['app_raidmanager_cfg_raid_new_days_ahead_desc'] = 'Anzahl an Tagen, die vorausgehend eingehalten werden sollen. Die Differenz zwischen vorhandenen Raidtagen und diesem Wert wird als neue Raids automatisch angelegt.';
$txt['app_raidmanager_cfg_raid_days'] = 'Raidtage';
$txt['app_raidmanager_cfg_raid_days_desc'] = 'Für die ausgewählten Tage werden automatisch neue Raids angelegt, so die Anzahl kommender Raids die gewünschte Menge unterschreitet.';
$txt['app_raidmanager_cfg_raid_destination'] = 'Raidziel';
$txt['app_raidmanager_cfg_raid_destination_desc'] = 'Wird bei der automatischen Raiderstellung als Raidziel eingetragen.';
$txt['app_raidmanager_cfg_raid_specials'] = 'Hinweise';
$txt['app_raidmanager_cfg_raid_specials_desc'] = 'Raidhinweise, die bei jedem neuen Raid automatisch mit angegeben werden sollen.';
$txt['app_raidmanager_cfg_raid_time_start'] = 'Startzeit';
$txt['app_raidmanager_cfg_raid_time_start_desc'] = 'Uhrzeit zu der die Raids beginnen. Die Zeitangabe muss das Format hh::ss haben.';
$txt['app_raidmanager_cfg_raid_duration'] = 'Dauer';
$txt['app_raidmanager_cfg_raid_duration_desc'] = 'Dauer des Raides in Minuten. Dieser Wert wird bei der automatischen Raiderstellung als Wert für die Berechnung der Raidendzeit verwendet.';
$txt['app_raidmanager_cfg_raid_weekday_start'] = 'Wochentag';
$txt['app_raidmanager_cfg_raid_weekday_start_desc'] = 'Tag der Woche, an dem der die Raidwoche beginnt (0=Sonntag, 6=Samstag). Bei WoW üblicherweise 2 für Dienstag oder 3 für Mittwoch.';

/* Group: Setup */
$txt['app_raidmanager_cfg_group_setup'] = 'Setup';
$txt['app_raidmanager_cfg_setup_title'] = 'Setupname';
$txt['app_raidmanager_cfg_setup_title_desc'] = 'Diese Bezeichnung wird bei der automatischen Raiderstellung für das erste Setup gewählt.';
$txt['app_raidmanager_cfg_setup_notes'] = 'Hinweise';
$txt['app_raidmanager_cfg_setup_notes_desc'] = 'Diese Hinweise werden bei der automatischen Raiderstellung für das erste Setup eingetragen.';
$txt['app_raidmanager_cfg_setup_tank'] = 'Tank';
$txt['app_raidmanager_cfg_setup_tank_desc'] = 'Anzahl an Tanks für neue Setups';
$txt['app_raidmanager_cfg_setup_damage'] = 'Schaden';
$txt['app_raidmanager_cfg_setup_damage_desc'] = 'Anzahl an Damagedealern für neue Setups';
$txt['app_raidmanager_cfg_setup_heal'] = 'Heiler';
$txt['app_raidmanager_cfg_setup_heal_desc'] = 'Anzahl an Heilern für neue Setups';

/* Group: Rotation */
$txt['app_raidmanager_cfg_group_rotation'] = 'Rotation';
$txt['app_raidmanager_cfg_rotation_away_multiplicator'] = 'Abwesenheitsmultiplikator';
$txt['app_raidmanager_cfg_rotation_away_multiplicator_desc'] = 'Wird bei der Berechnung der Rotation als multiplikator auf Setuppunkte angewendet.';
$txt['app_raidmanager_cfg_rotation_period'] = 'Zeitraum';
$txt['app_raidmanager_cfg_rotation_period_desc'] = 'Der für die Berechnung der Rotation zu berücksichtigende Zeitraum in Raidtagen.';

/* Group Stats */
$txt['app_raidmanager_cfg_group_stats'] = 'Statistik';
$txt['app_raidmanager_cfg_wanted_subindex'] = 'Anmeldequote';
$txt['app_raidmanager_cfg_wanted_subindex_desc'] = 'Wert der gewünschten Anmeldequote in Prozent. Dieser wird bei der Darstellung der Spielerstatisktik genutzt.';

/* Group: Calendar */
$txt['app_raidmanager_cfg_group_raidlist'] = 'Raidlisten';

/* Group: Forum */
$txt['app_raidmanager_cfg_group_forum'] = 'Forum';
$txt['app_raidmanager_cfg_use_forum'] = 'Topic erstellen';
$txt['app_raidmanager_cfg_use_forum_desc'] = 'Wenn aktiviert, dann wird für jeden neuen Raid ein Topic im ausgewählten Board gelegt.';
$txt['app_raidmanager_cfg_topic_board'] = 'Boardauswahl';
$txt['app_raidmanager_cfg_topic_board_desc'] = 'Das Board, in dem das Raidtopic erstellt werden soll. Wenn das gewünschte Board noch nich existiert, dann <a href="' . $scripturl .'?action=admin;area=manageboards;sa=main">hier anlegen</a> und die Konfiguration erneut aufrufen.';
$txt['app_raidmanager_cfg_topic_intro'] = 'Topicintro';
$txt['app_raidmanager_cfg_topic_intro_desc'] = 'Dieser Text wird als automatisches Intro für jedes neue Raidtopic eingefügt. Der Standartwert für dieses Feld ist in der jeweiligen Sprachdatei hinterlegt.';
$txt['app_raidmanager_raid_topic_text'] = 'Dieses Topic wurde als Basis für Diskussionen rund um den zugehörigen Raid angelegt';
$txt['app_raidmanager_cfg_use_calendar'] = 'Kalendereintrag';
$txt['app_raidmanager_cfg_use_calendar_desc'] = 'Wenn aktiviert, dann wird zusätzlich zum Topic ein Eintrag in den SMF Kalender vorgenommen. (setzt "Topic erstellen" voraus)';

// -------------------------------------------------------------------
// CLASSES
// -------------------------------------------------------------------

/* Warrior */
$txt['app_raidmanager_class_warrior'] = 'Krieger';
$txt['app_raidmanager_class_warrior_fury'] = 'Furor';
$txt['app_raidmanager_class_warrior_arms'] = 'Waffen';
$txt['app_raidmanager_class_warrior_protection'] = 'Schutz';

/* Paladin */
$txt['app_raidmanager_class_paladin'] = 'Paladin';
$txt['app_raidmanager_class_paladin_holy'] = 'Heilig';
$txt['app_raidmanager_class_paladin_protection'] = 'Schutz';
$txt['app_raidmanager_class_paladin_retribution'] = 'Vergeltung';

/* Druid */
$txt['app_raidmanager_class_druid'] = 'Druide';
$txt['app_raidmanager_class_druid_balance'] = 'Gleichgewicht';
$txt['app_raidmanager_class_druid_feral'] = 'Wilder Kampf';
$txt['app_raidmanager_class_druid_restoration'] = 'Wiederherstellung';

/* Hunter */
$txt['app_raidmanager_class_hunter'] = 'J&auml;ger';
$txt['app_raidmanager_class_hunter_beatmaster'] = 'Tierherrschaft';
$txt['app_raidmanager_class_hunter_marksman'] = 'Treffsicherheit';
$txt['app_raidmanager_class_hunter_survival'] = 'Überleben';

/* Warlock */
$txt['app_raidmanager_class_warlock'] = 'Hexenmeister';
$txt['app_raidmanager_class_warlock_demonology'] = 'D&auml;monologie';
$txt['app_raidmanager_class_warlock_affliction'] = 'Gebrechen  ';
$txt['app_raidmanager_class_warlock_destruction'] = 'Zerst&ouml;rung';

/* Mage */
$txt['app_raidmanager_class_mage'] = 'Magier';
$txt['app_raidmanager_class_mage_frost'] = 'Frost';
$txt['app_raidmanager_class_mage_fire'] = 'Feuer';
$txt['app_raidmanager_class_mage_arcane'] = 'Arkan';

/* Priest */
$txt['app_raidmanager_class_priest'] = 'Priester';
$txt['app_raidmanager_class_priest_discipline'] = 'Disziplin';
$txt['app_raidmanager_class_priest_holy'] = 'Heilig';
$txt['app_raidmanager_class_priest_shadow'] = 'Schatten';

/* Shaman */
$txt['app_raidmanager_class_shaman'] = 'Schamane';
$txt['app_raidmanager_class_shaman_elemental'] = 'Elementar';
$txt['app_raidmanager_class_shaman_enhancement'] = 'Verst&auml;rkung';
$txt['app_raidmanager_class_shaman_restoration'] = 'Wiederherstellung';

/* Rogue */
$txt['app_raidmanager_class_rogue'] = 'Schurke';
$txt['app_raidmanager_class_rogue_assassination'] = 'Meucheln';
$txt['app_raidmanager_class_rogue_combat'] = 'Kampf';
$txt['app_raidmanager_class_rogue_subtlety'] = 'T&auml;uschung';

/* Deathknight */
$txt['app_raidmanager_class_deathknight'] = 'Todesritter';
$txt['app_raidmanager_class_deathknight_blood'] = 'Blut';
$txt['app_raidmanager_class_deathknight_unholy'] = 'Unheilig';
$txt['app_raidmanager_class_deathknight_frost'] = 'Frost';

/* Monk */
$txt['app_raidmanager_class_monk'] = 'M&ouml;nch';
$txt['app_raidmanager_class_monk_windwaker'] = 'Windwalker';
$txt['app_raidmanager_class_monk_brewmaster'] = 'Brewmaster';
$txt['app_raidmanager_class_monk_mistweaver'] = 'Mistweaver';

// -------------------------------------------------------
// ERRORS
// -------------------------------------------------------
$txt['app_raidmanager_raid_start_after_end'] = 'Der Raidstart darf nicht nach dem Raidende liegen.';
$txt['app_raidmanager_raid_end_before_start'] = 'Das Raidende darf nicht vor dem Raidstart liegen.';
?>