<?php
if (!function_exists('display_price')) {
    function display_price($price) {
        global $conn;

        // Determine the currency ID to use
        $currencyId = $_SESSION['currency_id'] ?? null;

        // If not set in session, try to get from user's preference
        if (empty($currencyId) && !empty($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT currency_id FROM user_currency_preference WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $currencyId = $row['currency_id'];
                $_SESSION['currency_id'] = $currencyId; // store in session for next time
            }
            $stmt->close();
        }

        // Default to PHP if still null
        $currencyId = $currencyId ?? 1;

        // Convert price using the currency_id
        $stmt = $conn->prepare("SELECT ConvertPrice(?, ?) AS converted");
        $stmt->bind_param("di", $price, $currencyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $converted = $row['converted'] ?? $price;
        $stmt->close();

        // Get currency code for display
        $codeStmt = $conn->prepare("SELECT currency_code FROM currencies WHERE currency_id = ?");
        $codeStmt->bind_param("i", $currencyId);
        $codeStmt->execute();
        $codeRes = $codeStmt->get_result();
        $codeRow = $codeRes->fetch_assoc();
        $currencyCode = $codeRow['currency_code'] ?? 'PHP';
        $codeStmt->close();

        return $currencyCode . ' ' . number_format($converted, 2);
    }
}
?>
