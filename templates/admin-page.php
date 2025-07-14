<div class="aliprods-container">
    <div class="aliprods-header">
        <div class="aliprods-logo">
            <div class="aliprods-logo-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="aliprods-logo-text">
                <h1>AliProds</h1>
                <p class="aliprods-subtitle">Advanced AliExpress Product Importer for WooCommerce</p>
            </div>
        </div>
        <div class="aliprods-author">
            <div class="aliprods-author-card">
                <div class="aliprods-author-avatar">MU</div>
                <div class="aliprods-author-info">
                    <p class="aliprods-author-name">Mehar Umar</p>
                    <a href="https://meharumar.codes" target="_blank" class="aliprods-author-link">meharumar.codes</a>
                </div>
            </div>
        </div>
    </div>

    <div class="aliprods-stats">
        <div class="aliprods-stat-card">
            <div class="aliprods-stat-icon">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="aliprods-stat-info">
                <h3>Active</h3>
                <p>Plugin Status</p>
            </div>
        </div>
        <div class="aliprods-stat-card">
            <div class="aliprods-stat-icon">
                <span class="dashicons dashicons-star-filled"></span>
            </div>
            <div class="aliprods-stat-info">
                <h3>v3.0.0</h3>
                <p>Version</p>
            </div>
        </div>
    </div>

    <!-- Section Tabs -->
    <div class="aliprods-tabs">
        <button class="aliprods-tab-btn active" data-tab="url-import">
            <span class="dashicons dashicons-admin-links"></span>
            URL Import
        </button>
        <button class="aliprods-tab-btn" data-tab="category-browse">
            <span class="dashicons dashicons-category"></span>
            Browse Categories
        </button>
    </div>

    <!-- Section A: URL Import -->
    <div id="url-import" class="aliprods-tab-content active">
        <div class="aliprods-main">
            <div class="aliprods-card aliprods-import-card">
                <div class="aliprods-card-header">
                    <div class="aliprods-card-header-content">
                        <div class="aliprods-card-icon">
                            <span class="dashicons dashicons-admin-links"></span>
                        </div>
                        <div class="aliprods-card-title">
                            <h2>Import Product by URL</h2>
                            <p>Enter an AliExpress product URL to import it with all images, videos, and auto-generated description</p>
                        </div>
                    </div>
                    <div class="aliprods-card-badge">
                        <span class="aliprods-badge aliprods-badge-primary">Section A</span>
                    </div>
                </div>
                
                <div class="aliprods-card-body">
                    <form id="aliprods-import-form">
                        <div class="aliprods-form-group">
                            <label for="product-url">
                                <span class="aliprods-label-icon">🔗</span>
                                AliExpress Product URL
                            </label>
                            <div class="aliprods-input-group">
                                <div class="aliprods-input-wrapper">
                                    <input type="url" id="product-url" name="product_url" 
                                           placeholder="https://www.aliexpress.com/item/1234567890.html" required>
                                    <div class="aliprods-input-focus"></div>
                                </div>
                                <button type="submit" class="aliprods-btn aliprods-btn-primary aliprods-btn-animated" id="fetch-product">
                                    <span class="aliprods-btn-content">
                                        <span class="dashicons dashicons-search"></span>
                                        <span class="aliprods-btn-text">Fetch Product</span>
                                    </span>
                                    <div class="aliprods-btn-ripple"></div>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div id="aliprods-loading" class="aliprods-loading" style="display: none;">
                        <div class="aliprods-loading-animation">
                            <div class="aliprods-spinner-modern"></div>
                            <div class="aliprods-loading-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <p class="aliprods-loading-text">Fetching from AliExpress...</p>
                    </div>
                    
                    <div id="aliprods-error" class="aliprods-alert aliprods-alert-error" style="display: none;">
                        <div class="aliprods-alert-icon">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                        <div class="aliprods-alert-content"></div>
                    </div>
                    
                    <div id="aliprods-success" class="aliprods-alert aliprods-alert-success" style="display: none;">
                        <div class="aliprods-alert-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="aliprods-alert-content"></div>
                    </div>
                </div>
            </div>

            <div id="product-details" class="aliprods-card aliprods-product-card" style="display: none;">
                <div class="aliprods-card-header">
                    <div class="aliprods-card-header-content">
                        <div class="aliprods-card-icon">
                            <span class="dashicons dashicons-edit"></span>
                        </div>
                        <div class="aliprods-card-title">
                            <h2>Product Details</h2>
                            <p>Review and edit the product information before adding to your store</p>
                        </div>
                    </div>
                    <div class="aliprods-card-badge">
                        <span class="aliprods-badge aliprods-badge-success">Fetched from AliExpress</span>
                    </div>
                </div>
                
                <div class="aliprods-card-body">
                    <form id="aliprods-add-form">
                        <div class="aliprods-product-preview">
                            <div class="aliprods-product-media-section">
                                <div class="aliprods-product-gallery">
                                    <div class="aliprods-main-media" id="main-media-container">
                                        <img id="main-product-image" src="/placeholder.svg?height=400&width=400" alt="Product Image">
                                        <video id="main-product-video" style="display: none;" controls muted>
                                            <source id="main-video-source" src="/placeholder.svg" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    
                                    <div class="aliprods-media-controls">
                                        <button type="button" class="aliprods-media-btn active" id="show-main-image">
                                            <span class="dashicons dashicons-format-image"></span>
                                            Main Image
                                        </button>
                                        <button type="button" class="aliprods-media-btn" id="show-main-video" style="display: none;">
                                            <span class="dashicons dashicons-format-video"></span>
                                            Video
                                        </button>
                                    </div>
                                    
                                    <div class="aliprods-image-thumbnails" id="image-thumbnails">
                                        <!-- Thumbnails will be populated here -->
                                    </div>
                                </div>
                                
                                <div class="aliprods-product-badges">
                                    <span class="aliprods-badge aliprods-badge-external">External Product</span>
                                    <span class="aliprods-badge aliprods-badge-video" id="video-badge" style="display: none;">Has Video</span>
                                </div>
                            </div>
                            
                            <div class="aliprods-product-form">
                                <div class="aliprods-form-group">
                                    <label for="product-title">
                                        <span class="aliprods-label-icon">📝</span>
                                        Product Title
                                        <span class="aliprods-required">*</span>
                                    </label>
                                    <div class="aliprods-input-wrapper">
                                        <input type="text" id="product-title" name="title" required>
                                        <div class="aliprods-input-focus"></div>
                                        <div class="aliprods-char-counter">
                                            <span id="title-count">0</span>/1800
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="aliprods-form-row">
                                    <div class="aliprods-form-group">
                                        <label for="product-price">
                                            <span class="aliprods-label-icon">💰</span>
                                            Price (PKR)
                                            <span class="aliprods-required">*</span>
                                        </label>
                                        <div class="aliprods-input-wrapper">
                                            <input type="number" id="product-price" name="price" step="0.01" required>
                                            <div class="aliprods-input-focus"></div>
                                            <div class="aliprods-currency-symbol">PKR</div>
                                        </div>
                                    </div>
                                    
                                    <div class="aliprods-form-group">
                                        <label for="button-text">
                                            <span class="aliprods-label-icon">🔘</span>
                                            Button Text
                                        </label>
                                        <div class="aliprods-input-wrapper">
                                            <input type="text" id="button-text" name="button_text" value="Buy on AliExpress">
                                            <div class="aliprods-input-focus"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="aliprods-form-group">
                                    <label for="product-description">
                                        <span class="aliprods-label-icon">📄</span>
                                        Description
                                    </label>
                                    <div class="aliprods-textarea-wrapper">
                                        <textarea id="product-description" name="description" rows="6" 
                                                  placeholder="Description here..."></textarea>
                                        <div class="aliprods-textarea-focus"></div>
                                    </div>
                                </div>
                                
                                <div class="aliprods-affiliate-info">
                                    <div class="aliprods-affiliate-header">
                                        <span class="dashicons dashicons-admin-links"></span>
                                        <strong>Affiliate Link Preview:</strong>
                                    </div>
                                    <div class="aliprods-affiliate-url" id="affiliate-preview">
                                        <span class="aliprods-affiliate-text">No affiliate link loaded</span>
                                        <button type="button" class="aliprods-copy-btn" id="copy-affiliate">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="product-images" name="images">
                                <input type="hidden" id="affiliate-link" name="affiliate_link">
                                <input type="hidden" id="product-video-url" name="video_url">
                                
                                <div class="aliprods-form-actions">
                                    <button type="submit" class="aliprods-btn aliprods-btn-success aliprods-btn-animated aliprods-btn-large" id="add-product">
                                        <span class="aliprods-btn-content">
                                            <span class="dashicons dashicons-plus-alt"></span>
                                            <span class="aliprods-btn-text">Add Product to Store</span>
                                        </span>
                                        <div class="aliprods-btn-ripple"></div>
                                    </button>
                                    
                                    <button type="button" class="aliprods-btn aliprods-btn-secondary aliprods-btn-animated" id="reset-form">
                                        <span class="aliprods-btn-content">
                                            <span class="dashicons dashicons-update"></span>
                                            <span class="aliprods-btn-text">Reset Form</span>
                                        </span>
                                        <div class="aliprods-btn-ripple"></div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Section B: Category Browse -->
    <div id="category-browse" class="aliprods-tab-content">
        <div class="aliprods-main">
            <div class="aliprods-card aliprods-category-card">
                <div class="aliprods-card-header">
                    <div class="aliprods-card-header-content">
                        <div class="aliprods-card-icon">
                            <span class="dashicons dashicons-category"></span>
                        </div>
                        <div class="aliprods-card-title">
                            <h2>Browse by Categories</h2>
                            <p>Explore AliExpress categories and discover products to import</p>
                        </div>
                    </div>
                    <div class="aliprods-card-badge">
                        <span class="aliprods-badge aliprods-badge-primary">Section B</span>
                    </div>
                </div>
                
                <div class="aliprods-card-body">
                    <div class="aliprods-category-search">
                        <div class="aliprods-input-wrapper">
                            <input type="text" id="category-search" placeholder="Search categories...">
                            <div class="aliprods-input-focus"></div>
                            <span class="dashicons dashicons-search aliprods-search-icon"></span>
                        </div>
                    </div>
                    
                    <div id="categories-loading" class="aliprods-loading" style="display: none;">
                        <div class="aliprods-loading-animation">
                            <div class="aliprods-spinner-modern"></div>
                        </div>
                        <p class="aliprods-loading-text">Loading categories from AliExpress...</p>
                    </div>
                    
                    <div id="categories-grid" class="aliprods-categories-grid">
                        <!-- Categories will be populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div id="products-section" class="aliprods-card aliprods-products-card" style="display: none;">
                <div class="aliprods-card-header">
                    <div class="aliprods-card-header-content">
                        <div class="aliprods-card-icon">
                            <span class="dashicons dashicons-products"></span>
                        </div>
                        <div class="aliprods-card-title">
                            <h2 id="category-title">Category Products</h2>
                            <p id="category-subtitle">Browse and import products from this category</p>
                        </div>
                    </div>
                    <button class="aliprods-btn aliprods-btn-secondary" id="back-to-categories">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        Back to Categories
                    </button>
                </div>
                
                <div class="aliprods-card-body">
                    <div class="aliprods-products-controls">
                        <div class="aliprods-input-wrapper">
                            <input type="text" id="products-search" placeholder="Search products in this category...">
                            <div class="aliprods-input-focus"></div>
                            <span class="dashicons dashicons-search aliprods-search-icon"></span>
                        </div>
                        <button class="aliprods-btn aliprods-btn-primary" id="search-products">
                            <span class="dashicons dashicons-search"></span>
                            Search
                        </button>
                    </div>
                    
                    <div id="products-loading" class="aliprods-loading" style="display: none;">
                        <div class="aliprods-loading-animation">
                            <div class="aliprods-spinner-modern"></div>
                        </div>
                        <p class="aliprods-loading-text">Loading products from AliExpress...</p>
                    </div>
                    
                    <div id="products-grid" class="aliprods-products-grid">
                        <!-- Products will be populated here -->
                    </div>
                    
                    <div id="products-pagination" class="aliprods-pagination">
                        <!-- Pagination will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div id="product-modal" class="aliprods-modal" style="display: none;">
        <div class="aliprods-modal-content">
            <div class="aliprods-modal-header">
                <h2 id="modal-product-title">Product Details</h2>
                <button class="aliprods-modal-close" id="close-modal">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aliprods-modal-body" id="modal-product-content">
                <!-- Product details will be populated here -->
            </div>
        </div>
    </div>
</div>
