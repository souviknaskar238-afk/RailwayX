<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {

    header("Location: ../home/login.php");
    exit();
}

include 'admin_header.php';
?>

<div class="flex relative min-h-screen">

    <!-- ================= BACKGROUND ================= -->

    <div class="fixed inset-0 -z-10">

        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1600&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-20"
             alt="Train">

        <div class="absolute inset-0 bg-slate-750/90"></div>

    </div>

    <!-- ================= OVERLAY ================= -->

    <div id="sidebarOverlay"
         onclick="toggleSidebar()"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden">
    </div>

    <!-- ================= SIDEBAR ================= -->

    <aside id="sidebar"
           class="fixed lg:relative top-0 left-0 z-80 min-h-screen w-72 bg-slate-900/70 backdrop-blur-2xl border-r border-white/10 transition-all duration-300 overflow-y-auto -translate-x-full lg:translate-x-0">

        <!-- Sidebar Top -->
        <div class="p-6 border-b border-white/10 flex items-center justify-between">

            <h2 class="text-white text-2xl font-black">
                MENU
            </h2>

            <button onclick="toggleSidebar()"
                    class="lg:hidden text-slate-400 text-2xl">

                ✖

            </button>

        </div>

       <!-- Navigation -->
<div class="p-4 space-y-2">

    <?php

    $menus = [

        [
            "icon" => "🏠",
            "name" => "Dashboard",
            "link" => "dashboard.php"
        ],

        [
            "icon" => "👤",
            "name" => "Users",
            "link" => "manage_users.php"
        ],

        [
            "icon" => "🚆",
            "name" => "Trains",
            "link" => "manage_trains.php"
        ],

        [
            "icon" => "🚉",
            "name" => "Stations",
            "link" => "manage_stations.php"
        ],

        [
            "icon" => "🛏",
            "name" => "Coaches",
            "link" => "manage_coaches.php"
        ],


        [
            "icon" => "🎫",
            "name" => "Reservations",
            "link" => "manage_reservations.php"
        ],

        [
            "icon" => "💳",
            "name" => "Payments",
            "link" => "manage_payments.php"
        ]

     

       

    ];

    foreach($menus as $menu){
    ?>

    <a href="<?php echo $menu['link']; ?>"
       class="flex items-center gap-4 px-5 py-4 rounded-2xl text-slate-300 hover:bg-cyan-500/20 hover:text-cyan-400 transition duration-300 border border-transparent hover:border-cyan-400/20">

        <span class="text-xl">
            <?php echo $menu['icon']; ?>
        </span>

        <span class="font-medium">
            <?php echo $menu['name']; ?>
        </span>

    </a>

    <?php } ?>

    <!-- Logout -->

    <a href="admin_logout.php"
       class="flex items-center gap-4 px-5 py-4 rounded-2xl text-red-400 hover:bg-red-500/10 transition duration-300 mt-6">

        <span class="text-xl">
            🚪
        </span>

        <span class="font-medium">
            Logout
        </span>

    </a>

</div>

    </aside>

    <!-- ================= MAIN CONTENT ================= -->

    <main class="flex-1 p-4 md:p-8">

        <!-- Welcome -->
        <div class="mb-10">

            <h1 class="text-4xl md:text-5xl font-black text-white leading-tight">

                Railway Control Center 🚆

            </h1>

            <p class="text-slate-400 text-lg mt-4 max-w-2xl leading-8">

                Manage trains, reservations, stations,
                payments and monitor the entire railway
                infrastructure from one smart dashboard.

            </p>

        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-3xl p-8 shadow-2xl hover:scale-[1.02] transition duration-500">

                <p class="text-cyan-100 text-sm">
                    Total Users
                </p>

                <h2 class="text-5xl font-black text-white mt-5">
                    1,240
                </h2>

            </div>

            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition duration-500">

                <p class="text-slate-400 text-sm">
                    Trains
                </p>

                <h2 class="text-5xl font-black text-white mt-5">
                    85
                </h2>

            </div>

            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition duration-500">

                <p class="text-slate-400 text-sm">
                    Reservations
                </p>

                <h2 class="text-5xl font-black text-white mt-5">
                    3,560
                </h2>

            </div>

            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-8 hover:-translate-y-2 transition duration-500">

                <p class="text-slate-400 text-sm">
                    Revenue
                </p>

                <h2 class="text-5xl font-black text-white mt-5">
                    ₹2.5L
                </h2>

            </div>

        </div>

        <!-- Sections -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-10">

            <!-- Reservations -->
            <div class="xl:col-span-2 bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-6 overflow-x-auto">

                <div class="flex items-center justify-between mb-8">

                    <h2 class="text-2xl font-bold text-white">
                        Recent Reservations
                    </h2>

                    <a href="manage_reservations.php"><button class="bg-cyan-500 hover:bg-cyan-600 text-white px-5 py-2 rounded-xl transition">
                        View All
                    </button></a>

                </div>

                <table class="w-full min-w-[650px]">

                    <thead>

                        <tr class="border-b border-white/10">

                            <th class="text-left py-4 text-slate-400">
                                PNR
                            </th>

                            <th class="text-left py-4 text-slate-400">
                                Passenger
                            </th>

                            <th class="text-left py-4 text-slate-400">
                                Train
                            </th>

                            <th class="text-left py-4 text-slate-400">
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr class="border-b border-white/5">

                            <td class="py-5 text-white">
                                PNR982341
                            </td>

                            <td class="py-5 text-slate-300">
                                Rahul Sharma
                            </td>

                            <td class="py-5 text-slate-300">
                                Rajdhani Express
                            </td>

                            <td class="py-5">

                                <span class="bg-green-500/20 text-green-400 px-4 py-2 rounded-full text-sm">
                                    Confirmed
                                </span>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- Quick Actions -->
            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-6">

                <h2 class="text-2xl font-bold text-white mb-8">
                    Quick Actions
                </h2>

                <div class="space-y-4">

                    <a href="add_train.php"><button class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 py-4 rounded-2xl text-white font-semibold hover:scale-[1.02] transition duration-500">
                        ➕ Add Train
                    </button></a>
                    <p>       </p>

                    <a href="add_station.php"><button class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 py-4 rounded-2xl text-white font-semibold hover:scale-[1.02] transition duration-500">
                        🚉 Add Station
                    </button></a>

                

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

<?php
include '../home/footer.php';
?>