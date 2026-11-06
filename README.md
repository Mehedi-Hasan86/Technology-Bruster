<div align="center">

# 💻 Technology-Bruster

**A full-stack digital product marketplace built with PHP & MySQL — optimized for selling software, web themes, scripts, and digital assets securely.**

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![XAMPP](https://img.shields.io/badge/XAMPP-Local%20Dev-FB7A24?style=flat-square&logo=apache&logoColor=white)](https://www.apachefriends.org/)

**Made with ❤️ by the CSE-2424 DBMS Lab Team**

</div>

---

## 📖 Table of Contents

- [About the Project](#-about-the-project)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Database Schema](#-database-schema)
- [Project Structure](#-project-structure)
- [Security](#-security)
- [Team](#-team)
- [Future Enhancements](#-future-enhancements)
- [License](#-license)

---

## 🚀 About the Project

**Technology-Bruster** is a comprehensive, full-stack digital product marketplace engineered to facilitate the seamless exchange of software, web themes, and diverse digital assets. Unlike traditional e-commerce systems, this project is specifically optimized for **virtual goods**, featuring a specialized architecture that handles everything from instant file delivery to complex license management.

The platform is divided into **two primary ecosystems**:

1. **🛍️ User Storefront** — A high-conversion, responsive frontend where customers can search, filter, and purchase digital products using local (bKash) and international (SSLCommerz) payment gateways.
2. **🛠️ Admin Command Center** — A data-driven dashboard empowering site owners with real-time analytics, user management, product CRUD, and an automated coupon engine.

Built with a **modular MVC-inspired design** and a **repository pattern** that separates business logic from database operations, the system ensures high maintainability, scalability, and a lightning-fast user experience.

---

## ✨ Features

### 👤 User-Facing Features

| Feature | Description |
|---|---|
| 🔍 **Search & Filtering** | Robust search bar with category-based filtering and price-range sorting. |
| 🗂️ **Dynamic Catalog** | Products displayed with high-resolution screenshots, descriptions, and version metadata. |
| 🔐 **Authentication** | Secure registration/login using **bcrypt** encrypted credentials. |
| 👤 **User Profile** | Dedicated space to update information and track purchase history. |
| 🛒 **AJAX-Powered Cart** | Add/remove items without page reloads — a modern single-page feel. |
| 💳 **Payment Integration** | Secure checkout via **bKash** and **SSLCommerz**. |
| ⚡ **Instant Delivery** | Upon successful payment, users are redirected to a secure token-based download area. |
| 🎧 **Support System** | Integrated contact forms and FAQ section. |

### 🛠️ Administrative Features

| Feature | Description |
|---|---|
| 📊 **Centralized Dashboard** | Overview of total sales, active users, pending orders, and top products. |
| 📦 **Product CRUD** | Full Create/Read/Update/Delete for listings, file uploads, and screenshots. |
| 🗃️ **Category Control** | Organize the marketplace into logical sections (Web Templates, Scripts, Graphics). |
| 📋 **Order & Transaction Tracking** | Detailed payment logs with manual status updates and refunds. |
| 👥 **User Moderation** | View activity, block/unblock suspicious accounts. |
| 🎟️ **Coupon Engine** | Discount codes with expiration dates and usage limits. |
| 👨‍💼 **Staff Management (RBAC)** | **Super Admin** and **Editor** roles with granular permissions. |
| ⚙️ **System Configuration** | Global settings for SMTP email and payment gateway API keys. |

---

## 🛠️ Tech Stack

### Frontend
- **HTML5 & CSS3** — Content structure and custom styling
- **Bootstrap 5** — Mobile-first responsive design, pre-built UI components
- **JavaScript (Vanilla)** — DOM manipulation, client-side validation
- **AJAX** — Asynchronous data exchange (e.g., cart updates without refresh)
- **Font Awesome** — High-quality vector icons

### Backend
- **PHP 7.4+** — Business logic, session management, database communication
- **Apache** — Web server (via XAMPP)

### Database
- **MySQL 5.7+** — Users, products, orders, download tokens
- **SQL** — Schema design and secure prepared statements

### Payment & Email
- **bKash API** — Mobile Financial Services (MFS) transactions
- **SSLCommerz** — Comprehensive online payment gateway
- **SMTP (Gmail)** — Automated order confirmations and password reset links

### Development Tools
- **XAMPP** — Local dev environment (Apache + MySQL + PHP)
- **VS Code** — Primary IDE
- **Git & GitHub** — Version control

---

## 🚦 Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL + PHP 7.4+ stack)
- PHP 7.4+ with `mysqli` extension
- MySQL 5.7+

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Mehedi-Hasan86/Technology-Bruster.git
   ```

2. **Copy the project folder** into your web root (e.g., `htdocs/` for XAMPP):
   ```bash
   cp -r Technology-Bruster/project /path/to/htdocs/project
   ```

3. **Start Apache and MySQL** from the XAMPP Control Panel.

4. **Import the database schema:**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Import `config/schema.sql` — this creates the `technology_bruster` database and all 15 tables.

5. **Configure the database connection** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');            // your MySQL password
   define('DB_NAME', 'technology_bruster');
   ```

6. **Configure site settings** in `config/settings.php`:
   - `SITE_URL` — your local URL (default: `http://localhost/project`)
   - Payment gateway keys (`BKASH_API_KEY`, `SSL_STORE_ID`)
   - SMTP credentials

7. **Create the admin account** by visiting `setup.php` in your browser:
   ```
   http://localhost/project/setup.php
   ```
   This creates a default **Super Admin**:
   - **Email:** `admin@test.com`
   - **Password:** `123456`

   > ⚠️ **Important:** Change the default admin credentials immediately after first login.

8. **Access the platform:**
   - **Storefront:** `http://localhost/project/`
   - **Admin Panel:** `http://localhost/project/?page=admin`

> 💡 **Note:** The `project/` directory is the canonical runnable application. The root-level files are the workspace copies kept for reference.

---

## 🗄️ Database Schema

The database `technology_bruster` consists of **15 relational tables**, normalized to **3NF**:

| Table | Purpose |
|---|---|
| `users` | Registered customers (name, email, bcrypt password, phone, block status) |
| `admin_users` | Administrators with role enum (`super_admin` / `editor`) |
| `categories` | Product categories |
| `products` | Digital products (title, price, discount, file path, download count) |
| `product_screenshots` | Product preview images |
| `cart` | User shopping cart (unique per user+product) |
| `orders` | Order header (total, tax, coupon, payment status, transaction ID) |
| `order_items` | Order line items (resolves Many-to-Many between orders & products) |
| `download_tokens` | Secure, time-limited download links |
| `coupons` | Discount codes (flat/percentage, expiry, usage limits) |
| `support_tickets` | User support requests |
| `faq` | Frequently asked questions |
| `contact_messages` | Contact form submissions |
| `password_resets` | Password reset tokens |
| `settings` | Key-value global configuration |

The ER diagram for the schema is documented in the project report.

---

## 📁 Project Structure

```
Technology-Bruster/
├── project/                  # ⭐ Canonical runnable application
│   ├── api/                  # AJAX endpoints (cart, orders, payment, download, auth)
│   │   ├── admin.php
│   │   ├── auth.php
│   │   ├── cart.php
│   │   ├── coupon.php
│   │   ├── download.php      # Token-based secure file delivery
│   │   ├── orders.php
│   │   └── payment.php       # bKash & SSLCommerz processing
│   ├── classes/              # OOP classes (User, Product, Email)
│   ├── config/
│   │   ├── database.php      # DB connection
│   │   ├── schema.sql        # Full database schema
│   │   └── settings.php      # Global constants & configuration
│   ├── includes/             # Reusable components (navbar, sidebar, footer)
│   ├── pages/
│   │   ├── admin/            # Admin panel pages (dashboard, products, orders, ...)
│   │   ├── auth/             # Login/signup/reset-password
│   │   └── user/             # Storefront pages (products, cart, checkout, ...)
│   ├── public/               # Static assets (css/, js/)
│   ├── uploads/              # Product files & images
│   ├── index.php             # Front controller / router
│   ├── loader.php            # Bootstrap loader (paths, config, session)
│   └── setup.php             # One-click admin creation
├── api/                      # (root-level workspace copy)
├── classes/
├── config/
├── pages/
├── public/
├── uploads/
├── index.php
├── loader.php
├── setup.php
└── .htaccess                 # URL rewriting (pretty URLs)
```

**Routing:** `index.php` acts as a front controller. It sanitizes the `page` GET parameter and maps it to the appropriate page file via `$page_map`. Admin routes require an authenticated `$_SESSION['admin_id']` session and are further gated by role.

---

## 🔒 Security

Security is a cornerstone of Technology-Bruster. Key protections implemented:

| Security Measure | Implementation |
|---|---|
| 🔑 **Password Hashing** | `password_hash()` with **bcrypt** (`PASSWORD_BCRYPT`) |
| 🧼 **SQL Injection Prevention** | **Prepared statements** (`mysqli::prepare` + `bind_param`) throughout |
| 🛡️ **RBAC / Role Gating** | Middleware at the top of admin pages checks the `role` column; unauthorized access triggers a 403 |
| ⏱️ **Token-Based Downloads** | `download_tokens` table generates unique, time-sensitive tokens; `download.php` validates before streaming, hiding the real file path |
| 🚫 **Hotlink Prevention** | Product files stored in a protected directory; direct URL access blocked |
| 📋 **Input Sanitization** | `htmlspecialchars()`, `basename()`, and regex filtering on all user input |
| 🔒 **Session Security** | `SESSION_TIMEOUT` constant (1 hour) for idle session expiry |
| 📁 **File Upload Controls** | Whitelist extensions (`zip`, `rar`, `7z`, `pdf`, `exe`) and `MAX_FILE_SIZE` (500 MB) |

---

## 👥 Team

This project was developed by a dedicated team of four as part of the **CSE-2424 Database Management Systems Lab**:

| Member | ID | Contribution | Role |
|---|---|---|---|
| **Md. Mehedi Hasan Howladar** | C241086 | 35% | **Team Leader** — Database architecture, MySQL schema, admin/editor role logic |
| **A.K.M. Mehraz Haque** | C241105 | 35% | **Frontend Developer & Backend Associate** — Responsive UI, product filtering logic |
| **Mohammad Ahnaf** | C241090 | 15% | **Full-Stack Associate** — FAQ & contact pages |
| **Monjur Uddin** | C241096 | 15% | **Inspiration** — Frontend development support |

---

## 🔮 Future Enhancements

- **Multi-Vendor Marketplace** — Allow third-party developers to create shops, upload assets, and earn commissions.
- **Advanced Security** — Watermarking, Two-Factor Authentication (2FA via OTP), IP-restricted downloads.
- **AI-Powered Recommendations** — "Frequently Bought Together" suggestions and personalized newsletters.
- **Mobile App (Android & iOS)** — Push notifications and offline access via Flutter/React Native.
- **Global Expansion** — Multi-currency support and Stripe/PayPal integration.

---

## 📄 License

This project is developed for educational purposes as part of the **CSE-2424 Database Management Systems Lab** at **International Islamic University Chittagong (IIUC)**.

---

<div align="center">

**⭐ If you find this project useful, give it a star! ⭐**

</div>
