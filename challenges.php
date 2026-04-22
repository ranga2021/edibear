<?php
    session_start();
    require_once("./classes/class.user.php");
    require_once("./classes/class.header.php");
    require_once("./classes/class.widgets.php");

    $userHeader = new HEADER("challenges");
    $user = new USER();
    $widgets = new WIDGETS();

    // Pagination Logic: Show 2 events per page
    $limit = 2;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Fetch Categories for the top navigation
    $bhCategories = $user->fetchAll(array("id", "name"), array("braveheart_categories"), array("status" => 1));

    // Fetch Events with Pagination
    $totalEventsQuery = "SELECT COUNT(id) AS total FROM braveheart_events WHERE status = 1";
    $stmtTotal = $user->getConnection()->prepare($totalEventsQuery);
    $stmtTotal->execute();
    $totalEvents = $stmtTotal->fetch()['total'];
    $totalPages = ceil($totalEvents / $limit);

    // Main Query
    $eventQuery = "SELECT e.*, c.name as cat_name 
                   FROM braveheart_events e 
                   LEFT JOIN braveheart_categories c ON e.category_id = c.id 
                   WHERE e.status = 1 
                   ORDER BY e.deadline_date DESC 
                   LIMIT :limit OFFSET :offset";
    
    $stmt = $user->getConnection()->prepare($eventQuery);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll();

    $today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $userHeader->printUserHeader() ?>
    
    <style>
        .challenge-header-title { color: #555; font-weight: 700; border-bottom: 2px solid #f1c40f; display: inline-block; padding-bottom: 5px; }
        .category-nav a { color: #666; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .category-nav a:hover { color: #e67e22; }
        
        .event-container { margin-bottom: 50px; }
        .event-banner { 
            width: 100%; 
            border-radius: 5px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            display: block;
            min-height: 150px; /* Ensures space is reserved if image loads slow */
            background-color: #f8f9fa;
        }
        
        .status-badge { font-weight: 800; text-transform: uppercase; font-size: 0.9rem; margin-bottom: 5px; display: block; }
        .status-upcoming { color: #28a745; } 
        .status-completed { color: #8b0000; } 
        
        .event-title { color: #d35400; font-weight: 800; text-transform: uppercase; margin-top: 10px; margin-bottom: 2px; }
        .category-tag { color: #f39c12; font-size: 0.9rem; font-weight: bold; }
        .category-tag i { margin-right: 5px; }

        .pagination .page-item.active .page-link { background-color: #e74c3c; border-color: #e74c3c; }
        .pagination .page-link { color: #333; }
    </style>
</head>
<body class="index">

    <?php echo $userHeader->printUserNav(); ?>
    <div class="page-header-bg"></div>

    <div class="container mt-4 page-header-content">
        <nav class="edi-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
                <li class="breadcrumb-item active">Brave Heart Challenge</li>
            </ol>
        </nav>

        
         <!-- Title + Line -->
        <div class="edi-page-title-row">
            <h2>CHALLENGES</h2>
            <div class="edi-page-title-rule" role="presentation"></div>
        </div>
        
        <div class="category-nav mt-2 mb-4">
            <?php foreach($bhCategories as $cat): ?>
                <a href="?cat=<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></a>
            <?php endforeach; ?>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if(empty($events)): ?>
                    <div class="alert alert-info text-center">No challenges found at the moment.</div>
                <?php else: 
                    foreach($events as $event): 
                        $isUpcoming = ($event['deadline_date'] >= $today);
                        $statusText = $isUpcoming ? "UP COMING ..." : "COMPLETED";
                        $statusClass = $isUpcoming ? "status-upcoming" : "status-completed";
                        
                        // Fix for Image Path: Ensure the path matches your folder structure
                        $imagePath = "./img/braveheart/" . $event['main_image'];
                ?>
                    <div class="event-container">
                        <div class="text-center">
                            <img src="<?php echo $imagePath; ?>" class="img-fluid event-banner" alt="<?php echo $event['title']; ?>" onerror="this.src='./img/placeholder-banner.jpg';">
                        </div>

                        <div class="mt-3">
                            <span class="category-tag"><i class="fa fa-tag"></i> <?php echo $event['cat_name']; ?></span>
                            <a href="challenge-details.php?id=<?php echo $event['id']; ?>">
                             <h4 class="event-title"><?php echo $event['title']; ?></h4>
                            </a>
                            <span class="<?php echo $statusClass; ?> status-badge"><?php echo $statusText; ?></span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page-1; ?>">&laquo;</a>
                </li>
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page+1; ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>


    <?php echo $userHeader->printUserFooter(); ?>

</body>
</html>