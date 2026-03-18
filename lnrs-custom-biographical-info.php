<?php
/**
 * Plugin Name: LNRS Custom Biographical Info
 * Description: A plugin to customize the biographical info field in WordPress user profiles.
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class LNRS_Custom_Biographical_Info {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        add_action('show_user_profile', array($this, 'add_gutenberg_bio_field'));
        add_action('edit_user_profile', array($this, 'add_gutenberg_bio_field'));
        add_action('personal_options_update', array($this, 'save_gutenberg_bio_field'));
        add_action('edit_user_profile_update', array($this, 'save_gutenberg_bio_field'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_editor_assets'));
        add_filter('get_the_author_description', array($this, 'filter_author_bio'), 10, 2);
        add_filter('get_user_metadata', array($this, 'filter_user_description'), 10, 4);
        add_filter('author_bio', array($this, 'filter_author_bio_output'), 10, 2);
        add_action('admin_head-profile.php', array($this, 'hide_default_bio_field'));
        add_action('admin_head-user-edit.php', array($this, 'hide_default_bio_field'));
        
        // Prevent WordPress from auto-formatting the bio content
        add_filter('pre_user_description', array($this, 'prevent_bio_formatting'));
        add_filter('get_the_author_user_description', array($this, 'prevent_bio_formatting'));
    }
    
    public function enqueue_editor_assets($hook) {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }
        
        // Enqueue media scripts for TinyMCE
        wp_enqueue_media();
        wp_enqueue_editor();
    }
    
    public function add_gutenberg_bio_field($user) {
        $bio_content = get_user_meta($user->ID, 'lnrs_gutenberg_bio', true);
        ?>
        <h3><?php _e('Biographical Info (Rich Content)', 'lnrs-bio'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="lnrs_gutenberg_bio"><?php _e('Bio Content'); ?></label></th>
                <td>
                    <?php
                    wp_editor($bio_content, 'lnrs_gutenberg_bio', array(
                        'wpautop' => false,  // Disable automatic paragraph formatting
                        'media_buttons' => true,
                        'textarea_name' => 'lnrs_gutenberg_bio',
                        'textarea_rows' => 10,
                        'teeny' => false,
                        'tinymce' => array(
                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                            'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                            'content_css' => get_stylesheet_uri(),
                            'forced_root_block' => 'p',  // Force p tags to be created
                            'force_p_newlines' => true,
                            'remove_redundant_brs' => false
                        ),
                        'quicktags' => array(
                            'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'
                        )
                    ));
                    ?>
                    <p class="description"><?php _e('Use the rich text editor to create formatted biographical content with headings, lists, and more.'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_gutenberg_bio_field($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (isset($_POST['lnrs_gutenberg_bio'])) {
            $content = $_POST['lnrs_gutenberg_bio'];
            
            // For users who can edit posts, allow unfiltered HTML
            if (current_user_can('unfiltered_html')) {
                $filtered_content = $content;
            } else {
                // For other users, allow more HTML tags including headings and formatting
                $allowed_tags = wp_kses_allowed_html('post');
                $allowed_tags['h1'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['h2'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['h3'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['h4'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['h5'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['h6'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['p'] = array('id' => array(), 'class' => array(), 'style' => array());
                $allowed_tags['br'] = array();
                
                $filtered_content = wp_kses($content, $allowed_tags);
            }
            
            update_user_meta($user_id, 'lnrs_gutenberg_bio', $filtered_content);
        }
    }
    
    public function filter_author_bio($description, $user_id = null) {
        if (!$user_id) {
            global $authordata;
            if ($authordata) {
                $user_id = $authordata->ID;
            }
        }
        
        if ($user_id) {
            $gutenberg_bio = get_user_meta($user_id, 'lnrs_gutenberg_bio', true);
            if (!empty($gutenberg_bio)) {
                // Return the HTML content without additional processing
                return $gutenberg_bio;
            }
        }
        
        return $description;
    }
    
    public function filter_user_description($value, $object_id, $meta_key, $single) {
        if ($meta_key === 'description' && $single) {
            $gutenberg_bio = get_user_meta($object_id, 'lnrs_gutenberg_bio', true);
            if (!empty($gutenberg_bio)) {
                // Return the raw HTML content
                return array($gutenberg_bio);
            }
        }
        
        return $value;
    }
    
    public function filter_author_bio_output($bio, $user_id = null) {
        if ($user_id) {
            $gutenberg_bio = get_user_meta($user_id, 'lnrs_gutenberg_bio', true);
            if (!empty($gutenberg_bio)) {
                // Return raw HTML content without any additional processing
                // Remove any WordPress filters that might strip HTML
                remove_filter('the_content', 'wpautop');
                return $gutenberg_bio;
            }
        }
        return $bio;
    }
    
    public function prevent_bio_formatting($content) {
        // Don't let WordPress auto-format the bio content
        return $content;
    }
    
    public function hide_default_bio_field() {
        ?>
        <style>
        .user-description-wrap {
            display: none !important;
        }
        </style>
        <script>
        jQuery(document).ready(function($) {
            // Hide the default biographical info field
            $('tr.user-description-wrap').hide();
            $('tr').has('textarea[name="description"]').hide();
            $('label[for="description"]').closest('tr').hide();
        });
        </script>
        <?php
    }
}

new LNRS_Custom_Biographical_Info();