<?php

namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Html\Controls\Actionbar;

class SetupController extends Controller
{
    protected $access = array(
        'Edit' => 'raidmanager_perm_setup',
        'Delete' => 'raidmanager_perm_setup'
    );

    public function Index($id_setup)
    {
        // create infos
        $this->Infos($id_setup);

        // create setlist
        $this->setVar('setlist_' . $id_setup, $this->getController('Setlist')->run('Complete', array(
            'id_setup' => $id_setup
        )));

        // ajax definition
        $this->setAjaxTarget('#raidmanager_setup_' . $id_setup);
    }

    public function Complete($id_raid)
    {
        $setup_keys = $this->model->getIdsByRaid($id_raid);

        $this->setVar('setup_keys', $setup_keys);

        foreach ( $setup_keys as $id_setup )
            $this->Index($id_setup);

        $this->setAjaxTarget('#raidmanager_setups');
    }

    public function Infos($id_setup)
    {
        $this->model->getInfos($id_setup);

        // ------------------------------
        // Actionbar
        // ------------------------------
        $actionbar = new Actionbar();

        $param = array(
            'id_raid' => $this->model->data->id_raid,
            'id_setup' => $this->model->data->id_setup,
            'back_to' => $this->model->data->id_setup
        );

        if ($this->checkAccess('raidmanager_perm_setup') === true)
        {
            // build edit button
            $actionbar->createButton('edit')->setRoute('raidmanager_setup_edit', $param);

            // build add setup button
            $actionbar->createButton('new')->setRoute('raidmanager_setup_add', $param);
        }

        // build add button
        if ($this->checkAccess('raidmanager_perm_setlist') === true && $this->getModel('Setlist')->countAvail($id_setup) > 0)
            $actionbar->createButton('setup')->setIcon('user')->setRoute('raidmanager_setlist_edit', $param);

        // delete button only if there is more than one setup
        if ($this->model->data->num_setups > 1 && $this->checkAccess('raidmanager_perm_setup') === true)
            $actionbar->createButton('delete')->setRoute('raidmanager_setup_delete', $param);

        // build bar if access allowed
        $this->model->data->actionbar = $actionbar->build();

        $this->setVar('infos_' . $id_setup, $this->model->data);
    }

    public function Edit($back_to, $id_raid, $id_setup = null)
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
                if ($this->model->data->mode == 'new' || $this->model->data->position != $this->model->data->old_position)
                    // this is a new setup. refresh the complete setup section
                    $this->run('complete', array(
                        'id_raid' => $this->model->data->id_raid
                    ));
                else
                    // this is an update. refresh only the setup
                    $this->run('index', array(
                        'id_setup' => $this->model->data->id_setup
                    ));

                return;
            }
        }

        // Do we need to load data?
        if ($this->model->hasNoData())
            $this->model->getEditSetup($id_raid, $id_setup);

            // Set headline text
        $this->setVar('headline', $this->txt('setup_' . $this->model->data->mode));

        // ------------------------------
        // FORM
        // ------------------------------

        // Prepare parameters
        $param = array(
            'back_to' => $back_to,
            'id_raid' => $id_raid
        );

        if (isset($id_setup))
            $param['id_setup'] = $id_setup;

        $form = $this->getFormDesigner();

        // Set forms action route
        $form->setActionRoute($this->request->getCurrentRoute(), $param);

        // We need no buttons
        $form->noButtons();

        // Hidden controls
        if (isset($this->model->data->id_setup))
            $form->createElement('hidden', 'id_setup');

        $form->createElement('hidden', 'id_raid');
        $form->createElement('hidden', 'mode');

        // Title input
        $form->createElement('text', 'title');

        // Description textarea
        $form->createElement('textarea', 'description')->setAutofocus()->setCols(40)->setRows(3);

        // Needed categories number inputs
        $categories = array(
            'tank',
            'damage',
            'heal'
        );

        foreach ( $categories as $cat )
            $form->createElement('number', 'need_' . $cat)->setSize(2)->setMaxlenght(2)->addAttribute('min', 0);

        // Other number inputs
        $fields = array(
            'points',
            'position'
        );

        foreach ( $fields as $fld )
            $form->createElement('number', $fld)->setSize(2)->setMaxlenght(2);

        // Send form to view
        $this->setVar('form', $form);

        // Actionbar
        $actionbar = new Actionbar();

        // Build cancel button
        $button = $actionbar->createButton('cancel');

        if (isset($id_raid))
            $button->setRoute('raidmanager_setup_complete', array(
                'id_raid' => $id_raid
            ));

        if ($id_setup)
            $button->setRoute('raidmanager_setup_index', array(
                'id_setup' => $id_setup
            ));

            // Build save button
        $param = array(
            'id_raid' => $id_raid,
            'back_to' => $back_to
        );

        $actionbar->createButton('save')->setForm($form->getId())->setRoute('raidmanager_setup_save', $param);

        // Build actionbar
        $this->setVar('actionbar', $actionbar);

        // Create ajax response
        $this->setAjaxTarget($id_setup ? '#raidmanager_setup_' . $id_setup : '#raidmanager_setups');
    }

    public function Delete($id_setup, $id_raid)
    {
        // First delete setlists
        $this->getModel('Setlist')->deleteBySetup($id_setup);

        // Then the setup
        $this->model->delete($id_setup);

        // Reload setup area
        $this->ajax('Complete', array(
            'id_raid' => $id_raid
        ));
    }
}
?>
