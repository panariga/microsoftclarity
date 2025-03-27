<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MicrosoftClarity extends Module
{

    public function __construct()
    {
        $this->name = 'microsoftclarity';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Panariga'; 
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.0', 
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Microsoft Clarity');
        $this->description = $this->l('Easily integrate the Microsoft Clarity tracking tag into your Prestashop store.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');


        if (!Configuration::get('MICROSOFTCLARITY_ID')) {
            $this->warning = $this->l('No Clarity Project ID provided.');
        }
    }

    public function install()
    {
        if (
            !parent::install() ||
            !$this->registerHook('displayHeader') ||
            !Configuration::updateValue('MICROSOFTCLARITY_ID', '')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (
            !parent::uninstall() ||
            !Configuration::deleteByName('MICROSOFTCLARITY_ID')
        ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitMicrosoftClarity')) {
            $clarityId = strval(Tools::getValue('MICROSOFTCLARITY_ID'));

            if (!empty($clarityId) && Validate::isGenericName($clarityId)) {
                Configuration::updateValue('MICROSOFTCLARITY_ID', $clarityId);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            } else {
                $output .= $this->displayError($this->l('Invalid Clarity Project ID'));
            }
        }

        return $output . $this->renderForm();
    }


    public function hookdisplayHeader($params)
    {
        if (!empty((new Cookie('psAdmin'))->id_employee)) {
            return; // Don't display the tag for logged-in administrators
        }

        $clarityId = Configuration::get('MICROSOFTCLARITY_ID');

        if (empty($clarityId)) {
            return; // Don't display the tag if the ID is not set
        }

        $this->context->smarty->assign(
            array(
                'clarity_id' => $clarityId,
            )
        );

        return $this->display(__FILE__ , 'views/templates/hook/microsoftclarity.tpl');
    }



    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $this->context->controller->getLanguages();
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submitMicrosoftClarity';
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array(array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Clarity Project ID'),
                        'name' => 'MICROSOFTCLARITY_ID',
                        'desc' => $this->l('Enter your Microsoft Clarity Project ID.'),
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        )));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'MICROSOFTCLARITY_ID' => Configuration::get('MICROSOFTCLARITY_ID'),
        );
    }
}
