<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

/**
 * Charclass model
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @package WebExt
 * @subpackage App Raidmanager
 * @license BSD
 * @copyright 2014 by author
 */
final class CharclassModel extends Model
{
	protected $tbl = 'app_raidmanager_classes';
	protected $alias = 'classes';
	protected $pk = 'id_class';

	public function getClasses()
	{
		$query = array(
		    'type' => '2col',
			'field' => array(
			    'id_class',
			    "CONCAT('class_', class)"
			),
		    'order' => 'class'
		);

		$this->read($query, 'translate');

		$out = $this->data->getProperties();

		asort($out);

		return $out;

	}

	/**
	 * Callback method for class translation
	 * @param $row
	 * @return unknown
	 */
	public function translate($row)
	{
		$row[1] = $this->txt($row[1]);
		return $row;
	}

	public function loadClasslist()
	{
		// our sorted return array
		$return = array();

		// load classdata from model
		$classes = $this->read('*');

		// get the translated classname
		$class_keys = array_keys((array)$classes);

		foreach($class_keys as $key)
			$return[$key] = $this->txt($classes[$key]['txt_class']);

		// sort the classes by classname
		asort($return);

		// attach data to the sorted classnames
		$return_keys = array_keys($return);

		foreach($return_keys as $key)
			$return[$key] = $classes[$key];

		return $return;
	}
}
?>
