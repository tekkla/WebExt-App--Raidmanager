<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Lib\Data;

/**
 * Setlist model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class SetlistModel extends Model
{
	protected $tbl = 'app_raidmanager_setlists';
	protected $alias = 'setlist';
	protected $pk = 'id_setlist';

	/**
	 * Get all players set for one setup
	 * @param int $id_setup
	 * @return boolean| Data
	 */
	public function getSet($id_setup)
	{
		$this->read(array(
		    'type' => '*',
		    'field' => array(
		        'setlist.id_setlist',
		        'setlist.id_raid',
		        'setlist.id_player',
		        'setlist.id_char',
		        'setlist.set_as',
		        'chars.char_name',
		        'chars.id_class',
		        'chars.id_category',
		        'chars.is_main',
		        'class.class',
		        'class.color',
		        'cat1.category AS category_set',
		        'cat2.category AS category_org'
		    ),
		    'join' => array(
		        array('app_raidmanager_chars', 'chars', 'INNER', 'setlist.id_char = chars.id_char'),
		        array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class = class.id_class'),
		        array('app_raidmanager_categories', 'cat1', 'INNER', 'setlist.set_as = cat1.id_category'),
		        array('app_raidmanager_categories', 'cat2', 'INNER', 'chars.id_category = cat2.id_category'),
		    ),
		    'filter' => 'setlist.id_setup={int:id_setup}',
		    'param' => array('id_setup' => $id_setup),
		    'order' => 'setlist.set_as, class.id_class, chars.char_name',
		));

		// no data! return false
		if (!$this->hasData())
			return false;

		$data = new Data();

		$data->tank = array();
		$data->damage = array();
		$data->heal = array();

		foreach ($this->data as $set)
		{
			switch ($set->set_as)
			{
				case 1: $cat = 'tank'; break;
				case 2: $cat = 'damage'; break;
				case 3: $cat = 'heal'; break;
				default: $cat = 'tank'; break;
			}

			if ($set->set_as !== $set->id_category)
				$set->char_name .= ' (!)';

			$data->{$cat}->{$set->id_setlist} = $set;
		}

		$this->reset();

		return $this->data = $data;
	}

	public function getAvail($id_setup)
	{
		// load the player ids of set player
		$this->read(array(
			'type' => 'key',
		    'field' => 'id_player',
		    'filter' => 'id_setup={int:id_setup}',
		    'param' => array('id_setup' => $id_setup)
		));

		// from her we want to get the playerdata from all the players who are not set
		$query = array(
		    'type' => '*',
		    'field' => array(
    			'chars.id_char',
    			'subs.id_player',
    			'chars.char_name',
    			'chars.is_main',
    			'cats.id_category',
    			'cats.category',
    			'class.class',
    			'class.color'
		    ),
		    'join' => array(
		        array('app_raidmanager_chars', 'chars', 'INNER', 'subs.id_player=chars.id_player'),
		        array('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category'),
		        array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class'),
		    ),
		    'param' => array(
		        'id_raid' => $this->getRaidIdOfSetlist($id_setup),
		        'status' => 1,
		    ),
		    'order' => 'chars.char_name'
		);

		// if no player is set, all player in substable will be returned
		if ($this->hasNoData())
			$query['filter'] = 'subs.state=1 AND subs.id_raid={int:id_raid}';
		else
		{
			// there are players set, get all from subs except them.
			$query['filter'] = 'subs.state=1 AND subs.id_raid={int:id_raid} AND subs.id_player NOT IN ({array_int:setplayer})';
			$query['param']['setplayer'] = $this->data;
		}

		$this->data = $this->getModel('Subscription')->read($query);

		return $this->data;
	}


	public function deleteByRaid($id_raid)
	{
		$this->setFilter('id_raid={int:id_raid}');
		$this->setParameter('id_raid', $id_raid);
		$this->delete();
	}

	public function deleteBySetup($id_setup)
	{
		$this->setFilter('id_setup={int:id_setup}');
		$this->setParameter('id_setup', $id_setup);
		$this->delete();
	}

	private function getRaidIdOfSetlist($id_setup)
	{
		return $this->getModel('Setup')
					->setField('id_raid')
					->setIdFilter('setup', $id_setup)
					->read('val');
	}


	public function getSetlist($side, $id_setup, $id_category)
	{
		switch($side)
		{
			case 'avail':
				return $this->getEditAvail($id_setup, $id_category);
				break;

			case 'set':
				return $this->getEditSet($id_setup, $id_category);
                break;
        }
    }

    public function getEditSet($id_setup, $id_category)
    {
        $query = array(
            'type' => '*',
            'field' => array(
                'setlist.id_setlist',
                'setlist.id_setup',
                'setlist.id_player',
                'setlist.id_char',
                'setlist.set_as',
                'chars.char_name',
                'chars.is_main',
                'cats.id_category',
                'cats.category',
                'class.class',
                'class.color',
                'class.css'
            ),
            'join' => array(
                array('app_raidmanager_chars', 'chars', 'INNER', 'setlist.id_char=chars.id_char'),
                array('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category'),
                array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class')
            ),
            'filter' => 'setlist.id_setup={int:id_setup} AND setlist.set_as={int:set_as}',
            'param' => array(
    		    'id_setup' => $id_setup,
    		    'set_as' => $id_category
    		),
        );

        return $this->read($query, 'addSetActionbar');
	}

	public function addSetActionbar($player)
	{
		$actionbar = new Actionbar();

		// create the unset and set buttons for categories
		// the player ist not currently set to
		// 0 = unset
		// 1 = Tank
		// 2 = DD
		// 3 = Heal

		$categories = array(
			0 => array('unset', 'chevron-right'),
			1 => array('tank', 'shield'),
			2 => array('damage' , 'rocket'),
			3 => array('heal', 'medkit'),
		);

		// build links
		foreach ($categories as $key => $val)
		{
			// only if not already set as the current category
			if($player->set_as == $key)
				continue;

			// Basic parameters
			$params = array('id_setlist' => $player->id_setlist);

			// switch requires the new category id
			if ($key!=0)
				$params['id_category'] = $key;

			// which route?
			$route = $key==0 ? 'unset' : 'switch';

			$actionbar->createButton($val[1])->setIcon($val[1])->setTitle($this->txt($val[0]))->setRoute('raidmanager_setlist_' . $route, $params);
		}

		$player->actionbar = $actionbar->build();

		return $player;
	}

	public function getEditAvail($id_setup, $id_category)
	{
		// load the player ids of set player
		$this->read(array(
		    'type' => 'key',
		    'field' => 'id_player',
		    'filter' => 'id_setup={int:id_setup}',
		    'param' => array('id_setup' => $id_setup)
		));

		// from her we want to get the playerdata from all the players who are not set
		$query = array(
		    'type' => '*',
		    'field' => array(
    			'chars.id_char',
    			'subs.id_player',
    			'chars.char_name',
    			'chars.is_main',
    			'cats.id_category',
    			'cats.category',
    			'class.class',
    			'class.color',
    			'class.css',
    			$id_setup . ' AS id_setup',
		    ),
		    'join' => array(
		        array('app_raidmanager_chars', 'chars', 'INNER', 'subs.id_player=chars.id_player'),
		        array('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category'),
		        array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class')
		    ),
		    'param' => array(
		        'id_raid' => $this->getRaidIdOfSetlist($id_setup),
		        'id_category' => $id_category
		    ),
		    'order' => 'chars.is_main, class.class'
		);

		// if no player is set, all player in substable will be returned
		if ($this->hasNoData())
			$query['filter'] = 'cats.id_category={int:id_category} AND subs.state=1 AND subs.id_raid={int:id_raid}';
		else
		{
			// there are players set, get all from subs except them.
			$query['filter'] = 'cats.id_category={int:id_category} AND subs.state=1 AND subs.id_raid={int:id_raid} AND subs.id_player NOT IN ({array_int:setplayer})';
			$query['param']['setplayer'] =  $this->data;
		}

		return $this->data = $this->getModel('Subscription')->read($query, 'Setlist::addAvailActionbar');
	}


	public function addAvailActionbar($player)
	{
		// charakter flag
		if ($player->is_main == 0)
		{
		    $query = array(
		        'field' => array(
		            'chars.char_name',
		        	'class.css'
		        ),
		        'join' => array(
		            array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class')
		        ),
		        'filter' => 'chars.id_player={int:id_player} AND chars.is_main=1',
		        'param' => array('id_player' => $player->id_player)
		    );

			$mainchar = $this->getModel('Char')->read($query);

			$player->char_name .= ' <span class="' . $mainchar->css .'">(' . $mainchar->char_name . ')</span>';
		}

		// create the unset and set buttons for categories
		// the player ist not currently set to
		// 0 = unset
		// 1 = Tank
		// 2 = DD
		// 3 = Heal

		$categories = array(
			1 => array('tank', 'shield'),
			2 => array('damage' , 'rocket'),
			3 => array('heal', 'medkit'),
		);

		$actionbar = new Actionbar();

		// build links
		foreach ($categories as $key => $category)
		{
			$params = array(
				'id_setup' => $player->id_setup,
				'id_char' => $player->id_char,
				'id_category' => $key
			);

			// only if not already set as the current category
			$actionbar->createButton($category[1])->setIcon($category[1])->setTitle($this->txt($category[0]))->setRoute('raidmanager_setlist_set', $params);
		}

		$player->actionbar = $actionbar->build();

		return $player;

	}

	public function saveSetting($data)
	{
		if ($this->data->set_as == 0)
		{
			$this->delete($this->data->id_setlist);
			return;
		}

		$this->save();
	}

	public function removePlayerByRaid($id_raid, $id_player)
	{
		$this->setFilter('id_setup IN ({array_int:arr_setups}) AND id_player={int:id_player}');
		$this->setParameter(array(
			'arr_setups' => $this->app->getModel('Setup')->getIdsByRaid($id_raid),
			'id_player' => $id_player,
		));

		$this->delete();
	}

	public function deletePlayerFromFutureSetlist($id_player)
	{
		$this->setFilter('id_setup IN ({array_int:setups}) AND id_player={int:id_player}');
		$this->setParameter(array(
			'setups' => $this->app->getModel('Setup')->getFutureSetupIDs(),
			'id_player' => $id_player
		));
		$this->delete();
	}

	public function deletePlayerFromSetlist($id_player)
	{
		$this->setFilter('id_player={int:id_player}');
		$this->setParameter(array(
			'id_player' => $id_player
		));
		$this->delete();
	}

	public function deleteCharFromFutureSetlist($id_char)
	{
		$this->setFilter('id_setup IN ({array_int:setups}) AND id_char={int:id_char}');
		$this->setParameter(array(
			'setups' => $this->app->getModel('Setup')->getFutureSetupIDs(),
			'id_char' => $id_char
		));
		$this->delete();
	}

	public function setPlayer($id_setup, $id_char, $id_category)
	{
		// create new data object and fill it with needed setlist content
		$data = new Data();

		//  Set setup id
		$data->id_setup = $id_setup;

		// Get raid id by setup data
		$setup = $this->getModel('Setup')->find($id_setup);
		$data->id_raid = $setup->id_raid;

		// Get player id by chardata
		$char = $this->getModel('Char')->find($id_char);
		$data->id_player = $char->id_player;

		// Set the char id and the category
		$data->id_char = $id_char;
		$data->set_as = $id_category;

		$this->setData($data)->save(false);

		return $this->data;
	}

	public function switchPlayer($id_setlist, $id_category)
	{
		// load setlist entry
		$this->find($id_setlist);

		// update set_as field
		$this->data->set_as = $id_category;

		// save data
		$this->save(false);

		return $this->data;
	}

	public function unsetPlayer($id_setlist)
	{
		// get setlist infos
		$setlist = $this->find($id_setlist);

		// remove from setlist
		$this->delete($id_setlist);

		return $setlist;

	}

	public function copySetlist($src_id_setup, $dest_id_setup)
	{
		$dest = clone $this;

		$this->setFilter('id_setup={int:id_setup}');
		$this->setParameter('id_setup', $src_id_setup);
		$this->read('*');

		foreach ($this->data as $set)
		{
			// remove setlist id
			unset ($set->id_setlist);

			// set destination setup id
			$set->id_setup = $dest_id_setup;

			// and save
			$dest->setData($set)->save();
		}

	}
}
?>
