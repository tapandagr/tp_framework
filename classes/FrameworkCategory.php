<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class FrameworkCategory extends ObjectModel
{
    public $id_tp_framework_category;
    public $level;
    public $parent_id;
    public $position;
    public $link_rewrite;
    public $children;
    public $descendants;
    public $files;

    public $meta_title;

	public static $definition = array(
        'table'		=> 'tp_framework_category',
        'primary'	=> 'id_tp_framework_category',
        'multilang'	=> true,
        'fields'	=> array(
            'level' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'parent_id' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'position' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'link_rewrite' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true
            ),
            'children' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'descendants' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'files' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'meta_title' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'lang' => true,
                'required' => true
            ),
        ),
    );

    /**
    *
    */
    public function __construct($id_tp_framework_category = null, $id_lang = null)
    {
        $this->name = 'tp_framework';
        $this->table = $this->name.'_category';

        $this->convert = new FrameworkConvert();
        $this->database = new FrameworkDatabase();

        $this->languages = tp_framework::getLanguages();
        $this->language = Context::getContext()->language;

        parent::__construct($id_tp_framework_category, $id_lang);
    }

    /**
    * Returns a categories tree from a module we specify
    *
    * @param $module_name varchar We have categories in many modules. This parameter helps us retrieve the desired ones
    *
    * @param $level int Margin level / If set, the function will not return deeper children
    *
    * @return Returns a sorted array based on the position among the siblings and the parental hierarchy
    */
    public function getCategoriesTree($module_name = 'tp_framework', $level = null)
    {
        //Get the module categories table
        $table = $module_name.'_category';

        if ($level !== null) {
            $restriction = '`level` <= '.$level;
        } else {
            $restriction = null;
        }

        //Get the categories
        $result = FrameworkDatabase::selectLang('*', $table, $this->language->id, $restriction, '`nleft` ASC');

        //Add images home directory
        array_unshift(
            $result,
            array(
                'id_'.$table => 0,
                'level' => 0,
                'parent_id' => 0,
                'meta_title' => Context::getContext()->getTranslator()->trans('Αρχική κατηγορία',array(),'Modules.tp_framework.Admin'),
                'descendants' => array()
            )
        );

        return $result;
    }

    /**
    *
    */
    public function regenerateTree($module = 'tp_framework')
    {
        $sort = '`level` ASC, `parent_id` ASC, `position` ASC';

        $table = $module.'_category';

        $ids = $this->database->select('id_'.$table, $table, null, null, $sort);

        $index = array();

        for ($x=0; $x < count($ids); $x++) {
            $index[$ids[$x]['id_'.$table]] = $x;
        }

        $flip = array_flip($index);

        //We get the whole table
        $sql = $this->database->select('*', $table, null, null, $sort);

        //Always the first one will have nleft = 1
        $needle = 1;

        $result = array();

        $result[0] = $sql[0];

        $result[0]['nleft'] = $needle;

        $needle += 2 * $result[0]['descendants'] + 1;

        $result[0]['nright'] = $needle;

        $needle++;

        for ($x=1; $x < count($sql); $x++) {
            $result[$x] = $sql[$x];
            if ($sql[$x]['parent_id'] != $result[$x-1]['parent_id']) {
                $needle = $result[$flip[$sql[$x]['parent_id']]]['nleft'] + 1;
            }

            $result[$x]['nleft'] = $needle;

            $needle += 2 * $sql[$x]['descendants'] + 1;

            $result[$x]['nright'] = $needle;

            $needle++;
        }

        $fields = $this->getClassFields();

        //We convert the $sql to csv
        $csv = $this->convert->tableToCSV($result, $fields);

        $columns_to_update = '`nleft` = VALUES(`nleft`), `nright` = VALUES(`nright`)';

        $this->database->update($this->table, self::getClassFieldsCSV(), $csv, $columns_to_update);

        return true;
    }

    /**
    * It returns the last position among siblings
    */
    public function getLastPosition()
    {
        $result = $this->database->getValue('position', $this->table, '`parent_id` = '.$this->parent_id, '`position` DESC');

        if($result === null)
        {
            return 0;
        }

        $result++;

        return $result;
    }

    public function getClassFields()
    {
        $result = array(
            'id_tp_framework_category',
            'level',
            'parent_id',
            'position',
            'nleft',
            'nright',
            'link_rewrite',
            'children',
            'descendants',
            'files'
        );

        return $result;
    }

    /**
    *
    */
    public static function getClassFieldsCSV()
    {
        return '(`id_tp_framework_category`,`level`,`parent_id`,`position`,`nleft`,`nright`,`link_rewrite`,`children`,`descendants`,`files`)';
    }

    /**
    *
    */
    public function getChildren($id)
    {
        return FrameworkDatabase::select('*', 'tp_framework_category', null, '`parent_id` = '.$id);
    }

    /**
    *
    */
    public function getDescendants($categories)
    {
        //We flip the array to be able to get the position when given the id
        $flip = array_flip(array_column($categories, 'id_tp_framework_category'));

        //We initialize the descendants counter
        foreach ($categories as $c)
        {
            $c['descendants'] = 0;
        }

        for ($x = count($categories) - 1; $x >= 0; $x--)
        {
            $y = $categories[$x]['parent_id'];

            while ($y > 0)
            {
                $categories[$flip[$y]]['descendants']++;

                //We get the parent of the parent
                $y = $categories[$flip[$y]]['parent_id'];
            }
        }

        return $categories;
    }

    /**
    *
    */
