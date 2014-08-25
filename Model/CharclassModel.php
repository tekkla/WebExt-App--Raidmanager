<?php
namespace Web\Apps\Raidmanager\Model;

use Web\Framework\Lib\Model;

// Check for direct file access
if (!defined('WEB'))
    die('Cannot run without WebExt framework...');

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

    /**
     * Returns a translated and alphabetically sorted list of charclasses
     * @return multitype:
     */
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
     * @param array $row
     * @return array
     */
    final protected function translate(&$row)
    {
        $row[1] = $this->txt($row[1]);
        return $row;
    }
}
?>
