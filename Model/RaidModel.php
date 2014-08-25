<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Url;
use Web\Framework\Lib\User;
use Web\Framework\Lib\Smf;
use Web\Framework\Lib\App;
use Web\Framework\Lib\Data;
use Web\Framework\Lib\Error;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

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
        'destination' => array(
            'empty'
        ),
        'starttime' => array(
            'empty',
            'date'
        ),
        'endtime' => array(
            'empty',
            'date'
        )
    );

    /**
     * Loads an returns data for a specific raid
     * @param int $id_raid
     * @return \Web\Framework\Lib\Data
     */
    public function getInfos($id_raid)
    {
        $this->find($id_raid);

        // Create a link to the
        if ($this->data->id_topic && $this->cfg('use_forum'))
            $this->data->topic_url = Url::factory()->setTopic($this->data->id_topic)->getUrl();

        return $this->data;
    }

    /**
     * Loads and returns raid data to be used for edit. When $id_raid is not set, this method assumes an empty record
     * for a new raid is requested and returns a new raid with default values set in app config.
     * @param int $id_raid
     * @return \Web\Framework\Lib\Data
     */
    public function getEdit($id_raid = null)
    {
        // For edits
        if (isset($id_raid))
        {
            // Load data of the raid only if hasErrors() indicates that there is already no data
            if ($this->hasErrors() == false)
                $this->find($id_raid);

            $this->data->mode = 'edit';
        }

        // For new raids
        if (!isset($id_raid))
        {
            // Create empty data container
            $this->data = new Data();

            // Some default values and dateconversions for the datepicker
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

    /**
     * Saves raid data to database.
     * @param Data $data
     */
    public function saveInfos($data)
    {
        // Attach data to model
        $this->data = $data;

        // We need timestamp from todays date for compare the starts and ends
        $now = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        // Before saving data, let's see if this is a new raid to add.
        // A set value for id_raid indicates that we have an existing raid to update
        // Without set id_raid it is a new raid with further actions to run after the raid has been created
        // and the id_raid has been set as pk value from the insert.
        $is_new = isset($this->data->id_raid) ? false : true;

        // Validate the data
        $this->validate();

        // Check for startime after endtime
        $start = strtotime($this->data->starttime);
        $end = strtotime($this->data->endtime);

        if ($start > $end)
        {
            $this->addError('starttime', $this->txt('raid_start_after_end'));
            $this->addError('endtime', $this->txt('raid_end_before_start'));
        }

        // Validation errors?
        if ($this->hasErrors())
        	return;

        // Write timestamps to data
        $this->data->starttime = $start;
        $this->data->endtime = $end;

        // And save raid data without further validation
        $this->save(false);

        // On new raids we need to create the playersubscriptions and a first default setup
        if ($is_new)
        {
            // Add subs to this raid
            $this->getModel('Subscription')->createSubscriptionForRaid($this->data->id_raid, $this->data->autosignon);

            // Create one blank setup
            $this->getModel('Setup')->createDefaultSetup($this->data->id_raid);

            // New raids will be loaded completly
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
                    'raids.id_event'
                ),
                'filter' => 'raids.id_raid={int:id_raid}',
                'param' => array(
                    'id_raid' => $this->data->id_raid
                )
            ));
        }

        $this->manageTopic();
    }

    /*
     * + Deletes a raid and it's child data
     */
    public function deleteRaid($id_raid)
    {
        // Delete all the child raiddata
        $model_names = array(
            'Setup',
            'Comment',
            'Subscription',
            'Setlist'
        );

        foreach ( $model_names as $model )
        {
            $this->getModel($model)->delete(array(
                'filter' => 'id_raid={int:pk}',
                'param' => array(
                    'pk' => $id_raid
                )
            ));
        }

        // Load raiddata to get informations about topics and events
        $this->find($id_raid);

        // Remove possible topics and events
        $this->manageTopic(true);

        // Delete the raid
        $this->delete($id_raid);

        // Remove all present raiddata from memory
        $this->reset(true);

        // and return the next raid id, so we can load
        return $this->getNextRaidID();
    }

    /**
     * Returns the ID of the next upcomming raid if there is no next raidid, the method will return false
     * @return int|boolean
     */
    public function getNextRaidId()
    {
        $id_raid = $this->read(array(
            'type' => 'val',
            'field' => 'id_raid',
            'filter' => '(starttime>={int:starttime} OR {int:starttime} BETWEEN raids.starttime AND raids.endtime)',
            'param' => array('starttime' => time()),
            'order' => 'starttime ASC',
            'limit' => 1
        ));

        // Do we have a raid id?
        if (!$id_raid)
        {
            // No, which means we have no raids at all. Lets try to add raids by calling
            // the autoAddRaids() Mmethod. Which returns either the id of the last added raid
            // or false if something went wrong.
            return $this->autoAddRaids();
        }
        else
        {
            // Yesm return it
            return $id_raid;
        }
    }

    /**
     * Adds automatically new raids to the raidlist
     */
    public function autoAddRaids()
    {
        // Get the raiddays from settings
        $raiddays = $this->cfg('raid_days');

        // No raiddays no raid creation
        if (!$raiddays)
            return false;

        // Calculate the number of raids we have to add
        $num_raids_to_add = $this->cfg('raid_new_days_ahead') - $this->getNumFutureRaids();

        // No raids to add?
        if ($num_raids_to_add == 0)
            return false;

        // We have raids to add so we need the start timestamp from the last future raid
        $last_raid_starttime = $this->read(array(
            'type' => 'val',
            'field' => 'starttime',
            'filter' => 'starttime >={int:starttime}',
            'param' => array(
                'starttime' => time()
            ),
            'order' => 'starttime DESC',
            'limit' => 1
        ));

        $this->fire->log($last_raid_starttime);

        // Reset the modelfilter
        $this->resetFilter();

        // Some time calculations for later use
        $time_start = explode(':', $this->cfg('raid_time_start'));

        // Do we have a timestamp of the last recent raid?
        if ($last_raid_starttime && $last_raid_starttime > 0)
            // yay, use the date of this timestamp
            $starttime = mktime($time_start[0], $time_start[1], 0, date('m', $last_raid_starttime), date('d', $last_raid_starttime), date('Y', $last_raid_starttime));
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
            if (isset($raiddays->{$checkday}))
            {
                $counter++;

                // Calculate endtime
                $endtime = strtotime('+' . $this->cfg('raid_duration') . ' minutes', $starttime);

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

                // Info text to be used set in config
                if ($this->cfg('raid_specials'))
                    $data->specials = $this->cfg('raid_specials');

                // Flag raids autosignon by value set in config
                $data->autosignon = $this->cfg('raid_autosignon');

                // Create raid without validation
                $this->data = $data;
                $this->save(false);

                // Any errors?
                if ($this->hasErrors())
                    Throw new Error('Error on Raidmanager: autoAdd()', 1000, $this->errors);

                // Go and create the playersubscriptions
                $this->getModel('Subscription')->createSubscriptionForRaid($this->data->id_raid, $this->cfg('raid_autosignon'));

                // And create the first default setup
                $this->getModel('Setup')->createDefaultSetup($this->data->id_raid);

                // Finally manage the Topic and Calendar
                $this->manageTopic();

                // Store id of new created raid
                $raids[] = $this->data->id_raid;
            }

            // Our current starttime will be the previous one for the next raid to add.
            $starttime_previous = $starttime;

            if ($error_count == $max_count)
                Throw new Error('Errorcount (' . $error_count . '/' . $max_count . ') on Raidmanagerautoadd raid.', 1000);
        }

        return $raids[0];
    }

    /**
     * Loads id and autosignon infos of all future raids. Returns boolean false when no raid is found.
     * @return boolean|arrayl
     */
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

    /**
     * Returns raid ids of future raids
     * @return array
     */
    public function getFutureRaidIDs()
    {
        return $this->read(array(
            'type' => 'key',
            'filter' => 'starttime>{int:starttime}',
            'param' => array(
                'starttime' => time()
            )
        ));
    }

    /**
     * Returns the number of future raids
     * @return int
     */
    public function getNumFutureRaids()
    {
        return $this->count('starttime>{int:starttime}', array('starttime' => time()));
    }

    /**
     * Truncates raid, setup, setlist, comment and subscription table
     */
    public function clearAllRaids()
    {
        $this->truncate();

        $model_names = array(
            'Subscription',
            'Setlist',
            'Comment',
            'Setup'
        );

        foreach ( $model_names as $model )
            $this->getModel($model)->truncate();
    }

    /**
     * Manages topic and event creation/deletion for raids
     * @param boolean $delete Flag to use this method for topic and event removal
     * @return void boolean
     */
    private function manageTopic($delete = false)
    {
        // Delete requested?
        if ($delete==true)
        {
            // Do only when topic id present
            if (isset($this->data->id_topic))
                App::getInstance('Forum')->getModel('Topic')->deleteTopic($this->data->id_topic, true, true);

            // Remove also possible calendar event
            if (isset($this->data->id_event))
            {
                Smf::useSource('Subs-Calendar');
                removeEvent($this->data->id_event);
            }

            return;
        }

        ## Topic

        // Do only when topic using is switched on in config
        if (!$this->cfg('use_forum'))
            return;

        // Create message body
        $body = '[b]Intro:[/b]' . PHP_EOL . $this->cfg('topic_intro');

        // Add special infos on exist
        if (isset($this->data->specials))
            $body .= PHP_EOL . PHP_EOL . '[b]' . $this->txt('raid_specials') . ':[/b]' . PHP_EOL . $this->data->specials;

        // Add direct link to raid
        $body .= PHP_EOL . PHP_EOL . '[b]Raidmanager Link:[/b]' . PHP_EOL . Url::factory('raidmanager_raid_selected', array(
            'id_raid' => $this->data->id_raid
        ))->getUrl();

        // Create the topics message
        $msgOptions = array(
            'body' => $body,
            'subject' => date('Y-m-d H:i', $this->data->starttime) . ' - ' . $this->data->destination
        );

        // Is this an update to an existing message?
        if (isset($this->data->id_message))
            $msgOptions['id'] = $this->data->id_message;

        // Set some topic parameters
        $topicOptions = array(
            'mark_as_read' => false,
            'lock_mode' => 0,
            'sticky_mode' => 0
        );

        // Important!!! With set topic id, this is an update otherwise this is a new post
        if (isset($this->data->id_topic))
            $topicOptions['id'] = $this->data->id_topic;
        else
            $topicOptions['board'] = $this->cfg('topic_board');

        // Infos about poster
        $posterOptions = array(
            'id' => User::getId()
        );

        // Run topic creation. If this is an CREATE the return value will be an array with data about the topic created
        $topic = App::getInstance('Forum')->getModel('Topic')->saveTopic($msgOptions, $topicOptions, $posterOptions);

        // Is there topic data?
        if (!$topic)
            return false;

        $this->data->id_topic = $topic->id_topic;
        $this->data->id_message = $topic->id_message;

        ## Calendar

        // Create calendar event?
        if ($this->cfg('use_calendar'))
        {
            Smf::useSource('Subs-Calendar');

            $eventOptions = array(
                'title' => date('H:i', $this->data->starttime) . ' - ' . $this->data->destination,
                'start_date' => date('Y-m-d', $this->data->starttime),
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
