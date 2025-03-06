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
			


			<div class=" w-full page-wrapper overflow-hidden">
				<!-- Main Content -->
				<main class="h-full overflow-y-auto  max-w-full  pt-4">
					<div class="container full-container py-5 flex flex-col gap-6">
						<div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-x-6 gap-x-0 lg:gap-y-0 gap-y-6">
							<div class="col-span-2">
								<div class="card">
									<div class="card-body">
										<div class="sm:flex block justify-between mb-5">
											<h4 class="text-gray-600 text-lg font-semibold sm:mb-0 mb-2">Grafik Transaksi</h4>

											<input type="date" class=" border-gray-400 text-gray-500 rounded-md text-sm border-[1px] focus:ring-0 sm:w-auto w-full">
										</div>
										<div id="chart1"></div>
									</div>
								</div>
							</div>

							<div class="flex flex-col gap-6">
								<div class="card">
									<div class="card-body">
										<h4 class="text-gray-600 text-lg font-semibold mb-5">Pemasukan</h4>
										<div class="flex gap-6 items-center justify-between">
											<div class="flex flex-col gap-4">
												<h3 class="text-[21px] font-semibold text-gray-600">Rp <?php echo number_format($income, 0, ',', '.'); ?></h3>
												<div class="flex items-center gap-1">
													<span class="flex items-center justify-center w-5 h-5 rounded-full bg-teal-400">
														<i class="ti ti-arrow-up-left text-teal-500"></i>
													</span>
													<p class="text-gray-600 text-sm font-normal ">+9%</p>
													<p class="text-gray-500 text-sm font-normal text-nowrap"></p>
												</div>
												<div class="flex">
													<div class="flex gap-2 items-center">
														<span class="w-2 h-2 rounded-full bg-blue-600"></span>
														<p class="text-gray-500 font-normal text-xs">2023</p>
													</div>
													<div class="flex gap-2 items-center">
														<span class="w-2 h-2 rounded-full bg-blue-500"></span>
														<p class="text-gray-500 font-normal text-xs">2023</p>
													</div>
												</div>
											</div>
											<div class="flex  items-center">
												<div id="breakup1"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="card">
									<div class="card-body">
										<div class="flex gap-6 items-center justify-between">
											<div class="flex flex-col gap-5">
												<h4 class="text-gray-600 text-lg font-semibold">Pengeluaran</h4>
												<div class="flex flex-col gap-[18px]">
													<h3 class="text-[21px] font-semibold text-gray-600">Rp <?php echo number_format($expense, 0, ',', '.'); ?></h3>
													<div class="flex items-center gap-1">
														<span class="flex items-center justify-center w-5 h-5 rounded-full bg-red-400">
															<i class="ti ti-arrow-down-right text-red-500"></i>
														</span>
														<p class="text-gray-600 text-sm font-normal ">+9%</p>
														<p class="text-gray-500 text-sm font-normal">last year</p>
													</div>
												</div>
											</div>

											<div class="w-11 h-11 flex justify-center items-center rounded-full bg-cyan-500 text-white self-start">
												<i class="ti ti-currency-dollar text-xl"></i>
											</div>

										</div>
									</div>
									<div id="earning"></div>
								</div>
							</div>


						</div>
						<div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-x-6 gap-x-0 lg:gap-y-0 gap-y-6">
							<div class="card">
								<div class="card-body">
									<h4 class="text-gray-600 text-lg font-semibold mb-6">History Transaksi</h4>
									<ul class="timeline-widget relative">
									<?php while ($row = $result_transactions->fetch_assoc()) { ?>
										<li class="timeline-item flex relative overflow-hidden min-h-[70px]">
											<div class="timeline-time text-gray-600 text-sm min-w-[90px] py-[6px] pr-4 text-end">
												9:30 am
											</div>
											<div class="timeline-badge-wrap flex flex-col items-center ">
												<div class="timeline-badge w-3 h-3 rounded-full shrink-0 bg-transparent border-2 border-blue-600 my-[10px]">
												</div>
												<div class="timeline-badge-border block h-full w-[1px] bg-gray-100"></div>
											</div>
											<div class="timeline-desc py-[6px] px-4">
												<p class="text-gray-600 text-lg font-normal"><?php echo $row['category']; ?></p>
												<p class="text-gray-600 text-xs font-normal"><?php echo $row['date']; ?></p>
											</div>
										</li>
										
										
										<?php } ?>
									</ul>
								</div>
							</div>

							<?php
							$result_transactions = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
							?>

							<div class="col-span-2">
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

						<footer>
							<p class="text-base text-gray-500 font-normal p-3 text-center">
								Made With 🔥 Wirr
							</p>
						</footer>
					</div>


				</main>
				<!-- Main Content End -->

			</div>
		</div>
		<!--end of project-->
	</main>



	<script src="./assets/libs/jquery/dist/jquery.min.js"></script>
	<script src="./assets/libs/simplebar/dist/simplebar.min.js"></script>
	<script src="./assets/libs/iconify-icon/dist/iconify-icon.min.js"></script>
	<script src="./assets/libs/@preline/dropdown/index.js"></script>
	<script src="./assets/libs/@preline/overlay/index.js"></script>
	<script src="./assets/js/sidebarmenu.js"></script>



	<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


	<script>
		var options = {
          series: [{
          name: 'series1',
          data: [<?php echo $income; ?>]
        }, {
          name: 'series2',
          data: [<?php echo $expense; ?>]
        }],
          chart: {
          height: 350,
          type: 'area'
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        xaxis: {
          type: 'datetime',
          categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        };

        var chart = new ApexCharts(document.querySelector("#chart1"), options);
        chart.render();



		var options = {
          series: [<?php echo $income; ?>,<?php echo $expense; ?>],
          chart: {
          width: 380,
          type: 'pie',
        },
        labels: ['Pemasukan','Pengeluaran'],
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
        };

        var chart = new ApexCharts(document.querySelector("#breakup1"), options);
        chart.render();
      
	</script>

	<script src="./assets/js/dashboard.js"></script>
</body>

</html>