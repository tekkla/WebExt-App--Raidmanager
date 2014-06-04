<?php

namespace Web\Apps\Raidmanager\Model;

// used libs
use Web\Framework\Lib\Model;
use Web\Framework\Lib\Url;


class StatsModel extends Model
{
	public function calcInitStats()
	{

	}

	public function getMonths()
	{
		$data = $this->factory('Raidmanager' , 'Raid')
						->setAlias('r')
						->setField(array(
							'CONCAT_WS(\'-\', FROM_UNIXTIME(r.starttime,\'%Y\'), FROM_UNIXTIME(r.starttime,\'%m\')) as id_period',
							'FROM_UNIXTIME(r.starttime,\'%Y\') as start_year',
							'FROM_UNIXTIME(r.starttime,\'%m\') as start_month',
							'Sum(if(s.state = 1, 1,0)) / count(s.id_subscription) * 100 as signed_on_perc',
							'Sum(if(s.state <> 1, 1,0)) / count(s.id_subscription) * 100  as signed_off_perc'
						))
						->addJoin('app_raidmanager_subscriptions', 's', 'INNER', 's.id_raid=r.id_raid')
						->setFilter('r.deleted=0 AND FROM_UNIXTIME(r.starttime, "%Y-%m-%d") < CURDATE()')
						->setGroupBy(array(
							'CONCAT_WS(\'-\', FROM_UNIXTIME(r.starttime,\'%Y\'), FROM_UNIXTIME(r.starttime,\'%m\'))',
							'FROM_UNIXTIME(r.starttime,\'%Y\')',
							'FROM_UNIXTIME(r.starttime,\'%m\')'
						))
						->setOrder('r.starttime DESC')
						->read('*');


		foreach ($data as $record)
		{
			$record->url = Url::factory('raidmanager_stats_subs', array('month' => $record->start_month, 'year' => $record->start_year))->isAjax()->getUrl();
		}

		return $data;

	}


	public function getRaidStats($month, $year)
	{
		$data = $this->factory('Raidmanager', 'Raid')
						->setAlias('r')
						->setField(array(
							'r.id_raid',
							'r.destination',
							'r.raidweek',
							'r.starttime',
							'Count(s.id_player) as count_player',
							'FROM_UNIXTIME(r.starttime,\'%Y\') as start_year',
							'FROM_UNIXTIME(r.starttime,\'%d\') as start_day',
							'FROM_UNIXTIME(r.starttime,\'%H\') as start_hour',
							'FROM_UNIXTIME(r.starttime,\'%i\') as start_minute',
							'Sum(if(s.state = 1, 1, 0))  as signed_on_num',
							'Sum(if(s.state <> 1, 1,0))  as signed_off_num',
							'Sum(if(s.state = 1, 1,0)) / count(s.id_subscription) * 100 as signed_on_perc',
							'Sum(if(s.state <> 1, 1,0)) / count(s.id_subscription) * 100  as signed_off_perc'
						))
						->addJoin('app_raidmanager_subscriptions', 's', 'INNER', 's.id_raid=r.id_raid')
						->setFilter('r.deleted=0 AND FROM_UNIXTIME(r.starttime,\'%Y\')={int:year} AND FROM_UNIXTIME(r.starttime,\'%m\')={int:month} AND FROM_UNIXTIME(r.starttime, "%Y-%m-%d") < CURDATE()')
						->setParameter(array(
							'year' => (int) $year,
							'month' => (int) $month,
						))
						->setGroupBy(array(
							'r.id_raid',
							'r.destination',
							'r.starttime'
						))
						->setOrder('r.starttime DESC')
						->read('*');


		// load player by substate
		foreach ($data as $raid)
		{
			$raid->sub_on = $this->factory('Raidmanager', 'Subscription')->getBySubsstate($raid->id_raid, 1);
			$raid->sub_off = $this->factory('Raidmanager', 'Subscription')->getBySubsstate($raid->id_raid, 2);
			$raid->sub_unknown = $this->factory('Raidmanager', 'Subscription')->getBySubsstate($raid->id_raid, 0);
		}

		return $data;
	}


}
?>