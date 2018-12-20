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
    public function getAdminLinks($object)
    {
        //Initialize result
        $result = new stdClass();
        $result->admin = new stdClass();

        foreach($object->getAdminControllers() as $c)
        {
            $controller = $object->class->convert->lowercase($c[1]);
            $result->admin->$controller = new stdClass();
            $slug = $object->class->convert->capital($c[1]);

            $result->admin->$controller->url = $this->getAdminLink($object,$slug);
            $result->admin->$controller->token = $this->getAdminToken($object,$controller);

            foreach($c[2] as $t)
            {
                $type = $object->class->convert->lowercase($t);
                $result->admin->$controller->$type = $this->getAdminLink($object,$slug,$t);
            }
        }

        return $result;
    }

    /**
    *
    */
    public function getAdminLink($object, $controller, $action = null, $module_prefix = 3)
    {
        $prefix = $object->class->convert->getPartOfString($object->name, $module_prefix);
        $prefix = $object->class->convert->capital($prefix);

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
    public function getAdminToken($object, $controller, $module_prefix = 3)
    {
        $prefix = $object->class->convert->getPartOfString($object->name, $module_prefix);
        $prefix = $object->class->convert->capital($prefix);
        $controller = $object->class->convert->capital($controller);
        $controller = 'Admin'.$prefix.$controller;

        return Tools::getAdminToken(
            $controller.
            (int)Tab::getIdFromClassName($controller).
            (int)Context::getContext()->employee->id
        );
    }
}
