<?php
/**
 * Mantrabrain Starter Sites.
 *
 * @package Mantrabrain_Starter_Sites/Classes
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Mantrabrain_Demo_Importer Class.
 */
class Mantrabrain_Demo_Importer
{

    /**
     * Demo packages.
     *
     * @var array
     */
    public $demo_packages;

    public $current_theme;

    public $theme_demo_data_uris;

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'setup'), 5);
        add_action('init', array($this, 'includes'));


        // Add Demo Importer menu.
        if (apply_filters('mantrabrain_show_demo_importer_page', true)) {
            add_action('admin_menu', array($this, 'admin_menu'), 9);
            add_action('admin_head', array($this, 'add_menu_classes'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        }
        /*
                // Help Tabs.
                if ( apply_filters( 'mantrabrain_starter_sites_enable_admin_help_tab', true ) ) {
                    add_action( 'current_screen', array( $this, 'add_help_tabs' ), 50 );
                }*/

        // Footer rating text.
        add_filter('admin_footer_text', array($this, 'admin_footer_text'), 1);

        // Disable WooCommerce setup wizard.
        add_filter('woocommerce_enable_setup_wizard', '__return_false', 1);

        // AJAX Events to query demo, import demo and update rating footer.
        add_action('wp_ajax_query-demos', array($this, 'ajax_query_demos'));
        add_action('wp_ajax_import-demo', array($this, 'ajax_import_demo'));
        add_action('wp_ajax_footer-text-rated', array($this, 'ajax_footer_text_rated'));

        // Update custom nav menu items, elementor and siteorigin panel data.
        add_action('mantrabrain_ajax_demo_imported', array($this, 'update_nav_menu_items'));
        add_action('mantrabrain_ajax_demo_imported', array($this, 'update_elementor_data'), 10, 2);
        add_action('mantrabrain_ajax_demo_imported', array($this, 'update_siteorigin_data'), 10, 2);

        // Update widget and customizer demo import settings data.
        add_filter('mantrabrain_widget_demo_import_settings', array($this, 'update_widget_data'), 10, 4);
        add_filter('mantrabrain_customizer_demo_import_settings', array($this, 'update_customizer_data'), 10, 2);
    }

    /**
     * Demo importer setup.
     */
    public function setup()
    {


        $this->demo_packages = Mantrabrain_Demo_API::get_theme_demo_configuration();
        $this->current_theme = get_option('template');
        $this->theme_demo_data_uris = Mantrabrain_Demo_API::get_theme_demo_data_uri();
    }

    /**
     * Include required core files.
     */
    public function includes()
    {
        include_once dirname(__FILE__) . '/importers/class-mantrabrain-widget-importer.php';
        include_once dirname(__FILE__) . '/importers/class-mantrabrain-customizer-importer.php';
    }

    /**
     * Add menu item.
     */
    public function admin_menu()
    {
        add_theme_page(__('Starter Sites', 'mantrabrain-starter-sites'), __('Starter Sites', 'mantrabrain-starter-sites'), 'switch_themes', 'starter-sites', array($this, 'starter_sites'));
    }

    /**
     * Adds the class to the menu.
     */
    public function add_menu_classes()
    {
        global $submenu;

        if (isset($submenu['themes.php'])) {
            $submenu_class = 'demo-importer hide-if-no-js';

            // Add menu classes if user has access.
            if (apply_filters('mantrabrain_starter_sites_include_class_in_menu', true)) {
                foreach ($submenu['themes.php'] as $order => $menu_item) {
                    if (0 === strpos($menu_item[0], _x('Starter Sites', 'Admin menu name', 'mantrabrain-starter-sites'))) {
                        $submenu['themes.php'][$order][4] = empty($menu_item[4]) ? $submenu_class : $menu_item[4] . ' ' . $submenu_class;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Enqueue scripts.
     */
    public function enqueue_scripts()
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $suffix = '';//defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $assets_path = mb_starter_sites()->plugin_url() . '/assets/';

        // Register admin styles.
        wp_register_style('sweetalert2', $assets_path . 'libs/sweetalert2/css/sweetalert2.css', array(), MANTRABRAIN_STARTER_SITES_VERSION);
        wp_register_style('mantrabrain-starter-sites', $assets_path . 'css/mantrabrain-starter-sites.css', array('sweetalert2'), MANTRABRAIN_STARTER_SITES_VERSION);

        // Add RTL support for admin styles.
        wp_style_add_data('mantrabrain-starter-sites', 'rtl', 'replace');

        // Register admin scripts.
        wp_register_script('sweetalert2', $assets_path . 'libs/sweetalert2/js/sweetalert2.js', array('jquery'), '1.3', true);
        wp_register_script('jquery-tiptip', $assets_path . 'js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array('jquery'), '1.3', true);
        wp_register_script('mantrabrain-demo-updates', $assets_path . 'js/admin/demo-updates' . $suffix . '.js', array('jquery', 'updates', 'sweetalert2'), MANTRABRAIN_STARTER_SITES_VERSION, true);
        wp_register_script('mantrabrain-starter-sites', $assets_path . 'js/admin/demo-importer' . $suffix . '.js', array('jquery', 'jquery-tiptip', 'wp-backbone', 'wp-a11y', 'mantrabrain-demo-updates', 'sweetalert2'), MANTRABRAIN_STARTER_SITES_VERSION, true);

        // Demo Importer appearance page.
        if ('appearance_page_starter-sites' === $screen_id) {
            wp_enqueue_style('mantrabrain-starter-sites');
            wp_enqueue_script('mantrabrain-starter-sites');
            wp_localize_script('mantrabrain-demo-updates', '_demoUpdatesSettings', array(
                'l10n' => array(
                    'importing' => __('Importing...', 'mantrabrain-starter-sites'),
                    'demoImportingLabel' => _x('Importing %s...', 'demo', 'mantrabrain-starter-sites'), // no ellipsis
                    'importingMsg' => __('Importing... please wait.', 'mantrabrain-starter-sites'),
                    'importedMsg' => __('Import completed successfully.', 'mantrabrain-starter-sites'),
                    'importFailedShort' => __('Import Failed!', 'mantrabrain-starter-sites'),
                    'importFailed' => __('Import failed: %s', 'mantrabrain-starter-sites'),
                    'demoImportedLabel' => _x('%s imported!', 'demo', 'mantrabrain-starter-sites'),
                    'demoImportFailedLabel' => _x('%s import failed', 'demo', 'mantrabrain-starter-sites'),
                    'livePreview' => __('Live Preview', 'mantrabrain-starter-sites'),
                    'livePreviewLabel' => _x('Live Preview %s', 'demo', 'mantrabrain-starter-sites'),
                    'imported' => __('Imported!', 'mantrabrain-starter-sites'),
                    'statusTextLink' => '<a href="https://mantrabrain.com/docs/demo-import-failed-issue/" target="_blank">' . __('Try this solution!', 'mantrabrain-starter-sites') . '</a>',
                ),
            ));

            $theme_slug = str_replace('-pro', '', get_option('stylesheet'));

            $support_link = 'https://wordpress.org/support/theme/' . esc_attr($theme_slug) . '/reviews/?filter=5';

            $rating_message = 'If you have some spare time, can you please rate our theme from <a href="' . $support_link . '" target="_blank">here</a>';

            $rating_message =
                wp_localize_script('mantrabrain-starter-sites', '_demoImporterSettings', array(
                    'demos' => $this->ajax_query_demos(true),
                    'settings' => array(
                        'isNew' => false,
                        'ajaxUrl' => admin_url('admin-ajax.php'),
                        'adminUrl' => parse_url(self_admin_url(), PHP_URL_PATH),
                        'suggestURI' => apply_filters('mantrabrain_starter_sites_suggest_new', 'https://mantrabrain.com/contact/'),
                        'confirmReset' => __('It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the reset wizard now?', 'mantrabrain-starter-sites'),
                        'confirmImportTitle' => __('Demo Import Confirmation?', 'mantrabrain-starter-sites'),
                        'confirmImport' => __("Are you sure to import demo content?", 'mantrabrain-starter-sites'),
                        'ratingMessage' => $rating_message,
                        'supportLink' => $support_link,
                        'demoImportSuccessTitle' => __('Demo Import Success', 'mantrabrain-starter-sites'),
                        'install_recommanded_plugin' => 'install_recommanded',
                        'install_recommanded_plugin_nonce' => wp_create_nonce('mantrabrain_starter_sites_install_recommanded_plugin_nonce'),
                    ),
                    'l10n' => array(
                        'importing' => __('Importing...', 'mantrabrain-starter-sites'),
                        'search' => __('Search Demos', 'mantrabrain-starter-sites'),
                        'searchPlaceholder' => __('Search demos...', 'mantrabrain-starter-sites'), // placeholder (no ellipsis)
                        /* translators: %s: support forums URL */
                        'error' => sprintf(__('An unexpected error occurred. Something may be wrong with Mantrabrain demo server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'mantrabrain-starter-sites'), 'https://wordpress.org/support/plugin/mantrabrain-starter-sites'),
                        'tryAgain' => __('Try Again', 'mantrabrain-starter-sites'),
                        'suggestNew' => __('Please suggest us!', 'mantrabrain-starter-sites'),
                        'demosFound' => __('Number of Demos found: %d', 'mantrabrain-starter-sites'),
                        'noDemosFound' => __('No demos found. Try a different search.', 'mantrabrain-starter-sites'),
                        'collapseSidebar' => __('Collapse Sidebar', 'mantrabrain-starter-sites'),
                        'expandSidebar' => __('Expand Sidebar', 'mantrabrain-starter-sites'),
                        /* translators: accessibility text */
                        'selectFeatureFilter' => __('Select one or more Demo features to filter by', 'mantrabrain-starter-sites'),
                    ),
                ));
        }
    }

    /**
     * Change the admin footer text.
     *
     * @param string $footer_text
     * @return string
     */
    public function admin_footer_text($footer_text)
    {
        if (!current_user_can('manage_options')) {
            return $footer_text;
        }

        $current_screen = get_current_screen();

        // Check to make sure we're on a Mantrabrain Starter Sites admin page.
        if (isset($current_screen->id) && apply_filters('mantrabrain_starter_sites_display_admin_footer_text', in_array($current_screen->id, array('appearance_page_starter-sites')))) {
            // Change the footer text.
            if (!get_option('mantrabrain_starter_sites_admin_footer_text_rated')) {
                $footer_text = sprintf(
                /* translators: 1: Mantrabrain Starter Sites 2: five stars */
                    __('If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'mantrabrain-starter-sites'),
                    sprintf('<strong>%s</strong>', esc_html__('Mantrabrain Starter Sites', 'mantrabrain-starter-sites')),
                    '<a href="https://wordpress.org/support/plugin/mantrabrain-starter-sites/reviews?rate=5#new-post" target="_blank" class="mantrabrain-starter-sites-rating-link" data-rated="' . esc_attr__('Thanks :)', 'mantrabrain-starter-sites') . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
                );
            } else {
                $footer_text = __('Thank you for importing with Mantrabrain Starter Sites.', 'mantrabrain-starter-sites');
            }
        }

        return $footer_text;
    }

    /**
     * Add Contextual help tabs.
     */
    public function add_help_tabs()
    {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->id, array('appearance_page_starter-sites'))) {
            return;
        }

        $screen->add_help_tab(array(
            'id' => 'mantrabrain_starter_sites_support_tab',
            'title' => __('Help &amp; Support', 'mantrabrain-starter-sites'),
            'content' =>
                '<h2>' . __('Help &amp; Support', 'mantrabrain-starter-sites') . '</h2>' .
                '<p>' . sprintf(
                    __('Should you need help understanding, using, or extending Mantrabrain Starter Sites, <a href="%s">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.', 'mantrabrain-starter-sites'),
                    'https://docs.mantrabrain.com/'
                ) . '</p>' .
                '<p>' . sprintf(
                    __('For further assistance with Mantrabrain Starter Sites core you can use the <a href="%1$s">community forum</a>. If you need help with premium themes sold by Mantrabrain, please <a href="%2$s">use our free support forum</a>.', 'mantrabrain-starter-sites'),
                    'https://wordpress.org/support/plugin/mantrabrain-starter-sites',
                    'https://mantrabrain.com/support-forum/'
                ) . '</p>' .
                '<p><a href="' . 'https://wordpress.org/support/plugin/mantrabrain-starter-sites' . '" class="button button-primary">' . __('Community forum', 'mantrabrain-starter-sites') . '</a> <a href="' . 'https://mantrabrain.com/support-forum/' . '" class="button">' . __('Mantrabrain Support', 'mantrabrain-starter-sites') . '</a></p>',
        ));

        $screen->add_help_tab(array(
            'id' => 'mantrabrain_starter_sites_bugs_tab',
            'title' => __('Found a bug?', 'mantrabrain-starter-sites'),
            'content' =>
                '<h2>' . __('Found a bug?', 'mantrabrain-starter-sites') . '</h2>' .
                '<p>' . sprintf(__('If you find a bug within Mantrabrain Starter Sites you can create a ticket via <a href="%1$s">Github issues</a>. Ensure you read the <a href="%2$s">contribution guide</a> prior to submitting your report. To help us solve your issue, please be as descriptive as possible.', 'mantrabrain-starter-sites'), 'https://github.com/mantrabrain/mantrabrain-starter-sites/issues?state=open', 'https://github.com/mantrabrain/mantrabrain-starter-sites/blob/master/.github/CONTRIBUTING.md') . '</p>' .
                '<p><a href="' . 'https://github.com/mantrabrain/mantrabrain-starter-sites/issues?state=open' . '" class="button button-primary">' . __('Report a bug', 'mantrabrain-starter-sites') . '</a></p>',

        ));


        $screen->set_help_sidebar(
            '<p><strong>' . __('For more information:', 'mantrabrain-starter-sites') . '</strong></p>' .
            '<p><a href="' . 'https://mantrabrain.com/demo-importer/' . '" target="_blank">' . __('About Starter Sites', 'mantrabrain-starter-sites') . '</a></p>' .
            '<p><a href="' . 'https://wordpress.org/plugins/mantrabrain-starter-sites/' . '" target="_blank">' . __('WordPress.org project', 'mantrabrain-starter-sites') . '</a></p>' .
            '<p><a href="' . 'https://github.com/mantrabrain/mantrabrain-starter-sites' . '" target="_blank">' . __('Github project', 'mantrabrain-starter-sites') . '</a></p>' .
            '<p><a href="' . 'https://mantrabrain.com/wordpress-themes/' . '" target="_blank">' . __('Official themes', 'mantrabrain-starter-sites') . '</a></p>' .
            '<p><a href="' . 'https://mantrabrain.com/plugins/' . '" target="_blank">' . __('Official plugins', 'mantrabrain-starter-sites') . '</a></p>'
        );
    }


    /**
     * Demo Importer page output.
     */
    public function starter_sites()
    {
        include_once dirname(__FILE__) . '/admin/views/html-admin-page-importer.php';
    }

    /**
     * Ajax handler for getting demos from github.
     */
    public function ajax_query_demos($return = true)
    {
        $prepared_demos = array();

        $current_template = get_option('template');

        $is_pro_theme_demo = strpos($current_template, '-pro') !== false;

        $is_pro_theme_demo = apply_filters('mantrabrain_starter_sites_pro_demo_import', $is_pro_theme_demo);

        $demo_activated_id = get_option('mantrabrain_starter_sites_activated_id');

        $available_packages = $this->demo_packages;

        /**
         * Filters demo data before it is prepared for JavaScript.
         *
         * @param array $prepared_demos An associative array of demo data. Default empty array.
         * @param null|array $available_packages An array of demo package config to prepare, if any.
         * @param string $demo_activated_id The current demo activated id.
         */
        $prepared_demos = (array)apply_filters('mantrabrain_starter_sites_pre_prepare_demos_for_js', array(), $available_packages, $demo_activated_id);

        if (!empty($prepared_demos)) {

            return $prepared_demos;
        }

        if (!$return) {
            $request = wp_parse_args(wp_unslash($_REQUEST['request']), array(
                'browse' => 'all',
            ));
        } else {
            $request = array(
                'browse' => 'all',
            );
        }

        if (isset($available_packages['demos'])) {

            foreach ($available_packages['demos'] as $demo_slug => $package_data) {

                $current_demo_uris = isset($this->theme_demo_data_uris[$demo_slug]) ? $this->theme_demo_data_uris[$demo_slug] : array();

                $screenshot_url = isset($current_demo_uris['import_preview_image_url']) ? $current_demo_uris['import_preview_image_url'] : '';

                if (isset($request['browse'], $package_data['category']) && !in_array($request['browse'], $package_data['category'], true)) {
                    continue;
                }

                if (isset($request['builder'], $package_data['pagebuilder']) && !in_array($request['builder'], $package_data['pagebuilder'], true)) {
                    continue;
                }

                // Prepare all demos.
                $prepared_demos[$demo_slug] = array(
                    'slug' => $demo_slug,
                    'name' => $package_data['title'],
                    'theme' => $is_pro_theme_demo ? sprintf(esc_html__('%s Pro', 'mantrabrain-starter-sites'), $available_packages['name']) : $available_packages['name'],
                    'isPro' => $is_pro_theme_demo ? false : isset($package_data['isPro']),
                    'active' => $demo_slug === $demo_activated_id,
                    'author' => isset($package_data['author']) ? $package_data['author'] : __('Mantrabrain', 'mantrabrain-starter-sites'),
                    'version' => isset($package_data['version']) ? $package_data['version'] : $available_packages['version'],
                    'description' => isset($package_data['description']) ? $package_data['description'] : '',
                    'homepage' => $available_packages['homepage'],
                    'preview_url' => set_url_scheme($package_data['preview']),
                    'screenshot_url' => $screenshot_url,
                    'plugins' => array(),
                    'requiredTheme' => isset($package_data['template']) && !in_array($current_template, $package_data['template'], true),
                    'requiredPlugins' => false,
                    'required_plugins' => isset($package_data['plugins_list']) ? $package_data['plugins_list'] : array()
                );
            }
        }

        /**
         * Filters the demos prepared for JavaScript.
         *
         * Could be useful for changing the order, which is by name by default.
         *
         * @param array $prepared_demos Array of demos.
         */
        $prepared_demos = apply_filters('mantrabrain_starter_sites_prepare_demos_for_js', $prepared_demos);

        $prepared_demos = array_values($prepared_demos);

        if ($return) {
            return $prepared_demos;
        }

        wp_send_json_success(array(
            'info' => array(
                'page' => 1,
                'pages' => 1,
                'results' => count($prepared_demos),
            ),
            'demos' => array_filter($prepared_demos),
        ));
    }

    /**
     * Ajax handler for importing a demo.
     *
     * @see Mantrabrain_Demo_Upgrader
     *
     * @global WP_Filesystem_Base $wp_filesystem Subclass
     */
    public function ajax_import_demo()
    {

        check_ajax_referer('updates');

        if (empty($_POST['slug'])) {
            wp_send_json_error(array(
                'slug' => '',
                'errorCode' => 'no_demo_specified',
                'errorMessage' => __('No demo specified.', 'mantrabrain-starter-sites'),
            ));
        }

        $slug = sanitize_key(wp_unslash($_POST['slug']));
        $status = array(
            'import' => 'demo',
            'slug' => $slug,
        );

        if (!defined('WP_LOAD_IMPORTERS')) {
            define('WP_LOAD_IMPORTERS', true);
        }


        if (!current_user_can('import')) {
            $status['errorMessage'] = __('Sorry, you are not allowed to import content.', 'mantrabrain-starter-sites');
            wp_send_json_error($status);
        }

        $packages = isset($this->demo_packages['demos']) ? $this->demo_packages['demos'] : array();

        $demo_data = isset($packages[$slug]) ? $packages[$slug] : '';

        $status['demoName'] = $demo_data['title'];

        $status['previewUrl'] = get_home_url('/');

        do_action('mantrabrain_ajax_before_demo_import');


        $current_demo_uris = isset($this->theme_demo_data_uris[$slug]) ? $this->theme_demo_data_uris[$slug] : array();

        $import_file_url = isset($current_demo_uris['import_file_url']) ? $current_demo_uris['import_file_url'] : '';

        $import_widget_file_url = isset($current_demo_uris['import_widget_file_url']) ? $current_demo_uris['import_widget_file_url'] : '';

        $import_customizer_file_url = isset($current_demo_uris['import_customizer_file_url']) ? $current_demo_uris['import_customizer_file_url'] : '';


        if (!empty($demo_data)) {
            $this->import_dummy_xml($slug, $demo_data, $import_file_url);
            $this->import_core_options($demo_data);
            $this->import_elementor_schemes($demo_data);
            $this->import_customizer_data($slug, $demo_data, $import_customizer_file_url);
            $this->import_widget_settings($slug, $demo_data, $import_widget_file_url);

            // Update imported demo ID.
            update_option('mantrabrain_starter_sites_activated_id', $slug);

            do_action('mantrabrain_ajax_demo_imported', $slug, $demo_data);
        }

        wp_send_json_success($status);
    }

    /**
     * Triggered when clicking the rating footer.
     */
    public function ajax_footer_text_rated()
    {
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        update_option('mantrabrain_starter_sites_admin_footer_text_rated', 1);
        wp_die();
    }

    /**
     * Import dummy content from a XML file.
     *
     * @param string $demo_id
     * @param array $demo_data
     * @param string $import_file
     * @return bool
     */
    public function import_dummy_xml($demo_id, $demo_data, $import_file)
    {
        // Load Importer API.
        require_once ABSPATH . 'wp-admin/includes/import.php';

        if (!class_exists('WP_Importer')) {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

            if (file_exists($class_wp_importer)) {
                require $class_wp_importer;
            }
        }

        // Include WXR Importer.
        require dirname(__FILE__) . '/importers/wordpress-importer/class-mantrabrain-wxr-importer.php';

        do_action('mantrabrain_ajax_before_dummy_xml_import', $demo_data, $demo_id);

        // Import XML file demo content.
        $wp_import = new Mantrabrain_WXR_Importer();
        $wp_import->fetch_attachments = true;

        ob_start();
        $wp_import->import($import_file);
        ob_end_clean();

        do_action('mantrabrain_ajax_dummy_xml_imported', $demo_data, $demo_id);

        flush_rewrite_rules();


        return true;
    }

    /**
     * Import site core options from its ID.
     *
     * @param array $demo_data
     * @return bool
     */
    public function import_core_options($demo_data)
    {
        if (!empty($demo_data['core_options'])) {
            foreach ($demo_data['core_options'] as $option_key => $option_value) {
                if (!in_array($option_key, array('blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts'))) {
                    continue;
                }

                // Format the value based on option key.
                switch ($option_key) {
                    case 'show_on_front':
                        if (in_array($option_value, array('posts', 'page'))) {
                            update_option('show_on_front', $option_value);
                        }
                        break;
                    case 'page_on_front':
                    case 'page_for_posts':
                        $page = get_page_by_title($option_value);

                        if (is_object($page) && $page->ID) {
                            update_option($option_key, $page->ID);
                            update_option('show_on_front', 'page');
                        }
                        break;
                    default:
                        update_option($option_key, sanitize_text_field($option_value));
                        break;
                }
            }
        }

        return true;
    }

    /**
     * Import elementor schemes from its ID.
     *
     * @param array $demo_data Demo Data.
     * @return bool
     */
    public function import_elementor_schemes($demo_data)
    {
        if (!empty($demo_data['elementor_schemes'])) {
            foreach ($demo_data['elementor_schemes'] as $scheme_key => $scheme_value) {
                if (!in_array($scheme_key, array('color', 'typography', 'color-picker'))) {
                    continue;
                }

                // Change scheme index to start from 1 instead.
                $scheme_value = array_combine(range(1, count($scheme_value)), $scheme_value);

                if (!empty($scheme_value)) {
                    update_option('elementor_scheme_' . $scheme_key, $scheme_value);
                }
            }
        }

        return true;
    }

    /**
     * Import customizer data from a DAT file.
     *
     * @param string $demo_id
     * @param array $demo_data
     * @param array $status
     * @return bool
     */
    public function import_customizer_data($demo_id, $demo_data, $import_file)
    {
        $results = Mantrabrain_Customizer_Importer::import($import_file, $demo_id, $demo_data);

        if (is_wp_error($results)) {
            return false;
        }


        return true;
    }

    /**
     * Import widgets settings from WIE or JSON file.
     *
     * @param string $demo_id
     * @param array $demo_data
     * @param string $import_file
     * @return bool
     */
    public function import_widget_settings($demo_id, $demo_data, $import_file)
    {

        $results = Mantrabrain_Widget_Importer::import($import_file, $demo_id, $demo_data);

        if (is_wp_error($results)) {
            return false;
        }


        return true;
    }

    /**
     * Update custom nav menu items URL.
     */
    public function update_nav_menu_items()
    {
        $menu_locations = get_nav_menu_locations();

        foreach ($menu_locations as $location => $menu_id) {

            if (is_nav_menu($menu_id)) {
                $menu_items = wp_get_nav_menu_items($menu_id, array('post_status' => 'any'));

                if (!empty($menu_items)) {
                    foreach ($menu_items as $menu_item) {
                        if (isset($menu_item->url) && isset($menu_item->db_id) && 'custom' == $menu_item->type) {
                            $site_parts = parse_url(home_url('/'));
                            $menu_parts = parse_url($menu_item->url);

                            // Update existing custom nav menu item URL.
                            if (isset($menu_parts['path']) && isset($menu_parts['host']) && apply_filters('mantrabrain_starter_sites_nav_menu_item_url_hosts', in_array($menu_parts['host'], array('demo.mantrabrain.com')))) {
                                $menu_item->url = str_replace(array($menu_parts['scheme'], $menu_parts['host'], $menu_parts['path']), array($site_parts['scheme'], $site_parts['host'], trailingslashit($site_parts['path'])), $menu_item->url);
                                update_post_meta($menu_item->db_id, '_menu_item_url', esc_url_raw($menu_item->url));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Updates widgets settings data.
     *
     * @param array $widget
     * @param string $widget_type
     * @param int $instance_id
     * @param array $demo_data
     * @return array
     */
    public function update_widget_data($widget, $widget_type, $instance_id, $demo_data)
    {
        if ('nav_menu' == $widget_type) {
            $nav_menu = wp_get_nav_menu_object($widget['title']);

            if (is_object($nav_menu) && $nav_menu->term_id) {
                $widget['nav_menu'] = $nav_menu->term_id;
            }
        } elseif (!empty($demo_data['widgets_data_update'])) {
            foreach ($demo_data['widgets_data_update'] as $dropdown_type => $dropdown_data) {
                if (!in_array($dropdown_type, array('dropdown_pages', 'dropdown_categories'))) {
                    continue;
                }

                // Format the value based on dropdown type.
                switch ($dropdown_type) {
                    case 'dropdown_pages':
                        foreach ($dropdown_data as $widget_id => $widget_data) {
                            if (!empty($widget_data[$instance_id]) && $widget_id == $widget_type) {
                                foreach ($widget_data[$instance_id] as $widget_key => $widget_value) {
                                    $page = get_page_by_title($widget_value);

                                    if (is_object($page) && $page->ID) {
                                        $widget[$widget_key] = $page->ID;
                                    }
                                }
                            }
                        }
                        break;
                    case 'dropdown_categories':
                        foreach ($dropdown_data as $taxonomy => $taxonomy_data) {
                            if (!taxonomy_exists($taxonomy)) {
                                continue;
                            }

                            foreach ($taxonomy_data as $widget_id => $widget_data) {
                                if (!empty($widget_data[$instance_id]) && $widget_id == $widget_type) {
                                    foreach ($widget_data[$instance_id] as $widget_key => $widget_value) {
                                        $term = get_term_by('name', $widget_value, $taxonomy);

                                        if (is_object($term) && $term->term_id) {
                                            $widget[$widget_key] = $term->term_id;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $widget;
    }

    /**
     * Update customizer settings data.
     *
     * @param array $data
     * @param array $demo_data
     * @return array
     */
    public function update_customizer_data($data, $demo_data)
    {
        if (!empty($demo_data['customizer_data_update'])) {
            foreach ($demo_data['customizer_data_update'] as $data_type => $data_value) {
                if (!in_array($data_type, array('pages', 'categories', 'nav_menu_locations'))) {
                    continue;
                }

                // Format the value based on data type.
                switch ($data_type) {
                    case 'pages':
                        foreach ($data_value as $option_key => $option_value) {
                            if (!empty($data['mods'][$option_key])) {
                                $page = get_page_by_title($option_value);

                                if (is_object($page) && $page->ID) {
                                    $data['mods'][$option_key] = $page->ID;
                                }
                            }
                        }
                        break;
                    case 'categories':
                        foreach ($data_value as $taxonomy => $taxonomy_data) {
                            if (!taxonomy_exists($taxonomy)) {
                                continue;
                            }

                            foreach ($taxonomy_data as $option_key => $option_value) {
                                if (!empty($data['mods'][$option_key])) {
                                    $term = get_term_by('name', $option_value, $taxonomy);

                                    if (is_object($term) && $term->term_id) {
                                        $data['mods'][$option_key] = $term->term_id;
                                    }
                                }
                            }
                        }
                        break;
                    case 'nav_menu_locations':
                        $nav_menus = wp_get_nav_menus();

                        if (!empty($nav_menus)) {
                            foreach ($nav_menus as $nav_menu) {
                                if (is_object($nav_menu)) {
                                    foreach ($data_value as $location => $location_name) {
                                        if ($nav_menu->name == $location_name) {
                                            $data['mods'][$data_type][$location] = $nav_menu->term_id;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Recursive function to address n level deep elementor data update.
     *
     * @param array $elementor_data
     * @param string $data_type
     * @param array $data_value
     * @return array
     */
    public function elementor_recursive_update($elementor_data, $data_type, $data_value)
    {
        $elementor_data = json_decode(stripslashes($elementor_data), true);

        // Recursively update elementor data.
        foreach ($elementor_data as $element_id => $element_data) {
            if (!empty($element_data['elements'])) {
                foreach ($element_data['elements'] as $el_key => $el_data) {
                    if (!empty($el_data['elements'])) {
                        foreach ($el_data['elements'] as $el_child_key => $child_el_data) {
                            if ('widget' === $child_el_data['elType']) {
                                $settings = isset($child_el_data['settings']) ? $child_el_data['settings'] : array();
                                $widgetType = isset($child_el_data['widgetType']) ? $child_el_data['widgetType'] : '';

                                if (isset($settings['display_type']) && 'categories' === $settings['display_type']) {
                                    $categories_selected = isset($settings['categories_selected']) ? $settings['categories_selected'] : '';

                                    if (!empty($data_value['data_update'])) {
                                        foreach ($data_value['data_update'] as $taxonomy => $taxonomy_data) {
                                            if (!taxonomy_exists($taxonomy)) {
                                                continue;
                                            }

                                            foreach ($taxonomy_data as $widget_id => $widget_data) {
                                                if (!empty($widget_data) && $widget_id == $widgetType) {
                                                    if (is_array($categories_selected)) {
                                                        foreach ($categories_selected as $cat_key => $cat_id) {
                                                            if (isset($widget_data[$cat_id])) {
                                                                $term = get_term_by('name', $widget_data[$cat_id], $taxonomy);

                                                                if (is_object($term) && $term->term_id) {
                                                                    $categories_selected[$cat_key] = $term->term_id;
                                                                }
                                                            }
                                                        }
                                                    } elseif (isset($widget_data[$categories_selected])) {
                                                        $term = get_term_by('name', $widget_data[$categories_selected], $taxonomy);

                                                        if (is_object($term) && $term->term_id) {
                                                            $categories_selected = $term->term_id;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // Update the elementor data.
                                    $elementor_data[$element_id]['elements'][$el_key]['elements'][$el_child_key]['settings']['categories_selected'] = $categories_selected;
                                }
                            }
                        }
                    }
                }
            }
        }

        return wp_json_encode($elementor_data);
    }

    /**
     * Update elementor settings data.
     *
     * @param string $demo_id Demo ID.
     * @param array $demo_data Demo Data.
     */
    public function update_elementor_data($demo_id, $demo_data)
    {
        if (!empty($demo_data['elementor_data_update'])) {
            foreach ($demo_data['elementor_data_update'] as $data_type => $data_value) {
                if (!empty($data_value['post_title'])) {
                    $page = get_page_by_title($data_value['post_title']);

                    if (is_object($page) && $page->ID) {
                        $elementor_data = get_post_meta($page->ID, '_elementor_data', true);

                        if (!empty($elementor_data)) {
                            $elementor_data = $this->elementor_recursive_update($elementor_data, $data_type, $data_value);
                        }

                        // Update elementor data.
                        update_post_meta($page->ID, '_elementor_data', $elementor_data);
                    }
                }
            }
        }
    }

    /**
     * Recursive function to address n level deep layoutbuilder data update.
     *
     * @param array $panels_data
     * @param string $data_type
     * @param array $data_value
     * @return array
     */
    public function siteorigin_recursive_update($panels_data, $data_type, $data_value)
    {
        static $instance = 0;

        foreach ($panels_data as $panel_type => $panel_data) {
            // Format the value based on panel type.
            switch ($panel_type) {
                case 'grids':
                    foreach ($panel_data as $instance_id => $grid_instance) {
                        if (!empty($data_value['data_update']['grids_data'])) {
                            foreach ($data_value['data_update']['grids_data'] as $grid_id => $grid_data) {
                                if (!empty($grid_data['style']) && $instance_id === $grid_id) {
                                    $level = isset($grid_data['level']) ? $grid_data['level'] : (int)0;
                                    if ($level == $instance) {
                                        foreach ($grid_data['style'] as $style_key => $style_value) {
                                            if (empty($style_value)) {
                                                continue;
                                            }

                                            // Format the value based on style key.
                                            switch ($style_key) {
                                                case 'background_image_attachment':
                                                    $attachment_id = mantrabrain_get_attachment_id($style_value);

                                                    if (0 !== $attachment_id) {
                                                        $grid_instance['style'][$style_key] = $attachment_id;
                                                    }
                                                    break;
                                                default:
                                                    $grid_instance['style'][$style_key] = $style_value;
                                                    break;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Update panel grids data.
                        $panels_data['grids'][$instance_id] = $grid_instance;
                    }
                    break;

                case 'widgets':
                    foreach ($panel_data as $instance_id => $widget_instance) {
                        if (isset($widget_instance['panels_data']['widgets'])) {
                            $instance = $instance + 1;
                            $child_panels_data = $widget_instance['panels_data'];
                            $panels_data['widgets'][$instance_id]['panels_data'] = $this->siteorigin_recursive_update($child_panels_data, $data_type, $data_value);
                            $instance = $instance - 1;
                            continue;
                        }

                        if (isset($widget_instance['nav_menu']) && isset($widget_instance['title'])) {
                            $nav_menu = wp_get_nav_menu_object($widget_instance['title']);

                            if (is_object($nav_menu) && $nav_menu->term_id) {
                                $widget_instance['nav_menu'] = $nav_menu->term_id;
                            }
                        } elseif (!empty($data_value['data_update']['widgets_data'])) {
                            $instance_class = $widget_instance['panels_info']['class'];

                            foreach ($data_value['data_update']['widgets_data'] as $dropdown_type => $dropdown_data) {
                                if (!in_array($dropdown_type, array('dropdown_pages', 'dropdown_categories'))) {
                                    continue;
                                }

                                // Format the value based on data type.
                                switch ($dropdown_type) {
                                    case 'dropdown_pages':
                                        foreach ($dropdown_data as $widget_id => $widget_data) {
                                            if (!empty($widget_data[$instance_id]) && $widget_id == $instance_class) {
                                                $level = isset($widget_data['level']) ? $widget_data['level'] : (int)0;

                                                if ($level == $instance) {
                                                    foreach ($widget_data[$instance_id] as $widget_key => $widget_value) {
                                                        $page = get_page_by_title($widget_value);

                                                        if (is_object($page) && $page->ID) {
                                                            $widget_instance[$widget_key] = $page->ID;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        break;
                                    case 'dropdown_categories':
                                        foreach ($dropdown_data as $taxonomy => $taxonomy_data) {
                                            if (!taxonomy_exists($taxonomy)) {
                                                continue;
                                            }

                                            foreach ($taxonomy_data as $widget_id => $widget_data) {
                                                if (!empty($widget_data[$instance_id]) && $widget_id == $instance_class) {
                                                    $level = isset($widget_data['level']) ? $widget_data['level'] : (int)0;

                                                    if ($level == $instance) {
                                                        foreach ($widget_data[$instance_id] as $widget_key => $widget_value) {
                                                            $term = get_term_by('name', $widget_value, $taxonomy);

                                                            if (is_object($term) && $term->term_id) {
                                                                $widget_instance[$widget_key] = $term->term_id;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        break;
                                }
                            }
                        }

                        $panels_data['widgets'][$instance_id] = $widget_instance;
                    }
                    break;
            }
        }

        return $panels_data;
    }

    /**
     * Update siteorigin panel settings data.
     *
     * @param string $demo_id Demo ID.
     * @param array $demo_data Demo Data.
     */
    public function update_siteorigin_data($demo_id, $demo_data)
    {
        if (!empty($demo_data['siteorigin_panels_data_update'])) {
            foreach ($demo_data['siteorigin_panels_data_update'] as $data_type => $data_value) {
                if (!empty($data_value['post_title'])) {
                    $page = get_page_by_title($data_value['post_title']);

                    if (is_object($page) && $page->ID) {
                        $panels_data = get_post_meta($page->ID, 'panels_data', true);

                        if (!empty($panels_data)) {
                            $panels_data = $this->siteorigin_recursive_update($panels_data, $data_type, $data_value);
                        }

                        // Update siteorigin panels data.
                        update_post_meta($page->ID, 'panels_data', $panels_data);
                    }
                }
            }
        }
    }
}

new Mantrabrain_Demo_Importer();
