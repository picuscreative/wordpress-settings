<?php
/**
* Plugin Name: Settings
* Plugin URI: http://
* Description: This plugin adds all mandatory functions.php settings.
* Version: 1.0.0
* Author: PICUS
* Author URI: https://picuscreative.com
*/

/*
  Remove default scripts from header and add them to footer (functions.php)
*/
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');

function remove_head_scripts() {
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);

    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5);
}
add_action( 'wp_enqueue_scripts', 'remove_head_scripts' );

/*
  Create "Client" role and function to check if user is an "admin" (functions.php)
*/
add_role('client', __( 'Client' ), array(
    'read'                  => true,
    'edit_pages'            => true,
    'edit_others_pages'     => true,
    'edit_private_pages'    => true,
    'edit_published_pages'  => true,
));

function is_admin_user() {
    $user = wp_get_current_user();
    if ( is_array( $user->roles ) ) {
        if ( in_array( 'administrator', $user->roles ) ) {
            return true;
        }
    }
}

/*
  Remove admin menus
*/
function remove_admin_menus() {
    if(!is_admin_user()) {
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'index.php' );
        remove_menu_page( 'edit-comments.php' );
        remove_menu_page( 'edit.php' );
        remove_menu_page( 'index.php' );
        remove_submenu_page( 'upload.php', 'wp-smush-bulk' );
        remove_meta_box('pageparentdiv', 'page', 'normal');
    }
}
add_action( 'admin_menu', 'remove_admin_menus' );

/*
  Remove admin bar menus
*/
function remove_admin_bar_menus() {
    if(!is_admin_user()) {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('wpseo-menu');
    }
}
add_action( 'wp_before_admin_bar_render', 'remove_admin_bar_menus' );

/*
  Remove versions from admin menu
*/
function change_footer_admin () {
    if(!is_admin_user()) {
        return ' ';
    }
}
add_filter('admin_footer_text', 'change_footer_admin', 9999);

function change_footer_version() {
    if(!is_admin_user()) {
        return ' ';
    }
}
add_filter( 'update_footer', 'change_footer_version', 9999);

/*
  Remove notices from admin menu
*/
function remove_notices()
{
    if(!is_admin_user()) { ?>
        <style type="text/css">
            .notice,
            .update-nag,
            h2.hndle.ui-sortable-handle {
                display: none !important;
            }
        </style>
    <?php }
}
add_action('admin_head', 'remove_notices');

/*
  Add logo in login page
*/
function login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
          display: none !important;
        }
    </style>
    
    <script type="application/javascript">
        document.addEventListener("DOMContentLoaded", function(event) { 
            var loginElement = document.getElementsByTagName("h1")[0];
            var img = document.createElement("img");
            img.setAttribute("src", "<?php echo home_url() ?>/wp-content/themes/picus/dist/images/logo.svg");
            loginElement.appendChild(img);
        });
    </script>
<?php }
add_action( 'login_enqueue_scripts', 'login_logo' );

/*
  Defer script loading so that there is non-blocking javascript
*/
if (!is_admin()) {
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {
        return str_replace( ' src', ' defer src', $tag );
    }, 10, 2 );
}

/*
  Filter to Remove JS and CSS versioning loading to enable proxy server caching
*/

function remove_version_para_from_style_and_script( $style_or_js_url ) {
    if ( strpos($style_or_js_url, 'ver=' ) ) {
        $style_or_js_url = remove_query_arg( 'ver', $style_or_js_url );
    }
    return $style_or_js_url;
}

add_filter( 'style_loader_src', 'remove_version_para_from_style_and_script', 10 );
