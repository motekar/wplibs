<?php
namespace Motekar\WPLibs;

class Settings_Page {
	public $page_name;
	public $option_name;

	private $settings_array = [];
	private $recent_data = [];

	public function __construct( $page_name, $option_name = '', $callback = false ) {
		$this->page_name   = $page_name;
		$this->option_name = empty( $option_name ) ? $page_name : $option_name;

		\add_action( 'admin_init', function() use ( $callback ) {
			// callback to populate $setings_array
			call_user_func( $callback, $this );
			$this->init_settings();
		} );

		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		\add_action( 'plugins_loaded', array( $this, 'save_options' ) );
	}

	public function tab( $tab ) {
		$this->recent_data['tab'] = $tab;

		if ( ! isset( $this->settings_array[$tab] ) ) {
			$this->settings_array[$tab] = [
				'sections' => []
			];
		}
	}

	public function section( $section, $label = '' ) {
		$tab = $this->recent_data['tab'] ?? '';

		if ( empty( $tab ) ) {
			return false;
		}

		$this->recent_data['section'] = $section;

		if ( ! isset( $this->settings_array[$tab]['sections'][$section] ) ) {
			$this->settings_array[$tab]['sections'][$section] = [
				'label'  => $label,
				'fields' => []
			];
		}
	}

	public function field( $field_name, $field_type, $args ) {
		$tab     = $this->recent_data['tab'] ?? '';
		$section = $this->recent_data['section'] ?? '';

		if ( empty( $tab ) || empty( $section ) ) {
			return false;
		}

		$args['type']  = $field_type;
		$args['label'] = $args['label'] ?? ucwords( str_replace( ['_', '-'], ' ', $field_name) );

		$this->settings_array[$tab]['sections'][$section]['fields'][$field_name] = $args;
	}

	public function section_for( $parent, $section_name, $section_label = '' ) {
		if ( ! isset( $this->settings_array[$parent] ) ) {
			return false;
		}

		$this->recent_data['section'] = $section_name;

		$this->settings_array[$parent]['sections'][$section_name] = [
			'label'  => $section_label,
			'fields' => []
		];
	}

	public function field_for( $parent, $field_name, $field_type, $args ) {
		$parents = explode( '.', $parent );
		if ( count( $parents ) != 2 ) {
			return false;
		}

		list( $tab, $section ) = $parents;

		$args['type']  = $field_type;
		$args['label'] = $args['label'] ?? ucwords( str_replace( ['_', '-'], ' ', $field_name) );
		$this->settings_array[$tab]['sections'][$section]['fields'][$field_name] = $args;
	}

	public function init_settings() {
		$this->settings_array = \apply_filters( $this->page_name . '_scheme', $this->settings_array );

		// save initial settings
		if ( false === get_option( $this->option_name, false ) ) {
			$initial_options = [];
			foreach ( $this->settings_array as $page_name => $page ) {
				foreach ( $page['sections'] as $section_name => $section ) {
					foreach ( $section['fields'] as $field_name => $field ) {
						$initial_options[$field_name] = $field['default'] ?? '';
					}
				}
			}
			\update_option( $this->option_name, $initial_options );
		}

		$this->_register_settings();
	}

