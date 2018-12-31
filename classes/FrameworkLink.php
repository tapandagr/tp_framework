<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkLink
{
    /**
    *
    */
    public function getAdminLinks($admin_controllers)
    {
        //Initialize result
        $result = new stdClass();
        $result->admin = new stdClass();

        foreach($admin_controllers as $c)
        {
            $controller = FrameworkConvert::lowercase($c[1]);
            $result->admin->$controller = new stdClass();
            $slug = FrameworkConvert::capital($c[1]);

            $result->admin->$controller->url = self::getAdminLink($slug);
            $result->admin->$controller->token = self::getAdminToken($controller);

            foreach($c[2] as $t)
            {
                $type = FrameworkConvert::lowercase($t);
                $result->admin->$controller->$type = self::getAdminLink($slug, $t);
            }
        }

        return $result;
    }

    /**
    *
    */
    public function getAdminLink($controller, $action = null, $prefix = 'Framework')
    {
        if($action == null)
        {
            $action = '';
        }else
            $action = '&action=ajaxProcess'.$action;

        return Context::getContext()->link->getAdminLink('Admin'.$prefix.$controller).$action;
    }

    /**
    *
    */
    public function getAdminToken($controller, $prefix = 'Framework')
    {
        $controller = FrameworkConvert::capital($controller);
        $controller = 'Admin'.$prefix.$controller;

        return Tools::getAdminToken(
            $controller.
            (int)Tab::getIdFromClassName($controller).
            (int)Context::getContext()->employee->id
        );
    }

    /**
    *
    */
    public static function getLink($module, $controller, $data = array())
    {
        return Context::getContext()->link->getModuleLink($module, $controller, $data);
    }
}
