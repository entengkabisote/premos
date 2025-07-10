<?php
    include 'session_config.php';
    require 'db_connect.php';
    require_once 'functions.php';
    include 'header.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$fullname = $_SESSION['fullname'];
$avatar = $_SESSION['profile_picture'] ?? 'images/default_profile.png';

// Defaults
$companyLogo = 'images/default_client.png';
$smsLogo = 'images/default_sms.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - PREMOS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/header.css">
    <link rel="stylesheet" href="styles/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="styles/toastr_custom.css">
    <style>
        .fade-toggle {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        .fade-toggle.show {
            display: block;
            opacity: 1;
        }
    </style>

</head>
<body>

    <div class="container py-4">
        <h2 class="dashboard-title">Welcome, <?php echo htmlspecialchars($fullname); ?>!</h2>
        <div class="row g-4">
            <?php
            $cards = [];
            if ($role === 'SuperAdmin') {
                $cards = [
                    ['📋 Dashboard', 'View all enrolled vessels and their status.', 'dashboard_overview.php', 'btn-outline-primary'],
                    // ['👥 Manage All Users', 'Create, edit, or disable any account across the system.', 'user_management.php', 'btn-outline-primary'],
                    ['🧰 Equipments', 'Manage equipment used onboard.', 'equipment.php', 'btn-outline-primary'],
                    ['⚙️ Ancillary', 'Ancillary machinery listing and control.', 'ancillary.php', 'btn-outline-secondary'],
                    ['🛠️ Machinery', 'Main machinery records and controls.', 'machinery.php', 'btn-outline-secondary'],
                    ['⚙️ Settings', 'Master data setup, users, and vessel panel settings.', '#', 'btn-outline-dark']
                    // ['🏢 Manage Companies', 'Handle client company registration and assignments.', 'clients.php', 'btn-outline-secondary'],
                    // ['⚙️ System Settings', 'Edit SMTP, security, and global configuration.', '#', 'btn-outline-dark'],
                    // ['🚢 Manage Vessels', 'Add, update, and assign vessels to companies.', 'vessels.php', 'btn-outline-success'],
                    // ['📚 Vessel Item Library', 'Manage the list of all equipment and machinery used on vessels.', 'vessel_item_library.php', 'btn-outline-warning'],
                    // ['🛠️ Maintenance Library', 'Define inspections and maintenance tasks to assign later.', 'maintenance_library.php', 'btn-outline-secondary'],
                    // ['📦 Assign Equipment', 'Link components and tasks to specific vessels.', 'equipment_assignment.php', 'btn-outline-info'],
                    // ['📊 View Logs', 'Track user logins, changes, and system actions.', 'logs.php', 'btn-outline-info'],
                    // ['📚 System Library', 'Manage categories, inspections, and maintenance definitions.', 'maintenance_library.php', 'btn-outline-primary']

                ];
            } elseif ($role === 'Admin') {
                $cards = [
                    ['📋 Dashboard', 'View all enrolled vessels and their status.', 'dashboard_overview.php', 'btn-outline-primary'],
                    ['🧰 Equipments', 'Manage equipment used onboard.', 'equipment.php', 'btn-outline-primary'],
                    ['⚙️ Ancillary', 'Ancillary machinery listing and control.', 'ancillary.php', 'btn-outline-secondary'],
                    ['🛠️ Machinery', 'Main machinery records and controls.', 'machinery.php', 'btn-outline-secondary'],
                    ['⚙️ Settings', 'Master data setup, users, and vessel panel settings.', '#', 'btn-outline-dark']
                ];
            } elseif ($role === 'SuperUser') {
                $cards = [
                    ['📋 Dashboard', 'View all enrolled vessels and their status.', 'dashboard_overview.php', 'btn-outline-primary'],
                    ['🧰 Equipments', 'Manage equipment used onboard.', 'equipment.php', 'btn-outline-primary'],
                    ['⚙️ Ancillary', 'Ancillary machinery listing and control.', 'ancillary.php', 'btn-outline-secondary'],
                    ['🛠️ Machinery', 'Main machinery records and controls.', 'machinery.php', 'btn-outline-secondary']
                ];
            } elseif ($role === 'User') {
                $cards = [
                    ['🏠 Dashboard', 'Go to your dashboard home.', 'index.php', 'btn-outline-primary'],
                    ['📤 Upload CSV', 'Upload data in CSV format.', 'import_csv.php', 'btn-outline-secondary'],
                    ['🚢 Vessel Operations', 'Access vessel operations panel.', 'vessel.php', 'btn-outline-success'],
                    ['🚪 Logout', 'Logout from the system.', 'logout.php', 'btn-outline-danger']
                ];
            }


            foreach ($cards as $card) {
                $title = $card[0];
                $desc = $card[1];
                $link = $card[2];
                $btnClass = $card[3];

                if ($title === '⚙️ Settings') {
                    echo "<div class='col-md-4 settings-card'><div class='card shadow-sm'><div class='card-body'>";
                    echo "<h5 class='card-title'>{$title}</h5><p class='card-text'>{$desc}</p>";
                    echo "<button class='btn {$btnClass} w-100' onclick='toggleSettingsSubmenu()'>View Settings</button>";
                    echo "</div></div></div>";

                    echo "<div class='col-12 mt-3 fade-toggle' id='settings-submenu'>";
                    echo "<div class='card border-secondary shadow-sm'>";
                    echo "<div class='card-header bg-dark text-white fw-bold'>Settings Options</div>";
                    echo "<div class='card-body'>";
                    echo "<div class='row g-4'>";

                    $submenu = [];

                    // Submenu for SuperAdmin
                    if ($role === 'SuperAdmin') {
                        $submenu = [
                            ['🔧 Master Data Setup', 'master_data.php', 'btn-outline-primary'],
                            ['👤 User Management', 'user_management.php', 'btn-outline-secondary'],
                            ['🚢 Vessel Operations Panel', 'vessel.php', 'btn-outline-success']
                        ];
                    }
                    // Submenu for Admin
                    elseif ($role === 'Admin') {
                        $submenu = [
                            ['🔧 Master Data Setup', 'master_data.php', 'btn-outline-primary'],
                            ['🚢 Vessel Operations Panel', 'vessel.php', 'btn-outline-success']
                        ];
                    }

                    foreach ($submenu as $sub) {
                        echo "<div class='col-md-4'><div class='card shadow-sm'><div class='card-body'>";
                        echo "<h6 class='card-title'>{$sub[0]}</h6>";
                        echo "<a href='{$sub[1]}' class='btn {$sub[2]} w-100'>Open</a>";
                        echo "</div></div></div>";
                    }
                    echo "</div>"; // end row
                    echo "</div>"; // end card-body
                    echo "</div>"; // end card
                    echo "</div>"; // end col-12

                } else {
                    echo "<div class='col-md-4'><div class='card shadow-sm'><div class='card-body'>";
                    echo "<h5 class='card-title'>{$title}</h5><p class='card-text'>{$desc}</p>";
                    echo "<a href='{$link}' class='btn {$btnClass} w-100'>Go</a>";
                    echo "</div></div></div>";
                }
            }

            // Add Logout card to all roles
            // $cards[] = ['🚪 Logout', 'End your session securely.', 'logout.php', 'btn-outline-danger'];

            // foreach ($cards as $card) {
            //     echo "<div class='col-md-4'><div class='card shadow-sm'><div class='card-body'>";
            //     echo "<h5 class='card-title'>{$card[0]}</h5><p class='card-text'>{$card[1]}</p>";
            //     echo "<a href='{$card[2]}' class='btn {$card[3]} w-100'>Go</a>";
            //     echo "</div></div></div>";
            // }
            ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

     <!-- JS includes -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="scripts/toastr_settings.js"></script>
    <?php include 'toastr_handler.php'; ?>
    <script src="scripts/header.js" defer></script>

    <script>
        function toggleSettingsSubmenu() {
            const submenu = document.getElementById('settings-submenu');
            submenu.classList.toggle('show');
        }

    </script>


</body>
</html>
