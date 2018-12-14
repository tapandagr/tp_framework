<?php

/**
 * @author     Konstantinos A. Kogkalidis <konstantinos@tapanda.gr>
 * @copyright  2018 tapanda.gr <https://tapanda.gr/el/>
 * @license    Single website per license
 * @version    0.0.1
 * @since      0.0.1
 */

require_once _PS_MODULE_DIR_.'tp_framework/tp_framework.php';

class AdminFrameworkHooksController extends ModuleAdminController
{
    public function __construct()
    {
        //Framework module call
        $this->fw = new tp_framework();

        //Useful ’global’ vars
        $this->lid = Context::getContext()->language->id;

        $this->table = 'hook';
        $this->className = 'Hook';
        $this->lang = true;
        $this->bootstrap = true;

        parent::__construct();

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Διαγραφή επιλεγμένων'),
                'confirm' => $this->l('Διαγραφή των επιλεγμένων στοιχείων;')
            ),
            'enableSelection' => array('text' => $this->l('Ενεργοποίηση επιλεγμένων')),
            'disableSelection' => array('text' => $this->l('Απενεργοποίηση επιλεγμένων'))
        );

        $this->fields_list = array(
            'id_hook' => array(
                'title' => $this->l('ID'),
                'width' => 30,
                'type' => 'text',
            ),
            'name' => array(
                'title' => $this->l('Όνομα'),
                'width' => 'auto'
            ),
            'meta_title' => array(
                'title' => $this->l('Meta τίτλος'),
                'width' => 'auto'
            ),
            'meta_description' => array(
                'title' => $this->l('Meta περιγραφή'),
                'width' => 'auto'
            ),
        );
    }

    public function renderForm()
    {
        $status = array
        (
            array('id' => 1, 'meta_title' => $this->trans('Ενεργό', array(), 'Modules.tp_framework.Admin')),
            array('id' => 2, 'meta_title' => $this->trans('Ίδια γραμμή', array(), 'Modules.tp_framework.Admin')),
            array('id' => 0, 'meta_title' => $this->trans('Ανενεργό', array(), 'Modules.tp_framework.Admin'))
        );

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Άγκιστρο'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Κατάσταση'),
                    'name' => 'status',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Ενεργό'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Ανενεργό'),
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Όνομα'),
                    'name' => 'name',
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta τίτλος'),
                    'name' => 'meta_title',
                    'required' => true,
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta περιγραφή'),
                    'name' => 'meta_description',
                    'lang' => true,
                ),
            ),
            'submit' => array('title' => $this->l('Αποθήκευση')),
        );

        $this->fields_form['submit'] = array(
            'title' => $this->l('Αποθήκευση')
        );

        return parent::renderForm();
    }
}
