<?php
namespace ShortCalc;

class Plugin implements PluginInterface {
	private $plugin_slug = null;

	public function __construct($slug) {
		$this->plugin_slug = $slug;
		add_action( 'init', array( $this, 'loadPluginTextdomain' ) );
	}

	public function loadPluginTextdomain () {
		if(!session_id()) {
			session_start();
		}

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}
}