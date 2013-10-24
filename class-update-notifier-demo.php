<?php

/**
 * Update Notifier Demo.
 *
 * @package   Update Notifier Demo
 * @author    Ivan Batić <ivan.batic@live.com>
 * @license   GPL-2.0+
 * @link      http://github.com/ivanbatic/wp-update-notifier-demo
 */

/**
 * Update Notifier Demo class.
 *
 * @package Update Notifier Demo
 * @author  Ivan Batić <ivan.batic@live.com>
 */
class update_notifier_demo {
    /**
     * Plugin version, used for cache-busting of style and script file references.
     * @since   1.0.0
     * @var     string
     */

    const VERSION = '1.0.0';

    /**
     * Unique identifier for your plugin.
     * The variable name is used as the text domain when internationalizing strings of text.
     * Its value should match the Text Domain file header in the main plugin file.
     * @since    1.0.0
     * @var      string
     */
    public $plugin_slug = 'update_notifier_demo';

    /**
     * Instance of this class.
     * @since    1.0.0
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     * @var      string
     */
    public $plugin_screen_hook_suffix = 'update-notifier-demo';

    /** @var string Plugin name */
    public static $plugin_name = 'Update Notifier';

    /** Option values */
    private $options;
    protected $intervals = array('hourly', 'daily', 'weekly', 'monthly');
    public static $update_notification_hook = 'send_update_notification';

