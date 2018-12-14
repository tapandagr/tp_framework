<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

$idtabs = array();

$idtabs[] = Tab::getIdFromClassName('AdminFrameworkCategories');
$idtabs[] = Tab::getIdFromClassName('AdminFrameworkDashboard');
$idtabs[] = Tab::getIdFromClassName('AdminFrameworkFiles');
$idtabs[] = Tab::getIdFromClassName('AdminFrameworkHooks');
$idtabs[] = Tab::getIdFromClassName('AdminFrameworkEntities');
$idtabs[] = Tab::getIdFromClassName('AdminFrameworkSettings');
