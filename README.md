# 🛍️ AliProds v3.0 — AliExpress Product Importer Plugin

**AliProds v3.0** by **Mehar Umar**  
Website: [https://meharumar.codes](https://meharumar.codes)  
Contact: [contact@meharumar.codes](mailto:contact@meharumar.codes)

---

AliProds is a WordPress plugin that imports products directly from **AliExpress** using the **AliExpress Affiliate API**. Ideal for affiliate marketers, dropshippers, and WooCommerce users to quickly add products with affiliate links, live pricing, and stock management.

---

## 🚀 Features

- ✅ **Single Product Import via Link**
- ✅ **Keyword & Category Bulk Product Import**
- ✅ **Live Price, Stock, and Shipping Details**
- ✅ **Automatic Affiliate Link Generation**
- ✅ **WooCommerce Compatibility**
- ✅ **Custom Product Title, Description & Images**
- ✅ **Real-time API Integration**
- ✅ **Clean AJAX-based Interface**
- ✅ **Currency, Language & Country Support**

---

## 📦 Installation Guide

1. Download the plugin `.zip` file.
2. Go to **WordPress Dashboard → Plugins → Add New → Upload Plugin**.
3. Upload the downloaded file and click **Install Now**.
4. Activate the plugin.
5. Navigate to **AliProds Settings** from the admin menu.

---

## 🔑 API Configuration

1. Create an account at [AliExpress Portals](https://portals.aliexpress.com).
2. Go to **API Settings** and generate your:
   - **App Key**
   - **App Secret**
   - **Tracking ID**
3. Fill these credentials in **AliProds Settings** inside your WordPress Dashboard.
4. Set:
   - Default **Currency**
   - Preferred **Language**
   - **Shipping Destination Country**

---

## 🖥️ How to Use

### ✅ **Single Product Import**
- Go to **AliProds → Single Product Import**.
- Paste your **AliExpress Product Link**.
- Click **Import Product** and it's done!

### ✅ **Bulk Import**
- Go to **AliProds → Bulk Import**.
- Use **Keyword Search** or **Category ID**.
- Choose number of pages and products.
- Click **Import Products**.

---

## 📝 Shortcodes

| Shortcode | Description |
|------------|-------------|
| `[aliprods]` | Displays the latest imported products |
| `[aliprods category="shoes" limit="5"]` | Shows 5 products from the 'shoes' category |

---

## 🧑‍💻 Developer Hooks

AliProds offers hooks and filters for advanced customization:

```php
do_action('ali_import_before_save', $product_data);
apply_filters('ali_product_title', $title);
apply_filters('ali_product_description', $description);
```

---

## 💡 Important Notes

* ✅ Requires an **approved AliExpress Affiliate Account**.
* ✅ Compliant with AliExpress Affiliate API (no scraping).
* ✅ WooCommerce must be installed for product syncing.

---

## 🧑‍🎨 Author

**AliProds v3.0** by **Mehar Umar**
🌐 [meharumar.codes](https://meharumar.codes)
📧 [contact@meharumar.codes](mailto:contact@meharumar.codes)

---

## 📃 License

**MIT License**
Free for personal and commercial use. You can modify and redistribute, but please retain the original credits.
---
