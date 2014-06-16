<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;
use Web\Framework\Lib\Data;
use Web\Framework\Html\Controls\Actionbar;

/**
 * Char model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class CharModel extends Model
{
	protected $tbl = 'app_raidmanager_chars';
	protected $alias = 'chars';
	protected $pk = 'id_char';
	public $validate = array(
		'char_name' => array(
		    'required',
		    'empty',
		    array('min', 3)
		),
		'id_player' => array(
		    'required',
		    'empty'
		),
		'id_category' => array(
		    'required',
		    'empty'
		),
		'id_class' => array(
		    'required',
		    'empty'
		),
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
		$query = array(
		    'type' => '*',
		    'field' => array(
		        'chars.id_char',
		        'chars.id_player',
		        'chars.char_name',
		        'chars.is_main',
		        'class.class',
		        'class.color',
		        'class.css',
		        'cats.category'
		    ),
		    'join' => array(
		        array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class'),
		        array('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category'),
		    ),
		    'filter' => 'chars.id_player={int:id_player}',
		    'param' => array(
		        'id_player' => $id_player
		    ),
		    'order' => 'chars.is_main DESC'
		);

		return $this->read($query, 'extendChar');
	}

	protected function extendChar(&$char)
	{
	    if (!$this->checkAccess('raidmanager_perm_player'))
	        return $char;

    	$actionbar = new Actionbar();

    	$params = array(
   			'id_char' => $char->id_char,
   			'id_player' => $char->id_player
    	);

	    // The edit button
    	$actionbar->createButton('edit', 'ajax', 'icon')->setRoute('raidmanager_char_edit', $params);

    	// Delete button only on non mainchars
	    if ($char->is_main == 0)
    		$actionbar->createButton('delete', 'ajax', 'icon')->setRoute('raidmanager_char_delete', $params);

    	$char->actionbar = $actionbar->build();

	    if ($char->is_main == 1)
	    	$char->char_name .= ' (Main)';

	    // Translated category name
	    $char->category = $this->txt('category_' . $char->category);

	    return $char;
	}

	public function getMaincharName($id_player)
	{
		return $this->read(array(
			'type' => 'val',
		    'field' => 'chars.char_name',
		    'filter' => 'chars.id_player={int:id_player} AND chars.is_main=1',
		    'param' => array(
		        'id_player' => $id_player
		    )
		));
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
			$this->read(array(
			    'field' => array(
			        'chars.id_char',
			        'chars.char_name',
			        'chars.is_main',
			        'chars.id_class',
			        'chars.id_category',
			        'class.class',
			        'class.color',
			        'class.css',
			        'cats.category'
			    ),
			    'join' => array(
			        array('app_raidmanager_classes', 'class', 'INNER', 'chars.id_class=class.id_class'),
			        array('app_raidmanager_categories', 'cats', 'INNER', 'chars.id_category=cats.id_category'),
			    ),
			    'filter' => 'chars.id_char={int:id_char}',
			    'param' => array(
			        'id_char' => $id_char
			    ),
			    'order' => 'chars.is_main',
			));

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

		$filter = 'chars.char_name={string:char_name}';
		$params = array(
		    'char_name' => $this->data->char_name
		);

		if (isset($this->data->id_char))
		{
			$filter .= ' AND chars.id_char<>{int:id_char}';
			$params['id_char'] = $this->data->id_char;
		}

		if ( $this->getModel()->count($filter, $params) != 0)
			$this->addError('char_name', $this->txt('char_name_already_taken'));
	}

	public function createFirstChar($data)
	{
	    $this->data = $data;
		$this->save();
	}

	/**
	 * Sets all chars of one player (except the char provided by id_char) to be alts
	 * @param int $id_player
	 * @param int $id_char
	 */
	public function setCharsToTwink($id_player, $id_char)
	{
	    $this->getModel()->update(array(
		    'field' => 'is_main',
		    'filter' => 'id_player={int:id_player} AND id_char<>{int:id_char}',
		    'param' => array(
    			'is_main' => 0,
    			'id_player' => $id_player,
    			'id_char' => $id_char
		    )
		));
	}

	public function saveChar($data)
	{
		$this->data = $data;

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

	/**
	 * Deletes the char with the provided char id.
	 * @param int $id_char
	 */
	public function deleteChar($id_char)
	{
		// Remove char from setlists
	    $this->getModel('Setlist')->delete(array(
			'filter' => 'setlists.id_char={int:id_char}',
		    'param' => array(
		        'id_char' => $id_char
		    )
		));

		// Delete char itself
		$this->delete($id_char);
	}
}
?>
