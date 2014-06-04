<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class SetupView extends View
{
	public function Complete()
	{
		foreach($this->setup_keys as $id_setup)
			echo '
			<div id="raidmanager_setup_', $id_setup, '">', $this->Index($id_setup), '</div>';
	}

	public function Index($id_setup)
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<div id="raidmanager_setup_', $id_setup, '_info">', $this->Info($id_setup), '</div>
				<div id="raidmanager_setup_', $id_setup, '_player">
					', $this->{'setlist_' . $id_setup}, '
				</div>
			</div>
		</div>';
	}


	public function Info($id_setup)
	{
		echo $this->{'infos_'. $id_setup}->actionbar;

		echo '<h3 class="no-top-margin">', $this->{'infos_' . $id_setup}->title, '</h3>';

		if($this->{'infos_' . $id_setup}->description)
			echo '<p class="raidmanager_setup_description">', parse_bbc($this->{'infos_' . $id_setup}->description), '</p>';
	}

	function Edit()
	{
		echo '
		<div class="panel panel-default">
			<div class="panel-body">
				<div class="raidmanager_edit raidmanager_section">';

					echo $this->actionbar;

					echo '
					<h3 class="no-top-margin">', $this->headline, '</h3>';

					echo $this->form;

				echo '
				</div>
			</div>
		</div>';
	}
}
?>