<?php

namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class CharView extends View
{
	public function Index()
	{
		echo '<div id="', $this->frame_id, '" class="panel panel-default" style="position: relative;">', $this->Charlist(), '</div>';
	}

	public function Charlist()
	{
		echo '
		<div class="panel-heading">
			<h4 class="panel-title">', $this->headline, '</h4>
			', $this->actionbar, '
		</div>
		<div class="panel-body">
			<ul class="list-group">';

			foreach ($this->charlist as $char)
				echo '
				<li class="list-group-item" id="raidmanager_char_',  $char->id_char, '">', $char->category, ' <span class="app-raidmanager-class-', $char->class . '">', $char->char_name, '</span>', $char->actionbar, '</li>';

			echo '
			</ul>
		</div>';
	}

	public function Edit()
	{
		echo '
		<div class="panel-heading">
			<h4 class="panel-title">', $this->headline, '</h4>
			', $this->actionbar, '
		</div>
		<div class="panel-body">', $this->form, '</div>';
	}
}
?>