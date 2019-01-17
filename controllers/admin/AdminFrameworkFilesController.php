<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Free for personal use. No warranty. Contact us at info@tapanda.gr for details
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class AdminFrameworkFilesController extends ModuleAdminController
{
    public function __construct()
    {
        //We call the framework module main class
        $this->fw = new tp_framework();

        $this->table = 'tp_framework_file';
        $this->className = 'FrameworkFile';

        $this->lang = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->addRowAction('delete');

        $this->fields_list = array(
            'id_tp_framework_file' => array(
                'title' => 'ID',
                'width' => 30,
                'type' => 'text',
            ),
            'ext' => array(
                'title' => $this->trans('Προεπισκόπηση', array(), 'Modules.tp_framework.Admin'),
                'width' => 150,
                'orderby' => false,
                'search' => false,
                'callback' => 'Image'
            )
        );
    }

    /**
    *
    */
    public function initContent()
    {
        //Media category ID
        $mcid = Tools::getValue('mcid');

        if(isset($category_id) and Validate::isInt($category_id) and $category_id > 0)
        {
        	$category = new FrameworkCategory($category_id, $this->fw->language->id);
        	$category->path = $category->getRelativePath();
        }else
        {
            $category = FrameworkObject::makeObjectByID('FrameworkCategory');
        }

        if($category->parent != 0)
        {
            $parent = new $this->fw->category($category->parent, $this->fw->language->id);
        }else
        {
        	$parent = $this->fw->object->makeObjectByID();
        	$parent->meta_title = $this->l('Αρχική κατηγορία');
        }

        //We add ajax link to the parent entity
        $parent = $this->fw->object->getObjectWithExtraLink('FrameworkCategories', $parent, 'CategoryView');

        //$category->children = $this->fw->getDirectoryContent($category->path);

        if(Validate::isInt($category->id) and $category->id > 0)
        {
            $category->files = $category->getFiles();
        }else
        {
            $category->files = array();
        }

        //Get media categories and add ajax link for file browsing
        $children = FrameworkDatabase::selectLang('*', $this->fw->name.'_category', $this->fw->language->id,'`parent_id` = '.$category->id);
        $category->children = FrameworkArray::getArrayWithExtraLink('FrameworkCategories',$this->fw->name.'_category',$children,'CategoryView');

        $this->errors = [];

    	if (!isset($this->display))
    	{
    		$categories = FrameworkDatabase::selectLang('*', 'tp_framework_category', $this->fw->language->id);
    		$files = FrameworkDatabase::select('*', 'tp_framework_file');

    		if(empty($categories))
            {
                $this->errors[] = $this->l('Δεν έχετε κατηγορίες', null, null, false);
            }

    		if(empty($files))
            {
                $this->errors[] = $this->l('Δεν έχετε αρχεία', null, null, false);
            }
    	}else
    	{
    		$categories = null;
    		$files = null;
    	}

        $fields = $this->getFields();

        $this->context->smarty->assign(array(
            'languages' => $this->fw->languages,
            'current_language' => $this->fw->language,
            'links' => $this->fw->links,
            'fields' => $fields,
            'tree' => $this->fw->database->getCategoriesTree(),
            'column_remainder' => FrameworkForm::getColumnRemainder($fields->category),
            'categories' => $categories,
    		'category' => $category,
        ));

        $this->setTemplate('modules/tp_framework/files/content.tpl');

        $action = Tools::getValue('action');

        if(isset($action) && $action != '')
        {
            $this->ajax = true;

            if($action == 'ajaxProcessFilesView')
                $this->ajaxProcessFilesView();
        }

        parent::initContent();
    }

    /**
    *
    */
    public function initPageHeaderToolbar()
    {
        if (!isset($this->display))
        {
            $this->page_header_toolbar_btn['add_category'] = array(
                'desc' => $this->trans('Προσθήκη κατηγορίας', array(), 'Modules.tp_framework.Admin'),
                'modal_target' => '#exampleModalCenter',
                'class' => 'ghnew-framework-category-ajax',
                'icon' => 'fas fa-stream',
                'size' => 3
            );

            $this->page_header_toolbar_btn['add_file'] = array(
                'desc' => $this->trans('Προσθήκη αρχείου', array(), 'Modules.tp_framework.Admin'),
                'class' => 'new-framework-file-ajax',
                'icon' => 'fas fa-cloud-upload-alt',
                'size' => 3 // If set from 2 to 10, it will adapt the size based on Font Awesome 5 directive
            );
        }

        parent::initPageHeaderToolbar();
    }

    /**
    *
    */
    public function getFields()
    {
        $fields = new stdClass();

    	//Get category form fields
    	$fields->category = $this->getCategoryFormFields();

        return $fields;
    }

    /**
    *
    */
    public function getCategoryFormFields()
    {
        $result = [];

        $result[0]['name']  = 'categories';
        $result[0]['type']  = 'select';
        $result[0]['lang']  = 0;
        $result[0]['width'] = 6;
        $result[1]['name']  = 'meta_title';
        $result[1]['type']  = 'text';
        $result[1]['lang']  = 1;
        $result[1]['width'] = 6;
        $result[2]['name']  = 'link_rewrite';
        $result[2]['type']  = 'text';
        $result[2]['lang']  = 0;
        $result[2]['width'] = 6;

        return $result;
    }
}
