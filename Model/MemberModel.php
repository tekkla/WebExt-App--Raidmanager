<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

class MemberModel extends Model
{
	public $tbl = 'members';
	public $alias = 'mem';
	public $pk = 'id_member';

	public function getNoProfile()
	{
		$this->setField(array(
			'mem.id_member',
			'IFNULL(mem.real_name,mem.member_name) as username'
		));
		$this->setJoin('app_raidmanager_players', 'player', 'LEFT OUTER', 'mem.id_member = player.id_player');
		$this->setFilter('player.id_player IS NULL');
		$this->setOrder('mem.real_name ASC');
		return $this->read('2col');
	}

	public function countNoProfile()
	{
		$this->setJoin('app_raidmanager_players', 'player', 'LEFT OUTER', 'mem.id_member = player.id_player');
		$this->setFilter('player.id_player IS NULL');
		return $this->count();
	}
}
?>