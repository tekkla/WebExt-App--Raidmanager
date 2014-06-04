<?php
namespace Web\Apps\Raidmanager\Controller;

use Web\Framework\Lib\Controller;

class StatsController extends Controller
{
	public function Index()
	{
		$this->setVar('periods', $this->model->getMonths());
	}


	public function Subs($month, $year)
	{
		$this->setVar(array(
			'raids' => $this->model->getRaidStats($month, $year),
			'month' => $month,
			'year'  => $year
		));

		$this->ajax->setMode('after')->setTarget('#raidmanager_stats_period_' . $year . '-' . $month);

	}


}
?>