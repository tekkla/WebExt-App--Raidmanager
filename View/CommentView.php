<?php
namespace Web\Apps\Raidmanager\View;

use Web\Framework\Lib\View;

class CommentView extends View
{
	public function Index()
	{
		echo  '
		<div class="panel panel-default">
			<div class="panel-body">
				<h3 class="no-top-margin">', $this->headline, '</h3>';

				echo $this->actionbar;

				// no comments to show
				if($this->comments->count() == 0)
				{
					echo '<p>', $this->empty, '</p></div></div>';
					return;
				}

				// create commenttable
				echo '
				<div class="app-raidmanager-commentlist clearfix">';

				foreach($this->comments as $comment)
				{
					echo '
					<div class="panel panel-default">
						<div class="panel-body">';

						if ($comment->delete)
							echo $comment->delete;

							echo '
							<div class="app-raidmanager-comment">
								<span class="app-raidmanager-class-', $comment->class, ' ">', $comment->char_name, ' <small>(', date('Y-m-d H:i', $comment->stamp), ')</small></span>
								<div class="app-raidmanager-commenttext text-' . $comment->color_msg . '">', $comment->msg, '</div>
							</div>
						</div>
					</div>';
				}

				echo '
				</div>
			</div>
		</div>';
	}
}
?>