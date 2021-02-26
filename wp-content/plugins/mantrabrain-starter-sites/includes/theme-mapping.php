<?php

if (!function_exists('mantrabrain_starter_sites_supported_themes')) {

    function mantrabrain_starter_sites_supported_themes()
    {

        return apply_filters('mantrabrain_starter_sites_supported_themes', array(

            'mantranews' => 'Mantranews',
            'mantranews-pro' => 'Mantranews Pro',
            'november-zero' => 'November Zero',
            'november-zero-pro' => 'November Zero Pro',
            'agency-ecommerce' => 'Agency Ecommerce',
            'magazinenp' => 'MagazineNP',
            'pragyan' => 'Pragyan',
        ));
    }
}

if (!function_exists('mantrabrain_starter_sites_demo_directory_mapping')) {

    function mantrabrain_starter_sites_demo_directory_mapping()
    {

        return apply_filters('mantrabrain_starter_sites_demo_directory_mapping', array(

            'mantranews' => 'mantranews',
            'mantranews-pro' => 'mantranews',
            'november-zero' => 'november-zero',
            'november-zero-pro' => 'november-zero',
            'agency-ecommerce' => 'agency-ecommerce',
            'magazinenp' => 'magazinenp',
            'pragyan' => 'pragyan',
        ));


    }
}