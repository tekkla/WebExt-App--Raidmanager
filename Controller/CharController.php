<?php
namespace Web\Apps\Raidmanager\Controller;


use Web\Framework\Lib\Controller;

use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Helper\FormDesigner;
use Web\Framework\Lib\Debug;


class CharController extends Controller
{
	public $actions = array(
		'*' => array(
			'access' => 'raidmananger_perm_player',
			'tools' => 'Form'
		)
	);

	public function Index($id_player)
	{
		$this->Charlist($id_player);
	}


	public function Charlist($id_player)
	{
		// ------------------------------
		// DATA
		// ------------------------------
		$data = $this->model->getCharlist($id_player);

		$this->setVar('charlist', $data);

		// ------------------------------
		// TEXT
		// ------------------------------
		$this->setVar(array(
			'headline' => $this->txt('charlist_headline') . ' (' . $this->model->countData() . ')',
			'frame_id' => 'raidmanager_charlist_' . $id_player
		));

		// ------------------------------
		// ACTIONBAR
		// ------------------------------
		if ($this->checkUserrights('raidmanager_player_edit') === true)
		{
			$actionbar = new Actionbar();
			$actionbar->createButton('new', 'ajax', 'icon')->setRoute('raidmanager_char_add', array('id_player' => $id_player));
			$this->setVar('actionbar', $actionbar);
		}

		// ------------------------------
		// FOR AJAX RESPONSE
		// ------------------------------
		$this->ajax->setTarget('#raidmanager_charlist_' . $id_player);
	}

	public function Edit($id_player, $id_char=null)
	{
		// get the posted data
		$post = $this->request->getPost();

		if ($post)
		{
			// attach player id to posted data
			$post->id_player = $id_player;

			// attach set char id to posted data
			if (isset($id_char))
				$post->id_char= $id_char;

			// try to save our char data
			$this->model->saveChar($post);

			// any errors? no? ok, refresh the pageparts
			if ($this->model->hasNoErrors())
			{
				// params both functions need
				$params = array('id_player' => $id_player);

				// If we edited a mainchar, we have to reload the complete player editor. And we only want an ajax result!
				if (isset($this->model->data->is_main))
				{
					$this->app->getController('Player')->ajax('Edit', array('id_player' => $id_player));
					$this->ajax->setTarget('#');

				}
				else
				{
					$this->redirect('Charlist', $params);
				}

				return;
			}
		}

		// load data from model if char id is given
		// otherwise assume it is a new char
		if ($this->model->hasNoData())
			$this->model->getEditChar($id_player, $id_char);

		// ------------------------------
		// TEXT
		// ------------------------------
		$this->setVar('headline', $this->txt('char_' . $this->model->data->mode . '_headline'));

		// ------------------------------
		// FORM
		// ------------------------------

		// action params
		$params = array(
			'id_player' => $id_player,
		);

		if (isset($id_char))
			$params['id_char'] = $id_char;

		$form = new FormDesigner();

		$form->attachModel($this->model);

		$form->setActionRoute($this->request->getCurrentRoute(), $params);

		// No buttons needed. Using Actionbar.
		$form->noButtons();

		// hidden mode field
		$form->createElement('hidden', 'mode');

		// input: char name
		$form->createElement('text', 'char_name')->hasCompare(true);

		// select: class
		$control = $form->createElement('dataselect', 'id_class')->setDataSource('Raidmanager','Charclass','getClasses');

		if (isset($this->model->data->id_class))
			$control->setSelectedValue($this->model->data->id_class);

		// select: category
		$control = $form->createElement('dataselect', 'id_category')->setDataSource('Raidmanager','Category','getCategories');

		if (isset($this->model->data->id_category))
			$control->setSelectedValue($this->model->data->id_category);

		$this->message->info(Debug::dumpVar($this->model->data), false);

		// mainchar selection only on chars which are not mains
		if (isset($this->model->data->is_main) && $this->model->data->is_main == 0)
			$form->createElement('switch', 'is_main');

		// build form and bind to view
		$this->setVar('form', $form);

		// ------------------------------
		// ACTIONBAR
		// ------------------------------
		$actionbar = new Actionbar();

		// cancel button
		$actionbar->createButton('cancel', 'ajax', 'icon')->setRoute('raidmanager_char_list', $params);

		// save button
		$actionbar->createButton('save', 'ajax', 'icon')->setForm($form->getId())->setRoute($this->request->getCurrentRoute(), $params);

		// Publish actionbar to view.
		$this->setVar('actionbar',  $actionbar);

		// Define ajax ajax target
		$this->ajax->setTarget('#raidmanager_charlist_' . $id_player);
	}

	public function Delete($id_player, $id_char)
	{
		$this->model->deleteChar($id_char);
		$this->run('Charlist', array('id_player' => $id_player));

	}
}

?>