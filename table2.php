<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />


    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <!-- Core Css -->
    <link rel="stylesheet" href="./assets/css/theme.css" />
    <title>CashFlow</title>
</head>

<body class=" bg-white">
    <main>
        <!--start the project-->
        <div id="main-wrapper" class=" flex">

            <?php include "include/sidebar.php" ?>
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
            <div class="w-full page-wrapper overflow-hidden">
                <!-- Main Content -->
                <div class="h-full overflow-y-auto  max-w-full  pt-4">
                    <div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-x-6 gap-x-0 lg:gap-y-0 gap-y-6">
                        <div class="col-span-" style="grid-column: span 4 / span 4;">
                        <form method="GET" class="mb-4" style="padding-left: 25px; padding-right: 190px;">
                            <div class="flex flex-row justify-between items-center xl:flex-row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Transaction Type</label>
                                        <select name="type" class="form-control">
                                            <option value="">All</option>
                                            <option value="income" <?php echo (isset($_GET['type']) && $_GET['type'] == 'income') ? 'selected' : ''; ?>>Income</option>
                                            <option value="expense" <?php echo (isset($_GET['type']) && $_GET['type'] == 'expense') ? 'selected' : ''; ?>>Expense</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="table.php" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                            <div class="card h-full">
                                <div class="card-body">
                                    <h4 class="text-gray-600 text-lg font-semibold mb-6">Transaksi Terbaru</h4>
                                    <div class="relative overflow-x-auto">
                                        <!-- table -->
                                        <table class="text-left w-full whitespace-nowrap text-sm">
                                            <thead class="text-gray-700">
                                                <tr class="font-semibold text-gray-600">
                                                    <th scope="col" class="p-4">Id</th>
                                                    <th scope="col" class="p-4">Judul</th>
                                                    <th scope="col" class="p-4">Tanggal</th>
                                                    <th scope="col" class="p-4">Kategori</th>
                                                    <th scope="col" class="p-4">Total</th>
                                                    <th scope="col" class="p-4">Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result_transactions->fetch_assoc()) { ?>
                                                    <tr>
                                                        <td class="p-4 font-semibold text-gray-600 ">1</td>
                                                        <td class="p-4">
                                                            <?php echo $row['category']; ?>
                                                        </td>
                                                        <td class="p-4">
                                                            <?php echo $row['date']; ?>
                                                        </td>
                                                        <td class="p-4">
                                                            <span class="inline-flex items-center py-[3px] px-[10px] rounded-2xl font-semibold bg-blue-600 text-white"><?php echo ucfirst($row['type']); ?></span>
                                                        </td>
                                                        <td class="p-4 font-semibold">
                                                            Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?>
                                                        </td>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>


</body>

</html>