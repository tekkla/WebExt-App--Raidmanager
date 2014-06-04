<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;
use web\framework\Web;

class SubscriptionView extends View
{
	public function Index()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 class="no-top-margin">', $this->headline, '</h3>';

				echo $this->actionbar;

		foreach ($this->types as $type => $txt)
		{
			if ($this->isVar($txt))
			{
				echo '
			    <ul class="list-inline">
					<li>
						<strong>', $this->{'headline_'.$txt}, ' :</strong>
					</li>';

				foreach($this->{$txt} as $player)
					echo '
					<li>
						<span class="app-raidmanager-class-',  $player->class, '">', $player->char_name, '</span>
					</li>';

				echo '
				</ul>';
			}
		}

			echo '
			</div>
		</div>';
	}

	public function Enrollform()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 class="no-top-margin"', $this->color, '>', $this->headline, $this->actionbar, '</h3>
				', $this->enrollform, '
			</div>
		</div>';
	}

	function Edit()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 class="no-top-margin">', $this->headline, $this->actionbar, '</h3>
				<div class="pull-left" style="width:48%">
					<ul class="list-unstyled">';

				foreach($this->resigned as $player)
					echo '<li style="margin-bottom: 1px;">', $player->link, '</li>';

					echo '
					</ul>
				</div>
				<div class="pull-right" style="width:48%;">
					<ul class="list-unstyled">';

				foreach($this->enrolled as $player)
					echo '<li style="margin-bottom: 1px;">', $player->link, '</li>';

				echo '
					</ul>
				</div>
			</div>
		</div>';
	}
}
?>