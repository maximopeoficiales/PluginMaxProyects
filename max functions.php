<?php
require __DIR__ . '/vendor/autoload.php';
error_reporting(0);

/* 
     Plugin Name: Max Functions 
     Plugin URI:
     Description: Añade endpoints y modificaciones necesarios para maxco-proyect
     Version: 1.0.0
     Author Uri: Maximo Junior Apaza Chirhuana
     Text Domain: Max Functions
*/
/* Previene que puedan ver este codigo  */

if (!defined('ABSPATH')) die();

function max_functions_init()
{

	$labels = array(
		'name' => _x('Configuraciones Custom', 'Post Type General Name', 'max_functions'),
		'singular_name' => _x('Configuracion MaxFunctions', 'Post Type Singular Name', 'max_functions'),
		'menu_name' => __('Configuracion Especial', 'max_functions'),
		'name_admin_bar' => __('Configuracion', 'max_functions'),
		'archives' => __('Archivo', 'max_functions'),
		'attributes' => __('Atributos', 'max_functions'),
		'parent_item_colon' => __('Clase Padre', 'max_functions'),
		'all_items' => __('Mostrar Configuracion', 'max_functions'),
		'add_new_item' => __('Agregar Configuracion', 'max_functions'),
		'add_new' => __('Agregar Configuracion', 'max_functions'),
		'new_item' => __('Nueva Configuracion', 'max_functions'),
		'edit_item' => __('Editar Configuracion', 'max_functions'),
		'update_item' => __('Actualizar Configuracion', 'max_functions'),
		'view_item' => __('Ver Configuracion', 'max_functions'),
		'view_items' => __('Ver Configuraciones', 'max_functions'),
		'search_items' => __('Buscar Configuracion', 'max_functions'),
		'not_found' => __('No Encontrado', 'max_functions'),
		'not_found_in_trash' => __('No Encontrado en Papelera', 'max_functions'),
		'featured_image' => __('Imagen Destacada', 'max_functions'),
		'set_featured_image' => __('Guardar Imagen destacada', 'max_functions'),
		'remove_featured_image' => __('Eliminar Imagen destacada', 'max_functions'),
		'use_featured_image' => __('Utilizar como Imagen Destacada', 'max_functions'),
		'insert_into_item' => __('Insertar en Clase', 'max_functions'),
		'uploaded_to_this_item' => __('Agregado en Clase', 'max_functions'),
		'items_list' => __('Lista de Configuraciones', 'max_functions'),
		'items_list_navigation' => __('Navegación de Configuraciones', 'max_functions'),
		'filter_items_list' => __('Filtrar Configuracion', 'max_functions'),
	);
	$args = array(
		'label' => __('Max Functions', 'max_functions'), /* nombre del boton */
		/*   'description' => __('Clases para el Sitio Web', 'max_functions'), */
		'labels' => $labels,
		'supports' => array(''),
		'hierarchical' => false, /* false porque no tiene un padre quien le asigne un template*/
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 6,    /* de 5 en la barra de navegacion */
		'menu_icon' => 'dashicons-admin-tools', /* dash icon de wordpress*/
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true, /* permitir la exportacion en un backup */
		'has_archive' => true,
		'exclude_from_search' => false, /* permitira la busqueda */
		'publicly_queryable' => true,
		'capability_type' => 'page',
	);
	register_post_type('max_functions_config', $args);
}
add_action('init', 'max_functions_init', 0);




require 'inc/woo-functions.php';
require 'inc/endpoints.php';
