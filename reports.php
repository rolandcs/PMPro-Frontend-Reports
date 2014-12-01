<?php
/**
 * Template Name: Reports
 *
 * Display the last post from the blog, like a single post.
 *
 * @package Stag_Customizer
 * @since 1.1.0.
 */

$background_filter  = stag_get_post_meta( 'settings', get_the_ID(), 'post-background-filter' );

if ( ! $background_filter ) $background_filter = 'none';

get_header(); ?>

	<div id="main" class="site-main page-cover page-cover--<?php echo get_the_ID(); ?>">

		<div class="page-cover__background stag-image--<?php echo esc_attr( $background_filter ); ?>"></div>

		<?php stag_post_background_css( get_the_ID(), '.page-cover--', '.page-cover__background' ); ?>
		
		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'page' ); ?>
			
			<?php function pmpro_columnist_report()
			
{
	global $wpdb;
	
	//vars
	if(!empty($_REQUEST['s']))
		$s = $_REQUEST['s'];
	else
		$s = "";
		
	if(!empty($_REQUEST['l']))
		$l = $_REQUEST['l'];
	else
		$l = "";
?>

	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php _e('Visits, Views, and Logins Report', 'pmpro');?>
	</h2>		
	<ul class="subsubsub">
		<li>			
			<?php _ex('Show', 'Dropdown label, e.g. Show All Users', 'pmpro')?> <select name="l" onchange="jQuery('#posts-filter').submit();">
				<option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Users', 'pmpro')?></option>
				<option value="all" <?php if($l == "all") { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmpro')?></option>
				<?php
					$levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
					foreach($levels as $level)
					{
				?>
					<option value="<?php echo $level->id?>" <?php if($l == $level->id) { ?>selected="selected"<?php } ?>><?php echo $level->name?></option>
				<?php
					}
				?>
			</select>			
		</li>
	</ul>
	<p class="search-box">
		<label class="hidden" for="post-search-input"><?php _ex('Search', 'Search form label', 'pmpro')?> <?php if(empty($l)) echo "Users"; else echo "Members";?>:</label>
		<input type="hidden" name="page" value="pmpro-reports" />		
		<input type="hidden" name="report" value="login" />		
		<input id="post-search-input" type="text" value="<?php echo $s?>" name="s"/>
		<input class="button" type="submit" value="Search Members"/>
	</p>
	<?php 
		//some vars for the search					
		if(isset($_REQUEST['pn']))
			$pn = $_REQUEST['pn'];
		else
			$pn = 1;
			
		if(isset($_REQUEST['limit']))
			$limit = $_REQUEST['limit'];
		else
			$limit = 15;
		
		$end = $pn * $limit;
		$start = $end - $limit;				
					
		if($s)
		{
			$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id AND mu.status = 'active' LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE (u.user_login LIKE '%$s%' OR u.user_email LIKE '%$s%' OR um.meta_value LIKE '%$s%') ";
		
			if($l == "all")
				$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id > 0 ";
			elseif($l)
				$sqlQuery .= " AND mu.membership_id = '" . $l . "' ";					
				
			$sqlQuery .= "GROUP BY u.ID ORDER BY user_registered DESC LIMIT $start, $limit";
		}
		else
		{
			$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id AND mu.status = 'active' LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id";
			$sqlQuery .= " WHERE 1=1 ";
			
			if($l == "all")
				$sqlQuery .= " AND mu.membership_id > 0  AND mu.status = 'active' ";
			elseif($l)
				$sqlQuery .= " AND mu.membership_id = '" . $l . "' ";
			$sqlQuery .= "GROUP BY u.ID ORDER BY user_registered DESC LIMIT $start, $limit";
		}
		$sqlQuery = apply_filters("pmpro_members_list_sql", $sqlQuery);
		
		$theusers = $wpdb->get_results($sqlQuery);
		$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
		
		if($theusers)
		{
		?>
		<p class="clear"><?php echo strval($totalrows)?> <?php if(empty($l)) echo "users"; else echo "members";?> found.	
		<?php		
		}		
	?>
	<table class="widefat">
		<thead>
			<tr class="thead">
				<th><?php _e('ID', 'pmpro')?></th>
				<th><?php _e('User', 'pmpro')?></th>	
				<th><?php _e('Name', 'pmpro')?></th>
				<th><?php _e('Membership', 'pmpro')?></th>	
				<th><?php _e('Joined', 'pmpro')?></th>
				<th><?php _e('Expires', 'pmpro')?></th>
				<th><?php _e('Last Visit', 'pmpro')?></th>
				<th><?php _e('Visits This Month', 'pmpro')?></th>
				<th><?php _e('Total Visits', 'pmpro')?></th>
				<th><?php _e('Views This Month', 'pmpro')?></th>
				<th><?php _e('Total Views', 'pmpro')?></th>
				<th><?php _e('Last Login', 'pmpro')?></th>
				<th><?php _e('Logins This Month', 'pmpro')?></th>
				<th><?php _e('Total Logins', 'pmpro')?></th>				
			</tr>
		</thead>
		<tbody id="users" class="list:user user-list">	
			<?php	
				$count = 0;							
				foreach($theusers as $auser)
				{
					//get meta																					
					$theuser = get_userdata($auser->ID);
					$visits = get_user_meta($auser->ID, "pmpro_visits", true);
					$views = get_user_meta($auser->ID, "pmpro_views", true);
					$logins = get_user_meta($auser->ID, "pmpro_logins", true);
					if(empty($logins))
						$logins = array("last"=>"N/A", "month"=>"N/A", "alltime"=>"N/A");
					?>
						<tr <?php if($count++ % 2 == 0) { ?>class="alternate"<?php } ?>>
							<td><?php echo $theuser->ID?></td>
							<td>
								<?php echo get_avatar($theuser->ID, 32)?>
								<strong>
									<?php
										$userlink = '<a href="user-edit.php?user_id=' . $theuser->ID . '">' . $theuser->user_login . '</a>';
										$userlink = apply_filters("pmpro_members_list_user_link", $userlink, $theuser);
										echo $userlink;
									?>																		
								</strong>
							</td>										
							<td>
								<?php echo $theuser->display_name;?>
							</td>
							<td><?php echo $auser->membership?></td>												
							<td><?php echo date("m/d/Y", strtotime($theuser->user_registered, current_time("timestamp")))?></td>
							<td>
								<?php 									
									if($auser->enddate) 
										echo date(get_option('date_format'), $auser->enddate);
									else
										echo "Never";
								?>
							</td>
							<td><?php if(!empty($visits['last'])) echo $visits['last'];?></td>
							<td><?php if(!empty($visits['month'])) echo $visits['month'];?></td>
							<td><?php if(!empty($visits['alltime'])) echo $visits['alltime'];?></td>							
							<td><?php if(!empty($visits['month'])) echo $views['month'];?></td>
							<td><?php if(!empty($visits['alltime'])) echo $views['alltime'];?></td>
							<td><?php if(!empty($visits['last'])) echo $logins['last'];?></td>
							<td><?php if(!empty($visits['month'])) echo $logins['month'];?></td>
							<td><?php if(!empty($visits['alltime'])) echo $logins['alltime'];?></td>
						</tr>
					<?php
				}
				
				if(!$theusers)
				{
				?>
				<tr>
					<td colspan="9"><p><?php _e('No members found.', 'pmpro')?> <?php if($l) { ?><a href="?page=pmpro-memberslist&s=<?php echo $s?>"><?php _e('Search all levels', 'pmpro')?></a>.<?php } ?></p></td>
				</tr>
				<?php
				}
			?>		
		</tbody>
	</table>
	</form>

	<?php
	echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, get_admin_url(NULL, "/admin.php?page=pmpro-reports&report=login&s=" . urlencode($s)), "&l=$l&limit=$limit&pn=");
	?>
<?php
}

	 endwhile; // end of the loop. ?>
		

	</div><!-- #main -->

	<?php get_template_part( '_post', 'comments' ); ?>

<?php get_footer();
