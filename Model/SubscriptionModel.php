<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use	Web\Framework\Lib\Error;
use Web\Framework\Lib\User;
use Web\Framework\Lib\Data;
use Web\Framework\Html\Controls\UiButton;

// Check for direct file access
if ( !defined('WEB'))
    die('Cannot run without WebExt framework...');

/**
 * Subscription Model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package Raidmanager
 * @subpackage Model
 * @license BSD
 * @copyright 2014 by author
 */
class SubscriptionModel extends Model
{
	public $tbl = 'app_raidmanager_subscriptions';
	public $alias = 'subs';
	public $pk = 'id_subscription';

	public $validate = array(
		'msg' => array(
			'empty',
			array('range', array(10,100))
		)
	);

	/**
	 * Returns the enrollstate of the current player on a specified raid
	 *
	 * @param int $id_raid
	 * <p>ID of the raid to check for enrollstate</p>
	 */
	public function getEnrollstateOnRaid($id_raid, $id_player)
	{
		$this->setField('state');
		$this->setFilter('id_raid={int:id_raid} AND id_player={int:id_player}');
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'id_player' => $id_player
		));
		return $this->read('val');
	}

	public function createSubscriptionForRaid($id_raid, $autosignon)
	{
		// load active players => state=3
		$players = $this->getModel('Player')->getPlayerByState(3);

		foreach($players as $player)
		{
			$data = new Data();

			$data->id_player = $player->id_player;
			$data->id_raid = $id_raid;

			// ------------------------------------
			// different states for different
			// combinations of signon flags
			// ------------------------------------

			// autosign on active but player is not on autosignon
			if ($autosignon == 1 && $player->autosignon == 0)
				$data->state = 0; # state without ajax

			// autosign on active and player is on autosignon
			if ($autosignon == 1 && $player->autosignon == 1)
				$data->state = 1; # state enrolled

			// autosign on inactive
			if ($autosignon == 0)
				$data->state = 0; # state for all on no ajax

			$this->setData($data)->save();
		}
	}

	/**
	 * Deletes all subscritions of a specific raid
	 *
	 * @param int $id
	 */
	public function deleteByRaid($id_raid)
	{
		$this->setFilter('id_raid={int:id_raid}');
		$this->setParameter('id_raid', $id_raid);
		$this->delete();
	}

	/**
	 * Deletes a specific subscription
	 *
	 * @param int $id
	 */
	public function deleteSubscriptionByID($id)
	{
		$this->setFilter('id_subscription={int:id_subscription}');
		$this->setParameter('id_subscription', $id);
		$this->delete();
	}

	/**
	 * Deletes all subscrition for one specific player
	 *
	 * @param int $id
	 */
	public function deleteSubscriptionByPlayer($id_player)
	{
		$this->setFilter('id_player={int:id_player}');
		$this->setParameter('id_player', $id_player);
		$this->delete();
	}


	public function getBySubsstate($id_raid, $state)
	{
		$this->setField(array(
			'subs.id_player',
			'chars.char_name',
			'cats.category',
			'class.class',
			'class.color'
		));
		$this->setJoin('app_raidmanager_chars', 'chars', 'INNER', 'subs.id_player=chars.id_player');
		$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');
		$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');
		$this->setFilter('subs.state={int:state} AND subs.id_raid={int:id_raid} AND chars.is_main=1');
		$this->setOrder('chars.char_name');
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'state' => $state
		));

		return $this->read('*');
	}

	public function getEditSubscriptions($id_raid, $type)
	{
		// define the fields we want to read from model
		$this->setField(array(
			'subs.id_raid',
			'subs.id_subscription',
			'subs.id_player',
			'subs.state',
			'chars.char_name',
			'cats.category',
			'class.class',
			'class.color'
		));

		// our following queries need the same joins. so we add it to model.
		$this->setJoin('app_raidmanager_chars', 'chars', 'INNER', 'subs.id_player=chars.id_player');
		$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');
		$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');

		// set order on char names
		$this->setOrder('chars.char_name');

		#$filter = 'subs.id_raid={int:id_raid} AND chars.is_main=1 AND subs.id_player<>{int:id_player} AND subs.state ';
		$filter = 'subs.id_raid={int:id_raid} AND chars.is_main=1 AND subs.state ';

		switch($type)
		{
			case 'resigned' :
				// get player who are away or without any ajax
				$filter .= 'IN(0,2)';
				$btn_type = 'btn-success';
				$icon = 'smile-o';
				$title = 'raidmanager_comment_enroll';
				$state = 1;
				break;

			case 'enrolled' :
				// get the lisst of enrolled player
				$filter .= '=1';
				$btn_type = 'btn-danger';
				$icon = 'frown-o';
				$title = 'raidmanager_comment_resign';
				$state = 2;
				break;

			default :
				Throw new Error('Raidmanager: The given subscriptiontype is wrong. Set it to "enrolled" or "resigned"');
		}

		$this->setFilter($filter);
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'id_player' => User::getId()
		));

		return $this->read('*', 'createSubsButton');
	}

	public function createSubsButton($player)
	{
		// editing user is not allowed to change his own state here.
		#if ($player->id_player == User::getId())
			#return false;

		switch($player->state)
		{
			case 0:
			case 2:
				// get player who are away or without any ajax
				$btn_type = 'btn-success';
				$icon = 'smile-o';
				$title = 'raidmanager_comment_enroll';
				$state = 1;
				break;

			case 1:
				// get the lisst of enrolled player
				$btn_type = 'btn-danger';
				$icon = 'frown-o';
				$title = 'raidmanager_comment_resign';
				$state = 2;
				break;

			default :
				Throw new Error('Raidmanager: The given subscriptiontype is wrong. Set it to "enrolled" or "resigned"');
		}

		// build enrollbuttons
		$button = UiButton::factory('ajax', 'imgbutton');
		$button->setIcon($icon);
		$button->setText($player->char_name);
		$button->setTitle($this->txt($title));

		$button->addCss('btn-block');
		$button->addCss($btn_type);

		$button->setRoute(
			'raidmanager_subscription_enrollform',
			array(
				'state' => $state,
				'from' => 'subscription',
				'id_raid' => $player->id_raid,
				'id_player' => $player->id_player,
				'id_subscription' => $player->id_subscription
			)
		);

		$button->setTarget('raidmanager_subscriptions');

		$player->link = $button->build();

		return $player;
	}

	/**
	 * Save the enrollform in which the subscriptionstate can be changed and comments are stored in DB
	 */
	public function saveEnrollform($data)
	{
		$this->setData($data);

		// normal users onl can changer their own subscription state. we need to prevent users from changing other
		// users state. if user is different then the player of the subscription to change and the user lacks needed
		// permissions, override the subscriptions player id with the id of the user.
		// admin can change the subscriptionstate for all player
		if ($this->data->id_player != User::getId() && $this->checkAccess('raidmanager_perm_subs') === false)
			$this->data->id_player = User::getId();

		// state 1 stands for enroll and 2 for resign. both stands for an subscription action to save
		if($this->data->state !=0)
		{
			$this->save();

			// on resign, the players chars need to be removed
			// from possible setlists of this raid
			if ($this->data->state == 2)
				$this->getModel('Setlist')->removePlayerByRaid($this->data->id_raid, $this->data->id_player);
		}

		// Create comment if the subscription save is without error
		if (!$this->hasErrors())
		{
			// extend data to fit as comment
			$this->data->id_poster = User::getId();
			$this->data->stamp = time();

			// create comment
			$comment_model = $this->getModel('Comment');
			$comment_model->createComment($this->data);

			if ($comment_model->hasErrors())
			{
				$comment_errors = $comment_model->getErrors();

				foreach ($comment_errors as $fld => $msg)
					$this->addError($fld, $msg);
			}
		}
	}

	/**
	 * Change substate of player
	 * @param int $id_subscription
	 * @param int $state 	1=enrolled | 2=resigned
	 */
	private function changePlayerSubscription($id_subscription, $state)
	{
		$model = $this->getModel();
		$model->data->id_subscription = $id_subscription;
		$model->data->state = $state;
		$model->save();
	}

	public function getIdAndState($id_raid, $id_player)
	{
		$this->setField(array(
			'id_subscription',
			'state'
		));
		$this->setFilter('id_raid={int:id_raid} AND id_player={int:id_player}');
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'id_player' => $id_player
		));
		return $this->read();
	}

	public function getRaidId($id_subscription)
	{
		$this->setField('id_raid');
		$this->setFilter('id_subscription={int:id_subscription}');
		$this->setParameter('id_subscription', $id_subscription);
		return $this->read('val');
	}

	/**
	 * Add player to subscription table
	 * The signonstate is related to the parameter this method gets and the state of the raid itself.
	 * Only if both are true e.g. 1 the player will be flagged as subscribed. Otherwise the player will
	 * be flagges to no ajax (1)
	 *
	 * @param int $id_player
	 * @param int $state (0|1)
	 */
	public function addPlayerToFutureSubs($id_player, $state)
	{
		// get future raids
		$raids = $this->getModel('Raid')->getFutureRaidIDsAndAutosignon();

		// create data for each raid
		foreach ($raids as $raid)
		{
			// prepare data container
			$sub = new Data();

			// get raid id from raid record
			$sub->id_raid = $raid->id_raid;

			// set player id
			$sub->id_player = $id_player;

			// set signon state according to autosignon of raid and autosignon state of player
			$sub->state = $raid->autosignon==1 && $state==1 ? 1 : 0;

			// add data container to model and save
			$this->setData($sub)->save(false);
		}
	}

	public function deletePlayerFromFutureRaidSub($id_player)
	{
		$this->setFilter('id_raid IN ({array_int:raids}) AND id_player={int:id_player}');
		$this->setParameter(array(
			'raids' => $this->getModel('Raid')->getFutureRaidIDs(),
			'id_player' => $id_player
		));
		$this->delete();
	}

	public function setPlayerStateOnFutureSubs($id_player, $state)
	{
		$this->setField('state');
		$this->setFilter('subs.id_raid IN({array_int:raids}) AND subs.id_player={int:id_player}');
		$this->setParameter(array(
			'raids' => $this->getModel('Raid')->getFutureRaidIDs(),
			'id_player' => $id_player,
			'state' => $state
		));
		$this->update();
	}

	public function countEnrolledPlayers($id_raid)
	{
		$this->setField('Count(id_player)');
		$this->setFilter('id_raid={int:id_raid} AND state=1', array('id_raid' => $id_raid));
		return $this->read('num');
	}
}
?>