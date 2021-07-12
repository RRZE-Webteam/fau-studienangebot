<?php

/*
Plugin Name:     FAU-Studienangebot
Plugin URI:      https://github.com/RRZE-Webteam/fau-studienangebot
Description:     Verwaltung des Studienangebots der FAU.
Version:         2.5.2
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v2
License URI:     http://www.gnu.org/licenses/gpl-2.0.html
Domain Path:     /languages
Text Domain:     cms-workflow
*/

add_action('plugins_loaded', array('FAU_Studienangebot', 'instance'));

register_activation_hook(__FILE__, array('FAU_Studienangebot', 'activation'));
register_deactivation_hook(__FILE__, array('FAU_Studienangebot', 'deactivation'));

class FAU_Studienangebot {

    const version = '2.5.2';
    const option_name = '_fau_studienangebot';
    const version_option_name = '_fau_studienangebot_version';
    const post_type = 'studienangebot';
    const capability_type = 'studienangebot';
    const author_role = 'studienangebot_author';
    const editor_role = 'studienangebot_editor';

    public static $taxonomies = array(
        'studiengang',
        'semester',
        'abschluss',
        'faechergruppe',
        'fakultaet',
        'studienort',
        'saattribut',
        'sazvs',
        'satag'
    );

    public static $fauthemes = array(
        'FAU',
        'FAU-Einrichtungen',
        'FAU-Einrichtungen-BETA',
        'FAU-Philfak',
        'FAU-Natfak',
        'FAU-Medfak',
        'FAU-RWFak',
        'FAU-Techfak'
    );

    protected static $instance = null;

    const textdomain = 'studienangebot';
    const php_version = '7.3'; // Minimal erforderliche PHP-Version
    const wp_version = '5.5'; // Minimal erforderliche WordPress-Version

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {

        define('SA_TEXTDOMAIN', self::textdomain);

        // Sprachdateien werden eingebunden.
        self::load_textdomain();

        // Aktualisierung des Plugins (ggf).
        self::update_version();

        // register post type
        add_action('init', array(__CLASS__, 'register_post_type_studienangebot'));

        // register taxonomies
        add_action('init', array($this, 'register_taxonomy_studiengang'));
        add_action('init', array($this, 'register_taxonomy_abschluss'));
        add_action('init', array($this, 'register_taxonomy_semester'));
        add_action('init', array($this, 'register_taxonomy_studienort'));
        add_action('init', array($this, 'register_taxonomy_faechergruppe'));
        add_action('init', array($this, 'register_taxonomy_fakultaet'));
        add_action('init', array($this, 'register_taxonomy_saattribut'));
        add_action('init', array($this, 'register_taxonomy_sazvs'));
        add_action('init', array($this, 'register_taxonomy_saconstant'));
        add_action('init', array($this, 'register_taxonomy_satag'));

        // register the options
        //add_action('admin_init', array($this, 'settings_init'));
        //add_action('admin_init', array($this, 'settings_save'), 100);

        // add settings submenu page
        //add_action('admin_menu', array($this, 'settings_submenu_page'));

        // rename "featured image"
        add_action('admin_head-post-new.php', array($this, 'change_thumbnail_html'));
        add_action('admin_head-post.php', array($this, 'change_thumbnail_html'));

        // add sidebar
        add_action('widgets_init', array($this, 'register_sidebar'));


	add_action('init', array($this, 'register_script'));
	// add_action('wp_footer', array($this, 'print_script'));


        // add term meta field
        add_action('abschluss_add_form_fields', array($this, 'abschluss_add_new_meta_field'), 10, 2);
        add_action('abschluss_edit_form_fields', array($this, 'abschluss_edit_meta_field'), 10, 2);
        add_action('edited_abschluss', array($this, 'save_abschluss_custom_meta'));
        add_action('create_abschluss', array($this, 'save_abschluss_custom_meta'));
        add_action('delete_abschluss', array($this, 'delete_abschluss_custom_meta'));

        add_action('sazvs_add_form_fields', array($this, 'sazvs_add_new_meta_field'), 10, 2);
        add_action('sazvs_edit_form_fields', array($this, 'sazvs_edit_meta_field'), 10, 2);
        add_action('edited_sazvs', array($this, 'save_sazvs_custom_meta'));
        add_action('create_sazvs', array($this, 'save_sazvs_custom_meta'));
        add_action('delete_sazvs', array($this, 'delete_sazvs_custom_meta'));

        add_action('saconstant_add_form_fields', array($this, 'saconstant_add_new_meta_field'), 10, 2);
        add_action('saconstant_edit_form_fields', array($this, 'saconstant_edit_meta_field'), 10, 2);
        add_action('edited_saconstant', array($this, 'save_saconstant_custom_meta'));
        add_action('create_saconstant', array($this, 'save_saconstant_custom_meta'));
        add_action('delete_saconstant', array($this, 'delete_saconstant_custom_meta'));

        // custom term columns
        add_filter('manage_edit-studienangebot_columns', array($this, 'term_columns'));
        add_filter('manage_edit-abschluss_columns', array($this, 'term_columns'));
        add_filter('manage_edit-faechergruppe_columns', array($this, 'term_columns'));
        add_filter('manage_edit-fakultaet_columns', array($this, 'term_columns'));
        add_filter('manage_edit-saattribut_columns', array($this, 'term_columns'));
        add_filter('manage_edit-sazvs_columns', array($this, 'term_columns'));
        add_filter('manage_edit-saconstant_columns', array($this, 'term_columns'));
        add_filter('manage_edit-semester_columns', array($this, 'term_columns'));
        add_filter('manage_edit-studiengang_columns', array($this, 'term_columns'));
        add_filter('manage_edit-studienort_columns', array($this, 'term_columns'));

        add_filter('manage_abschluss_custom_column', array($this, 'abschluss_custom_column'), 15, 3);
        add_filter('manage_edit-abschluss_columns', array($this, 'abschluss_columns'));
        add_filter('manage_sazvs_custom_column', array($this, 'sazvs_custom_column'), 15, 3);
        add_filter('manage_edit-sazvs_columns', array($this, 'sazvs_columns'));
        add_filter('manage_saconstant_custom_column', array($this, 'saconstant_custom_column'), 15, 3);
        add_filter('manage_edit-saconstant_columns', array($this, 'saconstant_columns'));

        // hide term fields
        add_action('admin_head', array($this, 'hide_term_fields'));

        // add rewrite endpoint
        add_action('init', array(__CLASS__, 'add_rewrite_endpoint'));
        add_action('permalink_structure_changed', array(__CLASS__, 'add_rewrite_endpoint'));

        add_filter('the_content', array($this, 'the_content'));

        // initialize Meta Boxes
        add_action('add_meta_boxes', array($this, 'set_meta_boxes'));
        add_action('init', array($this, 'initialize_meta_boxes'), 999);

        // include Meta Boxes
        include_once(plugin_dir_path(__FILE__) . 'includes/metaboxes.php');

        // include Shortcodes
        include_once(plugin_dir_path(__FILE__) . 'includes/shortcodes/studienangebot.php');
        include_once(plugin_dir_path(__FILE__) . 'includes/shortcodes/studiengaenge.php');

        // remove quick edit
        add_filter('post_row_actions', array(__CLASS__, 'remove_quick_edit'), 10, 2);

        // Export All Posts To CSV
        add_action( 'manage_posts_extra_tablenav', array(__CLASS__, 'export_to_csv_button'), 20);
        add_action( 'admin_init', array(__CLASS__, 'export_to_csv') );
    }

