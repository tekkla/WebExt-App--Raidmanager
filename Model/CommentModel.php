<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Lib\User;

class CommentModel extends Model
{
	public $tbl = 'app_raidmanager_comments';
	public $alias = 'comment';
	public $pk = 'id_comment';

 	public $validate = array(
		'msg' => array(
			'empty',
			array('range', array(5,100))
		)
	);

	public function getComments($id_raid)
	{
		// get player id from context
		$id_player = User::getId();

		// get the comment
		$this->setField(array(
			'comment.id_comment',
			'comment.id_raid',
			'comment.id_player',
			'comment.id_poster',
			'comment.msg',
			'comment.state',
			'comment.stamp',
			'classes.class',
			'chars.char_name'
		));
		$this->addJoin('app_raidmanager_chars', 'chars', 'INNER', 'comment.id_player=chars.id_player');
		$this->addJoin('app_raidmanager_classes', 'classes', 'INNER', 'chars.id_class=classes.id_class');
		$this->setFilter('comment.id_raid={int:id_raid} AND chars.is_main=1');
		$this->setParameter('id_raid', $id_raid);
		$this->setOrder('stamp DESC');

		$this->read('*');

		if (!$this->hasData())
			return false;

		foreach ($this->data as $comment)
		{
			// create delete button
			if ($this->checkAccess('raidmanager_perm_subs'))
			{
				$params = array(
					'id_comment' => $comment->id_comment,
					'id_raid' => $comment->id_raid
				);

				$actionbar = new Actionbar();
				$actionbar->createButton('delete')->setRoute('raidmanager_comment_delete', $params);
				$comment->delete = $actionbar->build();
			}

			// text color by comment type
			switch($comment->state)
			{
				case 1 :
					$comment->color_msg = ' style="color: #00FF00;"';
					break;
				case 2 :
					$comment->color_msg = ' style="color: #FF0000;"';
					break;
				case 3 :
					$comment->color_msg = ' style="color: #2A8EFF;"';
					break;
				case 4 :
					$comment->color_msg = ' style="color: #2A8EFF;"';
					break;
				default :
					$comment->color_msg = '';
					break;
			}

			// add name of player if it's not the current player
			if ($comment->id_player != $comment->id_poster)
				$comment->msg .= ' (' . $this->app->getModel('Char')->getMaincharName($comment->id_poster) . ')';
		}

		return $this->data;
	}

	public function deleteByRaid($id_raid)
	{
		$this->setFilter('id_raid={int:id_raid}');
		$this->setParameter('id_raid', $id_raid);
		$this->delete();
	}

	public function deleteByPlayerAndRaid($id_raid, $id_player)
	{
		$this->setFilter('id_raid={int:id_raid} AND id_player={int:id_player}');
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'id_player' => $id_player
		));
		$this->delete();
	}

	public function deleteByPlayer($id_player)
	{
		$this->setFilter('id_player={int:id_player}');
		$this->setParameter(array(
				'id_player' => $id_player
		));
		$this->delete();
	}

	public function deleteByPlayerAndState($id_player, $state)
	{
		if (!is_array($state))
			$state = array($state);

		$this->setFilter('id_player={int:id_player} AND state IN ({array_int:state})');
		$this->setParameter(array(
			'id_player' => $id_player,
			'state' => $state
		));
		$this->delete();
	}

	public function deleteByRaidPlayerAndState($id_raid, $id_player, $state)
	{
		$this->setFilter('id_raid={int:id_raid} AND id_player={int:id_player} AND state={int:state}');
		$this->setParameter(array(
			'id_raid' => $id_raid,
			'id_player' => $id_player,
			'state' => $state
		));
		$this->delete();
	}

	public function createComment($data)
	{
		$this->setData($data)->save();

		// on resign or enroll delete previous comments with opposing state
		if (!$this->hasErrors() && $this->data->state != 0)
			$this->deleteByRaidPlayerAndState($this->data->id_raid, $this->data->id_player, ($this->data->state == 1 ? 2 : 1));
	}

	public function deleteComment($id_comment)
	{
		$this->setFilter('id_comment={int:id_comment}');
		$this->setParameter('id_comment', $id_comment);
		$this->delete();
	}
}
?>