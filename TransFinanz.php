<?php

/*
		Plugin Name: TransFinanz
		Plugin URI: http://www.kay-wilhelm.de
		Description: Einfache, transparente und basisdemokratische Finanzverwaltung
		Author: Kay Wilhelm Mähler
		Author URI: http://www.kay-wilhelm.de
		Version: 1.0.0
	*/

	ini_set('display_errors', '1');
	require('TF_Form.php');
	require('functions.php');
	require('Postlist.php');
	require('Geldquelle.php');
	require('KontoKategorie.php');
	require('Konto.php');
	require('AbsetzbareMittel.php');
	require('Vorgang.php');
	require('Vorgaenge.php');
	require('Einstellungen.php');
	require('Antrag.php');
	require('Haushaltsplan.php');
	
	if(isset($_GET['tf_excel_export']) && $_GET['tf_excel_export'] == "Vorgang Excel exportieren")
	{
		add_action("wp_loaded", "excelDownload");
	}
	
	add_action('init', 'TransFinanzInit');
	
	//Frontend
	add_action('save_post',												array('Antrag', 'save'));
	add_shortcode('antrag_form',										array('Antrag', 'frontendMaske'));
	add_shortcode('haushaltsplan',										array('Haushaltsplan', 'anzeige'));
	
	//Backend
	if(is_admin())
	{
		add_action('admin_init',										array('Einstellungen', 'register'));
		add_action('admin_menu',										array('Einstellungen', 'EinstellungenMenu'));
		add_action('admin_notices',										array('Einstellungen', 'errorMessage')); 
		
		if(get_option('tf_import') == "")
		{
			set_time_limit(6000);
			importData();
			update_option("tf_import", "-");
		}
		
		add_action('add_meta_boxes',									array('Geldquelle', 'metabox'));
		add_action('save_post',											array('Geldquelle', 'save'));
		add_filter('manage_tf_geldquelle_posts_columns',				array('Geldquelle', 'postlist'));
		add_filter('manage_tf_geldquelle_posts_custom_column',			array('Geldquelle', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_geldquelle_sortable_columns',		array('Geldquelle', 'postlist_sorting'));
		add_filter('request',											array('Geldquelle', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('Geldquelle', 'postlist_filtering'));
		add_filter('parse_query',										array('Geldquelle', 'postlist_filtering_sort'));
		add_filter('post_row_actions',									array('Geldquelle', 'postlist_options'), 10, 2);

		add_action('add_meta_boxes',									array('KontoKategorie', 'metabox'));
		add_action('save_post',											array('KontoKategorie', 'save'));
		add_filter('manage_tf_kontokategorie_posts_columns',			array('KontoKategorie', 'postlist'));
		add_filter('manage_tf_kontokategorie_posts_custom_column',		array('KontoKategorie', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_kontokategorie_sortable_columns',	array('KontoKategorie', 'postlist_sorting'));
		add_filter('request',											array('KontoKategorie', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('KontoKategorie', 'postlist_filtering'));
		add_filter('parse_query',										array('KontoKategorie', 'postlist_filtering_sort'));
		add_filter('post_row_actions',									array('KontoKategorie', 'postlist_options'), 10, 2);

		add_action('add_meta_boxes',									array('Konto', 'metabox'));
		add_action('save_post',											array('Konto', 'save'));
		add_filter('manage_tf_konto_posts_columns',						array('Konto', 'postlist'));
		add_filter('manage_tf_konto_posts_custom_column',				array('Konto', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_konto_sortable_columns',				array('Konto', 'postlist_sorting'));
		add_filter('request',											array('Konto', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('Konto', 'postlist_filtering'));
		add_filter('parse_query',										array('Konto', 'postlist_filtering_sort'));
		add_filter('post_row_actions',									array('Konto', 'postlist_options'), 10, 2);

		add_action('add_meta_boxes',									array('AbsetzbareMittel', 'metabox'));
		add_action('save_post',											array('AbsetzbareMittel', 'save'));
		add_filter('manage_tf_absetzbaremittel_posts_columns',			array('AbsetzbareMittel', 'postlist'));
		add_filter('manage_tf_absetzbaremittel_posts_custom_column',	array('AbsetzbareMittel', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_absetzbaremittel_sortable_columns',	array('AbsetzbareMittel', 'postlist_sorting'));
		add_filter('request',											array('AbsetzbareMittel', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('AbsetzbareMittel', 'postlist_filtering'));
		add_filter('parse_query',										array('AbsetzbareMittel', 'postlist_filtering_sort'));
		add_filter('post_row_actions',									array('AbsetzbareMittel', 'postlist_options'), 10, 2);

		add_action('add_meta_boxes',									array('Vorgang', 'metabox'));
		add_action('save_post',											array('Vorgang', 'save'));
		add_action('trashed_post',										array('Vorgang', 'trash'));
		add_action('untrashed_post',									array('Vorgang', 'untrash'));
		add_action('before_delete_post',								array('Vorgang', 'delete'));
		add_filter('manage_tf_vorgang_posts_columns',					array('Vorgang', 'postlist'));
		add_filter('manage_tf_vorgang_posts_custom_column',				array('Vorgang', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_vorgang_sortable_columns',			array('Vorgang', 'postlist_sorting'));
		add_filter('request',											array('Vorgang', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('Vorgang', 'postlist_filtering'));
		add_filter('parse_query',										array('Vorgang', 'postlist_filtering_sort'));
		add_action('admin_menu',										array('Vorgang', 'VorgangMenu'));
		add_filter('post_row_actions',									array('Vorgang', 'postlist_options'), 10, 2);
		
		add_action('add_meta_boxes',									array('Antrag', 'metabox'));
		add_filter('manage_tf_antrag_posts_columns',					array('Antrag', 'postlist'));
		add_filter('manage_tf_antrag_posts_custom_column',				array('Antrag', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_antrag_sortable_columns',			array('Antrag', 'postlist_sorting'));
		add_filter('request',											array('Antrag', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('Antrag', 'postlist_filtering'));
		add_filter('parse_query',										array('Antrag', 'postlist_filtering_sort'));
		add_action('admin_menu',										array('Antrag', 'AntragMenu'));
		add_filter('post_row_actions',									array('Antrag', 'postlist_options'), 10, 2);
		
		add_action('add_meta_boxes',									array('Haushaltsplan', 'metabox'));
		add_filter('manage_tf_haushaltsplan_posts_columns',				array('Haushaltsplan', 'postlist'));
		add_filter('manage_tf_haushaltsplan_posts_custom_column',		array('Haushaltsplan', 'postlist_column'), 10, 2);
		add_filter('manage_edit-tf_haushaltsplan_sortable_columns',		array('Haushaltsplan', 'postlist_sorting'));
		add_filter('request',											array('Haushaltsplan', 'postlist_orderby'));
		add_action('restrict_manage_posts',								array('Haushaltsplan', 'postlist_filtering'));
		add_filter('parse_query',										array('Haushaltsplan', 'postlist_filtering_sort'));
		add_action('admin_menu',										array('Haushaltsplan', 'HaushaltsplanMenu'));
		add_filter('post_row_actions',									array('Haushaltsplan', 'postlist_options'), 10, 2);
	}
	
?>