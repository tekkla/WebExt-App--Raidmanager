<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Member model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class MemberModel extends Model
{
    protected $tbl = 'members';
    protected $alias = 'mem';
    protected $pk = 'id_member';

    /**
     * Returns an array of player ids and names from users without a raidmanageer profile.
     *
     * Returns boolean false when no user were found.
     * @return Ambigous boolean|array
     */
    public function getNoProfile()
    {
        return $this->read(array(
            'type' => '2col',
            'field' => array(
                'mem.id_member',
                'IFNULL(mem.real_name,mem.member_name) as username'
            ),
            'join' => array(
                array(
                    'app_raidmanager_players',
                    'player',
                    'LEFT OUTER',
                    'mem.id_member = player.id_player'
                )
            ),
            'filter' => 'player.id_player IS NULL',
            'order' => 'mem.real_name ASC'
        ));
    }

    /**
     * Counts the user without a raidmanager profile and returns it.
     * @return Ambigous int
     */
    public function countNoProfile()
    {
        return $this->read(array(
            'type' => 'num',
            'join' => array(
                array(
                    'app_raidmanager_players',
                    'player',
                    'LEFT OUTER',
                    'mem.id_member = player.id_player'
                )
            ),
            'filter' => 'player.id_player IS NULL'
        ));
    }
}
?>
