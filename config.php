<?php
// config.php - System configuration

define('APP_NAME', 'Smart Parking System');
define('APP_URL', 'http://localhost/smartparking');
define('APP_VERSION', '1.0.0');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartparking');   // تم التصحيح
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_PORT', 3306);

// Payment Configuration
define('PAYMENT_GATEWAY', 'stripe');
define('STRIPE_KEY', 'pk_live_xxx');
define('STRIPE_SECRET', 'sk_live_xxx');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);
define('SMTP_FROM', 'noreply@smartparking.com');

// Security
define('SESSION_TIMEOUT', 3600);
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCK_TIME', 900);

// Features
define('GRACE_PERIOD', 15);
define('MAX_EXTENSION_HOURS', 24);
define('FINE_RATE', 5);
define('PLATFORM_FEE', 15);
define('MUNICIPAL_LEVY', 10);

// Loyalty Tiers
define('LOYALTY_BRONZE', 100);
define('LOYALTY_SILVER', 500);
define('LOYALTY_GOLD', 1000);

// File Upload
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// APIs
define('GOOGLE_MAPS_API_KEY', 'your-key-here');
define('WEATHER_API_KEY', 'your-key-here');

// لا نبدأ الجلسة هنا؛ auth.php يتولى ذلك