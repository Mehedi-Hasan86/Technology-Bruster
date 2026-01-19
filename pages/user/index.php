<?php
// Loader already included by index.php

// Get featured products
$featured = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6");
$featured_products = $featured ? $featured->fetch_all(MYSQLI_ASSOC) : [];

// Get all categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$category_list = $categories ? $categories->fetch_all(MYSQLI_ASSOC) : [];

// Get testimonials (from sample data or hardcoded)
$testimonials = [
    [
        'name' => 'Sadman Molla',
        'text' => 'I have bought laptop for my Girlfriend and the servies is great!',
        'rating' => 5
    ],
    [
        'name' => 'Rifat Hossain',
        'text' => 'Good place to buy goods at suitable price',
        'rating' => 5
    ],
    [
        'name' => 'Javed Mahmud',
        'text' => 'I bought my Mac from here and im willing to come again!.',
        'rating' => 5
    ]
];

// Get FAQs
$faqs = $conn->query("SELECT * FROM faq WHERE is_active = 1 LIMIT 6");
$faq_items = $faqs ? $faqs->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Premium Digital Products</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background: white;
        }

        /* Hero Section */
        .hero-section {
            background: white;
            min-height: 600px;
            display: flex;
            align-items: center;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100px;
            width: 500px;
            height: 500px;
            background: rgba(0, 123, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--secondary);
            margin-bottom: 30px;
        }

        .hero-btn {
            padding: 14px 40px;
            font-size: 1rem;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }

        .hero-image {
            position: relative;
            z-index: 2;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.1));
        }

        /* Categories Section */
        .category-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }

        .category-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .category-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .category-count {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        /* Featured Products Section */
        .featured-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 50px;
            text-align: center;
            position: relative;
            padding-bottom: 20px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .product-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .product-description {
            font-size: 0.9rem;
            color: var(--secondary);
            margin-bottom: 12px;
            flex-grow: 1;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .product-features {
            font-size: 0.85rem;
            color: var(--secondary);
            margin-bottom: 15px;
        }

        .product-features i {
            color: var(--primary);
            margin-right: 8px;
        }

        .product-btn {
            align-self: stretch;
            padding: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .product-btn:hover {
            background: #0056b3;
        }

        /* Sort Buttons */
        .sort-btn {
            padding: 10px 16px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }

        .sort-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .sort-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 80px 0;
            background: var(--light);
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: 100%;
            position: relative;
            border-left: 4px solid var(--primary);
        }

        .testimonial-quote {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .testimonial-text {
            font-style: italic;
            color: var(--secondary);
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .testimonial-author {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .stars {
            color: #ffc107;
            font-size: 0.9rem;
        }

        /* FAQ Section */
        .faq-section {
            padding: 80px 0;
            background: white;
        }

        .accordion-button {
            background-color: transparent;
            font-weight: 600;
            color: var(--dark);
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background-color: var(--light);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: var(--primary);
        }

        .accordion-body {
            padding: 15px 20px;
            color: var(--secondary);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 0;
        }

        .footer-section h5 {
            margin-bottom: 20px;
            font-weight: 700;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: white;
            padding-left: 5px;
        }

        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .footer-bottom {
            background: rgba(0, 0, 0, 0.2);
            padding: 20px 0;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<section style="padding: 0px 60px;">
    <!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-12" data-aos="fade-right" data-aos-duration="1000">
                <div class="hero-content">
                    <h1>Welcome to <?php echo SITE_NAME; ?></h1>
                    <p>Discover premium digital products for your needs. Instant access, secure downloads, and professional support.</p>
                    <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary hero-btn">
                        <i class="fas fa-arrow-right"></i> Explore Products
                    </a>
                </div>
            </div>
            <div class="col-lg-6 col-12 hero-image" data-aos="fade-left" data-aos-duration="1000">
<div style="display:flex; justify-content:center; align-items:center;">
    <img src="/project/pages/user/anaf.png"
         alt="Logo"
         style="
            width:500px;
            height:500px;
            border-radius:50%;
            object-fit:cover;
            border:2px solid #11aaf7ff;
            transition: all 0.4s ease;
         "
         onmouseover="
            this.style.boxShadow='0 0 15px rgba(16, 179, 255, 0.72), 0 0 30px rgba(0,123,255,0.4)';
            this.style.transform='scale(1.05)';
         "
         onmouseout="
            this.style.boxShadow='none';
            this.style.transform='scale(1)';
         ">
</div>


            </div>
        </div>
    </div>
</section>

<!-- Categories Section with Beautiful Container -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up" style="margin-bottom: 60px;">
            🛍️ Shop by Category
        </h2>
        
        <!-- Category Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 24px;" data-aos="fade-up" data-aos-delay="100">
            <!-- Phone -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=1" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E72 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(255, 107, 107, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(255, 107, 107, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-mobile-alt"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Phones</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">Latest Models</div>
                </div>
            </a>

            <!-- Drone -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=2" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #4ECDC4 0%, #44A08D 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(78, 205, 196, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(78, 205, 196, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(78, 205, 196, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-cube"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Drones</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">4K & Beyond</div>
                </div>
            </a>

            <!-- Camera -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=3" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #F77F00 0%, #FCBF49 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(247, 127, 0, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(247, 127, 0, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(247, 127, 0, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-camera"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Cameras</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">DSLR & Digital</div>
                </div>
            </a>

            <!-- Computer -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=4" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(102, 126, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(102, 126, 234, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-desktop"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Computers</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">Desktops & All-in-One</div>
                </div>
            </a>

            <!-- Laptop -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=5" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #FA8072 0%, #FF6347 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(250, 128, 114, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(250, 128, 114, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(250, 128, 114, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-laptop"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Laptops</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">Gaming & Professional</div>
                </div>
            </a>

            <!-- Router -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=6" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #00D4FF 0%, #0099FF 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(0, 212, 255, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(0, 212, 255, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-wifi"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Routers</div>
                    <div style="color: rgba(255,255,255,0.8); font-size: 0.8rem; margin-top: 4px;">5G & Wi-Fi 6</div>
                </div>
            </a>

            <!-- TV -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=7" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #A8EDEA 0%, #FED6E3 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(168, 237, 234, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(168, 237, 234, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(168, 237, 234, 0.3)';">
                    <i style="font-size: 3rem; color: #333; margin-bottom: 12px;" class="fas fa-tv"></i>
                    <div style="color: #333; font-weight: 700; font-size: 1rem;">TV</div>
                    <div style="color: rgba(51,51,51,0.7); font-size: 0.8rem; margin-top: 4px;">4K & Smart TV</div>
                </div>
            </a>

            <!-- Hardware -->
            <a href="<?php echo SITE_URL; ?>/?page=products&category=8" style="text-decoration: none;">
                <div style="background: linear-gradient(135deg, #2E2E2E 0%, #1A1A1A 100%); border-radius: 20px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 30px rgba(46, 46, 46, 0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;" onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 20px 50px rgba(46, 46, 46, 0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 10px 30px rgba(46, 46, 46, 0.3)';">
                    <i style="font-size: 3rem; color: white; margin-bottom: 12px;" class="fas fa-microchip"></i>
                    <div style="color: white; font-weight: 700; font-size: 1rem;">Hardware</div>
                    <div style="color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-top: 4px;">Components & Parts</div>
                </div>
            </a>
        </div>

        <!-- View All Button -->
        <div style="text-align: center; margin-top: 40px;" data-aos="fade-up" data-aos-delay="200">
            <a href="<?php echo SITE_URL; ?>/?page=products" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 40px; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-block; box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3); transition: all 0.3s;">
                View All Categories <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="featured-section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
            <h2 class="section-title" data-aos="fade-up">Featured Products</h2>
            
            <!-- Sort Options -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap;" data-aos="fade-up">
                <button onclick="sortFeatured('newest')" class="sort-btn active" title="Newest First">
                    <i class="fas fa-calendar-alt"></i> Newest
                </button>
                <button onclick="sortFeatured('price-low')" class="sort-btn" title="Price: Low to High">
                    <i class="fas fa-arrow-up-long"></i> Price ↑
                </button>
                <button onclick="sortFeatured('price-high')" class="sort-btn" title="Price: High to Low">
                    <i class="fas fa-arrow-down-long"></i> Price ↓
                </button>
                <button onclick="sortFeatured('popular')" class="sort-btn" title="Most Popular">
                    <i class="fas fa-fire"></i> Trending
                </button>
            </div>
        </div>
        
        <?php if (!empty($featured_products)): ?>
            <div class="row g-4" id="featured-products-list">
                <?php foreach ($featured_products as $index => $product): ?>
                    <div class="col-lg-4 col-md-6 col-12 product-item" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>" data-price="<?php echo $product['price']; ?>" data-downloads="<?php echo $product['downloads_count']; ?>" data-date="<?php echo strtotime($product['created_at']); ?>">
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image_path']) && file_exists(PRODUCT_IMG_DIR . $product['image_path'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-cube"></i>
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <h5 class="product-name"><?php echo htmlspecialchars(substr($product['title'], 0, 40)); ?></h5>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 60)); ?>...</p>
                                <div class="product-features">
                                    <i class="fas fa-bolt"></i> Fast Access
                                    <i class="fas fa-shield-alt"></i> Secure
                                </div>
                                <div class="product-price"><?php echo CURRENCY; ?> <?php echo number_format($product['price'], 0); ?></div>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?php echo SITE_URL; ?>/?page=product&id=<?php echo $product['id']; ?>" class="product-btn" style="flex: 1; text-align: center; text-decoration: none; color: white;">View</a>
                                    <button onclick="addToCart(<?php echo $product['id']; ?>)" class="product-btn" style="flex: 1; background: #28a745;">Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary hero-btn">
                    View All Products <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center" data-aos="fade-up">
                <i class="fas fa-info-circle"></i> No products available yet. Check back soon!
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">What Customers Say</h2>
        
        <div class="row g-4">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="col-lg-4 col-md-6 col-12" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p class="testimonial-text"><?php echo htmlspecialchars($testimonial['text']); ?></p>
                        <div class="testimonial-author"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                        <div class="stars">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Frequently Asked Questions</h2>
        
        <div class="row" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-8 offset-lg-2">
                <div class="accordion" id="faqAccordion">
                    <?php 
                    $default_faqs = [
                        ['q' => 'How do I download my products?', 'a' => 'After purchase, you\'ll receive download links instantly. You can access them from your orders page anytime.'],
                        ['q' => 'Are products DRM protected?', 'a' => 'Our products are delivered securely with unique download tokens for each purchase.'],
                        ['q' => 'What\'s your refund policy?', 'a' => 'We offer a 30-day money-back guarantee if you\'re not satisfied with your purchase.'],
                        ['q' => 'How long can I download my products?', 'a' => 'You have 30 days from purchase to download your products. You can re-download anytime from your account.'],
                        ['q' => 'Do you offer payment plans?', 'a' => 'Yes! We support multiple payment methods including bKash, SSLCommerz, and bank transfers.'],
                        ['q' => 'Is customer support available?', 'a' => 'Absolutely! Our support team is available Mon-Fri, 9 AM - 6 PM. Contact us anytime.']
                    ];
                    
                    $faqs_to_display = !empty($faq_items) ? $faq_items : $default_faqs;
                    
                    foreach ($faqs_to_display as $index => $faq): 
                    ?>
                        <div class="accordion-item border-0 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?php echo $index; ?>">
                                    <i class="fas fa-question-circle me-2"></i>
                                    <?php echo htmlspecialchars($faq['question'] ?? $faq['q']); ?>
                                </button>
                            </h2>
                            <div id="faq<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo htmlspecialchars($faq['answer'] ?? $faq['a']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row mt-5" data-aos="fade-up">
            <div class="col-12 text-center">
                <p class="text-muted">Didn't find your answer?</p>
                <a href="<?php echo SITE_URL; ?>/?page=contact" class="btn btn-primary hero-btn">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>
</section>

<!-- Footer -->
<footer style="padding: 0px 80px">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-12 footer-section mb-4">
                <h5><i class="fas fa-cube"></i> <?php echo SITE_NAME; ?></h5>
                <p class="text-muted">Your trusted platform for premium digital products with instant access and secure downloads.</p>
            </div>

            <div class="col-lg-3 col-md-6 col-12 footer-section mb-4">
                <h5>Quick Links</h5>
                <a href="<?php echo SITE_URL; ?>/?page=products" class="footer-link">Products</a>
                <a href="<?php echo SITE_URL; ?>/?page=faq" class="footer-link">FAQ</a>
                <a href="<?php echo SITE_URL; ?>/?page=contact" class="footer-link">Contact</a>
            </div>

            <div class="col-lg-3 col-md-6 col-12 footer-section mb-4">
                <h5>Support</h5>
                <a href="<?php echo SITE_URL; ?>/?page=support" class="footer-link">Support Tickets</a>
                <a href="<?php echo SITE_URL; ?>/?page=contact" class="footer-link">Contact Support</a>
                <a href="<?php echo SITE_URL; ?>/?page=help" class="footer-link">Help Center</a>
            </div>

            <div class="col-lg-3 col-md-6 col-12 footer-section mb-4">
                <h5>Follow Us</h5>
                <div class="social-icons">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | <a href="#" style="color: rgba(255,255,255,0.7); text-decoration: none;">Privacy Policy</a> | <a href="#" style="color: rgba(255,255,255,0.7); text-decoration: none;">Terms of Service</a></p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<!-- Main JS -->
<script src="<?php echo SITE_URL; ?>/public/js/main.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        once: true,
        offset: 100
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Sort Featured Products
    function sortFeatured(sortType) {
        const productList = document.getElementById('featured-products-list');
        const productItems = Array.from(document.querySelectorAll('#featured-products-list .product-item'));
        
        // Remove active class from all buttons
        document.querySelectorAll('.sort-btn').forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked button
        event.target.closest('.sort-btn').classList.add('active');

        // Sort products
        productItems.sort((a, b) => {
            switch(sortType) {
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'popular':
                    return parseFloat(b.dataset.downloads) - parseFloat(a.dataset.downloads);
                case 'newest':
                default:
                    return parseFloat(b.dataset.date) - parseFloat(a.dataset.date);
            }
        });

        // Reorder DOM elements
        productItems.forEach(item => {
            productList.appendChild(item);
        });

        // Reinitialize AOS for animation
        if (typeof AOS !== 'undefined') {
            AOS.refreshHard();
        }
    }
</script>

</body>
</html>
