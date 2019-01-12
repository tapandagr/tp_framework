<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_ . 'tp_framework/tp_framework.php';

class AdminFrameworkEntitiesController extends ModuleAdminController
{
	public function __construct()
    {
    	$this->table = 'tp_framework_entity';
        $this->className = 'FrameworkEntity';
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bootstrap = true;
        parent::__construct();

        $this->bulk_actions = array('delete' => array('text' => $this->l('Διαγραφή επιλεγμένων'), 'confirm' => $this->l('Διαγραφή των επιλεγμένων στοιχείων;')));

        $this->fields_list = array(
            'id_tp_framework_entity' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30
            ),
            'language' => array(
                'title' => $this->l('Γλώσσα'),
                'width' => 25,
                'language' => 'status',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => false
            ),
            'meta_title' => array(
                'title' => $this->l('Όνομα'),
                'width' => 'auto'
            ),
            'class' => array(
                'title' => $this->l('Κλάση'),
                'width' => 500,
                'orderby' => false,
            )
        );
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Όνομα'),
                    'name' => 'meta_title',
                    'required' => true,
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Κλάσση'),
                    'name' => 'class',
                ),
				array(
                    'type' => 'switch',
                    'label' => $this->l('Γλώσσα'),
                    'name' => 'language',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Ναι')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Όχι')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Αποθήκευση'),
            )
        );

        return parent::renderForm();
    }
}
