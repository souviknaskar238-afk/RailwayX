<?php
session_start();
include 'header.php';
?>

<!-- ================= ALERT MESSAGE ================= -->

<?php if(isset($_SESSION['error'])): ?>

<div id="alertBox"
     class="fixed top-8 right-8 z-[9999] animate-slideIn">

    <div class="bg-red-500/10 backdrop-blur-xl border border-red-500/30 shadow-[0_15px_40px_rgba(0,0,0,0.4)] rounded-2xl overflow-hidden min-w-[350px]">

        <div class="flex items-start gap-4 p-5">

            <!-- Icon -->
            <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center text-2xl text-red-400 shrink-0">

                ❌

            </div>

            <!-- Content -->
            <div class="flex-1">

                <h3 class="text-white font-bold text-lg">
                    Login Failed
                </h3>

                <p class="text-slate-300 mt-1 text-sm leading-6">

                    <?php
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>

                </p>

            </div>

            <!-- Close Button -->
            <button onclick="closeAlert()"
                    class="text-slate-400 hover:text-white transition text-xl">

                ✖

            </button>

        </div>

        <!-- Bottom Line -->
        <div class="h-1 bg-gradient-to-r from-red-500 to-pink-500"></div>

    </div>

</div>

<?php endif; ?>

<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-slate-950">

    <!-- Background Image -->
    <div class="absolute inset-0">

        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1600&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-40"
             alt="Train Background">

    </div>

    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-blue-950/80 to-slate-950/95"></div>

    <!-- Animated Glow -->
    <div class="absolute top-20 left-20 w-72 h-72 bg-cyan-500/20 rounded-full blur-3xl animate-pulse"></div>

    <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-pulse"></div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-6xl px-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <!-- LEFT CONTENT -->
            <div class="hidden lg:block animate-slideLeft">

                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-lg border border-white/10 px-5 py-2 rounded-full text-cyan-300 text-sm shadow-lg">

                    🚆 Smart Railway Access Portal

                </span>

                <h1 class="text-6xl font-black text-white leading-tight mt-8">

                    Welcome To
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">

                        RailWayX

                    </span>

                </h1>

                <p class="text-slate-300 text-lg leading-8 mt-8 max-w-xl">

                    Access your railway dashboard securely.
                    Login as a passenger to book trains or
                    as an administrator to manage the railway system.

                </p>

                <!-- Features -->
                <div class="space-y-5 mt-12">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-cyan-500/20 border border-cyan-400/30 flex items-center justify-center text-cyan-400 text-xl">

                            ⚡

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Fast Booking System
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Instant train reservations
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400 text-xl">

                            🔒

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Secure Authentication
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Protected login system
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400 text-xl">

                            🚄

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Smart Railway Management
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Real-time train handling
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- LOGIN CARD -->
            <div class="animate-slideUp">

                <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] overflow-hidden shadow-[0_25px_80px_rgba(0,0,0,0.5)]">

                    <!-- Top Header -->
                    <div class="p-8 text-center border-b border-white/10">

                        <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-4xl shadow-2xl">

                            🚆

                        </div>

                        <h2 class="text-4xl font-black text-white mt-6">
                            Login
                        </h2>

                        <p class="text-slate-400 mt-3">
                            Continue to your dashboard
                        </p>

                    </div>

                    <!-- Form -->
                    <div class="p-8 lg:p-10">

                        <form action="authenticate.php" method="POST" class="space-y-7">

                            <!-- Role Selection -->
                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Login As
                                </label>

                                <div class="grid grid-cols-2 gap-4">

                                    <label class="cursor-pointer">

                                        <input type="radio"
                                               name="role"
                                               value="user"
                                               checked
                                               class="hidden peer">

                                        <div class="bg-white/5 border border-white/10 rounded-2xl py-4 text-center text-slate-300 font-semibold transition duration-500 peer-checked:bg-cyan-500 peer-checked:text-white peer-checked:border-cyan-400 hover:bg-white/10">

                                            👤 User

                                        </div>

                                    </label>

                                    <label class="cursor-pointer">

                                        <input type="radio"
                                               name="role"
                                               value="admin"
                                               class="hidden peer">

                                        <div class="bg-white/5 border border-white/10 rounded-2xl py-4 text-center text-slate-300 font-semibold transition duration-500 peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-400 hover:bg-white/10">

                                            🛠 Admin

                                        </div>

                                    </label>

                                </div>

                            </div>

                            <!-- Email -->
                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Email Address
                                </label>

                                <input type="email"
                                       name="email"
                                       placeholder="Enter your email"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

                            </div>

                            <!-- Password -->
                           <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Password
                                </label>

                                <div class="relative">

    <input type="password"
           id="password"
           name="password"
           placeholder="Enter your password"
           class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 pr-14 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

    <!-- Eye Button -->

    <button type="button"
            onclick="togglePassword()"
            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-cyan-400 transition">

        👁

    </button>

</div>

                            <!-- Remember -->
                            <div class="flex items-center justify-between">

                                <label class="flex items-center gap-3 text-slate-400 cursor-pointer">

                                    <input type="checkbox"
                                           class="w-5 h-5 accent-cyan-500">

                                    Remember Me

                                </label>

                                <a href="#"
                                   class="text-cyan-400 hover:text-cyan-300 transition">

                                    Forgot Password?

                                </a>

                            </div>

                            <!-- Button -->
                            <button type="submit"
                                    class="group relative w-full overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-600 hover:scale-[1.02] py-4 rounded-2xl text-white text-lg font-bold shadow-[0_15px_40px_rgba(0,0,0,0.4)] transition duration-500">

                                <span class="relative z-10">
                                    Login Now
                                </span>

                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-500 opacity-0 group-hover:opacity-100 transition duration-500"></div>

                            </button>

                            <!-- Register -->
                            <p class="text-center text-slate-400">

                                Don't have an account?

                                <a href="register.php"
                                   class="text-cyan-400 hover:text-cyan-300 font-semibold transition">

                                    Create Account

                                </a>

                            </p>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ================= ANIMATIONS ================= -->

<style>

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

    @keyframes slideLeft {

        from {
            opacity: 0;
            transform: translateX(-80px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideIn {

        from {
            opacity: 0;
            transform: translateX(100px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slideUp {
        animation: slideUp 1s ease forwards;
    }

    .animate-slideLeft {
        animation: slideLeft 1.2s ease forwards;
    }

    .animate-slideIn {
        animation: slideIn 0.6s ease forwards;
    }

</style>

<!-- ================= ALERT SCRIPT ================= -->

<script>

    function closeAlert() {

        const alertBox = document.getElementById('alertBox');

        if(alertBox){

            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateX(100px)';

            setTimeout(() => {

                alertBox.remove();

            }, 400);
        }
    }

    // Auto close after 5 seconds
    setTimeout(() => {

        closeAlert();

    }, 5000);


function togglePassword() {

    const password = document.getElementById('password');

    if(password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }
}



</script>

<?php
include 'footer.php';
?>