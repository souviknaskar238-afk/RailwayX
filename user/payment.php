<?php
session_start();
require_once '../home/config.php'; 

// Ensure user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    // header("Location: ../home/login.php");
    // exit();
}

$user_id = $_SESSION['user_id'] ?? 1; // Fallback for testing
$error = '';

// If the form is submitted from this very page (Final Confirm Payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    
    $train_id = (int)$_POST['train_id'];
    $travel_date = trim($_POST['date']);
    $base_fare = (float)$_POST['base_fare'];
    $payment_method = trim($_POST['payment_method']);
    
    $names = $_POST['p_name'];
    $ages = $_POST['p_age'];
    $genders = $_POST['p_gender'];
    $num_passengers = count($names);
    
    $passenger_details = [];
    $seat_numbers = [];
    
    $seat_prefix = 'S';
    if(strpos($_POST['class_name'], 'AC') !== false) $seat_prefix = 'A';
    if(strpos($_POST['class_name'], 'General') !== false) $seat_prefix = 'D';

    for ($i = 0; $i < $num_passengers; $i++) {
        $passenger_details[] = [
            'name' => trim($names[$i]),
            'age' => (int)$ages[$i],
            'gender' => trim($genders[$i])
        ];
        $seat_numbers[] = $seat_prefix . rand(1, 4) . "-" . rand(1, 72); 
    }
    
    $json_passengers = json_encode($passenger_details);
    $json_seats = json_encode($seat_numbers);
    
    $total_fare = ($base_fare * $num_passengers) + 35;
    $pnr = '8' . rand(100000000, 999999999);
    $status = 'Confirmed';

    try {
        // Step A: Insert Reservation
        $res_query = "INSERT INTO reservations (user_id, train_id, pnr_number, travel_date, num_passengers, passenger_details, seat_numbers, total_fare, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = mysqli_prepare($conn, $res_query);
        mysqli_stmt_bind_param($stmt1, "iisssssdss", $user_id, $train_id, $pnr, $travel_date, $num_passengers, $json_passengers, $json_seats, $total_fare, $payment_method, $status);
        
        if (mysqli_stmt_execute($stmt1)) {
            $reservation_id = mysqli_insert_id($conn);

            // Step B: Record Payment
            $txn_id = 'TXN' . strtoupper(uniqid());
            $pay_status = 'Paid';
            $pay_query = "INSERT INTO payments (reservation_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($conn, $pay_query);
            mysqli_stmt_bind_param($stmt2, "idsss", $reservation_id, $total_fare, $payment_method, $txn_id, $pay_status);
            mysqli_stmt_execute($stmt2);

            // Step C: Link Payment to Reservation
            $upd_query = "UPDATE reservations SET payment_id = ? WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $upd_query);
            mysqli_stmt_bind_param($stmt3, "si", $txn_id, $reservation_id);
            mysqli_stmt_execute($stmt3);

            // Step D: Deduct Seats
            $seat_query = "UPDATE trains SET available_seats = available_seats - ? WHERE id = ?";
            $stmt4 = mysqli_prepare($conn, $seat_query);
            mysqli_stmt_bind_param($stmt4, "ii", $num_passengers, $train_id);
            mysqli_stmt_execute($stmt4);

            header("Location: tickets.php?msg=booked&pnr=" . $pnr);
            exit();
        } else {
            $error = "Failed to create reservation: " . mysqli_error($conn);
        }
    } catch (Exception $e) {
        $error = "Database Exception: " . $e->getMessage();
    }
} 
// If accessed directly without form data
elseif (!isset($_POST['train_id'])) {
    header("Location: search_trains.php");
    exit();
}

// Calculate totals for the UI
$num_passengers = isset($_POST['p_name']) ? count($_POST['p_name']) : 1;
$base_fare = (float)$_POST['base_fare'];
$total_amount = ($base_fare * $num_passengers) + 35;
$payment_method = $_POST['payment_method'] ?? 'UPI';

include 'user_header.php';
?>

