<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Lib\Data;

final class SetlistController extends Controller
{
    protected $access = array(
    	'Edit' => 'raidmanager_perm_setlist',
        'Save' => 'raidmanager_perm_setlist',
    );

	public function Complete($id_setup)
	{
		$this->Index($id_setup);
		$this->Availlist($id_setup);
		$this->ajax->setTarget('#raidmanager_setup_' . $id_setup . '_player');
	}

	public function Index($id_setup)
	{
		$this->setVar(array(
			'noneset' 			=> $this->txt('setup_noneset'),
			'notset' 			=> $this->txt('setup_notset'),
			'headline_tank' 	=> $this->txt('category_tank'),
			'headline_damage' 	=> $this->txt('category_damage'),
			'headline_heal' 	=> $this->txt('category_heal'),
			'ismain' 			=> $this->txt('char_ismain'),
			'istwink' 			=> $this->txt('char_istwink'),
			'setlist' 			=> $this->model->getSet($id_setup),
			'count_set' 		=> $this->model->countData()
		));
	}

	public function Availlist($id_setup)
	{
		$this->setVar(array(
			'availlist' =>  $this->model->getAvail($id_setup),
			'count' => $this->model->countData(),
		));
	}

	public function Edit($id_setup, $id_setlist=null)
	{
		// get setup data
		$setup = $this->getModel('Setup')->getInfos($id_setup);

		// Create actionbar
		$actionbar = new Actionbar();
		$actionbar->createButton('cancel')->setRoute('raidmanager_setup_index', array('id_setup' => $id_setup));

		// Set view vars
		// gloabl text
		$this->setVar(array(
			'none_set'	=> $this->txt('setlist_none_set'),
			'none_avail'	=> $this->txt('setlist_none_avail'),
			'actionbar' => $actionbar,
			'headline' => $setup->title
		));


		// -------------------------
		// PLAYERLISTS
		// -------------------------
		$categories = array(
			1 => 'tank',
			2 => 'damage',
			3 => 'heal'
		);

		foreach($categories as $key => $category)
		{
			$this->setVar('headline_' . $category, $this->txt('category_' . $category));

			$this->Editlist('avail', $id_setup, $key, $category, $setup->{'need_' . $category});
			$this->Editlist('set', $id_setup, $key, $category, $setup->{'need_' . $category});
		}

		$this->ajax->setTarget('#raidmanager_setup_' . $id_setup);

	}

	public function Editlist($side, $id_setup, $id_category, $category, $num_need)
	{
		// get the set available
		$this->setVar(array(
			$side . '_' . $category =>  $this->model->getSetlist($side, $id_setup, $id_category),
			'headline_' . $category => $this->txt('category_' . $category) . ' (' . $this->model->countData() . '/' . $num_need . ')',
			$category . '_count' => $this->model->countData()
		));

		$this->ajax->setTarget('#raidmanager_setup_' . $id_setup . '_setlist');
	}


	/**
	 * Set playerchar to category
	 * @param unknown $id_setup
	 * @param unknown $id_char
	 * @param unknown $set_as
	 */
	public function SetPlayer($id_setup, $id_char, $id_category)
	{
		#$this->model->setPlayer($id_setup, $id_char, $id_category);
		#$this->run('Edit', array('id_setup' => $id_setup));
		$this->run(
			'Edit',
			array(
				'id_setup' => $this->model->setPlayer($id_setup, $id_char, $id_category)->id_setup
			)
		);
	}

	/**
	 * Changes category in the setlist
	 * @param int $id_setlist
	 * @param int $set_as
	 */
	public function SwitchPlayer($id_setlist, $id_category)
	{
		#$data = $this->model->switchPlayer($id_setlist, $id_category);
		#$this->run('Edit', array('id_setup' => $data->id_setup));

		$this->run(
			'Edit',
			array(
				'id_setup' => $this->model->switchPlayer($id_setlist, $id_category)->id_setup
			)
		);
	}

	/**
	 * Remove player from the setlist
	 * @param int $id_setup
	 * @param int $id_setlist
	 */
	public function UnsetPlayer($id_setlist)
	{
		#$this->model->unsetPlayer($id_setlist);
		#$this->run('Edit', array('id_setup' => $this->model->data->id_setup));
		$this->run(
			'Edit',
			array(
				'id_setup' => $this->model->unsetPlayer($id_setlist)->id_setup
			)
		);
	}

	public function Save($side, $id_setup, $id_player, $id_char, $set_as, $id_setlist)
	{
		// create data container and as arguments
		$data = new Data();
		$data->id_setup = $id_setup;
		$data->id_player = $id_player;
		$data->id_char = $id_char;
		$data->set_as = $set_as;

		// with set id_setlist, this is a change of an already set char
		if ($id_setlist)
			$data->id_setlist = $id_setlist;

		// create / update setlist entry
		$this->model->setData($data)->saveSetting();

		$this->run('editlist');
	}
}
?>