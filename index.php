<?php
// index.php – Main Router (secure, no direct view access)

require_once 'core/DBController.php';
require_once 'includes/auth.php';

// Models
require_once 'models/UserModel.php';
require_once 'models/ParkingSpotModel.php';
require_once 'models/ReservationModel.php';
require_once 'models/PaymentModel.php';
require_once 'models/FineModel.php';
require_once 'models/LoyaltyModel.php';
require_once 'models/AppealModel.php';
require_once 'models/ExtensionModel.php';
require_once 'models/WaitlistModel.php';
require_once 'models/QRModel.php';

// Controllers
require_once 'controllers/HomeController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/DriverController.php';
require_once 'controllers/OwnerController.php';
require_once 'controllers/QRController.php';

$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action'] ?? 'index';

$controllerClass = ucfirst($controller) . 'Controller';
$controllerFile  = __DIR__ . '/controllers/' . $controllerClass . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $ctrl = new $controllerClass();
    if (method_exists($ctrl, $action)) {
        $ctrl->$action();
        exit;
    }
}

// إذا لم يتم العثور على أي إجراء – 404
http_response_code(404);
echo "Page not found";