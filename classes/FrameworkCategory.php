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
    public $nleft;
    public $nright;
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
            'nleft' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
            ),
            'nright' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt'
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
        $this->class = 'FrameworkCategory';
        $this->identifier = 'id_tp_framework_category';

        $this->languages = tp_framework::getLanguages();
        $this->language = Context::getContext()->language;

        $this->directory = new stdClass();
        $this->directory->module = _PS_MODULE_DIR_.'tp_framework';
        $this->directory->uploads = $this->directory->module.'/uploads';
        $this->directory->images = $this->directory->uploads.'/images';
        $this->directory->templates = new stdClass();
        $this->directory->templates->plain = $this->directory->module.'/views/templates';
        $this->directory->templates->import = $this->directory->templates->plain.'/import';

        $this->convert = new FrameworkConvert();
        $this->database = new FrameworkDatabase();
        $this->directory = new FrameworkDirectory();

        parent::__construct($id_tp_framework_category, $id_lang);
    }

    public function add($autodate = true, $null_values = false)
    {
        $this->getPosition();

        $this->getLevel();

        $return = parent::add($autodate, $null_values);

        $this->makeDirectory();

        $this->regenerateTree();

        return $return;
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
    public function getCategoriesTree($table = 'tp_framework_category', $level = null)
    {
        if ($level !== null) {
            $restriction = '`level` <= '.$level;
        } else {
            $restriction = null;
        }

        //Get the categories
        $result = FrameworkDatabase::selectLang('*', $table, Context::getContext()->language->id, $restriction, '`nleft` ASC');

        //Add images home directory
        array_unshift(
            $result,
            array(
                'id_'.$table => 0,
                'level' => 0,
                'meta_title' => Context::getContext()->getTranslator()->trans('Αρχική κατηγορία',array(),'Modules.tp_framework.Admin')
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

        //We get the whole table
        $sql = $this->database->select('*', $table, null, null, $sort);

        //We flip the array to be able to get the position when given the id
        $flip = array_flip(array_column($sql, 'id_tp_framework_category'));

        //We update the descendants
        $sql = self::getDescendants($sql, $flip);

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
    public function getPosition()
    {
        $result = $this->database->getValue('position', $this->table, '`parent_id` = '.$this->parent_id, '`position` DESC');

        if($result !== null)
        {
            $this->position = $result + 1;
        }

        return $this;
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
    public static function getDescendants($categories, $flip)
    {
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
    public function getPath()
    {
        //We assign the object to a new instance that will be able to re-assigned
        $object = new $this->class($this->id);

        //Path initialization
        $path = '';

        while($object->parent_id != 0)
        {
            $object = new $this->class($object->parent_id);
            $path = '/'.$object->link_rewrite.$path;
        }

        $this->path = $path;

        return $this;
    }

    /**
    *
    */
    public function getUpdateFields()
    {
        $result = array();

        $result[0]['name']  = 'meta_title';
        $result[0]['type']  = 'text';
        $result[0]['lang']  = true;
        $result[0]['width'] = 3;
        $result[1]['name']  = 'link_rewrite';
        $result[1]['type']  = 'text';
        $result[1]['lang']  = false;
        $result[1]['width'] = 3;
        $result[2]['name']  = 'parent_id';
        $result[2]['type']  = 'select';
        $result[2]['lang']  = false;
        $result[2]['width'] = 3;
        $result[3]['name']  = 'position';
        $result[3]['type']  = 'text';
        $result[3]['lang']  = false;
        $result[3]['width'] = 3;

        return $result;
    }

    /**
    * It calculates the category location and creates a directory
    */
    public function makeDirectory()
    {
        $this->getRelativePath();

        $absolute_path = $this->directory->images.$this->path;

        FrameworkDirectory::makeDirectorySimple($absolute_path);

        return true;
    }

    /**
    *
    */
    public function getLevel()
    {
        //Get parent object
        $parent = FrameworkObject::makeObjectById($this->class, $this->parent_id);

        //Get respective level
        $this->level = $parent->level + 1;

        return $this;
    }

    /**
    * This function will get the available categories that can be assigned as parent_id. Think about assigning a child as parent (by mistake). Non-braking loop.
    */
    public function getAllowedCategories()
    {
        //We want recursive parents and siblings with their recursive children
        $result = $this->database->selectLang('*', $this->table, $this->language->id, '(`nleft` < '.$this->nleft.' OR `nright` > '.$this->nright.')');

        array_unshift(
            $result,
            array(
                $this->identifier => 0,
                'level' => 0,
                'meta_title' => Context::getContext()->getTranslator()->trans('Αρχική κατηγορία',array(),'Modules.tp_framework.Admin')
            )
        );

        return $result;
    }

    public function getAllowedCategoriesBulk($data)
    {
        $result = array();

        for ($x=0; $x < count($data); $x++)
        {
            $result[$x] = $data[$x];

            //We get the category object
            $category = new $this->class($data[$x][$this->identifier], $this->language->id);

            $result[$x]['allowed_categories'] = $category->getAllowedCategories();
        }

        return $result;
    }

    /**
    *
    */
    public function prepareCategoriesUpdate($table, $data, $columns, $date)
    {
        //We isolate the category IDs
        $ids = array_column($data, $this->identifier);

        //We convert them to csv (ready for use in MySQL queries)
        $ids_csv = $this->convert->listToCSV($ids);

        //We get the respective saved data
        $saved_data = $this->database->select('*', $table, null, '`'.$this->identifier.'` IN '.$ids_csv, '`'.$this->identifier.'` ASC');

        //We check if there is parent_id or link_rewrite update
        for ($x=0; $x < count($data); $x++)
        {
            if ($data[$x]['parent_id'] == $saved_data[$x]['parent_id'] and $data[$x]['link_rewrite'] == $saved_data[$x]['link_rewrite'])
            {
                unset($data[$x]);
            }
        }

        $result = array();
        $result['date'] = $date;
        $result['entities'] = array();
        $x = 0;

        //We now have only the records that the directory needs to be updated
        foreach ($data as $key => $value)
        {
            //We get the parent object
            $parent = new $this->class($data[$key]['parent_id']);

            $parent->getPath();

            //We get the category object
            $category = new $this->class($data[$key][$this->identifier]);

            $category->old_name = $category->link_rewrite;

            $category->new_name = $data[$key]['link_rewrite'];

            $category->getPath();

            $result['entities'][$x] = array(
                'category' => $category,
                'parent_id' => $parent->id
            );

            //We move the directory to the temporary directory
            $result['entities'][$x]['temporary_directory'] = $this->directory->moveFileToTemporaryDirectory($category, $date);

            $x++;
        }

        //We subtract positions by half to give them priority in the tree update
        $data = $this->subtractPosition($data);

        //We move the directories to the destination
        for ($x=0; $x < count($result['entities']); $x++)
        {
            $this->directory->moveFileToDestination(
                $result['entities'][$x]['category'],
                $result['entities'][$x]['parent_id'],
                $result['entities'][$x]['temporary_directory']
            );
        }

        return $result;
    }

    /**
    *
    */
    

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
