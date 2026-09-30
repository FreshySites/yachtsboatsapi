<?php

global $wpdb;

$table1 	= $wpdb->prefix.'boats';
$table2 	= $wpdb->prefix.'agents';
$table3 	= $wpdb->prefix.'boat_categories';
$table4 	= $wpdb->prefix.'boat_conditions';
$table5 	= $wpdb->prefix.'boat_fuel';
$table6 	= $wpdb->prefix.'boat_hull_materials';
$table7 	= $wpdb->prefix.'boat_prices';
$table8 	= $wpdb->prefix.'boat_properties';
$table9 	= $wpdb->prefix.'engines';
$table10 	= $wpdb->prefix.'events';
$table11 	= $wpdb->prefix.'images';
$table12 	= $wpdb->prefix.'videos';
$table13 	= $wpdb->prefix.'offices';

$wpdb->query("DROP TABLE IF EXISTS `$table1`");
$wpdb->query("DROP TABLE IF EXISTS `$table2`");
$wpdb->query("DROP TABLE IF EXISTS `$table3`");
$wpdb->query("DROP TABLE IF EXISTS `$table4`");
$wpdb->query("DROP TABLE IF EXISTS `$table5`");
$wpdb->query("DROP TABLE IF EXISTS `$table6`");
$wpdb->query("DROP TABLE IF EXISTS `$table7`");
$wpdb->query("DROP TABLE IF EXISTS `$table8`");
$wpdb->query("DROP TABLE IF EXISTS `$table9`");
$wpdb->query("DROP TABLE IF EXISTS `$table10`");
$wpdb->query("DROP TABLE IF EXISTS `$table11`");
$wpdb->query("DROP TABLE IF EXISTS `$table12`");
$wpdb->query("DROP TABLE IF EXISTS `$table13`");

    $object = new BoatsAPICreateTables();
    global $wpdb;
    $object->CheckAndCreateBoatsTbl($wpdb);
    $object->CheckAndCreateAgentsTbl($wpdb);
    $object->CheckAndCreateBoatCategoriesTbl($wpdb);
    $object->CheckAndCreateBoatConditionsTbl($wpdb);
    $object->CheckAndCreateBoatFuelTbl($wpdb);
    $object->CheckAndCreateBoatHullMaterialsTbl($wpdb);
    $object->CheckAndCreateBoatPricesTbl($wpdb);
    $object->CheckAndCreateBoatPropertiesTbl($wpdb);
    $object->CheckAndCreateEnginesTbl($wpdb);
    $object->CheckAndCreateEventsTbl($wpdb);
    $object->CheckAndCreateImagesTbl($wpdb);
    $object->CheckAndCreateVideosTbl($wpdb);
    $object->CheckAndCreateOfficesTbl($wpdb);
    $object->CheckAndCreateMoreQueriesTbl($wpdb);

    $object_save 	= new SaveAllBoatsAPIData();
    $apiData 		= $object_save->getBoatsFromAPI();
    $save 			= new BoatsAPI();

    // var_dump($apiData);
    $save->saveBoatsAPIData( $apiData );

    //wp_mail('', 'Cron Test', 'Every Six Hours Cron Job Test.');

    // require_once( BOATS__PLUGIN_DIR . 'templates/import.php' );

