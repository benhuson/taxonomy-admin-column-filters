<?php

/*
Plugin Name: Taxonomy Admin Column Filters
Plugin URI:  https://github.com/benhuson/taxonomy-admin-column-filters
Description: Framework for adding a taxonomy filter menus to post listings in admin.
Version:     0.1
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: taxonomy-admin-column-filter
*/

class Taxonomy_Admin_Column_Filters_Plugin {
	
	/**
	 * Constructor
	 *
	 * @since   0.1
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'current_screen', array( $this, 'setup_admin_screen_hooks' ) );
		}

	}
	
	/**
	 * Setup Admin Screen Hooks
	 *
	 * @since   0.1
	 * @access  private
	 */
	public function setup_admin_screen_hooks() {

		if ( $this->is_admin_edit_screen() ) {
			add_screen_option( 'layout_columns',  array(
				'label' => __('Members per page', 'pippin'),
				'default' => 10,
				'option' => 'pippin_per_page'
			) );
			add_action( 'restrict_manage_posts', array( $this, 'admin_posts_tax_filter_menu' ) );
		}

	}

	/**
	 * Is Admin Edit Screen
	 *
	 * @since   0.1
	 * @access  private
	 *
	 * @return  boolean
	 */
	private function is_admin_edit_screen() {

		if ( is_admin() && function_exists( 'get_current_screen' ) ) {

			$screen = get_current_screen();

			if ( 'edit' == $screen->base && in_array( $screen->post_type, $this->get_supported_post_types() ) ) {
				return true;
			}

		}

		return false;

	}

	/**
	 * Get Admin Edit Screen Post Type
	 *
	 * @since   0.1
	 * @access  private
	 *
	 * @return  string  Post type.
	 */
	private function get_admin_edit_screen_post_type() {

		$screen = get_current_screen();

		return $screen->post_type;

	}

	/**
	 * Get Admin Edit Screen Taxonomies
	 *
	 * @since   0.1
	 * @access  private
	 *
	 * @return  array  Taxonomy objects.
	 */
	private function get_admin_edit_screen_taxonomies() {

		$post_type = $this->get_admin_edit_screen_post_type();

		return $this->get_taxonomies( $post_type );

	}

	/**
	 * Get Supported Post Types
	 *
	 * @since   0.1
	 * @access  private
	 *
	 * @return  array  Post types.
	 */
	private function get_supported_post_types() {

		$post_types = array_values( get_post_types( array( '_builtin' => false ) ) );

		$post_types[] = 'page';
		$post_types[] = 'post';

		return apply_filters( 'taxonomy_admin_column_filters_post_types', $post_types );

	}

	/**
	 * Get Taxonomies
	 *
	 * @since   0.1
	 * @access  private
	 *
	 * @param   string  $post_type  Post type.
	 * @return  array               Taxonomy objects.
	 */
	private function get_taxonomies( $post_type ) {

		return apply_filters( 'taxonomy_admin_column_filters_taxonomies', get_object_taxonomies( $post_type ), $post_type );

	}

	/**
	 * Add a collection filter menu to the custom post type overview admin page
	 *
	 * @since   0.1
	 * @access  private
	 */
	public function admin_posts_tax_filter_menu() {

		global $wp_query;

		$taxonomies = $this->get_admin_edit_screen_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			$taxonomy_object = get_taxonomy( $taxonomy );

			// Selected
			if ( isset( $wp_query->query[ $taxonomy ] ) && ! empty( $wp_query->query[ $taxonomy ] ) ) {
				$term = get_term_by( 'slug', $wp_query->query[ $taxonomy ], $taxonomy );
				$selected = $term->slug;
			} else {
				$selected = '';
			}

			wp_dropdown_categories( array(
				'show_option_all' =>  __( "Show {$taxonomy_object->labels->all_items}" ),
				'taxonomy'        =>  $taxonomy,
				'name'            =>  $taxonomy,
				'orderby'         =>  'name',
				'value_field'     => 'slug',
				'selected'        =>  $selected,
				'hierarchical'    =>  true,
				'depth'           =>  3,
				'show_count'      =>  true,   // Show number of items in term
				'hide_empty'      =>  false,  // Don't show terms w/o listings
			) );
 
		}

	}
	
}

new Taxonomy_Admin_Column_Filters_Plugin();
