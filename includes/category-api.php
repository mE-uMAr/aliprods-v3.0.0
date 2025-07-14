<?php
/**
 * AliExpress Category and Product Query Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AliProds_Category_API {
    
    private $app_key = "";
    private $app_secret = "";
    private $tracking_id = "";
    
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
    
    public function get_categories() {
        $timestamp = (string) round(microtime(true) * 1000);
        
        $params = array(
            "method" => "aliexpress.affiliate.category.get",
            "app_key" => $this->app_key,
            "sign_method" => "md5",
            "timestamp" => $timestamp
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
        // error_log($response);
        
        if ($httpCode !== 200) {
            return [
                "error" => "Categories API failed with status: " . $httpCode
            ];
        }
        
        $data = json_decode($response, true);
        
        try {
            if (isset($data["aliexpress_affiliate_category_get_response"]["resp_result"]["result"]["categories"]["category"])) {
                return $data["aliexpress_affiliate_category_get_response"]["resp_result"]["result"]["categories"]["category"];
            } else {
                return ["error" => "No categories found in response"];
            }

        } catch (Exception $e) {
            return [
                "error" => "Failed to parse categories: " . $e->getMessage()
            ];
        }
    }
    
    public function get_category_products($category_ids, $keywords = '', $page_no = 1, $page_size = 20) {
    $timestamp = strval(time() * 1000);
    
    $params = array(
        "method" => "aliexpress.affiliate.product.query",
        "app_key" => $this->app_key,
        "sign_method" => "md5",
        "timestamp" => $timestamp,
        "category_ids" => $category_ids,
        "target_currency" => "PKR",
        "target_language" => "EN",
        "tracking_id" => $this->tracking_id,
        "page_no" => $page_no,
        "page_size" => $page_size
    );

    if (!empty($keywords)) {
        $params["keywords"] = $keywords;
    }

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
            "error" => "Products API failed with status: " . $httpCode
        ];
    }

    $data = json_decode($response, true);

    try {
        $result = $data["aliexpress_affiliate_product_query_response"]["resp_result"]["result"] ?? null;

        if ($result && isset($result["products"]["product"])) {
            return [
                "products" => $result["products"]["product"], // ✅ flattened array
                "current_page_no" => $result["current_page_no"] ?? $page_no,
                "total_page_no" => $result["total_page_no"] ?? 1,
                "current_record_count" => $result["current_record_count"] ?? 0,
                "total_record_count" => $result["total_record_count"] ?? 0,
            ];
        } else {
            return [
                "error" => "No products found in response"
            ];
        }
    } catch (Exception $e) {
        return [
            "error" => "Failed to parse products: " . $e->getMessage()
        ];
    }
}

public function get_keyword_products( $keywords, $page_no = 1, $page_size = 20) {
    $timestamp = strval(time() * 1000);
    
    $params = array(
        "method" => "aliexpress.affiliate.product.query",
        "app_key" => $this->app_key,
        "sign_method" => "md5",
        "timestamp" => $timestamp,
        "keywords" => $keywords,
        "target_currency" => "PKR",
        "target_language" => "EN",
        "tracking_id" => $this->tracking_id,
        "page_no" => $page_no,
        "page_size" => $page_size
    );

    if (!empty($keywords)) {
        $params["keywords"] = $keywords;
    }

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
            "error" => "Products API failed with status: " . $httpCode
        ];
    }

    $data = json_decode($response, true);

    try {
        $result = $data["aliexpress_affiliate_product_query_response"]["resp_result"]["result"] ?? null;

        if ($result && isset($result["products"]["product"])) {
            return [
                "products" => $result["products"]["product"], // ✅ flattened array
                "current_page_no" => $result["current_page_no"] ?? $page_no,
                "total_page_no" => $result["total_page_no"] ?? 1,
                "current_record_count" => $result["current_record_count"] ?? 0,
                "total_record_count" => $result["total_record_count"] ?? 0,
            ];
        } else {
            return [
                "error" => "No products found in response"
            ];
        }
    } catch (Exception $e) {
        return [
            "error" => "Failed to parse products: " . $e->getMessage()
        ];
    }
}

}
?>
