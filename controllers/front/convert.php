<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_ . 'tp_content/tp_content.php';

class tp_frameworkConvertModuleFrontController extends ModuleFrontController
{
    /**
    *
    */
    public function __construct()
    {
        //Framework module call
        $this->fw = new tp_framework();

        parent::__construct();
    }

    /**
    *
    */
    public function initContent()
    {
        $this->ajax = true;

        $action = Tools::getValue('action');

        if(isset($action))
        {
            if ($action == 'Slugify')
            {
                $this->ajaxProcessSlugify();
            }
        }
    }

    /**
    *
    */
    public function ajaxProcessAdd()
    {
        $result = 0;

        if(isset($_POST['data']))
        {
            $result = 1;
        }

        die(Context::getContext()->smarty->fetch(_PS_MODULE_DIR_.$this->fw->name.'/views/templates/front/_partials/convert.tpl'));
    }
}
