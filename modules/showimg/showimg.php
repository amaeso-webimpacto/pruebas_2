<?php
/**
* 2007-2020 PrestaShop
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
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Showimg extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'showimg';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'anabelmaeso';
        $this->need_instance = 1;

       
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Show Images');
        $this->description = $this->l('Modulo que muestra una galería de imágenes  ancla en el front office ');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:showimg/showimg.tpl';
    }

    
    public function install()
    {
        return (parent::install() &&
            $this->registerHook('displayHome') &&
            $this->registerHook('actionObjectLanguageAddAfter') &&
            $this->installFixtures() &&
            $this->disableDevice(Context::DEVICE_MOBILE));
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        return 
        $this->installFixture((int)$params['object']->id, Configuration::get('IMG_1', (int)Configuration::get('PS_LANG_DEFAULT'))) &&
        $this->installFixture((int)$params['object']->id, Configuration::get('LINK_IMG_1', (int)Configuration::get('PS_LANG_DEFAULT')));
    }

    protected function installFixtures()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $this->installFixture((int)$lang['id_lang'], 'adjfkld.jpg');
        }

        return true;
    }

    protected function installFixture($id_lang, $image = null)
    {
        //IMG 1
        $values['IMG_1'][(int)$id_lang] = $image;
        $values['LINK_IMG_1'][(int)$id_lang] = '';
        $values['DESC_IMG_1'][(int)$id_lang] = '';

        //IMG 2
        $values['IMG_2'][(int)$id_lang] = $image;
        $values['LINK_IMG_2'][(int)$id_lang] = '';
        $values['DESC_IMG_2'][(int)$id_lang] = '';

        //IMG 3
        $values['IMG_3'][(int)$id_lang] = $image;
        $values['LINK_IMG_3'][(int)$id_lang] = '';
        $values['DESC_IMG_3'][(int)$id_lang] = '';

        Configuration::updateValue('IMG_1', $values['IMG_1']);
        Configuration::updateValue('LINK_IMG_1', $values['LINK_IMG_1']);
        Configuration::updateValue('DESC_IMG_1', $values['DESC_IMG_1']);

        Configuration::updateValue('IMG_2', $values['IMG_2']);
        Configuration::updateValue('LINK_IMG_2', $values['LINK_IMG_2']);
        Configuration::updateValue('DESC_IMG_2', $values['DESC_IMG_2']);

        Configuration::updateValue('IMG_3', $values['IMG_3']);
        Configuration::updateValue('LINK_IMG_3', $values['LINK_IMG_3']);
        Configuration::updateValue('DESC_IMG_3', $values['DESC_IMG_3']);

        
        
        /* ORIGINAL CODE
        $values['BANNER_IMG'][(int)$id_lang] = $image;
        $values['BANNER_LINK'][(int)$id_lang] = '';
        $values['BANNER_DESC'][(int)$id_lang] = '';

        Configuration::updateValue('BANNER_IMG', $values['BANNER_IMG']);
        Configuration::updateValue('BANNER_LINK', $values['BANNER_LINK']);
        Configuration::updateValue('BANNER_DESC', $values['BANNER_DESC']);
        */
    }

    public function uninstall()
    {
        Configuration::deleteByName('BANNER_IMG');
        Configuration::deleteByName('BANNER_LINK');
        Configuration::deleteByName('BANNER_DESC');

        return parent::uninstall();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitStoreConf')) {
            $languages = Language::getLanguages(false);
            $values = array();
            $update_images_values = false;

            foreach ($languages as $lang) {
                if (isset($_FILES['IMG_1_'.$lang['id_lang']])
                    && isset($_FILES['IMG_1_'.$lang['id_lang']]['tmp_name'])
                    && !empty($_FILES['IMG_1_'.$lang['id_lang']]['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['IMG_1_'.$lang['id_lang']], 4000000)) {
                        return $error;
                    } else {
                        $ext = substr($_FILES['IMG_1_'.$lang['id_lang']]['name'], strrpos($_FILES['IMG_1_'.$lang['id_lang']]['name'], '.') + 1);
                        $file_name = md5($_FILES['IMG_1_'.$lang['id_lang']]['name']).'.'.$ext;

                        if (!move_uploaded_file($_FILES['IMG_1_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name)) {
                            return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                        } else {
                            if (Configuration::hasContext('IMG_1', $lang['id_lang'], Shop::getContext())
                                && Configuration::get('IMG_1', $lang['id_lang']) != $file_name) {
                                @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . Configuration::get('IMG_1', $lang['id_lang']));
                            }

                            $values['IMG_1'][$lang['id_lang']] = $file_name;
                        }
                    }

                    $update_images_values = true;
                }

                $values['LINK_IMG_1'][$lang['id_lang']] = Tools::getValue('LINK_IMG_1_'.$lang['id_lang']);
                $values['DESC_IMG_1'][$lang['id_lang']] = Tools::getValue('DESC_IMG_1_'.$lang['id_lang']);
            }

            if ($update_images_values) {
                Configuration::updateValue('IMG_1', $values['IMG_1']);
            }

            Configuration::updateValue('LINK_IMG_1', $values['LINK_IMG_1']);
            Configuration::updateValue('DESC_IMG_1', $values['DESC_IMG_1']);

            $this->_clearCache($this->templateFile);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        
        return ''; 

        /*ORIGINAL CODE
        if (Tools::isSubmit('submitStoreConf')) {
            $languages = Language::getLanguages(false);
            $values = array();
            $update_images_values = false;

            foreach ($languages as $lang) {
                if (isset($_FILES['BANNER_IMG_'.$lang['id_lang']])
                    && isset($_FILES['BANNER_IMG_'.$lang['id_lang']]['tmp_name'])
                    && !empty($_FILES['BANNER_IMG_'.$lang['id_lang']]['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['BANNER_IMG_'.$lang['id_lang']], 4000000)) {
                        return $error;
                    } else {
                        $ext = substr($_FILES['BANNER_IMG_'.$lang['id_lang']]['name'], strrpos($_FILES['BANNER_IMG_'.$lang['id_lang']]['name'], '.') + 1);
                        $file_name = md5($_FILES['BANNER_IMG_'.$lang['id_lang']]['name']).'.'.$ext;

                        if (!move_uploaded_file($_FILES['BANNER_IMG_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name)) {
                            return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                        } else {
                            if (Configuration::hasContext('BANNER_IMG', $lang['id_lang'], Shop::getContext())
                                && Configuration::get('BANNER_IMG', $lang['id_lang']) != $file_name) {
                                @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . Configuration::get('BANNER_IMG', $lang['id_lang']));
                            }

                            $values['BANNER_IMG'][$lang['id_lang']] = $file_name;
                        }
                    }

                    $update_images_values = true;
                }

                $values['BANNER_LINK'][$lang['id_lang']] = Tools::getValue('BANNER_LINK_'.$lang['id_lang']);
                $values['BANNER_DESC'][$lang['id_lang']] = Tools::getValue('BANNER_DESC_'.$lang['id_lang']);
            }

            if ($update_images_values) {
                Configuration::updateValue('BANNER_IMG', $values['BANNER_IMG']);
            }

            Configuration::updateValue('BANNER_LINK', $values['BANNER_LINK']);
            Configuration::updateValue('BANNER_DESC', $values['BANNER_DESC']);

            $this->_clearCache($this->templateFile);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        return '';*/
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'tinymce' => true,
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    //IMG 1
                   /* FORMATO ORIGINAL DE LA IMG array(
                        'type' => 'file_lang',
                        'label' => $this->trans('Image 1', array(), 'Modules.Banner.Admin'),
                        'name' => 'BANNER_IMG',
                        'desc' => $this->trans('Upload an image'),
                        'lang' => true,
                    ),*/
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Image 1', array(), 'Modules.Banner.Admin'),
                        'name' => 'IMG_1',
                        'desc' => $this->trans('The URL of the image to show on the frontoffice')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Link 1', array(), 'Modules.Banner.Admin'),
                        'name' => 'LINK_IMG_1',
                        'desc' => $this->trans('Enter the link associated to your image. When clicking on the banner, the link opens in the same window. If no link is entered, it redirects to the homepage.')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Description 1', array(), 'Modules.Banner.Admin'),
                        'name' => 'DESC_IMG_1',
                        'desc' => $this->trans('Please enter a short but meaningful description.')
                    ),
                    //IMG 2
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Image 2', array(), 'Modules.Banner.Admin'),
                        'name' => 'IMG_2',
                        'desc' => $this->trans('The URL of the image to show on the frontoffice')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Link 2', array(), 'Modules.Banner.Admin'),
                        'name' => 'LINK_IMG_2',
                        'desc' => $this->trans('Enter the link associated to your image. When clicking on the banner, the link opens in the same window. If no link is entered, it redirects to the homepage.')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Description 2', array(), 'Modules.Banner.Admin'),
                        'name' => 'DESC_IMG_2',
                        'desc' => $this->trans('Please enter a short but meaningful description.')
                    ),
                    //IMG 3
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Image 3', array(), 'Modules.Banner.Admin'),
                        'name' => 'IMG_3',
                        'desc' => $this->trans('The URL of the image to show on the frontoffice')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Link 3', array(), 'Modules.Banner.Admin'),
                        'name' => 'LINK_IMG_3',
                        'desc' => $this->trans('Enter the link associated to image 3. When clicking on the banner, the link opens in the same window. If no link is entered, it redirects to the homepage.')
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->trans('Description 3', array(), 'Modules.Banner.Admin'),
                        'name' => 'DESC_IMG_3',
                        'desc' => $this->trans('Please enter a short but meaningful description.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitStoreConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
       
        $languages = Language::getLanguages(false);
        $fields = array();

       
        foreach ($languages as $lang) {
            //IMG 1
            $fields['IMG_1'][$lang['id_lang']] = Tools::getValue('IMG_1_'.$lang['id_lang'], Configuration::get('IMG_1', $lang['id_lang']));
            $fields['LINK_IMG_1'][$lang['id_lang']] = Tools::getValue('LINK_IMG_1_'.$lang['id_lang'], Configuration::get('LINK_IMG_1', $lang['id_lang']));
            $fields['DESC_IMG_1'][$lang['id_lang']] = Tools::getValue('DESC_IMG_1_'.$lang['id_lang'], Configuration::get('DESC_IMG_1', $lang['id_lang']));
            //IMG 2
            $fields['IMG_2'][$lang['id_lang']] = Tools::getValue('IMG_2_'.$lang['id_lang'], Configuration::get('IMG_2', $lang['id_lang']));
            $fields['LINK_IMG_2'][$lang['id_lang']] = Tools::getValue('LINK_IMG_2_'.$lang['id_lang'], Configuration::get('LINK_IMG_2', $lang['id_lang']));
            $fields['DESC_IMG_2'][$lang['id_lang']] = Tools::getValue('DESC_IMG_2_'.$lang['id_lang'], Configuration::get('DESC_IMG_2', $lang['id_lang']));
            //IMG 3
            $fields['IMG_3'][$lang['id_lang']] = Tools::getValue('IMG_3_'.$lang['id_lang'], Configuration::get('IMG_3', $lang['id_lang']));
            $fields['LINK_IMG_3'][$lang['id_lang']] = Tools::getValue('LINK_IMG_3_'.$lang['id_lang'], Configuration::get('LINK_IMG_3', $lang['id_lang']));
            $fields['DESC_IMG_3'][$lang['id_lang']] = Tools::getValue('DESC_IMG_3_'.$lang['id_lang'], Configuration::get('DESC_IMG_3', $lang['id_lang']));
            
        }

        return $fields;
        //ORIGINAL
        /*
        $languages = Language::getLanguages(false);
        $fields = array();

        foreach ($languages as $lang) {
            
            $fields['BANNER_IMG'][$lang['id_lang']] = Tools::getValue('BANNER_IMG_'.$lang['id_lang'], Configuration::get('BANNER_IMG', $lang['id_lang']));
            $fields['BANNER_LINK'][$lang['id_lang']] = Tools::getValue('BANNER_LINK_'.$lang['id_lang'], Configuration::get('BANNER_LINK', $lang['id_lang']));
            $fields['BANNER_DESC'][$lang['id_lang']] = Tools::getValue('BANNER_DESC_'.$lang['id_lang'], Configuration::get('BANNER_DESC', $lang['id_lang']));
            
        }

        return $fields;*/
    }

    public function renderWidget($hookName, array $params)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('showimg'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('showimg'));
    }

    public function getWidgetVariables($hookName, array $params)
    {
        $imgname = Configuration::get('IMG_1', $this->context->language->id);

        if ($imgname && file_exists(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$imgname)) {
            $this->smarty->assign('img_1', $this->context->link->protocol_content . Tools::getMediaServer($imgname) . $this->_path . 'img/' . $imgname);
        }

        $banner_link = Configuration::get('BANNER_LINK', $this->context->language->id);
        if (!$banner_link) {
            $banner_link = $this->context->link->getPageLink('index');
        }

        return array(
            'banner_link' => $this->updateUrl($banner_link),
            'banner_desc' => Configuration::get('BANNER_DESC', $this->context->language->id)
        );
    }

    private function updateUrl($link)
    {
        if (substr($link, 0, 7) !== "http://" && substr($link, 0, 8) !== "https://") {
            $link = "http://" . $link;
        }

        return $link;
    }

    public function hookDisplayHome ($paramas){
        
       /* 
        $imgname = Configuration::get('IMG_1', $this->context->language->id);
        //variables que vienen el input en configuracion del modulo
        $valor = Configuration::get('MODULO_TAREA4_VALOR');
        $minimo = Configuration::get('MODULO_TAREA4_MINIMO_SUMAR');
        */

        
        $languages = Language::getLanguages(false);
       

       
        foreach ($languages as $lang) {
            //IMG 1
            $img1['IMG_1'][$lang['id_lang']] = Tools::getValue('IMG_1_'.$lang['id_lang'], Configuration::get('IMG_1', $lang['id_lang']));
            $url_img1['LINK_IMG_1'][$lang['id_lang']] = Tools::getValue('LINK_IMG_1_'.$lang['id_lang'], Configuration::get('LINK_IMG_1', $lang['id_lang']));
            $desc_img1['DESC_IMG_1'][$lang['id_lang']] = Tools::getValue('DESC_IMG_1_'.$lang['id_lang'], Configuration::get('DESC_IMG_1', $lang['id_lang']));
            /*//IMG 2
            $fields['IMG_2'][$lang['id_lang']] = Tools::getValue('IMG_2_'.$lang['id_lang'], Configuration::get('IMG_2', $lang['id_lang']));
            $fields['LINK_IMG_2'][$lang['id_lang']] = Tools::getValue('LINK_IMG_2_'.$lang['id_lang'], Configuration::get('LINK_IMG_2', $lang['id_lang']));
            $fields['DESC_IMG_2'][$lang['id_lang']] = Tools::getValue('DESC_IMG_2_'.$lang['id_lang'], Configuration::get('DESC_IMG_2', $lang['id_lang']));
            //IMG 3
            $fields['IMG_3'][$lang['id_lang']] = Tools::getValue('IMG_3_'.$lang['id_lang'], Configuration::get('IMG_3', $lang['id_lang']));
            $fields['LINK_IMG_3'][$lang['id_lang']] = Tools::getValue('LINK_IMG_3_'.$lang['id_lang'], Configuration::get('LINK_IMG_3', $lang['id_lang']));
            $fields['DESC_IMG_3'][$lang['id_lang']] = Tools::getValue('DESC_IMG_3_'.$lang['id_lang'], Configuration::get('DESC_IMG_3', $lang['id_lang']));
            //ORIGINAL
            $fields['BANNER_IMG'][$lang['id_lang']] = Tools::getValue('BANNER_IMG_'.$lang['id_lang'], Configuration::get('BANNER_IMG', $lang['id_lang']));
            $fields['BANNER_LINK'][$lang['id_lang']] = Tools::getValue('BANNER_LINK_'.$lang['id_lang'], Configuration::get('BANNER_LINK', $lang['id_lang']));
            $fields['BANNER_DESC'][$lang['id_lang']] = Tools::getValue('BANNER_DESC_'.$lang['id_lang'], Configuration::get('BANNER_DESC', $lang['id_lang']));
            */
        }
        
        return $this->display(__FILE__, 'views/templates/hook/showimg.tpl'); 

    }
}