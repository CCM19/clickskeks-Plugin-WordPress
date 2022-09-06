<?php

/*
    Plugin Name: Clickskeks
    description: Clickskeks
    Author: CLICKSKEKS GMBH & CO KG
    Version: 1.2.1
*/

class CKeksScriptInserter {
    
    protected $CKeksScriptKey = '';
    
    public function __construct() {
        
        $this->CKeksScriptKey = get_option('ckeks_script_key');
        
        if( $this->CKeksScriptKey && ( !is_admin() && !$this->ckeks_is_login_page() ) )
        {
            add_action( 'init', [ $this, 'ckeks_enqueue_my_scripts' ], -999 );
        }
        
        add_action( 'admin_menu', [ $this, 'ckeks_create_plugin_settings_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'ckeks_enqueue_my_admin_scripts' ] );
        
        add_shortcode('clickskeks', [ $this, 'ckeks_shortcode_cookietable' ]);
    }
    
    public function ckeks_is_login_page() {
        return in_array( $GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php') );
    }
    
    public function ckeks_enqueue_my_scripts() {
        wp_enqueue_script('keks', 'https://static.clickskeks.at/'.$this->CKeksScriptKey.'/bundle.js' );
    }
    
    public function ckeks_enqueue_my_admin_scripts() {
        wp_enqueue_script('keks_admin', plugins_url( 'js/ckeks_admin.js', __FILE__ ) );
        wp_enqueue_style('keks', plugins_url( 'keks.css', __FILE__ ) );
    }
    
    public function ckeks_shortcode_cookietable() {
        return '<script id="clickskeks-disclaimer-script" src="https://static.clickskeks.at/'.$this->CKeksScriptKey.'/disclaimer.js" type="application/javascript"></script>';
    }
    
    public function ckeks_create_plugin_settings_page() {
        // Add the menu item and page
        $page_title = 'Clickskeks';
        $menu_title = 'Clickskeks';
        $capability = 'manage_options';
        $slug = 'clickskeks';
        $callback = array( $this, 'ckeks_plugin_settings_page_content' );
        $icon = 'dashicons-keks';
        $position = 100;
        
        add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }
    
    public function ckeks_plugin_settings_page_content() {
        
        if( (isset($_POST['updated'])) && ($_POST['updated'] === 'true') ){
            $this->ckeks_handle_form();
        }
        
        ?>
        <div class="wrap clickskeks" style="background: white; padding: 1rem">
            <img src="<?php echo plugins_url( 'img/logo.png', __FILE__ ); ?>" style="width: 220px;">
            
            <form method="POST" id="ckeksform" name="ckeksform">
                <input type="hidden" name="updated" value="true" />
                <input type="hidden" name="submit_type"/>
                <?php wp_nonce_field( 'script_update', 'script_add_form' ); ?>
                <br/>
                <strong style="font-size: 1rem"><?php _e('clickskeks - gemeinsam gegen DSGVO Strafen', 'clickskeks'); ?></strong>
                <p><?php _e('Das clickskeks Cookie-Management-Plugin aus Österreich gibt dir die volle Kontrolle über Cookies und Tracker auf deiner Website. Nach einem Erstscan deiner Website werden Cookies identifiziert und dein DSGVO-konformer Cookie-Banner erstellt, welcher deinen Usern die gesetzlich vorgeschriebene aktive Einwilligung zu Cookies ermöglicht.', 'clickskeks'); ?></p>
                <p><?php _e('clickskeks überprüft daraufhin regelmäßig und automatisch deine Seite auf neue oder veränderte Cookies und informiert dich, wenn Anpassungen notwendig sind. Du kannst clickskeks 30 Tage kostenlos testen und erhältst anschließend dein Cookie-Tool ab nur 9,90 EUR im Monat.', 'clickskeks'); ?></p>
                <p><?php _e('Deinen 30 Tage Test kannst du auf <a href="https://www.clickskeks.at/">clickskeks.at</a> bekommen!', 'clickskeks'); ?></p>
                <p><?php _e('Bei Fragen wende dich bitte an <a href="mailto:hallo@clickskeks.at">hallo@clickskeks.at</a>', 'clickskeks'); ?></p>
                
                <h2 style="margin-top:40px;font-size: 1rem"><?php _e('Cookie-Einbindung in deine Datenschutzerklärung', 'clickskeks'); ?></h2>
                <p><?php _e('Damit deine Website DSGVO-konform ist, musst du die Cookies auch in deiner Datenschutzerklärung anführen. <br/>Gehe dazu auf deine Datenschutz-Seite zu dem Abschnitt "Cookies" und füge hier den Shortcode <b style="color:#000;">[clickskeks]</b> ein. Speichere die Änderungen und schon werden deine gesetzten Cookies als Tabelle angezeigt.', 'clickskeks'); ?></p>
                <img src="<?php echo plugins_url( 'img/screenshot.png', __FILE__ ); ?>" style="display:block;max-width:100%;height:auto;margin:0 0 40px;padding:10px;border:1px solid #7e8993;" width="634" height="141" />
    
                <label for="ckeks_script_key"><strong><?php _e('Bitte geben Sie hier Ihren Clickskeks Code ein!', 'clickskeks'); ?></strong></label>
                <br/>
                <input name="ckeks_script_key" id="ckeks_script_key" type="text" value="<?php echo get_option('ckeks_script_key'); ?>" style="" class="regular-text" />&nbsp;
                <input type="submit" name="submit_code" id="submit_code" class="keks-btn" value="Code speichern">
                <input type="submit" name="reset" id="reset" class="keks-btn" value="Zurücksetzen">
                <br/>
                <br/>
            </form>
        </div> <?php
    }
    
    public function ckeks_handle_form() {
        if( ! isset( $_POST['script_add_form'] ) || ! wp_verify_nonce( $_POST['script_add_form'], 'script_update' )
        ){ ?>
            <div class="error" style="margin-left: 0">
                <p><?php __('Es ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.', 'clickskeks'); ?></p>
            </div> <?php
            exit;
        } else {
            
            $successText = '';
            
            if( isset( $_POST['submit_type']) && $_POST['submit_type'] == 'reset' )
            {
                $scriptKey = '';
                $successText = 'Erfolgreich zurückgesetzt.';
            } elseif( isset( $_POST['submit_type'] ) && $_POST['submit_type'] == 'submit' )
            {
                $scriptKey = sanitize_text_field( $_POST['ckeks_script_key'] );
                $successText = 'Der Code wurde erfolgreich gespeichert.';
            }
            
            update_option( 'ckeks_script_key', $scriptKey );
            
            ?>
            <div class="updated" style="margin-left: 0">
                <p> <?php echo $successText; ?> </p>
            </div> <?php
            
        }
    }
}
new CKeksScriptInserter();