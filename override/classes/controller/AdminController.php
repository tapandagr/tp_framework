<?php
/**
 * Core PrestaShop module - Cornelius
 * @author    tivuno.com <hi@tivuno.com>
 * @copyright 2018 - 2025 © tivuno.com
 * @license   https://tivuno.com/blog/nea-tis-epicheirisis/apli-adeia
 */
// PrestaShop validator - Start
if (!defined('_PS_VERSION_')) {
    exit;
}
// PrestaShop validator - Finish
class AdminController extends AdminControllerCore
{
    /**
     * @throws PrestaShopException
     */
    public function setMedia($isNewTheme = false): void
    {
        parent::setMedia($isNewTheme);
        Hook::exec('action' . $this->controller_name . 'ControllerSetMedia');
    }
}
