<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

use Web\Framework\Html\Controls\Actionbar;


class PlayerModel extends Model
{
	public $tbl = 'app_raidmanager_players';
	public $alias = 'players';
	public $pk = 'id_player';

	public $validate = array(
		'id_player' => array('required', 'empty'),
		'autosignon' => array('required', 'blank'),
		'org_autosignon' => array('required', 'blank'),
		'state' => array('required', 'blank'),
		'org_state' => array('required', 'blank')
	);

	public function getPlayer($id_player, $use_actionbar = true)
	{
		$this->setField(array(
			'players.id_player',
			'players.autosignon',
			'players.state',
			'chars.char_name',
			'chars.id_category',
			'class.class',
			'class.color',
			'class.css',
			'cats.category'
		));

		$this->setJoin('app_raidmanager_chars', 'chars', 'INNER', 'players.id_player=chars.id_player');
		$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');
		$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');

		$this->setFilter('chars.is_main=1 AND players.id_player={int:id_player}');
		$this->setParameter('id_player', $id_player);

		$callbacks = array('extendPlayer');

		if ($use_actionbar)
			$callbacks[] = 'addActionbar';

		return $this->read('row', $callbacks);
	}

	public function getPlayerByState($state)
	{
		$this->setFilter('state={int:state}');
		$this->setParameter('state', $state);
		return $this->read('*');
	}

	public function getPlayerlist($type)
	{
		$types = array(
			'old' => 0,
			'applicant' => 1,
			'inactive' => 2,
			'active' => 3,
		);

		$this->setField(array(
			'players.id_player',
			'players.autosignon',
			'chars.char_name',
			'chars.id_category',
			'class.class',
			'class.color',
			'class.css',
			'cats.category'
		));

		$this->setJoin('app_raidmanager_chars', 'chars', 'INNER', 'players.id_player=chars.id_player');
		$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');
		$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');

		$this->setFilter('chars.is_main=1 AND state={int:state}');
		$this->setParameter('state', $types[$type]);

		$this->setOrder('chars.char_name');

		return $this->read('*', array('extendPlayer', 'addActionbar'));
	}

	/**
	 * Used for read callbacks to add some statusinfos to the playerrecord
	 */
	public function extendPlayer($player)
	{
		// a visual x if player is on autosignon
		$player->x_on_autosignon = $player->autosignon == 1 ? '<i class="fa fa-calendar fa-fixed-width" title="' . $this->txt('raid_autosignon') . '"></i> ' : '';

		// translated category name
		$categories = array(
			1 => array('tank', 'shield'),
			2 => array('damage' , 'rocket'),
			3 => array('heal', 'medkit'),
		);

		$title = $this->txt('category_' . $categories[$player->id_category][0]);

		$player->category = '<i class="fa fa-' . $categories[$player->id_category][1] . ' fa-fixed-width" title="' . $title . '"></i>';

		return $player;

	}

	/**
	 * Used for read callbacks to add actionbars to a playerrecord.
	 */
	public function addActionbar($player)
	{
		if ($this->checkAccess('raidmanager_perm_player'))
		{
			$actionbar = new Actionbar();

			// the edit button
			$actionbar->createButton('edit', 'ajax', 'icon')
					  ->setRoute('raidmanager_player_edit', array('id_player' => $player->id_player));

			// the delete button
			$actionbar->createButton('delete', 'ajax', 'icon')
					  ->setRoute('raidmanager_player_delete',array('id_player' => $player->id_player));

			$player->actionbar = $actionbar->build();
		}

		return $player;
	}

	public function deletePlayer($id_player)
	{

		// define filter and parameter for multiple deletes
		$filter = 'id_player={int:id_player}';
		$params = array(
				'id_player' => $id_player
		);

		// delete player data from this tables
		$models = array(
			'Setlist',
			'Comment',
			'Subscription',
			'Char',
		);

		foreach($models as $model_name)
			$this->getModel($model_name)->setFilter($filter, $params)->delete();

		// and finally delete the player self
		$this->delete($id_player);
	}

