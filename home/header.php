<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RailWayX</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- ================= HEADER START ================= -->

<header class="bg-gray-800 shadow-md fixed top-0 left-0 w-full z-50">

    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="flex items-center justify-between h-20">

            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3">

            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center text-white text-3xl shadow-xl">

                    🚆

                </div>

                <div>
                    <h1 class="text-2xl font-extrabold text-gray-300 tracking-wide">
                        RailWayX
                    </h1>

                 
                </div>

            </a>


            <!-- Buttons -->
            <div class="hidden md:flex items-center gap-4">

                <a href="login.php"
                   class="px-5 py-2 rounded-lg border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white transition duration-300 font-medium">
                    Login
                </a>

                <a href="register.php"
                   class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition duration-300 font-medium shadow-md">
                    Register
                </a>

            </div>

            <!-- Mobile Menu Button -->
            <button id="menuBtn" class="md:hidden text-gray-700">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-8 w-8"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>

                </svg>

            </button>

        </div>

    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu"
         class="hidden md:hidden bg-white border-t border-gray-200">

        <div class="flex flex-col px-6 py-4 space-y-4">

            <a href="index.php" class="text-gray-700 hover:text-blue-600">
                Home
            </a>

            <a href="#" class="text-gray-700 hover:text-blue-600">
                Search Trains
            </a>

            <a href="#" class="text-gray-700 hover:text-blue-600">
                My Bookings
            </a>

            <a href="#" class="text-gray-700 hover:text-blue-600">
                Contact
            </a>

            <a href="login.php"
               class="w-full text-center px-4 py-2 rounded-lg border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white transition">
                Login
            </a>

            <a href="register.php"
               class="w-full text-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                Register
            </a>

        </div>

    </div>

</header>

<!-- HEADER SPACING -->
<div class="h-20"></div>

<!-- ================= HEADER END ================= -->