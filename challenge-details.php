<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");

    $user = new USER();
    $userHeader = new HEADER("challenges");

    $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Fetch single event details
    $eventQuery = "SELECT e.*, c.name as cat_name 
                   FROM braveheart_events e 
                   LEFT JOIN braveheart_categories c ON e.category_id = c.id 
                   WHERE e.id = :id AND e.status = 1";
    $stmt = $user->getConnection()->prepare($eventQuery);
    $stmt->execute(['id' => $eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        header("Location: challenges.php");
        exit;
    }
    // Fetch winners for this specific event
     $winnerQuery = "SELECT * FROM braveheart_winners WHERE event_id = :id ORDER BY id ASC";
     $stmtWin = $user->getConnection()->prepare($winnerQuery);
     $stmtWin->execute(['id' => $eventId]);
     $winners = $stmtWin->fetchAll();

    $today = date('Y-m-d');
    $isUpcoming = ($event['deadline_date'] >= $today);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader() ?>
    <style>
        .detail-banner { width: 100%; border-radius: 8px; margin-bottom: 20px; }
        .winner-box { border: 1px solid #ddd; height: 200px; display: flex; align-items: flex-end; justify-content: center; padding: 10px; background: #f9f9f9; }
        .disabled-btn { pointer-events: none; opacity: 0.5; cursor: not-allowed; }
        .section-title { color: #d35400; font-weight: 800; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 30px; }
    </style>
</head>
<body>
    <?php echo $userHeader->printUserNav(); ?>

    <div class="container" style="margin-top: 100px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <img src="./img/braveheart/<?php echo $event['main_image']; ?>" class="detail-banner">
                
                <span class="text-warning font-weight-bold"><i class="fa fa-tag"></i> <?php echo $event['cat_name']; ?></span>
                <h2 class="font-weight-bold" style="color: #d35400;"><?php echo $event['title']; ?></h2>

                <div class="mt-4">
                    <?php echo nl2br($event['description']); ?>
                </div>

                <div class="mt-5 p-3 border rounded bg-light">
                    <h5>Application & Details</h5>
                    <?php if($event['application_file']): ?>
                        <?php if($isUpcoming): ?>
                            <a href="img/braveheart/<?php echo $event['application_file']; ?>" class="btn btn-danger" download>Download Application</a>
                        <?php else: ?>
                            <button class="btn btn-secondary disabled-btn">Application Closed</button>
                            <small class="text-muted d-block mt-2">The deadline for this challenge has passed.</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if(!$isUpcoming): ?>
    <h3 class="section-title">Winner's Details</h3>
    <div class="row mt-4">
        <?php if(!empty($winners)): ?>
            <?php foreach($winners as $winner): ?>
                <div class="col-md-4 mb-3">
                    <div class="card text-center shadow-sm">
                        <img src="./img/braveheart/<?php echo $winner['image']; ?>" 
                             class="card-img-top" 
                             alt="Winner" 
                             style="height: 200px; object-fit: cover;"
                             onerror="this.src='./img/placeholder-winner.jpg';">
                        <div class="card-body p-2">
                            <h6 class="font-weight-bold mb-0" style="font-size: 12px;"><?php echo htmlspecialchars($winner['title']); ?></h6>
                            <!-- <small class="text-muted"><?php echo htmlspecialchars($winner['position'] ?? 'Winner'); ?></small> -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-muted italic">Winners will be announced soon!</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
            </div>
        </div>
    </div>

    <?php echo $userHeader->printUserFooter(); ?>
</body>
</html>