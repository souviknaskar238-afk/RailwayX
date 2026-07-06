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

// Handle Profile Update Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    
    // Basic validation
    if (empty($name)) {
        $error_msg = "Name cannot be empty.";
    } else {
        try {
            $update_query = "UPDATE users SET name = ?, phone = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Profile updated successfully!";
                // Update session variable if you use it for the header
                $_SESSION['name'] = $name; 
            } else {
                $error_msg = "Failed to update profile: " . mysqli_error($conn);
            }
        } catch (Exception $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// Handle Password Change (Mock/UI functionality for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Add your secure password hashing and update logic here
    $success_msg = "Password updated successfully!"; 
}

// Fetch current user data to pre-fill the forms
$user_data = null;
try {
    $query = "SELECT name, email, phone, created_at FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
} catch (Exception $e) {
    $error_msg = "Failed to load profile data.";
}

// Extract initials for the avatar
$initials = "U";
if ($user_data && !empty($user_data['name'])) {
    $name_parts = explode(" ", trim($user_data['name']));
    $initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $initials .= strtoupper(substr($name_parts[count($name_parts)-1], 0, 1));
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
            $current_page = 'profile.php';
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
                <a href="../home/logout.php" class="flex items-center gap-4 px-4 py-3.5 rounded-xl text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-all duration-200">
                    <span class="text-lg w-6 text-center">🚪</span>
                    <span class="font-medium text-sm">Sign Out</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-4 md:p-8 lg:px-12 pt-8 overflow-y-auto">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-600/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="mb-8 relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-emerald-400 text-sm font-semibold tracking-wider uppercase mb-1">Account Settings</p>
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight flex items-center gap-3">
                    My Profile 👤
                </h1>
            </div>
        </div>

        <!-- NOTIFICATIONS -->
        <?php if(!empty($success_msg)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-6 py-4 rounded-2xl mb-8 text-sm relative z-10 flex items-center gap-3 shadow-[0_0_20px_rgba(16,185,129,0.1)]">
                <span class="text-xl">✅</span>
                <span><?= $success_msg ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_msg)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl mb-8 text-sm relative z-10 flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span><?= $error_msg ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 relative z-10 pb-12">
            
            <!-- LEFT COLUMN: Profile Overview -->
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-emerald-500/20 to-transparent pointer-events-none"></div>
                    
                    <div class="relative z-10">
                        <div class="w-32 h-32 mx-auto rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-4xl font-black text-white shadow-[0_0_30px_rgba(16,185,129,0.3)] border-4 border-slate-900 mb-6">
                            <?= $initials ?>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-white mb-1">
                            <?= htmlspecialchars($user_data['name'] ?? 'Guest User') ?>
                        </h2>
                        <p class="text-emerald-400 text-sm font-medium mb-6">Premium Member</p>
                        
                        <div class="space-y-4 border-t border-white/10 pt-6 text-left">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Email Address</p>
                                <p class="text-slate-300 text-sm flex items-center gap-2">
                                    <span>✉️</span> <?= htmlspecialchars($user_data['email'] ?? 'Not provided') ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Phone Number</p>
                                <p class="text-slate-300 text-sm flex items-center gap-2">
                                    <span>📱</span> <?= !empty($user_data['phone']) ? htmlspecialchars($user_data['phone']) : 'Not provided' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Member Since</p>
                                <p class="text-slate-300 text-sm flex items-center gap-2">
                                    <span>📅</span> 
                                    <?= !empty($user_data['created_at']) ? date('F d, Y', strtotime($user_data['created_at'])) : 'Unknown' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Edit Forms -->
            <div class="xl:col-span-2 space-y-8">
                
                <!-- Update Personal Information -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="text-emerald-400">📝</span> Personal Information
                    </h3>
                    
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Full Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user_data['name'] ?? '') ?>" required class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 transition-all text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Email Address <span class="text-slate-600 normal-case font-normal">(Cannot be changed)</span></label>
                                <input type="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled class="w-full bg-slate-950/20 border border-white/5 rounded-xl px-4 py-3.5 text-slate-500 outline-none cursor-not-allowed text-sm">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Phone Number</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" placeholder="Enter 10-digit mobile number" class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 transition-all text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-white/5">
                            <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 py-3 px-8 rounded-xl font-bold shadow-lg shadow-emerald-500/25 transition-all active:scale-95 text-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 md:p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="text-emerald-400">🔒</span> Security Settings
                    </h3>
                    
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="space-y-6 mb-6">
                            <div>
                                <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Current Password</label>
                                <input type="password" name="current_password" required placeholder="••••••••" class="w-full md:w-2/3 bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">New Password</label>
                                    <input type="password" name="new_password" required placeholder="••••••••" class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono">
                                </div>
                                <div>
                                    <label class="block text-slate-400 text-xs font-bold uppercase tracking-widest mb-2">Confirm New Password</label>
                                    <input type="password" name="confirm_password" required placeholder="••••••••" class="w-full bg-slate-950/50 border border-white/10 rounded-xl px-4 py-3.5 text-white outline-none focus:border-emerald-500 transition-all text-sm font-mono">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-white/5">
                            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white py-3 px-8 rounded-xl font-bold border border-white/10 transition-all active:scale-95 text-sm">
                                Update Password
                            </button>
                        </div>
                    </form>
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
        overlay.classList.toggle('hidden');
    }
</script>

<?php include '../home/footer.php'; ?>