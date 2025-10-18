<?php
require_once __DIR__ . '/config.php'; // Sesuaikan path jika perlu

// Set header ke JSON
header('Content-Type: application/json');

// Pastikan ada parameter 'q' (query search)
if (!isset($_GET['q'])) {
    echo json_encode([]); // Kirim array kosong jika tidak ada query
    exit;
}

$searchTerm = trim($_GET['q']);

// Jangan cari jika term terlalu pendek atau kosong
if (strlen($searchTerm) < 1) {
    echo json_encode([]);
    exit;
}

// Siapkan query pencarian (cari di judul)
// Gunakan LIKE dengan wildcard %
// Ambil ID dan Judul saja
$sql = "SELECT id, judul FROM notulen WHERE judul LIKE ? ORDER BY tanggal DESC LIMIT 10"; // Batasi hasil

$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Handle error prepare (sebaiknya log error, bukan tampilkan ke user)
    error_log("Search suggestion prepare failed: " . $conn->error);
    echo json_encode(['error' => 'Query preparation failed']); // Kirim pesan error (opsional)
    exit;
}

// Tambahkan wildcard % ke search term untuk LIKE
$likeTerm = '%' . $searchTerm . '%';
$stmt->bind_param('s', $likeTerm);

$results = []; // Array untuk menampung hasil

if ($stmt->execute()) {
    $result_set = $stmt->get_result();
    while ($row = $result_set->fetch_assoc()) {
        // Tambahkan hasil ke array
        $results[] = [
            'id' => $row['id'],
            'judul' => htmlspecialchars($row['judul']) // Hindari XSS
        ];
    }
    $result_set->free(); // Bebaskan memori
} else {
    // Handle error execute (sebaiknya log error)
     error_log("Search suggestion execute failed: " . $stmt->error);
     echo json_encode(['error' => 'Query execution failed']); // Kirim pesan error (opsional)
     exit;
}

$stmt->close(); // Tutup statement

// Output hasil sebagai JSON
echo json_encode($results);
?>