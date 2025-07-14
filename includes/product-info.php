<?php
/**
 * AliExpress Product Information Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AliProds_Product_Info {
    
    private $app_key = "";
    private $app_secret = "";
    private $tracking_id = "";
    private $groq_api_key = "gsk_";
    
    public function __construct($groq_api_key = '') {
        $this->groq_api_key = $groq_api_key;
    }
    
    private function extract_product_id($url) {
        if (preg_match('/\/item\/(\d+)\.html/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function generate_signature($params) {
        $filtered_params = array_filter($params, function($value, $key) {
            return $key !== 'sign' && $value !== null;
        }, ARRAY_FILTER_USE_BOTH);
        
        ksort($filtered_params);
        
        $base_string = $this->app_secret;
        foreach ($filtered_params as $key => $value) {
            $base_string .= $key . $value;
        }
        $base_string .= $this->app_secret;
        
        return strtoupper(md5($base_string));
    }
    
    private function generate_ai_description($title, $price) {
        if (empty($this->groq_api_key)) {
            return "High-quality product imported from AliExpress. " . $title;
        }
        
        $prompt = "Create a compelling product description for an e-commerce store. 

Product Title: {$title}

Requirements:
- Write a professional, engaging description
- Highlight key features and benefits
- Use persuasive language to encourage purchases
- Keep it between 300-800 words
- Format with proper paragraphs
- Focus on value proposition
- Make it SEO-friendly

Write only the description, no additional text or formatting.";

        $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->groq_api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'model' => 'llama3-70b-8192',
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'max_tokens' => 2000,
                'temperature' => 0.7
            )),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return "High-quality product imported from AliExpress. " . $title;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }
        
        return "High-quality product imported from AliExpress. " . $title;
    }
    
    public function get_product_info($product_url) {
        $product_id = $this->extract_product_id($product_url);
        
        if (!$product_id) {
            return ["error" => "Invalid product URL"];
        }
        
        $timestamp = strval(time() * 1000);
        
        $params = array(
            "method" => "aliexpress.affiliate.productdetail.get",
            "app_key" => $this->app_key,
            "sign_method" => "md5",
            "timestamp" => $timestamp,
            "product_ids" => $product_id,
            "target_currency" => "PKR",
            "target_language" => "EN",
            "tracking_id" => $this->tracking_id
        );
        
        $params["sign"] = $this->generate_signature($params);
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api-sg.aliexpress.com/sync");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return [
                "error" => "AliExpress API failed with status: " . $httpCode
            ];
        }
        
        $data = json_decode($response, true);
        
        try {
            $product = $data["aliexpress_affiliate_productdetail_get_response"]["resp_result"]["result"]["products"]["product"][0];
            
            // Collect ALL images
            $images = array();
            
            // Add main image first
            if (isset($product["product_main_image_url"]) && !empty($product["product_main_image_url"])) {
                $images[] = $product["product_main_image_url"];
            }
            
            // Add all small images
            if (isset($product["product_small_image_urls"]["string"]) && is_array($product["product_small_image_urls"]["string"])) {
                foreach ($product["product_small_image_urls"]["string"] as $img_url) {
                    if (!empty($img_url) && !in_array($img_url, $images)) {
                        $images[] = $img_url;
                    }
                }
            }

            
            // Check for video URL
            $video_url = '';
            if (isset($product["product_video_url"]) && !empty($product["product_video_url"])) {
                $video_url = $product["product_video_url"];
            }
            
            $title = $product["product_title"];
            $price = $product["target_sale_price"];
            
            // Generate AI description automatically
            $description = $this->generate_ai_description($title, $price);
            
            return [
                "title" => $title,
                "images" => $images,
                "video_url" => $video_url,
                "price_pkr" => floatval($price),
                "affiliate_link" => $product["promotion_link"],
                "original_url" => $product_url,
                "description" => $description,
                "total_images" => count($images),
                "source" => "AliExpress"
            ];
            
        } catch (Exception $e) {
            return [
                "error" => "Failed to parse product info: " . $e->getMessage()
            ];
        }
    }
}
?>
