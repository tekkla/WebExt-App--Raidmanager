<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

use Web\Framework\Html\Controls\Actionbar;
use Web\Framework\Lib\Data;

class CharModel extends Model
{
	public $tbl = 'app_raidmanager_chars';
	public $alias = 'chars';
	public $pk = 'id_char';

	public $validate = array(
		'char_name' => array('required', 'empty', array('min', 3)),
		'id_player' => array('required', 'empty'),
		'id_category' => array('required', 'empty'),
		'id_class' => array('required', 'empty'),
	);

	public function getCharTypes()
	{
		return array(
			0 => $this->txt('char_istwink'),
			1 => $this->txt('char_ismain'),
		);
	}

	public function getCharlist($id_player)
	{
		$this->setField(array(
			'chars.id_char',
			'chars.char_name',
			'chars.is_main',
			'class.class',
			'class.color',
			'class.css',
			'cats.category'
		));

		$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');
		$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');

		$this->setFilter('chars.id_player={int:id_player}');
		$this->setParameter('id_player', $id_player);

		$this->setOrder('chars.is_main DESC');

		$this->read('*');

		foreach($this->data as $char)
		{
			if ($this->checkAccess('raidmanager_perm_player')===true)
			{

				$actionbar = new Actionbar();

				$params = array(
					'id_char' => $char->id_char,
					'id_player' => $id_player
				);

				// the edit button
				$actionbar->createButton('edit', 'ajax', 'icon')
				     	  ->setRoute('raidmanager_char_edit', $params);


				if ($this->countData() > 1 && $char->is_main == 0)
				{
					// the edit button
					$actionbar->createButton('delete', 'ajax', 'icon')
							  ->setRoute('raidmanager_char_delete', $params);

				}

				$char->actionbar = $actionbar->build();

			}

			if ($char->is_main == 1)
				$char->char_name .= ' (Main)';

			// translated category name
			$char->category = $this->txt('category_' . $char->category);
		}

		return $this->data;
	}

	public function getMaincharName($id_player)
	{
		$this->setField('char_name');
		$this->setFilter('id_player={int:id_player} AND is_main=1');
		$this->setParameter('id_player', $id_player);
		$this->read();

		return $this->data->char_name;
	}

	/**
	 * Loads and return char data. The method takes care about loading existing data
	 * when $id_char parameter isset and a new char when $id_char is null.
	 * @param int $id_player
	 * @param int $id_char
	 * @return \Web\Framework\Lib\Data
	 */
	public function getEditChar($id_player, $id_char=null)
	{
		// Load data from model if char id is given
		if (isset($id_char))
		{
			// char edit, get some char data for use in form
			$this->setField(array(
				'chars.id_char',
				'chars.char_name',
				'chars.is_main',
				'chars.id_class',
				'chars.id_category',
				'class.class',
				'class.color',
				'class.css',
				'cats.category'
			));

			$this->addJoin('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class');
			$this->addJoin('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category');
			$this->setFilter('chars.id_char={int:id_char}');
			$this->setParameter('id_char', $id_char);
			$this->setOrder('chars.is_main');
			$this->read();

			$this->data->mode = 'edit';
		}
		// Otherwise assume it is a new char
		else
		{
			$this->data = new Data();

			// new char, set default values
			$this->data->id_player = $id_player;
			$this->data->char_name = '';
			$this->data->is_main = 0;
			$this->data->id_class = 0;
			$this->data->id_category = 0;
			$this->data->mode = 'add';
		}

		return $this->data;
	}

	/**
	 * Checks for existiance of a charname and attaches an error to field char_name if this name is already taken.
	 */
	public function checkNameExists()
	{
		// Do nothing if the char name is the same as the name in char name compare field
		if (isset($this->data->char_name) && isset($this->data->char_name_compare) && $this->data->char_name == $this->data->char_name_compare)
			return;

		$model = $this->getModel();

		$filter = 'char_name={string:char_name}';
		$params = array('char_name' => $this->data->char_name);

		if (isset($this->data->id_char))
		{
			$filter .= ' AND id_char<>{int:id_char}';
			$params['id_char'] = $this->data->id_char;
		}

		$model->setFilter($filter);
		$model->setParameter($params);

		$num_chars = $model->count();

		if ( $num_chars != 0)
			$this->addError('char_name', $this->txt('char_name_already_taken'));
	}

	public function createFirstChar($data)
	{
		$this->setData($data)->save();
	}

	public function setCharsToTwink($id_player, $id_char)
	{
		$model = $this->getModel();

		$model->setField('is_main');
		$model->setFilter('chars.id_player={int:id_player} AND chars.id_char<>{int:id_char}');
		$model->setParameter(array(
			'is_main' => 0,
			'id_player' => $id_player,
			'id_char' => $id_char
		));

		$model->update();
	}

	public function saveChar($data)
	{
		$this->setData($data);

		// on changed cha name we have to check for already existing charname
		$this->checkNameExists();

		if ($this->hasErrors())
			return;

		// save our chardata to db
		$this->save();

		#var_dump($this->data);

		// if this is the new mainchar, change all other chars than this to twinks
		if ($this->hasNoErrors() && isset($this->data->is_main) && $this->data->is_main == 1)
			$this->setCharsToTwink($this->data->id_player, $this->data->id_char);
	}

	public function deleteChar($id_char)
	{
		// remove char from setlists
		$model_setlist = $this->getModel('Setlist');
		$model_setlist->setFilter('id_char={int:id_char}')
					  ->setParameter('id_char', $id_char)
					  ->delete();

		// delete char itself
		$this->delete($id_char);
	}
}
?>