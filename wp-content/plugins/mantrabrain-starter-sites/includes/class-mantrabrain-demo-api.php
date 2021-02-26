<?php

class Mantrabrain_Demo_API
{
    //private static $api_uri = 'http://localhost/WordPressThemes/subidha/wp-content/mantrabrain-demo-pack/';

    private static $api_uri = 'https://raw.githubusercontent.com/mantrabrain/mantrabrain-demo-pack/master/';


    private static function file_data_array($url = '')
    {
        $response = wp_remote_head($url);

        $status = (200 === wp_remote_retrieve_response_code($response));

        if (!$status) {

            return array();
        }

        $file_data = mantrabrain_file_get_contents($url);

        $file_data_array = json_decode($file_data, true);

        return $file_data_array;

    }

    public static function get_valid_themes()
    {

        $supported_themes = mantrabrain_starter_sites_supported_themes();

        $mantrabrain_supported_theme_slug = array_keys($supported_themes);

        return $mantrabrain_supported_theme_slug;


    }

    public static function get_theme_demo_configuration($theme_slug = '')
    {

        $current_template = empty($theme_slug) ? get_option('template') : $theme_slug;

        $demo_config_array = get_transient('mantrabrain_get_theme_demo_configuration');

        if (is_array($demo_config_array) && $demo_config_array && (isset($demo_config_array['slug']) && $current_template === $demo_config_array['slug'])) {

            return $demo_config_array;

        }

        $supported_demos_config = mantrabrain_starter_sites_demo_directory_mapping();

        $config_file_path_for_template = isset($supported_demos_config[$current_template]) ? self::$api_uri . $supported_demos_config[$current_template] . '/' . $supported_demos_config[$current_template] . '.json' : '';

        $demo_config_array = array();

        if (!empty($config_file_path_for_template)) {

            $demo_config_array = self::file_data_array($config_file_path_for_template);
        }
        set_transient('mantrabrain_get_theme_demo_configuration', $demo_config_array, DAY_IN_SECONDS);

        return $demo_config_array;
    }

    public static function get_theme_demo_data_uri($demo_slug = '', $theme_slug = '')
    {

        $current_template = empty($theme_slug) ? get_option('template') : $theme_slug;

        $supported_demos_config = mantrabrain_starter_sites_demo_directory_mapping();

        $demo_directory_path = isset($supported_demos_config[$current_template]) ? self::$api_uri . $supported_demos_config[$current_template] . '/' : '';

        $demo_config = self::get_theme_demo_configuration($theme_slug);

        $demos = isset($demo_config['demos']) ? $demo_config['demos'] : array();

        $all_demo_path_config = array();

        $all_demo_datas = $demos;

        if (!empty($demo_slug) && isset($demos[$demo_slug])) {

            $all_demo_datas = array(
                $demo_slug => $demos[$demo_slug]
            );
        }


        foreach ($all_demo_datas as $single_demo_slug => $single_demo_data) {

            $demo_uri = $demo_directory_path . $single_demo_slug . '/';

            $demo_data_config = array(

                'import_file_name' => isset($single_demo_data['title']) ? $single_demo_data['title'] : '',

                'import_file_url' => $demo_uri . 'data/content.xml',

                'import_widget_file_url' => $demo_uri . 'data/widgets.wie',

                'import_customizer_file_url' => $demo_uri . 'data/customizer.dat',

                'import_preview_image_url' => $demo_uri . 'screenshot.png',

                'import_notice' => isset($single_demo_data['import_notice']) ? $single_demo_data['import_notice'] : '',

                'preview_url' => isset($single_demo_data['preview']) ? $single_demo_data['preview'] : '',


            );

            $all_demo_path_config[$single_demo_slug] = $demo_data_config;
        }

        if (!empty($demo_slug) && isset($all_demo_path_config[$demo_slug])) {

            return $all_demo_path_config[$demo_slug];
        }

        return $all_demo_path_config;

    }


}

?>