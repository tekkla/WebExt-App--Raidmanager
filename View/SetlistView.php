<?php

namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class SetlistView extends View
{

	public function Complete()
	{
		$this->Index();
		$this->Waitlist();
	}

	public function Index()
	{
		echo '
		<div class="clearfix">
			<div class="app-raidmanager-setlist-tank">
				<h5><strong>', $this->headline_tank, '</strong></h5>
				', $this->createSetlist('tank'), '
			</div>
			<div class="app-raidmanager-setlist-damage">
				<h5><strong>', $this->headline_damage, '</strong></h5>
				', $this->createSetlist('damage'), '
			</div>
			<div class="app-raidmanager-setlist-heal">
				<h5><strong>', $this->headline_heal, '</strong></h5>
				', $this->createSetlist('heal'), '
			</div>
		</div>';
	}


	public function Waitlist()
	{
		if ($this->count == 0)
			return;

		echo '
		<div class="row">
			<div class="app-raidmanager-waitlist col-sm-12">
				<p><strong>', $this->notset, ' (', $this->availlist->count() , '):</strong> ';

				foreach ($this->availlist as $char)
					echo '<span class="app-raidmanager-class-' . $char->class . '">' . $char->char_name . '</span> ';

				echo '
				</p>
			</div>
		</div>';
	}

	public function Availlist()
	{
		echo '
		<h5>', $this->notset, '</h5>
		<div class="clearfix">
			<div class="raidmanager_waitlist_tank">
				', $this->createWaitlist('tank'), '
			</div>
			<div class="web_cf raidmanager_waitlist_damage">
				', $this->createWaitlist('damage'), '
			</div>
			<div class="web_cf raidmanager_waitlist_heal">
				', $this->createWaitlist('heal'), '
			</div>
		</div>';
	}

	private function createSetlist($category)
	{
		if (isset($this->setlist->{$category}))
		{
			echo '
			<ul class="list-unstyled">';

			foreach ($this->setlist->{$category} as $char)
				echo '
				<li class="app-raidmanager-list-item">
					<span class="app-raidmanager-class-', $char->class, '">', $char->char_name, '</span>
				</li>';

			echo '
			</ul>';
		}
		else
		{
			echo '<p>', $this->noneset, '</p>';
		}
	}

	private function createWaitlist($category)
	{
		if (isset($this->availlist->{$category}))
		{
			echo '
			<ul class="list-inline app-raidmanager-waitlist-', $category, '">';

			foreach ($this->availlist->{$category} as $char)
				echo '
				<li>
					<span class="app-raidmanager-class-', $char->class, '">', $char->char_name, '</span>
				</li>';

			echo '
			</ul>';
		}
	}

	function Edit()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 class="raidmanager_headline raidmanager_underline no-top-margin">', $this->headline, $this->actionbar, '</h3>';

			$categories = array('tank','damage','heal');

			foreach ($categories as $category)
			{
				echo '
				<h4 class="">', $this->{'headline_' . $category}, '</h4>
				<div class="row">
					<div class="col-sm-6">
					', $this->createSelection('set', $category), '
					</div>
					<div class="col-sm-6">
					', $this->createSelection('avail', $category), '
					</div>
				</div>';
			}

			echo '
			</div>
		</div>';
	}

	private function createSelection($side, $category)
	{
		if(isset($this->{$side . '_'. $category}))
		{
			foreach($this->{$side . '_'. $category} as $player)
				echo '
				<div class="app-raidmanager-setlist-player app-raidmanager-class-', $player->class, '">', $player->char_name, $player->actionbar, '</div>';
		}
		else
			echo '<p>', $this->{'none_' . $side} .'</p>';
	}
}
?>