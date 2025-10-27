<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$koneksi = new mysqli("localhost", "root", "", "ms_studio");

if ($koneksi->connect_error) {
    die(json_encode(["status" => "error", "message" => $koneksi->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ===================== GET =====================
    case 'GET':
        if (isset($_GET['id_user'])) {
            $id = intval($_GET['id_user']);
            $result = $koneksi->query("SELECT id_user, username, nama_lengkap, email, whatsapp, role FROM user WHERE id_user = $id");
            $data = $result->fetch_assoc();
            echo json_encode($data ?: ["message" => "Data tidak ditemukan"]);
        } else {
            $result = $koneksi->query("SELECT id_user, username, nama_lengkap, email, whatsapp, role FROM user ORDER BY id_user DESC");
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode($data);
        }
        break;

    // ===================== POST =====================
    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['username'], $input['nama_lengkap'], $input['email'], $input['whatsapp'], $input['password'])) {
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
            exit;
        }

        $username = $koneksi->real_escape_string($input['username']);
        $nama = $koneksi->real_escape_string($input['nama_lengkap']);
        $email = $koneksi->real_escape_string($input['email']);
        $wa = $koneksi->real_escape_string($input['whatsapp']);
        $password = password_hash($input['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO user (username, nama_lengkap, email, role, password, whatsapp, reset_token) 
                VALUES ('$username', '$nama', '$email', 'user', '$password', '$wa', '')";

        if ($koneksi->query($sql)) {
            echo json_encode(["status" => "success", "message" => "User baru berhasil ditambahkan"]);
        } else {
            echo json_encode(["status" => "error", "message" => $koneksi->error]);
        }
        break;

    // ===================== PUT =====================
    case 'PUT':
        if (!isset($_GET['id_user'])) {
            echo json_encode(["status" => "error", "message" => "Parameter id_user diperlukan"]);
            exit;
        }

        $id = intval($_GET['id_user']);
        $input = json_decode(file_get_contents("php://input"), true);
        $fields = [];

        if (isset($input['username'])) $fields[] = "username='" . $koneksi->real_escape_string($input['username']) . "'";
        if (isset($input['nama_lengkap'])) $fields[] = "nama_lengkap='" . $koneksi->real_escape_string($input['nama_lengkap']) . "'";
        if (isset($input['email'])) $fields[] = "email='" . $koneksi->real_escape_string($input['email']) . "'";
        if (isset($input['whatsapp'])) $fields[] = "whatsapp='" . $koneksi->real_escape_string($input['whatsapp']) . "'";
        if (isset($input['password'])) $fields[] = "password='" . password_hash($input['password'], PASSWORD_BCRYPT) . "'";

        if (empty($fields)) {
            echo json_encode(["status" => "error", "message" => "Tidak ada data untuk diupdate"]);
            exit;
        }

        $sql = "UPDATE user SET " . implode(",", $fields) . " WHERE id_user=$id";
        if ($koneksi->query($sql)) {
            echo json_encode(["status" => "success", "message" => "Data user berhasil diperbarui"]);
        } else {
            echo json_encode(["status" => "error", "message" => $koneksi->error]);
        }
        break;

    // ===================== DELETE =====================
    case 'DELETE':
        if (!isset($_GET['id_user'])) {
            echo json_encode(["status" => "error", "message" => "Parameter id_user diperlukan"]);
            exit;
        }

        $id = intval($_GET['id_user']);
        $sql = "DELETE FROM user WHERE id_user = $id";
        if ($koneksi->query($sql)) {
            echo json_encode(["status" => "success", "message" => "User berhasil dihapus"]);
        } else {
            echo json_encode(["status" => "error", "message" => $koneksi->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Metode tidak diizinkan"]);
        break;
}
?>
