<?php 
/*
global $wpdb;

$table_name = rcp_get_payments_db_name();

$query = $wpdb-> prepare(
    "SELECT SUM( amount) FROM {$table_name} WHERE customer_id = %d AND status ='complete'",
    $this->get_id()
);

$results = $wpdb->get_var($query);
*/
function deferredTab(){
	$current_page = admin_url( 'admin.php?page=rcp-reports' );
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'earnings';
?>

    <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'deferred' ), $current_page ) ); ?>" class="nav-tab <?php echo $active_tab == 'deferred' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Deffered income', 'rcp' ); ?></a>
<?php 

}
add_action( 'rcp_reports_tabs', 'deferredTab' );

/**
 * Displays the deferred graph.
 *
 * @uses rcp_get_report_dates()
 *
 * @access  public
 * @since   1.8
 * @return  void
*/
function rcp_deferred_graph() {
	global $rcp_options, $wpdb;

	// Retrieve the queried dates
	$dates = rcp_get_report_dates();

	// Determine graph options
	switch ( $dates['range'] ) :
		case 'today' :
			$time_format 	= '%d/%b';
			$tick_size		= 'hour';
			$day_by_day		= true;
			break;
		case 'last_year' :
			$time_format 	= '%b';
			$tick_size		= 'month';
			$day_by_day		= false;
			break;
		case 'this_year' :
			$time_format 	= '%b';
			$tick_size		= 'month';
			$day_by_day		= false;
			break;
		case 'last_quarter' :
			$time_format	= '%b';
			$tick_size		= 'month';
			$day_by_day 	= false;
			break;
		case 'this_quarter' :
			$time_format	= '%b';
			$tick_size		= 'month';
			$day_by_day 	= false;
			break;
		case 'other' :
			if( $dates['m_end'] - $dates['m_start'] >= 2 || $dates['year_end'] > $dates['year'] ) {
				$time_format	= '%b';
				$tick_size		= 'month';
				$day_by_day 	= false;
			} else {
				$time_format 	= '%d/%b';
				$tick_size		= 'day';
				$day_by_day 	= true;
			}
			break;
		default:
			$time_format 	= '%d/%b'; 	// Show days by default
			$tick_size		= 'day'; 	// Default graph interval
			$day_by_day 	= true;
			break;
	endswitch;


	$time_format 	= apply_filters( 'rcp_graph_timeformat', $time_format );
	$tick_size 		= apply_filters( 'rcp_graph_ticksize', $tick_size );
	$earnings 		= (float) 0.00; // Total earnings for time period shown
	$subscription   = isset( $_GET['subscription'] ) ? absint( $_GET['subscription'] ) : false;
	$payments_db    = new RCP_Payments;

	$args = array(
		'subscription' => rcp_get_subscription_name( $subscription ),
		'date' => array()
	);

	ob_start(); ?>
	<script type="text/javascript">
	   jQuery( document ).ready( function($) {
			$.plot(
				$("#rcp_deferred_graph"),
				[{
					data: [
						<?php

						if( $dates['range'] == 'this_week' || $dates['range'] == 'last_week'  ) {

							//Day by day
							$day     = $dates['day'];
							$day_end = $dates['day_end'];
							$month   = $dates['m_start'];

							while ( $day <= $day_end ) :

								$args = array(
									'date' => array(
										'day'   => $day,
										'month' => $month,
										'year'  => $dates['year']
									),
									'fields' => 'amount'
								);

								$args['date'] = array( 'day' => $day, 'month' => $month, 'year' => $dates['year'] );

								$payments = $payments_db->get_earnings( $args );
								$earnings += $payments;
								$date = mktime( 0, 0, 0, $month, $day, $dates['year'] ); ?>
								[<?php echo $date * 1000; ?>, <?php echo $payments; ?>],
								<?php
								$day++;
							endwhile;

						} else {

							$y = $dates['year'];
							while( $y <= $dates['year_end'] ) :

								if( $dates['year'] == $dates['year_end'] ) {
									$month_start = $dates['m_start'];
									$month_end   = $dates['m_end'];
								} elseif( $y == $dates['year'] ) {
									$month_start = $dates['m_start'];
									$month_end   = 12;
								} elseif ( $y == $dates['year_end'] ) {
									$month_start = 1;
									$month_end   = $dates['m_end'];
								} else {
									$month_start = 1;
									$month_end   = 12;
								}

								$i = $month_start;
								while ( $i <= $month_end ) :
									if ( $day_by_day ) :
										$num_of_days 	= cal_days_in_month( CAL_GREGORIAN, $i, $y );
										$d 				= 1;
										while ( $d <= $num_of_days ) :
											$args['date'] = array( 'day' => $d, 'month' => $i, 'year' => $y );
											$payments = $payments_db->get_earnings( $args );
											$earnings += $payments;
											$date = mktime( 0, 0, 0, $i, $d, $y ); ?>
											[<?php echo $date * 1000; ?>, <?php echo $payments; ?>],
										<?php
										$d++;
										endwhile;
									else :

										$args['date'] = array( 'day' => null, 'month' => $i, 'year' => $y );
										$payments = $payments_db->get_earnings( $args );
										$earnings += $payments;
										$date = mktime( 0, 0, 0, $i, 1, $y );
										?>
										[<?php echo $date * 1000; ?>, <?php echo $payments; ?>],
									<?php
									endif;
									$i++;
								endwhile;

								$y++;
							endwhile;

						}

						?>,
					],
					yaxis: 2,
					label: "<?php _e( 'Earnings', 'rcp' ); ?>",
					id: 'sales'
				}],
			{
				series: {
				   lines: { show: true },
				   points: { show: true }
				},
				grid: {
					show: true,
					aboveData: false,
					color: '#ccc',
					backgroundColor: '#fff',
					borderWidth: 2,
					borderColor: '#ccc',
					clickable: false,
					hoverable: true
				},
				xaxis: {
					mode: "time",
					timeFormat: "<?php echo $time_format; ?>",
					minTickSize: [1, "<?php echo $tick_size; ?>"]
				},
				yaxis: {
					min: 0,
					minTickSize: 1,
					tickDecimals: 0
				}

			});

			function rcp_flot_tooltip(x, y, contents) {
				$('<div id="rcp-flot-tooltip">' + contents + '</div>').css( {
					position: 'absolute',
					display: 'none',
					top: y + 5,
					left: x + 5,
					border: '1px solid #fdd',
					padding: '2px',
					'background-color': '#fee',
					opacity: 0.80
				}).appendTo("body").fadeIn(200);
			}

			var previousPoint = null;
			$("#rcp_deferred_graph").bind("plothover", function (event, pos, item) {
				if (item) {
					if (previousPoint != item.dataIndex) {
						previousPoint = item.dataIndex;
						$("#rcp-flot-tooltip").remove();
						var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1].toFixed(2);
						if( rcp_vars.currency_pos == 'before' ) {
							rcp_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + rcp_vars.currency_sign + y );
						} else {
							rcp_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + rcp_vars.currency_sign );
						}
					}
				} else {
					$("#rcp-flot-tooltip").remove();
					previousPoint = null;
				}
			});
	   });
	</script>
	<h1><?php _e( 'Deffered Income Report', 'rcp' ); ?></h1>
	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<div class="inside">
				<?php rcp_reports_graph_controls(); ?>
				<div id="rcp_deferred_graph" style="height: 300px;"></div>
				<p class="rcp_graph_totals"><strong><?php _e( 'Total earnings for period shown: ', 'rcp' ); echo rcp_currency_filter( $earnings ); ?></strong></p>
			</div>
		</div>
	</div>
	<?php
	echo ob_get_clean();
}
add_action( 'rcp_reports_tab_deferred', 'rcp_deferred_graph' );