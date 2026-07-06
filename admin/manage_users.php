<?php
session_start();
// Make sure this path correctly points to your db_config.php file
require_once '../home/config.php'; 

// Fetch all users using procedural MySQLi
$users = [];
$query = "SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    die("Database query failed: " . mysqli_error($conn));
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
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | RailWayX Admin</title>
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
                    <h2 class="text-3xl font-black text-white tracking-tight">User Management</h2>
                    <p class="text-sm text-slate-400 mt-1">View and manage passenger accounts and roles.</p>
                </div>
                <button class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-sm py-3 px-6 rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2">
                    <span>+</span> Add New User
                </button>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl relative z-10">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <h3 class="text-lg font-bold text-white">System Users</h3>
                    <div class="relative w-full sm:w-72">
                        <input type="text" placeholder="Search by name or email..." class="w-full bg-slate-900 border border-white/10 text-sm text-slate-200 rounded-xl pl-11 pr-4 py-2.5 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                        <span class="absolute left-4 top-2.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">ID</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Passenger</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Contact Info</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Role</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest">Joined</th>
                                <th class="pb-4 px-4 text-xs font-semibold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors group">
                                        <td class="py-4 px-4 text-slate-500 font-mono text-xs">#<?= htmlspecialchars($user['id']) ?></td>
                                        <td class="py-4 px-4 font-bold text-white"><?= htmlspecialchars($user['name']) ?></td>
                                        <td class="py-4 px-4">
                                            <div class="flex flex-col gap-1 text-xs">
                                                <span class="text-slate-300">✉️ <?= htmlspecialchars($user['email']) ?></span>
                                                <span class="text-slate-400">📱 <?= htmlspecialchars($user['phone']) ?></span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-cyan-400 border border-blue-500/30">
                                                    Admin
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/5 text-slate-300 border border-white/10">
                                                    User
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4 text-slate-400 text-xs">
                                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                        </td>
             <td class="py-4 px-4 text-right">
    <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <a href="edit_user.php?id=<?= $user['id'] ?>" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-blue-600/20 text-slate-400 hover:text-cyan-400 rounded-lg transition-colors" title="Edit">
            ✏️
        </a>
        
        <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 rounded-lg transition-colors" title="Delete">
            🗑️
        </a>
    </div>
</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">No users found in the system.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                    <p class="text-xs text-slate-500">Showing <?= count($users) ?> entries</p>
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