<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2017 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it. Fact that you downloaded it for free does not mean that you can copy and redistribute it. You can just use it for free in your shop "as is" without removing the 'MyPresta' branding and advertises
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

if (!defined('_PS_VERSION_'))
{
    exit;
}

class fixedheader extends Module
{
    public function __construct()
    {
        $this->name = 'fixedheader';
        $this->author = 'MyPresta.eu';
        $this->mypresta_link = 'https://mypresta.eu/modules/front-office-features/fixed-sticky-header.html';
        $this->version = '1.3.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();
        $this->key = 'freelicense';
        $this->checkforupdates();

        $this->displayName = $this->trans('Fixed Header', array(), 'Modules.fixedheader');
        $this->description = $this->trans('Module creates fixed header in PrestaShop 1.7 in themes based on classic theme', array(), 'Modules.fiexedheader');
        $this->ps_versions_compliancy = array(
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_
        );
    }

    public static function psversion($part = 1)
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1)
        {
            return $exp[1];
        }
        if ($part == 2)
        {
            return $exp[2];
        }
        if ($part == 3)
        {
            return $exp[3];
        }
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayHeader');
    }

    public function inconsistency($ret)
    {
        return true;
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 11 //
        // ---------- //
        // ---------- //
        if ($form == 1)
        {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_modu\le_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        }
        else
        {
            if (defined('_PS_ADMIN_DIR_'))
            {
                if (Tools::isSubmit('submit_settings_updates'))
                {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') == false)
                {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200))
                    {
                        $actual_version = fixedheaderUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (fixedheaderUpdate::version($this->version) < fixedheaderUpdate::version(Configuration::get('updatev_' . $this->name)))
                    {
                        $this->warning = $this->l('New version available, check http://MyPresta.eu for more informations');
                    }
                }
                if ($display_msg == 1)
                {
                    if (fixedheaderUpdate::version($this->version) < fixedheaderUpdate::version(fixedheaderUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version)))
                    {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    }
                    else
                    {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        $html = '
        <div class="panel">
            <h3> <i class="icon-usd"></i> '.$this->l('MyPresta Modules').'</h3>
            <div class="alert alert-success">
                <strong>'.$this->l('We develop this module for free.').'</strong>
                '.$this->l('If you want, you can ').'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7WE8PTH4ZPYZA">'.$this->l('send PayPal donation').'</a>
            </div>
            <div style="diplay:block; clear:both; margin-bottom:20px;">
              <iframe src="//apps.facepages.eu/somestuff/onlyexample.html" width="100%" height="150" border="0" style="border:none;"></iframe>
            </div>
		</div>
        <div class="panel"><h3> <i class="icon-bell"></i> '.$this->l('Important notification').'</h3>
            <div class="alert alert-warning">
                <strong>'.$this->l('This is module for PrestaShop 1.7.').'<br/></strong>
                '.$this->l('Module creates sticky fixed header menu in themes based on "classic" theme. If you use different theme or modification of classic theme where the header works in a different way - it might be necessary to ').' <a href="https://mypresta.eu/prestashop-modifications/1h-prestashop-software-development.html">'.$this->l('personalize the module').'</a>
            </div>
        </div>';
        return $html . $this->checkforupdates(0, 1);
    }

    public function hookdisplayHeader($params)
    {
        $this->context->controller->addJS(($this->_path) . 'fixedheader.js', 'all');
        $this->context->controller->addCSS(($this->_path) . 'fixedheader.css', 'all');
    }

}


class fixedheaderUpdate extends fixedheader
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3)
        {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2)
        {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1)
        {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0)
        {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen"))
        {
            if (function_exists("file_get_contents"))
            {
                $actual_version = @file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}