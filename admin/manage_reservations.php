<?php
session_start();
// Make sure this path correctly points to your config.php file
require_once '../home/config.php'; 

$reservations = [];
$db_error = null;

// Safe database query block using actual schema columns
try {
    $query = "
        SELECT 
            r.id, 
            r.pnr_number, 
            u.name AS passenger_name, 
            t.train_number, 
            t.train_name, 
            r.travel_date,  -- Corrected from journey_date
            r.status, 
            r.total_fare 
        FROM reservations r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN trains t ON r.train_id = t.id
        ORDER BY r.travel_date DESC, r.id DESC
    ";
    
    $result = mysqli_query($conn, $query);

    if ($result) {
        $reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $db_error = mysqli_error($conn);
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
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
$current_page = 'manage_reservations.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations | RailWayX Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; } 
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; } 
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 overflow-x-hidden font-sans h-screen flex flex-col">
    
    <header class="fixed top-0 left-0 w-full z-50 backdrop-blur-2xl bg-slate-950/70 border-b border-white/10 shrink-0">
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

    <div class="h-20 shrink-0"></div>

    <div class="flex max-w-[1800px] mx-auto relative flex-1 w-full overflow-hidden">

        <aside id="sidebar" class="fixed lg:static top-20 left-0 h-full w-72 bg-slate-950/90 backdrop-blur-xl border-r border-white/10 flex flex-col transition-transform duration-300 z-40 transform -translate-x-full lg:translate-x-0 shrink-0">
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                <h2 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4 px-4">Menu</h2>
                <?php foreach ($menus as $menu): 
                    $isActive = ($current_page == $menu['link']);
                    $activeClass = $isActive 
                        ? 'bg-[#162032] border border-cyan-500/70 text-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.15)]' 
                        : 'text-slate-400 hover:bg-white/5 hover:text-white border border-transparent';
                ?>
                    <a href="<?= $menu['link'] ?>" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-300 <?= $activeClass ?>">
                        <span class="text-lg w-6 text-center"><?= $menu['icon'] ?></span><?= $menu['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="p-4 border-t border-white/10 mb-2">
                <a href="admin_logout.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors border border-transparent">
                    <span class="text-lg w-6 text-center">🚪</span> Logout
                </a>
            </div>
        </aside>

        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden top-20"></div>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 relative z-10">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">Ticket Reservations 🎫</h2>
                    <p class="text-sm text-slate-400 mt-1">Monitor passenger bookings, PNR statuses, and revenue streams.</p>
                </div>
                <button class="bg-slate-900 border border-slate-800 hover:bg-slate-800 text-white font-medium text-sm py-2.5 px-5 rounded-xl transition-all flex items-center gap-2">
                    <span>📥</span> Export CSV
                </button>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl relative z-10">
                
                <?php if($db_error): ?>
                    <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-3">
                        <span class="text-xl">⚠️</span> 
                        <div>
                            <strong>Database Notice:</strong><br>
                            <?= htmlspecialchars($db_error) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h3 class="text-lg font-bold text-white">Recent Bookings</h3>
                    <div class="relative w-full sm:w-72">
                        <input type="text" placeholder="Search PNR or Passenger..." class="w-full bg-slate-900 border border-white/10 text-sm text-slate-200 rounded-xl pl-11 pr-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                        <span class="absolute left-4 top-2.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">PNR Number</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Passenger</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Train Details</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Travel Date</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Fare</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if (count($reservations) > 0): ?>
                                <?php foreach ($reservations as $res): ?>
                                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors group">
                                        <td class="py-4 px-4">
                                            <span class="font-mono text-cyan-400 font-bold tracking-widest text-sm">
                                                <?= htmlspecialchars($res['pnr_number'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-bold text-white">
                                            <?= htmlspecialchars($res['passenger_name'] ?? 'Guest User') ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-slate-300"><?= htmlspecialchars($res['train_name'] ?? 'Unknown Train') ?></span>
                                                <span class="text-xs text-slate-500">Train #<?= htmlspecialchars($res['train_number'] ?? '---') ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-slate-400 text-sm">
                                            <?= !empty($res['travel_date']) ? date('d M, Y', strtotime($res['travel_date'])) : '---' ?>
                                        </td>
                                        <td class="py-4 px-4 font-medium text-emerald-400">
                                            ₹<?= number_format((float)($res['total_fare'] ?? 0), 2) ?>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <?php 
                                                $status = $res['status'] ?? 'Unknown';
                                                if (strcasecmp($status, 'Confirmed') == 0) {
                                                    $statusClass = 'text-green-400 border-green-500/30 bg-green-500/10';
                                                } elseif (strcasecmp($status, 'Waiting') == 0) { // Corrected to check for 'Waiting'
                                                    $statusClass = 'text-amber-400 border-amber-500/30 bg-amber-500/10';
                                                } else {
                                                    $statusClass = 'text-red-400 border-red-500/30 bg-red-500/10';
                                                }
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $statusClass ?>">
                                                <?= htmlspecialchars($status) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-16 text-center text-slate-500">
                                        <div class="text-4xl mb-3">🎫</div>
                                        <p class="text-base font-semibold">No reservations found.</p>
                                        <p class="text-xs mt-1">Passenger bookings will appear here once the frontend is active.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                    <p class="text-xs text-slate-500">Showing <?= count($reservations) ?> entries</p>
                    <div class="flex gap-2">
                        <button class="px-4 py-2 text-xs bg-slate-900 border border-white/10 text-slate-500 rounded-xl cursor-not-allowed">Prev</button>
                        <button class="px-4 py-2 text-xs bg-gradient-to-br from-cyan-500 to-blue-600 text-white font-bold rounded-xl shadow-lg">1</button>
                        <button class="px-4 py-2 text-xs bg-slate-900 border border-white/10 text-slate-300 rounded-xl hover:bg-white/5 transition">Next</button>
                    </div>
                </div>

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
<?php
include 'admin_footer.php';
?>