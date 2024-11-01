<?php

    class ilm_SimpleSettings
    {

        /**
         * Plugin Name: Simple Settings
         * Plugin URI: http://www.ilikemustard.com
         * Description: A WordPress plugin to create, modify, and retrieve basic settings for use in templates, posts, and pages.
         * Version: 1.2
         *
         * Author: Jimmy K. <jimmy@ilikemustard.com>
         * Author URI: http://www.ilikemustard.com
         */

        /* ================================================== */
        /* Variables
        /* ================================================== */

        // Hold our settings..
        public $aSettings = array();

        // The current admin notice text. We have to do it this
        // way because you can't pass arguments to the 'admin_notice'
        // hook. Bah!
        private $sAdminNoticeText = '';

        /* ================================================== */
        /* Constructor & Initialization
        /* ================================================== */

        /**
         * The constructor for this plugin.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function __construct()
        {

            // Set our plugin settings..
            $this->aSettings['namespace'] = 'ilmss';
            $this->aSettings['dir'] = plugin_dir_url(__FILE__);
            $this->aSettings['version'] = '1.1';

            // Initialize the goods..
            add_action('init', array($this, 'init'), 1);
            add_action('admin_init', array($this, 'initAdmin'), 1);
            add_action('shutdown', array($this, 'shutdown'), 1);

            // Enqueue the scripts and styles..
            add_action('wp_print_scripts', array($this, 'enqueueScripts'));

            // Keep the database happy and content..
            add_action('admin_head-edit.php', array($this, 'keepDatabaseHappy'));

            // Oh, you know this is clever! :P
            $this->aSettings['debug'] = (boolean) $this->getSetting('debug');

        }

        /**
         * Triggered when the plugin is initialized.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function init()
        {

            // Start the output buffer (for token replacement)..
            $this->doHeaderActions();

            // Set the labels for our post type..
            $aLabels = array(
                'name' => __('Simple Settings', $this->aSettings['namespace']),
                'singular_name' => __('Simple Settings', $this->aSettings['namespace']),
                'add_new' => __('Add New', $this->aSettings['namespace']),
                'add_new_item' => __('Add New Setting', $this->aSettings['namespace']),
                'edit_item' => __('Edit Setting', $this->aSettings['namespace']),
                'view_item' => __('View Setting', $this->aSettings['namespace']),
                'search_items' => __('Search Settings', $this->aSettings['namespace']),
                'not_found' => __('No settings found!', $this->aSettings['namespace']),
                'not_found_in_trash' => __('No settings found in trash!', $this->aSettings['namespace']),
            );

            // Set the options for our post type..
            $aPostType = array(
                'labels' => $aLabels,
                'public' => true,
                'show_ui' => true,
                '_builtin' => false,
                'capability_type' => 'page',
                'hierarchical' => true,
                'rewrite' => false,
                'query_var' => $this->aSettings['namespace'],
                'supports' => array('title'),
                'show_in_menu' => true,
            );

            // Register our post type.
            register_post_type($this->aSettings['namespace'], $aPostType);

        }

        /**
         * Triggered when the plugin shuts down.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function shutdown() {}

        /* ================================================== */
        /* Administration
        /* ================================================== */

        /**
         * Initialize the admin interface.
         *
         * @return void
         * @see add_action
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function initAdmin()
        {

            // Add a metabox..
            add_meta_box('valuesMeta', 'Values', array($this, 'addValueMetaBox'), $this->aSettings['namespace'], 'normal', 'low');

            // Listen for save..
            add_action('save_post', array($this, 'saveSettings'));

            // Update the columns..
            add_action('manage_' . $this->aSettings['namespace'] . '_posts_columns', array($this, 'setCustomCols'));
            add_filter('manage_' . $this->aSettings['namespace'] . '_posts_custom_column', array($this, 'formatCustomCols'));

            // Update the messages..
            add_filter('post_updated_messages', array($this, 'setMessages'));

            // Swap default WordPress text with custom text..
            add_filter('gettext', array($this, 'setCustomText'), 1, 3);

            // Enqueue admin scripts/styles..
            add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));

        }

        /**
         * Enqueue the JavaScript and CSS.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function enqueueScripts()
        {

            global $post_type;

            if ($post_type == $this->aSettings['namespace']) {

                // Enqueue the scripts..
                wp_register_script($this->aSettings['namespace'], $this->aSettings['dir'] . '/scripts.js');
                wp_enqueue_script($this->aSettings['namespace']);

                // Enqueue the styles..
                wp_register_style($this->aSettings['namespace'], $this->aSettings['dir'] . '/styles.css');
                wp_enqueue_style($this->aSettings['namespace']);

            }

        }

        /**
         * Enqueue the JavaScript and CSS for the admin interface.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function enqueueAdminScripts()
        {

            global $wp_version;

            /* ================================================== */
            /* Set the icon(s)!
            /* I wanted to do this in the 'styles.css', but the
            /* directory name would be unreliable. =/
            /* ================================================== */

            // Default title icon position..
            $sDefaultTitleIconPosition = '2px 0 / 76px 38px';

            // Default icon position..
            $sIconPosition = '-21px 3px / 48px 22px';
            $sIconHoverPosition = '3px 3px';

            if ($wp_version >= '3.7') {

                // Post-3.7 icon position..
                $sIconPosition = '-23px 4px / 54px 25px';
                $sIconHoverPosition = '4px 4px';

            }

            echo '
                <style type="text/css" media="all">

                    /* Menu Icon */
                    #menu-posts-' . $this->aSettings['namespace'] . ' .wp-menu-image {
                        background: url(\'' . $this->aSettings['dir'] . 'images/icons/plugin.png\') ' . $sIconPosition . ' no-repeat !important;
                    }

                    /* Menu Icon -> Font Icon (Content) */
                    #menu-posts-' . $this->aSettings['namespace'] . ' .wp-menu-image:before {
                        content: \'\' !important;
                    }

                    /* Menu Icon -> Hover */
                    #menu-posts-' . $this->aSettings['namespace'] . '.wp-menu-open .wp-menu-image,
                    #menu-posts-' . $this->aSettings['namespace'] . ':hover .wp-menu-image {
                        background-position: ' . $sIconHoverPosition . ' !important;
                    }

                    /* Title Icon */
                    /* Apparently they removed this completely in 3.7. */
                    #icon-edit.icon32-posts-' . $this->aSettings['namespace'] . ' {
                        background: url(\'' . $this->aSettings['dir'] . 'images/icons/plugin.png\') ' . $sDefaultTitleIconPosition . ' no-repeat !important;
                    }

                </style>
            ';

        }

        /**
         * Replace sections of default WordPress text with something better.
         *
         * @param string $sTranslatedText The translated text.
         * @param string $sUntranslatedText The untranslated text.
         * @param string $sDomain The text domain. (No idea what this does, tbh.)
         * @return string
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function setCustomText($sTranslatedText, $sUntranslatedText, $sDomain)
        {

            if ($sUntranslatedText == 'Move to Trash') {

                // Found the text we want to replace with something else.
                // Replace it with something more appropriate. (Currently no
                // change. However, could easily be changed to 'Delete'.)
                return 'Move to Trash';

            }

            // Return the default translation..
            return $sTranslatedText;

        }

        /**
         * Set the messages.
         *
         * @return array
         * @see add_action
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function setMessages()
        {

            // Set the messages for our post type..
            $aValues[$this->aSettings['namespace']] = array(
                0 => '', // Unused. Messages start at 1.
                1 => 'Setting updated! Keep up the good work, buddy!',
                2 => 'Setting updated! Fantastic!',
                3 => 'Setting deleted. "Hasta la vista, baby!"',
                4 => 'Setting updated! Man, I love updates!',
                5 => 'Revision!',
                6 => 'Setting saved. Right on!',
                7 => 'BOOM! Saved.',
                8 => 'Submitted!',
                9 => 'Scheduled!',
                10 => 'Draft updated!',
            );

            return $aValues;

        }

        /**
         * Add the 'value' meta box to the admin interface.
         *
         * @return void
         * @see add_meta_box
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function addValueMetaBox()
        {

            global $post;

            // Get the values..
            $aMetaValues = get_post_custom($post->ID);
            $sType = $aMetaValues['type'][0];
            $sValue = $aMetaValues['value'][0];

            $sFormHTML = '
                <label>Type:</label>
                <div>
                    <select id="' . $this->aSettings['namespace'] . '_type" name="' . $this->aSettings['namespace'] . '_type" class="type">
                        <option value=""></option>
                        <option value="textarea" ' . ($sType == 'textarea' ? 'selected' : '') . '>Textarea</option>
                        <option value="boolean" ' . ($sType == 'boolean' ? 'selected' : '') . '>Boolean</option>
                    </select>
                </div>
                <!-- valueWrapper -->
                <div class="valueWrapper">
                    <label>Value:</label>
                    <!-- textAreaValueWrapper -->
                    <div class="textareaValueWrapper">
                        <textarea cols="50" rows="5" name="' . $this->aSettings['namespace'] . '_textareaValue" class="value">' . $sValue . '</textarea>
                        <div class="clearer"></div>
                    </div><!-- /textAreaValueWrapper -->
                    <!-- booleanValueWrapper -->
                    <div class="booleanValueWrapper">
                        <label><input type="radio" name="' . $this->aSettings['namespace'] . '_booleanValue" value="true" ' . ($sValue == 'true' ? 'checked' : '') . ' /><span>True</span></label>
                        <label><input type="radio" name="' . $this->aSettings['namespace'] . '_booleanValue" value="false" ' . ($sValue == 'false' ? 'checked' : '') . ' /><span>False</span></label>
                        <div class="clearer"></div>
                    </div><!-- /booleanValueWrapper -->
                </div>
            ';

            echo $sFormHTML;

        }

        /**
         * Save the settings.
         *
         * @return void
         * @see add_action
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function saveSettings()
        {

            global $post;

            // Sanitize the type..
            $sType = $_POST[$this->aSettings['namespace'] . '_type'];
            $sType = stripslashes($sType);
            $sType = trim($sType);

            if ($sType == 'textarea') {
                $sValue = $_POST[$this->aSettings['namespace'] . '_textareaValue'];
            } else if ($sType == 'boolean') {
                $sValue = $_POST[$this->aSettings['namespace'] . '_booleanValue'];
            } else {
                $sValue = '';
            }

            // Sanitize the value..
            $sValue = $sValue;
            $sValue = stripslashes($sValue);
            $sValue = trim($sValue);

            // Sanitize the slug..
            $sSlug = $this->sanitizeSlug($post->post_title);

            // Update the post meta..
            update_post_meta($post->ID, 'type', $sType);
            update_post_meta($post->ID, 'value', $sValue);
            update_post_meta($post->ID, 'slug', $sSlug);

        }

        /* ================================================== */
        /* Custom Columns
        /* ================================================== */

        /**
         * Set the custom column data.
         *
         * @param array $aDefaults The default column data.
         * @return array
         * @see add_action
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function setCustomCols($aDefaults)
        {

            // Remove the default 'date' column..
            unset($aDefaults['date']);

            // Add our custom column values..
            $aDefaults['slug'] = 'Slug';
            $aDefaults['type'] = 'Type';
            $aDefaults['value'] = 'Value';

            return $aDefaults;

        }

        /**
         * Format the custom column data.
         *
         * @param string $sColumn The index of the column to format.
         * @return string
         * @see add_filter
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function formatCustomCols($sColumn)
        {

            global $post;

            // Get the meta values..
            $aMetaValues = get_post_custom();

            // Hold the output..
            $sValue = '';

            switch (strtolower($sColumn)) {
            case 'slug':
                $sValue = $aMetaValues['slug'][0];
                break;
            case 'type':
                $sValue = $aMetaValues['type'][0];
                break;
            case 'value':
                $sValue = $aMetaValues['value'][0];
                break;
            }

            if (!empty($sValue)) {
                // Make the value safe for output..
                $sValue = htmlentities($sValue);
                $sValue = empty($sValue) ? '&nbsp;' : $sValue;
            }

            echo $sValue;

        }

        /**
         * Do the header actions.
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function doHeaderActions()
        {

            ob_start(array($this, 'doFooterActions'));

        }

        /**
         * Do the footer actions!
         *
         * @return string
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function doFooterActions($sBuffer)
        {

            global $oSimpleSettings;

            if (!is_admin()) {

                // Replace tokens on non-admin pages..
                $sReplacedBuffer = $oSimpleSettings->replaceTokens($sBuffer);
                return $sReplacedBuffer;

            }

            // Don't replace tokens on the admin interface..
            return $sBuffer;

        }

        /**
         * Set the admin notice.
         *
         * @param string $sMessage The message to display.
         * @return string
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        private function setAdminNotice($sMessage) {

            $this->sAdminNoticeText = $sMessage;

        }

        /**
         * Show the admin notice.
         *
         * @return void
         * @see add_action
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function showAdminNotice()
        {

            echo '<div class="updated">';
            echo '<p>';
            _e($this->sAdminNoticeText);
            echo '</p>';
            echo '</div>';

            unset($this->sAdminNoticeText);

        }

        /* ================================================== */
        /* Helper Functions
        /* ================================================== */

        /**
         * Search and replace the token values for each setting.
         *
         * @param string $sSubject The subject to search and replace tokens on.
         * @return string
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        protected function replaceTokens($sSubject)
        {

            // Get the published posts..
            $aPublishedPosts = get_posts(array(
                'post_type' => $this->aSettings['namespace'],
                'post_status' => 'publish',
                'posts_per_page' => 5000,
            ));

            foreach ($aPublishedPosts as $oPost) {

                // Get the meta values..
                $aMetaValues = get_post_custom($oPost->ID);

                // Replace the buffer..
                $sSubject = str_replace('{' . $aMetaValues['slug'][0] . '}', $aMetaValues['value'][0], $sSubject);

            }

            return $sSubject;

        }

        /**
         * Sanitize a slug.
         *
         * @param string $sValue The value to sanitize.
         * @return string
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        protected function sanitizeSlug($sValue)
        {

            $sValue = trim($sValue);
            $sValue = preg_replace("/[^A-Za-z0-9 _]/", '', $sValue);
            $sValue = strtolower($sValue);
            $sValue = str_replace(' ', '_', $sValue);

            return $sValue;

        }

        /**
         * Get a setting from the database.
         *
         * @return string
         * @see ilmss_get_setting, get_setting
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function getSetting($sSlug = '')
        {

            // Sanitize the slug..
            $sSlug = $this->sanitizeSlug($sSlug);

            if (empty($sSlug)) {
                // Exit if the requested slug is empty..
                return '';
            }

            // Get the published posts..
            $aPublishedPosts = get_posts(array(
                'post_type' => $this->aSettings['namespace'],
                'post_status' => 'publish',
                'posts_per_page' => 5000,
            ));

            foreach ($aPublishedPosts as $oPost) {

                // Get the meta values..
                $aMetaValues = get_post_custom($oPost->ID);

                if ($sSlug == $aMetaValues['slug'][0]) {

                    if ($aMetaValues['type'][0] == 'boolean') {

                        // BOOLEAN
                        return filter_var($aMetaValues['value'][0], FILTER_VALIDATE_BOOLEAN);

                    }

                    // TEXT
                    return $aMetaValues['value'][0];

                }

            }

            return '';

        }

        /**
         * Delete any posts that belong to this plugin and have the 'auto-draft'
         * status. This will help keep our database 'footprint' low. :)
         *
         * @return void
         * @author Jimmy K. <jimmy@ilikemustard.com>
         */

        public function keepDatabaseHappy()
        {

            // Get the garbage posts..
            $aGarbagePosts = get_posts(array(
                'post_type' => $this->aSettings['namespace'],
                'post_status' => 'auto-draft',
                'posts_per_page' => 99999, // Arbitrary value..
            ));

            $i = 0;

            foreach ($aGarbagePosts as $oPost) {

                // Forcefully this post because it's, well, garbage..
                wp_delete_post($oPost->ID, true);
                $i++;

            }

            if ($this->aSettings['debug'] && $i > 0) {

                // Output a debug notice..
                $this->sAdminNoticeText = $i . ' garbage posts deleted.';
                add_action('admin_notices', array($this, 'showAdminNotice'));

            }

            // There is a weird bug where if you go to add a new setting,
            // insert a title, set the values, then change the title, the
            // slug that's generated is for the OLD title instead of the
            // current title. So! We're going to loop through the settings
            // and update any mismatching slugs.

            // Get all the posts..
            $aAllPosts = get_posts(array(
                'post_type' => $this->aSettings['namespace'],
                'post_status' => 'publish',
                'post_per_page' => 99999, // Arbitrary value..
            ));

            foreach ($aAllPosts as $oPost) {

                // Get the meta values..
                $aMetaValues = get_post_custom($oPost->ID);

                // Sanitize the title-based slug..
                $sSlugFromTitle = $this->sanitizeSlug($oPost->post_title);

                if ($sSlugFromTitle != $aMetaValues['slug'][0]) {

                    // Title-based slug and stored slug don't match; update..
                    update_post_meta($oPost->ID, 'slug', $sSlugFromTitle);

                }

            }

        }

    }

    // Create an instance of our plugin object.
    $oSimpleSettings = new ilm_SimpleSettings();

    /**
     * Local scope function to get a setting from the database.
     *
     * @return string
     * @author Jimmy K. <jimmy@ilikemustard.com>
     */

    function ilmss_get_setting($sSlug)
    {

        global $oSimpleSettings;

        // Get the setting.
        $sValue = $oSimpleSettings->getSetting($sSlug);

        return $sValue;

    }

    /* ================================================== */
    /* Procedural Stuff
    /* ================================================== */

    if (!function_exists('get_setting')) {

        /**
         * Local scope helper function to get a setting from the database.
         * @return string
         * @author Jimmy K.
         */

        function get_setting($sSlug)
        {

            return ilmss_get_setting($sSlug);

        }

    }
