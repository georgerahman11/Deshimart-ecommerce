<?php
/**
 * Mantrabrain Starter Sites setup
 *
 * @package Mantrabrain_Starter_Sites
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Main Mantrabrain Starter Sites Class.
 *
 * @class Mantrabrain_Starter_Sites
 */
final class Mantrabrain_Starter_Sites
{

    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = '1.0.13';

    /**
     * Theme single instance of this class.
     *
     * @var object
     */
    protected static $_instance = null;

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'mantrabrain-starter-sites'), '1.4');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'mantrabrain-starter-sites'), '1.4');
    }

    /**
     * Initialize the plugin.
     */
    private function __construct()
    {
        $this->define_constants();
        $this->init_hooks();

        do_action('mantrabrain_starter_sites_loaded');
    }

    /**
     * Define Constants.
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir(null, false);

        $this->define('MANTRABRAIN_STARTER_SITES_ABSPATH', dirname(MANTRABRAIN_STARTER_SITES_PLUGIN_FILE) . '/');
        $this->define('MANTRABRAIN_STARTER_SITES_PLUGIN_BASENAME', plugin_basename(MANTRABRAIN_STARTER_SITES_PLUGIN_FILE));
        $this->define('MANTRABRAIN_STARTER_SITES_VERSION', $this->version);
        $this->define('MANTRABRAIN_STARTER_SITES_DEMO_DIR', $upload_dir['basedir'] . '/starter-site-package/');
    }

    /**
     * Define constant if not already set.
     *
     * @param string $name Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks()
    {
        // Load plugin text domain.
        if (is_admin()) {

            add_action('init', array($this, 'load_plugin_textdomain'));

            // Register activation hook.
            register_activation_hook(MANTRABRAIN_STARTER_SITES_PLUGIN_FILE, array($this, 'install'));

            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/theme-mapping.php';
            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/class-mantrabrain-demo-api.php';


            // Check with Official Mantrabrain theme is installed.
            if (in_array(get_option('template'), $this->get_core_supported_themes(), true)) {
                $this->includes();

                add_filter('plugin_action_links_' . MANTRABRAIN_STARTER_SITES_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
                add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 2);
            } else {
                add_action('admin_notices', array($this, 'theme_support_missing_notice'));
            }
        }
    }

    /**
     * Get core supported themes.
     *
     * @return array
     */
    private function get_core_supported_themes()
    {

        $supported_themes = mantrabrain_starter_sites_supported_themes();

        return array_keys($supported_themes);
    }

    /**
     * Include required core files.
     */
    private function includes()
    {

        if (is_admin()) {
            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/class-mantrabrain-demo-importer.php';
            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/admin/class-mantrabrain-admin-notices.php';
            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/functions.php';
            //Dashboard
            include_once MANTRABRAIN_STARTER_SITES_ABSPATH . 'includes/admin/dashboard/class-mantrabrain-admin-dashboard.php';
        }
    }

    /**
     * Install
     */
    public function install()
    {
        $files = array(
            array(
                'base' => MANTRABRAIN_STARTER_SITES_DEMO_DIR,
                'file' => 'index.html',
                'content' => '',
            ),
        );

        // Bypass if filesystem is read-only and/or non-standard upload system is used.
        if (!is_blog_installed() || apply_filters('mantrabrain_starter_sites_install_skip_create_files', false)) {
            return;
        }

        // Install files and folders.
        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                $file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w'); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
                if ($file_handle) {
                    fwrite($file_handle, $file['content']); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
                    fclose($file_handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
                }
            }
        }

        // Redirect to demo importer page.
        set_transient('_mantrabrain_starter_sites_activation_redirect', 1, 30);
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     *
     * Locales found in:
     *      - WP_LANG_DIR/mantrabrain-starter-sites/mantrabrain-starter-sites-LOCALE.mo
     *      - WP_LANG_DIR/plugins/mantrabrain-starter-sites-LOCALE.mo
     */
    public function load_plugin_textdomain()
    {
        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'mantrabrain-starter-sites');

        unload_textdomain('mantrabrain-starter-sites');
        load_textdomain('mantrabrain-starter-sites', WP_LANG_DIR . '/mantrabrain-starter-sites/mantrabrain-starter-sites-' . $locale . '.mo');
        load_plugin_textdomain('mantrabrain-starter-sites', false, plugin_basename(dirname(MANTRABRAIN_STARTER_SITES_PLUGIN_FILE)) . '/languages');
    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url()
    {
        return untrailingslashit(plugins_url('/', MANTRABRAIN_STARTER_SITES_PLUGIN_FILE));
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path()
    {
        return untrailingslashit(plugin_dir_path(MANTRABRAIN_STARTER_SITES_PLUGIN_FILE));
    }

    /**
     * Display action links in the Plugins list table.
     *
     * @param  array $actions Plugin Action links.
     * @return array
     */
    public function plugin_action_links($actions)
    {
        $new_actions = array(
            'importer' => '<a href="' . admin_url('themes.php?page=starter-sites') . '" aria-label="' . esc_attr(__('View Starter Sites', 'mantrabrain-starter-sites')) . '">' . __('Starter Sites', 'mantrabrain-starter-sites') . '</a>',
        );

        return array_merge($new_actions, $actions);
    }

    /**
     * Display row meta in the Plugins list table.
     *
     * @param  array $plugin_meta Plugin Row Meta.
     * @param  string $plugin_file Plugin Row Meta.
     * @return array
     */
    public function plugin_row_meta($plugin_meta, $plugin_file)
    {
        if (MANTRABRAIN_STARTER_SITES_PLUGIN_BASENAME === $plugin_file) {
            $new_plugin_meta = array(
                'docs' => '<a href="' . esc_url(apply_filters('mantrabrain_starter_sites_docs_url', 'https://docs.mantrabrain.com/')) . '" title="' . esc_attr(__('View Demo Importer Documentation', 'mantrabrain-starter-sites')) . '">' . __('Docs', 'mantrabrain-starter-sites') . '</a>',
                'support' => '<a href="' . esc_url(apply_filters('mantrabrain_starter_sites_support_url', 'https://mantrabrain.com/support-forum/')) . '" title="' . esc_attr(__('Visit Free Customer Support Forum', 'mantrabrain-starter-sites')) . '">' . __('Free Support', 'mantrabrain-starter-sites') . '</a>',
            );

            return array_merge($plugin_meta, $new_plugin_meta);
        }

        return (array)$plugin_meta;
    }

    /**
     * Theme support fallback notice.
     */
    public function theme_support_missing_notice()
    {
        $themes_url = array_intersect(array_keys(wp_get_themes()), $this->get_core_supported_themes()) ? admin_url('themes.php?search=mantrabrain') : admin_url('theme-install.php?search=mantrabrain');

        /* translators: %s: official Mantrabrain themes URL */
        echo '<div class="error notice is-dismissible"><p><strong>' . esc_html__('Mantrabrain Starter Sites', 'mantrabrain-starter-sites') . '</strong> &#8211; ' . sprintf(esc_html__('This plugin requires %s to be activated to work.', 'mantrabrain-starter-sites'), '<a href="' . esc_url($themes_url) . '">' . esc_html__('Official Mantrabrain Theme', 'mantrabrain-starter-sites') . '</a>') . '</p></div>';
    }
}