<div class="flex relative min-h-screen font-sans">
    
    <div class="fixed inset-0 -z-10 pointer-events-none">
        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover opacity-10" alt="Train Journey">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/50"></div>
    </div>

    <!-- Processing Overlay (Hidden by default) -->
    <div id="processingOverlay" class="fixed inset-0 bg-slate-950/90 backdrop-blur-md z-[100] hidden flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-slate-700 border-t-emerald-500 rounded-full animate-spin mb-6"></div>
        <h2 class="text-2xl font-bold text-white tracking-wide mb-2">Processing Payment...</h2>
        <p class="text-slate-400 text-sm">Please do not refresh or close this window.</p>
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
                ["icon" => "🚆", "name" => "Journey History", "link" => "history.php"],
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

    <main class="flex-1 p-4 md:p-8 lg:px-12 pt-8 overflow-y-auto flex justify-center">
        
        <div class="w-full max-w-4xl">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black text-white tracking-tight flex items-center justify-center gap-3">
                    <span class="text-emerald-400">🔒</span> Secure Checkout
                </h1>
                <p class="text-sm text-slate-400 mt-2">Complete your payment to confirm your tickets.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-6 text-sm text-center"><?= $error ?></div>
            <?php endif; ?>

            <div class="bg-slate-900/60 backdrop-blur-2xl border border-white/10 rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row">
                
                <!-- Order Summary Panel -->
                <div class="w-full md:w-2/5 bg-slate-950/80 p-8 border-b md:border-b-0 md:border-r border-white/5 relative">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-[60px]"></div>
                    
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-6">Order Summary</h3>
                    
                    <div class="space-y-4 mb-8">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Journey Date</p>
                            <p class="text-white font-medium"><?= date('d M, Y', strtotime($_POST['date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Class</p>
                            <p class="text-emerald-400 font-bold"><?= htmlspecialchars($_POST['class_name']) ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Passengers</p>
                            <p class="text-white font-medium"><?= $num_passengers ?> Traveler(s)</p>
                        </div>
                    </div>

                    <div class="border-t border-white/10 pt-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-400 text-sm">Amount</span>
                            <span class="text-white">₹<?= number_format($base_fare * $num_passengers, 0) ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-slate-400 text-sm">Fee & Taxes</span>
                            <span class="text-white">₹35</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-dashed border-slate-600">
                            <span class="text-white font-bold">Total Pay</span>
                            <span class="text-2xl font-black text-emerald-400">₹<?= number_format($total_amount, 0) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway Panel -->
                <div class="w-full md:w-3/5 p-8 relative">
                    <div class="flex items-center gap-3 mb-8">
                        <?php if($payment_method === 'UPI'): ?>
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-xl">📱</div>
                            <h3 class="text-xl font-bold text-white">Pay via UPI</h3>
                        <?php elseif($payment_method === 'Credit/Debit Card'): ?>
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-xl">💳</div>
                            <h3 class="text-xl font-bold text-white">Card Payment</h3>
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 flex items-center justify-center text-xl">🏦</div>
                            <h3 class="text-xl font-bold text-white">Net Banking</h3>
                        <?php endif; ?>
                    </div>

                    <form action="payment.php" method="POST" id="finalPaymentForm">
                        
                        <!-- Hidden inputs to carry over all data from previous page -->
                        <input type="hidden" name="confirm_payment" value="1">
                        <input type="hidden" name="train_id" value="<?= htmlspecialchars($_POST['train_id']) ?>">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($_POST['date']) ?>">
                        <input type="hidden" name="base_fare" value="<?= htmlspecialchars($_POST['base_fare']) ?>">
                        <input type="hidden" name="class_name" value="<?= htmlspecialchars($_POST['class_name']) ?>">
                        <input type="hidden" name="payment_method" value="<?= htmlspecialchars($payment_method) ?>">
                        
                        <?php foreach($_POST['p_name'] as $index => $pname): ?>
                            <input type="hidden" name="p_name[]" value="<?= htmlspecialchars($pname) ?>">
                            <input type="hidden" name="p_age[]" value="<?= htmlspecialchars($_POST['p_age'][$index]) ?>">
                            <input type="hidden" name="p_gender[]" value="<?= htmlspecialchars($_POST['p_gender'][$index]) ?>">
                        <?php endforeach; ?>

                        <!-- MOCK UI BASED ON SELECTED METHOD -->
                        <?php if($payment_method === 'UPI'): ?>
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-48 h-48 bg-white p-2 rounded-xl border-4 border-slate-700 relative overflow-hidden mb-6">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=railwayx@upi&pn=RailWayX&am=<?= $total_amount ?>" alt="QR Code" class="w-full h-full object-contain">
                                    <div class="absolute inset-0 bg-slate-900/10 backdrop-blur-[1px]"></div>
                                </div>
                                <p class="text-slate-400 text-sm mb-4">Scan the QR code with any UPI app</p>
                                <div class="w-full relative">
                                    <span class="absolute left-4 top-3 text-slate-500">ID</span>
                                    <input type="text" placeholder="Or enter your UPI ID (e.g. user@okhdfc)" class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm">
                                </div>
                            </div>

                        <?php elseif($payment_method === 'Credit/Debit Card'): ?>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Card Number</label>
                                    <input type="text" placeholder="XXXX XXXX XXXX XXXX" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono tracking-widest">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Expiry</label>
                                        <input type="text" placeholder="MM/YY" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono text-center">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">CVV</label>
                                        <input type="password" placeholder="•••" maxlength="3" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono text-center">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Cardholder Name</label>
                                    <input type="text" placeholder="Name on card" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm">
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Select Your Bank</label>
                                    <select class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white outline-none focus:border-emerald-500 transition-all text-sm cursor-pointer appearance-none">
                                        <option value="sbi">State Bank of India (SBI)</option>
                                        <option value="hdfc">HDFC Bank</option>
                                        <option value="icici">ICICI Bank</option>
                                        <option value="axis">Axis Bank</option>
                                        <option value="pnb">Punjab National Bank</option>
                                    </select>
                                </div>
                                <p class="text-xs text-slate-500 mt-4 text-center">You will be securely redirected to your bank's portal.</p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-8 pt-6 border-t border-white/10">
                            <button type="button" onclick="processMockPayment()" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 py-3.5 rounded-xl font-bold shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all active:scale-95 text-base">
                                Pay ₹<?= number_format($total_amount, 0) ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
            <div class="mt-6 flex items-center justify-center gap-6 text-slate-500">
                <span class="text-xs font-semibold flex items-center gap-1">✅ PCI DSS Compliant</span>
                <span class="text-xs font-semibold flex items-center gap-1">✅ 256-bit Encryption</span>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    // Function to simulate payment processing delay
    function processMockPayment() {
        // Show loading overlay
        document.getElementById('processingOverlay').classList.remove('hidden');
        document.getElementById('processingOverlay').classList.add('flex');
        
        // Wait 2 seconds, then submit the form automatically
        setTimeout(() => {
            document.getElementById('finalPaymentForm').submit();
        }, 2000);
    }
</script>

<?php include '../home/footer.php'; ?>