<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class CalendarView extends View
{
	public function Index()
	{
		echo '
		<div class="btn-group app-raidmanager-calendar-selection">
		', $this->calendar_by_type('future', 'left'), '
		', $this->calendar_by_type('recent', 'right'), '
		</div>';
	}

	public function calendar_by_type($type, $side='left')
	{
		// No raids to show, no list to show ;)
		if ($this->{'list_' . $type}->count() == 0)
			return;

		echo '
		<div class="btn-group btn-group-sm">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">', $this->{$type}, ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-' . $side .'">';

			foreach ($this->{'list_' . $type} as $link)
				echo '<li>', $link, '</li>';

			echo '
			</ul>
		</div>';
	}

	public function WidgetNextRaid()
	{
		echo '
		<div class="panel panel-info">
			<div class="panel-body">
				<strong>Nächster Raid:</strong> <a href="', $this->raid->url, '">', timeformat($this->raid->starttime), ' - ', $this->raid->destination, ' <span class="badge">', $this->raid->players, '</span></a>
			</div>
		</div>';
	}

}

?>