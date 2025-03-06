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
        header("Location: table.php"); // Kembali ke dashboard
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


<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <!-- Core Css -->
    <link rel="stylesheet" href="./assets/css/theme.css" />
    <title>Modernize TailwindCSS HTML Admin Template</title>
</head>

<body class="DEFAULT_THEME bg-white">
    <main>
        <!-- Main Content -->
        <div class="flex flex-col w-full  overflow-hidden relative min-h-screen radial-gradient items-center justify-center g-0 px-4">

            <div class="justify-center items-center w-full card lg:flex max-w-md ">
                <div class=" w-full card-body">
                    <h1 class="mb-6 text-2xl font-semibold">Tambah Barang</h1>
                    <!-- form -->
                    <form method="POST">
                        <!-- Kategori -->
                        <div class="mb-4">
                            <label for="forUsername"
                            class="block text-sm font-semibold mb-2 text-gray-600">Jenis Transaksi</label>
                            <select name="type" class="form-control w-full" style="height: 50px;" required>
                                <option value="income" >Pemasukan</option>
                                <option value="expense" >Pengeluaran</option>
                            </select>
                        </div>
                        <!-- Nama Transaksi -->
                        <div class="mb-4">
                            
                            <label for="forTransaksi"
                                class="block text-sm font-semibold mb-2 text-gray-600">Nama Transaksi</label>
                            <input type="text" id="forTransaksi" name="category"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                        </div>

                        
                        <!-- amount -->
                        <div class="mb-6">
                            <label for="foramount"
                                class="block text-sm font-semibold mb-2 text-gray-600">Total Pemasukan / Pengeluaran</label>
                            <input type="text" id="foramount" name="amount"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " aria-describedby="hs-input-helper-text">
                        </div>

                        <!-- Date -->
                        <div class="mb-6">
                            <label for="fordate"
                                class="block text-sm font-semibold mb-2 text-gray-600">Total Pemasukan / Pengeluaran</label>
                            <input type="date" id="fordate"  type="submit" name="date"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 "  aria-describedby="hs-input-helper-text">
                        </div>
                        
                        <!-- button -->
                        <div class="grid my-6">
                            <button type="submit" name="add_transaction"  class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Tambah</button>
                        </div>

                       
                </div>
                </form>
            </div>
        </div>

        </div>
        <!--end of project-->
    </main>



    <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../assets/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="../assets/libs/iconify-icon/dist/iconify-icon.min.js"></script>
    <script src="../assets/libs/@preline/dropdown/index.js"></script>
    <script src="../assets/libs/@preline/overlay/index.js"></script>
    <script src="../assets/js/sidebarmenu.js"></script>



</body>


</html>

<?php $conn->close(); ?>