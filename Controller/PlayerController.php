<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Lib\Url;
use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Helper\FormDesigner;
use Web\Framework\Html\Elements\Link;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Player Controller
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage Global
 * @license BSD
 * @copyright 2014 by author
 * @final
 */
final class PlayerController extends Controller
{
    // Accessmanagment on actions
    protected $access = array(
    	'*' => 'raidmanager_perm_player'
    );

    /**
     * Complete player overview
     */
    public function Complete()
    {
        $this->Create();
        $this->Playerlist('old');
        $this->Playerlist('applicant');
        $this->Playerlist('inactive');
        $this->Playerlist('active');

        $this->ajax->setTarget('#raidmanager');
    }

    /**
     * Playerlist for a special playertype
     * @param string $type
     */
    public function Playerlist($type)
    {
        $this->setVar(array(
            $type . '_headline' => $this->txt('playerlist_headline_' . $type),
            'empty_list' => $this->txt('playerlist_empty'),
            $type . '_data' => $this->model->getPlayerlist($type),
            $type . '_count' => $this->model->countData()
        ));

        // Targetdefinition for ajax response
        $this->ajax->setTarget('#raidmanager_playerlist_' . $type);
    }

    /**
     * Single player
     * @param int $id_player
     */
    public function Index($id_player)
    {
        $this->setVar('player', $this->model->getPlayer($id_player));
        $this->ajax->setTarget('#raidmanager_player_' . $id_player);
    }

    /**
     * Player creation
     */
    public function Create()
    {
        // This only reacts if there are user without a player profile
        if ($this->app->getModel('Member')->countNoProfile() == 0)
            return;

        // -----------------------------
        // Save/create player?
        // -----------------------------

        // Get posted playerdate
        $post = $this->request->getPost();

        // and create the player if there is posted data
        if ($post)
        {
            // Playercreation
            $this->model->createPlayer($post);

            // No errors on player creation means that we do not need the model data anymore
            // so we reset the model including the data set on save.
            if ($this->model->hasNoErrors())
            {
                // Inform user about successful playercreation
                $this->message->success($this->txt('player_created'));

                // Clear posted data
                $this->request->clearPost();

                // Reset model incl set data
                $this->model->reset(true);

                // Reload the pagecontent
                $this->run('Complete');
                return;
            }
        }

        // Set headline text to view
        $this->setVar('headline', $this->txt('player_create'));

        // ------------------------------
        // Form
        // ------------------------------
        $form = new FormDesigner();

        $form->attachModel($this->model);

        // Route for form post action
        $form->setActionRoute('raidmanager_player_add');

        // We use actionbars as buttons, so disable the automatic send button
        $form->noButtons();

        // Smf user selection
        $form->createElement('dataselect', 'id_player')->setLabel($this->txt('player_smfuser'))->setDataSource('Raidmanager', 'Member', 'getNoProfile');

        // Create form fields
        $form->createElement('text', 'char_name');
        $form->createElement('dataselect', 'id_class')->setDataSource('Raidmanager', 'Charclass', 'getClasses');
        $form->createElement('dataselect', 'id_category')->setDataSource('Raidmanager', 'Category', 'getCategories');
        $form->createElement('switch', 'autosignon');

        // Publish form to view
        $this->setVar('form', $form);

        // ------------------------------
        // Actionbar
        // ------------------------------
        $actionbar = new Actionbar();
        $actionbar->createButton('save')->setForm($form->getId());
        $this->setVar('actionbar', $actionbar);

        // ------------------------------
        // Ajax
        // ------------------------------
        $this->ajax->setTarget('#raidmanager_player_create');
    }

    /**
     * Player editing
     * @param int $id_player
     */
    public function Edit($id_player)
    {
        // ------------------------------
        // Save edited player?
        // ------------------------------
        $post = $this->request->getPost('Raidmanager', 'Player');

        if ($post)
        {
            $this->model->savePlayer($post);

            if ($this->model->hasNoErrors())
            {
                $this->Redirect($this->model->data->action, array(
                    'id_player' => $id_player
                ));
                return;
            }
        }

        // No data in model? Load it!
        if ($this->model->hasNoData())
            $this->model->getPlayer($id_player, false);

            // Publish playerdata to view
        $this->setVar('player', $this->model);

        // Our headline is a class coloured charname enclosed of a link
        // to the smf profile of the player
        $url = Url::factory()->setAction('profile')->addParameter('area', 'profile')->addParameter('u', $id_player)->getUrl();

        $link = Link::factory()->setInner('<span class="raidmanager_class_' . $this->model->data->class . '">' . $this->model->data->char_name . '</span>')->setTitle($this->txt('player_smfprofile'))->setTarget('_blank')->setHref($url)->build();

        $this->setVar('headline', $link);

        // Formdesigner
        $form = $this->getFormDesigner();

        $form->extendName($this->model->data->id_player);

        $form->setActionRoute($this->request->getCurrentRoute(), array(
            'id_player' => $id_player
        ));

        // We use actionbars as buttons, so disable the automatic send button
        $form->noButtons();

        // Form fields
        $form->createElement('hidden', 'id_player');
        $form->createElement('hidden', 'char_name');
        $form->createElement('switch', 'autosignon')->hasCompare(true);

        // Playerstate is a select field
        $control = $form->createElement('select', 'state');

        $states = array(
            0 => 'old',
            1 => 'applicant',
            2 => 'inactive',
            3 => 'active'
        );

        foreach ( $states as $val => $state )
            $control->newOption($val, $this->txt('player_state_' . $state), $this->model->data->state == $val ? 1 : 0);

            // Playerstate compare for changes
        $control->hasCompare(true);

        // Publish form to view
        $this->setVar('form', $form);

        // ------------------------------
        // Actionbar
        // ------------------------------
        $actionbar = new Actionbar();

        $params = array(
            'id_player' => $id_player
        );

        // cancel button
        $actionbar->createButton('cancel')->setRoute('raidmanager_player_index', $params);

        // save button
        $actionbar->createButton('save')->setForm($form->getId())->setRoute('raidmanager_player_edit', $params);

        $this->setVar('actionbar', $actionbar);

        // ------------------------------
        // External data
        // ------------------------------
        $this->setVar('charlist', $this->getController('Char')->run('Index'));

        // ------------------------------
        // Ajax response
        // ------------------------------
        $this->ajax->setTarget('#raidmanager_player_' . $id_player);
    }

    /**
     * Handles save process after player editing
     */
    public function Save($id_player)
    {
    }

    /**
     * Deletes a player from the playertable and from all tables whre it's id is
     * stored in
     */
    public function Delete($id_player)
    {
        // Delete the player
        $this->model->deletePlayer($id_player);

        // Delete is complete. reload the playerlist.
        $this->run('Complete');
    }
}
?>