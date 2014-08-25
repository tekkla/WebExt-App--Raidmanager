<?php

namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;
use Web\Framework\Lib\Error;
use Web\Framework\Lib\User;
use Web\Framework\Html\Controls\Actionbar;

final class SubscriptionController extends Controller
{
    public $access = array(
        'Edit' => 'raidmanager_perm_subs'
    );

    public function Index($id_raid)
    {
        // Create subscription lists and headlines
        $types = array(
            0 => 'noresponse',
            1 => 'enrolled',
            2 => 'resigned'
        );

        $this->setVar('types', $types);

        $counter = 0;

        foreach ( $types as $type => $txt )
        {
            // player with no ajax
            $this->model->getBySubsstate($id_raid, $type);

            if ($this->model->countData() > 0)
            {
                $this->setVar(array(
                    'headline_' . $txt => $this->txt('subscription_' . $txt . '_headline') . ' (' . $this->model->countData() . ')',
                    $txt => $this->model->data
                ));

                $counter++;
            }
        }

        // Headline and text
        $this->setVar(array(
            'headline' => $this->txt('subscription_headline'),
            'txt_nodata' => $this->txt('subscription_noplayer'),
            'nodata' => $counter == 0 ? true : false,
        ));

        // Actionbar
        if ($this->checkUserrights('raidmanager_perm_subs') === true)
        {
            $actionbar = new Actionbar();

            // Build delete button
            $actionbar->createButton('user')->setIcon('user')->setTitle($this->txt('raid_signon_change'))->setRoute('raidmanager_subscription_edit', array(
                'id_raid' => $id_raid
            ));

            // Publish actionbar
            $this->setVar('actionbar', $actionbar);
        }

        // Target of ajax result
        $this->setAjaxTarget('#raidmanager_subscriptions');
    }

    /**
     * The enrollform can be called by several actions.
     * You can use it as a simple commentform or as a form for
     * player enrolls and player resigns.
     */
    function Enrollform($id_subscription, $state, $from, $id_player = null)
    {
        // get the player id - if not set as request param, assume it is the current user
        $id_player = isset($id_player) ? $id_player : User::getId();

        // get the raid id of this subscription if no data is present
        $id_raid = $this->model->hasData() ? $this->model->data->id_raid : $this->model->getRaidId($id_subscription);

        // get the mainchar name of player
        $char_name = $this->getModel('Char')->getMaincharName($id_player);

        // select headline and bg color class
        switch ($state)
        {
            case 0 :
                $headline = $this->txt('comment_comment');
                break;

            case 1 :
                $headline = $char_name . ' ' . $this->txt('comment_enroll');
                $bg_class = 'text-success';
                break;

            case 2 :
                $headline = $char_name . ' ' . $this->txt('comment_resign');
                $bg_class = 'text-danger';
                break;
        }

        $this->setVar(array(
            'headline' => $headline,
            'placeholder' => $this->txt('comment_placeholder')
        ));

        if (isset($bg_class))
            $this->setVar('color', ' class="' . $bg_class . '"');

            // Get FormDesigner object
        $form = $this->getFormDesigner();

        // SOME FORMDATA
        $form->setActionRoute('raidmanager_subscription_save', array(
            'from' => $from,
            'id_raid' => $id_raid
        ));

        // No buttons please
        $form->noButtons();

        // Hidden subscription id
        $form->createElement('hidden', 'id_subscription')->setValue($id_subscription);

        // Hidden raid id
        $form->createElement('hidden', 'id_raid')->setValue($id_raid);

        // Hidden player id
        $form->createElement('hidden', 'id_player')->setValue($id_player);

        // Hidden state
        $form->createElement('hidden', 'state')->setValue($state);

        // Visible textarea for comment
        $form->createElement('textarea', 'msg')->setRows(2)->setPlaceholder($this->txt('comment_placeholder'))->noLabel();

        $form->setName($from);

        // publish form data to view
        $this->setVar('enrollform', $form);

        // ------------------------------
        // BUILD ICONS
        // ------------------------------

        switch ($from)
        {
            case 'comment' :
                $route = 'raidmanager_comment_index';
                $target = 'raidmanager_comments';
                break;

            case 'subscription' :
                $route = 'raidmanager_subscription_edit';
                $target = 'raidmanager_subscriptions';
                break;
        }

        // New actionbar
        $actionbar = new Actionbar();

        // If cancel parameter set in request and is 'back' we want to go back to the calling dialog when we cklick the cancel button
        $actionbar->createButton('cancel')->setRoute($route, array(
            'id_raid' => $id_raid
        ))->setTarget($target);

        // Save button
        $param = array(
            'id_raid' => $id_raid,
            'from' => $from
        );

        $actionbar->createButton('save')->setForm($form->getId())->setRoute('raidmanager_subscription_save', $param);

        // publish icons to view
        $this->setVar('actionbar', $actionbar);

        // where to place on ajax requests
        $this->setAjaxTarget('#' . $target);
    }

    public function Save($from, $id_raid)
    {
        $post = $this->request->getPost();

        if (!$post)
            Throw new Error('Data of raidmanager subscription could not be retreived.');

            // Save data
        $this->model->saveEnrollform($post);

        if ($this->model->hasErrors())
        {
            $this->run('Enrollform', array(
                'id_subscription' => $this->model->data->id_subscription,
                'state' => $this->model->data->state,
                'from' => $from,
                'id_player' => $this->model->data->id_player,
                'id_raid' => $id_raid
            ));
            return;
        }

        $param = array(
            'id_raid' => $id_raid
        );

        // back to subscription or Index?
        $this->getController('Subscription')->ajax($from == 'subscription' ? 'Edit' : 'Index', $param, '#raidmanager_subscriptions');

        // refresh commentlist
        $this->getController('Comment')->ajax('Index', $param, '#raidmanager_comments');

        // refresh setups only if the player subscribes or unsubscribes
        if ($this->model->data->state != 0)
            $this->getController('Setup')->ajax('Complete', $param, '#raidmanager_setups');
    }

    public function Edit($id_raid)
    {
        // cancel button
        $actionbar = new Actionbar();
        $actionbar->createButton('cancel')->setRoute('raidmanager_subscription_index', array(
            'id_raid' => $id_raid
        ));

        $this->setVar(array(
            'actionbar' => $actionbar,
            'headline' => $this->txt('subslist_headline'),
            'resigned' => $this->model->getEditSubscriptions($id_raid, 'resigned'),
            'enrolled' => $this->model->getEditSubscriptions($id_raid, 'enrolled')
        ));

        $this->setAjaxTarget('#raidmanager_subscriptions');
    }
}
?>
