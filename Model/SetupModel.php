<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Data;
use Web\Framework\Lib\Error;


class SetupModel extends Model
{
    // Table in database
    public $tbl = 'app_raidmanager_setups';

    // Alias to use
    public $alias = 'setups';

    // Name of id columns
    public $pk = 'id_setup';

    // Validation rules
    public $validate = array(
        'title' => array(
            'empty'
        ),
        'need_tank' => array(
            'empty',
            array('min', array(0, 'number'))
        ),
        'need_damage' => array(
            'empty'
        ),
        'need_heal' => array(
            'empty'
        )
    );

    /**
     * Returns data for setup create/edit
     * @param int $id_raid
     * @param int $id_setup
     * @throws ParameterNotSetError
     * @return \Web\Framework\Lib\Data
     */
    public function getEditSetup($id_raid = null, $id_setup = null)
    {
        if (isset($id_setup))
        {
            $this->find($id_setup);
            $this->data->mode = 'edit';
        }
        else
        {
            if (!isset($id_raid))
                Throw new Error('Needed parameter not set', 1001, array('id_raid'));

            // Create default data
            $data = new Data();
            $data->id_raid = $id_raid;
            $data->title = $this->cfg('setup_title');
            $data->description = $this->cfg('setup_notes');
            $data->notes = $this->cfg('setup_notes');
            $data->need_tank = $this->cfg('setup_tank');
            $data->need_damage = $this->cfg('setup_damage');
            $data->need_heal = $this->cfg('setup_heal');
            $data->position = 0;
            $data->points = 0;
            $data->killed = 0;
            $data->mode = 'new';

            $this->data = $data;
        }

        return $this->data;
    }

    /**
     * Loads setup IDs of a specific raid
     * @param int $id_raid
     */
    public function getIdsByRaid($id_raid)
    {
        // get the setups
        $this->setField('id_setup');
        $this->setFilter('id_raid={int:id_raid}');
        $this->setParameter('id_raid', $id_raid);
        $this->setOrder('position, id_setup');
        return $this->read('keysonly');
    }

    /**
     * Creates and stores a new setup for a specific raid and returns the complete data of it.
     * @param int $id_raid
     */
    public function createDefaultSetup($id_raid)
    {
        $data = new Data();

        $data->id_raid = $id_raid;
        $data->title = $this->cfg('setup_title');
        $data->description = $this->cfg('setup_notes');
        $data->need_tank = $this->cfg('setup_tank');
        $data->need_damage = $this->cfg('setup_damage');
        $data->need_heal = $this->cfg('setup_heal');
        $data->position = 0;
        $data->points = 0;
        $data->killed = 0;

        $this->data = $data;

        // Save without validation
        $this->save(false);

        return $this->data;
    }

    /**
     * Deletes all setups for a specific raid
     * @param int $id_raid
     */
    public function deleteByRaid($id_raid)
    {
        $this->setFilter('id_raid={int:pk}');
        $this->setParameter('pk', $id_raid);
        $this->delete();
    }

    /**
     * Returns data of a specific setup.
     * @param int $id_setup
     * @return \Web\Framework\Lib\Data
     */
    public function getInfos($id_setup)
    {
        // Get setupdata
        $data = $this->find($id_setup);

        // How many setups?
        $this->setField('COUNT(setups.id_setup) AS num_setups');
        $this->setFilter('id_raid={int:id_raid}');
        $this->setParameter('id_raid', $this->data->id_raid);
        $this->read('ext');

        // Build complete headline
        if ($this->data->need_tank || $this->data->need_damage || $this->data->need_heal)
            $this->data->title .= ' (' . $this->data->need_tank . '/' . $this->data->need_damage . '/' . $this->data->need_heal . ')';

        return $this->data;
    }

    /**
     * Saves setup data to db
     * @param Data $data
     */
    public function saveSetup(Data $data)
    {
        $this->setData($data);

        // What edit mode do we have?
        $mode = isset($this->data->id_setup) ? 'update' : 'new';

        // We need to check a change in setup position (only on updates. new setups will be added to the end.)
        if (isset($this->data->id_setup))
            $position_is_same = $this->compare('position');

        // Save dataset to db
        $this->save();

        if (!$this->hasErrors() && $mode == 'new')
            // For lazy raidadmins we copy the setlist of the setup we came from as the edit startet to the new setup we created
            $this->getModel('Setlist')->copySetlist($this->data->id_from, $this->data->id_setup);

        // If the posiotion of the setup has been changed, we flag the mode to 'new' so the controller
        // reloads the complete setuplist.
        if ($position_is_same == false)
            $mode = 'new';
    }

    /**
     * Returns the all setup IDs of all future raids
     * @return Data List of setup IDs
     */
    public function getFutureSetupIDs()
    {
        return $this->setField('setups.id_setup')
                    ->setJoin('app_raidmanager_raids', 'raids', 'INNER', 'setups.id_raid = raids.id_raid')
                    ->setFilter('raids.starttime>{int:starttime}')
                    ->setParameter('starttime', time())
                    ->read('keysonly');
    }
}
?>
