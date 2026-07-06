<?php
session_start();
// Make sure this path correctly points to your config.php file
require_once '../home/config.php'; 

$error = '';

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = strtoupper(trim($_POST['code']));
    $name = trim($_POST['name']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $address = trim($_POST['address']);

    // Check if station code already exists (to prevent duplicates)
    $check_query = "SELECT id FROM stations WHERE code = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $code);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $error = "Error: The Station Code '<strong>$code</strong>' already exists in the database.";
    } else {
        // Insert new station
        $query = "INSERT INTO stations (name, code, city, state, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $name, $code, $city, $state, $address);

        if (mysqli_stmt_execute($stmt)) {
            // Redirect back to manage stations on success
            header("Location: manage_stations.php?msg=added");
            exit();
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}

// Structured Array Sidebar Navigation
$menus = [
    ["icon" => "🏠", "name" => "Dashboard", "link" => "dashboard.php"],
    ["icon" => "👤", "name" => "Users", "link" => "manage_users.php"],
    ["icon" => "🚆", "name" => "Trains", "link" => "manage_trains.php"],
    ["icon" => "🚉", "name" => "Stations", "link" => "manage_stations.php"],
    ["icon" => "💺", "name" => "Coaches", "link" => "manage_coaches.php"],
    ["icon" => "🎫", "name" => "Reservations", "link" => "manage_reservations.php"],
    ["icon" => "💳", "name" => "Payments", "link" => "manage_payments.php"]
  
];
$current_page = 'manage_stations.php'; // Keep the stations menu highlighted
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Station | RailWayX Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar to match the dark theme */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; } /* slate-950 */
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; } /* slate-800 */
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 overflow-x-hidden font-sans">
    
    <header class="fixed top-0 left-0 w-full z-50 backdrop-blur-2xl bg-slate-950/70 border-b border-white/10">
        <div class="max-w-[1800px] mx-auto px-4 md:px-8">
            <div class="h-20 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white text-2xl hover:bg-white/10 transition">
                        ☰
                    </button>
                    <a href="../home/index.php">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white text-3xl shadow-2xl shadow-blue-500/20">
                                🚆
                            </div>
                            <div class="hidden sm:block">
                                <h1 class="text-2xl font-black text-white tracking-wide">RailWayX</h1>
                                <p class="text-[10px] text-slate-400 tracking-[4px] uppercase">Admin Dashboard</p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-xl text-slate-300 hover:bg-white/10 transition">
                        🔔
                        <span class="absolute top-3 right-3 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                    </button>
                    <div class="hidden sm:flex items-center gap-4 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-inner">
                            A
                        </div>
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
                    $activeClass = $isActive 
                        ? 'bg-gradient-to-r from-blue-600/20 to-cyan-400/10 text-cyan-400 border-r-2 border-cyan-400' 
                        : 'text-slate-400 hover:bg-white/5 hover:text-white';
                ?>
                    <a href="<?= $menu['link'] ?>" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium transition-all <?= $activeClass ?>">
                        <span class="text-lg"><?= $menu['icon'] ?></span>
                        <?= $menu['name'] ?>
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
                    <h2 class="text-3xl font-black text-white tracking-tight">Add New Station</h2>
                    <p class="text-sm text-slate-400 mt-1">Register a new transit hub into the central database.</p>
                </div>
                <a href="manage_stations.php" class="bg-slate-900 border border-white/10 hover:bg-white/5 text-white font-semibold text-sm py-3 px-6 rounded-xl transition-all">
                    ← Back to Stations
                </a>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-10 shadow-2xl relative z-10 max-w-4xl">
                
                <?php if(!empty($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="add_station.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Station Code (Unique)</label>
                            <input type="text" name="code" placeholder="e.g. HWH, NDLS" required maxlength="10"
                                   class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all uppercase placeholder-slate-500">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Station Name</label>
                            <input type="text" name="name" placeholder="e.g. Howrah Junction" required 
                                   class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">City</label>
                            <input type="text" name="city" placeholder="e.g. Kolkata" required 
                                   class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-500">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">State / Region</label>
                            <input type="text" name="state" placeholder="e.g. West Bengal" required 
                                   class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-500">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Detailed Address (Optional)</label>
                        <textarea name="address" rows="3" placeholder="Enter full address details..."
                                  class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-500"></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-blue-500/20 transition-all tracking-wide flex items-center justify-center gap-2">
                            <span>💾</span> Save Station Node
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
            
            // Toggle sidebar translation
            sidebar.classList.toggle('-translate-x-full');
            
            // Toggle overlay visibility
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
<?php
include '../home/footer.php';
?>