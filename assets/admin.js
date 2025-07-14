const jQuery = window.jQuery || require("jquery")
jQuery(document).ready(($) => {
  const form = $("#aliprods-import-form")
  const addForm = $("#aliprods-add-form")
  const productDetails = $("#product-details")
  const loading = $("#aliprods-loading")
  const errorDiv = $("#aliprods-error")
  const successDiv = $("#aliprods-success")

  // Declare aliprods_ajax variable
  const aliprods_ajax = window.aliprods_ajax || {}

  // Current state
  let currentImages = []
  let currentVideoUrl = ""
  let currentCategory = null
  let currentPage = 1

  // Initialize
  init()

  function init() {
    updateProductCount()
    setupEventListeners()
    setupFormValidation()
    setupAnimations()
    setupTabs()
  }

  function setupEventListeners() {
    // Handle product fetch
    form.on("submit", handleProductFetch)

    // Handle product addition
    addForm.on("submit", handleProductAdd)

    // Reset form
    $("#reset-form").on("click", resetForm)

    // Copy affiliate link
    $("#copy-affiliate").on("click", copyAffiliateLink)

    // Character counter for title
    $("#product-title").on("input", updateCharacterCounter)

    // Price formatting
    $("#product-price").on("input", formatPrice)

    // URL validation
    $("#product-url").on("blur", validateUrl)

    // Media controls
    $("#show-main-image").on("click", () => showMainMedia("image"))
    $("#show-main-video").on("click", () => showMainMedia("video"))

    // Category search
    $("#category-search").on("input", debounce(filterCategories, 300))

    // Product search
    $("#search-products").on("click", searchCategoryProducts)
    $("#products-search").on("keypress", (e) => {
      if (e.which === 13) {
        searchCategoryProducts()
      }
    })

    // Back to categories
    $("#back-to-categories").on("click", showCategories)

    // Modal close
    $("#close-modal").on("click", closeModal)
    $("#product-modal").on("click", function (e) {
      if (e.target === this) {
        closeModal()
      }
    })
  }

  function setupTabs() {
    $(".aliprods-tab-btn").on("click", function () {
      const tabId = $(this).data("tab")

      // Update tab buttons
      $(".aliprods-tab-btn").removeClass("active")
      $(this).addClass("active")

      // Update tab content
      $(".aliprods-tab-content").removeClass("active")
      $("#" + tabId).addClass("active")

      // Load categories if switching to category browse
      if (tabId === "category-browse" && $("#categories-grid").children().length === 0) {
        loadCategories()
      }
    })
  }

  function setupFormValidation() {
    // Real-time validation
    $("#product-url").on("input", function () {
      const url = $(this).val().trim()
      const isValid = isValidAliExpressUrl(url)

      $(this).toggleClass("invalid", url && !isValid)
      $(".url-error").remove()

      if (url && !isValid) {
        $(this).after(
          '<div class="url-error" style="color: var(--error-color); font-size: 0.85em; margin-top: 5px;">Please enter a valid AliExpress product URL</div>',
        )
      }
    })
  }

  function setupAnimations() {
    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }
      })
    })

    $(".aliprods-card").each(function () {
      this.style.opacity = "0"
      this.style.transform = "translateY(20px)"
      this.style.transition = "all 0.6s ease"
      observer.observe(this)
    })

    // Button ripple effect
    $(".aliprods-btn-animated").on("click", function (e) {
      const ripple = $(this).find(".aliprods-btn-ripple")
      const rect = this.getBoundingClientRect()
      const size = Math.max(rect.width, rect.height)
      const x = e.clientX - rect.left - size / 2
      const y = e.clientY - rect.top - size / 2

      ripple.css({
        width: size + "px",
        height: size + "px",
        left: x + "px",
        top: y + "px",
      })
    })
  }

  function loadCategories() {
        const fakeCategory = $(`
          <div class="aliprods-category-item" data-category-id="200183144">
            <div class="aliprods-category-icon">
              <span class="dashicons dashicons-category"></span>
            </div>
            <div class="aliprods-category-name">Search for Mass Products</div>
          </div>
        `)

        fakeCategory.on("click", () => {
          loadCategoryProducts("200183144", "Canned Food1")
        })

        $("#categories-grid").html("").append(fakeCategory)

  } 

  function filterCategories() {
    const searchTerm = $("#category-search").val().toLowerCase()
    const categories = $(".aliprods-category-item")

    categories.each(function () {
      const categoryName = $(this).find(".aliprods-category-name").text().toLowerCase()
      const isVisible = categoryName.includes(searchTerm)
      $(this).toggle(isVisible)
    })
  }

  function loadCategoryProducts(categoryId, categoryName) {
    currentCategory = { id: categoryId, name: categoryName }
    currentPage = 1

    $("#category-title").text(categoryName)
    $("#category-subtitle").text(`Browse and import products from ${categoryName}`)

    $("#categories-grid").parent().hide()
    $("#products-section").show()

    fetchCategoryProducts()
  }

  function searchCategoryProducts() {
    if (!currentCategory) return

    currentPage = 1
    fetchCategoryProducts()
  }

  function fetchCategoryProducts() {
    const keywords = $("#products-search").val().trim()

    $("#products-loading").show()
    $("#products-grid").hide()
    $("#products-pagination").hide()

    $.ajax({
      url: aliprods_ajax.ajax_url,
      type: "POST",
      data: {
        action: "aliprods_get_category_products",
        category_ids: currentCategory.id,
        keywords: keywords,
        page_no: currentPage,
        page_size: 20,
        nonce: aliprods_ajax.nonce,
      },
      success: (response) => {
        $("#products-loading").hide()
        $("#products-grid").show()

        if (response.success) {
          const products = response.data.products || []
          displayProducts(products)
          setupPagination(response.data.total_page_no || 1)
        } else {
          showError(response.data.message || "Failed to load products")
        }
      },
      error: (xhr, status, error) => {
        $("#products-loading").hide()
        showError("Network error: " + error)
      },
    })
  }

  function displayProducts(products) {
    const grid = $("#products-grid")
    grid.empty()

    if (!products || products.length === 0) {
      grid.html('<div class="aliprods-no-products">No products found in this category.</div>')
      return
    }

    products.forEach((product) => {
      const productId = product.product_id || product.productId
      const productTitle = product.product_title || product.productTitle || "No title"
      const productImage =
        product.product_main_image_url || product.productMainImageUrl || "/placeholder.svg?height=200&width=200"
      const productPrice = product.target_sale_price || product.targetSalePrice || "N/A"
      const evaluateRate = product.evaluate_rate || product.evaluateRate || "N/A"
      const volume = product.volume || 0
      const productUrl = product.product_detail_url || product.productDetailUrl || ""

      const productItem = $(`
        <div class="aliprods-product-item" data-product-id="${productId}">
          <div class="aliprods-product-image">
            <img src="${productImage}" alt="${productTitle}" onerror="this.src='/placeholder.svg?height=200&width=200'">
            <div class="aliprods-product-price">${productPrice} PKR</div>
          </div>
          <div class="aliprods-product-info">
            <div class="aliprods-product-title">${productTitle}</div>
            <div class="aliprods-product-meta">
              <span>⭐ ${evaluateRate}</span>
              <span>🛒 ${volume} sold</span>
            </div>
          </div>
        </div>
      `)

      productItem.on("click", () => {
        if (productUrl) {
          openProductModal({ ...product, product_detail_url: productUrl })
        } else {
          showError("Product URL not available")
        }
      })

      grid.append(productItem)
    })
  }

  function setupPagination(totalPages) {
    const pagination = $("#products-pagination")
    pagination.empty()

    if (totalPages <= 1) {
      pagination.hide()
      return
    }

    pagination.show()

    // Previous button
    const prevBtn = $(`<button class="aliprods-pagination-btn" ${currentPage <= 1 ? "disabled" : ""}>Previous</button>`)
    prevBtn.on("click", () => {
      if (currentPage > 1) {
        currentPage--
        fetchCategoryProducts()
      }
    })
    pagination.append(prevBtn)

    // Page numbers
    const startPage = Math.max(1, currentPage - 2)
    const endPage = Math.min(totalPages, currentPage + 2)

    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = $(`<button class="aliprods-pagination-btn ${i === currentPage ? "active" : ""}">${i}</button>`)
      pageBtn.on("click", () => {
        currentPage = i
        fetchCategoryProducts()
      })
      pagination.append(pageBtn)
    }

    // Next button
    const nextBtn = $(
      `<button class="aliprods-pagination-btn" ${currentPage >= totalPages ? "disabled" : ""}>Next</button>`,
    )
    nextBtn.on("click", () => {
      if (currentPage < totalPages) {
        currentPage++
        fetchCategoryProducts()
      }
    })
    pagination.append(nextBtn)
  }

  function openProductModal(product) {
    fetchProductDetails(product.product_id, product.product_detail_url)
  }

  function fetchProductDetails(productId, productUrl) {
    $("#product-modal").show()
    $("#modal-product-content").html(`
      <div class="aliprods-loading">
        <div class="aliprods-loading-animation">
          <div class="aliprods-spinner-modern"></div>
        </div>
        <p class="aliprods-loading-text">Fetching from AliExpress and generating description...</p>
      </div>
    `)

    $.ajax({
      url: aliprods_ajax.ajax_url,
      type: "POST",
      data: {
        action: "aliprods_get_product",
        product_url: productUrl,
        nonce: aliprods_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          displayProductModal(response.data)
        } else {
          $("#modal-product-content").html(`
            <div class="aliprods-alert aliprods-alert-error">
              <div class="aliprods-alert-icon">
                <span class="dashicons dashicons-warning"></span>
              </div>
              <div class="aliprods-alert-content">${response.data.message || "Failed to load product details"}</div>
            </div>
          `)
        }
      },
      error: (xhr, status, error) => {
        $("#modal-product-content").html(`
          <div class="aliprods-alert aliprods-alert-error">
            <div class="aliprods-alert-icon">
              <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="aliprods-alert-content">Network error: ${error}</div>
          </div>
        `)
      },
    })
  }

  function displayProductModal(productData) {
    $("#modal-product-title").text(productData.title)

    const modalContent = $(`
      <div class="aliprods-product-preview">
        <div class="aliprods-product-media-section">
          <div class="aliprods-product-gallery">
            <div class="aliprods-main-media" id="modal-main-media">
              <img id="modal-main-image" src="${productData.images[0] || "/placeholder.svg?height=400&width=400"}" alt="Product Image">
              ${
                productData.video_url
                  ? `
                <video id="modal-main-video" style="display: none;" controls muted>
                  <source src="${productData.video_url}" type="video/mp4">
                  Your browser does not support the video tag.
                </video>
              `
                  : ""
              }
            </div>
            
            <div class="aliprods-media-controls">
              <button type="button" class="aliprods-media-btn active" id="modal-show-image">
                <span class="dashicons dashicons-format-image"></span>
                Main Image
              </button>
              ${
                productData.video_url
                  ? `
                <button type="button" class="aliprods-media-btn" id="modal-show-video">
                  <span class="dashicons dashicons-format-video"></span>
                  Video
                </button>
              `
                  : ""
              }
            </div>
            
            <div class="aliprods-image-thumbnails" id="modal-thumbnails">
              ${productData.images
                .map(
                  (img, index) => `
                <div class="aliprods-thumbnail ${index === 0 ? "active" : ""}" data-image="${img}">
                  <img src="${img}" alt="Thumbnail ${index + 1}">
                </div>
              `,
                )
                .join("")}
            </div>
          </div>
          
          <div class="aliprods-product-badges">
            <span class="aliprods-badge aliprods-badge-external">External Product</span>
            <span class="aliprods-badge aliprods-badge-affiliate">Affiliate Link</span>
            ${productData.video_url ? '<span class="aliprods-badge aliprods-badge-video">Has Video</span>' : ""}
            <span class="aliprods-badge aliprods-badge-success">Fetched from AliExpress</span>
          </div>
        </div>
        
        <div class="aliprods-product-form">
          <form id="modal-add-form">
            <div class="aliprods-form-group">
              <label for="modal-product-title">
                <span class="aliprods-label-icon">📝</span>
                Product Title
                <span class="aliprods-required">*</span>
              </label>
              <div class="aliprods-input-wrapper">
                <input type="text" id="modal-product-title-input" name="title" value="${productData.title}" required>
                <div class="aliprods-input-focus"></div>
                <div class="aliprods-char-counter">
                  <span id="modal-title-count">${productData.title.length}</span>/1800
                </div>
              </div>
            </div>
            
            <div class="aliprods-form-row">
              <div class="aliprods-form-group">
                <label for="modal-product-price">
                  <span class="aliprods-label-icon">💰</span>
                  Price (PKR)
                  <span class="aliprods-required">*</span>
                </label>
                <div class="aliprods-input-wrapper">
                  <input type="number" id="modal-product-price" name="price" value="${productData.price_pkr}" step="0.01" required>
                  <div class="aliprods-input-focus"></div>
                  <div class="aliprods-currency-symbol">PKR</div>
                </div>
              </div>
              
              <div class="aliprods-form-group">
                <label for="modal-button-text">
                  <span class="aliprods-label-icon">🔘</span>
                  Button Text
                </label>
                <div class="aliprods-input-wrapper">
                  <input type="text" id="modal-button-text" name="button_text" value="Buy on AliExpress">
                  <div class="aliprods-input-focus"></div>
                </div>
              </div>
            </div>
            
            <div class="aliprods-form-group">
              <label for="modal-product-description">
                <span class="aliprods-label-icon">📄</span>
                Description
              </label>
              <div class="aliprods-textarea-wrapper">
                <textarea id="modal-product-description" name="description" rows="6">${productData.description || ""}</textarea>
                <div class="aliprods-textarea-focus"></div>
              </div>
            </div>
            
            <div class="aliprods-affiliate-info">
              <div class="aliprods-affiliate-header">
                <span class="dashicons dashicons-admin-links"></span>
                <strong>Affiliate Link Preview:</strong>
              </div>
              <div class="aliprods-affiliate-url">
                <span class="aliprods-affiliate-text">${productData.affiliate_link}</span>
                <button type="button" class="aliprods-copy-btn" id="modal-copy-affiliate">
                  <span class="dashicons dashicons-admin-page"></span>
                </button>
              </div>
            </div>
            
            <input type="hidden" id="modal-product-images" name="images" value='${JSON.stringify(productData.images)}'>
            <input type="hidden" id="modal-affiliate-link" name="affiliate_link" value="${productData.affiliate_link}">
            <input type="hidden" id="modal-product-video-url" name="video_url" value="${productData.video_url || ""}">
            
            <div class="aliprods-form-actions">
              <button type="submit" class="aliprods-btn aliprods-btn-success aliprods-btn-animated aliprods-btn-large" id="modal-add-product">
                <span class="aliprods-btn-content">
                  <span class="dashicons dashicons-plus-alt"></span>
                  <span class="aliprods-btn-text">Add Product to Store</span>
                </span>
                <div class="aliprods-btn-ripple"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    `)

    $("#modal-product-content").html(modalContent)

    // Setup modal event handlers
    setupModalEventHandlers(productData)
  }

  function setupModalEventHandlers(productData) {
    // Media controls
    $("#modal-show-image").on("click", () => {
      $("#modal-main-image").show()
      $("#modal-main-video").hide()
      $("#modal-show-image").addClass("active")
      $("#modal-show-video").removeClass("active")
    })

    $("#modal-show-video").on("click", () => {
      $("#modal-main-image").hide()
      $("#modal-main-video").show()
      $("#modal-show-video").addClass("active")
      $("#modal-show-image").removeClass("active")
    })

    // Thumbnail clicks
    $("#modal-thumbnails .aliprods-thumbnail").on("click", function () {
      const imageUrl = $(this).data("image")
      $("#modal-main-image").attr("src", imageUrl)
      $("#modal-thumbnails .aliprods-thumbnail").removeClass("active")
      $(this).addClass("active")
    })

    // Character counter
    $("#modal-product-title-input").on("input", function () {
      const count = $(this).val().length
      $("#modal-title-count").text(count)
    })

    // Copy affiliate link
    $("#modal-copy-affiliate").on("click", () => {
      copyToClipboard(productData.affiliate_link)
    })

    // Form submission
    $("#modal-add-form").on("submit", handleModalProductAdd)
  }

  function handleModalProductAdd(e) {
    e.preventDefault()

    const title = $("#modal-product-title-input").val().trim()
    const price = $("#modal-product-price").val()
    const images = $("#modal-product-images").val()
    const videoUrl = $("#modal-product-video-url").val()
    const description = $("#modal-product-description").val()
    const affiliateLink = $("#modal-affiliate-link").val()
    const buttonText = $("#modal-button-text").val() || "Buy on AliExpress"

    if (!title || !price) {
      showError("Please fill in all required fields (Title and Price)")
      return
    }

    const addBtn = $("#modal-add-product")
    const originalContent = addBtn.find(".aliprods-btn-text").text()

    addBtn.prop("disabled", true)
    addBtn.find(".aliprods-btn-text").text("Adding Product...")
    addBtn.find(".dashicons").removeClass("dashicons-plus-alt").addClass("dashicons-update-alt")

    $.ajax({
      url: aliprods_ajax.ajax_url,
      type: "POST",
      data: {
        action: "aliprods_add_product",
        title: title,
        price: price,
        images: images,
        video_url: videoUrl,
        description: description,
        affiliate_link: affiliateLink,
        button_text: buttonText,
        nonce: aliprods_ajax.nonce,
      },
      success: (response) => {
        addBtn.prop("disabled", false)
        addBtn.find(".aliprods-btn-text").text(originalContent)
        addBtn.find(".dashicons").removeClass("dashicons-update-alt").addClass("dashicons-plus-alt")

        if (response.success) {
          const successMessage = response.data.message

          showSuccess(
            successMessage +
              ' <a href="' +
              response.data.edit_url +
              '" target="_blank" style="color: var(--success-dark); text-decoration: underline;">Edit Product</a>',
          )

          updateProductCount()
          closeModal()
        } else {
          showError(response.data.message || "Failed to add product")
        }
      },
      error: (xhr, status, error) => {
        addBtn.prop("disabled", false)
        addBtn.find(".aliprods-btn-text").text(originalContent)
        addBtn.find(".dashicons").removeClass("dashicons-update-alt").addClass("dashicons-plus-alt")
        showError("Network error: " + error)
      },
    })
  }

  function closeModal() {
    $("#product-modal").hide()
  }

  function showCategories() {
    $("#products-section").hide()
    $("#categories-grid").parent().show()
    currentCategory = null
  }

  function handleProductFetch(e) {
    e.preventDefault()

    const productUrl = $("#product-url").val().trim()

    if (!productUrl) {
      showError("Please enter a product URL")
      return
    }

    if (!isValidAliExpressUrl(productUrl)) {
      showError("Please enter a valid AliExpress product URL (must contain /item/[number].html)")
      return
    }

    fetchProduct(productUrl)
  }

  function handleProductAdd(e) {
    e.preventDefault()
    addProduct()
  }

  function isValidAliExpressUrl(url) {
    return /aliexpress\.com.*\/item\/\d+\.html/.test(url)
  }

  function fetchProduct(productUrl) {
    hideMessages()
    loading.show()
    productDetails.hide()

    const fetchBtn = $("#fetch-product")
    const originalContent = fetchBtn.find(".aliprods-btn-text").text()

    fetchBtn.prop("disabled", true)
    fetchBtn.find(".aliprods-btn-text").text("Fetching...")
    fetchBtn.find(".dashicons").removeClass("dashicons-search").addClass("dashicons-update-alt")

    $.ajax({
      url: aliprods_ajax.ajax_url,
      type: "POST",
      data: {
        action: "aliprods_get_product",
        product_url: productUrl,
        nonce: aliprods_ajax.nonce,
      },
      success: (response) => {
        loading.hide()
        resetFetchButton(fetchBtn, originalContent)

        if (response.success) {
          populateProductForm(response.data)
          showProductDetails()
        } else {
          showError(response.data.message || "Failed to fetch product information")
        }
      },
      error: (xhr, status, error) => {
        loading.hide()
        resetFetchButton(fetchBtn, originalContent)
        showError("Network error: " + error)
      },
    })
  }

  function resetFetchButton(btn, originalText) {
    btn.prop("disabled", false)
    btn.find(".aliprods-btn-text").text(originalText)
    btn.find(".dashicons").removeClass("dashicons-update-alt").addClass("dashicons-search")
  }

  function populateProductForm(data) {
    currentImages = data.images || []
    currentVideoUrl = data.video_url || ""

    $("#product-title").val(data.title)
    $("#product-price").val(data.price_pkr)
    $("#affiliate-link").val(data.affiliate_link)
    $("#product-images").val(JSON.stringify(currentImages))
    $("#product-video-url").val(currentVideoUrl)
    $("#product-description").val(data.description || "")

    // Set main image
    if (currentImages.length > 0) {
      $("#main-product-image").attr("src", currentImages[0])
    }

    // Handle video
    if (currentVideoUrl) {
      $("#main-video-source").attr("src", currentVideoUrl)
      $("#main-product-video")[0].load()
      $("#show-main-video").show()
      $("#video-badge").show()
    } else {
      $("#show-main-video").hide()
      $("#video-badge").hide()
    }

    // Create thumbnails
    createImageThumbnails()

    // Update affiliate link preview
    $("#affiliate-preview .aliprods-affiliate-text").text(data.affiliate_link)

    // Update character counter
    updateCharacterCounter()
  }

  function createImageThumbnails() {
    const thumbnailsContainer = $("#image-thumbnails")
    thumbnailsContainer.empty()

    currentImages.forEach((imageUrl, index) => {
      const thumbnail = $(`
        <div class="aliprods-thumbnail ${index === 0 ? "active" : ""}" data-image="${imageUrl}">
          <img src="${imageUrl}" alt="Thumbnail ${index + 1}">
        </div>
      `)

      thumbnail.on("click", function () {
        const imageUrl = $(this).data("image")
        $("#main-product-image").attr("src", imageUrl)
        $(".aliprods-thumbnail").removeClass("active")
        $(this).addClass("active")
      })

      thumbnailsContainer.append(thumbnail)
    })
  }

  function showMainMedia(type) {
    if (type === "image") {
      $("#main-product-image").show()
      $("#main-product-video").hide()
      $("#show-main-image").addClass("active")
      $("#show-main-video").removeClass("active")
    } else if (type === "video") {
      $("#main-product-image").hide()
      $("#main-product-video").show()
      $("#show-main-video").addClass("active")
      $("#show-main-image").removeClass("active")
    }
  }

  function showProductDetails() {
    productDetails.show()

    // Smooth scroll with offset
    $("html, body").animate(
      {
        scrollTop: productDetails.offset().top - 100,
      },
      800,
    )

    // Animate form fields
    productDetails.find(".aliprods-form-group").each(function (index) {
      $(this)
        .delay(index * 100)
        .animate(
          {
            opacity: 1,
            transform: "translateY(0)",
          },
          400,
        )
    })
  }

  function addProduct() {
    const title = $("#product-title").val().trim()
    const price = $("#product-price").val()
    const images = $("#product-images").val()
    const videoUrl = $("#product-video-url").val()
    const description = $("#product-description").val()
    const affiliateLink = $("#affiliate-link").val()
    const buttonText = $("#button-text").val() || "Buy on AliExpress"

    if (!title || !price) {
      showError("Please fill in all required fields (Title and Price)")
      return
    }

    if (title.length > 1800) {
      showError("Product title must be 1800 characters or less")
      return
    }

    hideMessages()

    const addBtn = $("#add-product")
    const originalContent = addBtn.find(".aliprods-btn-text").text()

    addBtn.prop("disabled", true)
    addBtn.find(".aliprods-btn-text").text("Adding Product...")
    addBtn.find(".dashicons").removeClass("dashicons-plus-alt").addClass("dashicons-update-alt")

    $.ajax({
      url: aliprods_ajax.ajax_url,
      type: "POST",
      data: {
        action: "aliprods_add_product",
        title: title,
        price: price,
        images: images,
        video_url: videoUrl,
        description: description,
        affiliate_link: affiliateLink,
        button_text: buttonText,
        nonce: aliprods_ajax.nonce,
      },
      success: (response) => {
        resetAddButton(addBtn, originalContent)

        if (response.success) {
          let successMessage = response.data.message

          if (videoUrl) {
            successMessage += " (Including video content!)"
          }

          if (currentImages.length > 1) {
            successMessage += ` (${currentImages.length} images imported!)`
          }

          showSuccess(
            successMessage +
              ' <a href="' +
              response.data.edit_url +
              '" target="_blank" style="color: var(--success-dark); text-decoration: underline;">Edit Product</a>',
          )

          updateProductCount()

          // Auto-reset form after success
          setTimeout(() => {
            resetForm()
          }, 5000)
        } else {
          showError(response.data.message || "Failed to add product")
        }
      },
      error: (xhr, status, error) => {
        resetAddButton(addBtn, originalContent)
        showError("Network error: " + error)
      },
    })
  }

  function resetAddButton(btn, originalText) {
    btn.prop("disabled", false)
    btn.find(".aliprods-btn-text").text(originalText)
    btn.find(".dashicons").removeClass("dashicons-update-alt").addClass("dashicons-plus-alt")
  }

  function copyAffiliateLink() {
    const affiliateLink = $("#affiliate-link").val()
    if (!affiliateLink) return

    copyToClipboard(affiliateLink)
  }

  function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
      const btn = $("#copy-affiliate, #modal-copy-affiliate")
      const originalIcon = btn.find(".dashicons").attr("class")

      btn.find(".dashicons").removeClass().addClass("dashicons dashicons-yes-alt")

      setTimeout(() => {
        btn.find(".dashicons").removeClass().addClass(originalIcon)
      }, 2000)
    })
  }

  function updateCharacterCounter() {
    const title = $("#product-title").val()
    const count = title.length
    const maxLength = 1800

    $("#title-count").text(count)

    const counter = $(".aliprods-char-counter")
    if (count > maxLength) {
      counter.css("color", "var(--error-color)")
    } else if (count > maxLength * 0.8) {
      counter.css("color", "var(--warning-color)")
    } else {
      counter.css("color", "var(--text-muted)")
    }
  }

  function formatPrice() {
    const priceInput = $("#product-price")
    const value = Number.parseFloat(priceInput.val())

    if (value < 0) {
      priceInput.val(0)
    }
  }

  function validateUrl() {
    const urlInput = $("#product-url")
    const url = urlInput.val().trim()

    if (url && !isValidAliExpressUrl(url)) {
      urlInput.addClass("invalid")
    } else {
      urlInput.removeClass("invalid")
    }
  }

  function updateProductCount() {
    // This would typically fetch from WordPress, for now we'll simulate
    const count = localStorage.getItem("aliprods_count") || 0
    $("#total-products").text(count)
  }

  function showError(message) {
    hideMessages()
    errorDiv.find(".aliprods-alert-content").html(message)
    errorDiv.show()
    scrollToMessage(errorDiv)
  }

  function showSuccess(message) {
    hideMessages()
    successDiv.find(".aliprods-alert-content").html(message)
    successDiv.show()
    scrollToMessage(successDiv)

    // Update product count
    const currentCount = Number.parseInt(localStorage.getItem("aliprods_count") || 0)
    localStorage.setItem("aliprods_count", currentCount + 1)
    updateProductCount()
  }

  function hideMessages() {
    errorDiv.hide()
    successDiv.hide()
  }

  function scrollToMessage(element) {
    $("html, body").animate(
      {
        scrollTop: element.offset().top - 100,
      },
      600,
    )
  }

  function resetForm() {
    form[0].reset()
    addForm[0].reset()
    productDetails.hide()
    hideMessages()

    // Reset images and video
    currentImages = []
    currentVideoUrl = ""
    $("#main-product-image").attr("src", "/placeholder.svg?height=400&width=400")
    $("#main-product-video").hide()
    $("#main-product-video")[0].pause()
    $("#main-video-source").attr("src", "")
    $("#image-thumbnails").empty()

    // Reset video controls
    $("#show-main-video").hide()
    $("#video-badge").hide()
    $("#show-main-image").addClass("active")
    $("#show-main-video").removeClass("active")

    // Reset affiliate preview
    $("#affiliate-preview .aliprods-affiliate-text").text("No affiliate link loaded")

    // Reset character counter
    $("#title-count").text("0")
    $(".aliprods-char-counter").css("color", "var(--text-muted)")

    // Remove validation classes
    $(".invalid").removeClass("invalid")
    $(".url-error").remove()

    // Smooth scroll to top
    $("html, body").animate(
      {
        scrollTop: 0,
      },
      800,
    )
  }

  // Utility functions
  function debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }

  // Auto-resize textareas
  $("textarea").on("input", function () {
    this.style.height = "auto"
    this.style.height = this.scrollHeight + "px"
  })

  // Add smooth scrolling for anchor links
  $('a[href^="#"]').on("click", function (e) {
    e.preventDefault()
    const target = $(this.getAttribute("href"))
    if (target.length) {
      $("html, body").animate(
        {
          scrollTop: target.offset().top - 100,
        },
        800,
      )
    }
  })

  // Add loading states to all buttons
  $(".aliprods-btn").on("click", function () {
    if (!$(this).prop("disabled")) {
      $(this).addClass("loading")
      setTimeout(() => {
        $(this).removeClass("loading")
      }, 300)
    }
  })
})
