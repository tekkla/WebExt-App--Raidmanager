<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;


class PlayerView extends View
{

	public function Complete()
	{
		echo '
		<div class="col-sm-6">';

			if (isset($this->form))
				echo'<div id="raidmanager_player_create" class="raidmanager_block">',  $this->Create(),  '</div>';

			echo '
			<div id="raidmanager_playerlist_applicant" class="raidmanager_block">',  $this->Playerlist('applicant'),  '</div>
			<div id="raidmanager_playerlist_inactive" class="raidmanager_block">',  $this->Playerlist('inactive'),  '</div>
			<div id="raidmanager_playerlist_old" class="raidmanager_block">',  $this->Playerlist('old'),  '</div>
		</div>
		<div class="col-sm-6">
			<div id="raidmanager_playerlist_active" class="raidmanager_block">',  $this->Playerlist('active'),  '</div>
		</div>';
	}


	public function Playerlist($type)
	{
		// headline
		echo '
		<h3 class="no-top-margin">',  $this->{$type. '_headline'},  ' (',  $this->{$type . '_count'},  ')</h3>
		<ul class="list-group">';

		// no data to show, show empty text
		if($this->{$type . '_count'} == 0)
		{
			echo '
				<li class="list-group-item">' . $this->empty_list . '</li>
			</ul>';

		}

		// show the players!
		if ($this->{$type . '_data'})
		{
		    foreach($this->{$type . '_data'} as $player)
		        echo '<li class="list-group-item" id="raidmanager_player_', $player->id_player ,'">', $this->Index($player), '</li>';
		}

		echo '
		</ul>';
	}

	public function Index($player=null)
	{
		if (!isset($player))
			$player = $this->player;

		echo '
		<span class="app-raidmanager-player-info">', $player->category, '&nbsp;', $player->x_on_autosignon, '</span>
		<a href="?action=profile;area=account;u=', $player->id_player, '" target="_blank" title="Raider ID: ', $player->id_player, '">
			<span class="app-raidmanager-class-', $player->class, '">', $player->char_name, '</span>
		</a>
		', $player->actionbar;
	}



	public function Edit()
	{
		echo '
		<h3 class="no-top-margin">', $this->headline, '</h3>
		', $this->actionbar, '
		<div style="margin-bottom: 20px;">', $this->form, '</div>
		', $this->charlist;
	}


	public function Create()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">', $this->headline, $this->actionbar, '</h3>
			</div>
			<div class="panel-body">',  $this->form, '</div>
		</div>';
	}
}
?>