/*    public static function getRelativePath()
    {
        $object = new FrameworkCategory($this->id);

        //We keep the link_rewrite because the object will be recycled
        $link_rewrite = $object->link_rewrite;

        //Path initialization
        $path = '';

        while($object->parent_id != 0)
        {
            $object = new FrameworkCategory($object->parent_id);
            $path = '/'.$object->link_rewrite.$path;
        }

        $path = $path.'/'.$link_rewrite;

        return $path;
    }

    /**
    *
    */
/*    public function getLastPosition($fw, $table, $object, $increase = null)
    {
        if($table != 'tp_framework_gallery_content')
        {
            //Get the last position for the children of the parent
            $result = FrameworkDatabase::getValue('position', $table, '`position` DESC', 'id_'.$table.' != '.(int)$object->id.' AND `parent_id` = "'.$object->parent_id.'"');
        }else
        {
            //Get the last position for files assigned to the library
            $result = FrameworkDatabase::getValue('position', $table, '`position` DESC', '`gallery_id` != '.(int)$object->id);
        }

        if($increase != null)
            $result += 1;

        return $result;
    }

    /**
    *
    */
/*    public function getParents($child, $table, $max_level)
    {
        $result = [];

        $result[0] = $child['parent_id'];

        for ($x=1; $x < $max_level - 1; $x++)
        {
            if($result[$x-1] == 0)
                $result[$x] = 0;
            else
                $result[$x] = self::getParent($table, $result[$x-1]);
        }

        return $result;
    }

    /**
    *
    */
/*    public function getParent($table, $row)
    {
        return FrameworkDatabase::getValue($table,'parent_id','`id_'.$table.'` = '.$row);
    }

    /**
    *
    */
/*    public function getDescendants($table, $categories, $row)
    {
        $counter = new stdClass();
        $counter->categories = 0;
        $counter->children = 0;
        $result = [];

        while($counter->categories < count($categories))
        {
            //Match needle
            $found = false;

            //Parents needle
            $parent = 0;

            while(isset($categories[$counter->categories]['parent_'.$parent]) and $found === false)
            {
                //If found, we update the needle to force the loop stop
                if($categories[$row]['id_'.$table] == $categories[$counter->categories]['parent_'.$parent])
                    $found = true;

                $parent++;
            }

            if($found === true)
            {
                $result[$counter->children] = $categories[$counter->categories];
                $counter->children++;
            }

            $counter->categories++;
        }

        return $result;
    }

    /**
    *
    */
/*    public function getPath()
    {
        $result = '/'.$this->link_rewrite;

        $object = new $this($this->id);

        while ($object->parent_id != 0) {
            //We get the parent object
            $object = new FrameworkCategory($object->parent_id);

            $result = '/'.$object->link_rewrite.$result;
        }

        return $result;
    }*/
}
