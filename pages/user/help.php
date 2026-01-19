<?php
// Loader already included by index.php

$articles = [
    [
        'id' => 1,
        'title' => 'Getting Started',
        'icon' => 'fa-rocket',
        'articles' => [
            ['title' => 'How to Create an Account', 'slug' => 'create-account'],
            ['title' => 'Complete Your Profile', 'slug' => 'complete-profile'],
            ['title' => 'First Purchase Guide', 'slug' => 'first-purchase'],
            ['title' => 'Verify Your Email', 'slug' => 'verify-email']
        ]
    ],
    [
        'id' => 2,
        'title' => 'Products & Downloads',
        'icon' => 'fa-download',
        'articles' => [
            ['title' => 'Browsing Products', 'slug' => 'browse-products'],
            ['title' => 'Product Details', 'slug' => 'product-details'],
            ['title' => 'Download Your Products', 'slug' => 'download-products'],
            ['title' => 'Download Limits', 'slug' => 'download-limits']
        ]
    ],
    [
        'id' => 3,
        'title' => 'Purchasing & Payment',
        'icon' => 'fa-credit-card',
        'articles' => [
            ['title' => 'Shopping Cart Guide', 'slug' => 'shopping-cart'],
            ['title' => 'Checkout Process', 'slug' => 'checkout-process'],
            ['title' => 'Payment Methods', 'slug' => 'payment-methods'],
            ['title' => 'Apply Coupon Codes', 'slug' => 'apply-coupons']
        ]
    ],
    [
        'id' => 4,
        'title' => 'Account & Security',
        'icon' => 'fa-lock',
        'articles' => [
            ['title' => 'Update Your Profile', 'slug' => 'update-profile'],
            ['title' => 'Change Password', 'slug' => 'change-password'],
            ['title' => 'Account Security', 'slug' => 'account-security'],
            ['title' => 'Two-Factor Authentication', 'slug' => '2fa']
        ]
    ],
    [
        'id' => 5,
        'title' => 'Refunds & Returns',
        'icon' => 'fa-undo',
        'articles' => [
            ['title' => 'Refund Policy', 'slug' => 'refund-policy'],
            ['title' => 'Request a Refund', 'slug' => 'request-refund'],
            ['title' => 'Refund Status', 'slug' => 'refund-status'],
            ['title' => 'Return Process', 'slug' => 'return-process']
        ]
    ],
    [
        'id' => 6,
        'title' => 'Troubleshooting',
        'icon' => 'fa-tools',
        'articles' => [
            ['title' => 'Download Issues', 'slug' => 'download-issues'],
            ['title' => 'Payment Failed', 'slug' => 'payment-failed'],
            ['title' => 'Account Access', 'slug' => 'account-access'],
            ['title' => 'Technical Support', 'slug' => 'technical-support']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- AOS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    
    <style>
        :root {
            --primary: #007bff;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .help-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
            border-radius: 0 0 30px 30px;
        }

        .help-header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .help-header p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 14px 20px 14px 45px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            padding: 40px 0;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
            border-left: 4px solid var(--primary);
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-left-color: #764ba2;
        }

        .category-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
            display: inline-block;
            width: 60px;
            height: 60px;
            background: var(--light);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .article-list {
            display: none;
        }

        .article-list.active {
            display: block;
        }

        .article-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .article-item:last-child {
            border-bottom: none;
        }

        .article-link {
            color: #666;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .article-link:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .article-count {
            font-size: 0.85rem;
            color: #999;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .quick-links {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .quick-links h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--dark);
        }

        .quick-link-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 12px;
            background: var(--light);
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .quick-link-item:hover {
            background: var(--primary);
            color: white;
            transform: translateX(5px);
        }

        .quick-link-icon {
            font-size: 1.5rem;
            min-width: 30px;
            text-align: center;
        }

        .quick-link-text h4 {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .quick-link-text p {
            margin: 4px 0 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 50px 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 50px;
        }

        .cta-section h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .cta-section p {
            font-size: 1.05rem;
            margin-bottom: 20px;
            opacity: 0.95;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 12px 30px;
            border: 2px solid white;
            background: transparent;
            color: white;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-btn:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .cta-btn.primary {
            background: white;
            color: #667eea;
        }

        .cta-btn.primary:hover {
            background: transparent;
            color: white;
        }

        @media (max-width: 768px) {
            .help-header {
                padding: 50px 0;
            }

            .help-header h1 {
                font-size: 2rem;
            }

            .category-grid {
                grid-template-columns: 1fr;
            }

            .quick-links {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<!-- Header -->
<div class="help-header">
    <div class="container">
        <h1>Help Center</h1>
        <p>Find answers and get support</p>
        
        <div class="search-box" data-aos="fade-up" data-aos-delay="100">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="search-articles" 
                   placeholder="Search articles..." onkeyup="searchArticles()">
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Categories Grid -->
    <div class="category-grid" data-aos="fade-up" data-aos-delay="200">
        <?php foreach ($articles as $index => $category): ?>
            <div class="category-card" onclick="toggleCategory(<?php echo $index; ?>)">
                <div class="category-icon">
                    <i class="fas <?php echo $category['icon']; ?>"></i>
                </div>
                <h3 class="category-title"><?php echo htmlspecialchars($category['title']); ?></h3>
                
                <div class="article-list" id="category-<?php echo $index; ?>">
                    <?php foreach ($category['articles'] as $article): ?>
                        <div class="article-item">
                            <a href="<?php echo SITE_URL; ?>/?page=help&article=<?php echo $article['slug']; ?>" class="article-link">
                                <i class="fas fa-chevron-right"></i>
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="article-count">
                    <i class="fas fa-file-alt"></i> <?php echo count($category['articles']); ?> articles
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Links -->
    <div class="quick-links" data-aos="fade-up" data-aos-delay="300">
        <h2><i class="fas fa-lightning-bolt"></i> Quick Links</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <a href="<?php echo SITE_URL; ?>/?page=faq" class="quick-link-item">
                <div class="quick-link-icon"><i class="fas fa-question-circle"></i></div>
                <div class="quick-link-text">
                    <h4>FAQ</h4>
                    <p>Frequently asked questions</p>
                </div>
            </a>

            <a href="<?php echo SITE_URL; ?>/?page=support" class="quick-link-item">
                <div class="quick-link-icon"><i class="fas fa-headset"></i></div>
                <div class="quick-link-text">
                    <h4>Support Tickets</h4>
                    <p>Contact our support team</p>
                </div>
            </a>

            <a href="<?php echo SITE_URL; ?>/?page=contact" class="quick-link-item">
                <div class="quick-link-icon"><i class="fas fa-envelope"></i></div>
                <div class="quick-link-text">
                    <h4>Contact Us</h4>
                    <p>Send us a message</p>
                </div>
            </a>

            <a href="<?php echo SITE_URL; ?>/?page=products" class="quick-link-item">
                <div class="quick-link-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="quick-link-text">
                    <h4>Browse Products</h4>
                    <p>View all products</p>
                </div>
            </a>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section" data-aos="fade-up" data-aos-delay="400">
        <h3>Can't find what you're looking for?</h3>
        <p>Our support team is here to help you 24/7</p>
        <div class="cta-buttons">
            <a href="<?php echo SITE_URL; ?>/?page=support" class="cta-btn primary">
                <i class="fas fa-paper-plane"></i> Create Support Ticket
            </a>
            <a href="<?php echo SITE_URL; ?>/?page=contact" class="cta-btn">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<!-- Bootstrap & AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    AOS.init({ duration: 1000, once: true });

    let openCategory = -1;

    function toggleCategory(index) {
        const categoryList = document.getElementById('category-' + index);
        
        // Close previous open category
        if (openCategory !== -1 && openCategory !== index) {
            document.getElementById('category-' + openCategory).classList.remove('active');
        }
        
        categoryList.classList.toggle('active');
        openCategory = categoryList.classList.contains('active') ? index : -1;
    }

    function searchArticles() {
        const searchTerm = document.getElementById('search-articles').value.toLowerCase();
        const cards = document.querySelectorAll('.category-card');

        cards.forEach(card => {
            const title = card.querySelector('.category-title').textContent.toLowerCase();
            const articles = card.querySelectorAll('.article-link');
            let found = title.includes(searchTerm);

            articles.forEach(article => {
                const text = article.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    found = true;
                    article.parentElement.style.display = 'block';
                } else {
                    article.parentElement.style.display = 'none';
                }
            });

            card.style.display = found ? 'block' : 'none';
        });
    }
</script>

</body>
</html>
