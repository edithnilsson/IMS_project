<?php
require "config/db.php";   
// Get the total number of announcements in the database
$total_announcements = $conn->query('SELECT COUNT(*) FROM Manual_warning')->fetch_row()[0];

// Check if the page number is specified and check if it's a number, if not return the default page number which is 1.
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;

// Number of results to show on each page.
$num_results_on_page = 2;


if ($stmt = $conn->prepare('
    SELECT mw.*, su.user_name, su.user_surname, p.pathogen_name
    FROM Manual_warning mw
    JOIN System_user su ON mw.user_id = su.user_id 
    JOIn Pathogen p ON mw.pathogen_id = p.pathogen_id
    ORDER BY mwarning_date DESC 
    LIMIT ?,?')) {
	// Calculate the page to get the results we need from our table.
	$calc_page = ($current_page  - 1) * $num_results_on_page;
	$stmt->bind_param('ii', $calc_page, $num_results_on_page);
	$stmt->execute(); 
	// Get the results...
	$result = $stmt->get_result();
	$stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/annoucement_page.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0">< </script>

    <title>Document</title>
</head>
<body>
<table>
	<tr>
		<th>Date</th>
		<th>Announcement</th>
        <th>Pathogen</th>
        <th>Ward</th>
		<th>Staff Member</th>
	</tr>
	<?php while ($row = $result->fetch_assoc()): ?>
	<tr>
		<td><?php echo $row['mwarning_date']; ?></td>
		<td><?php echo $row['mwarning_message']; ?></td>
        <td><?php echo $row['pathogen_name']; ?></td>
        <td><?php echo $row['department_id ']; ?></td>
		<td><?php echo "{$row['user_name']} {$row['user_surname']}"; ?></td>
	</tr>
	<?php endwhile; ?>
</table>

<?php 
// pagination
//how many pages will be needed? 
$page_count = ceil($total_announcements / $num_results_on_page);

if ($page_count > 0): ?>
    <ul class="pagination">
        <!-- if you are on another page than 1 then show the prev button -->
        <?php if ($current_page  > 1): ?>
        <li><a href="announcements.php?page=<?php echo $current_page -1 ?>">Prev</a></li>
        <?php endif; ?>

        <!-- if you are on a page further down than 3 then show ... instead of all the previous pages  -->
        <?php if ($current_page  > 3): ?>
        <li ><a href="announcements.php?page=1">1</a></li>
        <li >...</li>
        <?php endif; ?>

        <!-- show previous pages if the pagenumber does not become negative -->
        <?php if ($current_page -2 > 0): ?><li class="page"><a href="announcements.php?page=<?php echo $current_page -2 ?>"><?php echo $current_page -2 ?></a></li><?php endif; ?>
        <?php if ($current_page -1 > 0): ?><li class="page"><a href="announcements.php?page=<?php echo $current_page -1 ?>"><?php echo $current_page -1 ?></a></li><?php endif; ?>
            
        <!-- mark the curren page you are on-->
        <li class="currentpage"><a href="announcements.php?page=<?php echo $current_page  ?>"><?php echo $current_page ?></a></li>
        
        <!-- show the up comming pages if they do not exceed the number of pages needed  -->
        <?php if ($current_page +1 < ceil($total_announcements/ $num_results_on_page)+1): ?><li class="page"><a href="announcements.php?page=<?php echo $current_page +1 ?>"><?php echo $current_page +1 ?></a></li><?php endif; ?>
        <?php if ($current_page +2 < ceil($total_announcements / $num_results_on_page)+1): ?><li class="page"><a href="announcements.php?page=<?php echo $current_page +2 ?>"><?php echo $current_page +2 ?></a></li><?php endif; ?>
        
        <!-- if the current page is furhter away from the end page than 2 pages then show ... -->
        <?php if ($current_page  < ceil($total_announcements/ $num_results_on_page)-2): ?>
        <li>...</li>
        <li><a href="announcements.php?page=<?php echo ceil($total_announcements / $num_results_on_page) ?>"><?php echo ceil($total_announcements / $num_results_on_page) ?></a></li>
        <?php endif; ?>
    
        <!-- show the next button if you are not already on the last page -->
        <?php if ($current_page  < ceil($total_announcements/ $num_results_on_page)): ?>
        <li><a href="announcements.php?page=<?php echo $current_page +1 ?>">Next</a></li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>
    




</body>
</html>