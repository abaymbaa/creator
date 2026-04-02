<?php
namespace CreatorLmsSkills\User;

defined( 'ABSPATH' ) || exit;

use CreatorLmsSkills\PostTypes\SkillTaxonomy;

/**
 * Manager for student skill mastery calculations.
 */
class SkillMasteryManager {

	/**
	 * Meta key for storing mastery points.
	 */
	const META_KEY = '_crlms_skill_mastery';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Logic: Update mastery on quiz attempt save
		add_action( 'creator_lms_after_quiz_attempt_saved', array( $this, 'update_mastery_from_attempt' ), 10, 3 );
		
		// UI: Inject progress bars into profile
		add_action( 'creator_lms_student_profile_after_bio', array( $this, 'render_mastery_ui' ) );
	}

	/**
	 * Update student skill mastery based on a quiz attempt.
	 *
	 * @param \CreatorLms\Data\Quiz $quiz       The quiz object.
	 * @param int                   $student_id The student ID.
	 * @param array                 $arg        Attempt data.
	 */
	public function update_mastery_from_attempt( $quiz, $student_id, $arg ) {
		$attempt_id = isset( $arg['id'] ) ? $arg['id'] : 0;
		if ( ! $attempt_id ) {
			return;
		}

		// Get all questions from this attempt
		$attempt_data = $quiz->get_all_quiz_attempts_by_attempt_id( $student_id, $arg['course_id'], $attempt_id );
		if ( empty( $attempt_data['answers'] ) || ! is_array( $attempt_data['answers'] ) ) {
			return;
		}

		$current_mastery = $this->get_student_mastery( $student_id );

		foreach ( $attempt_data['answers'] as $answer ) {
			$question_id = $answer['question_id'];
			$is_correct  = isset( $answer['achive_mark'] ) && $answer['achive_mark'] > 0;

			// Get skills for this question
			$skills = wp_get_object_terms( $question_id, SkillTaxonomy::TAXONOMY, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $skills ) || empty( $skills ) ) {
				continue;
			}

			foreach ( $skills as $skill_id ) {
				if ( ! isset( $current_mastery[ $skill_id ] ) ) {
					$current_mastery[ $skill_id ] = array(
						'total_questions' => 0,
						'correct_answers' => 0,
					);
				}

				$current_mastery[ $skill_id ]['total_questions']++;
				if ( $is_correct ) {
					$current_mastery[ $skill_id ]['correct_answers']++;
				}
			}
		}

		update_user_meta( $student_id, self::META_KEY, $current_mastery );
	}

	/**
	 * Get student mastery data.
	 *
	 * @param int $student_id The student ID.
	 * @return array
	 */
	public static function get_student_mastery( $student_id ) {
		$mastery = get_user_meta( $student_id, self::META_KEY, true );
		return is_array( $mastery ) ? $mastery : array();
	}

	/**
	 * Render the mastery progress bars UI.
	 *
	 * @param \CreatorLms\Data\Student $student The student object.
	 */
	public function render_mastery_ui( $student ) {
		$mastery = self::get_student_mastery( $student->get_id() );
		if ( empty( $mastery ) ) {
			return;
		}
		?>
		<div class="profile-skills-mastery">
			<h5 class="profile-section-title"><?php echo esc_html__( 'Skill Mastery', 'creatorlms-skills' ); ?></h5>
			<div class="skills-mastery-list">
				<?php
				foreach ( $mastery as $skill_id => $data ) :
					$term = get_term( $skill_id, SkillTaxonomy::TAXONOMY );
					if ( is_wp_error( $term ) || ! $term ) {
						continue;
					}
					$accuracy = $data['total_questions'] > 0 ? round( ( $data['correct_answers'] / $data['total_questions'] ) * 100 ) : 0;
					?>
					<div class="skill-mastery-item">
						<div class="skill-info">
							<span class="skill-name"><?php echo esc_html( $term->name ); ?></span>
							<span class="skill-stats"><?php printf( esc_html__( '%d/%d Correct', 'creatorlms-skills' ), $data['correct_answers'], $data['total_questions'] ); ?></span>
						</div>
						<div class="skill-progress-bar">
							<div class="skill-progress-fill" style="width: <?php echo esc_attr( $accuracy ); ?>%"></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
