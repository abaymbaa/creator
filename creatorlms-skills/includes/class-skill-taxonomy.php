<?php
namespace CreatorLmsSkills\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Skill taxonomy for questions and quizzes.
 */
class SkillTaxonomy {

	/**
	 * Taxonomy slug.
	 */
	const TAXONOMY = 'creatorlms_skill';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_taxonomy' ), 20 );
	}

	/**
	 * Register the taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Skills', 'taxonomy general name', 'creatorlms-skills' ),
			'singular_name'              => _x( 'Skill', 'taxonomy singular name', 'creatorlms-skills' ),
			'search_items'               => __( 'Search Skills', 'creatorlms-skills' ),
			'popular_items'              => __( 'Popular Skills', 'creatorlms-skills' ),
			'all_items'                  => __( 'All Skills', 'creatorlms-skills' ),
			'parent_item'                => __( 'Parent Skill', 'creatorlms-skills' ),
			'parent_item_colon'          => __( 'Parent Skill:', 'creatorlms-skills' ),
			'edit_item'                  => __( 'Edit Skill', 'creatorlms-skills' ),
			'update_item'                => __( 'Update Skill', 'creatorlms-skills' ),
			'add_new_item'               => __( 'Add New Skill', 'creatorlms-skills' ),
			'new_item_name'              => __( 'New Skill Name', 'creatorlms-skills' ),
			'separate_items_with_commas' => __( 'Separate skills with commas', 'creatorlms-skills' ),
			'add_or_remove_items'        => __( 'Add or remove skills', 'creatorlms-skills' ),
			'choose_from_most_used'      => __( 'Choose from the most used skills', 'creatorlms-skills' ),
			'not_found'                  => __( 'No skills found.', 'creatorlms-skills' ),
			'menu_name'                  => __( 'Skills', 'creatorlms-skills' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'skill' ),
			'show_in_rest'          => true,
		);

		register_taxonomy(
			self::TAXONOMY,
			array( 'crlms-question', 'crlms-quiz' ),
			$args
		);
	}
}
