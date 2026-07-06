<?php
include 'header.php';
?>
<?php if(isset($_SESSION['error'])){ ?>

<div id="errorAlert"
     class="fixed top-24 right-5 z-50 bg-red-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 animate-bounce">

    <span>
        <?php echo $_SESSION['error']; ?>
    </span>

    <button onclick="document.getElementById('errorAlert').remove()"
            class="text-white text-xl font-bold">

        ×

    </button>

</div>

<?php unset($_SESSION['error']); } ?>

<?php if(isset($_SESSION['success'])){ ?>

<div id="successAlert"
     class="fixed top-24 right-5 z-50 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 animate-bounce">

    <span>
        <?php echo $_SESSION['success']; ?>
    </span>

    <button onclick="document.getElementById('successAlert').remove()"
            class="text-white text-xl font-bold">

        ×

    </button>

</div>

<?php unset($_SESSION['success']); } ?>

<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-slate-950 py-16">

    <!-- ================= BACKGROUND IMAGE ================= -->

    <div class="absolute inset-0">

        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?q=80&w=1600&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-40"
             alt="Train Background">

    </div>

    <!-- ================= DARK OVERLAY ================= -->

    <div class="absolute inset-0 bg-gradient-to-br from-slate-950/95 via-blue-950/80 to-slate-950/95"></div>

    <!-- ================= GLOW EFFECTS ================= -->

    <div class="absolute top-20 left-20 w-72 h-72 bg-cyan-500/20 rounded-full blur-3xl animate-pulse"></div>

    <div class="absolute bottom-10 right-10 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-pulse"></div>

    <!-- ================= MAIN CONTENT ================= -->

    <div class="relative z-10 w-full max-w-7xl px-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">

            <!-- ================= LEFT SIDE ================= -->

            <div class="hidden lg:block animate-slideLeft">

                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-lg border border-white/10 px-5 py-2 rounded-full text-cyan-300 text-sm shadow-lg">

                    🚆 Smart Railway Registration

                </span>

                <h1 class="text-6xl font-black text-white leading-tight mt-8">

                    Join
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">

                        RailWayX

                    </span>

                </h1>

                <p class="text-slate-300 text-lg leading-8 mt-8 max-w-xl">

                    Create your account and access seamless train bookings,
                    live schedules, smart reservations, and a modern railway experience.

                </p>

                <!-- FEATURES -->

                <div class="space-y-5 mt-12">

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-cyan-500/20 border border-cyan-400/30 flex items-center justify-center text-cyan-400 text-xl">

                            🎫

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Easy Train Booking
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Book tickets within seconds
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-400 text-xl">

                            🔒

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Secure Accounts
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Fully protected authentication system
                            </p>

                        </div>

                    </div>

                    <div class="flex items-center gap-4">

                        <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400 text-xl">

                            🚄

                        </div>

                        <div>

                            <h3 class="text-white font-semibold">
                                Smart Travel Experience
                            </h3>

                            <p class="text-slate-400 text-sm">
                                Real-time train updates and schedules
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <!-- ================= REGISTER CARD ================= -->

            <div class="animate-slideUp">

                <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] overflow-hidden shadow-[0_25px_80px_rgba(0,0,0,0.5)]">

                    <!-- CARD HEADER -->

                    <div class="p-8 text-center border-b border-white/10">

                        <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-4xl shadow-2xl">

                            🚆

                        </div>

                        <h2 class="text-4xl font-black text-white mt-6">
                            Create Account
                        </h2>

                        <p class="text-slate-400 mt-3">
                            Register to continue your railway journey
                        </p>

                    </div>

                    <!-- ================= FORM ================= -->

                    <div class="p-8 lg:p-10">

                        <form action="../user/register_process.php"
                              method="POST"
                              class="space-y-6">

                            <!-- NAME -->

                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Full Name
                                </label>

                                <input type="text"
                                       name="name"
                                       placeholder="Enter your full name"
                                       required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

                            </div>

                            <!-- EMAIL -->

                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Email Address
                                </label>

                                <input type="email"
                                       name="email"
                                       placeholder="Enter your email"
                                       required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

                            </div>

                            <!-- PHONE -->

                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Phone Number
                                </label>

                                <input type="text"
                                       name="phone"
                                       placeholder="Enter phone number"
                                       required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

                            </div>

                            <!-- PASSWORD -->

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

                            </div>

                            <!-- CONFIRM PASSWORD -->

                            <div>

                                <label class="block text-slate-300 font-medium mb-3">
                                    Confirm Password
                                </label>

                                <input type="password"
                                       name="confirm_password"
                                       placeholder="Confirm password"
                                       required
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder:text-slate-500 focus:ring-4 focus:ring-cyan-400/30 outline-none transition duration-500 hover:border-cyan-400">

                            </div>

                            <!-- TERMS -->

                            <label class="flex items-center gap-3 text-slate-400 cursor-pointer">

                                <input type="checkbox"
                                       required
                                       class="w-5 h-5 accent-cyan-500">

                                I agree to the Terms & Conditions

                            </label>

                            <!-- BUTTON -->

                            <button type="submit"
                                    class="group relative w-full overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-600 hover:scale-[1.02] py-4 rounded-2xl text-white text-lg font-bold shadow-[0_15px_40px_rgba(0,0,0,0.4)] transition duration-500">

                                <span class="relative z-10">
                                    Create Account
                                </span>

                                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-500 opacity-0 group-hover:opacity-100 transition duration-500"></div>

                            </button>

                            <!-- LOGIN -->

                            <p class="text-center text-slate-400">

                                Already have an account?

                                <a href="login.php"
                                   class="text-cyan-400 hover:text-cyan-300 font-semibold transition">

                                    Login Here

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

    .animate-slideUp {

        animation: slideUp 1s ease forwards;
    }

    .animate-slideLeft {

        animation: slideLeft 1.2s ease forwards;
    }

</style>
<script>

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
include '../home/footer.php';
?>