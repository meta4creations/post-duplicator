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
  private $encrypted_keys = [];
  private $encription_key_1 = '7Q@_DvLVTiHPEA';
  private $encription_key_2 = 'YgM2iCX-BtoBpJ';

  /**
   * Set up the instance
   */
  public static function instance() {
    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Settings ) ) {	
			self::$instance = new Settings;
      add_action( 'admin_menu', array( self::$instance, 'create_admin_pages' ) );
      //add_action( 'admin_menu', array( self::$instance, 'additional_pages' ) );
      add_action( 'admin_enqueue_scripts', array( self::$instance, 'enqueue_scripts' ) );
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
      self::$instance->id = esc_attr(  self::$instance->to_camel_case( $args['id'] ) );
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
   * Add an option key
   */
  public function add_option( $option ) {
    $options = self::$instance->get_options();
    $options[] = esc_attr( $option );
    self::$instance->options = array_unique( $options );
    return self::$instance->options;
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
   * Add an option key
   */
  public function add_section( $section ) {
    $sections = self::$instance->get_sections();
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

    $settings = self::$instance->get_settings();
    $settings[] = $setting;
    self::$instance->settings = $settings;

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

  /**
   * Return all values
   */
  public function get_values() {
    return self::$instance->values;
  }

  /**
   * Create admin pages
   */
  function create_admin_pages() {
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
  function enqueue_scripts() {

    // Only load script if on an admin page
    $current_screen = get_current_screen();
    $admin_pages = self::$instance->get_admin_pages();
    if ( ! isset( $admin_pages[$current_screen->id] ) ) {
      return false;
    }

    $admin_page = $admin_pages[$current_screen->id];
    $sections = self::$instance->get_sections( $admin_page );
    $settings = self::$instance->get_settings( $sections );

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
      'restUrl'        => esc_url_raw( rest_url( 'mtphrSettings/v1/' ) ),
      'settings'       => [],
      'fields'         => $settings,
      'field_sections' => $sections,
      'nonce'          => wp_create_nonce( 'wp_rest' )
    ) );
  }
}

/**
 * Get things started
 */
function MTPHR_POST_DUPLICATOR_SETTINGS() {
	return Settings::instance();
}
MTPHR_POST_DUPLICATOR_SETTINGS();



class SettingsX {

  private $id = 'mtphr';
  private $settings = [];
  private $values = [];
  private $encrypted_keys = [];
  private $encription_key_1 = '7Q@_DvLVTiHPEA';
  private $encription_key_2 = 'YgM2iCX-BtoBpJ';

  public function __construct( $id = '', $defaults = [], $encrypted_keys = [] ) {
    if ( '' != $id ) {
      $this->id = $id;
    }
    if ( ! empty( $defaults ) ) {
      $this->defaults = $defaults;
    }
    if ( ! empty( $encrypted_keys ) ) {
      $this->encrypted_keys = $encrypted_keys;
    }
  }

  /**
   * Return the defaults settings
   */
  public function get_defaults() {
    return apply_filters( "{$this->id}Settings/defaults", $this->$defaults );
  }

  /**
   * Return the encrypted setting keys
   */
  private function get_encrypted_keys() {
    return apply_filters( "{$this->id}Settings/encrypted_keys", $this->$encrypted_keys );
  }

  /**
   * Return the encrypted setting keys
   */
  public function set_encryption_keys( $key_1, $key_2 ) {
    $this->encription_key_1 = $key_1;
    $this->encription_key_2 = $key_2;
  }

  /**
   * Return the settings
   */
  public function get_settings( $id = false ) {
    if ( empty( $this->settings ) ) {
      $defaults = $this->get_defaults();
      $settings = get_option( "{$this->id}_settings" );
      $settings = wp_parse_args( $settings, $defaults );

      // Possibly decript settings
      $encrypted_keys = $this->get_encrypted_keys();
      if ( ! empty( $encrypted_keys ) ) {
        if ( is_array( $encrypted_keys ) && ! empty( $encrypted_keys ) ) {
          foreach ( $encrypted_keys as $key ) {
            if ( isset( $settings[$key] ) ) {
              $settings[$key] = $this->decrypt( $settings[$key] );
            }
          }
        }
      }
      $this->settings = $settings;
    }
    return $this->settings;
  }

  /**
   * Return an individual settings
   */
  public function get_setting( $id ) {
    $settings = $this->get_settings();
    if ( isset( $settings[$id] ) ) {
      return $settings[$id];
    }
  }

  /**
   * Update the settings
   */
  function update_settings( $key, $value = false ) {
    $defaults = $this->get_defaults();
    $settings = $this->get_settings();
    if ( is_array( $key ) ) {
      foreach ( $key as $k => $v ) {
        if ( ! array_key_exists( $k, $defaults ) ) {
          continue;
        }
        $settings[$k] = apply_filters( 'mtphrSettings/sanitize_setting', $v, $k );
      }  
    } else {
      if ( $value ) {
        if ( array_key_exists( $key, $defaults ) ) {
          $settings[$key] = apply_filters( 'mtphrSettings/sanitize_setting', $value, $key );
        }
      }
    }

    update_option( 'mtphr_emailcustomizer_settings', encrypt( $settings ) );

    $settings = wp_parse_args( $settings, $defaults );
    $settings = apply_filters( 'mtphrSettings/update_settings_after', $settings, $key, $value );

    if ( $key && ! is_array( $key ) ) {
      if ( isset( $settings[$key] ) ) {
        return $settings[$key];
      }
    } else {
      return $settings;
    }
  }

  private function sanitize_setting( $key, $value ) {
    $settings[$k] = apply_filters( 'mtphrSettings/sanitize_setting', $v, $k );
  }

  /**
   * Encrypt data
  */
  function encrypt( $string = '' ) {
    if ( is_array( $string ) ) {
      $string = json_encode( $string );
    }
    $key = hash( 'sha256', $this->encription_key_1 );
    $iv = substr( hash( 'sha256', $this->encription_key_2 ), 0, 16 );
    $output = base64_encode( openssl_encrypt( $string, "AES-256-CBC", $key, 0, $iv ) );
    return $output;
  }

  /**
   * Decrypt data
  */
  function decrypt( $string ) {
    if ( is_array( $string ) ) {
      return $string;
    }
    $key = hash( 'sha256', $this->encription_key_1 );
    $iv = substr( hash( 'sha256', $this->encription_key_2 ), 0, 16 );
    $output = openssl_decrypt( base64_decode( $string ), "AES-256-CBC", $key, 0, $iv );
    return json_decode( $output, true );
  }
}

