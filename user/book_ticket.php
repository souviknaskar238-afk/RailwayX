<?php
session_start();
// Make sure this path correctly points to your config.php file
require_once '../home/config.php'; 

// Ensure the user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    // header("Location: ../home/login.php");
    // exit();
}

// Fallback user ID for testing if login isn't fully integrated yet
$user_id = $_SESSION['user_id'] ?? 1; 
$error = '';
$train = null;
$base_fare = 0;
$selected_class_name = 'Class';

// 1. Fetch Train Details based on GET parameter (from Search Trains)
if (isset($_GET['train_id'])) {
    $train_id = (int)$_GET['train_id'];
    $date = $_GET['date'] ?? date('Y-m-d');
    $class = $_GET['class'] ?? 'All';

    try {
        $query = "SELECT * FROM trains WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $train_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $train = mysqli_fetch_assoc($result);
            
            // Determine Base Fare based on selected class
            if ($class === 'AC' && !empty($train['fare_ac'])) {
                $base_fare = $train['fare_ac'];
                $selected_class_name = 'AC Class';
            } elseif ($class === 'SL' && !empty($train['fare_sleeper'])) {
                $base_fare = $train['fare_sleeper'];
                $selected_class_name = 'Sleeper (SL)';
            } elseif ($class === 'GEN' && !empty($train['fare_general'])) {
                $base_fare = $train['fare_general'];
                $selected_class_name = 'General (UR)';
            } else {
                // Fallback to highest available class if 'All' was passed or class is missing
                if (!empty($train['fare_ac'])) { $base_fare = $train['fare_ac']; $selected_class_name = 'AC Class'; }
                elseif (!empty($train['fare_sleeper'])) { $base_fare = $train['fare_sleeper']; $selected_class_name = 'Sleeper (SL)'; }
                elseif (!empty($train['fare_general'])) { $base_fare = $train['fare_general']; $selected_class_name = 'General (UR)'; }
            }
        } else {
            header("Location: search_trains.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Failed to load train details.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: search_trains.php");
    exit();
}

// 2. Handle Form Submission (Processing the Booking)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_id = (int)$_POST['train_id'];
    $travel_date = trim($_POST['date']);
    $base_fare = (float)$_POST['base_fare'];
    $payment_method = trim($_POST['payment_method']);
    
    // Process Dynamic Passenger Array
    $names = $_POST['p_name'];
    $ages = $_POST['p_age'];
    $genders = $_POST['p_gender'];
    $num_passengers = count($names);
    
    $passenger_details = [];
    $seat_numbers = [];
    
    // Seat Prefix logic (e.g., A1 for AC, S1 for Sleeper, D1 for General)
    $seat_prefix = 'S';
    if(strpos($_POST['class_name'], 'AC') !== false) $seat_prefix = 'A';
    if(strpos($_POST['class_name'], 'General') !== false) $seat_prefix = 'D';

    for ($i = 0; $i < $num_passengers; $i++) {
        $passenger_details[] = [
            'name' => trim($names[$i]),
            'age' => (int)$ages[$i],
            'gender' => trim($genders[$i])
        ];
        // Generate mock seat numbers (e.g., A1-14, S2-45)
        $seat_numbers[] = $seat_prefix . rand(1, 4) . "-" . rand(1, 72); 
    }
    
    $json_passengers = json_encode($passenger_details);
    $json_seats = json_encode($seat_numbers);
    
    $total_fare = ($base_fare * $num_passengers) + 35; // Adding 35rs Convenience Fee
    $pnr = '8' . rand(100000000, 999999999); // Generate 10-digit Indian Railway style PNR
    $status = 'Confirmed';

    try {
        // Step A: Insert into Reservations Table
        $res_query = "INSERT INTO reservations (user_id, train_id, pnr_number, travel_date, num_passengers, passenger_details, seat_numbers, total_fare, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = mysqli_prepare($conn, $res_query);
        mysqli_stmt_bind_param($stmt1, "iisssssdss", $user_id, $train_id, $pnr, $travel_date, $num_passengers, $json_passengers, $json_seats, $total_fare, $payment_method, $status);
        
        if (mysqli_stmt_execute($stmt1)) {
            $reservation_id = mysqli_insert_id($conn);

            // Step B: Insert into Payments Table
            $txn_id = 'TXN' . strtoupper(uniqid());
            $pay_status = 'Paid';
            $pay_query = "INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($conn, $pay_query);
            mysqli_stmt_bind_param($stmt2, "idsss", $reservation_id, $total_fare, $payment_method, $txn_id, $pay_status);
            mysqli_stmt_execute($stmt2);

            // Step C: Update Reservation with the new Payment ID
            $upd_query = "UPDATE reservations SET payment_id = ? WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $upd_query);
            mysqli_stmt_bind_param($stmt3, "si", $txn_id, $reservation_id);
            mysqli_stmt_execute($stmt3);

            // Step D: Deduct available seats
            $seat_query = "UPDATE trains SET available_seats = available_seats - ? WHERE id = ?";
            $stmt4 = mysqli_prepare($conn, $seat_query);
            mysqli_stmt_bind_param($stmt4, "ii", $num_passengers, $train_id);
            mysqli_stmt_execute($stmt4);

            // Redirect to Tickets page with success message
            header("Location: tickets.php?msg=booked&pnr=" . $pnr);
            exit();
        } else {
            $error = "Failed to create reservation: " . mysqli_error($conn);
        }
    } catch (Exception $e) {
        $error = "Database Exception: " . $e->getMessage();
    }
}

