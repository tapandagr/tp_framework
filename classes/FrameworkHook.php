<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.4
 *
 * This class has been built to let us automate any procedure is related to database tables
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class FrameworkHook
{
    /**
    * It registers hooks (associated with the module) in case they do not already exist
    */
    public function installHooks($object)
    {
        foreach($object->getHooks() as $h)
        {
            $object->registerHook($h);
        }

        return true;
    }
}
