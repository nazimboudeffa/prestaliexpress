<?php
if (!defined('_PS_VERSION_'))
{
  exit;
}

class prestaliexpress extends Module
{

  public function __construct()
  {
    $this->name = 'prestaliexpress';
    $this->tab = 'front_office_features';
    $this->version = '1.0.0';
    $this->author = 'Nazim Boudeffa';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;

    parent::__construct();

    $this->displayName = $this->l('Ali Express Dropshipping');
    $this->description = $this->l('Adds a search by id on AliExpress for Dropshipping.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('MYMODULE_NAME') ||
        !Configuration::get('ALI_API_KEY') ||
        !Configuration::get('ALI_TRACKING_ID') ||
        !Configuration::get('ALI_DIGITAL_SIGNATURE')
    )
      $this->warning = $this->l('No name provided');
  }

  public function install()
  {
    if (Shop::isFeatureActive())
      Shop::setContext(Shop::CONTEXT_ALL);

    if (!parent::install() ||
      !$this->registerHook('leftColumn') ||
      !$this->registerHook('header') ||
      !Configuration::updateValue('MYMODULE_NAME', 'my friend') ||
      !Configuration::updateValue('ALI_API_KEY', 'please enter your api key') ||
      !Configuration::updateValue('ALI_TRACKING_ID', 'please enter your tracking id') ||
      !Configuration::updateValue('ALI_DIGITAL_SIGNATURE', 'please enter your digital signature')
    )
      return false;

    return true;
  }

  public function uninstall()
  {
    if (!parent::uninstall() ||
      !Configuration::deleteByName('MYMODULE_NAME') ||
      !Configuration::deleteByName('ALI_API_KEY') ||
      !Configuration::deleteByName('ALI_TRACKING_ID') ||
      !Configuration::deleteByName('ALI_DIGITAL_SIGNATURE')
    )
      return false;

    return true;
  }

  public function getContent()
  {
      $output = null;

      if (Tools::isSubmit('submit'.$this->name))
      {
          $my_module_name = strval(Tools::getValue('MYMODULE_NAME'));

          $ali_api_key = strval(Tools::getValue('ALI_API_KEY'));
          $ali_tracking_id = strval(Tools::getValue('ALI_TRACKING_ID'));
          $ali_digital_signture = strval(Tools::getValue('ALI_DIGITAL_SIGNATURE'));

          if (!$my_module_name
            || empty($my_module_name)
            || !Validate::isGenericName($my_module_name))
              $output .= $this->displayError($this->l('Invalid Configuration value'));
          else
          {
              Configuration::updateValue('MYMODULE_NAME', $my_module_name);

              Configuration::updateValue('ALI_API_KEY', $ali_api_key);
              Configuration::updateValue('ALI_TRACKING_ID', $ali_tracking_id);
              Configuration::updateValue('ALI_DIGITAL_SIGNATURE', $ali_digital_signture);

              $output .= $this->displayConfirmation($this->l('Settings updated'));
          }
      }
      return $output.$this->displayForm();
  }

  public function displayForm()
  {
      // Get default language
      $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

      // Init Fields form array
      $fields_form[0]['form'] = array(
          'legend' => array(
              'title' => $this->l('Settings'),
          ),
          'input' => array(
              array(
                  'type' => 'text',
                  'label' => $this->l('Configuration value'),
                  'name' => 'MYMODULE_NAME',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'text',
                  'label' => $this->l('API KEY'),
                  'name' => 'ALI_API_KEY',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'text',
                  'label' => $this->l('TRACKING ID'),
                  'name' => 'ALI_TRACKING_ID',
                  'size' => 20,
                  'required' => true
              ),
              array(
                  'type' => 'text',
                  'label' => $this->l('DIGITAL SIGNATURE'),
                  'name' => 'ALI_DIGITAL_SIGNATURE',
                  'size' => 20,
                  'required' => true
              )
          ),
          'submit' => array(
              'title' => $this->l('Save'),
              'class' => 'btn btn-default pull-right'
          )
      );

      $helper = new HelperForm();

      // Module, token and currentIndex
      $helper->module = $this;
      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

      // Language
      $helper->default_form_language = $default_lang;
      $helper->allow_employee_form_lang = $default_lang;

      // Title and toolbar
      $helper->title = $this->displayName;
      $helper->show_toolbar = true;        // false -> remove toolbar
      $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
      $helper->submit_action = 'submit'.$this->name;
      $helper->toolbar_btn = array(
          'save' =>
          array(
              'desc' => $this->l('Save'),
              'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
              '&token='.Tools::getAdminTokenLite('AdminModules'),
          ),
          'back' => array(
              'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
              'desc' => $this->l('Back to list')
          )
      );

      // Load current value
      $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
      $helper->fields_value['ALI_API_KEY'] = Configuration::get('ALI_API_KEY');
      $helper->fields_value['ALI_TRACKING_ID'] = Configuration::get('ALI_TRACKING_ID');
      $helper->fields_value['ALI_DIGITAL_SIGNATURE'] = Configuration::get('ALI_DIGITAL_SIGNATURE');

      return $helper->generateForm($fields_form);
  }
}
