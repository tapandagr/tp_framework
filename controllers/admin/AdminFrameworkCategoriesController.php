<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class AdminFrameworkCategoriesController extends ModuleAdminController
{
    public function __construct()
    {
        //Framework module call
        $this->fw = new tp_framework();

        $this->table = 'tp_framework_category';
        $this->className = 'FrameworkCategory';

        $this->array = new FrameworkArray();
        $this->category = new FrameworkCategory();
        $this->convert = new FrameworkConvert();
        $this->database = new FrameworkDatabase();
        $this->directory = new FrameworkDirectory();
        $this->link = new FrameworkLink();
        $this->object = new FrameworkObject();

        $this->language = Context::getContext()->language;
        $this->lang = true;
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initContent()
    {
        $this->ajax = true;

        $action = Tools::getValue('action');

        if(isset($action))
        {
            if ($action == 'ajaxProcessAdd')
            {
                $this->ajaxProcessAdd();
            } elseif ($action == 'ajaxProcessGetCategoriesTree')
            {
                $this->ajaxProcessGetCategoriesTree();
            } elseif ($action == 'ajaxProcessView')
            {
                $this->ajaxProcessView();
            } elseif ($action == 'ajaxProcessGetMassiveForm')
            {
                $this->ajaxProcessGetMassiveForm();
            } elseif ($action == 'ajaxProcessMassiveUpdate')
            {
                $this->ajaxProcessMassiveUpdate();
            }
        }
    }

    /**
    *
    */
    public function ajaxProcessAdd()
    {
        if(isset($_POST['data']))
        {
            //Convert serialized data into table
            $data = FrameworkConvert::makeArrayBySerializedData(urldecode($_POST['data']));

            $object = new $this->className();

            //Get parent ID
            $object->parent_id = $data['parent_id'];

            for ($x=1; $x <= count($this->fw->languages); $x++)
            {
                $object->meta_title[$x] = $data['meta_title'][$x];
            }

            $object->link_rewrite = $data['link_rewrite'];

            $object->add();

            $this->context->smarty->assign(array(
                'category' => $object,
                'language' => $this->fw->language
            ));
        }

        die($this->context->smarty->fetch('module:'.$this->fw->name.'/views/templates/admin/ajax/categories/add.tpl'));
    }

    /**
    *
    */
    public function ajaxProcessGetCategoriesTree()
    {
        //Get media categories
        $categories = FrameworkCategory::getCategoriesTree($this->table);

        $this->context->smarty->assign(array(
            'allowed_categories' => $categories,
        ));

        echo $this->context->smarty->fetch(_PS_MODULE_DIR_ .'tp_framework/views/templates/admin/ajax/categories/allowed_categories.tpl');

        die();
    }

    /**
    * We use this function to get a category data
    */
    public function ajaxProcessView()
    {
        //Medium ID
        $file_id = Tools::getValue('mid');

        //Get the category ID
        $category_id = Tools::getValue('cid');

        //Sanitization
        if(isset($mid) and Validate::isInt($mid) == 1)
            $medium = new $this->className($mid, $this->lid);
        else
        {
            //Bug-proof for unique smarty declaration
            $medium = '';
        }

        //In case `cid` has not been set, we render the images home directory
        if (!isset($category_id) or $category_id <= 0 or !Validate::isInt($category_id))
        {
            $category = new $this->className();
            $category->id = 0;
            $category->path = '';
        } else
        {
            //Category object retrieval
            $category = new $this->className($category_id, $this->language->id);

            //Path creation
            $category->path = $this->directory->calculateDirectoryLocation($category);
        }

        if($category->parent_id != 0)
        {
            $parent = new FrameworkCategory($category->parent_id, $this->language->id);
        }else
        {
            $parent = new stdClass();
            $parent->id = 0;
            $parent->location = '';
            $parent->meta_title = $this->l('Home directory');
        }

        //Get media categories and add ajax link for file browsing
        $children = $this->database->selectLang('*', $this->fw->name.'_category', $this->fw->language->id, '`parent_id` = '.$category->id);
        $children = $this->array->getArrayWithExtraLink('FrameworkCategories', $this->fw->name.'_category', $children, 'ajaxprocesscategoryview');

        //We add the parent link (3 is to get parent contents)
        $parent = $this->object->getObjectWithExtraLink('FrameworkCategories', $parent, 'ajaxprocesscategoryview');

        //Get categories regular link. In order not to burden our server too much, we make the rest of the link with jQuery
        $move_categories_link = $this->link->getAdminLink('FrameworkCategories');

        $this->context->smarty->assign(array(
            'move_categories_link' => $move_categories_link,
            'parent' => $parent,
            'category' => $category,
            'children' => $children,
            //We fill it only if we are not browsing the home directory
            'files' => ($category->id > 0)?FrameworkFile::getFiles($category):'',
            'medium' => $medium,
            'tree' => $this->category->getCategoriesTree(),
        ));

        die($this->context->smarty->fetch(_PS_MODULE_DIR_.$this->fw->name.'/views/templates/admin/ajax/categories/view.tpl'));
    }

    /**
    *
    */
    public function ajaxProcessGetMassiveForm()
    {
        $categories = $_GET['check-category'];

        //Sanitization
        foreach ($categories as $c)
        {
            if (!Validate::isInt($c))
            {
                unset($c);
            }
        }

        $categories_csv = $this->convert->listToCSV($categories);

        $category = new stdClass();

        $category->entities = $this->database->selectLanguageFull($this->table, '`id_'.$this->table.'` IN '.$categories_csv, $this->fw->languages);
        $category->fields = $this->category->getUpdateFields();

        $language = new stdClass();

        $language->entities = $this->fw->languages;
        $language->current = $this->fw->language;

        $this->context->smarty->assign(array(
            'category' => $category,
            'language' => $language,
        ));

        die($this->context->smarty->fetch(_PS_MODULE_DIR_.$this->fw->name.'/views/templates/admin/ajax/categories/massive_form.tpl'));
    }

    /**
    *
    */
    public function ajaxProcessMassiveUpdate()
    {
        //Convert serialized data into table
        $data = FrameworkConvert::makeArrayBySerializedData(urldecode($_POST['data']));

        //We get validation settings
        $rules = ObjectModel::getValidationRules('FrameworkCategory');

        $rules['validate']['category_id'] = 'isUnsignedInt';

        //We remove the rows with empty or invalid values
        $data = FrameworkValidate::removeUnwantedRows($data, $rules);

        FrameworkConvert::pre($data);
    }
}