	private function _default_settings() {
		return [
			'tab-1' => [
				'sections' => [
					'basic' => [
						'label' => 'Basic Fields',
						'fields' => [
							'text' => [
								'type' => 'text',
								'label' => 'Text Field'
							],
							'textarea' => [
								'type' => 'textarea',
								'label' => 'Textarea Field'
							],
							'select' => [
								'type'          => 'select',
								'label'         => 'Select Field',
								'default'       => '',
								'placeholder'   => '-- Select Option --',
								'options'       => [
									'option-1' => 'Option 1',
									'option-2' => 'Option 2',
									'option-3' => 'Option 3',
									'option-4' => 'Option 4',
								],
								'desc'          => 'This is select field',
							],
							'radio' => [
								'type'         => 'radio',
								'label'        => 'Radio Field',
								'default'      => 'option-1',
								'options'      => [
									'option-1' => 'Option 1',
									'option-2' => 'Option 2',
									'option-3' => 'Option 3',
									'option-4' => 'Option 4',
								],
								'desc'         => 'This is radio field',
							],
							'checkbox' => [
								'type'         => 'checkbox',
								'label'        => 'Checkbox Field',
								'label_title'  => 'Enable',
								'default'      => '1',
								'desc'         => 'This is checkbox field',
							],
							'checkboxes' => [
								'type'         => 'checkbox',
								'label'        => 'Checkbox Field (multiple)',
								'default'      => 'option-1',
								'options'      => [
									'option-1' => 'Option 1',
									'option-2' => 'Option 2',
									'option-3' => 'Option 3',
									'option-4' => 'Option 4',
								],
								'desc'         => 'This is checkbox field with multiple checkboxes',
							],
						]
					]
				]
			],
			'tab-2' => [
				'sections' => [
					'advanced' => [
						'label' => 'Advanced Fields',
						'fields' => [
							'media' => [
								'type' => 'media',
								'label' => 'Media Field'
							],
							'banks' => [
								'type' => 'bank-accounts',
								'label' => 'Bank Accounts'
							]
						]
					]
				]
			]
		];
	}

	private function _register_settings() {
		foreach ( $this->settings_array as $page_name => $page ) {
			if ( ! isset( $page['sections'] ) ) {
				continue;
			}

			foreach ( $page['sections'] as $section_name => $section ) {
				\add_settings_section(
					$section_name, // id
					$section['label'] ?? '', // title
					null,  // callback
					$page_name // page
				);

				if ( ! isset( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field_name => $field ) {
					$field['id']          = "setting_{$section_name}_{$field_name}";
					$field['name']        = $field_name;
					$field['description'] = $field['desc'] ?? '';
					$field['class']       = empty( $field['class'] ) ? '' : $field['class'];

					$long_field_name = "{$this->page_name}_{$field_name}";

					\add_settings_field(
						$long_field_name,  // id
						$field['label'],  // title
						array( $this, 'render_field' ), // callback
						$page_name,   // page
						$section_name,  // section
						$field // args
					);
					\register_setting(
						$this->page_name, // option group
						$long_field_name // option name
					);

				}
			}
		}
	}

	private function _get_option( $option, $default = false ) {
		$options = \get_option( $this->option_name, $default );

		return $options[$option] ?? $default;
	}

	public function enqueue_scripts() {
		if ( filter_input( INPUT_GET, 'page') != $this->page_name ) {
			return;
		}

		\wp_enqueue_media();
		\wp_enqueue_script( 'alpinejs', 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js', array(), '2.x.x' );
	}

	public function render_page() {
		Helper::view( 'settings', [
			'page_name'   => $this->page_name,
			'option_name' => $this->option_name,
		] );
	}

	public function render_field( $args ) {
		$args['value']    =  $this->_get_option( $args['name'] );
		$args['raw_name'] = $args['name'];
		$args['name']     = "{$this->page_name}[{$args['name']}]";

		Helper::view( 'fields/' . $args['type'], $args );
	}

	public function save_options() {
		if ( filter_input( INPUT_POST, 'option_page' ) != $this->page_name ) {
			return;
		} else {
			\check_admin_referer( $this->page_name . '-options' );
		}

		$is_ajax = 'xmlhttprequest' == strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' );

		$options = filter_input( INPUT_POST, $this->page_name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$options = $options ?? []; // must be an array
		$options = \apply_filters( $this->page_name . '_input', $options );
		\update_option( $this->option_name, $options );

		if ( $is_ajax ) {
			\wp_send_json_success( [
				'msg' => 'Settings Updated'
			] );
			exit;
		}

		// // allow core wp to save the data
		// foreach ( $options as $option_key => $option_value ) {
		// 	$_POST[$this->page_name . '_' . $option_key] = $option_value;
		// }
		// return;

		// Redirect back to the settings page that was submitted.
		$goback = \add_query_arg( 'settings-updated', 'true', wp_get_referer() );
		\wp_redirect( $goback );
		exit;
	}
}
