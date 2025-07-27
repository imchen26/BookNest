<?php
if (!function_exists('display_price')) {
    function display_price($price) {
        global $conn;

        if (!empty($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            $stmt = $conn->prepare("SELECT ConvertPrice(?, ?) AS converted");
            $stmt->bind_param("di", $price, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $converted = $row['converted'] ?? $price;

            $codeStmt = $conn->prepare("SELECT c.currency_code 
                                        FROM user_currency_preference ucp
                                        JOIN currencies c ON ucp.currency_id = c.currency_id
                                        WHERE ucp.user_id = ?");
            $codeStmt->bind_param("i", $userId);
            $codeStmt->execute();
            $codeRes = $codeStmt->get_result();
            $codeRow = $codeRes->fetch_assoc();
            $currencyCode = $codeRow['currency_code'] ?? 'PHP';

            return $currencyCode . ' ' . number_format($converted, 2);
        }

        $currencyId = $_SESSION['currency_id'] ?? 1;
        $stmt = $conn->prepare("SELECT exchange_rate, currency_code FROM currencies WHERE currency_id = ?");
        $stmt->bind_param("i", $currencyId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currency = $result->fetch_assoc();

        $rate = $currency['exchange_rate'] ?? 1.0;
        $code = $currency['currency_code'] ?? 'PHP';
        $converted = $price * $rate;

        return $code . ' ' . number_format($converted, 2);
    }
}
?>
