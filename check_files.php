<?php
echo "<h2>File Checker</h2>";
echo "<pre>";

$folders = ['admin', 'candidate', 'voter', 'auth', 'config', 'assets/css', 'assets/js'];
$files = [
    'admin/dashboard.php',
    'admin/manage_users.php',
    'admin/manage_candidates.php',
    'admin/results.php',
    'admin/settings.php',
    'admin/approve_candidate.php',
    'admin/remove_user.php',
    'candidate/dashboard.php',
    'candidate/profile.php',
    'candidate/campaign.php',
    'voter/dashboard.php',
    'voter/vote.php',
    'voter/cast_vote.php',
    'voter/results.php',
    'auth/login.php',
    'auth/register_voter.php',
    'auth/register_candidate.php',
    'config/db.php',
    'assets/css/style.css',
    'assets/js/animation.js',
    'index.php',
    'logout.php'
];

foreach ($files as $file ){
    if (file_exists($file)) {
        echo "✅ $file - EXISTS\n";
    } else {
        echo "❌ $file - MISSING\n";
    }
}
echo "</pre>";
?>
nehemia