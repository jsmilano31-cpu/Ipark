<?php
// Fetch unread message count
try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM messages 
        WHERE is_from_user = TRUE AND is_read = FALSE
    ");
    $stmt->execute();
    $unreadMessages = $stmt->fetch()['unread_count'];
} catch(PDOException $e) {
    $unreadMessages = 0;
}
?>

<!-- Add this in the navigation menu -->
<li class="nav-item">
    <a href="messages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
        <span class="nav-icon">💬</span>
        Messages
        <?php if($unreadMessages > 0): ?>
            <span class="nav-badge"><?php echo $unreadMessages; ?></span>
        <?php endif; ?>
    </a>
</li>

<!-- Add these styles -->
<style>
    .nav-badge {
        display: inline-block;
        background: var(--primary);
        color: var(--light);
        padding: 0.25rem 0.5rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .nav-link.active .nav-badge {
        background: var(--light);
        color: var(--primary);
    }
</style> 