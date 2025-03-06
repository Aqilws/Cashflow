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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <h3>Cashflow</h3>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="#" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item"><a href="table.php" class="nav-link text-white">Table Transactions</a></li>
            <li class="nav-item"><a href="?logout=true" class="nav-link text-danger">Logout</a></li>
        </ul>
    </div>
    
    <div class="content">
        <h2 class="text-center">Dashboard Cashflow</h2>
        
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
        
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card p-3">
                    <h5>Total Balance</h5>
                    <h3>Rp <?php echo number_format($balance, 0, ',', '.'); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-success">
                    <h5>Income</h5>
                    <h3>Rp <?php echo number_format($income, 0, ',', '.'); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 bg-danger">
                    <h5>Expense</h5>
                    <h3>Rp <?php echo number_format($expense, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
        
        <div class="grafik-container" style="display: flex; justify-content: center; align-items: center;">
        <div class="mt-4" style="width:500px;">
            <h4>Cashflow Chart</h4>
            <canvas id="financeChart"></canvas>
        </div>
        </div>
        


        <div class="mt-4">
            <h4>Riwayat Transaksi</h4>
            <table class="table table-dark table-bordered bg-secondary-subtle border border-white">
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
    
    <script>
        const data = {
            labels: ['Income', 'Expense'],
            datasets: [{
                label: 'Financial Overview',
                data: [<?php echo $income; ?>, <?php echo $expense; ?>],
                backgroundColor: [
                    'rgba(0, 255, 0, 0.6)',
                    'rgba(255, 0, 0, 0.6)'
                ]
            }]
        };

        var ctx = document.getElementById('financeChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true
            }
        });
    </script>

    <?php 
    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
    $conn->close(); 
    ?>
</body>
</html>
