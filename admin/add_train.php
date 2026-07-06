<?php
session_start();
require_once '../home/config.php'; 

$error = '';
$stations = [];

try {
    $station_query = "SELECT code, name FROM stations ORDER BY name ASC";
    $station_result = mysqli_query($conn, $station_query);
    if ($station_result) {
        $stations = mysqli_fetch_all($station_result, MYSQLI_ASSOC);
    }
} catch (Exception $e) {}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $train_number = trim($_POST['train_number']);
    $train_name = trim($_POST['train_name']);
    $source_station = trim($_POST['source_station']);
    $destination_station = trim($_POST['destination_station']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $total_seats = (int)$_POST['total_seats'];
    $available_seats = $total_seats; 
    
    $fare_ac = !empty($_POST['fare_ac']) ? $_POST['fare_ac'] : NULL;
    $fare_sleeper = !empty($_POST['fare_sleeper']) ? $_POST['fare_sleeper'] : NULL;
    $fare_general = !empty($_POST['fare_general']) ? $_POST['fare_general'] : NULL;
    $running_days = !empty($_POST['running_days']) ? trim($_POST['running_days']) : 'All';
    $status = !empty($_POST['status']) ? trim($_POST['status']) : 'Active';

    try {
        $check_query = "SELECT id FROM trains WHERE train_number = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "s", $train_number);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Error: Train Number '<strong>$train_number</strong>' already exists.";
        } else {
            $query = "INSERT INTO trains (train_name, train_number, source_station, destination_station, departure_time, arrival_time, total_seats, available_seats, fare_ac, fare_sleeper, fare_general, running_days, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssssssiiddsss", $train_name, $train_number, $source_station, $destination_station, $departure_time, $arrival_time, $total_seats, $available_seats, $fare_ac, $fare_sleeper, $fare_general, $running_days, $status);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: manage_trains.php?msg=added");
                exit();
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    } catch (Exception $e) {
        $error = "Database Exception: " . $e->getMessage();
    }
}

$menus = [
    ["icon" => "🏠", "name" => "Dashboard", "link" => "dashboard.php"],
    ["icon" => "👤", "name" => "Users", "link" => "manage_users.php"],
    ["icon" => "🚆", "name" => "Trains", "link" => "manage_trains.php"],
    ["icon" => "🚉", "name" => "Stations", "link" => "manage_stations.php"],
    ["icon" => "💺", "name" => "Coaches", "link" => "manage_coaches.php"],
    ["icon" => "🎫", "name" => "Reservations", "link" => "manage_reservations.php"],
    ["icon" => "💳", "name" => "Payments", "link" => "manage_payments.php"]
];
$current_page = 'manage_trains.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Train | RailWayX Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        select option { background-color: #0f172a; color: #f8fafc; }
        ::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 overflow-x-hidden font-sans">
    
    <header class="fixed top-0 left-0 w-full z-50 backdrop-blur-2xl bg-slate-950/70 border-b border-white/10">
        <div class="max-w-[1800px] mx-auto px-4 md:px-8">
            <div class="h-20 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white text-2xl hover:bg-white/10 transition">☰</button>
                    <a href="../home/index.php">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white text-3xl shadow-2xl shadow-blue-500/20">🚆</div>
                            <div class="hidden sm:block">
                                <h1 class="text-2xl font-black text-white tracking-wide">RailWayX</h1>
                                <p class="text-[10px] text-slate-400 tracking-[4px] uppercase">Admin Dashboard</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <button class="relative w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-xl text-slate-300 hover:bg-white/10 transition">
                        🔔<span class="absolute top-3 right-3 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                    </button>
                    <div class="hidden sm:flex items-center gap-4 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-inner">A</div>
                        <div>
                            <h3 class="text-white text-sm font-semibold">Admin</h3>
                            <p class="text-slate-400 text-xs">Railway Authority</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="h-20"></div>

    <div class="flex max-w-[1800px] mx-auto relative">

        <aside id="sidebar" class="fixed lg:sticky top-20 left-0 h-[calc(100vh-5rem)] w-72 bg-slate-950/90 backdrop-blur-xl border-r border-white/10 flex flex-col transition-transform duration-300 z-40 transform -translate-x-full lg:translate-x-0">
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 px-4">Menu</h2>
                <?php foreach ($menus as $menu): 
                    $isActive = ($current_page == $menu['link']);
                    $activeClass = $isActive ? 'bg-gradient-to-r from-blue-600/20 to-cyan-400/10 text-cyan-400 border-r-2 border-cyan-400' : 'text-slate-400 hover:bg-white/5 hover:text-white';
                ?>
                    <a href="<?= $menu['link'] ?>" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $activeClass ?>">
                        <span class="text-lg"><?= $menu['icon'] ?></span><?= $menu['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="p-4 border-t border-white/10 mb-2">
                <a href="admin_logout.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                    <span class="text-lg">🚪</span> Logout
                </a>
            </div>
        </aside>

        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden top-20"></div>

        <main class="flex-1 min-h-[calc(100vh-5rem)] p-4 md:p-8 relative">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 relative z-10">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight">Add New Train 🚄</h2>
                    <p class="text-sm text-slate-400 mt-1">Register a new locomotive with scheduling and seating details.</p>
                </div>
                <a href="manage_trains.php" class="bg-slate-900 border border-white/10 hover:bg-white/5 text-white font-semibold text-sm py-3 px-6 rounded-xl transition-all">
                    ← Back to Trains
                </a>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-10 shadow-2xl relative z-10 max-w-5xl">
                <?php if(!empty($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm"><?= $error ?></div>
                <?php endif; ?>

                <form action="add_train.php" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Train Number</label>
                            <input type="text" name="train_number" required maxlength="20" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Train Name</label>
                            <input type="text" name="train_name" required maxlength="100" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Source Station</label>
                            <select name="source_station" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="" disabled selected>Select Source...</option>
                                <?php foreach($stations as $station): ?>
                                    <option value="<?= htmlspecialchars($station['code']) ?>"><?= htmlspecialchars($station['code']) ?> - <?= htmlspecialchars($station['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Destination Station</label>
                            <select name="destination_station" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="" disabled selected>Select Destination...</option>
                                <?php foreach($stations as $station): ?>
                                    <option value="<?= htmlspecialchars($station['code']) ?>"><?= htmlspecialchars($station['code']) ?> - <?= htmlspecialchars($station['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Departure Time</label>
                            <input type="datetime-local" name="departure_time" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Arrival Time</label>
                            <input type="datetime-local" name="arrival_time" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Seats</label>
                            <input type="number" name="total_seats" required min="1" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">AC Fare (₹)</label>
                            <input type="number" step="0.01" name="fare_ac" placeholder="0.00" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sleeper Fare (₹)</label>
                            <input type="number" step="0.01" name="fare_sleeper" placeholder="0.00" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Gen Fare (₹)</label>
                            <input type="number" step="0.01" name="fare_general" placeholder="0.00" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Running Days</label>
                            <select name="running_days" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="All">All</option>
                                <option value="Weekdays">Weekdays</option>
                                <option value="Weekends">Weekends</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Status</label>
                            <select name="status" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="Active">Active</option>
                                <option value="Delayed">Delayed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/10">
                        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                            💾 Save Train Profile
                        </button>
                    </div>
                </form>

            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            if (overlay.classList.contains('hidden')) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
    </script>
</body>
</html>
<?php include '../home/footer.php'; ?>