include 'user_header.php';
?>

<div class="flex relative min-h-screen font-sans">
    
    <!-- FULL PAGE BACKGROUND -->
    <div class="fixed inset-0 -z-10 pointer-events-none">
        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover opacity-20" alt="Train Journey">
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
            $current_page = 'search_trains.php'; // Keep Search Trains highlighted during booking
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
                    <span class="text-lg w-6 text-center opacity-70 group-hover:opacity-100 group-hover:scale-110 transition-transform <?= $isActive ? 'opacity-100' : '' ?>"><?= $menu['icon'] ?></span>
                    <span class="font-medium text-sm"><?= $menu['name'] ?></span>
                </a>
            <?php } ?>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8 lg:px-12 pt-8 overflow-y-auto">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-600/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="mb-8 relative z-10 flex items-center justify-between">
            <div>
                <a href="search_trains.php" class="text-emerald-400 text-sm font-semibold hover:underline flex items-center gap-1 mb-2 transition-all hover:-translate-x-1 w-max">
                    ← Back to Search
                </a>
                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight flex items-center gap-3">
                    Passenger Details 📝
                </h1>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm relative z-10"><?= $error ?></div>
        <?php endif; ?>

        <?php if($train): ?>
        <form action="payment.php" method="POST" id="bookingForm" class="relative z-10 flex flex-col xl:flex-row gap-8 items-start pb-12">
            
            <input type="hidden" name="train_id" value="<?= $train['id'] ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
            <input type="hidden" id="baseFareInput" name="base_fare" value="<?= $base_fare ?>">
            <input type="hidden" name="class_name" value="<?= htmlspecialchars($selected_class_name) ?>">

            <!-- LEFT COLUMN: PASSENGER FORM -->
            <div class="w-full xl:w-2/3 flex flex-col gap-6">
                
                <!-- Train Quick Summary -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($train['train_name']) ?></h2>
                            <p class="text-slate-400 text-xs font-mono mt-1">TRAIN #<?= htmlspecialchars($train['train_number']) ?></p>
                        </div>
                        <span class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider w-max">
                            <?= htmlspecialchars($selected_class_name) ?>
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-4 sm:gap-8 bg-slate-950/50 rounded-2xl p-4 sm:p-6 border border-white/5">
                        <div class="w-1/3">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Departure</p>
                            <p class="text-white font-black text-xl sm:text-2xl"><?= htmlspecialchars($train['source_station']) ?></p>
                            <p class="text-emerald-400 text-sm font-medium"><?= date('h:i A', strtotime($train['departure_time'])) ?></p>
                        </div>
                        <div class="flex-1 border-t-2 border-dashed border-slate-600 relative">
                            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-slate-900 text-slate-300 text-xs px-3 py-1 rounded-full border border-slate-700 whitespace-nowrap">
                                <?= date('d M, Y', strtotime($date)) ?>
                            </span>
                        </div>
                        <div class="w-1/3 text-right">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Arrival</p>
                            <p class="text-white font-black text-xl sm:text-2xl"><?= htmlspecialchars($train['destination_station']) ?></p>
                            <p class="text-emerald-400 text-sm font-medium"><?= date('h:i A', strtotime($train['arrival_time'])) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Passenger Details Section -->
                <div class="bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-3xl p-6 md:p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="text-emerald-400 text-xl">👤</span> Passenger Roster
                        </h3>
                        <p class="text-xs text-slate-400 bg-white/5 px-3 py-1 rounded-full border border-white/10">Max 4</p>
                    </div>

                    <div id="passengerContainer" class="space-y-4">
                        <!-- Primary Passenger Row -->
                        <div class="passenger-row bg-white/5 border border-white/10 rounded-2xl p-5 relative overflow-hidden transition-all group hover:border-emerald-500/30">
                            <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                            
                            <div class="flex justify-between items-center mb-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest pass-num">Passenger 1</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-6">
                                    <input type="text" name="p_name[]" required placeholder="Full Name" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm placeholder-slate-600">
                                </div>
                                <div class="md:col-span-3">
                                    <input type="number" name="p_age[]" required min="1" max="120" placeholder="Age" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm placeholder-slate-600">
                                </div>
                                <div class="md:col-span-3">
                                    <select name="p_gender[]" required class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm appearance-none cursor-pointer">
                                        <option value="Male" class="bg-slate-900 text-white">Male</option>
                                        <option value="Female" class="bg-slate-900 text-white">Female</option>
                                        <option value="Other" class="bg-slate-900 text-white">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addPassenger()" id="addPassBtn" class="mt-6 text-sm font-bold text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 border border-emerald-500/30 hover:border-emerald-500 px-5 py-2.5 rounded-xl transition-all flex items-center justify-center w-full sm:w-auto gap-2">
                        <span>+</span> Add Another Passenger
                    </button>
                </div>

                <!-- Payment Method -->
                <div class="bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-3xl p-6 md:p-8 shadow-2xl">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="text-emerald-400 text-xl">💳</span> Payment Method
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="UPI" checked class="peer sr-only">
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center peer-checked:bg-emerald-500/10 peer-checked:border-emerald-500 transition-all hover:bg-white/10">
                                <div class="text-3xl mb-3">📱</div>
                                <p class="text-sm font-bold text-white">UPI / QR</p>
                            </div>
                            <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 border-emerald-500 bg-slate-900 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            </div>
                        </label>

                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="Credit/Debit Card" class="peer sr-only">
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center peer-checked:bg-emerald-500/10 peer-checked:border-emerald-500 transition-all hover:bg-white/10">
                                <div class="text-3xl mb-3">💳</div>
                                <p class="text-sm font-bold text-white">Card</p>
                            </div>
                            <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 border-emerald-500 bg-slate-900 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            </div>
                        </label>

                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="Net Banking" class="peer sr-only">
                            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-center peer-checked:bg-emerald-500/10 peer-checked:border-emerald-500 transition-all hover:bg-white/10">
                                <div class="text-3xl mb-3">🏦</div>
                                <p class="text-sm font-bold text-white">Net Banking</p>
                            </div>
                            <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 border-emerald-500 bg-slate-900 flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: FARE SUMMARY STICKY SIDEBAR -->
            <div class="w-full xl:w-1/3 xl:sticky xl:top-28">
                <div class="bg-slate-900/80 backdrop-blur-3xl border border-white/10 rounded-3xl p-6 md:p-8 shadow-[0_0_40px_rgba(0,0,0,0.5)] relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-[50px] pointer-events-none"></div>

                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span>🧾</span> Fare Summary
                    </h3>

                    <div class="space-y-4 mb-6 relative z-10">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Base Fare (<span id="passCountUI">1</span> × ₹<?= number_format($base_fare, 0) ?>)</span>
                            <span class="text-white font-mono font-medium">₹<span id="baseTotalUI"><?= number_format($base_fare, 0) ?></span></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Convenience Fee</span>
                            <span class="text-white font-mono font-medium">₹35</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Travel Insurance</span>
                            <span class="text-emerald-400 font-bold tracking-wide text-[10px] uppercase bg-emerald-500/10 px-2 py-0.5 rounded">Free</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-white/20 pt-6 mb-8 relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-white font-bold text-lg">Total Amount</span>
                            <span class="text-emerald-400 font-black text-3xl font-mono">₹<span id="grandTotalUI"><?= number_format($base_fare + 35, 0) ?></span></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 py-4 rounded-xl font-black shadow-[0_4px_20px_rgba(16,185,129,0.3)] transition-all active:scale-95 text-sm uppercase tracking-widest flex items-center justify-center gap-2 group relative z-10">
                        Pay & Book Ticket <span class="group-hover:translate-x-1 transition-transform">➔</span>
                    </button>
                    
                    <div class="mt-6 flex items-center justify-center gap-3 text-slate-500 relative z-10">
                        <span class="text-xl">🔒</span>
                        <p class="text-[10px] uppercase tracking-widest font-semibold leading-tight">
                            Secure 256-bit<br>Encrypted Payment
                        </p>
                    </div>
                </div>
            </div>

        </form>
        <?php endif; ?>

    </main>
