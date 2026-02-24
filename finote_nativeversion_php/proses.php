<?php
include 'koneksi.php';
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['action'])) {
    if ($input['action'] === 'add_transaction') {
        $query = "INSERT INTO transactions (user_id, title, amount, type) VALUES (1, '{$input['title']}', '{$input['amount']}', '{$input['type']}')";
        if(mysqli_query($conn, $query)) echo json_encode(['status' => 'success']);
    }
}
?>