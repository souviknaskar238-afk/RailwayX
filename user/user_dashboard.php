<?php
session_start();
// Make sure this path correctly points to your config.php file
require_once '../home/config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    // header("Location: ../home/login.php");
    // exit();
}

$user_id = $_SESSION['user_id'] ?? 1; // Fallback for testing

// 1. Fetch data for Dynamic Search Dropdowns
$stations_map = [];
$valid_routes = [];

try {
    // Fetch ALL stations for mapping names
    $station_query = "SELECT code, name FROM stations ORDER BY name ASC";
    $station_result = mysqli_query($conn, $station_query);
    if ($station_result) {
        while($row = mysqli_fetch_assoc($station_result)) {
            $stations_map[$row['code']] = $row['name'];
        }
    }

    // Fetch valid routes from active trains
    $route_query = "SELECT DISTINCT source_station, destination_station FROM trains WHERE status != 'Cancelled'";
    $route_result = mysqli_query($conn, $route_query);
    if ($route_result) {
        while($row = mysqli_fetch_assoc($route_result)) {
            $src = $row['source_station'];
            $dest = $row['destination_station'];
            if (!isset($valid_routes[$src])) {
                $valid_routes[$src] = [];
            }
            if (!in_array($dest, $valid_routes[$src])) {
                $valid_routes[$src][] = $dest;
            }
        }
    }
} catch (Exception $e) {}


// 2. Fetch Dynamic Dashboard Stats
$active_trips_count = 0;
$past_journeys_count = 0;
$upcoming_ticket = null;

try {
    $today = date('Y-m-d');
    
    // Active Trips (Future & Today)
    $q_active = "SELECT COUNT(*) as cnt FROM reservations WHERE user_id = ? AND status = 'Confirmed' AND travel_date >= ?";
    $stmt_active = mysqli_prepare($conn, $q_active);
    mysqli_stmt_bind_param($stmt_active, "is", $user_id, $today);
    mysqli_stmt_execute($stmt_active);
    $res_active = mysqli_stmt_get_result($stmt_active);
    if ($res_active) $active_trips_count = mysqli_fetch_assoc($res_active)['cnt'];
    
    // Past Journeys
    $q_past = "SELECT COUNT(*) as cnt FROM reservations WHERE user_id = ? AND travel_date < ?";
    $stmt_past = mysqli_prepare($conn, $q_past);
    mysqli_stmt_bind_param($stmt_past, "is", $user_id, $today);
    mysqli_stmt_execute($stmt_past);
    $res_past = mysqli_stmt_get_result($stmt_past);
    if ($res_past) $past_journeys_count = mysqli_fetch_assoc($res_past)['cnt'];

    // Next Upcoming Itinerary
    $q_next = "
        SELECT r.*, t.train_name, t.source_station, t.destination_station 
        FROM reservations r 
        JOIN trains t ON r.train_id = t.id 
        WHERE r.user_id = ? AND r.status = 'Confirmed' AND r.travel_date >= ? 
        ORDER BY r.travel_date ASC LIMIT 1
    ";
    $stmt_next = mysqli_prepare($conn, $q_next);
    mysqli_stmt_bind_param($stmt_next, "is", $user_id, $today);
    mysqli_stmt_execute($stmt_next);
    $res_next = mysqli_stmt_get_result($stmt_next);
    if ($res_next) $upcoming_ticket = mysqli_fetch_assoc($res_next);

} catch (Exception $e) {}


// Sidebar Menu Config
$current_page = 'user_dashboard.php';
include 'user_header.php';
?>

