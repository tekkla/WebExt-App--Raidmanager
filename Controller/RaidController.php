<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Error;
use	Web\Framework\Lib\Controller;
use	Web\Framework\Lib\Url;
use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Html\Controls\UiButton;

/**
 * Basic Raidmanager Controller
 * @author Michael
 * @copyright 2013
 *
 */
final class RaidController extends Controller
{
	protected $access = array(
		'Edit' => 'raidmanager_perm_raid',
	    'Save' => 'raidmanager_perm_raid',
	    'Delete' => 'raidmanager_perm_raid',
	);

	protected $events = array(
	    'Complete' => array(
	        'before' => 'checkForRaidId'
	     ),
	    'WidgetNextRaid' => array(
	        'before' => 'checkForRaidId',
	    ),
	);

	/**
	 *
	 * @param int $id_raid
	 */
	public function Complete($id_raid)
	{
		// load calendar
		$this->Calendar($id_raid);

		// load raid
		$this->Index($id_raid);

		// the index function replaces the complete raidmanager on call
		$this->ajax->setTarget('#raidmanager');
	}

	/**
	 * Checks for the current raid id in request. If no raid id in request,
	 * it tries to get the id of the next upcomming raid. If this also fails,
	 * because of no raids, the raid autoadd function will be started.
	 */
	public function checkForRaidId()
	{
		// Is there a raid id in the request?
		if($this->request->checkParam('id_raid') === false)
		{
			// Try to get raid id from database
			$id_raid = $this->model->getNextRaidID();

			if ($id_raid)
			{
				// add id_raid to current controllers parameter
				$this->addParam('id_raid', $id_raid);

				// And publish it via request for other controllers which may need it
				$this->request->addParam('id_raid',  $id_raid);
			}
			else
			{
				// The following belongs only to users with config userrights
				if ($this->checkAccess('raidmanager_perm_config'))
				{
					// Autoraid failed and we have no raids in db. Check for not set raiddays.
					if (!$this->cfg('raid_days'))
					{
						// No raiddays found, redirect user to raidmanager config and set a flash message
						// to select the days to use for raids
						$this->message->warning($this->txt('no_raid_days_selected'));
						redirectexit(Url::factory('admin_app_config', array('app_name' => 'raidmanager'))->getUrl());
					}

					// At this point there is whether a raid id as parameter nor as id from the db.
					// We have to assume that no future raids exist. show error message and offer a
					// link to create a raid.
					$button = UiButton::routeLink('raidmanager_raid_add', null, 'full')->setInner($this->txt('raidmanager_action_raid_add'));

					Throw new Error($this->txt('raidmanager_raid_noraid found') . '<br>' . $button->build());
				}
			}
		}
	}

	function Calendar($id_raid)
	{
		// calendar
		$this->setVar('calendar', $this->getController('Calendar')->run('index', array('id_raid' => $id_raid)));

		// as ajax ajax refresh calendar area
		$this->ajax->setTarget('#raidmanager_calendar');
	}


	function Index($id_raid)
	{
		// raidinfos
		$this->Infos($id_raid);

		// -----------------------------
		// content from other controller
		// -----------------------------

		// comments
		$this->setVar('comments', $this->getController('Comment')->run('index', array('id_raid' => $id_raid)));

		// subsriptions
		$this->setVar('subscriptions',$this->getController('Subscription')->run('index', array('id_raid' => $id_raid)));

		// setups
		$this->setVar('setups', $this->getController('Setup')->run('complete', array('id_raid' => $id_raid)));

		$this->ajax->setTarget('#raidmanager_raid');

	}

	function Infos($id_raid)
	{
		// Create actionbar if access granted
		if ($this->checkUserrights('raidmanager_perm_raid'))
		{
			$actionbar = new Actionbar();

			$params = array(
				'id_raid' => $id_raid,
				'back_to' => $id_raid,
				'target' => 'raidmanager_infos'
			);

			$actionbar->createButton('edit')->setRoute('raidmanager_raid_edit', $params);
			$actionbar->createButton('new')->setRoute('raidmanager_raid_add', $params);
			$actionbar->createButton('autoadd')
							->setRoute('raidmanager_raid_autoadd', $params)
							->setIcon('calendar')
							->setTitle($this->txt('raid_autoraid'))
							->useFull();
			$actionbar->createButton('delete')->setRoute('raidmanager_raid_delete', $params);

			$this->setVar('actionbar', $actionbar);
		}

		$this->setVar(array(
			'data' => $this->model->getInfos($id_raid),
			'txt_specials' => $this->txt('raid_specials'),
		));

		if (isset($data->topic_url))
			$this->setVar('txt_topiclink', $this->txt('raid_topiclink'));

		$this->ajax->setTarget('#raidmanager_infos');
	}

