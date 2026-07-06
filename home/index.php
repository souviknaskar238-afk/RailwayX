<?php include 'header.php'; ?>

<!-- ================= HERO SECTION ================= -->

<section class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900">

    <!-- Animated Background -->
    <div class="absolute inset-0 overflow-hidden">

        <div class="absolute top-20 left-10 w-72 h-72 bg-cyan-500/20 rounded-full blur-3xl animate-pulse"></div>

        <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>

        <div class="absolute top-1/2 left-1/2 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>

    </div>

    <!-- Grid Effect -->
    <div class="absolute inset-0 opacity-10"
         style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px);
                background-size: 50px 50px;">
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-24 relative z-10">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">

            <!-- LEFT CONTENT -->
            <div class="animate-slideUp">

                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-lg border border-white/20 px-5 py-2 rounded-full text-sm text-cyan-300 shadow-lg">
                    🚆 India's Smart Railway Reservation Platform
                </span>

                <h1 class="text-5xl lg:text-7xl font-black leading-tight text-white mt-8">

                    Travel
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">
                        Faster,
                    </span>

                    Smarter &
                    Seamlessly

                </h1>

                <p class="text-lg text-slate-300 leading-8 mt-8 max-w-2xl">

                    Experience the next generation railway management
                    system with instant train search, smart booking,
                    live seat availability and secure ticket reservation.

                </p>

                <!-- Buttons -->
                <div class="flex flex-wrap gap-5 mt-10">

                    <a href="#search"
                       class="group relative overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-600 px-8 py-4 rounded-2xl font-semibold text-white shadow-2xl hover:scale-105 transition duration-500">

                        <span class="relative z-10">
                            Search Trains
                        </span>

                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-500 opacity-0 group-hover:opacity-100 transition duration-500"></div>

                    </a>

                    <a href="#"
                       class="border border-white/20 bg-white/10 backdrop-blur-lg px-8 py-4 rounded-2xl font-semibold text-white hover:bg-white hover:text-slate-900 transition duration-500">
                        Explore More
                    </a>

                </div>

                <!-- Stats -->
                <div class="flex flex-wrap gap-10 mt-14">

                    <div>
                        <h2 class="text-4xl font-bold text-white">500+</h2>
                        <p class="text-slate-400 mt-1">Daily Bookings</p>
                    </div>

                    <div>
                        <h2 class="text-4xl font-bold text-white">120+</h2>
                        <p class="text-slate-400 mt-1">Active Trains</p>
                    </div>

                    <div>
                        <h2 class="text-4xl font-bold text-white">99%</h2>
                        <p class="text-slate-400 mt-1">User Satisfaction</p>
                    </div>

                </div>

            </div>

            <!-- RIGHT IMAGE -->
            <div class="relative flex justify-center items-center">

                <!-- Glow -->
                <div class="absolute w-[500px] h-[500px] bg-cyan-500/20 rounded-full blur-3xl animate-pulse"></div>

                <!-- Train Image -->
                <img src="
                https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1200&auto=format&fit=crop"
                     alt="Modern Train"
                     class="relative z-10 w-full max-w-2xl object-cover rounded-3xl shadow-[0_25px_80px_rgba(0,0,0,0.7)] border border-white/10 animate-float hover:scale-105 transition duration-700">

            </div>

        </div>

    </div>

</section>



<!-- ================= SEARCH SECTION ================= -->

<?php
// Include the database connection (both are in the 'home' folder)
require_once 'config.php'; 

$stations = [];

// Fetch stations to populate the dynamic dropdowns
if (isset($conn)) {
    $stations_query = mysqli_query($conn, "SELECT code, name FROM stations ORDER BY name ASC");
    if ($stations_query) {
        while ($row = mysqli_fetch_assoc($stations_query)) {
            $stations[] = $row;
        }
    }
} else {
    echo "<!-- Error: Database connection not established. -->";
}
?>

