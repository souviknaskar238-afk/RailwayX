# 🚆 RailWayX – Railway Management System

RailWayX is a modern Railway Management System developed using PHP, MySQL, Tailwind CSS, and JavaScript. The project provides a complete railway reservation and management platform with separate Admin and User dashboards, modern UI design, authentication system, train booking workflow, and responsive layouts.

---

# ✨ Features

## 👤 User Features

- User Registration & Login
- Search Trains
- Book Tickets
- View Tickets
- Journey History
- Payment Section
- User Profile Management
- Responsive User Dashboard
- Modern Railway-Themed UI

---

## 🛠 Admin Features

- Admin Authentication
- Manage Users
- Manage Trains
- Manage Stations
- Manage Coaches
- Manage Reservations
- Payment Monitoring
- Train Management
- Responsive Admin Dashboard

---

# 🎨 UI/UX Features

- Modern Tailwind CSS Design
- Dark Futuristic Railway Theme
- Glassmorphism Effects
- Responsive Layout
- Animated Sections
- Mobile-Friendly Dashboard
- Railway Background Images
- Collapsible Sidebar
- Smooth Hover Animations

---

# 🧰 Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend Development |
| MySQL | Database |
| Tailwind CSS | Styling |
| JavaScript | Interactivity |
| XAMPP | Local Development |

---

# 🗄 Database Tables

The project uses the following database tables:

- users
- trains
- coach_types
- coaches
- seats
- reservations
- payments
- stations

---

# 📂 Current Project Structure

```bash
rail/
│
├── admin/
│   ├── add_coach.php
│   ├── add_station.php
│   ├── add_train.php
│   ├── admin_footer.php
│   ├── admin_header.php
│   ├── admin_logout.php
│   ├── dashboard.php
│   ├── delete_station.php
│   ├── delete_user.php
│   ├── edit_coach.php
│   ├── edit_station.php
│   ├── edit_train.php
│   ├── edit_user.php
│   ├── manage_coaches.php
│   ├── manage_payments.php
│   ├── manage_reservations.php
│   ├── manage_stations.php
│   ├── manage_trains.php
│   └── manage_users.php
│
├── home/
│   ├── authenticate.php
│   ├── config.php
│   ├── footer.php
│   ├── header.php
│   ├── index.php
│   ├── login.php
│   └── register.php
│
├── user/
│   ├── book_ticket.php
│   ├── payment.php
│   ├── payments.php
│   ├── profile.php
│   ├── register_process.php
│   ├── search_trains.php
│   ├── tickets.php
│   ├── user_dashboard.php
│   ├── user_header.php
│   └── user_logout.php
│
└── README.md
