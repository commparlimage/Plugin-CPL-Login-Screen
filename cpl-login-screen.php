<?php
/**
 * Plugin Name: Page de connexion CPL
 * Description: Personnalisation de l’écran de connexion WordPress.
 * Version: 1.0.0
 * Author: CPL
 * Text Domain: cpl-login-screen
 * GitHub Plugin URI: https://github.com/commparlimage/cpl-login-screen
 * Primary Branch: main
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ============================================================================================ */
/* SÉCURITÉ LOGIN                                                                               */
/* ============================================================================================ */

/**
 * Bloque l'accès public aux endpoints REST API des utilisateurs.
 */
add_filter('rest_endpoints', function ($endpoints) {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users']);
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

/**
 * Bloque l'exposition des auteurs dans les réponses REST des posts.
 */
add_filter('rest_prepare_post', function ($response, $post, $request) {
    if (!is_user_logged_in()) {
        $data = $response->get_data();
        unset($data['author']);
        if (isset($data['_links']['author'])) {
            unset($data['_links']['author']);
        }
        $response->set_data($data);
    }
    return $response;
}, 10, 3);

/**
 * Bloque l'énumération publique des auteurs WordPress.
 */
add_action('init', function () {
    if (isset($_GET['author']) && is_numeric($_GET['author'])) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
});

add_action('template_redirect', function () {
    if (!is_user_logged_in() && is_author()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

/**
 * Désactive complètement XML-RPC.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Bloque les requêtes directes vers xmlrpc.php.
 */
add_action('init', function () {
    if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'xmlrpc.php') {
        status_header(403);
        exit;
    }
});

/**
 * Remplace les erreurs de connexion par un message générique.
 */
add_filter('login_errors', function () {
    return 'Les informations de connexion sont invalides.';
});

/* ============================================================================================ */
/* SÉCURITÉ LOGIN (SUITE)                                                                       */
/* ============================================================================================ */

/**
 * Limite les tentatives de connexion échouées par adresse IP.
 */
function cpl_get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    return sanitize_text_field($ip);
}

function cpl_login_rate_limit_key() {
    return 'cpl_login_attempts_' . md5(cpl_get_client_ip());
}

add_filter('authenticate', function ($user, $username, $password) {

    if (is_wp_error($user)) {
        return $user;
    }

    $key = cpl_login_rate_limit_key();
    $attempts = get_transient($key);

    if ($attempts !== false && (int) $attempts >= 5) {
        return new WP_Error(
            'cpl_too_many_login_attempts',
            'Trop de tentatives de connexion. Veuillez réessayer plus tard.'
        );
    }

    return $user;

}, 1, 3);

add_action('wp_login_failed', function ($username) {

    $key = cpl_login_rate_limit_key();
    $attempts = get_transient($key);

    if ($attempts === false) {
        $attempts = 0;
    }

    $attempts++;

    set_transient($key, $attempts, 30 * MINUTE_IN_SECONDS);

});

add_action('wp_login', function ($user_login, $user) {
    delete_transient(cpl_login_rate_limit_key());
}, 10, 2);

/* ============================================================================================ */
/* FIN SÉCURITÉ LOGIN                                                                           */
/* ============================================================================================ */

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

