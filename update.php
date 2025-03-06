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
                    <h1 class="mb-6 text-2xl font-semibold">Upadate</h1>
                    <!-- form -->
                    <form method="POST">
                        <!-- Kategori -->
                        <div class="mb-4">
                            <label for="forUsername"
                            class="block text-sm font-semibold mb-2 text-gray-600">Jenis Transaksi</label>
                            <select name="type" class="form-control w-full" style="height: 50px;" required>
                                <option value="income" <?php echo ($transaction['type'] == 'income') ? 'selected' : ''; ?>>Pemasukan</option>
                                <option value="expense" <?php echo ($transaction['type'] == 'expense') ? 'selected' : ''; ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <!-- Nama Transaksi -->
                        <div class="mb-4">
                            
                            <label for="forTransaksi"
                                class="block text-sm font-semibold mb-2 text-gray-600">Nama Transaksi</label>
                            <input type="text" id="forTransaksi" name="category"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " value="<?php echo $transaction['category']; ?>" aria-describedby="hs-input-helper-text">
                        </div>

                        
                        <!-- amount -->
                        <div class="mb-6">
                            <label for="foramount"
                                class="block text-sm font-semibold mb-2 text-gray-600">Total Pemasukan / Pengeluaran</label>
                            <input type="text" id="foramount" name="amount"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " value="<?php echo $transaction['amount']; ?>" aria-describedby="hs-input-helper-text">
                        </div>

                        <!-- Date -->
                        <div class="mb-6">
                            <label for="fordate"
                                class="block text-sm font-semibold mb-2 text-gray-600">Total Pemasukan / Pengeluaran</label>
                            <input type="date" id="fordate"  type="submit" name="update_transaction"
                                class="py-3 px-4 block w-full border-gray-200 rounded-md text-sm focus:border-blue-600 focus:ring-0 " value="<?php echo $transaction['date']; ?>" aria-describedby="hs-input-helper-text">
                        </div>
                        
                        <!-- button -->
                        <div class="grid my-6">
                            <button type="submit" name="update_transaction"  class="btn py-[10px] text-base text-white font-medium hover:bg-blue-700">Update</button>
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