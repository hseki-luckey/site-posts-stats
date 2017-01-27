<?php
/*
Plugin Name: Site Posts Stats
Plugin URI: https://github.com/hseki-luckey/site-posts-stats
Description: サイト内の投稿を集計するただひとつのプラグイン
Version: 1.0
Author: hseki
Author URI: https://tech.linkbal.co.jp
License: GPL2
*/

add_action('admin_menu', 'add_site_posts_stats_menu');

// メニュー追加
function add_site_posts_stats_menu(){
	add_menu_page('投稿集計', '投稿集計', 'manage_options', __FILE__, 'site_posts_stats_index');
}

// style.css追加
function chart_style_add_init(){
	wp_register_style('chartstyle', plugins_url('style.css', __FILE__));
	wp_enqueue_style('chartstyle');
}
add_action('admin_init', 'chart_style_add_init');

// 画面表示
function site_posts_stats_index(){
	// chart.js
	wp_enqueue_script('chartjs','https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js', array(), '1.0.2');
	// グラフ表示
	if(isset($_GET['year']) && ctype_digit($_GET['year'])){
		year_posts_chart($_GET['year']);
	}elseif(isset($_GET['status'])){
		status_posts_chart($_GET['status']);
	}elseif(isset($_GET['type'])){
		type_posts_chart($_GET['type']);
	}elseif(isset($_GET['author']) && ctype_digit($_GET['author'])){
		author_posts_chart($_GET['author']);
	}elseif(isset($_GET['category']) && ctype_digit($_GET['category'])){
		category_posts_chart($_GET['category']);
	}else{
		all_stats_chart();
	}
}

// 初期画面表示
function all_stats_chart(){
	echo "<h2>全投稿の集計情報</h2>";
	echo '<div class="boxContainer">';
	display_posts_info_box();
	display_line_chart(get_count_by_post_date(), 'year', '年別投稿数');
	echo '</div>';
	echo '<div class="boxContainer">';
	display_pie_chart(get_count_posts_by_label('post_status'), 'status', 'ステータス別投稿数');
	display_pie_chart(get_count_posts_by_label('post_type'), 'type', '投稿タイプ別投稿数');
	echo '</div>';
	echo '<div class="boxContainer">';
	display_bar_chart(get_count_posts_by_author(), 'author', '著者別投稿数');
	display_bar_chart(get_count_posts_by_categories(), 'category', 'カテゴリ別投稿数');
	echo '</div>';
}

// 年別詳細
function year_posts_chart($year){
	$content = $year > 0 ? "{$year}年" : '年設定なし';

	echo "<h2>「{$content}」の集計情報</h2>";
	echo '<div class="boxContainer">';
	display_pie_chart(get_count_posts_by_label('post_status', 'YEAR(post_date_gmt)', $year), 'status', 'ステータス別投稿数');
	display_pie_chart(get_count_posts_by_label('post_type', 'YEAR(post_date_gmt)', $year), 'type', '投稿タイプ別投稿数');
	echo '</div>';
	echo '<div class="boxContainer">';
	display_bar_chart(get_count_posts_by_author('YEAR(post_date_gmt)', $year), 'author', '著者別投稿数');
	display_bar_chart(get_count_posts_by_categories('YEAR(post_date_gmt)', $year), 'category', 'カテゴリ別投稿数');
	echo '</div>';
	echo '<a href="'.admin_url('admin.php?page=site-posts-stats%2Findex.php').'">←全投稿の集計情報に戻る</a>';
}

