<?php

namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Html\Controls\Actionbar;

class SetupController extends Controller
{
	public $actions = array(
		'Edit' => array(
			'access' => 'raidmanager_perm_setup',
		),
		'Delete' => array(
			'access' => 'raidmanager_perm_setup',
		),
	);

	function Index($id_setup)
	{
		// create infos
		$this->Infos($id_setup);

		// create setlist
		$this->setVar('setlist_'.$id_setup, $this->app->getController('Setlist')->run('complete', array('id_setup' => $id_setup)));

		// ajax definition
		$this->ajax->setTarget('#raidmanager_setup_'.$id_setup);
	}

	function Complete($id_raid)
	{
		$setup_keys = $this->model->getIdsByRaid($id_raid);

		$this->setVar('setup_keys', $setup_keys);

		foreach ( $setup_keys as $id_setup )
			$this->Index($id_setup);

		$this->ajax->setTarget('#raidmanager_setups');
	}

	function Infos($id_setup)
	{
		$data = $this->model->getInfos($id_setup);

		// ------------------------------
		// Actionbar
		// ------------------------------
		$actionbar = new Actionbar();

		$params = array(
			'id_raid' => $data->id_raid,
			'id_setup' => $data->id_setup,
			'back_to' => $data->id_setup
		);

		if ($this->checkAccess('raidmanager_perm_setup')===true)
		{
			// build edit button
			$actionbar->createButton('edit')->setRoute('raidmanager_setup_edit', $params);

			// build add setup button
			$actionbar->createButton('new')->setRoute('raidmanager_setup_add', $params);
		}

		if ($this->checkAccess('raidmanager_perm_setlist')===true)
		{
			// build add button
			$actionbar->createButton('setup')->setIcon('user')->setRoute('raidmanager_setlist_edit', $params);
		}

		// delete button only if there is more than one setup
		if ($data->num_setups>1 && $this->checkAccess('raidmanager_perm_setup')===true)
		{
			$actionbar->createButton('delete')->setRoute('raidmanager_setup_delete', $params);
		}

		// build bar if access allowed
		$data->actionbar = $actionbar->build();

		$this->setVar('infos_'.$id_setup, $data);
	}

	function Edit($back_to, $id_raid, $id_setup = null)
	{
		$post = $this->request->getPost();

		// start save process on posted data exists
		if ($post)
		{
			// set the setup id we are from as id to copy setlist from
			$post->id_from = $back_to;

			// save data?
			$this->model->saveSetup($post);

			// no errors? then redirect to content to show
			if ($this->model->hasNoErrors())
			{
				// is this an edit or a new setup?
				if ($this->model->data->mode=='new' || $this->model->data->position!=$this->model->data->old_position)
					// this is a new setup. refresh the complete setup section
					$this->run('complete', array('id_raid' => $this->model->data->id_raid));
				else
					// this is an update. refresh only the setup
					$this->run('index', array('id_setup' => $this->model->data->id_setup));

				return;
			}
		}

		// ------------------------------
		// DATA
		// ------------------------------
		if ($this->model->hasNoData())
			$this->model->getEditSetup($id_raid, $id_setup);

			// ------------------------------
			// TEXT
			// ------------------------------
		$this->setVar('headline', $this->txt('setup_'.$this->model->data->mode));

		// ------------------------------
		// FORM
		// ------------------------------

		// Prepare parameters

		$params = array(
			'back_to' => $back_to,
			'id_raid' => $id_raid
		);

		if (isset($id_setup))
			$params['id_setup'] = $id_setup;

		$form = $this->getFormDesigner();

		// Set forms action route
		$form->setActionRoute($this->request->getCurrentRoute(), $params);

		// We need no buttons
		$form->noButtons();

		// hidden setup key
		if (isset($this->model->data->id_setup))
			$form->createElement('hidden', 'id_setup');

		// hidden raid id input
		$form->createElement('hidden', 'id_raid');

		// hidden raid id input
		$form->createElement('hidden', 'mode');

		// title label and input
		$form->createElement('text', 'title');

		// description label and textarea
		$form->createElement('textarea', 'description')->setAutofocus()->setCols(40)->setRows(3);

		// needed categories
		$categories = array(
			'tank',
			'damage',
			'heal'
		);

		foreach ( $categories as $cat )
			$form->createElement('number', 'need_'.$cat)->setSize(2)->setMaxlenght(2)->addAttribute('min', 0);

		// Other number fields
		$fields = array(
			'points',
			'position'
		);

		foreach ( $fields as $fld )
			$form->createElement('number', $fld)->setSize(2)->setMaxlenght(2);

		$this->setVar('form', $form);

		// ------------------------------
		// ACTIONBAR
		// ------------------------------
		$actionbar = new Actionbar();

		// Build cancel button
		$button = $actionbar->createButton('cancel');

		if (isset($id_raid))
			$button->setRoute('raidmanager_setup_complete', array('id_raid' => $id_raid));

		if ($id_setup)
			$button->setRoute('raidmanager_setup_index', array('id_setup' => $id_setup));


		// Build save button
		$params = array(
			'id_raid' => $id_raid,
			'back_to' => $back_to
		);

		$actionbar->createButton('save')->setForm($form->getId())->setRoute('raidmanager_setup_save', $params);

		// build actionbar
		$this->setVar('actionbar', $actionbar);

		// ------------------------------
		// RESPONSE
		// ------------------------------
		$target = $id_setup ? 'raidmanager_setup_'.$id_setup : 'raidmanager_setups';
		$this->ajax->setTarget('#'.$target);
	}

	function Delete($id_setup, $id_raid)
	{
		// first delete setlists
		$this->getModel('Setlist')->deleteBySetup($id_setup);

		// then the setup
		$this->model->delete($id_setup);

		// reload setup area
		$this->run('complete', array('id_raid' => $id_raid));
	}
}

?>
