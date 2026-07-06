<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>RailWayX Admin</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-950 overflow-x-hidden">

<!-- ================= ADMIN HEADER ================= -->

<header class="fixed top-0 left-0 w-full z-50 backdrop-blur-2xl bg-slate-950/70 border-b border-white/10">

    <div class="max-w-[1800px] mx-auto px-4 md:px-8">

        <div class="h-20 flex items-center justify-between">

            <!-- LEFT -->
            <div class="flex items-center gap-4">

                <!-- Mobile Menu -->
                <button onclick="toggleSidebar()"
                        class="lg:hidden w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-white text-2xl">

                    ☰

                </button>
                <a href="../home/index.php">

                <!-- Logo -->
                <div class="flex items-center gap-4">

                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white text-3xl shadow-2xl">

                        🚆

                    </div>

                    <div>

                        <h1 class="text-2xl font-black text-white tracking-wide">
                            RailWayX
                        </h1>

                        <p class="text-xs text-slate-400 tracking-[4px] uppercase">
                            Admin Dashboard
                        </p>

                    </div>

                </div>
            </a>

            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-4">

                

                <!-- Notification -->
                <button class="relative w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-xl text-slate-300 hover:bg-white/10 transition">

                    🔔

                    <span class="absolute top-2 right-2 w-3 h-3 bg-red-500 rounded-full"></span>

                </button>

                <!-- Admin -->
                <div class="hidden sm:flex items-center gap-4 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">

                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">

                        A

                    </div>

                    <div>

                        <h3 class="text-white font-semibold">
                            Admin
                        </h3>

                        <p class="text-slate-400 text-sm">
                            Railway Authority
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</header>

<!-- Header Space -->
<div class="h-20"></div>