    public static function remove_quick_edit($actions, $post) {
        if ($post->post_type == 'studienangebot') {
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }

    public static function sync_roles() {
        $options = (object) self::get_options();

        $administrator_role = get_role('administrator');

        foreach($options->author_caps as $cap => $grant) {
            $administrator_role->add_cap($cap, boolval($grant));
        }

        $author_role = get_role('author');

        $capabilities = array_merge($author_role->capabilities, $options->author_caps);

        add_role(self::author_role, __('Studienangebotautor', self::textdomain), $capabilities);
    }

    public static function add_rewrite_endpoint() {
        add_rewrite_endpoint('studiengang', EP_ROOT | EP_PAGES);
    }

    /*
     * Überprüft die minimal erforderliche PHP- u. WP-Version.
     * @return void
     */
    private static function system_requirements() {
        $error = '';

        if (version_compare(PHP_VERSION, self::php_version, '<')) {
            $error = sprintf(__('Ihre PHP-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die PHP-Version %s.', self::textdomain), PHP_VERSION, self::php_version);
        }

        if (version_compare($GLOBALS['wp_version'], self::wp_version, '<')) {
            $error = sprintf(__('Ihre Wordpress-Version %s ist veraltet. Bitte aktualisieren Sie mindestens auf die Wordpress-Version %s.', self::textdomain), $GLOBALS['wp_version'], self::wp_version);
        }

        // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
        if (!empty($error)) {
            deactivate_plugins(plugin_basename(__FILE__), false, true);
            wp_die($error);
        }
    }

    /*
     * Aktualisierung des Plugins
     * @return void
     */
    private static function update_version() {
        $version = get_option(self::version_option_name, '0');

        if (version_compare($version, self::version, '<')) {
            // Wird durchgeführt wenn das Plugin aktualisiert muss.
        }

        update_option(self::version_option_name, self::version);
    }

    private static function default_options() {
        $options = array(
            'sa_page' => -1,
            'author_caps' => array(
                "edit_" . self::capability_type => true,
                "read_" . self::capability_type => true,
                "delete_" . self::capability_type => true,
                "edit_" . self::capability_type . "s" => true,
                "edit_others_" . self::capability_type . "s" => true,
                "publish_" . self::capability_type . "s" => true,
                "read_private_" . self::capability_type . "s" => true,
                "delete_" . self::capability_type . "s" => true,
                "delete_private_" . self::capability_type . "s" => true,
                "delete_published_" . self::capability_type . "s" => true,
                "delete_others_" . self::capability_type . "s" => true,
                "edit_private_" . self::capability_type . "s" => true,
                "edit_published_" . self::capability_type . "s" => true,
            ),
        );

        return $options;
    }

    private static function get_options() {
        $defaults = self::default_options();
        $options = (array) get_option(self::option_name);
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);
        return $options;
    }

