<?php

namespace Web\Apps\Raidmanager;

/**
 * Main app class of Raidmanager app
 * @author Michael Zorn (tekkla@tekkla.de)
 * @copyright 2014
 */

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

// Used classes
use Web\Framework\Lib\Url;
use Web\Framework\Lib\App;
use Web\Framework\Lib\User;

final class Raidmanager extends App
{
    // Has it's own css file
    public $css = true;

    // Has it's own js file
    public $js = 'scripts';

    // Has a languagefile
    public $lang = true;

    // Show the user that there is nothing to show
    public $output = true;

    /**
     * Config definiton for this app
     *
     * This structure defines what keys will be written to config fw config table on install, which keys have to be
     * added or removed on an app upadte. Un uninstall all keys will be removed.
     *
     * The sorting of this array also defines the display in the config form
     *
     * config_key = array(
     *      group => name of displaygroup
     *      default => default value
     *      control => type of control to use or array(type of control to use, array(attribname => value))
     *      validate => rulename or array(rule, array(function to call, array(params)))
     *      data => array(mode, datasource)
     * )
     *
     * Datasources can be of type array(key0=>val0, key1=>val1 ...) or NameOfApp::ModelName::ModelFunction as model source
     *
     * @var array
     */
    public $config = array(

        // group: raid
        'raid_destination' => array(
            'group' => 'raid',
            'default' => 'New raid',
            'control' => array(
                'text',
                array(
                    'size' => 50
                )
            ),
            'validate' => array(
                'required',
                'empty'
            )
        ),
        'raid_specials' => array(
            'group' => 'raid',
            'default' => '',
            'control' => array(
                'textarea',
                array(
                    'cols' => 50,
                    'rows' => 5
                )
            )
        ),
        'raid_autosignon' => array(
            'group' => 'raid',
            'default' => 1,
            'control' => 'switch'
        ),
        'raid_weekday_start' => array(
            'group' => 'raid',
            'default' => 3,
            'control' => array(
                'number',
                array(
                    'min' => 0,
                    'max' => 6
                )
            ),
            'validate' => array(
                'required',
                'int',
                array(
                    'range',
                    array(
                        0,
                        6
                    )
                )
            )
        ),
        'raid_new_days_ahead' => array(
            'group' => 'raid',
            'default' => 5,
            'control' => array(
                'number',
                array(
                    'min' => 1
                )
            ),
            'validate' => array(
                'required',
                array(
                    'min',
                    1
                )
            )
        ),
        'raid_days' => array(
            'group' => 'raid',
            'control' => 'optiongroup',
            'data' => array(
                'model',
                'Raidmanager::Calendar::getDays'
            )
        ),
        'raid_time_start' => array(
            'group' => 'raid',
            'default' => '20:15',
            'control' => 'time-24',
            'validate' => array(
                'required',
                'time24'
            )
        ),
        'raid_duration' => array(
            'group' => 'raid',
            'default' => 180,
            'control' => array(
                'number',
                array(
                    'min' => 1,
                    'max' => 1440
                )
            ),
            'validate' => array(
                'required',
                'int',
                array(
                    'min',
                    1
                )
            )
        ),

        // group: setup
        'setup_title' => array(
            'group' => 'setup',
            'default' => 'Autosetup',
            'control' => array(
                'text',
                array(
                    'size' => 50
                )
            ),
            'validate' => array(
                'required',
                'empty'
            )
        ),
        'setup_notes' => array(
            'group' => 'setup',
            'default' => null,
            'control' => array(
                'textarea',
                array(
                    'rows' => 5,
                    'cols' => 50
                )
            )
        ),
        'setup_tank' => array(
            'group' => 'setup',
            'default' => 2,
            'control' => array(
                'number',
                array(
                    'min' => 0,
                    'max' => 100,
                    'size' => 4
                )
            ),
            'validate' => array(
                'blank',
                'int',
                array(
                    'range',
                    array(
                        0,
                        100
                    )
                )
            )
        ),
        'setup_damage' => array(
            'group' => 'setup',
            'default' => 6,
            'control' => array(
                'number',
                array(
                    'min' => 0,
                    'max' => 100,
                    'size' => 4
                )
            ),
            'validate' => array(
                'blank',
                'int',
                array(
                    'range',
                    array(
                        0,
                        100
                    )
                )
            )
        ),
        'setup_heal' => array(
            'group' => 'setup',
            'default' => 2,
            'control' => array(
                'number',
                array(
                    'min' => 0,
                    'max' => 100,
                    'size' => 4
                )
            ),
            'validate' => array(
                'blank',
                'int',
                array(
                    'range',
                    array(
                        0,
                        100
                    )
                )
            )
        ),

        // group calendar
        'num_list_future_raids' => array(
            'group' => 'raidlist',
            'default' => 10,
            'control' => array(
                'number',
                array(
                    'min' => 1,
                    'max' => 30
                )
            ),
            'validate' => array(
                'required',
                array(
                    'range',
                    array(
                        1,
                        30
                    )
                )
            ),
            'open' => true
        ),
        'num_list_recent_raids' => array(
            'group' => 'raidlist',
            'default' => 10,
            'control' => array(
                'number',
                array(
                    'min' => 1,
                    'max' => 30
                )
            ),
            'validate' => array(
                'required',
                array(
                    'range',
                    array(
                        1,
                        30
                    )
                )
            ),
            'open' => true
        ),

        // forum topics
        'use_forum' => array(
            'group' => 'forum',
            'default' => 0,
            'control' => 'switch'
        ),
        'topic_board' => array(
            'group' => 'forum',
            'control' => 'select',
            'data' => array(
                'model',
                'Forum::Board::getBoardlist'
            )
        ),
        'topic_intro' => array(
            'group' => 'forum',
            'control' => array(
                'textarea',
                array(
                    'cols' => 50,
                    'rows' => 5
                )
            )
        ),
        'use_calendar' => array(
            'group' => 'forum',
            'default' => 0,
            'control' => 'switch'
        )
    );