	/**
	 * Updates an existing player by using the data send from controller. This method
	 * takes care of state or autosignon changes and alters subscriptions or playerlists
	 * accordingly.
	 * @param Data $data Data send from controller
	 */
	public function savePlayer($data)
	{
		// Attach data to model
		$this->setData($data);

		// Validate userinput
		$this->validate();

		// Any error?
		if ($this->hasErrors())
			return;

		// Init status flags
		$playerstate_changed = false;
		$signup_done = false;
		$autosignon_changed = false;

		// Autosign on can only be 1 or 0. All other then 0 is 1!
		if($this->data->autosignon != 0)
			$this->data->autosignon = 1;

		// Check 1: Did playerstate changed?
		// If a player state changed, it is important to change all raids where
		// the player is already subscribed.
		if($this->data->state != $this->data->state_compare)
		{
			// Flag this as changed playerdata
			$playerstate_changed = true;

			switch($this->data->state)
			{
				// This player is active (state: 3)
				case 3 :
					// Add subscription to all future raids
					$this->getModel('Subscription')->addPlayerToFutureSubs($this->data->id_player, $this->data->autosignon);
					$this->getModel('Comment')->deleteByPlayerAndState($this->data->id_player, array(1,2));
					$signup_done = true;
					break;

				// All other than active (state: 0, 1 or 2)
				default :
					// Set player on no ajax for all future raids
					$this->getModel('Subscription')->deleteSubscriptionByPlayer($this->data->id_player);

					// Remove player from all setlists
					$this->getModel('Setlist')->deletePlayerFromSetlist($this->data->id_player);

					// Remove all comments by player
					$this->getModel('Comment')->deleteByPlayerAndState($this->data->id_player, array(1,2));
					break;
			}
		}

		// Check 2: Has players autosignon state changed?
		if($this->data->autosignon != $this->data->autosignon_compare)
			$autosignon_changed = true;


		// After a change of autosignon it is important to update all future raids where this player maybe was set.
		// But we do not do this without care about the check 2 from above.
		if($autosignon_changed && !$signup_done && $this->data->state == 3)
		{
			switch($this->data->autosignon)
			{
				// Autosignon is off (0)
				case 0 :

					// Set player on no ajax for all future raids
					$this->getModel('Subscription')->setPlayerStateOnFutureSubs($this->data->id_player, 0);

					// Remove from all setlists of future raids
					$this->getModel('Setlist')->deletePlayerFromFutureSetlist($this->data->id_player);

					// Remove all signon comments
					$this->getModel('Comment')->deleteByPlayerAndState($this->data->id_player, array(1,2));

					break;

				case 1 :

					// Add subscription to all futur raids
					$this->getModel('Subscription')->setPlayerStateOnFutureSubs($this->data->id_player, 1);

					// Remove all resign comments
					$this->getModel('Comment')->deleteByPlayerAndState($this->data->id_player, array(1,2));

					break;
			}
		}

		// Save playerdata only if something has been changed
		if($playerstate_changed || $autosignon_changed )
			$this->save();

		// We are done and do a refunc do display the changed data
		$this->data->action = $playerstate_changed ? 'Complete' : 'Index';
	}

	/**
	 * Returns an array of useable playerstates.
	 * @return array
	 */
	public function getStateList()
	{
		return array(
			0 => $this->txt('player_state_old'),
			1 => $this->txt('player_state_applicant'),
			2 => $this->txt('player_state_inactive'),
			3 => $this->txt('player_state_active')
		);
	}

	/**
	 * Returns an array of values for autosignon selection
	 * @return array
	 */
	public function getAutosignonList()
	{
		return array(
			0 => $this->txt('web_no'),
			1 => $this->txt('web_yes')
		);
	}

	/**
	 * Creates the player account and the first char by using the data set in
	 * the player creation form. Checks for missing data and already used char name.
	 * @param Data $data Data from the controller
	 */
	public function createPlayer($data)
	{
		// Insert data into model
		$this->setData($data);

		// Check for empty charname
		if (empty($this->data->char_name))
			$this->addError('char_name', $this->txt('char_name_missing'));

		// Run validator manually because we do not use the save method for
		// playercreation (see insert() call below in this method) which means
		// the validator won't be called automatically
		$this->validate();

		// No saving with errors present
		if ($this->hasErrors())
			return;

		// set char is_main flag to 1
		$this->data->is_main = 1;

		// As we use the char creation from outside, there is no field for char
		// name comparision. If we do not set this field and value the name check
		// in chars model returns always true
		$this->data->org_char_name = '.';

		// Create an instance of the char model which we use for data validation
		// and creation of the first char
		$char_model = $this->getModel('Char');

		// Players can have multiple Chars. Here we create the players first char.
		$char_model->saveChar($this->data);

		// Char model errors need to be integrated in this models errors
		if ($char_model->hasErrors())
		{
			foreach ($char_model->errors as $fld => $error)
			{
				foreach ($error as $msg)
					$this->addError($fld, $msg);
			}
		}

		// Any errors here that stop us?
		if ($this->hasErrors())
			return;

		// By default new players are inactive
		$this->data->state = 2;

		// Uusing insert() method of model because we need to write the pk to
		// the table. This isn't possible with save() because a set id_player
		// marks the data as an update in save()
		$this->insert();
	}

	/**
	 * Looks for active players. Returns true when there are active players. Returns false on no active players.
	 */
	public function hasActivePlayer()
	{
		$this->setFilter('state=3');
		$this->read('key');
		return $this->hasData();
	}
}
?>
