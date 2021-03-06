<?php
namespace Web\Apps\Raidmanager\Controller;


use Web\Framework\Lib\Controller;
use Web\Framework\Lib\User;

use Web\Framework\Html\Controls\Actionbar;


class CommentController extends Controller
{
	public $access = array(
		'Delete' => 'raidmanager_perm_subs',
	);

	public function Index($id_raid)
	{
		// -----------------------------------
		// Essential player and raid parameter
		// -----------------------------------
		$id_player = User::getId();

		// -----------------------------------
		// Button creation
		// -----------------------------------
		$buttons = array();

		// get subscription state of player
		$subscription = $this->getModel('Subscription')->getIdAndState($id_raid, $id_player);

		// create the enroll/resign and comment button
		$actionbar = new Actionbar();

		// player is enrolled (undefined), button is resignbutton
		if($subscription->state == 1 || $subscription->state == 0)
		{
			$actionbuttons[] = array(
				'state' => 2,
				'txt' => 'comment_resign',
				'img' => 'frown-o',
				'btn' => 'btn-danger',
			);
		}

		// player is not enrolled, button is enrollbutton
		if($subscription->state == 2 || $subscription->state == 0)
		{
			$actionbuttons[] = array(
				'state' => 1,
				'txt' => 'comment_enroll',
				'img' => 'smile-o',
				'btn' => 'btn-success',
			);
		}

		// add comment button
		$actionbuttons[] = array(
			'state' => 0,
			'txt' => 'comment_comment',
			'img' => 'comment'
		);

		foreach ($actionbuttons as $actbtn)
		{
			// create subscriptionlink
			$button = $actionbar->createButton($actbtn['txt'])->setIcon($actbtn['img'])->setTitle($this->txt($actbtn['txt']));

			if (isset($actbtn['color']))
				$button->addStyle('color', $actbtn['color']);

			if (isset($actbtn['btn']))
				$button->addCss($actbtn['btn']);

			$params = array(
				'id_subscription' => $subscription->id_subscription,
				'state' => $actbtn['state'],
				'from' => 'comment',
				'id_player' => $id_player,
				'id_raid' => $id_raid
			);
			$button->setRoute('raidmanager_subscription_enrollform', $params);
		}

		// create actionbar
		$this->setVar('actionbar', $actionbar);

		// --------------------------------
		// Comment data
		// --------------------------------
		$comments = $this->model->getComments( $id_raid );
		$this->setVar('comments', $comments);

		$this->setVar(array(
			'headline' => $this->txt('comment_headline') . ($comments ? ' (' . $comments->count() . ')' : ''),
			'empty' => $this->txt('comment_empty')
		));

		// -------------------------------
		// Ajax output definition
		// -------------------------------
		$this->ajax->setTarget('#raidmanager_comments');
	}

	public function Delete($id_comment, $id_raid)
	{
		// still here? seems all checks were ok.
		$this->model->deleteComment($id_comment);
		$this->run('index', array('id_raid' => $id_raid));
	}

}
?>