<section id="search"
         class="relative py-32 overflow-hidden bg-cover bg-center bg-no-repeat animate-bg"
         style="background-image: url('https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1600&auto=format&fit=crop');">

    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-[2px]"></div>

    <!-- Gradient Glow -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-cyan-500/20 blur-3xl rounded-full"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 relative z-10">

        <!-- Heading -->
        <div class="text-center mb-20 animate-fadeIn">
            <span class="text-cyan-400 font-semibold uppercase tracking-[4px]">
                Smart Booking
            </span>
            <h2 class="text-5xl lg:text-6xl font-black text-white mt-5 leading-tight">
                Search Trains
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">
                    Instantly
                </span>
            </h2>
            <p class="text-slate-300 mt-6 text-lg max-w-2xl mx-auto leading-8">
                Check live availability, timings, routes and reserve your seats
                within seconds using RailWayX smart booking system.
            </p>
        </div>

        <!-- Search Card -->
        <div class="max-w-6xl mx-auto bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] overflow-hidden shadow-[0_25px_100px_rgba(0,0,0,0.5)] animate-cardFloat">

           <!-- Top Tabs -->
            <div class="grid grid-cols-2 border-b border-white/10">
                
                <!-- Active Tab -->
                <button type="button" class="bg-cyan-500 text-white font-semibold py-5 text-lg cursor-default">
                    🚆 Search Trains
                </button>

                <!-- Inactive Tab -->
                <button type="button" class="bg-slate-900/40 backdrop-blur-md text-slate-400 font-semibold py-5 text-lg hover:bg-white/5 hover:text-white transition duration-300">
                    🎫 PNR Status
                </button>
                
            </div>

            <!-- Form -->
            <div class="p-8 lg:p-12">

                <!-- Dynamic Datalist for Stations -->
                <datalist id="station_list">
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= htmlspecialchars($station['code']) ?>">
                            <?= htmlspecialchars($station['name']) ?> (<?= htmlspecialchars($station['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </datalist>

                <!-- 
                  Action points to the passenger portal using relative path ../user/
                  If not logged in, search_trains.php should handle the redirect to login.php 
                -->
                <form action="../user/search_trains.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

                    <!-- From -->
                    <div>
                        <label class="text-slate-300 font-medium mb-3 block" for="source">
                            From
                        </label>
                        <input type="text" 
                               id="source"
                               name="source" 
                               list="station_list"
                               required
                               autocomplete="off"
                               placeholder="Source Station"
                               class="w-full bg-white/10 border border-white/10 text-white placeholder:text-slate-400 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-cyan-400/40 outline-none transition">
                    </div>

                    <!-- To -->
                    <div>
                        <label class="text-slate-300 font-medium mb-3 block" for="destination">
                            To
                        </label>
                        <input type="text" 
                               id="destination"
                               name="destination" 
                               list="station_list"
                               required
                               autocomplete="off"
                               placeholder="Destination Station"
                               class="w-full bg-white/10 border border-white/10 text-white placeholder:text-slate-400 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-cyan-400/40 outline-none transition">
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="text-slate-300 font-medium mb-3 block" for="date">
                            Journey Date
                        </label>
                        <!-- PHP prevents selecting past dates -->
                        <input type="date" 
                               id="date"
                               name="date" 
                               required
                               min="<?= date('Y-m-d') ?>"
                               class="w-full bg-white/10 border border-white/10 text-white rounded-2xl px-5 py-4 focus:ring-4 focus:ring-cyan-400/40 outline-none transition [color-scheme:dark]">
                    </div>

                    <!-- Coach -->
                    <div>
                        <label class="text-slate-300 font-medium mb-3 block" for="class">
                            Coach Type
                        </label>
                        <select id="class" 
                                name="class" 
                                required
                                class="w-full bg-white/10 border border-white/10 text-white rounded-2xl px-5 py-4 focus:ring-4 focus:ring-cyan-400/40 outline-none transition">
                            <option value="fare_general" class="text-black">General</option>
                            <option value="fare_sleeper" class="text-black">Sleeper</option>
                            <option value="fare_ac" class="text-black">AC</option>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="md:col-span-2 lg:col-span-4 mt-8 text-center">
                        <button type="submit" 
                                class="group relative overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-600 hover:scale-105 px-14 py-5 rounded-2xl text-white text-lg font-bold shadow-[0_15px_40px_rgba(0,0,0,0.4)] transition duration-500 w-full sm:w-auto">
                            <span class="relative z-10">
                                Search Available Trains
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-500 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<!-- ================= TESTIMONIALS ================= -->

<section class="pt-28 pb-10 bg-slate-950 relative overflow-hidden">

    <!-- Glow -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 relative z-10">

        <!-- Heading -->
        <div class="text-center mb-20">

            <span class="text-cyan-400 font-semibold uppercase tracking-widest">
                Testimonials
            </span>

            <h2 class="text-5xl font-black text-white mt-4">
                Loved by Thousands
            </h2>

            <p class="text-slate-400 text-lg mt-5">
                What travelers say about RailWayX experience.
            </p>

        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">

            <!-- Card -->
            <div class="group bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-8 hover:-translate-y-3 hover:border-cyan-400 transition duration-500">

                <div class="flex items-center gap-4 mb-6">

                    <img src="https://randomuser.me/api/portraits/men/32.jpg"
                         class="w-16 h-16 rounded-full border-2 border-cyan-400">

                    <div>
                        <h4 class="text-white font-bold text-lg">
                            Rahul Sharma
                        </h4>

                        <p class="text-slate-400 text-sm">
                            Software Engineer
                        </p>
                    </div>

                </div>

                <p class="text-slate-300 leading-8">
                    “The smoothest railway booking experience I've ever used.
                    The interface feels modern and premium.”
                </p>

            </div>

            <!-- Card -->
            <div class="group bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-8 hover:-translate-y-3 hover:border-cyan-400 transition duration-500">

                <div class="flex items-center gap-4 mb-6">

                    <img src="https://randomuser.me/api/portraits/women/44.jpg"
                         class="w-16 h-16 rounded-full border-2 border-cyan-400">

                    <div>
                        <h4 class="text-white font-bold text-lg">
                            Priya Das
                        </h4>

                        <p class="text-slate-400 text-sm">
                            Student
                        </p>
                    </div>

                </div>

                <p class="text-slate-300 leading-8">
                    “Booking tickets became incredibly easy.
                    Loved the fast search and beautiful design.”
                </p>

            </div>

            <!-- Card -->
            <div class="group bg-white/5 border border-white/10 backdrop-blur-xl rounded-3xl p-8 hover:-translate-y-3 hover:border-cyan-400 transition duration-500">

                <div class="flex items-center gap-4 mb-6">

                    <img src="https://randomuser.me/api/portraits/men/67.jpg"
                         class="w-16 h-16 rounded-full border-2 border-cyan-400">

                    <div>
                        <h4 class="text-white font-bold text-lg">
                            Arjun Verma
                        </h4>

                        <p class="text-slate-400 text-sm">
                            Businessman
                        </p>
                    </div>

                </div>

                <p class="text-slate-300 leading-8">
                    “Real-time availability and smooth payment system.
                    Everything works perfectly.”
                </p>

            </div>

        </div>

    </div>

</section>

<!-- ================= CUSTOM ANIMATIONS ================= -->

<style>

    html {
        scroll-behavior: smooth;
    }

    body {
        overflow-x: hidden;
    }

    @keyframes slideUp {

        from {
            opacity: 0;
            transform: translateY(60px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {

        0% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }

        100% {
            transform: translateY(0px);
        }
    }

    @keyframes fadeIn {

        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .animate-slideUp {
        animation: slideUp 1s ease forwards;
    }

    .animate-float {
        animation: float 5s ease-in-out infinite;
    }

    .animate-fadeIn {
        animation: fadeIn 1.5s ease forwards;
    }
    @keyframes fadeUp {

    from {
        opacity: 0;
        transform: translateY(80px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cardFloat {

    0% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-12px);
    }

    100% {
        transform: translateY(0px);
    }
}

@keyframes buttonPulse {

    0% {
        box-shadow: 0 0 0 rgba(34,211,238,0.4);
    }

    50% {
        box-shadow: 0 0 40px rgba(34,211,238,0.6);
    }

    100% {
        box-shadow: 0 0 0 rgba(34,211,238,0.4);
    }
}

@keyframes bgMove {

    0% {
        background-position: center top;
    }

    50% {
        background-position: center center;
    }

    100% {
        background-position: center top;
    }
}

.animate-fadeUp {
    animation: fadeUp 1.2s ease forwards;
}

.animate-cardFloat {
    animation: cardFloat 5s ease-in-out infinite;
}

.animate-buttonPulse {
    animation: buttonPulse 2.5s infinite;
}

.animate-bg {
    animation: bgMove 20s ease infinite;
}

</style>

<?php include 'footer.php'; ?>