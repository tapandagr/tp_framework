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
            } elseif($action == 'ajaxProcessView') {
                $this->ajaxProcessView();
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

            //Get parent object
            $parent = FrameworkObject::makeObjectById($this->className, $object->parent_id, $this->fw->language->id);

            //Get respective level
            $object->level = $parent->level + 1;

            for ($x=1; $x <= count($this->fw->languages); $x++)
            {
                $object->meta_title[$x] = $data['meta_title'][$x];
            }

            $object->link_rewrite = $data['link_rewrite'];

            $object->add();

            //Set status
            $object->status = 1;

            //Directory add
            FrameworkDirectory::makeDirectory($object);

            //Get last position
            $object->position = FrameworkCategory::getLastPosition($this->fw, $this->table, $object, 1);

            $object->update();

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
        $categories = FrameworkCategory::getCategoriesTree($this->fw);

        $this->context->smarty->assign(array(
            'allowed_categories' => $categories,
        ));

        die($this->context->smarty->fetch('module:'.$this->fw->name.'/views/templates/admin/ajax/categories/allowed_categories.tpl'));
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
            $medium = new $this->className($mid,$this->lid);
        else
        {
            //Bug-proof for unique smarty declaration
            $medium = '';
        }

        //In case `cid` has not been set, we render the images home directory
        if(!isset($cid) or $cid == 0)
        {
            $category = new $this->className();
            $category->id = 0;
            $category->path = '';
        }else
        {
            //Sanitization check
            if(Validate::isInt($cid) and $cid >= 0)
            {
                //Category object retrieval
                $category = new $this->className($cid,$this->lid);
                //Path creation
                $category->path = $this->fw->calculateDirectoryLocation($category);
            }else
            {
                //Whitelisting: In any other condition we kill the function
                //die;
            }
        }

        if($category->parent_id != 0)
        {
            $parent = new FrameworkCategory($category->parent, $this->lid);
        }else
        {
            $parent = new stdClass();
            $parent->id = 0;
            $parent->location = '';
            $parent->meta_title = $this->l('Home directory');
        }

        //Get media categories and add ajax link for file browsing
        $children = $this->fw->database->selectLang('*', $this->fw->name.'_category', $this->fw->language->id, '`parent_id` = '.$category->id);
        $children = $this->fw->array->getArrayWithExtraLink('FrameworkCategories', $this->fw->name.'_category', $children, 'ajaxprocesscategoryview');

        //We add the parent link (3 is to get parent contents)
        $parent = $this->fw->object->getObjectWithExtraLink('FrameworkCategories', $parent, 'ajaxprocesscategoryview');

        //Get categories regular link. In order not to burden our server too much, we make the rest of the link with jQuery
        $move_categories_link = $this->fw->link->getAdminLink('FrameworkCategories');

        $this->context->smarty->assign(array(
            'move_categories_link' => $move_categories_link,
            'parent' => $parent,
            'category' => $category,
            'children' => $children,
            //We fill it only if we are not browsing the home directory
            'files' => ($category->id > 0)?FrameworkMedium::getMedia($category):'',
            'medium' => $medium,
            'tree' => $this->fw->category->getCategoriesTree(),
        ));

        die($this->context->smarty->fetch(_PS_MODULE_DIR_.$this->fw->name.'/views/templates/admin/ajax/categories/category_view.tpl'));
    }
}