    /**
     * Initialize the plugin by setting localization, filters, and administration functions.
     * @since     1.0.0
     */
    private function __construct() {

        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));

        // Add the options page and menu item.
        $plugin_basename = plugin_basename(plugin_dir_path(__FILE__) . 'update-notifier-demo.php');
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_action_links'));
        // Add an action link pointing to the options page.
        // Load admin style sheet and JavaScript.
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        //Bolierplate end
        add_action('admin_init', array($this, 'register_settings'));

        add_action(self::$update_notification_hook, array($this, self::$update_notification_hook));
    }

    /**
     * Return an instance of this class.
     * @since     1.0.0
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     * @since    1.0.0
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function activate() {
        if (!wp_next_scheduled(self::$update_notification_hook)) {
            $interval_name = 'update_notifier_demo_notification_interval';
            wp_schedule_event(current_time('timestamp'), get_option($interval_name) ? : 'daily', self::$update_notification_hook);
        }
    }

    /**
     * Fired when the plugin is deactivated.
     * @since    1.0.0
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public static function deactivate() {
        wp_clear_scheduled_hook(self::$update_notification_hook);
    }

    /**
     * Load the plugin text domain for translation.
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . '/languages');
    }

    /**
     * Register and enqueue admin-specific style sheet.
     * @since     1.0.0
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_styles() {

        if (!isset($this->plugin_screen_hook_suffix)) {
            return;
        }

        $screen = get_current_screen();
        if ($screen->id == $this->plugin_screen_hook_suffix) {
            wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('css/admin.css', __FILE__), array(), self::VERSION);
        }
    }

    /**
     * Register and enqueue admin-specific JavaScript.
     *
     * @since     1.0.0
     *
     * @return    null    Return early if no settings page is registered.
     */
    public function enqueue_admin_scripts() {

        if (!isset($this->plugin_screen_hook_suffix)) {
            return;
        }

        $screen = get_current_screen();
        if ($screen->id == $this->plugin_screen_hook_suffix) {
            wp_enqueue_script($this->plugin_slug . '-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), self::VERSION);
        }
    }

    /**
     * Register the administration menu for this plugin.
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        $this->plugin_screen_hook_suffix = add_options_page(
            __('Update Notifier Demo', $this->plugin_slug), __('Update Notifier', $this->plugin_slug), 'manage_options', $this->plugin_slug, array($this, 'display_plugin_admin_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once( 'views/admin.php' );
    }

    /**
     * Add settings action link to the plugins page.
     * @since    1.0.0
     */
    public function add_action_links($links) {
        return array_merge(
            array(
            'settings' => '<a href="' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>'
            ), $links
        );
    }

    // Boilerplate end

    /**
     * Whitelist plugin settings
     */
    public function register_settings() {
        $email_name = $this->plugin_slug . '_notification_email';
        $interval_name = $this->plugin_slug . '_notification_interval';
        register_setting($this->plugin_slug, $email_name, function($input) {
                $email = filter_var($input, FILTER_VALIDATE_EMAIL);
                if (!$email && !empty($email)) {
                    add_settings_error($email_name, $email_name . '_invalid', __('Invalid email'));
                }
                return $email ? : '';
            });
        register_setting($this->plugin_slug, $interval_name, function($input) {
                if (!in_array($input, $this->intervals)) {
                    add_settings_error($interval_name, $interval_name . '_invalid', __('Invalid Interval'));
                    end($this->intervals);
                    return $this->intervals[key($this->intervals)];
                }
                $opt = get_option($this->plugin_slug . '_notification_interval');
                if ($opt !== $input) {
                    wp_clear_scheduled_hook(self::$update_notification_hook);
                    wp_schedule_event(current_time('timestamp'), $input, self::$update_notification_hook);
                }
                //Reschedule doesn't work on remote server for some reason
//                wp_reschedule_event(current_time('timestamp'), $input, self::$update_notification_hook);
                return $input;
            });
        add_settings_section('notification_settings', __('Notification Settings'), null, $this->plugin_slug);

        add_settings_field($email_name, __('Notification Email'), array($this, 'notification_email_render'), $this->plugin_slug, 'notification_settings');
        add_settings_field($interval_name, __('Notification Interval'), array($this, 'notification_interval_render'), $this->plugin_slug, 'notification_settings');
    }

    /**
     * Render notification email field html
     */
    public function notification_email_render() {
        $email_name = $this->plugin_slug . '_notification_email';
        $email_option = get_option($email_name);
        printf('<input type="text" id="%s" name="%s" value="%s"/>', $email_name, $email_name, $email_option);
    }

    /**
     * Render notification interval field html
     */
    public function notification_interval_render() {
        $interval_name = $this->plugin_slug . '_notification_interval';
        $interval_option = get_option($interval_name);
        $select = sprintf("<select id='%s' name='%s'>", $interval_name, $interval_name);
        foreach ($this->intervals as $interval) {
            $select .= sprintf("<option %s value='%s'>%s</option>", $interval_option == $interval ? 'selected' : '', $interval, ucfirst($interval));
        }
        $select .= "</select>";
        echo $select;
    }

    /**
     * Returns available core updates
     * @global string $wp_version
     * @return array
     */
    public function check_core_updates() {
        global $wp_version;
        delete_site_transient('update_core');
        do_action('wp_version_check');
        $updates = get_core_updates();
        foreach ($updates as $key => $update) {
            if ($update->response != 'upgrade') {
                unset($updates[$key]);
                continue;
            }
            $updates[$key]->diff_data = array(
                'name'            => 'WordPress Core',
                'current_version' => $wp_version,
                'new_version'     => $update->current,
                'info_url'        => 'http://www.wordpress.org',
                'download_url'    => $update->packages->full,
                'author'          => 'WordPress.com',
                'install_link'    => $this->build_upgrade_link('core', array(
                    'version' => $update->current,
                    'locale'  => $update->locale)
                )
            );
        }
        return $updates;
    }

    /**
     * Returns available plugin updates
     * @return array
     */
    public function check_plugin_updates() {
        do_action('wp_update_plugins');
        $updates = get_plugin_updates();
        foreach ($updates as $plugin_key => $update) {
            $updates[$plugin_key]->diff_data = array(
                'name'            => $update->Name,
                'current_version' => $update->Version,
                'new_version'     => $update->update->new_version,
                'info_url'        => $update->update->url,
                'download_url'    => $update->update->package,
                'author'          => $update->Author ? : $update->AuthorName,
                'install_link'    => $this->build_upgrade_link('plugin', array('plugins' => $plugin_key))
            );
        }
        return $updates;
    }

    /**
     * Returns available theme updates
     * @return array
     */
    public function check_theme_updates() {
        do_action('wp_update_themes');
        $updates = get_theme_updates();
        foreach ($updates as $theme_key => $update) {
            $updates[$theme_key]->diff_data = array(
                'name'            => $update->get('Name'),
                'current_version' => $update->get('Version'),
                'new_version'     => $update->update['new_version'],
                'info_url'        => $update->update['url'],
                'download_url'    => $update->update['package'],
                'author'          => $update->get('Author'),
                'install_link'    => $this->build_upgrade_link('theme', array('themes' => $theme_key))
            );
        }
        return $updates;
    }

    /**
     * Gathers updates from necessary sources
     * @return array updates
     */
    public function gather_updates() {
        $updates = array_filter(array(
            'core'    => $this->check_core_updates(),
            'plugins' => $this->check_plugin_updates(),
            'themes'  => $this->check_theme_updates()
            ), function($e) {
                return !empty($e);
            });
        return $updates;
    }

    /**
     * Parses updates to create an email content
     * @param array $updates
     * @return string Email html content
     */
    protected function prepare_email_content(array $updates) {
        $updates_exist = false;
        $group_updates = array(
            'plugins' => array(),
            'themes'  => array()
        );
        foreach ($updates as $type => $block) {
            if (!empty($block)) {
                $updates_exist = true;
                foreach ($block as $update_key => $update) {
                    $group_updates[$type][] = $update_key;
                }
            }
        }
        foreach ($group_updates as $key => $update_key) {
            if (empty($update_key)) {
                unset($group_updates[$key]);
                continue;
            }
            $group_updates[$key] = $this->build_upgrade_link(rtrim($key, 's'), array($key => join(',', $update_key)));
        }
        ob_start();
        include __DIR__ . '/views/email-stub.php';
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Sends an email with notification and writes the email content to a log
     */
    public function send_update_notification() {
        $available_updates = $this->gather_updates();
        $content = $this->prepare_email_content($available_updates);
        $email = get_option($this->plugin_slug . '_notification_email');
        if (!empty($email)) {
            add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
            $sent = wp_mail($email, get_bloginfo('name') . ' ' . __('Update Report'), $content, 'Content-type: text/html');
            remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        }
        // Logging purposes
        echo ($sent ? 'Sent' : 'Not sent') . ' to ' . $email;
        $file = fopen('update_notification.txt', 'a+');
        fwrite($file, "Sending notification email {$sent} to {$email}\n{$content}\n\n");
        fclose($file);
    }

    /**
     * Creates an upgrade link
     * @param string $type
     * @param array $params
     * @return string
     */
    protected function build_upgrade_link($type, array $params = array()) {
        $url = network_admin_url('update-core.php')
            . "?action=do-{$type}-upgrade"
//            . '&_wpnonce=' . wp_create_nonce('upgrade-core') // Can't generate a real nonce because users differ
            . '&' . http_build_query($params);
        return $url;
    }

    public function set_html_content_type() {
        return 'text/html';
    }

}