// 投稿ステータス詳細
function status_posts_chart($status){
	echo "<h2>投稿ステータス「{$status}」の集計情報</h2>";
	echo '<div class="boxContainer">';
	display_pie_chart(get_count_posts_by_label('post_type', 'post_status', $status), 'type', '投稿タイプ別投稿数');
	display_line_chart(get_count_by_post_date('post_status', $status), 'year', '年別投稿数');
	echo '</div>';
	echo '<div class="boxContainer">';
	display_bar_chart(get_count_posts_by_author('post_status', $status), 'author', '著者別投稿数');
	display_bar_chart(get_count_posts_by_categories('post_status', $status), 'category', 'カテゴリ別投稿数');
	echo '</div>';
	echo '<a href="'.admin_url('admin.php?page=site-posts-stats%2Findex.php').'">←全投稿の集計情報に戻る</a>';
}

// 投稿タイプ詳細
function type_posts_chart($type){
	echo "<h2>投稿タイプ「{$type}」の集計情報</h2>";
	echo '<div class="boxContainer">';
	display_pie_chart(get_count_posts_by_label('post_status', 'post_type', $type), 'status', 'ステータス別投稿数');
	display_line_chart(get_count_by_post_date('post_type', $type), 'year', '年別投稿数');
	echo '</div>';
	echo '<div class="boxContainer">';
	display_bar_chart(get_count_posts_by_author('post_type', $type), 'author', '著者別投稿数');
	display_bar_chart(get_count_posts_by_categories('post_type', $type), 'category', 'カテゴリ別投稿数');
	echo '</div>';
	echo '<a href="'.admin_url('admin.php?page=site-posts-stats%2Findex.php').'">←全投稿の集計情報に戻る</a>';
}

// 著者詳細
function author_posts_chart($author_id){
	$author = get_user_by('id', $author_id);
	if($author){
		echo "<h2>著者「{$author->display_name}」の集計情報</h2>";
		echo '<div class="boxContainer">';
		display_pie_chart(get_count_posts_by_label('post_status', 'post_author', $author_id), 'status', 'ステータス別投稿数');
		display_pie_chart(get_count_posts_by_label('post_type', 'post_author', $author_id), 'type', '投稿タイプ別投稿数');
		echo '</div>';
		echo '<div class="boxContainer">';
		display_line_chart(get_count_by_post_date('post_author', $author_id), 'date', '年別投稿数');
		display_bar_chart(get_count_posts_by_categories('post_author', $author_id), 'category', 'カテゴリ別投稿数');
		echo '</div>';
	}else{
		echo '<p>該当の著者は存在しません</p>';
	}
	echo '<a href="'.admin_url('admin.php?page=site-posts-stats%2Findex.php').'">←全投稿の集計情報に戻る</a>';
}

// カテゴリ詳細
function category_posts_chart($category_id){
	$category = get_category($category_id);
	if($category){
		echo "<h2>カテゴリ「{$category->name}」の集計情報</h2>";
		echo '<div class="boxContainer">';
		display_category_info_box($category);
		display_pie_chart(get_count_posts_by_label('post_status', 'category', $category_id), 'status', 'ステータス別投稿数');
		echo '</div>';
		echo '<div class="boxContainer">';
		display_line_chart(get_count_by_post_date('category', $category_id), 'date', '年別投稿数');
		display_bar_chart(get_count_posts_by_author('category', $category_id), 'author', '著者別投稿数');
		echo '</div>';
	}else{
		echo '<p>該当のカテゴリは存在しません</p>';
	}
	echo '<a href="'.admin_url('admin.php?page=site-posts-stats%2Findex.php').'">←全投稿の集計情報に戻る</a>';
}

// 投稿タイプが設定されている場合
function set_post_type_query($label=false, $val=false){
	global $wpdb;

	$query = '';
	if($label == 'category'){
		$query .= " INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID";
		$query .= " INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id";
		$query .= " AND {$wpdb->term_taxonomy}.taxonomy = 'category'";
		$query .= " AND {$wpdb->term_taxonomy}.term_id = {$val}";
	}
	$page_type = $label == 'post_type' ? " WHERE post_type = '{$val}'" : ' WHERE post_type IN ("post", "page")';
	$where_query = in_array($label, array('post_status', 'post_author', 'YEAR(post_date_gmt)')) ? " AND {$label} = '{$val}'" : '';
	$query .= $page_type.$where_query;

	return $query;
}