<div class="flex relative min-h-screen font-sans">

    <!-- FULL PAGE BACKGROUND -->
    <div class="fixed inset-0 -z-10 pointer-events-none">
        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1600&auto=format&fit=crop" class="w-full h-full object-cover opacity-30" alt="Train Journey">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-slate-900/90 to-emerald-950/80"></div>
    </div>

    <!-- MOBILE OVERLAY -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed lg:relative top-0 left-0 z-50 min-h-screen w-72 bg-slate-900/40 backdrop-blur-3xl border-r border-white/5 transition-transform duration-300 overflow-y-auto -translate-x-full lg:translate-x-0 shrink-0">
        
        <div class="p-8 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-white text-xl font-extrabold tracking-wide flex items-center gap-2">
                    <span class="text-emerald-400">❖</span> My Journey
                </h2>
                <p class="text-slate-400 text-xs mt-1 tracking-widest uppercase">Passenger Portal</p>
            </div>
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white transition">✖</button>
        </div>

        <div class="p-6 space-y-1">
            <?php
            $menus = [
                ["icon" => "🏠", "name" => "Dashboard", "link" => "user_dashboard.php"],
                ["icon" => "🔍", "name" => "Search Trains", "link" => "search_trains.php"],
                ["icon" => "🎫", "name" => "My Tickets", "link" => "tickets.php"],
                ["icon" => "💳", "name" => "Payments", "link" => "payments.php"],
                ["icon" => "👤", "name" => "Profile", "link" => "profile.php"]
            ];

            foreach($menus as $menu){ 
                $isActive = ($current_page == $menu['link']);
            ?>
                <a href="<?= $menu['link'] ?>" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all duration-200 group <?= $isActive ? 'bg-emerald-500/10 text-emerald-400' : 'text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400' ?>">
                    <span class="text-lg w-6 text-center opacity-70 group-hover:opacity-100 group-hover:scale-110 transition-transform <?= $isActive ? 'opacity-100' : '' ?>">
                        <?= $menu['icon'] ?>
                    </span>
                    <span class="font-medium text-sm"><?= $menu['name'] ?></span>
                </a>
            <?php } ?>

            <div class="pt-8 mt-4 border-t border-white/5">
                <a href="user_logout.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200">
                    <span class="text-lg w-6 text-center">🚪</span>
                    <span class="font-medium text-sm">Sign Out</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8 lg:px-12 pt-8 overflow-y-auto">

        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-emerald-400 text-sm font-semibold tracking-wider uppercase mb-1">Welcome Aboard</p>
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight">
                    Where to next? 🌍
                </h1>
            </div>
            <div class="text-slate-400 text-sm bg-white/5 px-4 py-2 rounded-full border border-white/10 backdrop-blur-md inline-block">
                Member since 2026
            </div>
        </div>

        <!-- DYNAMIC SEARCH FORM -->
        <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-3xl p-2 shadow-2xl mb-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/20 rounded-full blur-[80px] pointer-events-none"></div>
            
            <form action="search_trains.php" method="GET" class="bg-slate-900/50 rounded-2xl p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end relative z-10">
                
                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Leaving From</label>
                    <select id="source" name="source" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm appearance-none cursor-pointer">
                        <option value="" disabled selected class="bg-slate-900 text-white">City or Station</option>
                        <?php foreach($stations_map as $code => $name): ?>
                            <?php if(array_key_exists($code, $valid_routes)): ?>
                                <option value="<?= htmlspecialchars($code) ?>" class="bg-slate-900 text-white">
                                    <?= htmlspecialchars($code) ?> - <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Going To</label>
                    <select id="destination" name="destination" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm appearance-none disabled:opacity-50 cursor-pointer">
                        <option value="" disabled selected class="bg-slate-900 text-white">City or Station</option>
                    </select>
                </div>

                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Date of Travel</label>
                    <style>input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }</style>
                    <input type="date" name="date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d') ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-slate-300 outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm cursor-pointer">
                </div>

                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Seat Class</label>
                    <select name="class" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-slate-300 outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm appearance-none cursor-pointer">
                        <option value="All" class="bg-slate-900 text-white">All Classes</option>
                        <option value="AC" class="bg-slate-900 text-white">AC Classes</option>
                        <option value="SL" class="bg-slate-900 text-white">Sleeper (SL)</option>
                        <option value="GEN" class="bg-slate-900 text-white">General (UR)</option>
                    </select>
                </div>

                <div class="xl:col-span-1">
                    <button type="submit" name="search" value="1" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/25 transition-all active:scale-95 text-sm">
                        Find Trains
                    </button>
                </div>
            </form>
        </div>

        <!-- DYNAMIC STATS -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-10">
            <div class="bg-white/5 backdrop-blur-xl border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:bg-white/10 transition cursor-default">
                <div class="w-12 h-12 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xl">🎫</div>
                <div>
                    <p class="text-slate-400 text-xs">Active Trips</p>
                    <h3 class="text-2xl font-bold text-white"><?= $active_trips_count ?></h3>
                </div>
            </div>
            <div class="bg-white/5 backdrop-blur-xl border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:bg-white/10 transition cursor-default">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl">✨</div>
                <div>
                    <p class="text-slate-400 text-xs">Past Journeys</p>
                    <h3 class="text-2xl font-bold text-white"><?= $past_journeys_count ?></h3>
                </div>
            </div>
            <div class="bg-white/5 backdrop-blur-xl border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:bg-white/10 transition cursor-default">
                <div class="w-12 h-12 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl">💳</div>
                <div>
                    <p class="text-slate-400 text-xs">Reward Points</p>
                    <h3 class="text-2xl font-bold text-white">850</h3>
                </div>
            </div>
            <div class="bg-white/5 backdrop-blur-xl border border-white/5 rounded-2xl p-5 flex items-center gap-4 hover:bg-white/10 transition cursor-default">
                <div class="w-12 h-12 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-xl">🔔</div>
                <div>
                    <p class="text-slate-400 text-xs">Updates</p>
                    <h3 class="text-2xl font-bold text-white">2</h3>
                </div>
            </div>
        </div>

        <!-- DYNAMIC UPCOMING ITINERARY -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/5 rounded-3xl p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white">Your Itinerary</h2>
                <a href="tickets.php" class="text-emerald-400 text-sm hover:text-emerald-300 hover:underline">View all tickets →</a>
            </div>

            <?php if ($upcoming_ticket): 
                $seats_arr = json_decode($upcoming_ticket['seat_numbers'], true);
                $seat_display = is_array($seats_arr) ? $seats_arr[0] . (count($seats_arr) > 1 ? ' +' : '') : 'N/A';
            ?>
                <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-6 hover:border-emerald-500/30 transition-colors">
                    
                    <div class="flex items-center gap-6 w-full md:w-auto">
                        <div class="bg-emerald-500/10 text-emerald-400 p-3 rounded-xl text-center min-w-[70px]">
                            <span class="block text-xs uppercase"><?= date('M', strtotime($upcoming_ticket['travel_date'])) ?></span>
                            <span class="block text-xl font-black"><?= date('d', strtotime($upcoming_ticket['travel_date'])) ?></span>
                        </div>
                        <div>
                            <h4 class="text-white font-bold text-lg"><?= htmlspecialchars($upcoming_ticket['train_name']) ?></h4>
                            <p class="text-slate-400 text-sm flex items-center gap-2">
                                <span><?= htmlspecialchars($upcoming_ticket['source_station']) ?></span> 
                                <span class="text-slate-600">→</span> 
                                <span><?= htmlspecialchars($upcoming_ticket['destination_station']) ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between w-full md:w-auto gap-8">
                        <div class="text-left md:text-right">
                            <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Status</p>
                            <span class="inline-flex items-center gap-1.5 bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-xs font-bold">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span> Confirmed
                            </span>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Seat</p>
                            <p class="text-white font-mono font-bold"><?= htmlspecialchars($seat_display) ?></p>
                        </div>
                        <a href="tickets.php" class="bg-white/10 hover:bg-white/20 text-white p-2.5 rounded-xl transition tooltip" title="View Ticket">
                            ➔
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-6">
                    <p class="text-slate-400 text-sm">No upcoming journeys. <a href="search_trains.php" class="text-emerald-400 font-bold hover:underline">Book a ticket now!</a></p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- JAVASCRIPT FOR DYNAMIC DROPDOWNS -->
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Data securely passed from PHP to JS
    const validRoutes = <?= json_encode($valid_routes) ?>;
    const stationsMap = <?= json_encode($stations_map) ?>;

    const sourceSelect = document.getElementById('source');
    const destSelect = document.getElementById('destination');

    function updateDestinations() {
        const selectedSource = sourceSelect.value;
        
        // Reset the destination dropdown
        destSelect.innerHTML = '<option value="" disabled selected class="bg-slate-900 text-white">City or Station</option>';
        
        if (selectedSource && validRoutes[selectedSource]) {
            destSelect.disabled = false;
            
            // Sort destinations alphabetically
            const destinations = validRoutes[selectedSource].sort();

            destinations.forEach(destCode => {
                const option = document.createElement('option');
                option.value = destCode;
                option.className = 'bg-slate-900 text-white';
                
                const stationName = stationsMap[destCode] ? stationsMap[destCode] : '';
                option.text = destCode + ' - ' + stationName;
                
                destSelect.appendChild(option);
            });
        } else {
            destSelect.disabled = true;
        }
    }

    // Run every time the source changes
    sourceSelect.addEventListener('change', updateDestinations);
</script>

<?php include '../home/footer.php'; ?>