<?php
namespace Mtphr\PostDuplicator;

final class Settings {

  private static $instance;

  private $id = 'mtphr';
  private $textdomain = 'mtphr-settings';
  private $settings_dir = '';
  private $settings_url = '';

  private $admin_pages = [];
  private $options = [];
  private $sections = [];
  private $settings = [];
  private $values = [];
  private $default_values = [];
  private $sanitize_settings = [];
  private $encryption_settings = [];
  private $default_sanitizer = 'esc_attr';
  private $encryption_key_1 = '7Q@_DvLVTiHPEA';
  private $encryption_key_2 = 'YgM2iCX-BtoBpJ';

  /**
   * Set up the instance
   */
  public static function instance() {
    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Settings ) ) {	
			self::$instance = new Settings;
      add_action( 'admin_menu', array( self::$instance, 'create_admin_pages' ) );
      add_action( 'admin_enqueue_scripts', array( self::$instance, 'enqueue_scripts' ) );
      add_action( 'rest_api_init',array( self::$instance, 'register_routes' ) );
    }
    return self::$instance;
  }

  /**
   * Throw error on object clone
   */
  public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', self::$instance->textdomain ), '1.0.0' );
	}

  /**
   * Disable unserializing of the class
   */
  public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', self::$instance->textdomain ), '1.0.0' );
	}

  /**
   * Set a custom id for the settings
   */
  public function init( $args = [] ) {
    if ( isset( $args['id'] ) ) {
      self::$instance->id = esc_attr( str_replace( [' ', '-'], '', $args['id'] ) );
    }
    if ( isset( $args['textdomain'] ) ) {
      self::$instance->textdomain = esc_attr( $args['textdomain'] );
    }
    if ( isset( $args['settings_dir'] ) ) {
      self::$instance->settings_dir = esc_attr( trailingslashit( $args['settings_dir'] ) );
    }
    if ( isset( $args['settings_url'] ) ) {
      self::$instance->settings_url = esc_url( trailingslashit( $args['settings_url'] ) );
    }
  }

  /**
   * Force a string to camel case
   */
  private function to_camel_case( $string ) {
    // Replace non-alphanumeric characters with spaces
    $string = preg_replace( '/[^a-zA-Z0-9]+/', ' ', $string );

    // Uppercase first letter of each word, then remove spaces
    $string = str_replace( ' ', '', ucwords( strtolower( $string ) ) );

    // Lowercase first character for camelCase
    return lcfirst( $string );
  }

  /**
   * Get the custom id for the settings
   */
  public function get_id() {
    return self::$instance->id . 'Settings';
  }

  /**
   * Set a textdomain for the settings
   */
  public function set_textdomain( $textdomain ) {
    self::$instance->textdomain = $textdomain;
  }

  /**
   * Get the textdomain for the settings
   */
  public function get_textdomain() {
    return self::$instance->textdomain;
  }

  /**
   * Set the default sanitizer
   */
  public function set_default_sanitizer( $sanitizer ) {
    self::$instance->default_sanitizer = $sanitizer;
  }

  /**
   * Get the default sanitizer
   */
  public function get_default_sanitizer() {
    return self::$instance->default_sanitizer;
  }

  /**
   * Set the default sanitizer
   */
  public function set_default_encryption_keys( $keys = [] ) {
    if ( isset( $keys['key_1'] ) ) {
      self::$instance->encryption_key_1 = esc_attr( $keys['key_1'] );
    }
    if ( isset( $keys['key_2'] ) ) {
      self::$instance->encryption_key_1 = esc_attr( $keys['key_2'] );
    }
  }

  /**
   * Add an option key
   */
  public function add_admin_page( $admin_page ) {
    if ( ! is_array( $admin_page ) ) {
      return false;
    }
    if ( ! isset( $admin_page['page_title'] ) || ! isset( $admin_page['menu_title'] ) || ! isset( $admin_page['capability'] ) || ! isset( $admin_page['menu_slug'] ) ) {
      return false;
    }
    $admin_pages = self::$instance->get_admin_pages();

    // Check if top level and slug already exists
    if ( ! isset( $admin_page['parent_slug'] ) ) {
      if ( in_array( $admin_page['menu_slug'], array_column( array_filter( $admin_pages, fn( $page ) => ! isset( $page['parent_slug'] ) ), 'menu_slug' ) ) ) {
        return false;
      }

    // Check if submenu and same slug exists with same parent
    } else {
      $exists = array_filter( $admin_pages, function ( $page ) use ( $admin_page ) {
        return isset( $page['parent_slug'] ) 
          && $page['parent_slug'] === $admin_page['parent_slug'] 
          && $page['menu_slug'] === $admin_page['menu_slug'];
      } );
      if ( ! empty( $exists ) ) {
        return false;
      }
    }
    
    $admin_pages[] = $admin_page;
    self::$instance->admin_pages = $admin_pages;
    return self::$instance->admin_pages;
  }

  /**
   * Return available options keys
   */
  public function get_admin_pages( $admin_page = false ) {
    $admin_pages = self::$instance->admin_pages;
    if ( ! is_array( $admin_pages ) ) {
      return false;
    }
    if ( $admin_page ) {
      if ( isset( $admin_pages[$admin_page] ) ) {
        return $admin_pages[$admin_page];
      }
    } else {
      return $admin_pages;
    }
  }

  /**
   * Return available options keys
   */
  public function get_options( $option = false ) {
    $options = self::$instance->options;
    if ( ! is_array( $options ) ) {
      return false;
    }
    if ( $option ) {
      if ( isset( $options[$option] ) ) {
        return $options[$option];
      }
    } else {
      return $options;
    }
  }

  /**
   * Return option keys by sections
   */
  public function get_option_keys( $sections = false ) {
    if ( $sections ) {
      if ( is_array( $sections ) ) {
        if ( ! empty( $sections ) ) {
          $option_keys = [];
          foreach ( $sections as $section ) {
            if ( isset( $section['option'] ) ) {
              $option_keys[$section['option']] = $section['option'];
            }
          }
          return array_values( $option_keys );
        }
      } else {
        if ( isset( $sections['option'] ) ) {
          return $section['option'];
        }
      }
    }
  }

  /**
   * Add an option key
   */
  public function add_section( $section ) {
    $sections = self::$instance->get_sections();
    $options = self::$instance->get_options();

    $id = false;
    $label = false;
    $order = 10;

    if ( ! is_array( $section ) ) {
      return false;
    }
    if ( ! isset( $section['id'] ) || ! isset( $section['slug'] ) || ! isset( $section['menu_slug'] ) ) {
      return false;
    }
    if ( ! isset( $section['label'] ) ) {
      $section['label'] = ucfirst( $section['id'] );
    }
    if ( ! isset( $section['order'] ) ) {
      $section['order'] = $order;
    }

    // Check if top level and slug already exists
    if ( ! isset( $sections['parent_slug'] ) ) {
      if ( in_array( $section['menu_slug'], array_column( array_filter( $sections, fn( $s ) => ! isset( $s['parent_slug'] ) ), 'menu_slug' ) ) ) {
        return false;
      }

    // Check if submenu and same slug exists with same parent
    } else {
      $exists = array_filter( $sections, function ( $s ) use ( $section ) {
        return isset( $s['parent_slug'] ) 
          && $s['parent_slug'] === $section['parent_slug'] 
          && $s['menu_slug'] === $section['menu_slug'];
      } );
      if ( ! empty( $exists ) ) {
        return false;
      }
    }

    $sections[] = $section;
    self::$instance->sections = $sections;

    $section_option = $section['option'];

    // Add to the options array
    if ( ! in_array( $section_option, $options ) ) {
      $options[] = $section_option;
      self::$instance->options = $options;
    }

    return self::$instance->sections;
  }

  /**
   * Return available options keys
   */
  public function get_sections( $page = false ) {
    $sections = self::$instance->sections;
    if ( ! is_array( $sections ) ) {
      return false;
    }
    if ( $page ) {
      $menu_slug = $page['menu_slug'];
      $parent_slug = isset( $page['parent_slug'] ) ? $page['parent_slug'] : false;     
      $page_sections = [];
      if ( is_array( $sections ) && ! empty( $sections ) ) {
        foreach ( $sections as $section ) {
          if ( $menu_slug == $section['menu_slug'] ) {
            if ( $parent_slug ) {
              if ( isset( $section['parent_slug'] ) &&  $parent_slug = $section['parent_slug'] ) {
                $page_sections[] = $section;
              }
            } else {
              $page_sections[] = $section;
            }
          }
        }
      }
      return $page_sections;
    } else {
      return $sections;
    }
  }

  /**
   * Add a single setting
   */
  private function add_setting( $setting ) {
    if ( ! is_array( $setting ) ) {
      return false;
    }

    $sections = self::$instance->get_sections();
    $settings = self::$instance->get_settings();

    if ( empty( $sections ) ) {
      return false;
    }
    $default_section = $sections[0]['id'];
    $default_option = $sections[0]['option'];
    $default_order = 10;

    if ( ! isset( $setting['option'] ) ) {
      $setting['option'] = $default_option;
    }
    if ( ! isset( $setting['section'] ) ) {
      $setting['section'] = $default_section;
    }
    if ( ! isset( $setting['order'] ) ) {
      $setting['order'] = $default_order;
    }

    // Make sure the section exists
    if ( ! in_array( $setting['section'], array_column( $sections, 'id' ) ) ) {
      return false;
    }
 
    $settings[] = $setting;
    self::$instance->settings = $settings;

    self::$instance->process_setting_data( $setting );

    return $setting;
  }

  /**
   * Add settings
   */
  public function add_settings( $data ) {
    if ( ! is_array( $data ) ) {
      return false;
    }
    if ( ! isset( $data['section'] ) || ! isset( $data['fields'] ) ) {
      return false;
    }

    $updated_settings = [];

    $section = isset( $data['section'] ) ? $data['section'] : false;
    if ( is_array( $data['fields'] ) && ! empty( $data['fields'] ) ) {
      foreach ( $data['fields'] as $key => $field ) {
        $field['section'] = $data['section'];
        if ( $setting = self::$instance->add_setting( $field ) ) {
          $updated_settings[] = $setting;
        } 
      }
    }
    
    return $updated_settings;
  }

  /**
   * Return all settings
   */
  public function get_settings( $sections = false ) {
    $settings = self::$instance->settings;
    if ( $sections ) {
      $section_settings = [];
      if ( is_array( $sections ) && ! empty( $sections ) ) {
        foreach ( $sections as $section ) {
          if ( is_array( $settings ) && ! empty( $settings ) ) {
            foreach ( $settings as $setting ) {
              if ( $setting['section'] == $section['id'] ) {
                $section_settings[] = $setting;
              }
            }
          }
        }
      }
      return $section_settings;
    }
    return $settings;
  }

  private function process_setting_data( $setting, $option = false, $parents = [] ) {
    // Reference to instance storage
    $default_storage =& self::$instance->default_values;
    $sanitize_storage =& self::$instance->sanitize_settings;
    $encryption_storage =& self::$instance->encryption_settings;

    // Extract setting data
    $id = isset( $setting['id'] ) ? $setting['id'] : false;
    $default = isset( $setting['default'] ) ? $setting['default'] : null;
    $sanitize = isset( $setting['sanitize'] ) ? $setting['sanitize'] : self::$instance->get_default_sanitizer();
    $encrypt = isset( $setting['encrypt'] ) ? $setting['encrypt'] : false;
    $option = isset( $setting['option'] ) ? $setting['option'] : $option;

    if ( $option && $id !== false ) {
      // Ensure option storage exists
      if ( !isset( $default_storage[$option] ) ) {
        $default_storage[$option] = [];
      }
      if ( !isset( $sanitize_storage[$option] ) ) {
        $sanitize_storage[$option] = [];
      }
      if ( !isset( $encryption_storage[$option] ) ) {
        $encryption_storage[$option] = [];
      }

      // Reference correct location in storage
      $default_target =& $default_storage[$option];
      $sanitize_target =& $sanitize_storage[$option];
      $encryption_target =& $encryption_storage[$option];

      // Traverse parents to ensure proper nesting
      if ( !empty( $parents ) ) {
        foreach ( $parents as $parent ) {
          if ( !isset( $default_target[$parent] ) || !is_array( $default_target[$parent] ) ) {
            $default_target[$parent] = [];
          }
          if ( !isset( $sanitize_target[$parent] ) || !is_array( $sanitize_target[$parent] ) ) {
            $sanitize_target[$parent] = [];
          }
          if ( !isset( $encryption_target[$parent] ) || !is_array( $encryption_target[$parent] ) ) {
            $encryption_target[$parent] = [];
          }
          $default_target =& $default_target[$parent];
          $sanitize_target =& $sanitize_target[$parent];
          $encryption_target =& $encryption_target[$parent];
        }
      }

      // Assign values at the correct depth
      $default_target[$id] = $default;
      $sanitize_target[$id] = $sanitize;

      // Handle encryption settings
      if ( $encrypt !== false ) {
        if ( is_array( $encrypt ) ) {
          $encryption_target[$id] = [
            'key_1' => isset( $encrypt['key_1'] ) ? esc_attr( $encrypt['key_1'] ) : self::$instance->encryption_key_1,
            'key_2' => isset( $encrypt['key_2'] ) ? esc_attr( $encrypt['key_2'] ) : self::$instance->encryption_key_2,
          ];
        } elseif ( $encrypt === true || $encrypt === 1 ) {
          $encryption_target[$id] = [
            'key_1' => self::$instance->encryption_key_1,
            'key_2' => self::$instance->encryption_key_2,
          ];
        }
      }
    }

    // Process sub-fields if they exist
    if ( !empty( $setting['fields'] ) && is_array( $setting['fields'] ) ) {
      $new_parents = $parents;
      if ( $id !== false ) {
        $new_parents[] = $id;
      }
      foreach ( $setting['fields'] as $field ) {
        self::$instance->process_setting_data( $field, $option, $new_parents );
      }
    }
  }

  /**
   * Get default values
   */
  public function get_default_values( $options = false ) {
    $default_values = self::$instance->default_values;
    if ( $options ) {
      if ( is_array( $options ) ) {
        if ( ! empty( $options ) ) {
          $values = [];
          foreach ( $options as $option ) {
            $values[$option] = isset( $default_values[$option] ) ? $default_values[$option] : [];
          }
          return $values;
        }
      } else {
        return isset( $default_values[$options] ) ? $default_values[$options] : [];
      }
    }
    return $default_values;
  }

  /**
   * Get sanitize settings
   */
  public function get_sanitize_settings( $options = false ) {
    $sanitize_settings = self::$instance->sanitize_settings;
    if ( $options ) {
      if ( is_array( $options ) ) {
        if ( ! empty( $options ) ) {
          $settings = [];
          foreach ( $options as $option ) {
            $settings[$option] = isset( $sanitize_settings[$option] ) ? $sanitize_settings[$option] : [];
          }
          return $settings;
        }
      } else {
        return isset( $sanitize_settings[$options] ) ? $sanitize_settings[$options] : [];
      }
    }
    return $sanitize_settings;
  }

  /**
   * Get sanitize settings
   */
  public function get_encryption_settings( $options = false ) {
    $encryption_settings = self::$instance->encryption_settings;
    if ( $options ) {
      if ( is_array( $options ) ) {
        if ( ! empty( $options ) ) {
          $settings = [];
          foreach ( $options as $option ) {
            $settings[$option] = isset( $encryption_settings[$option] ) ? $encryption_settings[$option] : [];
          }
          return $settings;
        }
      } else {
        return isset( $encryption_settings[$options] ) ? $encryption_settings[$options] : [];
      }
    }
    return $encryption_settings;
  }

  /**
   * Return values
   */
  public function get_values( $options = false, $decrypt = true ) {
    $options = $options ? $options : self::$instance->get_options();

    if ( is_array( $options ) ) {
      if ( ! empty( $options ) ) {
        $values = [];
        foreach ( $options as $option ) {
          // 1. Grab stored settings
          $settings = get_option( $option, [] );

          // 2. Decrypt
          if ( $decrypt ) {
            $enc_settings = $this->get_encryption_settings( $option );
            $settings     = $this->decrypt_values( $settings, $enc_settings );
          }

          // 3. Inject default values
          $parsed_settings = self::$instance->inject_default_values( $settings, $option );
          
          // 4. Sanitize
          $values[$option] = self::$instance->sanitize_values( $parsed_settings, $option );
        }
        return $values;
      }
    } else {
      // $options is a single string
      $settings = get_option( $options, [] );

      // Decrypt
      if ( $decrypt ) {
        $enc_settings = $this->get_encryption_settings( $options );
        $settings     = $this->decrypt_values( $settings, $enc_settings );
      }

      // Inject defaults
      $parsed_settings = self::$instance->inject_default_values( $settings, $options );
      
      // Sanitize
      return self::$instance->sanitize_values( $parsed_settings, $options );
    }

    // If empty, just return an empty array or something suitable
    return [];
  }

  /**
   * Create admin pages
   */
  public function create_admin_pages() {
    $admin_pages = self::$instance->get_admin_pages();
    $updated_admin_pages = [];
    if ( is_array( $admin_pages ) && ! empty( $admin_pages ) ) {
      foreach ( $admin_pages as &$admin_page ) {
        if ( isset( $admin_page['parent_slug'] ) ) {
          $id = add_submenu_page(
            $admin_page['parent_slug'],
            $admin_page['page_title'],
            $admin_page['menu_title'],
            $admin_page['capability'],
            $admin_page['menu_slug'],
            function () {
              echo '<div class="wrap">';
                echo '<div id="mtphr-settings-app" namespace="' . self::$instance->get_id() . '">Test Page</div>'; // React App will be injected here
              echo '</div>';
            },
            isset( $admin_page['position'] ) ? $admin_page['position'] : null,
          );
          $updated_admin_pages[$id] = $admin_page;
        } else {
          $id = add_menu_page(
            $admin_page['page_title'],
            $admin_page['menu_title'],
            $admin_page['capability'],
            $admin_page['menu_slug'],
            function () {
              echo '<div class="wrap">';
                echo '<div id="mtphr-settings-app" namespace="' . self::$instance->get_id() . '">Test Page</div>'; // React App will be injected here
              echo '</div>';
            },
            isset( $admin_page['icon'] ) ? $admin_page['icon'] : null,
            isset( $admin_page['position'] ) ? $admin_page['position'] : null,
          );
          $updated_admin_pages[$id] = $admin_page;
        }  
      }
    }
    self::$instance->admin_pages = $updated_admin_pages;
  }

  /**
   * Enqueue admin scripts
   */
  public function enqueue_scripts() {

    // Only load script if on an admin page
    $current_screen = get_current_screen();
    $admin_pages = self::$instance->get_admin_pages();
    if ( ! isset( $admin_pages[$current_screen->id] ) ) {
      return false;
    }

    $admin_page = $admin_pages[$current_screen->id];
    $sections = self::$instance->get_sections( $admin_page );
    $settings = self::$instance->get_settings( $sections );
    $options = self::$instance->get_option_keys( $sections );
    $values = self::$instance->get_values( $options );

    $asset_file = include( self::$instance->settings_dir . 'assets/build/mtphrSettings.asset.php' );
    wp_enqueue_style(
      self::$instance->get_id(),
      self::$instance->settings_url . 'assets/build/mtphrSettings.css',
      ['wp-components'],
      $asset_file['version']
    );
    wp_enqueue_script(
      self::$instance->get_id(),
      self::$instance->settings_url . 'assets/build/mtphrSettings.js',
      array_unique( array_merge( $asset_file['dependencies'], ['wp-element'] ) ),
      $asset_file['version'],
      true
    ); 
    wp_localize_script( self::$instance->get_id(), self::$instance->get_id() . 'Vars', array(
      'siteUrl'        => site_url(),
      'restUrl'        => esc_url_raw( rest_url( self::$instance->get_id() . '\/v1/' ) ),
      'values'         => $values,
      'fields'         => $settings,
      'field_sections' => $sections,
      'nonce'          => wp_create_nonce( 'wp_rest' )
    ) );
  }

  /**
   * Register rest routes
   */
  public function register_routes() {
    register_rest_route( self::$instance->get_id() . '/v1', 'settings', array(
      'methods' 	=> 'POST',
      'permission_callback' => function () {
        return current_user_can('manage_options');
      },
      'callback' => function( $request ) {
        $args = $request->get_params();
        $data = $request->get_json_params();

        $value_keys = isset( $data['valueKeys'] ) ? $data['valueKeys'] : [];
        $values = isset( $data['values'] ) ? $data['values'] : [];

        if ( is_array( $value_keys ) && ! empty( $value_keys ) ) {
          foreach ( $value_keys as $option => $keys ) {
            $values_to_update = array_intersect_key( $values[$option], array_flip( $keys ) );
            $updated_values = self::$instance->update_values( $option, $values_to_update );
            $values[$option] = $updated_values;
          }
        }
        
        //self::$instance->update_values( $values );
        return rest_ensure_response( $values , 200);
      },
    ) );
  }

  /**
   * Inject default values into $values if they do not exist.
   */
  private function inject_default_values( $values, $option ) { 
    $default_values = MTPHR_POST_DUPLICATOR_SETTINGS()->get_default_values( $option );
    return $this->recursive_inject_default_values( $values, $default_values );
  }

  /**
  * Recursively inject default values into the values array.
  */
  private function recursive_inject_default_values( $values, $default_values ) {
    if ( !is_array( $default_values ) || empty( $default_values ) ) {
      return $values; // No defaults to inject
    }

    foreach ( $default_values as $key => $default_value ) {
      if ( is_array( $default_value ) ) {
        // Ensure $values[$key] is an array before merging
        if ( !isset( $values[$key] ) || !is_array( $values[$key] ) ) {
          $values[$key] = [];
        }

        // Recursively process nested arrays
        $values[$key] = $this->recursive_inject_default_values( 
          $values[$key], 
          $default_value 
        );
      } else {
        // Only inject default if key does not exist in $values
        if ( !array_key_exists( $key, $values ) ) {
          $values[$key] = $default_value;
        }
      }
    }
    return $values;
  }

  /**
   * Update the settings
   */
  public function update_values( $option, $values = [] ) {
    $sanitize_settings = MTPHR_POST_DUPLICATOR_SETTINGS()->get_sanitize_settings( $option );
    $option_values     = get_option( $option, [] );

    // 1) Recursively merge and sanitize
    $updated_values = $this->recursive_update_values(
      $option_values,
      $values,
      $sanitize_settings,
      $option
    );

    // 2) Retrieve the encryption settings for this option
    $encryption_settings = $this->get_encryption_settings( $option );
    
    // 3) Recursively encrypt the updated values
    $updated_values = $this->encrypt_values( $updated_values, $encryption_settings );

    // 4) Finally save the updated (and possibly encrypted) array
    update_option( $option, $updated_values );

    return $updated_values;
  }

  /**
   * Recursively update and sanitize values.
   */
  private function recursive_update_values( $existing_values, $new_values, $sanitize_settings, $option ) {
    if ( ! is_array( $new_values ) || empty( $new_values ) ) {
      return $existing_values;
    }

    foreach ( $new_values as $key => $value ) {
      if ( is_array( $value ) ) {
        // Recursively update nested arrays, keeping existing structure
        $existing_values[$key] = $this->recursive_update_values(
          isset( $existing_values[$key] ) ? $existing_values[$key] : [],
          $value,
          isset( $sanitize_settings[$key] ) ? $sanitize_settings[$key] : [],
          $option
        );
      } else {
        // Get the sanitizer for this specific key, or fallback to default
        $sanitizer = isset( $sanitize_settings[$key] ) 
          ? $sanitize_settings[$key] 
          : self::$instance->get_default_sanitizer();

        $existing_values[$key] = self::$instance->sanitize_value( $value, $sanitizer, $key, $option );
      }
    }

    return $existing_values;
  }

  /**
   * Sanitize a value
   */
  private function sanitize_values( $values, $option ) { 
    $sanitize_settings = MTPHR_POST_DUPLICATOR_SETTINGS()->get_sanitize_settings( $option );
    return $this->recursive_sanitize_values( $values, $sanitize_settings, $option );
  }

  /**
   * Recursively sanitize values based on the sanitize settings structure.
   */
  private function recursive_sanitize_values( $values, $sanitize_settings, $option ) {
    $sanitized_values = [];
    if ( is_array( $values ) && ! empty( $values ) ) {
      foreach ( $values as $key => $value ) {
        if ( is_array( $value ) ) {
          // Recursively sanitize nested arrays using the same key structure
          $sanitized_values[$key] = $this->recursive_sanitize_values(
            $value, 
            isset( $sanitize_settings[$key] ) ? $sanitize_settings[$key] : [],
            $option
          );
        } else {
          // Retrieve the appropriate sanitizer or fallback to the default
          $sanitizer = isset( $sanitize_settings[$key] ) 
            ? $sanitize_settings[$key] 
            : self::$instance->get_default_sanitizer();

          $sanitized_values[$key] = self::$instance->sanitize_value( $value, $sanitizer, $key, $option );
        }
      }
    }
    return $sanitized_values;
  }

  /**
   * Sanitize a value
   */
  private function sanitize_value( $value, $sanitizer, $key, $option ) { 
    switch( $sanitizer ) {
      case 'esc_attr':
        return esc_attr( $value );
      case 'sanitize_text_field':
        return sanitize_text_field( $value );
      case 'wp_kses_post':
        return wp_kses_post( $value );
      case 'intval':
        return intval( $value );
      case 'floatval':
        return floatval( $value );
      case 'boolval':
        return boolval( $value );
      default:
        if ( function_exists( $sanitizer ) ) {
          return $sanitizer( $value, $key, $option );
        } else {
          $default = self::$instance->get_default_sanitizer();
          return $default( $value );
        }
        break;
    }
  }

  /**
   * Recursively encrypt values based on encryption settings.
   * 
   * - If `['key_1' => X, 'key_2' => Y]` is found at `$encryption_settings[$key]`, 
   *   encrypt the entire `$values[$key]` with those keys and **do not** recurse deeper.
   * - Otherwise, if `$encryption_settings[$key]` is an array but lacks `key_1`/`key_2`, 
   *   we assume it's a nested structure and continue recursing.
   */
  private function encrypt_values( $values, $encryption_settings ) {
    // If there is no encryption to apply or $values isn't an array, bail early.
    if ( ! is_array( $encryption_settings ) || ! is_array( $values ) ) {
      return $values;
    }

    foreach ( $encryption_settings as $key => $enc_info ) {
      // Skip if $values does not have this key at all.
      if ( ! array_key_exists( $key, $values ) ) {
        continue;
      }

      // Check if we have an actual encryption array with 'key_1'/'key_2'.
      if ( is_array( $enc_info ) && isset( $enc_info['key_1'] ) && isset( $enc_info['key_2'] ) ) {
        // Encrypt this entire branch with the custom keys and skip children.
        $values[$key] = $this->encrypt( $values[$key], $enc_info['key_1'], $enc_info['key_2'] );
      } 
      // If $enc_info is an array but no direct 'key_1'/'key_2', 
      // it's likely nested encryption settings. Recurse deeper if $values[$key] is array.
      elseif ( is_array( $enc_info ) ) {
        if ( is_array( $values[$key] ) ) {
          $values[$key] = $this->encrypt_values( $values[$key], $enc_info );
        }
      }
    }

    return $values;
  }

  /**
   * Encrypt data
  */
  private function encrypt( $string = '', $custom_key_1 = null, $custom_key_2 = null ) {
    // Convert arrays to JSON so we can encrypt them as strings.
    if ( is_array( $string ) ) {
      $string = json_encode( $string );
    }

    // Use custom keys if provided, otherwise fall back to defaults.
    $key_1 = $custom_key_1 ?: $this->encryption_key_1;
    $key_2 = $custom_key_2 ?: $this->encryption_key_2;

    $key = hash( 'sha256', $key_1 );
    $iv  = substr( hash( 'sha256', $key_2 ), 0, 16 );

    return base64_encode(
      openssl_encrypt( $string, 'AES-256-CBC', $key, 0, $iv )
    );
  }

  /**
   * Recursively decrypt values based on the same encryption settings structure.
   *
   * - If we find `['key_1' => ..., 'key_2' => ...]` at `$encryption_settings[$key]`,
   *   we decrypt the entire `$values[$key]` with those keys, and do NOT recurse deeper.
   * - If `$enc_info` is an array but lacks `key_1`/`key_2`, we assume it's nested settings.
   *   We keep recursing into `$values[$key]` if it's an array.
   */
  private function decrypt_values( $values, $encryption_settings ) {
    if ( ! is_array( $values ) || ! is_array( $encryption_settings ) ) {
      return $values;
    }

    foreach ( $encryption_settings as $key => $enc_info ) {
      // Skip if $values does not have this key at all
      if ( ! array_key_exists( $key, $values ) ) {
        continue;
      }

      // If this level has explicit 'key_1' and 'key_2', decrypt the entire branch
      if ( is_array( $enc_info ) && isset( $enc_info['key_1'] ) && isset( $enc_info['key_2'] ) ) {
        $values[$key] = $this->decrypt( $values[$key], $enc_info['key_1'], $enc_info['key_2'] );
      }
      // If $enc_info is an array but no direct 'key_1'/'key_2',
      // it might be nested encryption settings. Recurse deeper if $values[$key] is array
      elseif ( is_array( $enc_info ) ) {
        if ( is_array( $values[$key] ) ) {
          $values[$key] = $this->decrypt_values( $values[$key], $enc_info );
        }
      }
    }

    return $values;
  }

  /**
   * Decrypt data with optional custom keys.
   */
  private function decrypt( $string, $custom_key_1 = null, $custom_key_2 = null ) {
    // If already an array, it might have been double-processed or not encrypted at all
    if ( is_array( $string ) ) {
      return $string;
    }

    // Use custom keys if provided; otherwise, use defaults
    $key_1 = $custom_key_1 ?: $this->encryption_key_1;
    $key_2 = $custom_key_2 ?: $this->encryption_key_2;

    $key = hash( 'sha256', $key_1 );
    $iv  = substr( hash( 'sha256', $key_2 ), 0, 16 );

    $output = openssl_decrypt(
      base64_decode( $string ), 
      'AES-256-CBC', 
      $key, 
      0, 
      $iv
    );

    // Attempt to JSON-decode the result to restore arrays if originally encrypted from an array
    $decoded = json_decode( $output, true );
    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $output;
  }
}

/**
 * Get things started
 */
function MTPHR_POST_DUPLICATOR_SETTINGS() {
	return Settings::instance();
}
MTPHR_POST_DUPLICATOR_SETTINGS();