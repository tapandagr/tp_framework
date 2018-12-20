<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
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
            if($action == 'ajaxProcessAdd')
            {
                $this->ajaxProcessAdd();
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
            $data = $this->fw->class->convert->makeArrayBySerializedData(urldecode($_POST['data']));

            $object = new $this->className();

            //Get parent ID
            $object->parent = $data['parent'];

            //Get parent object
            $parent = $this->fw->class->object->makeObjectById($this->className, $object->parent, $this->fw->language->id);

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
            $this->fw->class->file->makeDir($object);

            //Get last position
            $object->position = $this->fw->class->category->getLastPosition($this->fw, $this->table, $object, 1);

            $object->update();
        }

        die($this->context->smarty->fetch(_PS_MODULE_DIR_.$this->fw->name.'/views/templates/admin/ajax/categories/add.tpl'));
    }
}