</div>

<!-- DYNAMIC PASSENGER SCRIPT -->
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    const baseFare = <?= $base_fare ?>;
    const fee = 35;
    let passengerCount = 1;
    const maxPassengers = 4;

    function updateSummary() {
        const baseTotal = passengerCount * baseFare;
        const grandTotal = baseTotal + fee;
        
        document.getElementById('passCountUI').innerText = passengerCount;
        document.getElementById('baseTotalUI').innerText = baseTotal.toLocaleString('en-IN');
        document.getElementById('grandTotalUI').innerText = grandTotal.toLocaleString('en-IN');

        const btn = document.getElementById('addPassBtn');
        if (passengerCount >= maxPassengers) {
            btn.style.display = 'none';
        } else {
            btn.style.display = 'flex';
        }
    }

    function removePassenger(btn) {
        btn.closest('.passenger-row').remove();
        passengerCount--;
        updateSummary();
        
        // Re-number remaining passenger labels
        const rows = document.querySelectorAll('.passenger-row');
        rows.forEach((row, index) => {
            const numLabel = row.querySelector('.pass-num');
            if (numLabel) numLabel.innerText = 'Passenger ' + (index + 1);
        });
    }

    function addPassenger() {
        if (passengerCount >= maxPassengers) return;
        passengerCount++;

        const container = document.getElementById('passengerContainer');
        const newRow = document.createElement('div');
        newRow.className = 'passenger-row bg-white/5 border border-white/10 rounded-2xl p-5 relative overflow-hidden transition-all group hover:border-emerald-500/30 mt-4';
        
        newRow.innerHTML = `
            <div class="absolute top-0 left-0 w-1 h-full bg-slate-500"></div>
            <div class="flex justify-between items-center mb-4">
                <p class="text-xs font-bold text-slate-300 uppercase tracking-widest pass-num">Passenger ${passengerCount}</p>
                <button type="button" onclick="removePassenger(this)" class="text-red-400 hover:text-red-300 bg-red-500/10 px-3 py-1 rounded-lg text-xs font-bold transition-colors">Remove</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-6">
                    <input type="text" name="p_name[]" required placeholder="Full Name" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm placeholder-slate-600">
                </div>
                <div class="md:col-span-3">
                    <input type="number" name="p_age[]" required min="1" max="120" placeholder="Age" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm placeholder-slate-600">
                </div>
                <div class="md:col-span-3">
                    <select name="p_gender[]" required class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm appearance-none cursor-pointer">
                        <option value="Male" class="bg-slate-900 text-white">Male</option>
                        <option value="Female" class="bg-slate-900 text-white">Female</option>
                        <option value="Other" class="bg-slate-900 text-white">Other</option>
                    </select>
                </div>
            </div>
        `;
        
        container.appendChild(newRow);
        updateSummary();
    }
</script>

<?php include '../home/footer.php'; ?>