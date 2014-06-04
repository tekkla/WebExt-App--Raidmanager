<?php
namespace Web\Apps\Raidmanager\View;


use Web\Framework\Lib\View;

class RaidView extends View
{
	public function Complete()
	{
		echo'
		<div id="raidmanager_calendar" class="col-sm-6">', $this->calendar, '</div>
		<div id="raidmanager_raid" class="col-sm-12">', $this->Index(), '</div>';
	}


	public function Index()
	{
		echo'
		<div class="row">
			<div class="col-sm-12">
				<div id="raidmanager_infos">', $this->Infos(), '</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div id="raidmanager_subscriptions">', $this->subscriptions, '</div>
				<div id="raidmanager_comments">	', $this->comments. '</div>
			</div>
			<div class="col-md-6">
				<div id="raidmanager_setups">', $this->setups, '</div>
			</div>
		</div>';
	}

	/**
	 * Creates the basic raidinformations
	 *
	 * @return string $html Data to display
	 */
	public function Infos()
	{
		echo'
		<div class="panel panel-default">
			<div class="panel-body">
				<h4 class="no-top-margin">', timeformat($this->data->starttime, true), '</h4>
				<h2 class="no-top-margin">', $this->data->destination .'</h2>';

				echo $this->actionbar;

			if ($this->isVar('topic_url'))
				echo '<a href="', $this->data->topic_url, '">', $this->txt_topiclink, '</a>';

			if($this->data->specials)
			{
				echo '
				<h4>', $this->txt_specials, '</h4>
				<p>', $this->data->specials, '</p>';
			}

			echo '
			</div>
		</div>';
	}

	public function Edit()
	{
		echo'
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 id="ajax" class="no-top-margin">', $this->headline, $this->actionbar, '</h3>
				', $this->form, '
			</div>
		</div>';
	}

	public function Autoadd()
	{
		echo'
		<section id="raidmanager_errors">
			<h3>Error on AutoAdd Raids</h3>
			', implode('<br>', $this->errors), '
		</section>';
	}
}
?>