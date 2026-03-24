<?php
/**
 * Plugin Name: Page de connexion CPL
 * Description: Personnalisation de l’écran de connexion WordPress.
 * Version: 1.1.1
 * Author: CPL
 * Text Domain: cpl-login-screen
 * GitHub Plugin URI: https://github.com/commparlimage/Plugin-CPL-Login-Screen
 * GitHub Branch: main
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin/cpl-login-screen-admin.php';
}

add_action( 'login_enqueue_scripts', function () {
    wp_enqueue_style(
        'cpl-login-screen',
        plugin_dir_url( __FILE__ ) . 'assets/css/login.css',
        [],
        '1.0.0'
    );
});

// Enlever les changements ajouter par le template
add_action( 'login_enqueue_scripts', function() {
    // Only try to remove if the functions exist
    if ( function_exists( 'my_login_logo' ) ) {
        remove_action( 'login_enqueue_scripts', 'my_login_logo' );
    }
    if ( function_exists( 'my_login_stylesheet' ) ) {
        remove_action( 'login_enqueue_scripts', 'my_login_stylesheet' );
    }
    if ( function_exists( 'my_login_logo_url' ) ) {
        remove_filter( 'login_headerurl', 'my_login_logo_url' );
    }
    if ( function_exists( 'my_login_logo_url_title' ) ) {
        remove_filter( 'login_headertitle', 'my_login_logo_url_title' );
    }
    if ( function_exists( 'smallenvelop_login_message' ) ) {
        remove_filter( 'login_message', 'smallenvelop_login_message' );
    }
}, 1 );

add_action( 'login_enqueue_scripts', function() {
    $client_logo = get_option('cpl_login_logo') ?? get_field('logo', 'options')['url'];
    echo "<style type=\"text/css\">
        #login h1 a {
            background-image: url({$client_logo});
            background-position: center;
            background-size: contain;
            width: 250px;
            height: 250px;
        }
    </style>";
} );

add_action( 'login_header', function () {
    $client_logo = get_option('cpl_login_logo') ?? get_field('logo', 'options')['url'];
    ?>
    <div class="cpl-login-logo">
        <img src="<?= $client_logo; ?>" alt="Votre site web WordPress">
    </div>
    <?php
});

add_action( 'login_footer', function () {
    ?>

    <div class="cpl-login-contact">
            <div class="cpl-login-inner">
                <h2>Vous avez besoin d'assistance avec votre site?</h2>
                <p>N'hésitez-pas à nous contacter! Nous sommes disponibles de <strong>8h00 à 16h30</strong>, du lundi au vendredi.</p>
                <div class="cpl-contact-link-container"> 
                    <a href="tel:4509190674" class="cpl-contact-link cpl-login-phone">450 919-0674</a>
                    <a href="mailto:info@commparlimage.ca" class="cpl-contact-link cpl-login-email">info@commparlimage.ca</a>
                </div>
                <p class="cpl-blog-message">Visitez <a href="https://www.commparlimage.ca/blogue/">notre blogue</a> pour des conseils utiles et des articles informatifs.</p>
            </div>
    </div>

    <div class="cpl-login-footer">
            <div class="cpl-login-inner">
                <p>Un site web developpé par <a href="https://www.commparlimage.ca/" target="_blank"><span class="cpl-logo" style="--cpl-logo: url(<?= plugin_dir_url( __FILE__ ); ?>/assets/images/logo-cpl.png);" aria-label="Communication par l'image"></span></a></p>
            </div>
    </div>

    <?php
} );

