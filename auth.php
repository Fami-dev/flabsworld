<?php
/**
 * File: auth.php
 * Versi: Final dan siap deploy untuk struktur folder root.
 */

header('Content-Type: application/json');

function send_json_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

set_exception_handler(function ($e) {
    error_reporting(0);
    ini_set('display_errors', 0);
    send_json_response(false, "Kesalahan Server (Exception). Periksa log server atau detail koneksi DB.");
});
set_error_handler(function ($severity, $message, $file, $line) {
    error_reporting(0);
    ini_set('display_errors', 0);
    send_json_response(false, "Kesalahan Server (Error). Periksa log server.");
});

// Karena file ada di folder yang sama, path ini sudah benar.
require_once 'db_connect.php';

if (!isset($conn) || $conn->connect_error) {
    send_json_response(false, "Koneksi ke database gagal. Pastikan detail di db_connect.php sudah benar.");
}

session_start();

if (!isset($_POST['action'])) {
    send_json_response(false, 'Aksi tidak valid atau tidak ditemukan.');
}

$action = $_POST['action'];

if ($action == 'signup') {
    if (!isset($_POST['fullName'], $_POST['email'], $_POST['password'])) { send_json_response(false, 'Data pendaftaran tidak lengkap.'); }
    $fullName = trim($_POST['fullName']); $email = trim($_POST['email']); $password = trim($_POST['password']);
    if (empty($fullName) || empty($email) || empty($password)) { send_json_response(false, 'Harap isi semua field.'); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { send_json_response(false, 'Format email tidak valid.'); }
    if (strlen($password) < 6) { send_json_response(false, 'Kata sandi minimal 6 karakter.'); }
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?"); $stmt_check->bind_param("s", $email); $stmt_check->execute(); $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) { $stmt_check->close(); send_json_response(false, 'Email ini sudah terdaftar.'); }
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); $stmt_insert = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)"); $stmt_insert->bind_param("sss", $fullName, $email, $hashed_password);
    if ($stmt_insert->execute()) { send_json_response(true, 'Pendaftaran berhasil! Silakan masuk.'); } else { send_json_response(false, 'Gagal membuat akun.'); }
    $stmt_insert->close();
}
elseif ($action == 'login') {
    if (!isset($_POST['email'], $_POST['password'])) { send_json_response(false, 'Email dan kata sandi wajib diisi.'); }
    $email = trim($_POST['email']); $password = trim($_POST['password']);
    if (empty($email) || empty($password)) { send_json_response(false, 'Harap isi email dan kata sandi.'); }
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?"); $stmt->bind_param("s", $email); $stmt->execute(); $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) { $_SESSION["loggedin"] = true; $_SESSION["id"] = $user['id']; $_SESSION["name"] = $user['full_name']; send_json_response(true, 'Login berhasil! Anda akan diarahkan...'); } else { send_json_response(false, 'Email atau kata sandi salah.'); }
    } else { send_json_response(false, 'Email atau kata sandi salah.'); }
    $stmt->close();
}
else {
    send_json_response(false, 'Aksi tidak dikenali.');
}
$conn->close();
?>