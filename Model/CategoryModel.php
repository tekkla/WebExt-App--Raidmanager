<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

class CategoryModel extends Model
{
	public $tbl = 'app_raidmanager_categories';
	public $alias = 'raidcat';
	public $pk = 'id_category';

	public function getCategories()
	{
		$this->setField(array(
			'id_category',
			'category'
		));

		$this->read('2col', 'translate');

		$out = $this->data->getProperties();

		asort($out);

		return $out;
	}

	public function translate($row)
	{
		$row[1] = $this->txt('category_' . $row[1]);
		return $row;
	}
}
?>