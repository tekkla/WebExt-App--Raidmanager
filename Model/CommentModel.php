<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Html\Controls\Actionbar;

/**
 * Comment model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class CommentModel extends Model
{
	protected $tbl = 'app_raidmanager_comments';
	protected $alias = 'comment';
	protected $pk = 'id_comment';
 	public $validate = array(
		'msg' => array(
			'empty',
			array('range', array(5,100))
		)
	);

	public function getComments($id_raid)
	{
		return $this->read(array(
		    'type' => '*',
		    'field' => array(
		        'comment.id_comment',
		        'comment.id_raid',
		        'comment.id_player',
		        'comment.id_poster',
		        'comment.msg',
		        'comment.state',
		        'comment.stamp',
		        'classes.class',
		        'chars.char_name'
		    ),
		    'join' => array(
		        array('app_raidmanager_chars', 'chars', 'INNER', 'comment.id_player=chars.id_player'),
		        array('app_raidmanager_classes', 'classes', 'INNER', 'chars.id_class=classes.id_class'),
		    ),
		    'filter' => 'comment.id_raid={int:id_raid} AND chars.is_main=1',
		    'param' => array(
		        'id_raid' => $id_raid
		    ),
		    'order' => 'stamp DESC',
		), 'extendComment');
	}

	protected function extendComment(&$comment)
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

	    return $comment;
	}

	public function deleteByRaid($id_raid)
	{
		$this->delete(array(
			'filter' => 'id_raid={int:id_raid}',
		    'param' => array('id_raid' => $id_raid)
		));
	}

	public function deleteByPlayerAndRaid($id_raid, $id_player)
	{
		$this->delete(array(
			'filter' => 'id_raid={int:id_raid} AND id_player={int:id_player}',
		    'param' => array(
		        'id_raid' => $id_raid,
		        'id_player' => $id_player
		    )
		));
	}

	public function deleteByPlayer($id_player)
	{
		$this->delete(array(
			'filter' => 'id_player={int:id_player}',
		    'param' => array('id_player' => $id_player)
		));
	}

	public function deleteByPlayerAndState($id_player, $state)
	{
		if (!is_array($state))
			$state = array($state);

		$this->delete(array(
			'filter' => 'id_player={int:id_player} AND state IN ({array_int:state})',
			'param' => array(
    			'id_player' => $id_player,
    			'state' => $state
			)
		));
	}

	public function deleteByRaidPlayerAndState($id_raid, $id_player, $state)
	{
		$this->delete(array(
			'filter' => 'id_raid={int:id_raid} AND id_player={int:id_player} AND state={int:state}',
			'param' => array(
    			'id_raid' => $id_raid,
    			'id_player' => $id_player,
    			'state' => $state
			)
		));
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
	    $this->delete($id_comment);
	}
}
?>
