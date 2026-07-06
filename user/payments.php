<?php
session_start();
// Make sure this path correctly points to your config.php file
require_once '../home/config.php'; 

// Ensure user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    // header("Location: ../home/login.php");
    // exit();
}

$user_id = $_SESSION['user_id'] ?? 1; // Fallback for testing
$error_msg = '';
$payments = [];

try {
    // Fetch user's payments, joining with reservations and trains for extra context
    $query = "
        SELECT 
            p.transaction_id, 
            p.amount, 
            p.payment_method, 
            p.payment_date, 
            p.status AS payment_status,
            r.pnr_number,
            t.train_name
        FROM payments p
        JOIN reservations r ON p.reservation_id = r.id
        JOIN trains t ON r.train_id = t.id
        WHERE r.user_id = ?
        ORDER BY p.payment_date DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        $payments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "Failed to load payment history: " . $e->getMessage();
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
            $current_page = 'payments.php';
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
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-600/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-emerald-400 text-sm font-semibold tracking-wider uppercase mb-1">Financial Ledger</p>
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight flex items-center gap-3">
                    Payment History 💳
                </h1>
            </div>
            
            <!-- Quick Stats -->
            <?php if(count($payments) > 0): 
                $total_spent = 0;
                foreach($payments as $p) {
                    if($p['payment_status'] === 'Paid') $total_spent += $p['amount'];
                }
            ?>
            <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-2xl px-6 py-3 flex items-center gap-4">
                <div class="w-10 h-10 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-lg">💰</div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Total Spent</p>
                    <p class="text-white font-black font-mono">₹<?= number_format($total_spent, 2) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if(!empty($error_msg)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl mb-8 text-sm relative z-10 flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span><?= $error_msg ?></span>
            </div>
        <?php endif; ?>

        <!-- TRANSACTIONS LIST -->
        <div class="relative z-10 pb-12">
            <?php if (count($payments) > 0): ?>
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
                    
                    <!-- Desktop Header -->
                    <div class="hidden md:grid grid-cols-12 gap-4 px-8 py-4 border-b border-white/10 bg-slate-900/50">
                        <div class="col-span-3 text-xs font-bold text-slate-400 uppercase tracking-widest">Transaction Info</div>
                        <div class="col-span-3 text-xs font-bold text-slate-400 uppercase tracking-widest">Journey Details</div>
                        <div class="col-span-2 text-xs font-bold text-slate-400 uppercase tracking-widest">Method</div>
                        <div class="col-span-2 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Amount</div>
                        <div class="col-span-2 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Status</div>
                    </div>

                    <!-- Payment Rows -->
                    <div class="divide-y divide-white/5">
                        <?php foreach ($payments as $payment): 
                            $is_refunded = (strcasecmp($payment['payment_status'], 'Refunded') == 0);
                            $date_obj = new DateTime($payment['payment_date']);
                        ?>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 px-6 md:px-8 py-6 items-center hover:bg-white/[0.02] transition-colors group">
                                
                                <!-- Transaction ID & Date -->
                                <div class="md:col-span-3 flex flex-col gap-1">
                                    <div class="flex items-center gap-2 mb-1 md:mb-0">
                                        <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">TXN ID</span>
                                        <span class="font-mono text-white font-bold text-sm bg-slate-950 px-2 py-0.5 rounded border border-white/10 group-hover:border-emerald-500/30 transition-colors">
                                            <?= htmlspecialchars($payment['transaction_id']) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">Date</span>
                                        <span class="text-xs text-slate-400">
                                            <?= $date_obj->format('M d, Y • h:i A') ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Journey Info -->
                                <div class="md:col-span-3 flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">PNR</span>
                                        <span class="text-emerald-400 font-bold text-sm tracking-wide">
                                            <?= htmlspecialchars($payment['pnr_number']) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">Train</span>
                                        <span class="text-xs text-slate-300 truncate max-w-[200px]" title="<?= htmlspecialchars($payment['train_name']) ?>">
                                            <?= htmlspecialchars($payment['train_name']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">Method</span>
                                    <span class="inline-flex items-center gap-2 text-sm text-slate-300 bg-white/5 px-3 py-1.5 rounded-lg border border-white/5">
                                        <?php 
                                            $method = strtolower($payment['payment_method']);
                                            if (strpos($method, 'upi') !== false) echo "📱";
                                            elseif (strpos($method, 'card') !== false) echo "💳";
                                            elseif (strpos($method, 'net') !== false) echo "🏦";
                                            else echo "💵";
                                        ?>
                                        <?= htmlspecialchars($payment['payment_method']) ?>
                                    </span>
                                </div>

                                <!-- Amount -->
                                <div class="md:col-span-2 flex items-center md:justify-end gap-2">
                                    <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">Amount</span>
                                    <span class="font-mono text-lg font-black <?= $is_refunded ? 'text-slate-500 line-through' : 'text-white' ?>">
                                        ₹<?= number_format($payment['amount'], 2) ?>
                                    </span>
                                </div>

                                <!-- Status -->
                                <div class="md:col-span-2 flex items-center md:justify-end gap-2">
                                    <span class="text-slate-500 md:hidden text-xs font-bold uppercase tracking-widest w-24">Status</span>
                                    <?php if($is_refunded): ?>
                                        <span class="bg-slate-500/10 text-slate-400 border border-slate-500/30 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 w-max">
                                            Refunded
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 w-max">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Paid
                                        </span>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- EMPTY STATE -->
                <div class="bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-16 text-center mt-10">
                    <div class="text-6xl mb-6 opacity-80">💸</div>
                    <h3 class="text-2xl font-bold text-white mb-2">No Transactions Yet</h3>
                    <p class="text-slate-400 text-sm max-w-md mx-auto mb-8">Your payment history is currently empty. Book a journey to see your transaction records here.</p>
                    <a href="search_trains.php" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-8 rounded-xl shadow-lg shadow-emerald-500/25 transition-all inline-block">
                        Book a Ticket
                    </a>
                </div>
            <?php endif; ?>
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
</script>

<?php include '../home/footer.php'; ?>