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
    public static function getLink($object, Array $data = array())
    {
        return Context::getContext()->link->getModuleLink($object->name, $object->controller, $data);
    }

    /**
    *
    */
    public static function getLinkSimple($module, $controller, Array $data = array())
    {
        $object = new stdClass();
        $object->name = $module;
        $object->controller = $controller;
        return self::getLink($object, $data);
    }

    /**
    *
    */
    public static function getPagination($object, $link_rewrite, $page, $steps = 2)
    {
        $result = array();

        $a = new stdClass();
        $a->open = '<a href="';
        $a->middle = '">';
        $a->close = '</a>';

        $more_text = '<i class="fas fa-ellipsis-h"></i>';

        //First page
        $meta_title = 1;

        if ($page->current != 1) {
            //We make the link
            $data = array('link_rewrite' => $link_rewrite, 'page' => 1);

            $link = FrameworkLink::getLink($object, $data);

            $result[0]['content'] = $a->open.$link.$a->middle.$meta_title.$a->close;
            $result[0]['class'] = '';
        } else {
            $result[0]['content'] = $meta_title;
            $result[0]['class'] = ' class="current"';
        }

        $x = 1;

        if ($page->current > $steps + 2) {
            $result[$x]['content'] = $more_text;
            $result[$x]['class'] = '';
            $x++;
        }

        $y = 0;

        while ($y < $steps and $page->current - $steps + $y <= 1) {
            $y++;
        }

        for ($i=$y; $i < $steps; $i++) {
            $meta_title = $page->current - $steps + $i;
            //We make the link
            $data = array('link_rewrite' => $link_rewrite, 'page' => $meta_title);

            $link = FrameworkLink::getLink($object, $data);
            $result[$x]['content'] = $a->open.$link.$a->middle.$meta_title.$a->close;
            $result[$x]['class'] = '';
            $x++;
        }

        if ($page->current > 1) {
            $result[$x]['content'] = $page->current;
            $result[$x]['class'] = ' class="current"';
            $x++;
        }

        $y = $steps;

        while ($page->current + $y > $page->max - 1 and $y > 0) {
            $y--;
        }

        for ($i=1; $i <= $y; $i++) {
            $meta_title = $page->current + $i;
            //We make the link
            $data = array('link_rewrite' => $link_rewrite, 'page' => $meta_title);

            $link = FrameworkLink::getLink($object, $data);
            $result[$x]['content'] = $a->open.$link.$a->middle.$meta_title.$a->close;
            $result[$x]['class'] = '';
            $x++;
        }

        if ($page->current < $page->max - $steps - 1) {
            $result[$x]['content'] = $more_text;
            $result[$x]['class'] = '';
            $x++;
        }

        if ($page->current < $page->max) {
            $meta_title = $page->max;
            //We make the link
            $data = array('link_rewrite' => $link_rewrite, 'page' => $page->max);

            $link = FrameworkLink::getLink($object, $data);

            $result[$x]['content'] = $a->open.$link.$a->middle.$meta_title.$a->close;
            $result[$x]['class'] = '';
            $x++;
        }

        return $result;
    }
}
