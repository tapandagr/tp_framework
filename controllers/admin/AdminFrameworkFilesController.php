<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
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
        $action = Tools::getValue('action');

        if(isset($action) && $action != '')
        {
            $this->ajax = true;

            if($action == 'ajaxProcessFilesView')
                $this->ajaxProcessFilesView();
        }
    }

    /**
    *
    */
    public function initPageHeaderToolbar()
    {
        if (!isset($this->display))
        {
            $this->page_header_toolbar_btn['add_category'] = array(
                'desc' => $this->l('Add category'),
                'class' => 'display_category_add_ajax_form',
                'icon' => 'fas fa-stream',
                'size' => 3
            );

            $this->page_header_toolbar_btn['add_media'] = array(
                'desc' => $this->l('Media upload'),
                'class' => 'display_files_upload_ajax_form',
                'icon' => 'fas fa-cloud-upload-alt',
                'size' => 3 // If set from 2 to 10, it will adapt the size based on Font Awesome 5 directive
            );
        }

        parent::initPageHeaderToolbar();
    }
}
