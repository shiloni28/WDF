
<?php
$role = $_SESSION["role"] ?? "";

$stmt = $conn->prepare(
    "SELECT title, url FROM menu_items
     WHERE allowed_role = ? ORDER BY id"
);
$stmt->bind_param("s", $role);
$stmt->execute();
$menuItems = $stmt->get_result();
?>

<aside class="sidebar">
    <h4>StudentHub</h4>
    <p class="sidebar-subtitle">
        <?= htmlspecialchars(ucfirst($role)) ?> Panel
    </p>

    <?php while ($item = $menuItems->fetch_assoc()): ?>
        <a class="menu-link"
           href="<?= htmlspecialchars($item["url"]) ?>">
            <?= htmlspecialchars($item["title"]) ?>
        </a>
    <?php endwhile; ?>

    <a class="menu-link logout-link" href="logout.php">Logout</a>
</aside>
