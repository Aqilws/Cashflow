<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "cashflow_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mengambil data transaksi berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM transactions WHERE id=$id");
    $transaction = $result->fetch_assoc();

    if (!$transaction) {
        die("Transaksi tidak ditemukan!");
    }
} else {
    die("ID tidak diberikan!");
}

// Menangani update transaksi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_transaction'])) {
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    
    $stmt = $conn->prepare("UPDATE transactions SET type=?, category=?, amount=?, date=? WHERE id=?");
    $stmt->bind_param("ssdsi", $type, $category, $amount, $date, $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: index.php"); // Kembali ke dashboard
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        label{
            margin-bottom: 5px;
            margin-top: 10px;
            font-weight: 500;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Edit Transaksi</h2>
        <form method="POST" class="mb-4">
            <div class=" d-flex flex-column align-items-center gap-3 justify-content-center" style="height: 80vh;">
                <div class="col-md-3 w-50">
                <label for="">Jenis Transaksi</label>
                    <select name="type" class="form-control" style="height: 50px;" required>
                        <option value="income" <?php echo ($transaction['type'] == 'income') ? 'selected' : ''; ?>>Pemasukan</option>
                        <option value="expense" <?php echo ($transaction['type'] == 'expense') ? 'selected' : ''; ?>>Pengeluaran</option>
                    </select>
                </div>
                <div class="col-md-3 w-50"> 
                    <label for="">Title Transaksi</label>
                    <input type="text" name="category" class="form-control" style="height: 50px;" value="<?php echo $transaction['category']; ?>" required>
                </div>
                <div class="col-md-3 w-50">
                    <label for="">Total Transaksi</label>
                    <input type="number" name="amount" class="form-control" style="height: 50px;" value="<?php echo $transaction['amount']; ?>" required>
                </div>
                <div class="col-md-2 w-50">
                    <label for="">Tanggal Transaksi</label>
                    <input type="date" name="date" class="form-control" style="height: 50px;" value="<?php echo $transaction['date']; ?>" required>
                </div>
                <div class="col-md-1 w-50 ">
                    <button type="submit" name="update_transaction" style="height: 50px;" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>
