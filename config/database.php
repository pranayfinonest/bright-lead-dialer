<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'telecrm');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Global database connection
$database = new Database();
$db = $database->getConnection();

// Helper functions
function getDashboardStats() {
    global $db;
    
    $stats = [];
    
    // Get today's calls
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM calls WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['calls_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get leads generated this week
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE WEEK(created_at) = WEEK(NOW())");
    $stmt->execute();
    $stats['leads_generated'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Calculate conversion rate
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM calls WHERE disposition = 'converted' AND MONTH(created_at) = MONTH(NOW())) as converted,
            (SELECT COUNT(*) FROM calls WHERE MONTH(created_at) = MONTH(NOW())) as total
    ");
    $stmt->execute();
    $conversion_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['conversion_rate'] = $conversion_data['total'] > 0 ? 
        round(($conversion_data['converted'] / $conversion_data['total']) * 100, 1) : 0;
    
    // Get revenue this month
    $stmt = $db->prepare("SELECT SUM(revenue) as total FROM conversions WHERE MONTH(created_at) = MONTH(NOW())");
    $stmt->execute();
    $stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Get successful calls today
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM calls WHERE DATE(created_at) = CURDATE() AND disposition IN ('connected', 'converted')");
    $stmt->execute();
    $stats['successful_calls'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get failed calls today
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM calls WHERE DATE(created_at) = CURDATE() AND disposition IN ('no_answer', 'busy', 'failed')");
    $stmt->execute();
    $stats['failed_calls'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Calculate success rate
    $total_today = $stats['successful_calls'] + $stats['failed_calls'];
    $stats['success_rate'] = $total_today > 0 ? 
        round(($stats['successful_calls'] / $total_today) * 100, 1) : 0;
    $stats['failed_rate'] = $total_today > 0 ? 
        round(($stats['failed_calls'] / $total_today) * 100, 1) : 0;
    
    // Get callbacks scheduled for today
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM callbacks WHERE DATE(scheduled_at) = CURDATE()");
    $stmt->execute();
    $stats['callbacks'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return $stats;
}

function getPendingLeadsCount() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE status = 'pending'");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function getSessionDuration() {
    if (isset($_SESSION['login_time'])) {
        $duration = time() - $_SESSION['login_time'];
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        return sprintf('%dh %dm', $hours, $minutes);
    }
    return '0h 0m';
}

function formatCurrency($amount) {
    if ($amount >= 100000) {
        return number_format($amount / 100000, 1) . 'L';
    } elseif ($amount >= 1000) {
        return number_format($amount / 1000, 1) . 'K';
    }
    return number_format($amount);
}

function getHighPriorityCallbacks() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM callbacks WHERE priority = 'high' AND DATE(scheduled_at) = CURDATE()");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function getNewLeadsCount() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

function getLastUpdateTime() {
    return '5 min ago';
}
?>