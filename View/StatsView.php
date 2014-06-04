<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class StatsView extends View
{

	public function index()
	{
		$html = '
		<div class="grid_12" id="raidmanager_stats">
			<h3>Raidstats</h3>
			<table class="table_grid" style="width: 100%">
				<tbody>';

				$year = 0;

				foreach ($this->periods as $period)
				{
					if ($year != $period->start_year)
						$html .= '<h4>' . $period->start_year . '</h4>';

					$html .= '
					<tr class="raidmanager_stats_period web_ajax" id="raidmanager_stats_period_' . $period->id_period . '" data-year="' . $period->start_year . '" data-month="' . $period->start_month . '" data-id_period="' . $period->id_period . '" data-url="' . $period->url . '">
						<td colspan="2">' . $period->start_month . '</td>
						<td width="15%">' . round($period->signed_on_perc) . '%</td>
						<td width="15%">' . round($period->signed_off_perc) . '%</td>
					</tr>';

					$year = $period->start_year;
				}

				$html .= '
				</tbody>
			</table>
		</div>';

		return $html;

	}


	public function subs()
	{
		$html = '
		<tr id="raidmanager_stats_'. $this->month . '_' . $this->year . '">';


		foreach ($this->raids as $raid)
		{
			$html .= '
			<tr class="raidstats_day" data-id_raid="' . $raid->id_raid . '">
				<td>' . timeformat($raid->starttime) . '</td>
				<td>' . $this->createSublist($raid) .'</td>
				<td>' . round($raid->signed_on_perc) . '%</td>
				<td>' . round($raid->signed_off_perc) . '%</td>
			</tr>';
		}

		$html .= '
		</tr>';

		return $html;
	}


	private function createSublist($raid)
	{

		$states = array(
			'sub_on',
			'sub_off',
			'sub_unknown'
		);


		$html = '';

		foreach ($states as $state)
		{

			if (!$raid->{$state})
				continue;

			$html .= '
			<div class="raidmanager_stats_' . $state . '">
				<span class="raidmanager_stats_substate">' . $this->{$state} .' (' . count((array)$raid->{$state}) . '):</span>';

			foreach ($raid->{$state} as $player)
				$html .= '
				<span class="raidmanager_class_' .  $player->class . ' web_ajax" data-url="">' . $player->char_name . '</span>';

			$html .= '
			</div>';

		}


		return $html;
	}

}
?>