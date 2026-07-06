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
$success_msg = '';
$error_msg = '';

// Check for success message from booking
if (isset($_GET['msg']) && $_GET['msg'] === 'booked' && isset($_GET['pnr'])) {
    $success_msg = "Booking Confirmed! Your PNR is <strong>" . htmlspecialchars($_GET['pnr']) . "</strong>.";
}

// Handle Ticket Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_ticket_id'])) {
    $cancel_id = (int)$_POST['cancel_ticket_id'];
    
    try {
        // Ensure the ticket belongs to the user and is currently Confirmed
        $check_query = "SELECT id, train_id, num_passengers, status FROM reservations WHERE id = ? AND user_id = ? AND status = 'Confirmed'";
        $stmt_check = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt_check, "ii", $cancel_id, $user_id);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Update Reservation Status to Cancelled
            $update_res = "UPDATE reservations SET status = 'Cancelled' WHERE id = ?";
            $stmt_upd = mysqli_prepare($conn, $update_res);
            mysqli_stmt_bind_param($stmt_upd, "i", $cancel_id);
            mysqli_stmt_execute($stmt_upd);
            
            // Return seats to the train's available pool
            $restore_seats = "UPDATE trains SET available_seats = available_seats + ? WHERE id = ?";
            $stmt_seats = mysqli_prepare($conn, $restore_seats);
            mysqli_stmt_bind_param($stmt_seats, "ii", $row['num_passengers'], $row['train_id']);
            mysqli_stmt_execute($stmt_seats);

            // Update Payments table status to Refunded
            $update_pay = "UPDATE payments SET status = 'Refunded' WHERE reservation_id = ?";
            $stmt_pay = mysqli_prepare($conn, $update_pay);
            mysqli_stmt_bind_param($stmt_pay, "i", $cancel_id);
            mysqli_stmt_execute($stmt_pay);

            $success_msg = "Ticket successfully cancelled. Your refund will be processed in 3-5 business days.";
        } else {
            $error_msg = "Invalid cancellation request or ticket already cancelled.";
        }
    } catch (Exception $e) {
        $error_msg = "Database Error: " . $e->getMessage();
    }
}

// Fetch all reservations for this user
$tickets = [];
try {
    $query = "
        SELECT r.*, t.train_name, t.train_number, t.source_station, t.destination_station, t.departure_time, t.arrival_time 
        FROM reservations r
        JOIN trains t ON r.train_id = t.id
        WHERE r.user_id = ?
        ORDER BY r.travel_date DESC, r.id DESC
    ";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        $tickets = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error_msg = "Failed to load tickets: " . $e->getMessage();
}

include 'user_header.php';
?>

