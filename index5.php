<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cashflow</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <?php
    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "cashflow_db");
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }



    $result_chart = $conn->query("SELECT 
DATE_FORMAT(date, '%Y-%m') AS month, 
SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
FROM transactions 
GROUP BY month 
ORDER BY month ASC");

    $months = [];
    $incomeData = [];
    $expenseData = [];

    while ($row = $result_chart->fetch_assoc()) {
        $months[] = $row['month'];
        $incomeData[] = $row['total_income'];
        $expenseData[] = $row['total_expense'];
    }

    // Menangani input transaksi
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
        $type = $_POST['type'];
        $category = $_POST['category'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];

        $stmt = $conn->prepare("INSERT INTO transactions (type, category, amount, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $type, $category, $amount, $date);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Menangani penghapusan transaksi
    if (isset($_GET['delete_id'])) {
        $delete_id = $_GET['delete_id'];
        $conn->query("DELETE FROM transactions WHERE id=$delete_id");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Mengambil total pemasukan
    $result_income = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type='income'");
    $income = $result_income->fetch_assoc()["total"] ?? 0;

    // Mengambil total pengeluaran
    $result_expense = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type='expense'");
    $expense = $result_expense->fetch_assoc()["total"] ?? 0;

    // Menghitung saldo
    $balance = $income - $expense;

    // Mengambil data transaksi
    $result_transactions = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
    ?>

    <div class="container mt-4">
        <h2 class="text-center">Dashboard Cashflow</h2>
        <a href="?logout=true" class="btn btn-danger float-end">Logout</a>

        <div class="row text-center mt-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Pemasukan</h5>
                        <h3>Rp <?php echo number_format($income, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5>Pengeluaran</h5>
                        <h3>Rp <?php echo number_format($expense, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Saldo</h5>
                        <h3>Rp <?php echo number_format($balance, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="grafik-container" style="width: 100%; display: flex; justify-content: center; ">
            <div class="mt-4" style="width: 500px;">
                <h4>Grafik Keuangan</h4>
                <canvas id="financeChart"></canvas>
            </div>
        </div>


        <div class="mt-4">
            <h4>Tambah Transaksi</h4>
            <form method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <select name="type" class="form-control" required>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="category" class="form-control" placeholder="Kategori" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="amount" class="form-control" placeholder="Jumlah" required>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" name="add_transaction" class="btn btn-primary">Tambah</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-4">
            <h4>Riwayat Transaksi</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_transactions->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['category']; ?></td>
                            <td><?php echo ucfirst($row['type']); ?></td>
                            <td>Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></td>
                            <td>
                                <a href="edit-transaction.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
    $conn->close();
    ?>


    <script>
        const data = {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                label: 'Keuangan',
                data: [<?php echo $income; ?>, <?php echo $expense; ?>],
                backgroundColor: [
                    'rgb(2, 255, 2)',
                    'rgb(255, 0, 0)'
                ]
            }]
        };

        var ctx = document.getElementById('financeChart').getContext('2d');
        new Chart(ctx, {
            type: 'polarArea',
            data: data,
            options: {
                responsive: true
            }
        });
    </script>

</body>

</html>