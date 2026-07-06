<?php
session_start();
require_once '../home/config.php'; 

// Ensure the user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    // header("Location: ../home/login.php");
    // exit();
}

$stations_map = [];
$valid_routes = [];
$search_results = [];
$has_searched = false;

// 1. Fetch ALL stations for mapping names
try {
    $station_query = "SELECT code, name FROM stations ORDER BY name ASC";
    $station_result = mysqli_query($conn, $station_query);
    if ($station_result) {
        while($row = mysqli_fetch_assoc($station_result)) {
            $stations_map[$row['code']] = $row['name'];
        }
    }

    // 2. Fetch valid routes from active trains to power the dynamic dropdown
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

// 3. Handle the Search Request
if (isset($_GET['source']) && isset($_GET['destination'])) {
    $has_searched = true;
    $source = $_GET['source'];
    $destination = $_GET['destination'];
    $date = $_GET['date'] ?? date('Y-m-d');
    $class = $_GET['class'] ?? 'All';

    // Build the dynamic class filter for the database
    $class_filter = "";
    if ($class === 'AC') {
        $class_filter = " AND fare_ac IS NOT NULL ";
    } elseif ($class === 'SL') {
        $class_filter = " AND fare_sleeper IS NOT NULL ";
    } elseif ($class === 'GEN') {
        $class_filter = " AND fare_general IS NOT NULL ";
    }

    try {
        $search_query = "
            SELECT * FROM trains 
            WHERE source_station = ? 
            AND destination_station = ? 
            AND status != 'Cancelled'
            $class_filter
            ORDER BY departure_time ASC
        ";
        
        $stmt = mysqli_prepare($conn, $search_query);
        mysqli_stmt_bind_param($stmt, "ss", $source, $destination);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            $search_results = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    } catch (Exception $e) {}
}

include 'user_header.php';
?>

<div class="flex relative min-h-screen font-sans">

    <div class="fixed inset-0 -z-10 pointer-events-none">
        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover opacity-20" alt="Train Journey">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-slate-900/90 to-emerald-950/80"></div>
    </div>

    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden"></div>

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
            $current_page = 'search_trains.php';
            
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

    <main class="flex-1 p-4 md:p-8 lg:px-12 pt-8 overflow-y-auto">
        
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-600/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-emerald-400 text-sm font-semibold tracking-wider uppercase mb-1">Search & Book</p>
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight flex items-center gap-3">
                    Find Trains 🚂
                </h1>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-3xl p-2 shadow-2xl mb-10 relative overflow-hidden z-10">
            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/20 rounded-full blur-[80px] pointer-events-none"></div>
            
            <form action="search_trains.php" method="GET" class="bg-slate-900/50 rounded-2xl p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 items-end relative z-10">
                
                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Leaving From</label>
                    <select id="source" name="source" required class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm appearance-none cursor-pointer">
                        <option value="" disabled selected class="bg-slate-900 text-white">City or Station</option>
                        <?php foreach($stations_map as $code => $name): ?>
                            <?php if(array_key_exists($code, $valid_routes)): ?>
                                <option value="<?= htmlspecialchars($code) ?>" class="bg-slate-900 text-white" <?= (isset($_GET['source']) && $_GET['source'] == $code) ? 'selected' : '' ?>>
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
                    <input type="date" name="date" required value="<?= htmlspecialchars($_GET['date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>" min="<?= date('Y-m-d') ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-slate-300 outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm cursor-pointer">
                </div>

                <div class="xl:col-span-1">
                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Seat Class</label>
                    <select name="class" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-slate-300 outline-none focus:border-emerald-500 focus:bg-white/10 transition-all text-sm appearance-none cursor-pointer">
                        <option value="All" class="bg-slate-900 text-white">All Classes</option>
                        <option value="AC" class="bg-slate-900 text-white" <?= (isset($_GET['class']) && $_GET['class'] == 'AC') ? 'selected' : '' ?>>AC Classes</option>
                        <option value="SL" class="bg-slate-900 text-white" <?= (isset($_GET['class']) && $_GET['class'] == 'SL') ? 'selected' : '' ?>>Sleeper (SL)</option>
                        <option value="GEN" class="bg-slate-900 text-white" <?= (isset($_GET['class']) && $_GET['class'] == 'GEN') ? 'selected' : '' ?>>General (UR)</option>
                    </select>
                </div>

                <div class="xl:col-span-1">
                    <button type="submit" name="search" value="1" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 py-3.5 rounded-xl font-bold shadow-lg shadow-emerald-500/25 transition-all active:scale-95 text-sm">
                        Find Trains
                    </button>
                </div>
            </form>
        </div>

        <?php if ($has_searched): ?>
            <div class="relative z-10">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white">
                        <?= count($search_results) ?> Train(s) Found
                    </h3>
                    <p class="text-sm font-medium text-emerald-400 bg-emerald-500/10 px-4 py-1.5 rounded-full border border-emerald-500/20">
                        <?= htmlspecialchars($_GET['source']) ?> → <?= htmlspecialchars($_GET['destination']) ?>
                    </p>
                </div>

                <?php if (count($search_results) > 0): ?>
                    <div class="space-y-6 pb-10">
                        <?php 
                        $selected_class = $_GET['class'] ?? 'All';
                        
                        foreach ($search_results as $train): 
                            $dep_time = strtotime($train['departure_time']);
                            $arr_time = strtotime($train['arrival_time']);
                            $diff = abs($arr_time - $dep_time);
                            $hours = floor($diff / 3600);
                            $minutes = floor(($diff - ($hours * 3600)) / 60);
                            $duration = sprintf("%02dh %02dm", $hours, $minutes);

                            $show_ac = !empty($train['fare_ac']) && ($selected_class === 'All' || $selected_class === 'AC');
                            $show_sl = !empty($train['fare_sleeper']) && ($selected_class === 'All' || $selected_class === 'SL');
                            $show_gen = !empty($train['fare_general']) && ($selected_class === 'All' || $selected_class === 'GEN');
                        ?>
                            <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-6 hover:border-emerald-500/30 transition-colors group">
                                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
                                    
                                    <div class="flex-1 w-full">
                                        <div class="flex items-center gap-3 mb-6">
                                            <h4 class="text-2xl font-bold text-white"><?= htmlspecialchars($train['train_name']) ?></h4>
                                            <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-md text-xs font-black tracking-widest group-hover:bg-emerald-500/20 transition-colors">
                                                #<?= htmlspecialchars($train['train_number']) ?>
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between xl:justify-start xl:gap-12 w-full">
                                            <div class="text-center xl:text-left">
                                                <p class="text-3xl font-black text-white mb-1"><?= date('h:i A', $dep_time) ?></p>
                                                <p class="text-sm text-slate-400 font-bold"><?= htmlspecialchars($train['source_station']) ?></p>
                                            </div>

                                            <div class="flex-1 max-w-[200px] flex flex-col items-center justify-center px-4">
                                                <p class="text-[10px] text-slate-400 mb-1.5 font-semibold tracking-widest"><?= $duration ?></p>
                                                <div class="w-full h-[2px] bg-slate-700 relative">
                                                    <div class="absolute -top-1.5 left-0 w-3 h-3 rounded-full bg-emerald-500 shadow-[0_0_10px_#10b981]"></div>
                                                    <div class="absolute -top-1.5 right-0 w-3 h-3 rounded-full bg-slate-500"></div>
                                                </div>
                                                <p class="text-[10px] text-emerald-400 mt-2 uppercase tracking-widest"><?= htmlspecialchars($train['running_days'] ?? 'Daily') ?></p>
                                            </div>

                                            <div class="text-center xl:text-right">
                                                <p class="text-3xl font-black text-white mb-1"><?= date('h:i A', $arr_time) ?></p>
                                                <p class="text-sm text-slate-400 font-bold"><?= htmlspecialchars($train['destination_station']) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full xl:w-[300px] shrink-0 border-t xl:border-t-0 xl:border-l border-white/10 pt-6 xl:pt-0 xl:pl-8 flex flex-col h-full">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">Available Classes</p>
                                        
                                        <div class="flex flex-wrap gap-3 mb-6">
                                            <?php if($show_ac): ?>
                                                <div class="bg-slate-900/80 border border-white/5 rounded-xl p-3 min-w-[80px] flex-1 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-500/10 transition">
                                                    <p class="text-emerald-400 font-black text-sm mb-1">AC</p>
                                                    <p class="text-white text-xs">₹<?= number_format($train['fare_ac'], 0) ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if($show_sl): ?>
                                                <div class="bg-slate-900/80 border border-white/5 rounded-xl p-3 min-w-[80px] flex-1 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-500/10 transition">
                                                    <p class="text-emerald-400 font-black text-sm mb-1">SL</p>
                                                    <p class="text-white text-xs">₹<?= number_format($train['fare_sleeper'], 0) ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if($show_gen): ?>
                                                <div class="bg-slate-900/80 border border-white/5 rounded-xl p-3 min-w-[80px] flex-1 text-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-500/10 transition">
                                                    <p class="text-emerald-400 font-black text-sm mb-1">GEN</p>
                                                    <p class="text-white text-xs">₹<?= number_format($train['fare_general'], 0) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-auto flex flex-wrap items-center justify-between gap-4 w-full pt-4 border-t border-white/5">
                                            <span class="text-xs font-bold text-slate-300 flex items-center gap-1.5 whitespace-nowrap">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block shadow-[0_0_5px_#10b981]"></span> 
                                                <?= htmlspecialchars($train['available_seats']) ?> Seats
                                            </span>
                                            <form action="book_ticket.php" method="GET" class="m-0 shrink-0">
                                                <input type="hidden" name="train_id" value="<?= $train['id'] ?>">
                                                <input type="hidden" name="date" value="<?= htmlspecialchars($_GET['date']) ?>">
                                                <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
                                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-sm font-bold py-2.5 px-6 rounded-xl shadow-[0_4px_15px_rgba(16,185,129,0.2)] transition-all whitespace-nowrap">
                                                    Book Now
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white/5 border border-white/5 backdrop-blur-xl rounded-3xl p-12 text-center mt-4">
                        <div class="text-5xl mb-4 opacity-50">🛤️</div>
                        <h3 class="text-xl font-bold text-white mb-2">No Trains Found</h3>
                        <p class="text-slate-400 text-sm max-w-md mx-auto">We couldn't find any active trains matching this specific class on the selected date.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="relative z-10 bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-16 text-center mt-10">
                    <div class="text-6xl mb-6 opacity-80">🗺️</div>
                    <h3 class="text-2xl font-bold text-white mb-2">Where will your journey take you?</h3>
                    <p class="text-slate-400 text-sm max-w-lg mx-auto">Select your origin, destination, and travel date above to view real-time train schedules and seat availability.</p>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        const validRoutes = <?= json_encode($valid_routes) ?>;
        const stationsMap = <?= json_encode($stations_map) ?>;
        const currentDestination = "<?= htmlspecialchars($_GET['destination'] ?? '') ?>";

        const sourceSelect = document.getElementById('source');
        const destSelect = document.getElementById('destination');

        function updateDestinations() {
            const selectedSource = sourceSelect.value;
            destSelect.innerHTML = '<option value="" disabled selected class="bg-slate-900 text-white">City or Station</option>';
            
            if (selectedSource && validRoutes[selectedSource]) {
                destSelect.disabled = false;
                const destinations = validRoutes[selectedSource].sort();

                destinations.forEach(destCode => {
                    const option = document.createElement('option');
                    option.value = destCode;
                    option.className = 'bg-slate-900 text-white';
                    
                    const stationName = stationsMap[destCode] ? stationsMap[destCode] : '';
                    option.text = destCode + ' - ' + stationName;
                    
                    if (destCode === currentDestination) {
                        option.selected = true;
                    }
                    destSelect.appendChild(option);
                });
            } else {
                destSelect.disabled = true;
            }
        }

        window.onload = updateDestinations;
        sourceSelect.addEventListener('change', updateDestinations);
    </script>

<?php include '../home/footer.php'; ?>