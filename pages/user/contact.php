<?php
require_once PROJECT_ROOT . '/loader.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = '❌ All fields are required';
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $success = '✅ Message sent successfully! We\'ll get back to you soon.';
            } else {
                $error = '❌ Failed to send message';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    
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

        .contact-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 50px;
            border-radius: 0 0 30px 30px;
        }

        .contact-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .contact-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .contact-container {
            max-width: 1000px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .info-item {
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: var(--light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .info-content h3 {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .info-content p {
            color: #999;
            font-size: 0.95rem;
            margin: 0;
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .contact-form h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--dark);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
            background: var(--light);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border-left-color: #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #2e7d32;
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .contact-form,
            .contact-info {
                padding: 20px;
            }

            .contact-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<!-- Header -->
<div class="contact-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Get in touch with our team</p>
    </div>
</div>

<div class="container contact-container py-5">
    <!-- Messages -->
    <?php if ($error): ?>
        <div class="message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Contact Grid -->
    <div class="contact-grid" data-aos="fade-up">
        <!-- Contact Info -->
        <div class="contact-info">
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 30px;">Get In Touch</h2>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-content">
                    <h3>Address</h3>
                    <p>Dhaka, Bangladesh</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="info-content">
                    <h3>Phone</h3>
                    <p>+880 1234567890</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <h3>Email</h3>
                    <p>support@technologybruster.com</p>
                </div>
            </div>

            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <h3>Business Hours</h3>
                    <p>Mon - Fri: 9:00 AM - 6:00 PM</p>
                </div>
            </div>

            <hr style="margin: 30px 0;">

            <h3 style="font-weight: 700; margin-bottom: 15px;">Follow Us</h3>
            <div style="display: flex; gap: 12px;">
                <a href="#" style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); text-decoration: none; transition: all 0.3s;">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); text-decoration: none; transition: all 0.3s;">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); text-decoration: none; transition: all 0.3s;">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" style="width: 40px; height: 40px; background: var(--light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); text-decoration: none; transition: all 0.3s;">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form" data-aos="fade-up" data-aos-delay="100">
            <h2>Send us a Message</h2>

            <form method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Your name" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" placeholder="Your email" required>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control" name="subject" placeholder="Message subject" required>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" name="message" placeholder="Your message..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<!-- Bootstrap & AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    AOS.init({ duration: 1000, once: true });

    // Auto-hide messages
    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => {
            msg.style.display = 'none';
        });
    }, 5000);
</script>

</body>
</html>
