<?php
/**
 * Plugin Name: CreatorLMS Quiz Skills
 * Plugin URI: https://creatorlms.com/
 * Description: Extension for CreatorLMS to categorize questions by skills and track student mastery.
 * Version: 1.0.0
 * Author: CreatorLMS
 * Author URI: https://creatorlms.com/
 * Text Domain: creatorlms-skills
 * Domain Path: /languages
 *
 * @package CreatorLmsSkills
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main class for CreatorLMS Quiz Skills add-on.
 */
class CreatorLmsSkills {

	/**
	 * Version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		// Check if CreatorLMS is active
		if ( ! class_exists( 'CreatorLMS' ) ) {
			add_action( 'admin_notices', array( $this, 'missing_dependency_notice' ) );
			return;
		}

		$this->includes();
		$this->setup();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-skill-taxonomy.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-skill-mastery-manager.php';
	}

	/**
	 * Setup components.
	 */
	private function setup() {
		new \CreatorLmsSkills\PostTypes\SkillTaxonomy();
		new \CreatorLmsSkills\User\SkillMasteryManager();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {
		if ( function_exists( 'is_creator_lms' ) && is_creator_lms() ) {
			wp_enqueue_style( 'crlms-skills-css', plugin_dir_url( __FILE__ ) . 'assets/css/skills.css', array(), self::VERSION );
		}
	}

	/**
	 * Notice for missing CreatorLMS.
	 */
	public function missing_dependency_notice() {
		?>
		<div class="error notice">
			<p><?php echo esc_html__( 'CreatorLMS Quiz Skills requires CreatorLMS plugin to be active.', 'creatorlms-skills' ); ?></p>
		</div>
		<?php
	}
}

new CreatorLmsSkills();
