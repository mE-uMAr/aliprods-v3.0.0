<?php
/**
 * Plugin Name: AliProds
 * Plugin URI: https://meharumar.codes
 * Description: Import AliExpress products to WooCommerce with advanced features including category browsing, multiple images, videos, and AI-generated descriptions
 * Version: 3.0.0
 * Author: Mehar Umar
 * Author URI: https://meharumar.codes
 * License: GPL v2 or later
 * Text Domain: aliprods
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ALIPRODS_VERSION', '3.0.0');
define('ALIPRODS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALIPRODS_PLUGIN_URL', plugin_dir_url(__FILE__));

class AliProds {
    
    private $groq_api_key = "";
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_aliprods_get_product', array($this, 'ajax_get_product'));
        add_action('wp_ajax_aliprods_add_product', array($this, 'ajax_add_product'));
        add_action('wp_ajax_aliprods_get_categories', array($this, 'ajax_get_categories'));
        add_action('wp_ajax_aliprods_get_category_products', array($this, 'ajax_get_category_products'));
        
        // Check if WooCommerce is active
        add_action('admin_notices', array($this, 'check_woocommerce'));
    }
    
    public function init() {
        load_plugin_textdomain('aliprods', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>';
            echo __('AliProds requires WooCommerce to be installed and active.', 'aliprods');
            echo '</p></div>';
        }
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('AliProds', 'aliprods'),
            __('AliProds', 'aliprods'),
            'manage_options',
            'aliprods',
            array($this, 'admin_page'),
            'dashicons-cart',
            30
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_aliprods') {
            return;
        }
        
        wp_enqueue_style('aliprods-admin', ALIPRODS_PLUGIN_URL . 'assets/admin.css', array(), ALIPRODS_VERSION);
        wp_enqueue_script('aliprods-admin', ALIPRODS_PLUGIN_URL . 'assets/admin.js', array('jquery'), ALIPRODS_VERSION, true);
        
        wp_localize_script('aliprods-admin', 'aliprods_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aliprods_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'aliprods'),
                'error' => __('Error occurred', 'aliprods'),
                'success' => __('Product added successfully!', 'aliprods'),
                'invalid_url' => __('Please enter a valid AliExpress product URL', 'aliprods'),
                'fetching_from_aliexpress' => __('Fetching from AliExpress...', 'aliprods'),
                'generating_description' => __('Fetching description...', 'aliprods')
            )
        ));
    }
    
    public function admin_page() {
        include ALIPRODS_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    public function ajax_get_product() {
        check_ajax_referer('aliprods_nonce', 'nonce');
        
        $product_url = sanitize_url($_POST['product_url']);
        
        // Use the product-info.php file
        include_once ALIPRODS_PLUGIN_DIR . 'includes/product-info.php';
        
        $product_info = new AliProds_Product_Info($this->groq_api_key);
        $result = $product_info->get_product_info($product_url);
        
        if (isset($result['error'])) {
            wp_send_json_error(array('message' => $result['error']));
        } else {
            wp_send_json_success($result);
        }
    }
    
    public function ajax_get_categories() {
        check_ajax_referer('aliprods_nonce', 'nonce');
        
        // Use the category-api.php file
        include_once ALIPRODS_PLUGIN_DIR . 'includes/category-api.php';
        
        $category_api = new AliProds_Category_API();
        $result = $category_api->get_categories();
        
        if (isset($result['error'])) {
            wp_send_json_error(array('message' => $result['error']));
        } else {
            wp_send_json_success($result);
        }
    }
    
    public function ajax_get_category_products() {
        check_ajax_referer('aliprods_nonce', 'nonce');
        
        $category_ids = sanitize_text_field($_POST['category_ids']);
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $page_no = intval($_POST['page_no'] ?? 1);
        $page_size = intval($_POST['page_size'] ?? 20);
        
        // Use the category-api.php file
        include_once ALIPRODS_PLUGIN_DIR . 'includes/category-api.php';
        
        $category_api = new AliProds_Category_API();
        $result = $category_api->get_category_products($category_ids, $keywords, $page_no, $page_size);
        
        if (isset($result['error'])) {
            wp_send_json_error(array('message' => $result['error']));
        } else {
            wp_send_json_success($result);
        }
    }
    public function ajax_get_keyword_products() {
        check_ajax_referer('aliprods_nonce', 'nonce');
        
        $keywords = sanitize_text_field($_POST['keywords'] ?? '');
        $page_no = intval($_POST['page_no'] ?? 1);
        $page_size = intval($_POST['page_size'] ?? 20);
        
        // Use the category-api.php file
        include_once ALIPRODS_PLUGIN_DIR . 'includes/category-api.php';
        
        $category_api = new AliProds_Category_API();
        $result = $category_api->get_keyword_products($keywords, $page_no, $page_size);
        
        if (isset($result['error'])) {
            wp_send_json_error(array('message' => $result['error']));
        } else {
            wp_send_json_success($result);
        }
    }
    
    public function ajax_add_product() {
        check_ajax_referer('aliprods_nonce', 'nonce');
        
        if (!class_exists('WC_Product_External')) {
            wp_send_json_error(array('message' => __('WooCommerce not found', 'aliprods')));
        }
        
        $title = sanitize_text_field($_POST['title']);
        $price = floatval($_POST['price']);
        $images = json_decode(stripslashes($_POST['images']), true);
        $video_url = esc_url_raw($_POST['video_url']);
        $description = wp_kses_post($_POST['description']);
        $affiliate_link = esc_url_raw($_POST['affiliate_link']);
        $button_text = sanitize_text_field($_POST['button_text']) ?: 'Buy on AliExpress';
        
        // Create WooCommerce external product
        $product = new WC_Product_External();
        $product->set_name($title);
        $product->set_regular_price($price);
        $product->set_description($description);
        $product->set_short_description($description);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');
        
        // Set external product URL (affiliate link) and button text
        $product->set_product_url($affiliate_link);
        $product->set_button_text($button_text);
        
        // Add video URL to product meta if available
        if ($video_url) {
            $product->add_meta_data('_aliprods_video_url', $video_url);
        }

        // Add custom meta for tracking
        $product->add_meta_data('_aliprods_affiliate_link', $affiliate_link);
        $product->add_meta_data('_aliprods_imported', 'yes');
        
        $product_id = $product->save();
        
        // Handle product images
        if ($images && $product_id && is_array($images)) {
            $this->set_product_images($product_id, $images);
        }
        
        if ($product_id) {
            wp_send_json_success(array(
                'message' => __('External product added successfully!', 'aliprods'),
                'product_id' => $product_id,
                'edit_url' => admin_url('post.php?post=' . $product_id . '&action=edit')
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to create product', 'aliprods')));
        }
    }
    
    private function set_product_images($product_id, $image_urls) {
        $upload_dir = wp_upload_dir();
        $attachment_ids = array();
        
        foreach ($image_urls as $index => $image_url) {
            $image_data = wp_remote_get($image_url);
            
            if (is_wp_error($image_data)) {
                continue;
            }
            
            $filename = 'aliprods_' . $product_id . '_' . ($index + 1) . '.jpg';
            $file = $upload_dir['path'] . '/' . $filename;
            file_put_contents($file, wp_remote_retrieve_body($image_data));
            
            $attachment = array(
                'post_mime_type' => 'image/jpeg',
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attachment_id = wp_insert_attachment($attachment, $file, $product_id);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            $attachment_ids[] = $attachment_id;
            
            // Set first image as featured image
            if ($index === 0) {
                set_post_thumbnail($product_id, $attachment_id);
            }
        }
        
        // Set product gallery
        if (count($attachment_ids) > 1) {
            $gallery_ids = array_slice($attachment_ids, 1); // Remove first image (featured)
            update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
        }
        
        return $attachment_ids;
    }
}

// Include frontend functionality
if (!is_admin()) {
    include_once ALIPRODS_PLUGIN_DIR . 'includes/frontend-display.php';
}

// Initialize the plugin
new AliProds();
?>