    // Permissions
    public $perms = array(
        'perm' => array(
            'config',  // access to config
            'raid',  // manage raidinfos
            'subs',  // manage player subscriptions
            'setup',  // manage setup infos
            'setlist',  // manage setlists
            'player',  // manage player roster
            'stats',  // see stats
            'profiles' // see all profiles
                )
    );

    // Used routes
    public $routes = array(
        array(
            'name' => 'raid_index',
            'route' => '/?',
            'ctrl' => 'raid',
            'action' => 'complete'
        ),
        array(
            'name' => 'raid_start',
            'route' => '/raid',
            'ctrl' => 'raid',
            'action' => 'complete'
        ),
        array(
            'name' => 'raid_selected',
            'route' => '/raid/[i:id_raid]',
            'ctrl' => 'raid',
            'action' => 'complete'
        ),
        array(
            'name' => 'raid_data',
            'route' => '/raid/index/[i:id_raid]',
            'ctrl' => 'raid',
            'action' => 'index'
        ),
        array(
            'name' => 'raid_add',
            'method' => 'GET|POST',
            'route' => '/raid/add/[i:back_to]?',
            'ctrl' => 'raid',
            'action' => 'edit'
        ),
        array(
            'name' => 'raid_edit',
            'method' => 'GET|POST',
            'route' => '/raid/edit/[i:id_raid]/[i:back_to]',
            'ctrl' => 'raid',
            'action' => 'edit'
        ),
        array(
            'name' => 'raid_infos',
            'route' => '/raid/infos/[i:id_raid]',
            'ctrl' => 'raid',
            'action' => 'infos'
        ),
        array(
            'name' => 'raid_autoadd',
            'route' => '/raid/autoadd',
            'ctrl' => 'raid',
            'action' => 'autoadd'
        ),
        array(
            'name' => 'raid_delete',
            'route' => '/raid/delete/[i:id_raid]',
            'ctrl' => 'raid',
            'action' => 'delete'
        ),
        array(
            'name' => 'subscription_index',
            'route' => '/raid/subscription/[i:id_raid]',
            'ctrl' => 'subscription',
            'action' => 'index'
        ),
        array(
            'name' => 'subscription_edit',
            'route' => '/raid/subscription/edit/[i:id_raid]',
            'ctrl' => 'subscription',
            'action' => 'edit'
        ),
        array(
            'name' => 'subscription_enrollform',
            'method' => 'GET|POST',
            'route' => '/raid/subscription/enrollform/[i:id_raid]/[i:id_subscription]/[i:id_player]/[i:state]/[a:from]',
            'ctrl' => 'subscription',
            'action' => 'enrollform'
        ),
        array(
            'name' => 'subscription_save',
            'method' => 'POST',
            'route' => '/raid/subscription/save/[a:from]/[i:id_raid]',
            'ctrl' => 'subscription',
            'action' => 'save'
        ),
        array(
            'name' => 'comment_index',
            'route' => '/raid/comment/index/[i:id_raid]',
            'ctrl' => 'comment',
            'action' => 'index'
        ),
        array(
            'name' => 'comment_delete',
            'route' => '/raid/comment/delete/[i:id_raid]/[i:id_comment]',
            'ctrl' => 'comment',
            'action' => 'delete'
        ),
        array(
            'name' => 'setup_index',
            'route' => '/raid/setup/index/[i:id_setup]',
            'ctrl' => 'setup',
            'action' => 'index'
        ),
        array(
            'name' => 'setup_complete',
            'route' => '/raid/setup/complete/[i:id_raid]',
            'ctrl' => 'setup',
            'action' => 'complete'
        ),
        array(
            'name' => 'setup_add',
            'method' => 'GET|POST',
            'route' => '/raid/setup/add/[i:id_raid]/[i:back_to]',
            'ctrl' => 'setup',
            'action' => 'edit'
        ),
        array(
            'name' => 'setup_edit',
            'method' => 'GET|POST',
            'route' => '/raid/setup/edit/[i:id_setup]/[i:id_raid]/[i:back_to]',
            'ctrl' => 'setup',
            'action' => 'edit'
        ),
        array(
            'name' => 'setup_save',
            'method' => 'POST',
            'route' => '/raid/setup/save/[i:id_raid]/[i:back_to]',
            'ctrl' => 'setup',
            'action' => 'save'
        ),
        array(
            'name' => 'setup_delete',
            'route' => '/raid/setup/delete/[i:id_setup]/[i:id_raid]',
            'ctrl' => 'setup',
            'action' => 'delete'
        ),
        array(
            'name' => 'setlist_edit',
            'route' => '/raid/setlist/edit/[i:id_raid]/[i:id_setup]',
            'ctrl' => 'setlist',
            'action' => 'edit'
        ),
        array(
            'name' => 'setlist_set',
            'route' => '/raid/setlist/set/[i:id_setup]/[i:id_char]/[i:id_category]',
            'ctrl' => 'setlist',
            'action' => 'set_player'
        ),
        array(
            'name' => 'setlist_switch',
            'route' => '/raid/setlist/switch/[i:id_setlist]/[i:id_category]',
            'ctrl' => 'setlist',
            'action' => 'switch_player'
        ),
        array(
            'name' => 'setlist_unset',
            'route' => '/raid/setlist/unset/[i:id_setlist]',
            'ctrl' => 'setlist',
            'action' => 'unset_player'
        ),
        array(
            'name' => 'setlist_save',
            'route' => '/raid/setlist/save/[i:id_setup]/[i:id_char]/[i:id_player]/[i:id_setlist]/[i:set_as]/[i:set_from]',
            'ctrl' => 'setlist',
            'action' => 'save'
        ),
        array(
            'name' => 'player_start',
            'route' => '/player',
            'ctrl' => 'player',
            'action' => 'complete'
        ),
        array(
            'name' => 'player_index',
            'route' => '/player/[i:id_player]',
            'ctrl' => 'player',
            'action' => 'index'
        ),
        array(
            'name' => 'player_edit',
            'method' => 'GET|POST',
            'route' => '/player/edit/[i:id_player]',
            'ctrl' => 'player',
            'action' => 'edit'
        ),
        array(
            'name' => 'player_delete',
            'route' => '/player/delete/[i:id_player]',
            'ctrl' => 'player',
            'action' => 'delete'
        ),
        array(
            'name' => 'player_add',
            'method' => 'POST',
            'route' => '/player/add',
            'ctrl' => 'player',
            'action' => 'create'
        ),
        array(
            'name' => 'char_list',
            'route' => '/charlist/[i:id_player]',
            'ctrl' => 'char',
            'action' => 'charlist'
        ),
        array(
            'name' => 'char_add',
            'method' => 'GET|POST',
            'route' => '/char/add/[i:id_player]',
            'ctrl' => 'char',
            'action' => 'edit'
        ),
        array(
            'name' => 'char_edit',
            'method' => 'GET|POST',
            'route' => '/char/edit/[i:id_player]/[i:id_char]?',
            'ctrl' => 'char',
            'action' => 'edit'
        ),
        array(
            'name' => 'char_delete',
            'route' => '/char/delete/[i:id_char]/[i:id_player]',
            'ctrl' => 'char',
            'action' => 'delete'
        ),
        array(
            'name' => 'stats',
            'route' => '/stats',
            'ctrl' => 'stats',
            'action' => 'index'
        ),
        array(
            'name' => 'stats_subs',
            'route' => '/stats/subs/[i:month]/[i:year]',
            'ctrl' => 'stats',
            'action' => 'subs'
        ),
        array(
            'name' => 'stats_player',
            'route' => '/stats/player/[i:month]/[i:year]',
            'ctrl' => 'stats',
            'action' => 'player'
        ),
        array(
            'name' => 'reset',
            'route' => '/reset',
            'ctrl' => 'raid',
            'action' => 'reset'
        )
    );

