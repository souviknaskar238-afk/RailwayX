<?php
session_start();
require_once '../home/config.php'; 

$error = '';
$coach_data = null;
$trains = [];
$coach_types = [];

// Fetch Trains and Coach Types for Dropdowns
try {
    $train_query = "SELECT id, train_number, train_name FROM trains ORDER BY train_number ASC";
    $train_result = mysqli_query($conn, $train_query);
    if ($train_result) $trains = mysqli_fetch_all($train_result, MYSQLI_ASSOC);

    $type_query = "SELECT id, name FROM coach_types ORDER BY name ASC";
    $type_result = mysqli_query($conn, $type_query);
    if ($type_result) $coach_types = mysqli_fetch_all($type_result, MYSQLI_ASSOC);
} catch (Exception $e) {}

// Fetch Existing Coach Data
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $fetch_query = "SELECT * FROM coaches WHERE id = ?";
    $stmt = mysqli_prepare($conn, $fetch_query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $coach_data = mysqli_fetch_assoc($result);
    } else {
        header("Location: manage_coaches.php?error=notfound");
        exit();
    }
} else {
    header("Location: manage_coaches.php");
    exit();
}

// Handle Form Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $train_id = (int)$_POST['train_id'];
    $coach_type_id = (int)$_POST['coach_type_id'];
    $coach_number = strtoupper(trim($_POST['coach_number']));
    $total_seats = (int)$_POST['total_seats'];

    try {
        // Ensure no conflict on the same train with the same coach number (excluding itself)
        $check_query = "SELECT id FROM coaches WHERE train_id = ? AND coach_number = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "isi", $train_id, $coach_number, $id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Error: Coach '<strong>$coach_number</strong>' already exists on this train.";
        } else {
            $update_query = "UPDATE coaches SET train_id=?, coach_type_id=?, coach_number=?, total_seats=? WHERE id=?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, "iisii", $train_id, $coach_type_id, $coach_number, $total_seats, $id);

            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: manage_coaches.php?msg=updated");
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
    ["icon" => "💳", "name" => "Payments", "link" => "manage_payments.php"],
    ["icon" => "📊", "name" => "Reports", "link" => "reports.php"],
    ["icon" => "⚙️", "name" => "Settings", "link" => "settings.php"]
];
$current_page = 'manage_coaches.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Coach | RailWayX Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        select option { background-color: #0f172a; color: #f8fafc; }
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
                        <span class="text-lg w-6 text-center"><?= $menu['icon'] ?></span>
                        <?= $menu['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="p-4 border-t border-white/10 mb-2">
                <a href="admin_logout.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                    <span class="text-lg w-6 text-center">🚪</span> Logout
                </a>
            </div>
        </aside>

        <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 hidden lg:hidden top-20"></div>

        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 relative z-10">
                <div>
                    <h2 class="text-3xl font-black text-white tracking-tight">Edit Coach 💺</h2>
                    <p class="text-sm text-slate-400 mt-1">Update carriage assignment and capacity.</p>
                </div>
                <a href="manage_coaches.php" class="bg-slate-900 border border-white/10 hover:bg-white/5 text-white font-semibold text-sm py-3 px-6 rounded-xl transition-all">
                    ← Back to Coaches
                </a>
            </div>

            <div class="bg-white/[0.02] backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-10 shadow-2xl relative z-10 max-w-4xl">
                <?php if(!empty($error)): ?>
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm"><?= $error ?></div>
                <?php endif; ?>

                <form action="edit_coach.php?id=<?= $id ?>" method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($coach_data['id']) ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Assign to Train</label>
                            <select name="train_id" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="" disabled>Select Train...</option>
                                <?php foreach($trains as $train): ?>
                                    <option value="<?= htmlspecialchars($train['id']) ?>" <?= ($train['id'] == $coach_data['train_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($train['train_number']) ?> - <?= htmlspecialchars($train['train_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Coach Type / Class</label>
                            <select name="coach_type_id" required class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="" disabled>Select Coach Type...</option>
                                <?php foreach($coach_types as $type): ?>
                                    <option value="<?= htmlspecialchars($type['id']) ?>" <?= ($type['id'] == $coach_data['coach_type_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Coach Number</label>
                            <input type="text" name="coach_number" value="<?= htmlspecialchars($coach_data['coach_number']) ?>" required maxlength="10" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 uppercase">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Seats</label>
                            <input type="number" name="total_seats" value="<?= htmlspecialchars($coach_data['total_seats']) ?>" required min="1" class="w-full bg-slate-900 border border-white/10 text-white rounded-xl px-4 py-3.5 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/10">
                        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-3.5 px-8 rounded-xl shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all flex items-center gap-2">
                            <span>🔄</span> Update Coach Details
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
include 'admin_footer.php';
?>