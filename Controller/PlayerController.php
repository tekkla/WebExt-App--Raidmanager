<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Lib\Url;
use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Helper\FormDesigner;
use Web\Framework\Html\Elements\Link;
use Web\Framework\Lib\Menu;

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

        $this->setAjaxTarget('#raidmanager');
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
        $this->setAjaxTarget('#raidmanager_playerlist_' . $type);
    }

    /**
     * Single player
     * @param int $id_player
     */
    public function Index($id_player)
    {
        $this->setVar('player', $this->model->getPlayer($id_player));
        $this->setAjaxTarget('#raidmanager_player_' . $id_player);
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
        $this->setAjaxTarget('#raidmanager_player_create');
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
                // Refresh menu when user becomes active or when he becomes inactive
                if ($this->model->data->state == 3 || ($this->model->data->state_compare == 3 && $this->model->data->state != 3))
                    Menu::refreshMenu();

                $this->run($this->model->data->action, array(
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

        // Our headline is a class coloured charname enclosed by a link to the smf profile of the player
        $url = Url::factory()->getUrl(array(
            'action' => 'profile',
            'param' => array(
                'area' => 'profile',
                'u' => $id_player
            )
        ));
        $link = Link::factory()->setInner('<span class="raidmanager_class_' . $this->model->data->class . '">' . $this->model->data->char_name . '</span>')->setTitle($this->txt('player_smfprofile'))->setTarget('_blank')->setHref($url)->build();
        $this->setVar('headline', $link);

        // Formdesigner
        $form = $this->getFormDesigner();

        // Extend fieldnames by the player id
        $form->extendName($this->model->data->id_player);

        // Set forms action by route
        $form->setActionRoute($this->request->getCurrentRoute(), array(
            'id_player' => $id_player
        ));

        // We use actionbars as buttons, so disable the automatic send button
        $form->noButtons();

        // Some hidden fields
        $form->createElement('hidden', 'id_player');
        $form->createElement('hidden', 'char_name');
        $form->createElement('switch', 'autosignon')->setCompare($this->model->data->autosignon);

        // Playerstate is a select field
        $control = $form->createElement('select', 'state');

        // These playerstates are available
        $states = array(
            0 => 'old',
            1 => 'applicant',
            2 => 'inactive',
            3 => 'active'
        );

        // Adding all states as option
        foreach ( $states as $val => $state )
            $control->newOption($val, $this->txt('player_state_' . $state), $this->model->data->state == $val ? 1 : 0);

        // Playerstate compare for changes
        $control->setCompare($this->model->data->state);

        // Publish form to view
        $this->setVar('form', $form);

        // Create new actionbar
        $actionbar = new Actionbar();

        // General actionbar parameter
        $param = array(
            'id_player' => $id_player
        );

        // Cancel button
        $actionbar->createButton('cancel')->setRoute('raidmanager_player_index', $param);

        // Save button
        $actionbar->createButton('save')->setForm($form->getId())->setRoute('raidmanager_player_edit', $param);

        // Publish actionbar to view
        $this->setVar('actionbar', $actionbar);

        // Get result of charlist controller and publih it to view
        $this->setVar('charlist', $this->getController('Char')->run('Index'));

        // Ajax response target
        $this->setAjaxTarget('#raidmanager_player_' . $id_player);
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
