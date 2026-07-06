<?php
session_start();
require_once '../home/config.php'; 

$trains = [];
$db_error = null;

try {
    // Note: Alias used here so the table HTML remains clean
    $query = "SELECT id, train_number, train_name, source_station AS source, destination_station AS destination, status AS type FROM trains ORDER BY train_number ASC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $trains = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $db_error = mysqli_error($conn);
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
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
    <title>Manage Trains | RailWayX Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
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
                    <h2 class="text-3xl font-black text-white tracking-tight">Locomotive Fleet 🚄</h2>
                    <p class="text-sm text-slate-400 mt-1">Manage active trains, route assignments, and service status.</p>
                </div>
                <a href="add_train.php" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-sm py-3 px-6 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <span>+</span> Add Train
                </a>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl relative z-10">
                
                <?php if($db_error): ?>
                    <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-3">
                        <span class="text-xl">⚠️</span> 
                        <div><strong>Database Notice:</strong><br><?= htmlspecialchars($db_error) ?></div>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h3 class="text-lg font-bold text-white">Active Trains</h3>
                    <div class="relative w-full sm:w-72">
                        <input type="text" placeholder="Search by name or number..." class="w-full bg-slate-900 border border-white/10 text-sm text-slate-200 rounded-xl pl-11 pr-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                        <span class="absolute left-4 top-2.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Train No.</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Train Name</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Assigned Route</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if (count($trains) > 0): ?>
                                <?php foreach ($trains as $train): ?>
                                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors group">
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black tracking-wider bg-slate-900 text-cyan-400 border border-white/10">
                                                <?= htmlspecialchars($train['train_number']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-bold text-white">
                                            <?= htmlspecialchars($train['train_name']) ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="text-slate-300 font-semibold"><?= htmlspecialchars($train['source']) ?></span>
                                                <span class="text-slate-500 text-xs">➔</span>
                                                <span class="text-slate-300 font-semibold"><?= htmlspecialchars($train['destination']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php 
                                                $statusClass = $train['type'] == 'Active' ? 'text-green-400 border-green-500/30 bg-green-500/10' : 
                                                              ($train['type'] == 'Delayed' ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-red-400 border-red-500/30 bg-red-500/10');
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $statusClass ?>">
                                                <?= htmlspecialchars($train['type']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="edit_train.php?id=<?= $train['id'] ?>" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-blue-600/20 text-slate-400 hover:text-cyan-400 rounded-lg transition-colors" title="Edit">✏️</a>
                                                <a href="delete_train.php?id=<?= $train['id'] ?>" onclick="return confirm('Are you sure you want to retire this train?');" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 rounded-lg transition-colors" title="Delete">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-500">
                                        <div class="text-3xl mb-2">🚄</div>
                                        <p>No active trains found in the database.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                    <p class="text-xs text-slate-500">Showing <?= count($trains) ?> entries</p>
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
<?php include '../home/footer.php'; ?>