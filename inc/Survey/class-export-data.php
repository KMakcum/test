<?php
/**
 * Export data from survey (questions ans answers)
 *
 * @class   SFExportSurveyData
 * @package LifeChef\Classes
 */


defined( 'ABSPATH' ) || exit;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class SFExportSurveyData
 */
class SFExportSurveyData {

	private static $_instance = null;

	private function __construct() {
	}

	/**
	 * @return SFImport
	 */
	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		add_action( 'custom', [ $this, 'create_file' ] );
	}

	private function get_questions() {
		global $wpdb;

		$result = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE `post_type` ='sf_survey_step'; " );

		$data = [];
		foreach ( $result as $question ) {
			$data[] = [
				'post_id'    => $question->ID,
				'post_title' => $question->post_title,
				'questions'  => carbon_get_post_meta( $question->ID, 'sf_survey' )
			];
		}

		return $data;
	}

	private function get_diets_group() {
		return carbon_get_theme_option( "sf_diet_groups" );
	}

	private function get_components_group() {
		return carbon_get_theme_option( "sf_additional_component_groups" );
	}

	public function create_file() {

		$questions   = $this->get_questions();
		$spreadsheet = new Spreadsheet();

		$sheet = $spreadsheet->getActiveSheet();

		// Groups (wp posts with carbon fields in)
		$sheet->setTitle( 'Questions Group' );
		$data_question_group = $this->create_groups_data( $questions );
		$sheet->fromArray( $data_question_group, null, 'A1' );

		// Group id & Questions titles (becouse questions doesnt have ids)
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'Group id & Questions' );
		$data_questions = $this->create_data_questions( $questions );
		$sheet->fromArray( $data_questions, null, 'A1' );

		// Sub block in questions (question, condition, answers, banner)
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'Questions Blocks' );
		$data_questions_blocks = $this->create_data_questions_blocks( $questions );
		$sheet->fromArray( $data_questions_blocks, null, 'A1' );

		// Sub block in questions (Answers LIST)
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'Answer Lists' );
		$data_answers_lists = $this->create_data_answers_lists( $questions );
		$sheet->fromArray( $data_answers_lists, null, 'A1' );

		// Diets from survey settings
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'Diets Group' );
		$data_diets_group = $this->create_data_diets_group();
		$sheet->fromArray( $data_diets_group, null, 'A1' );

		// Components Groups from survey settings
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'Components Group' );
		$data_components_group = $this->create_data_components_group();
		$sheet->fromArray( $data_components_group, null, 'A1' );

		// List of components with ids from all parts
		$sheet = $spreadsheet->createSheet();
		$sheet->setTitle( 'List of Components' );
		$data_components = $this->create_data_components();
		$sheet->fromArray( $data_components, null, 'A1' );

		$writer = new Xlsx( $spreadsheet );
		$writer->save( ABSPATH . 'survey-data.xlsx' );
		//$writer->save( 'php://output' );
	}

	private function create_groups_data( $questions ) {
		$headers             = [ 'Wp ID', 'Title' ];
		$data_question_group = [
			$headers
		];

		foreach ( $questions as $question ) {
			$data_question_group[] = [ $question['post_id'], $question['post_title'] ];
		}

		return $data_question_group;
	}

	private function create_data_questions( $questions ) {
		$headers_questions = [
			'group_id',
			'title question',
			'type',
			'show_in_catalog_filters_title',
			'show_in_catalog_filters',
			'filter_type'
		];

		$data_questions = [
			$headers_questions
		];

		foreach ( $questions as $question ) {
			foreach ( $question['questions'] as $single ) {
				$data_questions[] = [
					$question['post_id'],
					$single['question_title'],
					$single['_type'],
					$single['show_in_catalog_filters_title'],
					$single['show_in_catalog_filters'],
					$single['filter_type']
				];
			}
		}

		return $data_questions;
	}

	private function create_data_questions_blocks( $questions ) {
		$headers_questions_blocks = [
			'group_id',
			'question_title',
			'_type',
			'icon',
			'description',
			'notice',
			'confirm',
			'required_question',
			'button',
			'skip',
			'show_in_result',
			'result_title',
			'strength',
			'strength_male_score',
			'strength_female_score',
			'answers_in_row',
			'banner_enabled',
			'banner_background',
			'banner_title',
			'banner_description',
			'condition_crb_information_text',
			'condition_enabled',
			'condition_target',
			'condition_compare',
			'condition_c_value'
		];

		$data_questions_blocks = [
			$headers_questions_blocks
		];

		foreach ( $questions as $question ) {

			foreach ( $question['questions'] as $single ) {

				foreach ( $single['components'] as $component ) {
					$data_questions_blocks[] = [
						$question['post_id'],
						$single['question_title'],
						$this->return_value( $component['_type'] ),
						$this->return_value( $component['icon'] ),
						$this->return_value( $component['description'] ),
						$this->return_value( $component['notice'] ),
						$this->return_value( $component['confirm'] ),
						$this->return_value( $component['required_question'] ),
						$this->return_value( $component['button'] ),
						$this->return_value( $component['skip'] ),
						$this->return_value( $component['show_in_result'] ),
						$this->return_value( $component['result_title'] ),
						$this->return_value( $component['strength'] ),
						$this->return_value( $component['strength_male_score'] ),
						$this->return_value( $component['strength_female_score'] ),
						$this->return_value( $component['answers_in_row'] ),
						$this->return_value( $component['enabled'] ),
						$this->return_value( $component['background'] ),
						$this->return_value( $component['title'] ),
						$this->return_value( $component['description'] ),
						$this->return_value( $component['crb_information_text'] ),
						$this->return_value( $component['enabled'] ),
						$this->return_value( $component['target'] ),
						$this->return_value( $component['compare'] ),
						$this->return_value( $component['c_value'] ),
					];
				}
			}

		}

		return $data_questions_blocks;
	}

	private function create_data_answers_lists( $questions ) {
		$headers_answers_list = [
			'group_id',
			'question_title',
			'reset',
			'show_in_filter',
			'icon',
			'icon_show',
			'title',
			'external',
			'note',
			'components',
			'components_mode',
			'components_score',
			'diets',
			'diets_mode',
			'diets_score',
			'allergens',
			'allergens_mode'
		];

		$data_answers_lists = [
			$headers_answers_list
		];

		foreach ( $questions as $question ) {

			foreach ( $question['questions'] as $single ) {

				foreach ( $single['components'] as $component ) {
					if ( $component['_type'] == 'answer_list' ) {

						foreach ( $component['answer_list'] as $answer ) {

							$data_answers_lists[] = [
								$question['post_id'],
								$single['question_title'],
								$this->return_value( $answer['reset'] ),
								$this->return_value( $answer['show_in_filter'] ),
								$this->return_value( $answer['icon'] ),
								$this->return_value( $answer['icon_show'] ),
								$this->return_value( $answer['title'] ),
								$this->return_value( $answer['external'] ),
								$this->return_value( $answer['note'] ),
								$this->return_value( implode( ',', $answer['components'][0]['items'] ) ),
								$this->return_value( $answer['components'][0]['mode'] ),
								$this->return_value( $answer['components'][0]['score'] ),
								$this->return_value( implode( ',', $answer['diets'][0]['items'] ) ),
								$this->return_value( $answer['diets'][0]['mode'] ),
								$this->return_value( $answer['diets'][0]['score'] ),
								$this->return_value( implode( ',', $answer['allergens'][0]['items'] ) ),
								$this->return_value( $answer['allergens'][0]['mode'] ),
								$this->return_value( $answer['allergens'][0]['score'] ),

							];

						}

					}
				}

			}

		}

		return $data_answers_lists;
	}

	private function create_data_diets_group() {
		$diets_group         = $this->get_diets_group();
		$headers_diets_group = [
			'title',
			'slug'
		];

		$data_diets_group = [
			$headers_diets_group
		];

		foreach ( $diets_group as $diet ) {
			$data_diets_group[] = [
				$diet['title'],
				$diet['slug']
			];
		}

		return $data_diets_group;
	}

	private function create_data_components_group() {
		$components_group = $this->get_components_group();

		$headers_components_group = [
			'title',
			'slug',
			'components_ids',
		];

		$data_components_group = [
			$headers_components_group
		];

		foreach ( $components_group as $component ) {
			$chosen_components_ids = array_column( $component['components'], 'id' );

			$data_components_group[] = [
				$component['title'],
				$component['slug'],
				implode( ',', $chosen_components_ids )
			];
		}

		return $data_components_group;
	}

	private function create_data_components() {
		$components = $this->get_components();

		$headers_components = [
			'title',
			'all_ids_from_parts',
		];

		$data_components = [
			$headers_components
		];

		foreach ( $components as $name => $ids ) {
			$data_components[] = [
				$name,
				implode( ',', $ids )
			];
		}

		return $data_components;
	}

	private function get_components() {
		global $wpdb;

		$unique_ids = $wpdb->get_results(
			"SELECT tm.meta_value FROM {$wpdb->termmeta} as tm 
			LEFT JOIN `{$wpdb->term_taxonomy}` as tt ON tt.term_id = tm.term_id 
			WHERE tt.taxonomy = 'pa_part-1' AND tm.meta_key = '_op_variations_component_sku'"
		);

		$ids = implode( ',', array_column( $unique_ids, 'meta_value' ) );

		$name_term_id = $wpdb->get_results(
			"SELECT t.name, tm.term_id FROM {$wpdb->termmeta} as tm 
					LEFT JOIN `{$wpdb->terms}` as t ON  tm.term_id = t.term_id
				   WHERE meta_value IN ({$ids})"
		);

		$convert = [];

		foreach ( $name_term_id as $item ) {
			$convert[ $item->name ][] = $item->term_id;
		}

		return $convert;
	}

	private function return_value( $component ) {
		if ( ! empty( $component ) ) {
			return $component;
		}

		return '';
	}

	public function debug( $data ) {
		echo '<pre>';
		var_dump( $data );
		echo '</pre>';
	}

}