// 全投稿数を集計
function get_count_all_posts($label=false, $val=false){
	global $wpdb;

	$query = "SELECT COUNT(*) FROM {$wpdb->posts}";
	$query .= set_post_type_query($label, $val);
	$posts_cnt = $wpdb->get_var($query);

	return $posts_cnt;
}

// 最新の投稿
function get_site_new_posts($label=false, $val=false, $limit=10){
	global $wpdb;

	$query = <<<EOS
		SELECT
			{$wpdb->posts}.ID AS ID,
			{$wpdb->posts}.post_title AS title,
			{$wpdb->posts}.post_type AS type,
			{$wpdb->posts}.post_status AS status,
			{$wpdb->users}.display_name AS author,
			{$wpdb->posts}.post_date_gmt AS date
		FROM {$wpdb->posts}
		INNER JOIN {$wpdb->users} ON {$wpdb->users}.ID = {$wpdb->posts}.post_author
EOS;
	$query .= set_post_type_query($label, $val);
	$query .= " ORDER BY date DESC LIMIT {$limit}";
	$target_posts = $wpdb->get_results($query);

	return $target_posts;
}

// 投稿ステータス・タイプごとの投稿数
function get_count_posts_by_label($target, $label=false, $val=false){
	global $wpdb;

	$query = <<<EOS
		SELECT
			{$target} AS label,
			{$target} AS label_val,
			COUNT(*) AS count
		FROM {$wpdb->posts}
EOS;
	$query .= set_post_type_query($label, $val);
	$query .= ' GROUP BY label ORDER BY count DESC';

	$posts_cnt = $wpdb->get_results($query);

	return $posts_cnt;
}

// 著者ごとの投稿数
function get_count_posts_by_author($label=false, $val=false){
	global $wpdb;

	$query = <<<EOS
		SELECT
			{$wpdb->users}.display_name AS label,
			{$wpdb->posts}.post_author AS label_val,
			COUNT(*) AS count
		FROM {$wpdb->posts}
		INNER JOIN {$wpdb->users} ON {$wpdb->users}.ID = {$wpdb->posts}.post_author
EOS;
	$query .= set_post_type_query($label, $val);
	$query .= ' GROUP BY label ORDER BY count DESC';

	$posts_cnt = $wpdb->get_results($query);

	return $posts_cnt;
}

