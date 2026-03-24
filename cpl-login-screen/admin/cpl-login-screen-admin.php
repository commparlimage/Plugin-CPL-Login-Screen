<?php

add_action( 'admin_menu', function() {
    add_submenu_page(
        'options-general.php',
        'Gestion page de connexion',
        'Gérer la page de connexion CPL',
        'manage_options',
        'cpl-login-screen-settings',
        'cpl_login_screen_admin_page'
    );
});

add_action( 'admin_init', 'register_cpl_login_screen_settings');

add_action( 'admin_enqueue_scripts', function($hook) {
    wp_enqueue_media();
    wp_enqueue_script(
        'cpl-login-admin',
        plugin_dir_url( __FILE__ ) . 'cpl-login-screen-admin.js',
        ['jquery'],
        '1.0.0',
        true
    );
    wp_enqueue_style(
        'cpl-login-admin',
        plugin_dir_url( __FILE__ ) . 'cpl-login-screen-admin.css',
        [],
        '1.0.0'
    );
});

function cpl_login_screen_admin_page () {
    ?>

    <div class="wrap">
        <h1>Gestion de la page de connexion CPL</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('cpl-login-screen-group');
                do_settings_sections('cpl-login-screen-settings');
            ?>
            <div class="cpl-login-option-group">
                <h2>Logo du site web</h2>
                <div class="cpl-login-option-inner">
                    <button id="cpl-login-select-logo" class="button">Choisir un logo</button>
                    <input type="hidden" id="cpl-login-logo-url" name="cpl_login_logo" value="<?= esc_attr(get_option('cpl_login_logo')); ?>">
                    <div id="cpl-login-logo-preview">
                        <?php if ($logo = get_option('cpl_login_logo')) : ?>
                            <img src="<?= esc_url($logo); ?>" style="max-width: 200px;">
                        <?php else: ?>
                            <p>Aucune image sélectionnée</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>

    <?php

    if ( isset($_POST['cpl_login_logo']) && check_admin_referer( 'cpl_login_logo', 'cpl_login_logo_nonce' ) ) {
        update_option( 'cpl_login_logo', esc_url_raw($_POST['cpl_login_logo']) );
        echo '<div class="updated"><p>Changements enregistrés.</p></div>';
    }

}

function register_cpl_login_screen_settings () {
    register_setting('cpl-login-screen-group', 'cpl_login_logo');
}