<div class="flex relative min-h-screen font-sans">
    
    <!-- FULL PAGE BACKGROUND -->
    <div class="fixed inset-0 -z-10 pointer-events-none print:hidden">
        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover opacity-20" alt="Train Journey">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-slate-900/90 to-emerald-950/80"></div>
    </div>

    <!-- MOBILE OVERLAY -->
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden print:hidden"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed lg:relative top-0 left-0 z-50 min-h-screen w-72 bg-slate-900/40 backdrop-blur-3xl border-r border-white/5 transition-transform duration-300 overflow-y-auto -translate-x-full lg:translate-x-0 shrink-0 print:hidden">
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
            $current_page = 'tickets.php';
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
        
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-600/10 rounded-full blur-[100px] pointer-events-none print:hidden"></div>

        <!-- Page Header (Hidden on Print) -->
        <div class="mb-8 relative z-10 no-print">
            <p class="text-emerald-400 text-sm font-semibold tracking-wider uppercase mb-1">Your Itinerary</p>
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight flex items-center gap-3">
                My Tickets 🎫
            </h1>
        </div>

        <!-- NOTIFICATIONS (Hidden on Print) -->
        <?php if(!empty($success_msg)): ?>
            <div class="no-print bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-6 py-4 rounded-2xl mb-8 text-sm relative z-10 flex items-center gap-3 shadow-[0_0_20px_rgba(16,185,129,0.1)]">
                <span class="text-xl">✅</span>
                <span><?= $success_msg ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_msg)): ?>
            <div class="no-print bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl mb-8 text-sm relative z-10 flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span><?= $error_msg ?></span>
            </div>
        <?php endif; ?>

        <!-- TICKETS LIST -->
        <div class="relative z-10 space-y-6 pb-12">
            <?php if (count($tickets) > 0): ?>
                <?php foreach ($tickets as $ticket): 
                    $is_cancelled = ($ticket['status'] === 'Cancelled');
                    
                    // Format Journey Times
                    $dep_time = strtotime($ticket['departure_time']);
                    $arr_time = strtotime($ticket['arrival_time']);
                    
                    // Extract Seats
                    $seats_arr = json_decode($ticket['seat_numbers'], true);
                    $seat_string = is_array($seats_arr) ? implode(', ', $seats_arr) : 'N/A';
                ?>
                    <!-- Added 'ticket-card' class to uniquely identify each ticket for printing -->
                    <div class="ticket-card bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-3xl p-6 lg:p-8 shadow-2xl relative overflow-hidden group hover:border-white/10 transition-colors">
                        
                        <!-- Watermark Status -->
                        <?php if($is_cancelled): ?>
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-[120px] font-black text-red-500/5 rotate-[-15deg] pointer-events-none select-none uppercase tracking-tighter">Cancelled</div>
                        <?php endif; ?>

                        <div class="flex flex-col xl:flex-row justify-between gap-6 relative z-10">
                            
                            <!-- Left: Train & Route Details -->
                            <div class="flex-1 w-full xl:w-auto xl:border-r border-white/5 xl:pr-8">
                                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                                    <div>
                                        <h3 class="text-2xl font-bold text-white mb-1 <?= $is_cancelled ? 'opacity-50' : '' ?>">
                                            <?= htmlspecialchars($ticket['train_name']) ?>
                                        </h3>
                                        <div class="flex items-center gap-3">
                                            <span class="bg-white/5 text-slate-300 px-2 py-0.5 rounded text-xs font-mono tracking-widest border border-white/10">
                                                TRAIN #<?= htmlspecialchars($ticket['train_number']) ?>
                                            </span>
                                            <span class="text-emerald-400 font-mono text-sm tracking-widest font-bold">
                                                PNR: <?= htmlspecialchars($ticket['pnr_number']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    <?php if($is_cancelled): ?>
                                        <span class="bg-red-500/10 text-red-400 border border-red-500/30 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Cancelled
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Confirmed
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center justify-between xl:justify-start xl:gap-12 w-full <?= $is_cancelled ? 'opacity-50' : '' ?>">
                                    <div class="text-left">
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Departure</p>
                                        <p class="text-2xl sm:text-3xl font-black text-white mb-1"><?= date('h:i A', $dep_time) ?></p>
                                        <p class="text-sm text-emerald-400 font-medium"><?= htmlspecialchars($ticket['source_station']) ?></p>
                                    </div>

                                    <div class="flex-1 max-w-[150px] flex flex-col items-center justify-center px-4">
                                        <p class="text-[10px] text-slate-400 mb-2 font-semibold tracking-widest uppercase text-center border border-slate-700 bg-slate-900 px-2 py-0.5 rounded-full">
                                            <?= date('d M Y', strtotime($ticket['travel_date'])) ?>
                                        </p>
                                        <div class="w-full h-[2px] bg-slate-700 relative">
                                            <div class="absolute -top-1 left-0 w-2.5 h-2.5 rounded-full bg-emerald-500 <?= $is_cancelled ? 'bg-slate-500' : '' ?>"></div>
                                            <div class="absolute -top-1 right-0 w-2.5 h-2.5 rounded-full bg-slate-500"></div>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-1">Arrival</p>
                                        <p class="text-2xl sm:text-3xl font-black text-white mb-1"><?= date('h:i A', $arr_time) ?></p>
                                        <p class="text-sm text-emerald-400 font-medium"><?= htmlspecialchars($ticket['destination_station']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Passenger & Action Details -->
                            <div class="w-full xl:w-[280px] shrink-0 flex flex-col h-full <?= $is_cancelled ? 'opacity-50 pointer-events-none' : '' ?>">
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-slate-950/50 border border-white/5 rounded-xl p-3">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Passengers</p>
                                        <p class="text-white font-bold text-lg"><?= $ticket['num_passengers'] ?></p>
                                    </div>
                                    <div class="bg-slate-950/50 border border-white/5 rounded-xl p-3">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Fare</p>
                                        <p class="text-emerald-400 font-bold text-lg">₹<?= number_format($ticket['total_fare'], 0) ?></p>
                                    </div>
                                    <div class="col-span-2 bg-slate-950/50 border border-white/5 rounded-xl p-3">
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Assigned Seats</p>
                                        <p class="text-white font-mono font-medium text-sm break-words"><?= htmlspecialchars($seat_string) ?></p>
                                    </div>
                                </div>

                                <div class="mt-auto flex items-center gap-3">
                                    <!-- Changed onclick to trigger our smart print function -->
                                    <button class="flex-1 bg-white hover:bg-slate-200 text-slate-950 text-sm font-bold py-3 rounded-xl transition-all shadow-lg flex items-center justify-center gap-2" onclick="printSingleTicket(this)">
                                        📥 Save Ticket
                                    </button>
                                    
                                    <?php if(!$is_cancelled): ?>
                                    <form action="tickets.php" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to cancel this ticket? This action cannot be undone and a cancellation fee may apply.');">
                                        <input type="hidden" name="cancel_ticket_id" value="<?= $ticket['id'] ?>">
                                        <button type="submit" class="w-12 h-12 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 rounded-xl transition-all flex items-center justify-center tooltip" title="Cancel Ticket">
                                            ✖
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- EMPTY STATE (Hidden on Print) -->
                <div class="no-print bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-16 text-center mt-10">
                    <div class="text-6xl mb-6 opacity-80">🎟️</div>
                    <h3 class="text-2xl font-bold text-white mb-2">No Tickets Found</h3>
                    <p class="text-slate-400 text-sm max-w-md mx-auto mb-8">You haven't booked any journeys yet. When you do, your confirmed tickets will appear right here.</p>
                    <a href="search_trains.php" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold py-3 px-8 rounded-xl shadow-lg shadow-emerald-500/25 transition-all inline-block">
                        Book Your First Trip
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

    // Smart Print Function: Isolates a single ticket for printing
    function printSingleTicket(btnElement) {
        // Find the specific ticket container we want to print
        const targetTicket = btnElement.closest('.ticket-card');
        
        // Find ALL tickets on the page
        const allTickets = document.querySelectorAll('.ticket-card');
        
        // Hide every ticket EXCEPT the one we clicked on
        allTickets.forEach(ticket => {
            if (ticket !== targetTicket) {
                ticket.classList.add('hidden-during-print');
            }
        });

        // Trigger the browser's print dialog
        window.print();

        // Once the print dialog closes, restore all tickets so the page works normally again
        allTickets.forEach(ticket => {
            ticket.classList.remove('hidden-during-print');
        });
    }
</script>

<!-- High-Quality Print Stylesheet -->
<style>
    @media print {
        /* Hide Layout Elements */
        #sidebar, header, #sidebarOverlay, form, .tooltip, button, .no-print { 
            display: none !important; 
        }
        
        /* The class applied by JS to hide other tickets */
        .hidden-during-print { 
            display: none !important; 
        }

        /* Reset body and main layout to fit standard paper */
        body, html {
            background-color: white !important;
            color: black !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        main { 
            padding: 20px !important; 
            margin: 0 !important; 
            width: 100% !important; 
            background: transparent !important;
        }

        /* Force background colors to show for badges/borders (fixes issues in Chrome/Safari) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Style the Ticket specifically for white paper to save black ink */
        .ticket-card {
            background-color: white !important;
            border: 2px solid #ccc !important;
            box-shadow: none !important;
            border-radius: 16px !important;
            page-break-inside: avoid;
            margin: 0 !important;
        }

        /* Convert dark-mode text colors to dark text for white paper */
        .text-white { color: #111 !important; font-weight: 700; }
        .text-slate-400, .text-slate-300 { color: #555 !important; }
        .text-slate-500 { color: #666 !important; }
        
        /* Keep Emerald accents but make them slightly darker for paper contrast */
        .text-emerald-400 { color: #059669 !important; font-weight: bold; }
        
        /* Adjust internal boxes */
        .bg-slate-950\/50, .bg-white\/5 { 
            background-color: #f8fafc !important; 
            border: 1px solid #e2e8f0 !important; 
        }
        
        /* Ensure the dotted divider is visible */
        .bg-slate-700 { background-color: #cbd5e1 !important; }
    }
</style>

<?php include '../home/footer.php'; ?>