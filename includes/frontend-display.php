<?php
/**
 * Frontend display functionality for AliProds
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AliProds_Frontend {
    
    public function __construct() {
        // Add video to product gallery
        add_action('woocommerce_product_thumbnails', array($this, 'add_product_video'), 25);
        
        // Add video to single product page
        add_action('woocommerce_single_product_summary', array($this, 'display_product_video'), 25);
        
        // Add multiple images to gallery
        add_filter('woocommerce_single_product_image_thumbnail_html', array($this, 'enhance_product_gallery'), 10, 2);
        
        // Enqueue frontend scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    public function enqueue_frontend_scripts() {
        if (is_product()) {
            wp_enqueue_style('aliprods-frontend', ALIPRODS_PLUGIN_URL . 'assets/frontend.css', array(), ALIPRODS_VERSION);
            wp_enqueue_script('aliprods-frontend', ALIPRODS_PLUGIN_URL . 'assets/frontend.js', array('jquery'), ALIPRODS_VERSION, true);
        }
    }
    
    public function add_product_video() {
        global $product;
        
        if (!$product) return;
        
        $video_url = get_post_meta($product->get_id(), '_aliprods_video_url', true);
        
        if ($video_url) {
            echo '<div class="aliprods-product-video-gallery">';
            echo '<h4>Product Video</h4>';
            echo '<div class="aliprods-video-wrapper">';
            echo '<video controls muted preload="metadata" style="width: 100%; max-width: 400px; border-radius: 8px;">';
            echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    public function display_product_video() {
        global $product;
        
        if (!$product) return;
        
        $video_url = get_post_meta($product->get_id(), '_aliprods_video_url', true);
        $is_aliprods = get_post_meta($product->get_id(), '_aliprods_imported', true);
        
        if ($video_url && $is_aliprods) {
            echo '<div class="aliprods-product-video-section">';
            echo '<h3>Product Video</h3>';
            echo '<div class="aliprods-video-wrapper">';
            echo '<video controls muted preload="metadata">';
            echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    public function enhance_product_gallery($html, $attachment_id) {
        global $product;
        
        if (!$product) return $html;
        
        $is_aliprods = get_post_meta($product->get_id(), '_aliprods_imported', true);
        
        if ($is_aliprods) {
            // Add enhanced styling for AliProds imported products
            $html = str_replace('class="', 'class="aliprods-enhanced ', $html);
        }
        
        return $html;
    }
}

// Initialize frontend functionality
new AliProds_Frontend();
?>
