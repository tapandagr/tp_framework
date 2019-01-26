<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 - 2019 © tapanda.gr <https://tapanda.gr/el/>
 * @license    Free tapanda license <https://tapanda.gr/en/blog/licenses/free-license>
 * @version    0.0.1
 * @since      0.0.1
 *
 * This class has been built to let us automate any procedure is related to database tables
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkObject
{
    /**
    *
    */
    public function __construct()
    {
        //$this->fw = new tp_framework('Object');
    }

    /**
    *
    */
    public static function makeObjectById($class = 'stdClass', $id = 0, $language = null)
    {
        if($id == 0)
        {
            $object = new stdClass();
            $object->id = 0;
            $object->parent = 0;
            //We use -1 for initial parent, for the initial categories to be assigned to the proper level (0)
            $object->level = -1;
            $object->path = '/';
        }else
        {
            $object = new $class($id, $language);
            $object->path = $object->getPath();
        }

        return $object;
    }

    /**
    * It returns a category given an extra admin link
    */
    public function getObjectWithExtraLink($controller, $object, $action = null)
    {
        //Put controller url in
        $controller = FrameworkLink::getAdminLink($controller, $action);

        $object->extra_link = $controller.'&cid='.$object->id;

        return $object;
    }
}
