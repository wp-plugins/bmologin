<?php

class Mobiweb_BMO_Login_Settings {
    public function add_menu_settings() {
        add_options_page(
            'Mobiweb BMO Login Settings',
            'BMO Login Form',
            'administrator',
            'bmologin_plugin_login_form_settings',
            array($this, 'bmologin_plugin_settings_callback')
        );
    }
    
    public function init_settings() {
        $this->init_login_form_settings();
    }
    
    /**
     * Initialize Settings
     */
    protected function init_login_form_settings() {
        add_settings_section(
            'login_form_section',
            __('BMO Login Form', 'mobiweb-bmologin'),
            array($this, 'login_form_section_callback'),
            'bmologin_plugin_login_form_settings'
        );
        add_settings_field(
            'setting_login_form_company_code',
            __('Company Code', 'mobiweb-bmologin'),
            array($this, 'login_form_company_code_callback'),
            'bmologin_plugin_login_form_settings',
            'login_form_section'
        );
        add_settings_field(
            'setting_login_form_width',
            __('Width(px)', 'mobiweb-bmologin'),
            array($this, 'login_form_width_callback'),
            'bmologin_plugin_login_form_settings',
            'login_form_section'
        );
        add_settings_field(
            'setting_login_form_height',
            __('Height(px)', 'mobiweb-bmologin'),
            array($this, 'login_form_height_callback'),
            'bmologin_plugin_login_form_settings',
            'login_form_section'
        );
        
        // register settings
        register_setting('bmologin_plugin_login_form_settings', 'setting_login_form_company_code');
        register_setting('bmologin_plugin_login_form_settings', 'setting_login_form_width');
        register_setting('bmologin_plugin_login_form_settings', 'setting_login_form_height');
        
        // Set default values
        // Nothing yet
    }
    
    /**
     * Callbacks
     */
    public function bmologin_plugin_settings_callback() {
        ?>
        <div class="wrap">
            <h2><?php _e('Settings', 'mobiweb-bmologin')?></h2>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('bmologin_plugin_login_form_settings');
                do_settings_sections('bmologin_plugin_login_form_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function login_form_section_callback() {
        // Do nothing
    }
    
    public function login_form_company_code_callback() {
        ?>
        <input type="text" name="setting_login_form_company_code" id="setting_login_form_company_code" value="<?php echo get_option('setting_login_form_company_code'); ?>" />
        <?php
    }
    
    public function login_form_width_callback() {
        ?>
        <input type="number" name="setting_login_form_width" id="setting_login_form_width" value="<?php echo get_option('setting_login_form_width'); ?>" />
        <?php
    }
    
    public function login_form_height_callback() {
        ?>
        <input type="number" name="setting_login_form_height" id="setting_login_form_height" value="<?php echo get_option('setting_login_form_height'); ?>" />
        <?php
    }
}