    /**
     * To show at content start
     */
    public function onBefore()
    {
        $html = '
		<h1>Raidmanager</h1>
		<div id="raidmanager" class="row">';

        return $html;
    }

    /**
     * To show at content end
     * @return string
     */
    public function onAfter()
    {
        $html = '
		</div>';

        return $html;
    }

    /**
     * To shown when nothing is to show
     * @return string
     */
    public function onEmpty()
    {
        return '<div class="grid_12">' . $this->txt('no_content') . '</div>';
    }

    /*
     * Creates the arrayelements of Raidmanager menu.
     */
    public function addMenuButtons(&$menu_buttons)
    {
        $buttons = array();

        $buttons['raidmanager_raid_head'] = array(
            'title' => $this->txt('raids'),
            'show' => true,
            'href' => Url::factory('raidmanager_raid_start')->getUrl(),
            'sub_buttons' => $this->getModel('Calendar')->getMenu()
        );

        // add rest of buttons
        $buttons += array(
            'raidmanager_stats' => array(
                'title' => $this->txt('stats_headline'),
                'href' => Url::factory('raidmanager_stats')->getUrl(),
                'show' => true,
                'sub_buttons' => array()
            ),
            'raidmanager_playerlist' => array(
                'title' => $this->txt('playerlist'),
                'href' => Url::factory('raidmanager_player_start')->getUrl(),
                'show' => allowedTo('raidmanager_perm_player'),
                'sub_buttons' => array()
            ),
            'raidmanager_config' => array(
                'title' => $this->txt('web_config'),
                'href' => Url::factory('admin_app_config')->addParameter('app_name', 'raidmanager')->getUrl(),
                'show' => allowedTo('raidmanager_perm_config'),
                'sub_buttons' => array()
            )
        );

        $menu_buttons['raidmanager'] = array(
            'title' => 'Raidmanager',
            'href' => '#',
            'show' => $this->generalAccess(),
            'sub_buttons' => $buttons,
            'noslice' => true
        );
    }

    /**
     * Raidmanger specific method to check genereal access on raidmanager.
     * This method checks for active chars of an user. Without a char won't
     * see the raidmanager.
     * @return bool
     */
    public function generalAccess()
    {
        // User logged in?
        if (User::isLogged())
        {
            // If user is an admin => grant access
            if (User::isAdmin())
                return true;

            // All other will be checked for existing playerprofile
            $model = $this->getModel('Player');
            $model->addField('players.state');
            $model->setFilter('players.id_player={int:id_user}');
            $model->addParameter('id_user', User::getId());
            $model->read('val');

            // Access only on player state 3 (active)
            if ($model->data == 3)
                return true;
        }

        return false;
    }
}
?>