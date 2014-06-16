<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\User;
use Web\Framework\Lib\Txt;
use Web\Framework\Lib\Data;
use Web\Framework\Html\Controls\UiButton;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Calendar Model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package Raidmanager
 * @subpackage Model
 * @license BSD
 * @copyright 2014 by author
 */
final class CalendarModel extends Model
{
    protected $tbl = 'app_raidmanager_raids';
    protected $alias = 'raids';
    protected $pk = 'id_raid';

    public function getCalendar($id_raid)
    {
        $query = array(
        	'type' => '*',
            'field' => array(
                'raids.id_raid',
                'raids.destination',
                'raids.starttime',
                'subs.state'
            ),
            'join' => array(
                array('app_raidmanager_subscriptions', 'subs', 'INNER', 'raids.id_raid=subs.id_raid', true)
            ),
            'param' => array(
                'starttime' => time(),
                'id_player' => User::getId()
            ),
        );

        $out = new Data();

        // get future raids
        $query['filter'] = '(raids.starttime>{int:starttime} OR {int:starttime} BETWEEN raids.starttime AND raids.endtime) AND raids.deleted=0 AND subs.id_player={int:id_player}';
        $query['order'] = 'raids.starttime';
        $query['limit'] = $this->cfg('num_list_future_raids');
        $out->future = $this->buildRaidlistLinks($this->read($query), $id_raid);

        // get recent raids
        $query['filter'] = 'raids.endtime<{int:starttime} AND raids.deleted=0 AND subs.id_player={int:id_player}';
        $query['order'] = 'raids.starttime DESC';
        $query['limit'] = $this->cfg('num_list_recent_raids');
        $out->recent = $this->buildRaidlistLinks($this->read($query), $id_raid);

        return $out;
    }

    public function nextRaid()
    {
        $this->read(array(
        	'field' => array(
        	    'raids.id_raid',
        	    'raids.destination',
        	    'raids.starttime',
        	    'subs.state'
        	),
            'join' => array(
        		array('app_raidmanager_subscriptions', 'subs', 'INNER', 'raids.id_raid=subs.id_raid')
        	),
            'filter' => '(raids.starttime>{int:starttime} OR {int:starttime} BETWEEN raids.starttime AND raids.endtime) AND raids.deleted=0 AND subs.id_player={int:id_player}',
            'param' => array(
                'starttime' => time(),
                'id_player' => User::getId()
            ),
            'order' => 'raids.starttime',
            'limit' => 1
        ));

        if (!$this->hasData())
            return false;

        // Add number of enrolled players
        $this->data->players = $this->getModel('Subscription')->countEnrolledPlayers($this->data->id_raid);

        // Add url to this raid
        $this->data->url = Url::factory('raidmanager_raid_selected', array('id_raid' => $this->data->id_raid))->getUrl();

        return $this->data;
    }

    public function getMenu()
    {
        $this->read(array(
            'type' => '*',
            'field' => array(
                'raids.id_raid',
                'raids.destination',
                'raids.starttime',
                'subs.state'
            ),
            'join' => array(
                array('app_raidmanager_subscriptions', 'subs', 'INNER', 'raids.id_raid=subs.id_raid')
            ),
            'filter' => '(raids.starttime>{int:starttime} OR {int:starttime} BETWEEN raids.starttime AND raids.endtime) AND raids.deleted=0 AND subs.id_player={int:id_player}',
            'param' => array(
                'starttime' => time(),
                'id_player' => User::getId()
            ),
            'order' => 'raids.starttime',
            'limit' => $this->cfg('num_list_recent_raids')
        ));

        // No data mean we have to offer a link for raid creation
        if ($this->hasNoData())
            return false;

        // Still here? Ok. Seems that we have data to create menulinks.
        $menu_buttons = array();

        foreach ( $this->data as $raid )
        {
            $menu_buttons['raidmanager_menu_raid_' . $raid->id_raid] = array(
                'title' => '<span class="small">' . date('Y-m-d H:i', $raid->starttime) . '</span> ' . $raid->destination . ' <span class="badge">' . $this->app->getModel('Subscription')->countEnrolledPlayers($raid->id_raid) . '</span>',
                'href' => Url::factory('raidmanager_raid_selected', array('id_raid' => $raid->id_raid))->getUrl(),
                'show' => true,
                'sub_buttons' => array()
            );
        }

        return $menu_buttons;
    }

    private function buildRaidlistLinks($raidlist, $id_raid = null)
    {
        $buttons = array();

        if (!$raidlist)
            return $buttons;

        foreach ( $raidlist as $raid )
        {
            $button = UiButton::factory('ajax', 'link');

            // special css class for the current raid
            if ($id_raid == $raid->id_raid)
                $button->addCss('raidmanager_current');

                // set classes for viewing player enrollstate
            switch ($raid->state)
            {
                case 0 :
                    $css = 'text-warning';
                    $state = 'noajax';
                    break;
                case 1 :
                    $css = 'text-success';
                    $state = 'enrolled';
                    break;
                case 2 :
                    $css = 'text-danger';
                    $state = 'resigned';
                    break;
                case 3 :
                    $css = 'text-success';
                    $state = 'enrolled';
                    break;
            }

            $button->addCss('app-raidmanager-subscription-' . $state);
            $button->setTitle('raidmanager_raid_subscriptionstate_' . $state);

            // build link
            $button->url->setNamedRoute('raidmanager_raid_data')->setTarget('raidmanager_raid')->addParameter('id_raid', $raid->id_raid);

            // count subscribed players for this raid
            $num_enrolled = $this->app->getModel('Subscription')->countEnrolledPlayers($raid->id_raid);

            // build the link text with raid starttime an raid destination
            $txt = '<span class="app-raidmanager-calendar-raid ' . $css . '">' . date('Y-m-d, H:i', $raid->starttime) . ' - ' . $raid->destination . '  <span class="badge">' . $num_enrolled . '</span>';
            $button->setText($txt);

            $buttons[] = $button->build();
        }

        return $buttons;
    }

    public function getDays()
    {
        $days = Txt::get('days', 'SMF');

        return array(
            0 => $days[0],
            1 => $days[1],
            2 => $days[2],
            3 => $days[3],
            4 => $days[4],
            5 => $days[5],
            6 => $days[6]
        );
    }
}
?>
