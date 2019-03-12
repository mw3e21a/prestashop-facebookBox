<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class FacebookBox extends Module
{
    protected $config_form = false;
    private $errors = array();
    private $output = '';


    public function __construct()
    {
        $this->name = 'FacebookBox';
        $this->tab = 'social_networks';
        $this->version = '0.6';
        $this->author = 'Michał Wilczyński';
        $this->need_instance = 0;
        $this->mkey = "freelicense";

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Facebook Box');
        $this->description = $this->l('Facebook plugin allow you to add facebook box to your footer. 
        Gain the trust of your customers, and social popularity.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }


    public function install()
    {
        Configuration::updateValue('FACEBOOKBOX_PAGE_URL', 'https://facebook.com/Facebook');
        Configuration::updateValue('FACEBOOKBOX_SECOND_PAGE_URL', 'https://facebook.com/Facebook');
        Configuration::updateValue('FACEBOOKBOX_WIDTH', 300);
        Configuration::updateValue('FACEBOOKBOX_HEIGHT', 250);
        Configuration::updateValue('FACEBOOKBOX_ADAPT_CONTAINER_WIDTH', 1);
        Configuration::updateValue('FACEBOOKBOX_USE_SMALL_HEADER', 0);
        Configuration::updateValue('FACEBOOKBOX_HIDE_COVER_PHOTO', 0);
        Configuration::updateValue('FACEBOOKBOX_SHOW_FRIENDS_FACES', 1);
        Configuration::updateValue('FACEBOOKBOX_HOOK_POSITION', 0);
        Configuration::updateValue('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE', 0);
        Configuration::updateValue('FACEBOOKBOX_ACTIVE_SECOND_PAGE', 0);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('displayLeftColumn') &&
            $this->registerHook('displayRightColumn') &&
            $this->registerHook('displayAfterBodyOpeningTag');
    }

    public function uninstall()
    {
        Configuration::deleteByName('FACEBOOKBOX_PAGE_URL');
        Configuration::deleteByName('FACEBOOKBOX_SECOND_PAGE_URL');
        Configuration::deleteByName('FACEBOOKBOX_WIDTH');
        Configuration::deleteByName('FACEBOOKBOX_HEIGHT');
        Configuration::deleteByName('FACEBOOKBOX_ADAPT_CONTAINER_WIDTH');
        Configuration::deleteByName('FACEBOOKBOX_USE_SMALL_HEADER');
        Configuration::deleteByName('FACEBOOKBOX_HIDE_COVER_PHOTO');
        Configuration::deleteByName('FACEBOOKBOX_SHOW_FRIENDS_FACES');
        Configuration::deleteByName('FACEBOOKBOX_HOOK_POSITION');
        Configuration::deleteByName('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE');
        Configuration::deleteByName('FACEBOOKBOX_ACTIVE_SECOND_PAGE');

        return parent::uninstall();
    }


    private function postValidation()
    {
        if (!Tools::getValue('FACEBOOKBOX_PAGE_URL')) {
            $this->errors[] = $this->trans('Facebook Page URL can not be empty', array(), 'Modules.Facebookbox.Admin');
        }
        if (!Tools::getValue('FACEBOOKBOX_SECOND_PAGE_URL') && Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE')) {
            $this->errors[] = $this->trans('Second Facebook Page URL can not be empty', array(), 'Modules.Facebookbox.Admin');
        }
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitFacebookBoxModule')) == true) {
            $this->postValidation();
            if (!count($this->errors)) {
                $this->postProcess();

                $this->output = $this->displayConfirmation("Success! All data updated.");
            } else {
                $this->output = $this->displayError($this->errors);
            }
        };

        $this->output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $this->context->smarty->assign('module_dir', $this->_path);
        return $this->output . $this->renderForm();
    }

    /**
     * Create the form that is displayed in the configuration of module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitFacebookBoxModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );


        /**
         * if second FanPage option is enabled, append new rows to form
         */
        $extraRows = null;
        if (Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE') == true) {
            $extraRows = $this->getSecondPageFormRows();
        }

        return $helper->generateForm(array($this->getConfigForm($extraRows)));
    }

    /**
     * @return array
     * Generate array of rows append to form
     */

    protected function getSecondPageFormRows()
    {
        $secondFanPageUrl =   array(
                'col' => 3,
                'type' => 'text',
                'class' => '',
                'name' => 'FACEBOOKBOX_SECOND_PAGE_URL',
                'label' => $this->l('Second facebook Page URL'),
                'required' =>true,
            );

        $secondFanPageHookPosition = array(
             'type' => 'select',
             'name' => 'FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE',
             'desc' => $this->l('When Automatically adapt box size option is enabled, width is generated automatically.'),
             'label' => $this->l('Select Position of second Facebook Box'),
             'options' => array(
                 'query' =>  array(
                     array(
                         'id_option' => 0,       // The value of the 'value' attribute of the <option> tag.
                         'name' => 'Footer'    // The value of the text content of the  <option> tag.
                     ),
                     array(
                         'id_option' => 1,
                         'name' => 'Left column'
                     ),
                     array(
                         'id_option' => 2,
                         'name' => 'Right column'
                     ),
                 ),
                 'id' => 'id_option',
                 'name' => 'name'
             ),
         );

        return array($secondFanPageUrl,$secondFanPageHookPosition);
    }

    /**
     * Create the structure of the form.
     * @param $extraRows[]
     * @return array
     */
    protected function getConfigForm($extraRows)
    {
        $inputs = array(
            array(
                'col' => 3,
                'type' => 'text',

                'name' => 'FACEBOOKBOX_PAGE_URL',
                'label' => $this->l('Facebook Page URL'),
                'required' =>true,
            ),
            array(
                'type' => 'switch',
                'name' => 'FACEBOOKBOX_ACTIVE_SECOND_PAGE',
                'label' => $this->l('Activate second FanPage on shop'),
                'values' => array(
                    array(
                        'id' => 'FACEBOOKBOX_ACTIVE_SECOND_PAGE_ON',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'FACEBOOKBOX_ACTIVE_SECOND_PAGE_OFF',
                        'value' => 0,
                        'label' => $this->l('No')
                    )),
            ),

            array(
                'col' => 1,
                'type' => 'text',
                'name' => 'FACEBOOKBOX_WIDTH',
                'label' => $this->l('Width'),
            ),
            array(
                'col' => 1,
                'type' => 'text',
                'name' => 'FACEBOOKBOX_HEIGHT',
                'label' => $this->l('Height'),
            ),
            array(
                'type' => 'switch',
                'name' => 'FACEBOOKBOX_ADAPT_CONTAINER_WIDTH',
                'label' => $this->l('Automatically adapt box size'),
                'values' => array(
                    array(
                        'id' => 'FACEBOOKBOX_ADAPT_CONTAINER_WIDTH_ON',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'FACEBOOKBOX_ADAPT_CONTAINER_WIDTH_OFF',
                        'value' => 0,
                        'label' => $this->l('No')
                    )),
            ),
            array(
                'type' => 'switch',
                'name' => 'FACEBOOKBOX_USE_SMALL_HEADER',
                'label' => $this->l('Use small header'),
                'values' => array(
                    array(
                        'id' => 'FACEBOOKBOX_USE_SMALL_HEADER_ON',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'FACEBOOKBOX_USE_SMALL_HEADER_OFF',
                        'value' => 0,
                        'label' => $this->l('No')
                    )),
            ),
            array(
                'type' => 'switch',
                'name' => 'FACEBOOKBOX_HIDE_COVER_PHOTO',
                'label' => $this->l('Hide cover photo'),
                'values' => array(
                    array(
                        'id' => 'FACEBOOKBOX_HIDE_COVER_PHOTO_ON',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'FACEBOOKBOX_HIDE_COVER_PHOTO_OFF',
                        'value' => 0,
                        'label' => $this->l('No')
                    )),
            ),
            array(
                'type' => 'switch',
                'name' => 'FACEBOOKBOX_SHOW_FRIENDS_FACES',
                'label' => $this->l('Show friends faces'),
                'values' => array(
                    array(
                        'id' => 'FACEBOOKBOX_SHOW_FRIENDS_FACES_ON',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'FACEBOOKBOX_SHOW_FRIENDS_FACES_OFF',
                        'value' => 0,
                        'label' => $this->l('No')
                    )),
            ),
            array(
                'type' => 'select',
                'name' => 'FACEBOOKBOX_HOOK_POSITION',
                'desc' => $this->l('When Automatically adapt box size option is enabled, width is generated automatically.'),
                'label' => $this->l('Select position of Facebook Box'),
                'options' => array(
                    'query' =>  array(
                        array(
                            'id_option' => 0,       // The value of the 'value' attribute of the <option> tag.
                            'name' => 'Footer'    // The value of the text content of the  <option> tag.
                        ),
                        array(
                            'id_option' => 1,
                            'name' => 'Left column'
                        ),
                        array(
                            'id_option' => 2,
                            'name' => 'Right column'
                        ),
                    ),
                    'id' => 'id_option',
                    'name' => 'name'
                ),
            ),
        );

        if ($extraRows) {
            foreach ($extraRows as $rows) {
                array_push($inputs, $rows);
            }
        }

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'FACEBOOKBOX_PAGE_URL' => Configuration::get('FACEBOOKBOX_PAGE_URL'),
            'FACEBOOKBOX_SECOND_PAGE_URL' => Configuration::get('FACEBOOKBOX_SECOND_PAGE_URL'),
            'FACEBOOKBOX_WIDTH' => Configuration::get('FACEBOOKBOX_WIDTH'),
            'FACEBOOKBOX_HEIGHT' => Configuration::get('FACEBOOKBOX_HEIGHT'),
            'FACEBOOKBOX_ADAPT_CONTAINER_WIDTH' => Configuration::get('FACEBOOKBOX_ADAPT_CONTAINER_WIDTH'),
            'FACEBOOKBOX_USE_SMALL_HEADER' => Configuration::get('FACEBOOKBOX_USE_SMALL_HEADER'),
            'FACEBOOKBOX_HIDE_COVER_PHOTO' => Configuration::get('FACEBOOKBOX_HIDE_COVER_PHOTO'),
            'FACEBOOKBOX_SHOW_FRIENDS_FACES' => Configuration::get('FACEBOOKBOX_SHOW_FRIENDS_FACES'),
            'FACEBOOKBOX_HOOK_POSITION' => Configuration::get('FACEBOOKBOX_HOOK_POSITION'),
            'FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE' => Configuration::get('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE'),
            'FACEBOOKBOX_ACTIVE_SECOND_PAGE' => Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE'),

        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * CSS & JavaScript files loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/jquery-validate.js');
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * CSS & JavaScript files added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }


    public function hookDisplayAfterBodyOpeningTag()
    {
        return $this->display(__FILE__, './views/templates/front/javaScriptSDK.tpl');
    }

    public function getSmartyVars($fanPageUrl)
    {
        $smartyVars = array(
            'FACEBOOKBOX_PAGE_URL' => $fanPageUrl,
            'FACEBOOKBOX_WIDTH' => Configuration::get('FACEBOOKBOX_WIDTH'),
            'FACEBOOKBOX_HEIGHT' => Configuration::get('FACEBOOKBOX_HEIGHT'),
            'FACEBOOKBOX_ADAPT_CONTAINER_WIDTH' => Configuration::get('FACEBOOKBOX_ADAPT_CONTAINER_WIDTH'),
            'FACEBOOKBOX_USE_SMALL_HEADER' => Configuration::get('FACEBOOKBOX_USE_SMALL_HEADER'),
            'FACEBOOKBOX_HIDE_COVER_PHOTO' => Configuration::get('FACEBOOKBOX_HIDE_COVER_PHOTO'),
            'FACEBOOKBOX_SHOW_FRIENDS_FACES' => Configuration::get('FACEBOOKBOX_SHOW_FRIENDS_FACES'));

        return $smartyVars;
    }

    public function hookDisplayFooter()
    {
        $facebookBoxes = array();

        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE') == 0 && Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE') == 1) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_SECOND_PAGE_URL'));
        }
        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION') == 0) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_PAGE_URL'));
        }

        $this->context->smarty->assign(
            'FacebookBoxes',
            $facebookBoxes
        );
        return $this->display(__FILE__, './views/templates/front/facebookBox.tpl');
    }

    public function hookDisplayLeftColumn()
    {
        $facebookBoxes = array();

        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE') == 1 && Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE') == 1) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_SECOND_PAGE_URL'));
        }
        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION') == 1) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_PAGE_URL'));
        }

        $this->context->smarty->assign(
            'FacebookBoxes',
            $facebookBoxes
            );

        return $this->display(__FILE__, './views/templates/front/facebookBox.tpl');
    }

    public function hookDisplayRightColumn()
    {
        $facebookBoxes = array();

        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION_SECOND_PAGE') == 2 && Configuration::get('FACEBOOKBOX_ACTIVE_SECOND_PAGE') == 1) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_SECOND_PAGE_URL'));
        }
        if (Configuration::get('FACEBOOKBOX_HOOK_POSITION') == 2) {
            $facebookBoxes[] = $this->getSmartyVars(Configuration::get('FACEBOOKBOX_PAGE_URL'));
        }

        $this->context->smarty->assign(
            'FacebookBoxes',
            $facebookBoxes
         );

        return $this->display(__FILE__, './views/templates/front/facebookBox.tpl');
    }
}
