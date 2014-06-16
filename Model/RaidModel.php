<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\User;
use Web\Framework\Lib\Smf;
use Web\Framework\Lib\App;
use Web\Framework\Lib\Data;

/**
 * Player model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class RaidModel extends Model
{
	protected $tbl = 'app_raidmanager_raids';
	protected $alias = 'raids';
	protected $pk = 'id_raid';
	public $validate = array(
		'destination' => array('empty'),
		'starttime' => array('empty', 'date'),
		'endtime' => array('empty', 'date'),
	);

	public function getInfos($id_raid)
	{
		$this->find($id_raid);

		// Create a link to the
		if ($this->data->id_topic && $this->cfg('use_forum'))
			$this->data->topic_url = Url::factory()->setTopic($this->data->id_topic)->getUrl();

		return $this->data;
	}

	public function getEdit($id_raid=null)
	{
		// for info edits
		if(isset($id_raid))
		{
			// load data of the raid only if hasErrors() indicate,
			// that there is no data already
			if ($this->hasErrors()==false)
				$this->find($id_raid);

			$this->data->mode = 'edit';
		}

		// for new raids
		if(!isset($id_raid))
		{
			// create empty data container
			$this->data = new Data();

			// some default values and dateconversions for the datepicker
			$this->data->destination = $this->cfg('raid_destination');
			$this->data->starttime = strtotime(date('Y-m-d') . ' ' . $this->cfg('raid_time_start'));
			$this->data->endtime = strtotime('+' . $this->cfg('raid_duration') . ' Minutes', $this->data->starttime);
			$this->data->specials = '';

			$this->data->mode = 'new';
		}

		$this->data->starttime = date('Y-m-d H:i', $this->data->starttime);
		$this->data->endtime = date('Y-m-d H:i', $this->data->endtime);

		return $this->data;
	}


	public function saveInfos($data)
	{
		$this->setData($data);

		// --------------------------------------
		// Custom validation checks
		// --------------------------------------

		// we need timestamp from todays date for compare the starts and ends
		$now = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

		// --------------------------------------
		// Save update
		// --------------------------------------

		// before saving data, let's see if this is a new raid to add.
		// a set value for id_raid indicates that we have an existing raid to update
		// Without set id_raid it is a new raid with further actions to run after the raid has been created and the id_raid
		// has been set as pk value from the insert.
		$is_new = isset($this->data->id_raid) ? false : true;

		$this->validate();

		if (!$this->hasErrors())
		{
			$start =  strtotime($this->data->starttime);
			$end = strtotime($this->data->endtime);

			if ($start > $end)
			{
				$this->addError('starttime', $this->txt('raid_start_after_end'));
				$this->addError('endtime', $this->txt('raid_end_before_start'));
				return;
			}

			$this->data->starttime = $start;
			$this->data->endtime = $end;
		}

		$this->save(false);

		// errors?
		if ($this->hasErrors())
			return;

		// --------------------------------------
		// add additional data
		// --------------------------------------

		if ($is_new)
		{
			// add subs to this raid
			$this->getModel('Subscription')->createSubscriptionForRaid($this->data->id_raid, $this->data->autosignon);

			// create one blank setup
			$this->getModel('Setup')->createDefaultSetup($this->data->id_raid);

			// new raids will be loaded completly
			$this->data->action = 'Index';
		}
		else
		{
			$this->data->action = 'Infos';

			// extend the already existing raiddata with topic and calendar infos
			$this->read(array(
			    'type' => 'ext',
			    'field' => array(
			        'raids.id_topic',
			        'raids.id_message',
			        'raids.id_event',
			    ),
			    'filter' => 'raids.id_raid={int:id_raid}',
			    'param' => array(
			        'id_raid' => $this->data->id_raid
			    ),
			));
		}

		$this->manageTopic();
	}

	public function deleteRaid($id_raid)
	{
		// we need some datat about topic id etc
		$this->find($id_raid);

		// delete all the child raiddata
		$model_names = array('Setup', 'Comment', 'Subscription', 'Setlist');

		foreach ($model_names as $model)
			$this->getModel($model)->deleteByRaid($id_raid);

		// set a delete flag for topic management
		$this->manageTopic(true);

		// delete the raid
		$this->delete($id_raid);

		// and return the next raid id, so we can load
		return $this->getNextRaidID();
	}

	/**
	 * Returns all future raid IDs
	 *
	 * @return array Array of raid IDs
	 */
	public function getOldRaidIDs()
	{
		return $this->read(array(
		    'type' => '*',
		    'filter' => 'starttime<{int:starttime} AND deleted=0',
		    'param' => array(
		        'starttime' => time()
		    ),
		    'order' => 'starttime',
		    'limit' => $this->cfg('num_list_old_raids')
		));
	}

	/**
	 * Returns the ID of the next upcomming raid if there is no next raidid, the
	 * method will return false
	 *
	 * @return int || boolean
	 */
	public function getNextRaidId()
	{
		$this->setField('id_raid');
		$this->setFilter('(starttime>={int:starttime} OR {int:starttime} BETWEEN raids.starttime AND raids.endtime) AND deleted=0');
		$this->setParameter('starttime', time());
		$this->setOrder('starttime ASC');
		$this->setLowerLimit(1);

		$id_raid = $this->read('val');

		// Do we have a raid id?
		if (!$id_raid)
		{
			// No, which means we have no raids at all. Lets try to add raids by calling
			// the autoAddRaids() Mmethod. Which returns either the number of raids added
			// or false if there was something wrong. Getting anythin else than flase means
			// that raids have been added and we now can get a raid id by calling this
			// method again.
			return $this->autoAddRaids() ? $this->getNextRaidId() : false;

		}
		else
		{
			// Yesm return it
			return $id_raid;
		}
	}

	/**
	 * Adds automatically new Raids to the raidlist
	 */
	public function autoAddRaids()
	{
		// Get the raiddays from settings
		$raiddays = $this->cfg('raid_days');

		// No raiddays no raid creation
		if (!$raiddays)
			return false;

		// Cet the number of future raids
		$num_future_raids = $this->read(array(
		    'type' => 'val',
		    'field' => 'COUNT(id_raid)',
		    'filter' => 'starttime > {int:starttime} AND deleted=0',
		    'param' => array('starttime' => time())
		));

		// Calculate the number of raids we have to add
		$num_raids_to_add = $this->cfg('raid_new_days_ahead') - $num_future_raids;

		// No raids to add?
		if($num_raids_to_add == 0)
			return;

		// We have raids to add so we need the start timestamp from the last future raid
		$this->setField('starttime');
		$this->setFilter('deleted=0');
		$this->setOrder('starttime DESC');
		$this->setLowerLimit(1);

		$last_raid_starttime = $this->read(array(
		    'type' => 'val',
		    'field' => 'starttime',
		    'filter' => 'deleted=0',
		    'order' => 'starttime DESC',
		    'limit' => 1
		));

		// reset the modelfilter
		$this->resetFilter();

		// some time calculations for later use
		$time_start = explode(':', $this->cfg('raid_time_start'));

		// do we have a timestamp of the last recent raid?
		if($last_raid_starttime > 0)
			// yay, use the date of this timestamp
			$starttime = mktime($time_start[0], $time_start[1], 0, date('m',$last_raid_starttime), date('d',$last_raid_starttime), date('Y',$last_raid_starttime));
		else
			// no, use the date of today
			$starttime = mktime($time_start[0], $time_start[1], 0, date('m'), date('d'), date('Y'));

		//
		$starttime_previous = $starttime;

		//
		$raids = array();

		$error_count = 0;
		$max_count = 100;

		// add raids until the number of raids to add is reached
		for($counter = 0; $counter < $num_raids_to_add; $error_count++)
		{
			// add 24 hours to the current starttime
			$starttime = strtotime('+1 day', $starttime);
			$checkday = date('w', $starttime);

			// is this weekday a raid day?
			if(isset($raiddays->{$checkday}))
			{
				$counter++;

				// Calculate endtime
				$endtime = strtotime('+' . $this->cfg('raid_duration')  . ' minutes', $starttime);

				// seems to be a valid raidday. create it.
				$data = new Data();

				// Destination default value ca
				$data->destination = $this->cfg('raid_destination');

				// Set calculated start and end time
				$data->starttime = $starttime;
				$data->endtime = $endtime;

				// Get the raidweek related to the start day of a week set in config
				$raidweek = date('w', $starttime) < $this->cfg('raid_weekday_start') ? date('W', $starttime_previous) : date('W', $starttime);
				$data->raidweek = (int) $raidweek;

				// Flag raids autosignon by value set in config
				$data->autosignon = $this->cfg('raid_autosignon');

				// Saveraid
				$this->setData($data)->save(false);

				// Any errors?
				if ($this->hasErrors())
				{
					$this->debug($this->errors, 'echo', 'print');
					return;
				}

				// Go and create the playersubscriptions
				$this->getModel('Subscription')->createSubscriptionForRaid($this->data->id_raid, $this->cfg('raid_autosignon'));

				// And create the first default setup
				$this->getModel('Setup')->createDefaultSetup($this->data->id_raid);

				// Finally manage the Topic and Calendar
				$this->manageTopic();
			}

			// Our current starttime will be the previous one for the next raid to add.
			$starttime_previous = $starttime;

			if ($error_count == $max_count)
				die('Errorcount (' . $error_count .'/' . $max_count .') on autoadd raid.');
		}

		return $num_raids_to_add;
	}

	public function getFutureRaidIDsAndAutosignon()
	{
		return $this->read(array(
		    'type' => '*',
		    'field' => array(
		        'id_raid',
		        'autosignon'
		    ),
		    'filter' => 'starttime>{int:starttime}',
		    'param' => array(
		        'starttime' => time()
		    )
		));
	}

	public function getFutureRaidIDs()
	{
		return $this->read(array(
	    	'type' => 'key',
	        'field' => 'id_raid',
	        'filter' => 'starttime>{int:starttime}',
	        'param' => array('starttime' => time())
	    ));
	}

	public function clearAllRaids()
	{
		$this->truncate();

		$model_names = array('Subscription', 'Setlist', 'Comment', 'Setup');

		foreach($model_names as $model)
			$this->getModel($model)->truncate();
	}

	private function manageTopic($delete=false)
	{
		if ($delete == true && $this->data->id_topic)
		{
			$this->app('Forum')->getModel('Topic')->deleteTopic($this->data->id_topic);

			if ($this->data->id_event)
			{
				Smf::useSource('Subs-Calendar');
				removeEvent($this->data->id_event);
			}

			return;
		}

		// Topic wanted?
		if (!$this->cfg('use_forum'))
			return;

		// create message body
		$body = '[b]Intro:[/b]' . PHP_EOL . $this->cfg('topic_intro');

		// Add spechial infos on exist
		if ($this->data->specials)
			$body .= PHP_EOL . PHP_EOL . '[b]' . $this->txt('raid_specials') . ':[/b]'. PHP_EOL . $this->data->specials;

		// Add link to raid in Raidmanager
		$body .= PHP_EOL . PHP_EOL . '[b]Raidmanager Link:[/b]' . PHP_EOL . Url::factory('raidmanager_raid_selected', array('id_raid'=>$this->data->id_raid))->getUrl();

		// Create the topics message
		$msgOptions = array(
			'body' => $body,
			'subject' => date('Y-m-d H:i', $this->data->starttime ) . ' - ' . $this->data->destination
		);

		// Is this an update to an existing message?
		if($this->data->id_message)
			$msgOptions['id'] = (int) $this->data->id_message;

		// Set some topic parameters
		$topicOptions = array(
			'mark_as_read' => false,
			'lock_mode' => 0,
			'sticky_mode' => 0
		);

		// ----------------------------------------------------------------------
		// Important!!! With set topic id, this is an update otherwise this is a new post
		// ----------------------------------------------------------------------
		if($this->data->id_topic)
			$topicOptions['id'] = (int) $this->data->id_topic;
		else
			$topicOptions['board'] = $this->cfg('topic_board');

		// infos about poster
		$posterOptions = array(
			'id' => User::getId()
		);


		// run topic creation. if this is an CREATE the return value will be
		// an array with data about the created topic
		$topic = App::getInstance('Forum')->getModel('Topic')->createTopic($msgOptions, $topicOptions, $posterOptions);

		// are there topic data?
		if(!$topic)
			return false;

		$this->data->id_topic = $topic->id_topic;
		$this->data->id_message = $topic->id_message;

		// Create calendar event?
		if ($this->cfg('use_calendar'))
		{
			Smf::useSource('Subs-Calendar');

			$eventOptions = array(
				'title' => date('H:i', $this->data->starttime) . ' - ' . $this->data->destination,
				'start_date' =>  date('Y-m-d', $this->data->starttime),
				'board' => $topicOptions['board'],
				'topic' => $topic->id_topic,
				'member' => User::getId()
			);

			insertEvent($eventOptions);

			$this->data->id_event = $eventOptions['id'];
		}

		// Save raidinfos
		$this->save(false);
	}
}
?>
