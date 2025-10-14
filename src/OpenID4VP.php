<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Universal_OpenID4VP {
    public $version = '1.0.0';

    public static $_instance = null;

    protected $default_settings = [
        'openidEndpoint'             => '',
        'authenticationHeaderName'   => 'x-api-key',
        'authenticationToken'        => '',
        'loginUrl'                   => '',
        'redirectUserOrigin'         => 0
    ];

    public function __construct() {
        add_action('init', [__CLASS__, 'includes']);
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function includes() {
        require_once(UNIVERSAL_OPENID4VP_PLUGIN_DIR . 'build/adminSettings/openid4vp-admin-options.php');
        require_once(UNIVERSAL_OPENID4VP_PLUGIN_DIR . 'build/adminSettings/openid4vp-admin-settings.php');
    }

    public function setup() {
        $admin_options = get_option('universal_openid4vp_options');

        if (!isset($admin_options['openidEndpoint'])) {
            update_option('universal_openid4vp_options', $this->default_settings);
        }

        $this->install();
    }

    public function logout() {
        wp_redirect(home_url());
        exit();
    }

    public function wp_enqueue() {
        // Registers the script if $src provided (does NOT overwrite), and enqueues it.
        wp_enqueue_script('jquery-ui-accordion');
    }

    public function plugin_init() {
    }

    public function install() {
    }

    public function upgrade() {
    }
}

