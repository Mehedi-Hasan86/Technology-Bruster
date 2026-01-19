<?php
// Loader already included by index.php

// Get FAQs from database
$faqs = $conn->query("SELECT * FROM faq WHERE is_active = 1 ORDER BY created_at DESC");
$faq_list = $faqs ? $faqs->fetch_all(MYSQLI_ASSOC) : [];

// Default FAQs if none in database
$default_faqs = [
    [
        'id' => 1,
        'question' => 'How do I download my purchased products?',
        'answer' => 'After successful payment, you\'ll receive download links immediately. You can access them from your Orders page anytime within 30 days of purchase.',
        'category' => 'Downloads'
    ],
    [
        'id' => 2,
        'question' => 'What payment methods do you accept?',
        'answer' => 'We accept multiple payment methods including bKash, SSLCommerz, and Bank Transfers. Choose your preferred method during checkout.',
        'category' => 'Payment'
    ],
    [
        'id' => 3,
        'question' => 'Do you offer refunds?',
        'answer' => 'Yes! We offer a 30-day money-back guarantee if you\'re not satisfied with your purchase. Contact support for refund requests.',
        'category' => 'Refunds'
    ],
    [
        'id' => 4,
        'question' => 'How long can I download my products?',
        'answer' => 'You have 30 days from the date of purchase to download your products. After that, you can still access them from your account but may need to request a new download link.',
        'category' => 'Downloads'
    ],
    [
        'id' => 5,
        'question' => 'Can I share my account?',
        'answer' => 'For security and licensing reasons, we recommend not sharing your account details. Each purchase is tied to individual accounts.',
        'category' => 'Account'
    ],
    [
        'id' => 6,
        'question' => 'How do I apply a coupon code?',
        'answer' => 'During checkout, you\'ll see a "Apply Coupon" section. Enter your coupon code and click Apply. The discount will be calculated automatically.',
        'category' => 'Coupons'
    ]
];

$faqs_display = !empty($faq_list) ? $faq_list : $default_faqs;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - <?php echo SITE_NAME; ?></title>
    
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
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .faq-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 50px;
            border-radius: 20px;
            text-align: center;
        }

        .faq-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .faq-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .search-box {
            margin: 30px 0;
        }

        .search-input {
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        .category-filter {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            justify-content: center;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .category-btn:hover,
        .category-btn.active {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
        }

        .faq-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        .accordion-item {
            border: none;
            margin-bottom: 15px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .accordion-button {
            background: var(--light);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 18px 24px;
            font-weight: 600;
            font-size: 1rem;
            color: var(--dark);
            transition: all 0.3s;
        }

        .accordion-button:not(.collapsed) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: var(--primary);
        }

        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            filter: brightness(0);
        }

        .accordion-button:not(.collapsed)::after {
            filter: brightness(100);
        }

        .accordion-body {
            padding: 24px;
            background: white;
            color: #666;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .faq-category {
            display: inline-block;
            background: var(--light);
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
        }

        .no-results i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }

        .no-results p {
            color: #999;
            font-size: 1.1rem;
        }

        .contact-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 50px;
        }

        .contact-cta h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .contact-cta p {
            margin-bottom: 20px;
            font-size: 1rem;
            opacity: 0.9;
        }

        .btn-contact {
            background: white;
            color: var(--primary);
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="container py-5">
    <!-- Header -->
    <div class="faq-header" data-aos="fade-up">
        <h1>Frequently Asked Questions</h1>
        <p>Find answers to common questions about our platform and services</p>
    </div>

    <!-- Search & Filter -->
    <div class="search-box" data-aos="fade-up" data-aos-delay="100">
        <input type="text" id="search-input" class="search-input form-control" 
               placeholder="🔍 Search FAQs..." onkeyup="filterFAQs()">
    </div>

    <div class="category-filter" data-aos="fade-up" data-aos-delay="200">
        <button class="category-btn active" onclick="filterByCategory('all')">All</button>
        <button class="category-btn" onclick="filterByCategory('Downloads')">Downloads</button>
        <button class="category-btn" onclick="filterByCategory('Payment')">Payment</button>
        <button class="category-btn" onclick="filterByCategory('Refunds')">Refunds</button>
        <button class="category-btn" onclick="filterByCategory('Account')">Account</button>
        <button class="category-btn" onclick="filterByCategory('Coupons')">Coupons</button>
    </div>

    <!-- FAQ List -->
    <div class="faq-container" data-aos="fade-up" data-aos-delay="300">
        <div id="no-results" class="no-results" style="display: none;">
            <i class="fas fa-search"></i>
            <p>No FAQs found matching your search</p>
        </div>

        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs_display as $index => $faq): ?>
                <div class="accordion-item faq-item" data-category="<?php echo htmlspecialchars($faq['category'] ?? 'General'); ?>" 
                     data-question="<?php echo strtolower(htmlspecialchars($faq['question'])); ?>">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#faq<?php echo $index; ?>">
                            <i class="fas fa-question-circle me-2"></i>
                            <?php echo htmlspecialchars($faq['question']); ?>
                        </button>
                    </h2>
                    <div id="faq<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php if (isset($faq['category'])): ?>
                                <div class="faq-category"><?php echo htmlspecialchars($faq['category']); ?></div>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contact CTA -->
    <div class="contact-cta" data-aos="fade-up" data-aos-delay="400">
        <h3>Still need help?</h3>
        <p>Can't find the answer you're looking for? Our support team is here to help!</p>
        <a href="<?php echo SITE_URL; ?>/?page=support" class="btn-contact">
            <i class="fas fa-headset"></i> Contact Support
        </a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    AOS.init({
        duration: 1000,
        once: true
    });

    let currentCategory = 'all';

    function filterFAQs() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const faqs = document.querySelectorAll('.faq-item');
        let visibleCount = 0;

        faqs.forEach(faq => {
            const question = faq.getAttribute('data-question');
            const category = faq.getAttribute('data-category');
            const matchesSearch = question.includes(searchTerm);
            const matchesCategory = currentCategory === 'all' || category === currentCategory;
            
            if (matchesSearch && matchesCategory) {
                faq.style.display = 'block';
                visibleCount++;
            } else {
                faq.style.display = 'none';
            }
        });

        document.getElementById('no-results').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function filterByCategory(category) {
        currentCategory = category;
        
        // Update button states
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        filterFAQs();
    }
</script>

</body>
</html>