	function Edit($back_to, $id_raid=null)
	{
		$post = $this->request->getPost();

		if ($post)
		{
			$this->model->saveInfos($post);

			// save errors?
			if ($this->model->hasNoErrors())
			{
				// no error on saving -> add calendar refresh to ajax
				$this->ajax->call('Raidmanager', 'Calendar', 'Index', '#raidmanager_calendar', array('id_raid'=>$this->model->data->id_raid));

				// 	go to action set by model save action
				$this->redirect($this->model->data->action, array('id_raid'=>$this->model->data->id_raid));

				return;
			}
		}

		// ---------------------------------------
		// DATA
		// ---------------------------------------

		// load it only if the is no data present
		if ($this->model->hasNoData())
			$this->model->getEdit($id_raid);

		// ------------------------------
		// TEXT
		// ------------------------------
		$this->setVar('headline', $this->txt('raid_headline_' . $this->model->data->mode));

		// ---------------------------
		// FORM
		// ---------------------------
		$params = array(
			'back_to'=> $back_to
		);

		if (isset($id_raid))
			$params['id_raid'] = $id_raid;

		$form = $this->getFormDesigner();

		$form->setActionRoute($this->request->getCurrentRoute(), $params);

		// Form is ajax
		#$this->form->isAjax();

		// No buttons please
		$form->noButtons();

		// hidden raid id field only on edit
		if (isset($id_raid))
			$form->createElement('hidden', 'id_raid');

		// Edit or new mode
		$form->createElement('hidden', 'mode');

		// Destination field
		$form->createElement('text', 'destination');

		// Open a new group for the starting date and time
		$form->openGroup('start_group')->newRow();

		// date start field
		$form->createElement('datetime', 'starttime')->setElementWidth('sm-2')->setMinDate(date("Y-m-d"))->setMinuteStepping(15);

		// date end field
		$form->createElement('datetime', 'endtime')->setElementWidth('sm-2')->setMinDate(date("Y-m-d"))->setMinuteStepping(15);

		$form->closeGroup();

		// specials textarea
		$form->createElement('textarea', 'specials')->setRows(5);

		if(!isset($id_raid))
			$form->createElement('switch', 'autosignon');

		$this->setVar('form', $form);

		// ---------------------------
		// ACTIONBAR
		// ---------------------------

		// create actionbar
		$actionbar = new Actionbar();

		// prepare button creation
		$app = 'raidmanager';
		$ctrl = 'raid';

		// cancel button
		$button = $actionbar->createButton('cancel');

		$params = array('id_raid' => $back_to);

		if (isset($id_raid))
		{
			// on cancel reload only raidinfos
			$target = 'raidmanager_infos';
			$route = 'raidmanager_raid_infos';
		}
		else
		{
			// on cancel reload complete raid
			$target = 'raidmanager_raid';
			$route  = 'raidmanager_raid_data';
		}

		$button->setTarget($target);
		$button->setRoute($route, $params);

		// save button
		$button = $actionbar->createButton('save');

		$params = array('back_to' => $back_to);

		if (isset($id_raid))
			$params['id_raid'] = $id_raid;

		$button->setRoute($this->request->getCurrentRoute(), $params);

		// set the formname we want to post
		$button->setForm($form->getId());

		// publish actionbar to view
		$this->setVar('actionbar', $actionbar);

		// puiblish data to view
		$this->setVar('edit', $this->model->data );

		// some ajax definition for ajax calls
		$this->ajax->setTarget('#' . $target);
	}

	public function Delete($id_raid)
	{
		$this->model->deleteRaid($id_raid);

		// redirect to the index page of raidmanager
		$url = Url::factory('raidmanager_raid_start')->getUrl();
		$this->doRefresh($url);
	}

	public function Autoadd()
	{
		// this action has no render result

		// no, so let's try to add some raid by autoadding
		$this->model->autoAddRaids();

		if($this->model->hasErrors())
		{
			$this->debug($this->model->errors, 'console');
		}
		else
		{
			$url = URL::factory('raidmanager_raid_start')->getUrl();
			redirectexit($url);
		}
	}

	public function Reset()
	{
		$this->model->clearAllRaids();
		$this->Autoadd();
	}
}
?>