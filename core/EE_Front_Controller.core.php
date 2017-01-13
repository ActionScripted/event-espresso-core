<?php
use EventEspresso\core\services\shortcodes\LegacyShortcodesManager;
use EventEspresso\widgets\EspressoWidget;

if ( ! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

/**
 * Event Espresso
 * Event Registration and Management Plugin for WordPress
 * @ package            Event Espresso
 * @ author            Seth Shoultes
 * @ copyright        (c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license            http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link                    http://www.eventespresso.com
 * @ version            4.0
 * ------------------------------------------------------------------------
 * EE_Front_Controller
 *
 * @package               Event Espresso
 * @subpackage            core/
 * @author                Brent Christensen
 *                        ------------------------------------------------------------------------
 */
final class EE_Front_Controller
{

    /**
     * @var string $_template_path
     */
    private $_template_path;

    /**
     * @var string $_template
     */
    private $_template;

    /**
     * @type EE_Registry $Registry
     */
    protected $Registry;

    /**
     * @type EE_Request_Handler $Request_Handler
     */
    protected $Request_Handler;

    /**
     * @type EE_Module_Request_Router $Module_Request_Router
     */
    protected $Module_Request_Router;


    /**
     *    class constructor
     *    should fire after shortcode, module, addon, or other plugin's default priority init phases have run
     *
     * @access    public
     * @param \EE_Registry              $Registry
     * @param \EE_Request_Handler       $Request_Handler
     * @param \EE_Module_Request_Router $Module_Request_Router
     */
    public function __construct(
        EE_Registry $Registry,
        EE_Request_Handler $Request_Handler,
        EE_Module_Request_Router $Module_Request_Router
    ) {
        $this->Registry              = $Registry;
        $this->Request_Handler       = $Request_Handler;
        $this->Module_Request_Router = $Module_Request_Router;
        // determine how to integrate WP_Query with the EE models
        add_action('AHEE__EE_System__initialize', array($this, 'employ_CPT_Strategy'));
        // load other resources and begin to actually run shortcodes and modules
        add_action('wp_loaded', array($this, 'wp_loaded'), 5);
        // analyse the incoming WP request
        add_action('parse_request', array($this, 'get_request'), 1, 1);
        // process request with module factory
        add_action('pre_get_posts', array($this, 'pre_get_posts'), 10, 1);
        // before headers sent
        add_action('wp', array($this, 'wp'), 5);
        // after headers sent but before any markup is output,
        // primarily used to process any content shortcodes
        add_action('get_header', array($this, 'get_header'));
        // load css and js
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'), 1);
        // header
        add_action('wp_head', array($this, 'header_meta_tag'), 5);
        add_action('wp_print_scripts', array($this, 'wp_print_scripts'), 10);
        add_filter('template_include', array($this, 'template_include'), 1);
        // display errors
        add_action('loop_start', array($this, 'display_errors'), 2);
        // the content
        // add_filter( 'the_content', array( $this, 'the_content' ), 5, 1 );
        //exclude our private cpt comments
        add_filter('comments_clauses', array($this, 'filter_wp_comments'), 10, 1);
        //make sure any ajax requests will respect the url schema when requests are made against admin-ajax.php (http:// or https://)
        add_filter('admin_url', array($this, 'maybe_force_admin_ajax_ssl'), 200, 1);
        // action hook EE
        do_action('AHEE__EE_Front_Controller__construct__done', $this);
        // for checking that browser cookies are enabled
        if (apply_filters('FHEE__EE_Front_Controller____construct__set_test_cookie', true)) {
            setcookie('ee_cookie_test', uniqid('ect',true), time() + DAY_IN_SECONDS, '/');
        }
    }


    /**
     * @return EE_Request_Handler
     */
    public function Request_Handler()
    {
        return $this->Request_Handler;
    }


    /**
     * @return EE_Module_Request_Router
     */
    public function Module_Request_Router()
    {
        return $this->Module_Request_Router;
    }



    /**
     * @return LegacyShortcodesManager
     */
    public function getLegacyShortcodesManager()
    {
        return EE_Config::getLegacyShortcodesManager();
    }





    /***********************************************        INIT ACTION HOOK         ***********************************************/



    /**
     * filter_wp_comments
     * This simply makes sure that any "private" EE CPTs do not have their comments show up in any wp comment
     * widgets/queries done on frontend
     *
     * @param  array $clauses array of comment clauses setup by WP_Comment_Query
     * @return array array of comment clauses with modifications.
     */
    public function filter_wp_comments($clauses)
    {
        global $wpdb;
        if (strpos($clauses['join'], $wpdb->posts) !== false) {
            $cpts = EE_Register_CPTs::get_private_CPTs();
            foreach ($cpts as $cpt => $details) {
                $clauses['where'] .= $wpdb->prepare(" AND $wpdb->posts.post_type != %s", $cpt);
            }
        }
        return $clauses;
    }


    /**
     *    employ_CPT_Strategy
     *
     * @access    public
     * @return    void
     */
    public function employ_CPT_Strategy()
    {
        if (apply_filters('FHEE__EE_Front_Controller__employ_CPT_Strategy', true)) {
            $this->Registry->load_core('CPT_Strategy');
        }
    }


    /**
     * this just makes sure that if the site is using ssl that we force that for any admin ajax calls from frontend
     *
     * @param  string $url incoming url
     * @return string         final assembled url
     */
    public function maybe_force_admin_ajax_ssl($url)
    {
        if (is_ssl() && preg_match('/admin-ajax.php/', $url)) {
            $url = str_replace('http://', 'https://', $url);
        }
        return $url;
    }






    /***********************************************        WP_LOADED ACTION HOOK         ***********************************************/


    /**
     *    wp_loaded - should fire after shortcode, module, addon, or other plugin's have been registered and their
     *    default priority init phases have run
     *
     * @access    public
     * @return    void
     */
    public function wp_loaded()
    {
    }





    /***********************************************        PARSE_REQUEST HOOK         ***********************************************/
    /**
     *    _get_request
     *
     * @access public
     * @param WP $WP
     * @return void
     */
    public function get_request(WP $WP)
    {
        do_action('AHEE__EE_Front_Controller__get_request__start');
        $this->Request_Handler->parse_request($WP);
        do_action('AHEE__EE_Front_Controller__get_request__complete');
    }



    /**
     *    pre_get_posts - basically a module factory for instantiating modules and selecting the final view template
     *
     * @access    public
     * @param   WP_Query $WP_Query
     * @return    void
     */
    public function pre_get_posts($WP_Query)
    {
        // only load Module_Request_Router if this is the main query
        if (
            $this->Module_Request_Router instanceof EE_Module_Request_Router
            && $WP_Query->is_main_query()
        ) {
            // cycle thru module routes
            while ($route = $this->Module_Request_Router->get_route($WP_Query)) {
                // determine module and method for route
                $module = $this->Module_Request_Router->resolve_route($route[0], $route[1]);
                if ($module instanceof EED_Module) {
                    // get registered view for route
                    $this->_template_path = $this->Module_Request_Router->get_view($route);
                    // grab module name
                    $module_name = $module->module_name();
                    // map the module to the module objects
                    $this->Registry->modules->{$module_name} = $module;
                }
            }
        }
    }





    /***********************************************        WP HOOK         ***********************************************/


    /**
     *    wp - basically last chance to do stuff before headers sent
     *
     * @access    public
     * @return    void
     */
    public function wp()
    {
    }



    /***********************     GET_HEADER, WP_ENQUEUE_SCRIPTS && WP_HEAD HOOK     ***********************/



    /**
     * callback for the WP "get_header" hook point
     * checks posts for EE shortcodes, and sidebars for EE widgets
     * loads resources and assets accordingly
     *
     * @return void
     */
    public function get_header()
    {
        global $wp_query;
        if (empty($wp_query->posts)){
            return;
        }
        // if we already know this is an espresso page, then load assets
        $load_assets = $this->Request_Handler->is_espresso_page();
        // array of posts displayed in current request
        $posts = is_array($wp_query->posts) ? $wp_query->posts : array($wp_query->posts);
        $load_assets = $this->getLegacyShortcodesManager()->postHasShortcodes($posts)
            ? true : $load_assets;
        // if we are already loading assets then just move along, otherwise check for widgets
        $load_assets = $load_assets ? $load_assets : $this->espresso_widgets_in_active_sidebars();
        if ( $load_assets){
            add_filter('FHEE_load_css', '__return_true');
            add_filter('FHEE_load_js', '__return_true');
        }
    }



    /**
     * builds list of active widgets then scans active sidebars looking for them
     * returns true is an EE widget is found in an active sidebar
     * Please Note: this does NOT mean that the sidebar or widget
     * is actually in use in a given template, as that is unfortunately not known
     * until a sidebar and it's widgets are actually loaded
     *
     * @return boolean
     */
    private function espresso_widgets_in_active_sidebars()
    {
        $espresso_widgets = array();
        foreach ($this->Registry->widgets as $widget_class => $widget) {
            $id_base = EspressoWidget::getIdBase($widget_class);
            if (is_active_widget(false, false, $id_base)) {
                $espresso_widgets[] = $id_base;
            }
        }
        $all_sidebar_widgets = wp_get_sidebars_widgets();
        foreach ($all_sidebar_widgets as $sidebar_name => $sidebar_widgets) {
            if (is_array($sidebar_widgets) && ! empty($sidebar_widgets)) {
                foreach ($sidebar_widgets as $sidebar_widget) {
                    foreach ($espresso_widgets as $espresso_widget) {
                        if (strpos($sidebar_widget, $espresso_widget) !== false) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }




    /**
     *    wp_enqueue_scripts
     *
     * @access    public
     * @return    void
     */
    public function wp_enqueue_scripts()
    {
        // css is turned ON by default, but prior to the wp_enqueue_scripts hook, can be turned OFF  via:  add_filter( 'FHEE_load_css', '__return_false' );
        if (apply_filters('FHEE_load_css', false)) {

            $this->Registry->CFG->template_settings->enable_default_style = true;
            //Load the ThemeRoller styles if enabled
            if (isset($this->Registry->CFG->template_settings->enable_default_style) && $this->Registry->CFG->template_settings->enable_default_style) {

                //Load custom style sheet if available
                if (isset($this->Registry->CFG->template_settings->custom_style_sheet)) {
                    wp_register_style('espresso_custom_css',
                        EVENT_ESPRESSO_UPLOAD_URL . 'css/' . $this->Registry->CFG->template_settings->custom_style_sheet,
                        EVENT_ESPRESSO_VERSION);
                    wp_enqueue_style('espresso_custom_css');
                }

                if (is_readable(EVENT_ESPRESSO_UPLOAD_DIR . 'css/style.css')) {
                    wp_register_style('espresso_default', EVENT_ESPRESSO_UPLOAD_DIR . 'css/espresso_default.css',
                        array('dashicons'), EVENT_ESPRESSO_VERSION);
                } else {
                    wp_register_style('espresso_default', EE_GLOBAL_ASSETS_URL . 'css/espresso_default.css',
                        array('dashicons'), EVENT_ESPRESSO_VERSION);
                }
                wp_enqueue_style('espresso_default');

                if (is_readable(get_stylesheet_directory() . EE_Config::get_current_theme() . DS . 'style.css')) {
                    wp_register_style('espresso_style',
                        get_stylesheet_directory_uri() . EE_Config::get_current_theme() . DS . 'style.css',
                        array('dashicons', 'espresso_default'));
                } else {
                    wp_register_style('espresso_style',
                        EE_TEMPLATES_URL . EE_Config::get_current_theme() . DS . 'style.css',
                        array('dashicons', 'espresso_default'));
                }

            }

        }

        // js is turned ON by default, but prior to the wp_enqueue_scripts hook, can be turned OFF  via:  add_filter( 'FHEE_load_js', '__return_false' );
        if (apply_filters('FHEE_load_js', false)) {

            wp_enqueue_script('jquery');
            //let's make sure that all required scripts have been setup
            if (function_exists('wp_script_is') && ! wp_script_is('jquery')) {
                $msg = sprintf(
                    __('%sJquery is not loaded!%sEvent Espresso is unable to load Jquery due to a conflict with your theme or another plugin.',
                        'event_espresso'),
                    '<em><br />',
                    '</em>'
                );
                EE_Error::add_error($msg, __FILE__, __FUNCTION__, __LINE__);
            }
            // load core js
            wp_register_script('espresso_core', EE_GLOBAL_ASSETS_URL . 'scripts/espresso_core.js', array('jquery'),
                EVENT_ESPRESSO_VERSION, true);
            wp_enqueue_script('espresso_core');
            wp_localize_script('espresso_core', 'eei18n', EE_Registry::$i18n_js_strings);

        }

        //qtip is turned OFF by default, but prior to the wp_enqueue_scripts hook, can be turned back on again via: add_filter('FHEE_load_qtip', '__return_true' );
        if (apply_filters('FHEE_load_qtip', false)) {
            EEH_Qtip_Loader::instance()->register_and_enqueue();
        }


        //accounting.js library
        // @link http://josscrowcroft.github.io/accounting.js/
        if (apply_filters('FHEE_load_accounting_js', false)) {
            $acct_js = EE_THIRD_PARTY_URL . 'accounting/accounting.js';
            wp_register_script('ee-accounting', EE_GLOBAL_ASSETS_URL . 'scripts/ee-accounting-config.js',
                array('ee-accounting-core'), EVENT_ESPRESSO_VERSION, true);
            wp_register_script('ee-accounting-core', $acct_js, array('underscore'), '0.3.2', true);
            wp_enqueue_script('ee-accounting');

            $currency_config = array(
                'currency' => array(
                    'symbol'    => $this->Registry->CFG->currency->sign,
                    'format'    => array(
                        'pos'  => $this->Registry->CFG->currency->sign_b4 ? '%s%v' : '%v%s',
                        'neg'  => $this->Registry->CFG->currency->sign_b4 ? '- %s%v' : '- %v%s',
                        'zero' => $this->Registry->CFG->currency->sign_b4 ? '%s--' : '--%s',
                    ),
                    'decimal'   => $this->Registry->CFG->currency->dec_mrk,
                    'thousand'  => $this->Registry->CFG->currency->thsnds,
                    'precision' => $this->Registry->CFG->currency->dec_plc,
                ),
                'number'   => array(
                    'precision' => 0,
                    'thousand'  => $this->Registry->CFG->currency->thsnds,
                    'decimal'   => $this->Registry->CFG->currency->dec_mrk,
                ),
            );
            wp_localize_script('ee-accounting', 'EE_ACCOUNTING_CFG', $currency_config);
        }

        if ( ! function_exists('wp_head')) {
            $msg = sprintf(
                __('%sMissing wp_head() function.%sThe WordPress function wp_head() seems to be missing in your theme. Please contact the theme developer to make sure this is fixed before using Event Espresso.',
                    'event_espresso'),
                '<em><br />',
                '</em>'
            );
            EE_Error::add_error($msg, __FILE__, __FUNCTION__, __LINE__);
        }
        if ( ! function_exists('wp_footer')) {
            $msg = sprintf(
                __('%sMissing wp_footer() function.%sThe WordPress function wp_footer() seems to be missing in your theme. Please contact the theme developer to make sure this is fixed before using Event Espresso.',
                    'event_espresso'),
                '<em><br />',
                '</em>'
            );
            EE_Error::add_error($msg, __FILE__, __FUNCTION__, __LINE__);
        }

    }


    /**
     *    header_meta_tag
     *
     * @access    public
     * @return    void
     */
    public function header_meta_tag()
    {
        print(
            apply_filters(
                'FHEE__EE_Front_Controller__header_meta_tag',
                '<meta name="generator" content="Event Espresso Version ' . EVENT_ESPRESSO_VERSION . "\" />\n")
        );

        //let's exclude all event type taxonomy term archive pages from search engine indexing
        //@see https://events.codebasehq.com/projects/event-espresso/tickets/10249
        if (
            is_tax('espresso_event_type')
            && get_option( 'blog_public' ) !== '0'
        ) {
            print(
                apply_filters(
                    'FHEE__EE_Front_Controller__header_meta_tag__noindex_for_event_type',
                    '<meta name="robots" content="noindex,follow" />' . "\n"
                )
            );
        }
    }



    /**
     * wp_print_scripts
     *
     * @return void
     */
    public function wp_print_scripts()
    {
        global $post;
        if (get_post_type() === 'espresso_events' && is_singular()) {
            \EEH_Schema::add_json_linked_data_for_event($post->EE_Event);
        }
    }




    /***********************************************        THE_CONTENT FILTER HOOK         **********************************************



    // /**
    //  *    the_content
    //  *
    //  * @access    public
    //  * @param   $the_content
    //  * @return    string
    //  */
    // public function the_content( $the_content ) {
    // 	// nothing gets loaded at this point unless other systems turn this hookpoint on by using:  add_filter( 'FHEE_run_EE_the_content', '__return_true' );
    // 	if ( apply_filters( 'FHEE_run_EE_the_content', FALSE ) ) {
    // 	}
    // 	return $the_content;
    // }



    /***********************************************        WP_FOOTER         ***********************************************/


    /**
     * display_errors
     *
     * @access public
     * @return void
     */
    public function display_errors()
    {
        static $shown_already = false;
        do_action('AHEE__EE_Front_Controller__display_errors__begin');
        if (
            ! $shown_already
            && apply_filters('FHEE__EE_Front_Controller__display_errors', true)
            && is_main_query()
            && ! is_feed()
            && in_the_loop()
            && $this->Request_Handler->is_espresso_page()
        ) {
            echo EE_Error::get_notices();
            $shown_already = true;
            EEH_Template::display_template(EE_TEMPLATES . 'espresso-ajax-notices.template.php');
        }
        do_action('AHEE__EE_Front_Controller__display_errors__end');
    }





    /***********************************************        UTILITIES         ***********************************************/
    /**
     *    template_include
     *
     * @access    public
     * @param   string $template_include_path
     * @return    string
     */
    public function template_include($template_include_path = null)
    {
        if ($this->Request_Handler->is_espresso_page()) {
            $this->_template_path = ! empty($this->_template_path) ? basename($this->_template_path) : basename($template_include_path);
            $template_path        = EEH_Template::locate_template($this->_template_path, array(), false);
            $this->_template_path = ! empty($template_path) ? $template_path : $template_include_path;
            $this->_template      = basename($this->_template_path);
            return $this->_template_path;
        }
        return $template_include_path;
    }


    /**
     *    get_selected_template
     *
     * @access    public
     * @param bool $with_path
     * @return    string
     */
    public function get_selected_template($with_path = false)
    {
        return $with_path ? $this->_template_path : $this->_template;
    }



    /**
     * @deprecated 4.9.26
     * @param string $shortcode_class
     * @param \WP    $wp
     */
    public function initialize_shortcode($shortcode_class = '', WP $wp = null)
    {
        \EE_Error::doing_it_wrong(
            __METHOD__,
            __(
                'Usage is deprecated. Please use \EventEspresso\core\domain\services\helpers\ShortcodeHelper::initializeShortcode() instead.',
                'event_espresso'
            ),
            '4.9.26'
        );
        $this->LegacyShortcodesManager()->initializeShortcode($shortcode_class, $wp);
    }

}
// End of file EE_Front_Controller.core.php
// Location: /core/EE_Front_Controller.core.php