// カテゴリごとの投稿数
function get_count_posts_by_categories($label=false, $val=false){
	global $wpdb;

	$query = <<<EOS
		SELECT
			{$wpdb->terms}.name AS label,
			{$wpdb->terms}.term_id AS label_val,
			COUNT(*) AS count
			FROM {$wpdb->posts}
		INNER JOIN {$wpdb->term_relationships} ON {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID
		INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
			AND {$wpdb->term_taxonomy}.taxonomy = 'category'
		INNER JOIN {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
EOS;
	$query .= set_post_type_query($label, $val);
	$query .= ' GROUP BY label ORDER BY count DESC';
	$posts_cnt = $wpdb->get_results($query);

	return $posts_cnt;
}

// 投稿日ごとの投稿数
function get_count_by_post_date($label=false, $val=false){
	global $wpdb;

	$query = <<<EOS
		SELECT
			DATE_FORMAT(post_date_gmt, '%Y') AS label,
			DATE_FORMAT(post_date_gmt, '%Y') AS label_val,
			COUNT(*) AS count
		FROM {$wpdb->posts}
EOS;
	$query .= set_post_type_query($label, $val);
	$query .= 'GROUP BY label ORDER BY label';
	$posts_cnt = $wpdb->get_results($query);

	return $posts_cnt;
}

// 詳細リンク取得
function get_detail_chart_link($area_name, $label_val){
	$default_url = admin_url('admin.php?page=site-posts-stats%2Findex.php');
	$query_arg = array(
		'page' => 'site-posts-stats%2Findex.php',
		"{$area_name}" => $label_val
	);

	return add_query_arg($query_arg, $default_url);
}

// 投稿の概要
function display_posts_info_box(){
	$all_posts_cnt = get_count_all_posts();
	$new_posts = get_site_new_posts();
?>
<div class="box">
	<div class="detailbox">
	<p>投稿数：<?php echo $all_posts_cnt; ?>件</p>
	<p>最新の投稿（最大10件）：
	<?php if($new_posts): ?>
		<table>
			<tr>
				<th>title</th>
				<th>type</th>
				<th>status</th>
				<th>author</th>
			</tr>
		<?php foreach($new_posts as $index => $post): ?>
			<tr>
				<td><a href="<?php the_permalink($post->ID); ?>"><?php echo $post->title; ?></a></td>
				<td><?php echo $post->type; ?></td>
				<td><?php echo $post->status; ?></td>
				<td><?php echo $post->author; ?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	<?php else: ?>
		最新の投稿がありません
	<?php endif; ?>
	</p>
	</div>
</div>
<?php
}

// カテゴリの概要
function display_category_info_box($category){
	$new_posts = get_site_new_posts('category', $category->cat_ID, 5);
?>
<div class="box">
	<div class="detailbox">
	<p>投稿数：<?php echo $category->count; ?></p>
	<p>slug：<a href="<?php echo get_category_link($category->cat_ID); ?>"><?php echo $category->slug; ?></a></p>
	<p><?php echo empty($category->description) ? 'description設定なし' : $category->description; ?></p>
	<p>最新の投稿（最大5件）：
	<?php if($new_posts): ?>
		<table>
			<tr>
				<th>title</th>
				<th>type</th>
				<th>status</th>
				<th>author</th>
			</tr>
		<?php foreach($new_posts as $index => $post): ?>
			<tr>
				<td><a href="<?php the_permalink($post->ID); ?>"><?php echo $post->title; ?></a></td>
				<td><?php echo $post->type; ?></td>
				<td><?php echo $post->status; ?></td>
				<td><?php echo $post->author; ?></td>
			</tr>
		<?php endforeach; ?>
		</table>
	<?php else: ?>
		最新の投稿がありません
	<?php endif; ?>
	</p>
	</div>
</div>
<?php
}

// 円グラフを表示
function display_pie_chart($labels, $area_name, $content_name){
	// カラー指定
	$color = array('#E73C86', '#ED81A9', '#F099B8', '#F4B4C9', '#F7C9D8', '#FADCE7', '#F8E7EE', '#FBF2F6');

	$i = 0;
?>
<div class="box">
	<p><?php echo $content_name; ?></p>
	<?php if($labels): ?>
	<div class="piechart">
		<canvas id="<?php echo $area_name; ?>"></canvas>
		<div class="piebox boxContainer">
			<ul>
			<?php foreach($labels as $label): ?>
				<li><a href="<?php echo get_detail_chart_link($area_name, $label->label_val); ?>"><?php echo $label->label; ?></a></li>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<script>
	jQuery(function($){
		var pieData = [
			<?php foreach($labels as $label): ?>
			{
				label: "<?php echo $label->label; ?>",
				value: <?php echo $label->count; ?>,
				color:"<?php echo $color[$i]; ?>"
			},
			<?php $i++; ?>
			<?php endforeach; ?>
		];
		var ctx = document.getElementById("<?php echo $area_name; ?>").getContext('2d');
		myPie = new Chart(ctx).Pie(pieData);
	});
	</script>
	<?php else: ?>
	<p>該当データがありません</p>
	<?php endif; ?>
</div>
<?php
}

// 棒グラフを表示
function display_bar_chart($labels, $area_name, $content_name){
	$label_arr = [];
	$data_arr = [];

	if($labels){
		$i = 0;
		foreach($labels as $label){
			if($i >= 10) break;
			$label_arr[] = '"'.$label->label.'"';
			$data_arr[] = $label->count;
			$i++;
		}
		$label_arr = implode(',', $label_arr);
		$data_arr = implode(',', $data_arr);
	}
?>
<div class="box">
	<p><?php echo $content_name; ?></p>
	<?php if($labels): ?>
	<div class="barchart">
		<canvas id="<?php echo $area_name; ?>"></canvas>
	</div>
	<div class="scrolltable">
	<table class="bartable">
	<?php foreach($labels as $label): ?>
		<tr>
			<td><a href="<?php echo get_detail_chart_link($area_name, $label->label_val); ?>"><?php echo $label->label; ?></a></td>
			<td><?php echo $label->count; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
	</div>
	<script>
	jQuery(function($){
		var barChartData = {
			labels : [<?php echo $label_arr; ?>],
			datasets : [{
				fillColor : 'rgba(214,133,176,0.7)',
				strokeColor : 'rgba(214,133,176,0.7)',
				highlightFill: 'rgba(238,189,203,0.7)',
				highlightStroke: 'rgba(238,189,203,0.7)',
				data : [<?php echo $data_arr; ?>]
			}]
		}
		var ctx = document.getElementById("<?php echo $area_name; ?>").getContext('2d');
		myBar = new Chart(ctx).Bar(barChartData);
	});
	</script>
	<?php else: ?>
	<p>該当データがありません</p>
	<?php endif; ?>
</div>
<?php
}

// 横軸グラフ
function display_line_chart($labels, $area_name, $content_name){
	$label_arr = [];
	$data_arr = [];

	if($labels){
		$first_label = intval($labels[0]->label);
		// 初期値（0）を設定
		$label_arr[] = 0;
		$data_arr[] = $first_label > 0 ? 0 : $labels[0]->count;
		// それ以外の値を設定
		if(count($labels) > 0){
			$from = $first_label > 0 ? $first_label : intval($labels[1]->label);
			$to = intval(end($labels)->label);
			for($i=$from; $i <= $to; $i++){
				$data_add = true;
				$label_arr[] = $i;
				foreach($labels as $index => $label){
					if($i == $label->label){
						$data_arr[] = $label->count;
						$data_add = false;
						break;
					}
				}
				if($data_add){
					$data_arr[] = 0;
				}
			}
		}
		$target_label = implode(',', $label_arr);
		$target_data = implode(',', $data_arr);
	}
?>
<div class="box">
	<p><?php echo $content_name; ?></p>
	<div class="linechart">
		<canvas id="<?php echo $area_name; ?>"></canvas>
	</div>
	<div class="scrolltable">
	<table class="linetable">
	<?php for($i=0; $i < count($label_arr); $i++): ?>
		<tr>
			<td><a href="<?php echo get_detail_chart_link($area_name, $label_arr[$i]); ?>"><?php echo $i == 0 ? '初期値' : $label_arr[$i].'年'; ?></td>
			<td><?php echo $data_arr[$i]; ?></td>
		</tr>
	<?php endfor; ?>
	</table>
	</div>
	<script>
	jQuery(function($){
		var lineChartData = {
			labels : [<?php echo $target_label; ?>],
			datasets : [{
				fillColor : 'rgba(242,218,232,0.6)',
				strokeColor : 'rgba(221,156,180,0.6)',
				pointColor : 'rgba(221,156,180,0.6)',
				pointStrokeColor : '#fff',
				pointHighlightFill : '#fff',
				pointHighlightStroke : 'rgba(221,156,180,0.6)',
				data : [<?php echo $target_data; ?>]
			}]
		}
			var ctx = document.getElementById("<?php echo $area_name; ?>").getContext('2d');
			myLine = new Chart(ctx).Line(lineChartData);
		});
	</script>
</div>
<?php
}
?>
