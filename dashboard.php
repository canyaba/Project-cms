<?php
require_once __DIR__ . '/includes/auth.php';

requireAuth();

include 'includes/header.php';
?>
<main class="catalog-page">
    <section class="catalog-shell">
        <div class="equipment-toolbar">
            <div>
                <p class="catalog-eyebrow mb-2">Admin overview</p>
                <h1 class="h3 m-0">Dashboard</h1>
            </div>
            <p class="text-muted mb-0">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>.</p>
        </div>

        <div class="dashboard-grid">
            <a class="card dashboard-card text-decoration-none" href="equipment.php">
                <div class="card-body">
                    <h2 class="h5 mb-2">Manage Equipment</h2>
                    <p class="text-muted mb-0">Review and update the product catalog.</p>
                </div>
            </a>
            <a class="card dashboard-card text-decoration-none" href="insert_equipment.php">
                <div class="card-body">
                    <h2 class="h5 mb-2">Add Equipment</h2>
                    <p class="text-muted mb-0">Create a new item with pricing and imagery.</p>
                </div>
            </a>
            <a class="card dashboard-card text-decoration-none" href="categories.php">
                <div class="card-body">
                    <h2 class="h5 mb-2">Manage Categories</h2>
                    <p class="text-muted mb-0">Organize products for browsing and filtering.</p>
                </div>
            </a>
            <a class="card dashboard-card text-decoration-none" href="comments_moderation.php">
                <div class="card-body">
                    <h2 class="h5 mb-2">Moderate Comments</h2>
                    <p class="text-muted mb-0">Review visitor feedback and remove abuse.</p>
                </div>
            </a>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