    // Einbindung der Sprachdateien.
    private static function load_textdomain() {
        load_plugin_textdomain(self::textdomain, false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
    }

    public static function activation() {
        // Sprachdateien werden eingebunden.
        self::load_textdomain();

        // Überprüft die minimal erforderliche PHP- u. WP-Version.
        self::system_requirements();

        // Aktualisierung des Plugins (ggf).
        self::update_version();

        self::sync_roles();

        self::add_rewrite_endpoint();
        self::register_post_type_studienangebot();
        flush_rewrite_rules();
    }

    public static function deactivation() {
        $administrator_role = get_role('administrator');

        $options = (object) self::get_options();
        foreach($options->author_caps as $cap) {
            $administrator_role->remove_cap($cap);
        }

        remove_role(self::author_role);

        delete_option(self::option_name);

        flush_rewrite_rules();
    }

    public static function register_post_type_studienangebot() {
        $supports = array('title', 'author', 'thumbnail', 'revisions');

        $args = array(
            'labels' => array(
                'name' => __('Studienangebot', self::textdomain),
                'singular_name' => __('Studienangebot', self::textdomain),
                'menu_name' => __('Studienangebot', self::textdomain),
                'all_items' => 'Studienangebot',
                'add_new' => __('Erstellen', self::textdomain),
                'add_new_item' => __('Neues Studienangebot erstellen', self::textdomain),
                'edit_item' => __('Studienangebot bearbeiten', self::textdomain),
                'new_item' => __('Neues Studienangebot', self::textdomain),
                'view_item' => __('Studienangebot ansehen', self::textdomain),
                'search_items' => __('Studienangebot suchen', self::textdomain),
                'not_found' => __('Kein Studienangebot gefunden.', self::textdomain),
                'not_found_in_trash' => __('Kein Studienangebot im Papierkorb gefunden.', self::textdomain),
            ),
            'description' => 'Studienangebot',
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'exclude_from_search' => false,
            'show_in_nav_menus' => false,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'capability_type' => self::capability_type,
            'capabilities' => array(
                'edit_post' => "edit_" . self::capability_type . "",
                'read_post' => "read_" . self::capability_type . "",
                'delete_post' => "delete_" . self::capability_type . "",
                'edit_posts' => "edit_" . self::capability_type . "s",
                'edit_others_posts' => "edit_others_" . self::capability_type . "s",
                'publish_posts' => "publish_" . self::capability_type . "s",
                'read_private_posts' => "read_private_" . self::capability_type . "s",
                'delete_posts' => "delete_" . self::capability_type . "s",
                'delete_private_posts' => "delete_private_" . self::capability_type . "s",
                'delete_published_posts' => "delete_published_" . self::capability_type . "s",
                'delete_others_posts' => "delete_others_" . self::capability_type . "s",
                'edit_private_posts' => "edit_private_" . self::capability_type . "s",
                'edit_published_posts' => "edit_published_" . self::capability_type . "s",
            ),
            'map_meta_cap' => true,
            'supports' => $supports,
            'taxonomies' => array(implode(',', self::$taxonomies)),
            'has_archive' => false,
            'rewrite' => array('slug' => 'studiengang', 'with_front' => false),
            'query_var' => true,
            'can_export' => true,
        );

        register_post_type(self::post_type, $args);
    }

    public function register_taxonomy_studiengang() {
        register_taxonomy('studiengang', array(self::post_type), array(
            'label' => __('Studiengänge', self::textdomain),
            'labels' => array(
                'name' => __('Studiengänge', self::textdomain),
                'singular_name' => __('Studiengang', self::textdomain),
                'menu_name' => __('Studiengänge', self::textdomain),
                'all_items' => __('Alle Studiengänge', self::textdomain),
                'edit_item' => __('Studiengang bearbeiten', self::textdomain),
                'update_item' => __('Studiengänge aktualisieren', self::textdomain),
                'add_new_item' => __('Neuen Studiengang hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Studiengänge suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => true,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'studiengang',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_abschluss() {
        register_taxonomy('abschluss', array(self::post_type), array(
            'label' => __('Abschlüsse', self::textdomain),
            'labels' => array(
                'name' => __('Abschlüsse', self::textdomain),
                'singular_name' => __('Abschluss', self::textdomain),
                'menu_name' => __('Abschlüsse', self::textdomain),
                'all_items' => __('Alle Abschlüsse', self::textdomain),
                'edit_item' => __('Abschluss bearbeiten', self::textdomain),
                'update_item' => __('Abschlüsse aktualisieren', self::textdomain),
                'add_new_item' => __('Neuen Abschluss hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Abschlüsse suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => true,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'abschluss',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_semester() {
        register_taxonomy('semester', array(self::post_type), array(
            'label' => __('Semester', self::textdomain),
            'labels' => array(
                'name' => __('Semester', self::textdomain),
                'singular_name' => __('Semester', self::textdomain),
                'menu_name' => __('Semester', self::textdomain),
                'all_items' => __('Alle Semester', self::textdomain),
                'edit_item' => __('Semester bearbeiten', self::textdomain),
                'update_item' => __('Semester aktualisieren', self::textdomain),
                'add_new_item' => __('Neuen Semester hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Semester suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => true,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'semester',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_studienort() {
        register_taxonomy('studienort', array(self::post_type), array(
            'label' => __('Orte', self::textdomain),
            'labels' => array(
                'name' => __('Orte', self::textdomain),
                'singular_name' => __('Ort', self::textdomain),
                'menu_name' => __('Orte', self::textdomain),
                'all_items' => __('Alle Orte', self::textdomain),
                'edit_item' => __('Ort bearbeiten', self::textdomain),
                'update_item' => __('Ort aktualisieren', self::textdomain),
                'add_new_item' => __('Neuen Ort hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Ort suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => true,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'studienort',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_faechergruppe() {
        register_taxonomy('faechergruppe', array(self::post_type), array(
            'label' => __('Fächergruppen', self::textdomain),
            'labels' => array(
                'name' => __('Fächergruppen', self::textdomain),
                'singular_name' => __('Fächergruppe', self::textdomain),
                'menu_name' => __('Fächergruppen', self::textdomain),
                'all_items' => __('Alle Fächergruppen', self::textdomain),
                'edit_item' => __('Fächergruppe bearbeiten', self::textdomain),
                'update_item' => __('Fächergruppe aktualisieren', self::textdomain),
                'add_new_item' => __('Neue Fächergruppe hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Fächergruppe suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => false,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'faechergruppe',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_fakultaet() {
        register_taxonomy('fakultaet', array(self::post_type), array(
            'label' => __('Fakultäten', self::textdomain),
            'labels' => array(
                'name' => __('Fakultäten', self::textdomain),
                'singular_name' => __('Fakultät', self::textdomain),
                'menu_name' => __('Fakultäten', self::textdomain),
                'all_items' => __('Alle Fakultäten', self::textdomain),
                'edit_item' => __('Fakultät bearbeiten', self::textdomain),
                'update_item' => __('Fakultät aktualisieren', self::textdomain),
                'add_new_item' => __('Neue Fakultät hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Fakultät suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => false,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'fakultaet',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_saattribut() {
        register_taxonomy('saattribut', array(self::post_type), array(
            'label' => __('Attribut', self::textdomain),
            'labels' => array(
                'name' => __('Attribute', self::textdomain),
                'singular_name' => __('Attribut', self::textdomain),
                'menu_name' => __('Attribute', self::textdomain),
                'all_items' => __('Alle Attribute', self::textdomain),
                'edit_item' => __('Attribut bearbeiten', self::textdomain),
                'update_item' => __('Attribut aktualisieren', self::textdomain),
                'add_new_item' => __('Neue Attribut hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Attribut suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => false,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'saattribut',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_sazvs() {
        register_taxonomy('sazvs', array(self::post_type), array(
            'label' => __('ZVS', self::textdomain),
            'labels' => array(
                'name' => __('Zugangsvoraussetzungen', self::textdomain),
                'singular_name' => __('Zugangsvoraussetzung', self::textdomain),
                'menu_name' => __('ZVS', self::textdomain),
                'all_items' => __('Alle Zugangsvoraussetzungen', self::textdomain),
                'edit_item' => __('Zugangsvoraussetzung bearbeiten', self::textdomain),
                'update_item' => __('Zugangsvoraussetzung aktualisieren', self::textdomain),
                'add_new_item' => __('Neue Zugangsvoraussetzung hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Zugangsvoraussetzung suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => false,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'sazvs',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_saconstant() {
        register_taxonomy('saconstant', array(self::post_type), array(
            'label' => __('Konstante', self::textdomain),
            'labels' => array(
                'name' => __('Konstanten', self::textdomain),
                'singular_name' => __('Konstante', self::textdomain),
                'menu_name' => __('Konstanten', self::textdomain),
                'all_items' => __('Alle Konstante', self::textdomain),
                'edit_item' => __('Konstante bearbeiten', self::textdomain),
                'update_item' => __('Konstante aktualisieren', self::textdomain),
                'add_new_item' => __('Neue Konstante hinzufügen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'search_items' => __('Konstante suchen', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'show_admin_column' => false,
            'hierarchical' => true,
            'update_count_callback' => '',
            'query_var' => 'saconstant',
            'rewrite' => false,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function register_taxonomy_satag() {
        register_taxonomy('satag', array(self::post_type), array(
            'label' => __('Schlagworte', self::textdomain),
            'labels' => array(
                'name' => __('Schlagworte', self::textdomain),
                'singular_name' => __('Schlagwort', self::textdomain),
                'search_items' => __('Schlagwörter suchen', self::textdomain),
                'popular_items' => __('Beliebte Schlagwörter', self::textdomain),
                'all_items' => __('Alle Schlagwörtern', self::textdomain),
                'parent_item' => null,
                'parent_item_colon' => null,
                'edit_item' => __('Schlagwort bearbeiten', self::textdomain),
                'update_item' => __('Schlagwort aktualisieren', self::textdomain),
                'add_new_item' => __('Neues Schlagwort erstellen', self::textdomain),
                'new_item_name' => __('Name', self::textdomain),
                'separate_items_with_commas' => __('Trenne Schlagwörter durch Kommas', self::textdomain),
                'add_or_remove_items' => __('Hinzu', self::textdomain),
                'choose_from_most_used' => __('Wähle aus den häufig genutzten Schlagwörtern', self::textdomain),
                'menu_name' => __('Schlagworte', self::textdomain),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => true,
            'show_admin_column' => false,
            'hierarchical' => false,
            'query_var' => 'satag',
            'rewrite' => true,
            'capabilities' => array(
                'manage_terms' => "edit_" . self::capability_type . "s",
                'edit_terms' => "edit_" . self::capability_type . "s",
                'delete_terms' => "edit_others_" . self::capability_type . "s",
                'assign_terms' => "edit_" . self::capability_type . "s"
            ),
        ));
    }

    public function initialize_meta_boxes() {
        if ( !class_exists( 'rrze_Meta_Box' ) ) {
            include_once(plugin_dir_path(__FILE__) . 'includes/metaboxes/rrze_meta_box.php');
        }
    }

    public function set_meta_boxes() {
        global $wp_meta_boxes;

        $screen = get_current_screen();
        if (self::post_type != $screen->post_type) {
            return;
        }

        unset($wp_meta_boxes[self::post_type]['normal']['core']['authordiv']);

        remove_meta_box('postimagediv', self::post_type, 'side');
        add_meta_box('postimagediv', __('Studienangebotsbild', self::textdomain), 'post_thumbnail_meta_box', self::post_type, 'side', 'default');

        unset($wp_meta_boxes[self::post_type]['side']['core']['studiengangdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['abschlussdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['semesterdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['studienortdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['faechergruppediv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['fakultaetdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['saattributdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['sazvsdiv']);

        unset($wp_meta_boxes[self::post_type]['side']['core']['saconstantdiv']);
    }

    public function settings_submenu_page() {
        add_submenu_page('edit.php?post_type=' . self::post_type, __('Einstellungen', self::textdomain), __('Einstellungen', self::textdomain), 'manage_options', 'settings', array($this, 'settings_page'));
    }

    public function settings_save() {
        $options = self::get_options();

        if (!isset($_POST['action'], $_POST['_wpnonce'], $_POST['option_page'], $_POST['_wp_http_referer'], $_POST['submit']) || !is_admin()) {
            return false;
        }

        if ($_POST['action'] != 'update' || $_POST['option_page'] != 'settings') {
            return false;
        }

        if(isset($_POST['sa_page'])) {
            $sa_page = intval($_POST['sa_page']);
            $options['sa_page'] = $sa_page > 0 ? $sa_page : $options['sa_page'];
        }

        update_option(self::option_name, $options);

        $referer = add_query_arg('update', 'settings', remove_query_arg(array('message'), wp_get_referer()));
        wp_redirect($referer);
        exit;
    }

    public function settings_init() {
        add_settings_section(self::option_name . '_default_section', false, '__return_false', 'settings');
        add_settings_field('sa_page', __('Studienangebotseite', self::textdomain), array($this, 'settings_sapage'), 'settings', self::option_name . '_default_section');
    }

    public function settings_page() {
        if (isset($_GET['update'])) {
            $messages = array();
            if ('settings' == $_GET['update']) {
                $messages[] = __('Einstellungen gespeichert.', self::textdomain);
            }
        }
        ?>
        <div class="wrap">
            <h2><?php _e('Einstellungen', self::textdomain); ?></h2>
            <?php
            if (!empty($messages)) {
                foreach ($messages as $msg) {
                    printf('<div id="message" class="updated"><p>%s</p></div>', $msg);
                }
            }
            ?>
            <form action="<?php echo esc_url(menu_page_url('settings', false)); ?>" method="post">
            <?php
            settings_fields('settings');
            do_settings_sections('settings');
            submit_button(null, 'primary', 'submit', false);
            ?>
            </form>
        </div>
        <?php
    }

    public function settings_sapage() {
        $options = (object) self::get_options();

        wp_dropdown_pages(array(
            'name' => 'sa_page',
            'selected' => $options->sa_page,
            'show_option_none' => __('— Auswählen —', self::textdomain),
            'option_none_value' => -1
        ));
    }

    public function change_thumbnail_html($content) {
        if (self::post_type == $GLOBALS['post_type'])
            add_filter('admin_post_thumbnail_html', array($this, 'replace_content'));
    }

    public function replace_content($content) {
        return str_replace(__('Set featured image'), __('Studienangebotsbild festlegen', self::textdomain), $content);
    }

    public function hide_term_fields() {
        global $pagenow, $post_type;

        if(isset($pagenow) && $pagenow == 'edit-tags.php' && isset($post_type) && $post_type == 'studienangebot') {
            echo "<style type=\"text/css\">div.form-required+div.form-field+div.form-field, tr.form-required+tr.form-field+tr.form-field { display: none; }</style>";
        }
    }

    public function abschluss_add_new_meta_field() {
        ?>
        <div class="form-field">
            <label for="abschlussgruppe"><?php _e('Abschlussgruppe', self::textdomain); ?></label>
            <select class="postform" id="abschlussgruppe" name="term_meta[abschlussgruppe]">
                <option value=""><?php _e('Keine', self::textdomain); ?></option>
                <?php $abschlussgruppe = self::get_abschlussgruppe();?>
                <?php foreach ($abschlussgruppe as $key => $label): ?>
                    <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <p>&nbsp;</p>
        </div>
        <?php
    }

    public function abschluss_edit_meta_field($term) {
        $t_id = $term->term_id;
        $term_meta = get_option("abschluss_category_$t_id");
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term_meta[abschlussgruppe]"><?php _e('Abschlussgruppe', self::textdomain); ?></label></th>
            <td>
                <select class="postform" id="abschlussgruppe" name="term_meta[abschlussgruppe]">
                    <option value=""><?php _e('Keine', self::textdomain); ?></option>
                    <?php $abschlussgruppe = self::get_abschlussgruppe();?>
                    <?php foreach ($abschlussgruppe as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php selected($term_meta['abschlussgruppe'], $key); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public function save_abschluss_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = (array) get_option("abschluss_category_$t_id");
            $cat_keys = array_keys($_POST['term_meta']);
            foreach ($cat_keys as $key) {
                if (isset($_POST['term_meta'][$key])) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }

            update_option("abschluss_category_$t_id", $term_meta);
        }
    }

    public function delete_abschluss_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = get_option("abschluss_category_$t_id");

            delete_option("abschluss_category_$t_id", $term_meta);
        }
    }

    public function sazvs_add_new_meta_field() {
        ?>
        <div class="form-field">
            <label for="term-linktext"><?php _e('Linktext', self::textdomain); ?></label>
            <input name="term_meta[linktext]" id="term-linktext" type="text" value="" size="40" aria-required="true" />
            <p>&nbsp;</p>
        </div>
        <div class="form-field">
            <label for="term-linkurl"><?php _e('Linkurl', self::textdomain); ?></label>
            <input name="term_meta[linkurl]" id="term-linkurl" type="text" value="" size="40" aria-required="true" />
            <p>&nbsp;</p>
        </div>
        <?php
    }

    public function sazvs_edit_meta_field($term) {
        $t_id = $term->term_id;
        $term_meta = get_option("sazvs_category_$t_id");
        $linktext = !empty($term_meta['linktext']) ? $term_meta['linktext'] : '';
        $linkurl = !empty($term_meta['linkurl']) ? $term_meta['linkurl'] : '';
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term-linktext"><?php _e('Linktext', self::textdomain); ?></label></th>
            <td>
                <input name="term_meta[linktext]" id="term-linktext" type="text" value="<?php echo $linktext;?>" size="40" aria-required="true" />
             </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term-linkurl"><?php _e('Linkurl', self::textdomain); ?></label></th>
            <td>
                <input name="term_meta[linkurl]" id="term-linkurl" type="text" value="<?php echo $linkurl;?>" size="40" aria-required="true" />
             </td>
        </tr>
        <?php
    }

    public function save_sazvs_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = (array) get_option("sazvs_category_$t_id");
            $cat_keys = array_keys($_POST['term_meta']);
            foreach ($cat_keys as $key) {
                if (isset($_POST['term_meta'][$key])) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }

            update_option("sazvs_category_$t_id", $term_meta);
        }
    }

    public function delete_sazvs_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = get_option("sazvs_category_$t_id");

            delete_option("sazvs_category_$t_id", $term_meta);
        }
    }

    public function saconstant_add_new_meta_field() {
        ?>
        <div class="form-field">
            <label for="term-linktext"><?php _e('Linktext', self::textdomain); ?></label>
            <input name="term_meta[linktext]" id="term-linktext" type="text" value="" size="40" aria-required="true" />
            <p>&nbsp;</p>
        </div>
        <div class="form-field">
            <label for="term-linkurl"><?php _e('Linkurl', self::textdomain); ?></label>
            <input name="term_meta[linkurl]" id="term-linkurl" type="text" value="" size="40" aria-required="true" />
            <p>&nbsp;</p>
        </div>
        <?php
    }

    public function saconstant_edit_meta_field($term) {
        $t_id = $term->term_id;
        $term_meta = get_option("saconstant_category_$t_id");
        $linktext = !empty($term_meta['linktext']) ? $term_meta['linktext'] : '';
        $linkurl = !empty($term_meta['linkurl']) ? $term_meta['linkurl'] : '';
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term-linktext"><?php _e('Linktext', self::textdomain); ?></label></th>
            <td>
                <input name="term_meta[linktext]" id="term-linktext" type="text" value="<?php echo $linktext;?>" size="40" aria-required="true" />
             </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="term-linkurl"><?php _e('Linkurl', self::textdomain); ?></label></th>
            <td>
                <input name="term_meta[linkurl]" id="term-linkurl" type="text" value="<?php echo $linkurl;?>" size="40" aria-required="true" />
             </td>
        </tr>
        <?php
    }

    public function save_saconstant_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = (array) get_option("saconstant_category_$t_id");
            $cat_keys = array_keys($_POST['term_meta']);
            foreach ($cat_keys as $key) {
                if (isset($_POST['term_meta'][$key])) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }

            update_option("saconstant_category_$t_id", $term_meta);
        }
    }

    public function delete_saconstant_custom_meta($term_id) {
        if (isset($_POST['term_meta'])) {

            $t_id = $term_id;
            $term_meta = get_option("saconstant_category_$t_id");

            delete_option("saconstant_category_$t_id", $term_meta);
        }
    }

    public function term_columns($columns) {
        unset($columns['description']);
        return $columns;
    }

    public function abschluss_columns($columns) {
        $new_columns = $columns;
        array_splice($new_columns, 2);
        $new_columns['abschluss'] = esc_html__('Gruppe', self::textdomain);
        return array_merge($new_columns, $columns);
    }

    public function abschluss_custom_column($row, $column_name, $term_id) {
        $t_id = $term_id;
        $term_meta = get_option("abschluss_category_$t_id");
        $abschlussgruppe = self::get_abschlussgruppe();
        if ($term_meta && !empty($abschlussgruppe[$term_meta['abschlussgruppe']]))
            return $abschlussgruppe[$term_meta['abschlussgruppe']];

        return '';
    }

    public static function get_abschlussgruppe() {
       $abschlussgruppe = array(
            'bachelor' => __('Bachelorstudiengänge', self::textdomain),
            'master' => __('Masterstudiengänge', self::textdomain),
            'lehramt' => __('Lehramt und Staatsexamen', self::textdomain),
            'sonstige' => __('Sonstige Abschlüsse', self::textdomain),
        );

        return $abschlussgruppe;
    }

    public function sazvs_columns($columns) {
        $new_columns = $columns;
        array_splice($new_columns, 2);
        $new_columns['linktext'] = esc_html__('Linktext', self::textdomain);
        $new_columns['linkurl'] = esc_html__('Linkurl', self::textdomain);
        return array_merge($new_columns, $columns);
    }

    public function sazvs_custom_column($row, $column_name, $term_id) {
        $t_id = $term_id;
        $term_meta = get_option("sazvs_category_$t_id");
        if ($column_name == 'linktext' && !empty($term_meta[$column_name])) {
            return $term_meta[$column_name];
        } elseif ($column_name == 'linkurl' && !empty($term_meta[$column_name])) {
            return $term_meta[$column_name];
        }
        return '';
    }

    public function saconstant_columns($columns) {
        unset($columns['posts']);
        $new_columns = $columns;
        array_splice($new_columns, 2);
        $new_columns['linktext'] = esc_html__('Linktext', self::textdomain);
        $new_columns['linkurl'] = esc_html__('Linkurl', self::textdomain);
        return array_merge($new_columns, $columns);
    }

    public function saconstant_custom_column($row, $column_name, $term_id) {
        $t_id = $term_id;
        $term_meta = get_option("saconstant_category_$t_id");
        if ($column_name == 'linktext' && !empty($term_meta[$column_name])) {
            return $term_meta[$column_name];
        } elseif ($column_name == 'linkurl' && !empty($term_meta[$column_name])) {
            return $term_meta[$column_name];
        }
        return '';
    }

    public function register_sidebar() {
        register_sidebar( array(
            'name' => __('Studiengang-Sidebar', self::textdomain),
            'id' => 'sa-sidebar',
            'description' => __('Widgets in diesem Bereich werden in jedem Studiengang angezeigt.', self::textdomain),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title' => '<h2 class="small">',
            'after_title' => '</h2>',
        ));
    }



    public static function register_script() {
        wp_register_script('fa-sa-js', plugins_url('/js/studienangebot.min.js', __FILE__),  array('jquery'),  self::version, true);
        wp_register_style('fa-sa-style', plugins_url('/css/studienangebot.css', __FILE__ ),  array(),  self::version);
    }
    public static function print_script() {
	wp_enqueue_script('fa-sa-js');
	wp_enqueue_style('fa-sa-style');
    }

    private static function get_link_html($post_id, $taxonomy, $data, $ul = true) {
        $output = '-';
        $items = array();

        $terms = wp_get_object_terms($post_id, $taxonomy);
        if(empty($terms) || is_wp_error($terms)) {
            return $output;
        }

        if(!is_array($data)) {
            $data = array($data);
        }

        foreach($terms as $term) {
            foreach($data as $val) {
                $item = self::get_link_term($term->term_id, $taxonomy, $val);
                if(!$item) {
                    continue;
                }
                $items[] = $item;
            }
        }

        if($ul && !empty($items)) {
            if(count($items) > 1) {
                $output = '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
            }
            else {
                $output = implode(', ', $items);
            }
        }

        return $output;
    }

    private static function get_link_term($term_id, $taxonomy, $slug) {
        $item = '';
        $term = get_term_by('slug', $slug, $taxonomy);
        if($term && ($term->term_id == $term_id)) {
            $t_id = $term->term_id;
            $meta = get_option(sprintf('%1$s_category_%2$s', $taxonomy, $t_id));
            if($meta && !empty($meta['linkurl'])) {
                $item = sprintf('<a href="%2$s">%1$s</a>', $meta['linktext'], $meta['linkurl']);
            } elseif($meta) {
                $item = $meta['linktext'];
            }
        }
        return $item;
    }

    private static function get_metadata_html($post_id, $data, $ul = true) {
        $output = '-';
        $items = array();
        if(!is_array($data)) {
            $data = array($data);
        }
        foreach($data as $val) {
            $item = trim(get_post_meta($post_id, $val, true));
            if(!$item) {
                continue;
            }
            $items[] = $item;
        }
        if(!empty($items)) {
            if($ul && count($items) > 1) {
                $output = '<ul><li>' . implode('</li><li>', $items) . '</li></ul>';
            } else {
                $output = implode(', ', $items);
            }
        }

        return $output;
    }

    public function the_content($content) {

        if (is_singular(self::post_type)) {

            $post = get_post();

            if (!empty($post)) {
                $content = self::the_output($post->ID);
            }

        }
        return $content;

    }

    public static function the_output($post_id) {

        $terms = wp_get_object_terms($post_id, self::$taxonomies);

        if (empty($terms) || is_wp_error($terms)) {
            return '<p class="notice-attention">' . __('Es konnte nichts gefunden werden.', self::textdomain) . '</p>';
        }


        $faechergruppe = array();
        $fakultaet = array();
        $abschluss = array();
        $semester = array();
        $studienort = array();

        foreach ($terms as $term) {
            ${$term->taxonomy}[] = $term->name;
        }

        $faechergruppe = isset($faechergruppe) ? implode(', ', $faechergruppe) : '';
        $fakultaet = isset($fakultaet) ? implode(', ', $fakultaet) : '';
        $abschluss = isset($abschluss) ? implode(', ', $abschluss) : '';
        $semester = isset($semester) ? implode(', ', $semester) : '';
        $studienort = isset($studienort) ? implode(', ', $studienort) : '';

        $regelstudienzeit = self::get_metadata_html($post_id, 'sa_regelstudienzeit');
        $studiengang_info = self::get_metadata_html($post_id, 'sa_studiengang_info');
        $kombination_info = self::get_metadata_html($post_id, 'sa_kombination_info');

        $zvs_anfaenger = array();
        $zvs_hoeheres_semester = array();
        $zvs_terms = wp_get_object_terms($post_id, 'sazvs');
        if (!is_wp_error($zvs_terms) && !empty($zvs_terms)) {
            foreach($zvs_terms as $term) {
                if(strpos($term->slug, 'studienanfaenger') === 0) {
                    $zvs_anfaenger[] = $term->slug;
                } elseif(strpos($term->slug, 'hoeheres-semester') === 0) {
                    $zvs_hoeheres_semester[] = $term->slug;
                }

            }
        }
        $zvs_anfaenger = self::get_link_html($post_id, 'sazvs', $zvs_anfaenger);
        $zvs_hoeheres_semester = self::get_link_html($post_id, 'sazvs', $zvs_hoeheres_semester);

        $zvs_weiteres = self::get_metadata_html($post_id, 'sa_zvs_weiteres');

        $schwerpunkte = self::get_metadata_html($post_id, 'sa_schwerpunkte');
        $sprachkenntnisse = self::get_metadata_html($post_id, 'sa_sprachkenntnisse');

        $deutschkenntnisse = self::get_metadata_html($post_id, 'sa_de_kenntnisse_info');

        $besondere_hinweise = self::get_metadata_html($post_id, 'sa_besondere_hinweise');

        $fach = self::get_metadata_html($post_id, 'sa_fach_info');

        $sa_gebuehren = self::get_metadata_html($post_id, 'sa_gebuehren');
        $bewerbung = self::get_metadata_html($post_id, 'sa_bewerbung');
        $studiengangskoordination = self::get_metadata_html($post_id, 'sa_studiengangskoordination');
        $einfuehrung = self::get_metadata_html($post_id, 'sa_einfuehrung_info');

        $attribut_terms = wp_get_object_terms($post_id, 'saattribut', array('fields' => 'slugs'));

        $pruefung = self::get_metadata_html($post_id, array('sa_pruefungsamt_info', 'sa_pruefungsordnung_info'));
        $studienberatung = self::get_metadata_html($post_id, array('sa_sb_allgemein_info', 'sa_ssc_info'));
        $w_studienberatung = self::get_metadata_html($post_id, 'sa_ssc_info');

        $termine = self::get_link_html($post_id, 'saconstant', array('hinweisblatt-zur-einschreibung', 'semester-und-terminplan'));
        $gebuehren = self::get_link_html($post_id, 'saconstant', 'semesterbeitraege');
        $studentenvertretung = self::get_link_html($post_id, 'saconstant', 'studentenvertretungfachschaft');
        $beruflich = self::get_link_html($post_id, 'saconstant', 'berufliche-moeglichkeiten');

        ob_start();

        $current_theme = wp_get_theme();
        if (in_array($current_theme->stylesheet, self::$fauthemes)) {
            $template = 'content-fau-page.php';
        } else {
            $template = sprintf('content-%s.php', self::post_type);
        }
        include_once(plugin_dir_path(__FILE__) . 'includes/templates/' . $template);

        return ob_get_clean();
    }

    public static function export_to_csv_button( $which ) {
        if (get_post_type() == 'studienangebot' &&  $which == 'top') {
            ?>
            <input type="submit" name="fau-studienangebot-export-to-csv" class="button button-primary" value="<?php _e('Export nach CSV', self::textdomain); ?>" />
            <?php
        }
    }

    public static function export_to_csv() {
        if(!isset($_GET['fau-studienangebot-export-to-csv'])) {
            return;
        }

        $args = [
            'post_type' => 'studienangebot',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
    
        $metas = [
            'studiengang' => [
                'type' => 'taxonomy',
                'label' => 'Studiengang'
            ],
            'fakultaet' => [
                'type' => 'taxonomy',
                'label' => 'Fakultät'
            ],
            'abschluss' => [
                'type' => 'taxonomy',
                'label' => 'Abschluss'
            ],  
            'sa_regelstudienzeit' => [
                'type' => 'postmeta',
                'label' => 'Regelstudienzeit'
            ],
            'semester' => [
                'type' => 'taxonomy',
                'label' => 'Studienbeginn'
            ],
            'studienort' => [
                'type' => 'taxonomy',
                'label' => 'Studienort'
            ],
            'sa_studiengang_info' => [
                'type' => 'postmeta',
                'label' => 'Kurzinformationen zum Studiengang'
            ], 
            'faechergruppe' => [
                'type' => 'taxonomy',
                'label' => 'Fächergruppe'
            ], 
            'saattribut' => [
                'type' => 'taxonomy',
                'label' => 'Attribute'
            ],
            'sa_schwerpunkte' => [
                'type' => 'postmeta',
                'label' => 'Studieninhalte'
            ],
            'sa_besondere_hinweise' => [
                'type' => 'postmeta',
                'label' => 'Besondere Hinweise'
            ],
            'sa_kombination_info' => [
                'type' => 'postmeta',
                'label' => 'Kombinationsmöglichkeiten'
            ],
            'sazvs' => [
                'type' => 'taxonomy',
                'label' => 'Voraussetzungen'
            ],
            'sa_zvs_weiteres' => [
                'type' => 'postmeta',
                'label' => 'Details'
            ],
            'sa_sprachkenntnisse' => [
                'type' => 'postmeta',
                'label' => 'Sprachkenntnisse'
            ],
            'sa_de_kenntnisse_info' => [
                'type' => 'postmeta',
                'label' => 'Deutschkenntnisse für ausländische Studierende'
            ],
            'sa_einfuehrung_info' => [
                'type' => 'postmeta',
                'label' => 'Studienbeginn - Einführungsveranstaltung für Erstsemester'
            ],
            'sa_pruefungsamt_info' => [
                'type' => 'postmeta',
                'label' => 'Prüfungsamt/ Prüfungsbeauftragte'
            ],
            'sa_pruefungsordnung_info' => [
                'type' => 'postmeta',
                'label' => 'Studien- und Prüfungsordnung mit Studienplan'
            ],
            'sa_fach_info' => [
                'type' => 'postmeta',
                'label' => 'Link zum Studiengang'
            ],  
            'sa_sb_allgemein_info' => [
                'type' => 'postmeta',
                'label' => 'Studienberatung allgemein'
            ],          
            'sa_ssc_info' => [
                'type' => 'postmeta',
                'label' => 'Studien-Service-Center'
            ],
            'sa_englische_bezeichnung' => [
                'type' => 'postmeta',
                'label' => 'Englische Bezeichnung des Studiengangs'
            ],
            'sa_englische_url' => [
                'type' => 'postmeta',
                'label' => 'Link zur englischen Webseite des Faches'
            ],
            'sa_englisch_anzeige' => [
                'type' => 'postmeta',
                'label' => 'Anzeige des Studienganges im englischen Webauftritt'
            ],
            'sa_gebuehren' => [
                'type' => 'postmeta',
                'label' => 'Studiengangsgebühren'
            ],
            'sa_bewerbung' => [
                'type' => 'postmeta',
                'label' => 'Bewerbungsverfahren'
            ],
            'sa_studiengangskoordination' => [
                'type' => 'postmeta',
                'label' => 'Studiengangskoordination'
            ],
            'saconstant' => [
                'type' => 'taxonomy',
                'label' => 'Allgemein'
            ],
        ];         

        $posts_ary = get_posts($args);
        if (!$posts_ary) {
            return;
        }
        
        $columns[] = 'Titel';
        foreach ($metas as $meta_key => $meta_value) {
            $columns[] = $meta_value['label'];
        }

        $rows = [];
        foreach ($posts_ary as $post) {
            $row = [];
            $row[] = wp_specialchars_decode($post->post_title);

            foreach ($metas as $meta_key => $meta_value) {
                if ($meta_value['type'] == 'taxonomy') {
                    $term_list = wp_get_post_terms($post->ID, $meta_key, ['fields' => 'all']);
                    if (!is_wp_error($term_list) && !empty($term_list)) {
                        $t = [];
                        foreach ($term_list as $term) {
                            $t[] = wp_specialchars_decode($term->name);
                        }
                        $row[] = implode(',', $t);                          
                    } else {
                        $row[] = '';
                    }
                } elseif ($meta_value['type'] == 'postmeta') {
                    $post_meta = get_post_meta($post->ID, $meta_key, true);
                    if ($post_meta) {
                        $string = strip_tags($post_meta, '<a>');
                        $string = str_replace('&nbsp;', '', $string);
                        $string = preg_replace('/\t+/', '', $string);
                        $string = wp_specialchars_decode($string);
                        $row[] = trim($string);
                    } else {
                        $row[] = '';
                    }
                }
            }
            $rows[] = $row;               
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="fau-studienangebot.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $file = fopen('php://output', 'w');

        fputcsv($file, $columns, "\t");
        
        foreach ($rows as $row) {
            fputcsv($file, $row, "\t");
        }

        exit();
    }    
}
