<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... rest of the code
session_start();
require_once '../home/config.php';

// 1. Check if ID exists in URL
if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}
$id = $_GET['id'];

// 2. Handle Form Submission (UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    $update_query = "UPDATE users SET name=?, email=?, phone=?, role=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $phone, $role, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: manage_users.php?msg=updated");
        exit();
    } else {
        $error = "Error updating record: " . mysqli_error($conn);
    }
}

// 3. Fetch Current User Data to fill the form (SELECT)
$fetch_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $fetch_query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    die("User not found!");
}

// Sidebar Navigation
$menus = [
    ["icon" => "🏠", "name" => "Dashboard", "link" => "admin_dashboard.php"],
    ["icon" => "👤", "name" => "Users", "link" => "manage_users.php"],
    ["icon" => "🚆", "name" => "Trains", "link" => "manage_trains.php"],
    ["icon" => "🚉", "name" => "Stations", "link" => "manage_stations.php"],
    ["icon" => "💺", "name" => "Coaches", "link" => "manage_coaches.php"],
    ["icon" => "🎫", "name" => "Reservations", "link" => "manage_reservations.php"],
    ["icon" => "💳", "name" => "Payments", "link" => "manage_payments.php"]
];
$current_page = 'manage_users.php'; // Keep Users menu active while editing
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | RailWayX Admin</title>
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
                    <button onclick="toggleSidebar()" class="lg:hidden w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white text-2xl">☰</button>
                    <a href="../home/index.php">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white text-3xl shadow-2xl">🚆</div>
                            <div class="hidden sm:block">
                                <h1 class="text-2xl font-black text-white tracking-wide">RailWayX</h1>
                                <p class="text-[10px] text-slate-400 tracking-[4px] uppercase">Admin Dashboard</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-4 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">A</div>
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
                    <h2 class="text-3xl font-black text-white tracking-tight">Edit Passenger</h2>
                    <p class="text-sm text-slate-400 mt-1">Update profile details and system roles.</p>
                </div>
                <a href="manage_users.php" class="bg-slate-900 border border-white/10 hover:bg-white/5 text-white font-semibold text-sm py-3 px-6 rounded-xl transition-all">
                    ← Back to Users
                </a>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-10 shadow-2xl relative z-10 max-w-3xl">
                
                <?php if(isset($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form action="edit_user.php?id=<?= $id ?>" method="POST" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user_data['name']) ?>" required 
                                   class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user_data['phone']) ?>" required 
                                   class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required 
                               class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">System Role</label>
                        <select name="role" required class="w-full bg-slate-900/50 border border-white/10 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all appearance-none cursor-pointer">
                            <option value="user" <?= ($user_data['role'] == 'user') ? 'selected' : '' ?>>Passenger (User)</option>
                            <option value="admin" <?= ($user_data['role'] == 'admin') ? 'selected' : '' ?>>System Administrator</option>
                        </select>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transition-all text-sm tracking-wide">
                            Save Changes
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
<?php
include '../home/footer.php';
?>