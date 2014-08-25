<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Html\Controls\Actionbar;

if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

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
    protected $validate = array(
        'msg' => array(
            'empty',
            array('range', array(10, 100))
        )
    );

    /**
     * Loads and returns all comments of a specific raid
     * @param int $id_raid
     * @return boolean|Data
     */
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
                array(
                    'app_raidmanager_chars',
                    'chars',
                    'INNER',
                    'comment.id_player=chars.id_player'
                ),
                array(
                    'app_raidmanager_classes',
                    'classes',
                    'INNER',
                    'chars.id_class=classes.id_class'
                )
            ),
            'filter' => 'comment.id_raid={int:id_raid} AND chars.is_main=1',
            'param' => array(
                'id_raid' => $id_raid
            ),
            'order' => 'stamp DESC'
        ), 'extendComment');
    }

    /**
     * Callback method to extend a comment with actionbar
     * @param Data $comment
     * @return Data
     */
    final protected function extendComment(&$comment)
    {
        // create delete button
        if ($this->checkAccess('raidmanager_perm_subs'))
        {
            $param = array(
                'id_comment' => $comment->id_comment,
                'id_raid' => $comment->id_raid
            );

            $actionbar = new Actionbar();
            $actionbar->createButton('delete')->setRoute('raidmanager_comment_delete', $param);
            $comment->delete = $actionbar->build();
        }

        // text color by comment type
        switch ($comment->state)
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

    /**
     * Deletes all comments of a specific player
     * @param int $id_player
     */
    public function deleteByPlayer($id_player)
    {
        $this->delete(array(
            'filter' => 'id_player={int:id_player}',
            'param' => array(
                'id_player' => $id_player
            )
        ));
    }

    /**
     * Deletes all comments of a specific player with specific player state
     * @param int $id_player
     * @param int $state
     */
    public function deleteByPlayerAndState($id_player, $state)
    {
        if (!is_array($state))
            $state = array(
                $state
            );

        $this->delete(array(
            'filter' => 'id_player={int:id_player} AND state IN ({array_int:state})',
            'param' => array(
                'id_player' => $id_player,
                'state' => $state
            )
        ));
    }

    /**
     * Creates a comment using the provided data
     * @param unknown $data
     */
    public function createComment($data)
    {
        $this->data = $data;
        $this->save();

        // on resign or enroll delete previous comments with opposing state
        if (!$this->hasErrors() && $this->data->state != 0)
            $this->delete(array(
                'filter' => 'id_raid={int:id_raid} AND id_player={int:id_player} AND state={int:state}',
                'param' => array(
                    'id_raid' => $this->data->id_raid,
                    'id_player' => $this->data->id_player,
                    'state' => $this->data->state == 1 ? 2 : 1
                )
            ));
    }
}
?>
