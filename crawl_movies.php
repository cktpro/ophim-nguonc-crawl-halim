<?php
add_action('admin_menu', 'tool_add_menu');
function tool_add_menu()
{
	add_menu_page(
		'Tool Crawl Phim',
		'Crawl Phim Tool',
		'manage_options',
		'crawl-ophim-tools',
		'crawl_tools',
		'',
		'2'
	);
}

function crawl_tools()
{

	$cache = new Cache();

	$categoryFromApi = $cache->readCache(API_DOMAIN . "/the-loai");
	if(!$categoryFromApi) { // Kiểm tra nếu cache tồn tại
		$categoryFromApi = file_get_contents(API_DOMAIN . "/the-loai");
		$cache->timeCache = 86400;
		$cache->saveCache(API_DOMAIN . "/the-loai", $categoryFromApi); // Lưu cache
	}
	$categoryFromApi = json_decode($categoryFromApi);
	
	$countryFromApi = $cache->readCache(API_DOMAIN . "/quoc-gia");
	if(!$countryFromApi) { // Kiểm tra nếu cache tồn tại
		$countryFromApi = file_get_contents(API_DOMAIN . "/quoc-gia");
		$cache->timeCache = 86400;
		$cache->saveCache(API_DOMAIN . "/quoc-gia", $countryFromApi); // Lưu cache
	}
	$countryFromApi = json_decode($countryFromApi);	
?>

<?php
  $default_tab = null;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
?>

<div class="wrap">
	<nav class="nav-tab-wrapper">
	<a href="?page=crawl-ophim-tools&tab=nguonc" class="nav-tab <?php if($tab==='nguonc'):?>nav-tab-active<?php endif; ?>">Crawl Nguonc</a>
    <a href="?page=crawl-ophim-tools" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Thủ công</a>
    <a href="?page=crawl-ophim-tools&tab=schedule" class="nav-tab <?php if($tab==='schedule'):?>nav-tab-active<?php endif; ?>">Tự động</a>
    <a href="?page=crawl-ophim-tools&tab=about" class="nav-tab <?php if($tab==='about'):?>nav-tab-active<?php endif; ?>">Giới thiệu</a>
  </nav>
	<div class="tab-content">
		<?php
			switch($tab) :
				case 'schedule':
				$crawl_ophim_settings = json_decode(get_option(CRAWL_OPHIM_OPTION_SETTINGS, []));
				$schedule_log = getLastLog();
		?>

				<div class="crawl_page">
					<div class="postbox">
						<div class="inside">
							<b>Hưỡng dẫn cấu hình crontab</b>
							<div>
								<p>
									Thời gian thực hiện (<a href="https://crontab.guru/" target="_blank">Xem thêm</a>)
								</p>
								<p>
									Cấu hình crontab: <code><i style="color:blueviolet">*/10 * * * *</i> cd <i style="color:blueviolet">/path/to/</i>wp-content/plugins/crawl_ophim_halimthemes/ && php -q schedule.php <i style="color:blueviolet">{secret_key}</i></code>
								</p>
								<p>
									Ví dụ:
									<br />
									Mỗi 5 phút: <code>*/5 * * * * cd <?php echo CRAWL_OPHIM_PATH; ?> && php -q schedule.php <i style="color:blueviolet"><?php echo get_option(CRAWL_OPHIM_OPTION_SECRET_KEY, ''); ?></i></code>
									<br />
									Mỗi 10 phút: <code>*/10 * * * * cd <?php echo CRAWL_OPHIM_PATH; ?> && php -q schedule.php <i style="color:blueviolet"><?php echo get_option(CRAWL_OPHIM_OPTION_SECRET_KEY, ''); ?></i></code>
								</p>
							</div>
						</div>
					</div>
				</div>

				<div class="crawl_page">
					<div class="postbox">
						<div class="inside">
							<b>Cấu hình tự động</b>
							<div>
								<p>
									Secret Key: <input type="text" name="crawl_ophim_schedule_secret" value="<?php echo get_option(CRAWL_OPHIM_OPTION_SECRET_KEY, ''); ?>">
									<button id="save_crawl_ophim_schedule_secret" class="button">Lưu mật khẩu</button>
								</p>
							</div>
							<div>
								<p>
									Kích hoạt:
									<input type="checkbox" class="wppd-ui-toggle" id="crawl_ophim_schedule_enable" name="crawl_ophim_schedule_enable"
										value=""
										<?php echo (json_decode(file_get_contents(CRAWL_OPHIM_PATH_SCHEDULE_JSON))->enable === true) ? 'checked' : ''; ?>
									>
								</p>
							</div>
							<div>
								<p>Trạng thái: <?php echo (int) get_option(CRAWL_OPHIM_OPTION_RUNNING, 0) === 1 ? "<code style='color: blue'>Đang chạy...</code>" : "<code style='color: chocolate'>Dừng</code>"; ?></p>
							</div>
							<div>
								<p>Bỏ qua định dạng: <code style="color: red"><?php echo join(', ', $crawl_ophim_settings->filterType);?></code></p>
								<p>Bỏ qua thể loại: <code style="color: red"><?php echo join(', ', $crawl_ophim_settings->filterCategory);?></code></p>
								<p>Bỏ qua quốc gia: <code style="color: red"><?php echo join(', ', $crawl_ophim_settings->filterCountry);?></code></p>
							</div>
							<div>
								<p>Page đầu: <code style="color: blue"><?php echo $crawl_ophim_settings->pageFrom;?></code></p>
								<p>Page cuối: <code style="color: blue"><?php echo $crawl_ophim_settings->pageTo;?></code></p>
							</div>

								<div class="notice notice-success">
									<p>File logs: <code style="color:brown"><?php echo $schedule_log['log_filename'];?></code></p>
									<textarea rows="10" id="schedule_log" class="" readonly><?php echo $schedule_log['log_data'];?></textarea>
								</div>

						</div>
					</div>
				</div>
		<?php
				break;
				case 'nguonc':
		?>
		<!-- Auto crawl phim nguồn -->
		<div class="crawl_main">
				<div class="crawl_filter notice notice-info">
				<div class="crawl_page">
					Nhập phim cần tìm: <input type="text" name="key_search_nguonc" value="">
					<div id="search_movies_nguonc" class="primary">Search</div>
				</div>
					<div class="filter_title"><strong>Bỏ qua định dạng</strong></div>
					<div class="filter_item">
						<label><input type="checkbox" class="" name="filter_type[]" value="single"> Phim lẻ</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="series"> Phim bộ</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="hoathinh"> Hoạt hình</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="tvshows"> Tv shows</label>
					</div>

					<div class="filter_title"><strong>Bỏ qua thể loại</strong></div>
					<div class="filter_item">
						<?php
							foreach($categoryFromApi as $category) {
						?>
								<label><input type="checkbox" class="" name="filter_category[]" value="<?php echo $category->name;?>"> <?php echo $category->name;?></label>
						<?php
							}
						?>
					</div>

					<div class="filter_title"><strong>Bỏ qua quốc gia</strong></div>
					<div class="filter_item">
						<?php
							foreach($countryFromApi as $country) {
						?>
								<label><input type="checkbox" class="" name="filter_country[]" value="<?php echo $country->name;?>"> <?php echo $country->name;?></label>
						<?php
							}
						?>
					</div>
					<p>
						<div id="save_crawl_ophim_schedule" class="button">Lưu cấu hình cho crawl tự động</div>
					</p>
				</div>

				<div class="crawl_page">
					Page Crawl: From <input type="number" name="page_from" value="">
					To <input type="number" name="page_to" value="">
					<div id="get_list_movies_nguonc" class="primary">Get List Movies Nguonc</div>
				</div>

				<div class="crawl_page">
					Wait Timeout Random: From  <input type="number" name="timeout_from" value="">(ms) - 
					To <input type="number" name="timeout_to" value=""> (ms)
				</div>

				<div class="crawl_page">
					<div style="display: none" id="msg" class="notice notice-success">
						<p id="msg_text"></p>
					</div>
					<textarea rows="10" id="result_list_movies" class="list_movies"></textarea>
					<div id="roll_movies" class="roll">Trộn Link</div>
					<div id="crawl_movies_nguonc" class="primary">Crawl Movies Nguonc</div>

					<div style="display: none;" id="result_success" class="notice notice-success">
						<p>Crawl Thành Công</p>
						<textarea rows="10" id="list_crawl_success"></textarea>
					</div>

					<div style="display: none;" id="result_error" class="notice notice-error">
						<p>Crawl Lỗi</p>
						<textarea rows="10" id="list_crawl_error"></textarea>
					</div>
				</div>
		</div>
		<?php
					break;
				case 'about':
		?>
				<div class="crawl_page">
					<div class="postbox">
						<div class="inside">
							Ophim1.CC là website dữ liệu phim miễn phí vĩnh viễn. Cập nhật nhanh, chất lượng cao, ổn định và lâu dài. Tốc độ phát cực nhanh với đường truyền băng thông cao, đảm bảo đáp ứng được lượng xem phim trực tuyến lớn. Đồng thời giúp nhà phát triển website phim giảm thiểu chi phí của các dịch vụ lưu trữ và stream. <br />
							- Hàng ngày chạy tools tầm 10 đến 20 pages đầu (tùy số lượng phim được cập nhật trong ngày) để update tập mới hoặc thêm phim mới!<br />
							- Trộn link vài lần để thay đổi thứ tự crawl & update. Giúp tránh việc quá giống nhau về content của các website!<br />
							- API được cung cấp miễn phí: <a href="https://ophim1.cc/api-document" target="_blank">https://ophim1.cc/api-document</a> <br />
							- Tham gia trao đổi tại: <a href="https://t.me/+QMfjBOtNpkZmNTc1" target="_blank">https://t.me/+QMfjBOtNpkZmNTc1</a> <br />
						</div>
					</div>
				</div>
		<?php
					break;
				default:
		?>
			<div class="crawl_main">
				<div class="crawl_filter notice notice-info">
					<div class="filter_title"><strong>Bỏ qua định dạng</strong></div>
					<div class="filter_item">
						<label><input type="checkbox" class="" name="filter_type[]" value="single"> Phim lẻ</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="series"> Phim bộ</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="hoathinh"> Hoạt hình</label>
						<label><input type="checkbox" class="" name="filter_type[]" value="tvshows"> Tv shows</label>
					</div>

					<div class="filter_title"><strong>Bỏ qua thể loại</strong></div>
					<div class="filter_item">
						<?php
							foreach($categoryFromApi as $category) {
						?>
								<label><input type="checkbox" class="" name="filter_category[]" value="<?php echo $category->name;?>"> <?php echo $category->name;?></label>
						<?php
							}
						?>
					</div>

					<div class="filter_title"><strong>Bỏ qua quốc gia</strong></div>
					<div class="filter_item">
						<?php
							foreach($countryFromApi as $country) {
						?>
								<label><input type="checkbox" class="" name="filter_country[]" value="<?php echo $country->name;?>"> <?php echo $country->name;?></label>
						<?php
							}
						?>
					</div>
					<p>
						<div id="save_crawl_ophim_schedule" class="button">Lưu cấu hình cho crawl tự động</div>
					</p>
				</div>

				<div class="crawl_page">
					Page Crawl: From <input type="number" name="page_from" value="">
					To <input type="number" name="page_to" value="">
					<div id="get_list_movies" class="primary">Get List Movies</div>
				</div>

				<div class="crawl_page">
					Wait Timeout Random: From  <input type="number" name="timeout_from" value="">(ms) - 
					To <input type="number" name="timeout_to" value=""> (ms)
				</div>

				<div class="crawl_page">
					<div style="display: none" id="msg" class="notice notice-success">
						<p id="msg_text"></p>
					</div>
					<textarea rows="10" id="result_list_movies" class="list_movies"></textarea>
					<div id="roll_movies" class="roll">Trộn Link</div>
					<div id="crawl_movies" class="primary">Crawl Movies</div>

					<div style="display: none;" id="result_success" class="notice notice-success">
						<p>Crawl Thành Công</p>
						<textarea rows="10" id="list_crawl_success"></textarea>
					</div>

					<div style="display: none;" id="result_error" class="notice notice-error">
						<p>Crawl Lỗi</p>
						<textarea rows="10" id="list_crawl_error"></textarea>
					</div>
				</div>
			</div>

		<?php
					break;
			endswitch;
		?>
	</div>
</div>
<?php
}

add_action('wp_ajax_save_crawl_ophim_schedule_secret', 'save_crawl_ophim_schedule_secret');
function save_crawl_ophim_schedule_secret()
{
	update_option(CRAWL_OPHIM_OPTION_SECRET_KEY, $_POST['secret_key']);
	die();
}

add_action('wp_ajax_crawl_ophim_schedule_enable', 'crawl_ophim_schedule_enable');
function crawl_ophim_schedule_enable()
{
	$schedule = array(
		'enable' => $_POST['enable'] === 'true' ? true : false
	);
	file_put_contents(CRAWL_OPHIM_PATH_SCHEDULE_JSON, json_encode($schedule));
	die();
}

add_action('wp_ajax_crawl_ophim_save_settings', 'crawl_ophim_save_settings');
function crawl_ophim_save_settings()
{
	$data = array(
		'pageFrom' => $_POST['pageFrom'] ?? 5,
		'pageTo' => $_POST['pageTo'] ?? 1,
		'filterType' => $_POST['filterType'] ?? array(),
		'filterCategory' => $_POST['filterCategory'] ?? array(),
		'filterCountry' => $_POST['filterCountry'] ?? array(),
	);
	if (!get_option(CRAWL_OPHIM_OPTION_SETTINGS)) {
		add_option(CRAWL_OPHIM_OPTION_SETTINGS, json_encode($data));
	} else {
		update_option(CRAWL_OPHIM_OPTION_SETTINGS, json_encode($data));
	}
	die();
}
// Action search phim nguonc

add_action('wp_ajax_search_phim_nguonc', 'search_phim_nguonc');
// End action search phim nguonc
function search_phim_nguonc()
{
	echo search_phim_nguonc_handle($_POST['keyword']);
	die();
}
function search_phim_nguonc_handle($keyword)
{
	$key_search = slugify($keyword,'-');
	$search_url = "https://phim.nguonc.com/api/films/search?keyword=${key_search}";
	$sourcePage 			=  HALIMHelper::cURL($search_url );
	$sourcePage       = json_decode($sourcePage);
	$listMovies 			= [];

	if(count($sourcePage->items) > 0) {
		foreach ($sourcePage->items as $key => $item) {
			// ===================================================================================================================================
			// Cần chỉnh sửa
			$url= "https://phim.nguonc.com/api/film/{$item->slug}";
			$sourcePage 			=  HALIMHelper::cURL($url);
			$item_detail      = json_decode($sourcePage);
			// $year=date('Y', strtotime($item ->created ));
			// array_push($listMovies, "https://phim.nguonc.com/api/film/{$item->slug}|{$item->_id}|{$item->modified}|{$item->name}|{$item->original_name}|{$item ->created}");
			array_push($listMovies, "https://phim.nguonc.com/api/film/{$item->slug}|{$item_detail->movie->id}|{$item->modified}|{$item->name}|{$item->original_name}|{$item_detail ->movie->category->{3}->list[0]->name}");
		}
		return join("\n", $listMovies);
	}
	return false;
}
// action crawl nguonc
add_action('wp_ajax_crawl_ophim_page_nguonc', 'crawl_ophim_page_nguonc');
function crawl_ophim_page_nguonc()
{
	echo crawl_ophim_page_handle_nguonc($_POST['url']);
	die();
}
function crawl_ophim_page_handle_nguonc($url)
{
	$sourcePage 			=  HALIMHelper::cURL($url);
	$sourcePage       = json_decode($sourcePage);
	$listMovies 			= [];

	if(count($sourcePage->items) > 0) {
		foreach ($sourcePage->items as $key => $item) {
			// ===================================================================================================================================
			// Cần chỉnh sửa
			$url= "https://phim.nguonc.com/api/film/{$item->slug}";
			
			array_push($listMovies, "https://phim.nguonc.com/api/film/{$item->slug}|no_id|{$item->modified}|{$item->name}|{$item->original_name}|no_year");
		}
		return join("\n", $listMovies);
	}
	return $listMovies;
}
// end action crawl nguonc
add_action('wp_ajax_crawl_ophim_page', 'crawl_ophim_page');
function crawl_ophim_page()
{
	echo crawl_ophim_page_handle($_POST['url']);
	die();
}

function crawl_ophim_page_handle($url)
{
	$sourcePage 			=  HALIMHelper::cURL($url);
	$sourcePage       = json_decode($sourcePage);
	$listMovies 			= [];

	if(count($sourcePage->items) > 0) {
		foreach ($sourcePage->items as $key => $item) {
			array_push($listMovies, "https://ophim.tv/phim/{$item->slug}|{$item->_id}|{$item->modified->time}|{$item->name}|{$item->origin_name}|{$item->year}");
		}
		return join("\n", $listMovies);
	}
	return $listMovies;
}
// action crawl nguonc movies
add_action('wp_ajax_crawl_ophim_movies_nguonc', 'crawl_ophim_movies_nguonc');
function crawl_ophim_movies_nguonc()
{
	$data_post 					= $_POST['url'];
	$url 								= explode('|', $data_post)[0];
	$sourcePage 			=  HALIMHelper::cURL($url);
	$item_detail      = json_decode($sourcePage);
// 	$ophim_id 					= explode('|', $data_post)[1];
	$ophim_id 					= $item_detail->movie->id;
	$ophim_update_time 	= explode('|', $data_post)[2];
	$title 							= explode('|', $data_post)[3];
	$org_title 					= explode('|', $data_post)[4];
// 	$year 							= explode('|', $data_post)[5];
	$year 							= $item_detail ->movie->category->{3}->list[0]->name;
	
	$filterType 				= $_POST['filterType'] ?: [];
	$filterCategory 		= $_POST['filterCategory'] ?: [];
	$filterCountry 			= $_POST['filterCountry'] ?: [];

	$result = crawl_ophim_movies_handle_nguonc($url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry);
	echo $result;
	die();
}
function crawl_ophim_movies_handle_nguonc($url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry)
{
	try {
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_halim_metabox_options',
					'value' => $url,
					'compare' => 'LIKE'
				)
			)
		);
		$wp_query = new WP_Query($args);
		$total = $wp_query->found_posts;

		if ($total > 0) { # Trường hợp đã có

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => '_halim_metabox_options',
						'value' => $url,
						'compare' => 'LIKE'
					)
				)
			);
			$wp_query = new WP_Query($args);
			if ($wp_query->have_posts()) : while ($wp_query->have_posts()) : $wp_query->the_post();
					global $post;
					$_halim_metabox_options = get_post_meta($post->ID, '_halim_metabox_options', true);
					if($_halim_metabox_options["fetch_ophim_update_time"] == $ophim_update_time) { // Không có gì cần cập nhật
						
						$result = array(
							'status'   			=> true,
							'post_id' 			=> null,
							'list_episode' 	=> [],
							'msg' 					=> 'Nothing needs updating!',
							'wait'					=> false,
							'schedule_code' => SCHEDULE_CRAWLER_TYPE_NOTHING
						);
						return json_encode($result);
					}
					// Trường hợp phim chưa có -> tạo mới
					// $api_url 			= str_replace('ophim.tv', 'ophim1.com', $url);
					$sourcePage 	=  HALIMHelper::cURL($url);
					$sourcePage 	= json_decode($sourcePage, true);
					$data 				= create_data_nguonc($sourcePage, $url, $ophim_id, $ophim_update_time);

					// $status = getStatusNguonc($data['status']);

					// Re-Update Movies Info
					$formality 																					= ($data['type'] == 'tv_series') ? 'tv_series' : 'single_movies';
					$_halim_metabox_options["halim_movie_formality"] 		= $formality;
					$_halim_metabox_options["fetch_ophim_id"] 		= $data['fetch_ophim_id'];
					$_halim_metabox_options["halim_movie_status"] 			= $data['status'];
					$_halim_metabox_options["fetch_info_url"] 					= $data['fetch_url'];
					$_halim_metabox_options["fetch_ophim_update_time"] 	= $data['fetch_ophim_update_time'];
					$_halim_metabox_options["halim_original_title"] 		= $data['org_title'];
					$_halim_metabox_options["halim_trailer_url"] 				= $data['trailer_url'];
					$_halim_metabox_options["halim_runtime"] 						= $data['duration'];
					$_halim_metabox_options["halim_episode"] 						= $data['episode'];
					$_halim_metabox_options["halim_total_episode"] 			= $data['total_episode'];
					$_halim_metabox_options["halim_quality"] 						= $data['lang'] . ' - ' . $data['quality'];
					$_halim_metabox_options["halim_showtime_movies"] 		= $data['showtime'];
					update_post_meta($post->ID, '_halim_metabox_options', $_halim_metabox_options);

					// Re-Update Episodes
					$list_episode = get_list_episode_nguonc($sourcePage, $post->ID);
					$result = array(
						'status'				=> true,
						'post_id' 			=> $post->ID,
						'data'					=> $data,
						'list_episode' 	=> $list_episode,
						'wait'					=> true,
						'schedule_code' => SCHEDULE_CRAWLER_TYPE_UPDATE
					);
					wp_update_post($post);
					return json_encode($result);
				endwhile;
			endif;
		}
		
		// $api_url 		= str_replace('ophim.tv', 'ophim1.com', $url);
		$sourcePage =  HALIMHelper::cURL($url);
		$sourcePage = json_decode($sourcePage, true);
		$data 			= create_data_nguonc($sourcePage, $url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry);
		if($data['crawl_filter']) {
			$result = array(
				'status'				=> false,
				'post_id' 			=> null,
				'data'					=> null,
				'list_episode' 	=> null,
				'msg' 					=> "Lọc bỏ qua",
				'wait'					=> false,
				'schedule_code' => SCHEDULE_CRAWLER_TYPE_FILTER
			);
			return json_encode($result);
		}

		$post_id 		= add_posts($data);
		$list_episode = get_list_episode_nguonc($sourcePage, $post_id);
		$result = array(
			'status'				=> true,
			'post_id' 			=> $post_id,
			'data'					=> $data,
			'list_episode' 	=> $list_episode,
			'wait'					=> true,
			'schedule_code' => SCHEDULE_CRAWLER_TYPE_INSERT
		);
		return json_encode($result);
  } catch (Exception $e) {
		$result = array(
			'status'				=> false,
			'post_id' 			=> null,
			'data'					=> null,
			'list_episode' 	=> null,
			'msg' 					=> $e->getMessage(),
			'wait'					=> false,
			'schedule_code' => SCHEDULE_CRAWLER_TYPE_ERROR
		);
		return json_encode($result);
  }
}
// end action crawl nguonc movies
add_action('wp_ajax_crawl_ophim_movies', 'crawl_ophim_movies');
function crawl_ophim_movies()
{
	$data_post 					= $_POST['url'];
	$url 								= explode('|', $data_post)[0];
	$ophim_id 					= explode('|', $data_post)[1];
	$ophim_update_time 	= explode('|', $data_post)[2];
	$title 							= explode('|', $data_post)[3];
	$org_title 					= explode('|', $data_post)[4];
	$year 							= explode('|', $data_post)[5];
	
	$filterType 				= $_POST['filterType'] ?: [];
	$filterCategory 		= $_POST['filterCategory'] ?: [];
	$filterCountry 			= $_POST['filterCountry'] ?: [];

	$result = crawl_ophim_movies_handle($url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry);
	echo $result;
	die();
}

function crawl_ophim_movies_handle($url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry)
{
	try {
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_halim_metabox_options',
					'value' => $ophim_id,
					'compare' => 'LIKE'
				)
			)
		);
		$wp_query = new WP_Query($args);
		$total = $wp_query->found_posts;

		if ($total > 0) { # Trường hợp đã có

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => '_halim_metabox_options',
						'value' => $ophim_id,
						'compare' => 'LIKE'
					)
				)
			);
			$wp_query = new WP_Query($args);
			if ($wp_query->have_posts()) : while ($wp_query->have_posts()) : $wp_query->the_post();
					global $post;
					$_halim_metabox_options = get_post_meta($post->ID, '_halim_metabox_options', true);
					if($_halim_metabox_options["fetch_ophim_update_time"] == $ophim_update_time) { // Không có gì cần cập nhật
						$result = array(
							'status'   			=> true,
							'post_id' 			=> null,
							'list_episode' 	=> [],
							'msg' 					=> 'Nothing needs updating!',
							'wait'					=> false,
							'schedule_code' => SCHEDULE_CRAWLER_TYPE_NOTHING
						);
						return json_encode($result);
					}

					$api_url 			= str_replace('ophim.tv', 'ophim1.com', $url);
					$sourcePage 	=  HALIMHelper::cURL($api_url);
					$sourcePage 	= json_decode($sourcePage, true);
					$data 				= create_data($sourcePage, $url, $ophim_id, $ophim_update_time);

					$status = getStatus($data['status']);

					// Re-Update Movies Info
					$formality 																					= ($data['type'] == 'tv_series') ? 'tv_series' : 'single_movies';
					$_halim_metabox_options["halim_movie_formality"] 		= $formality;
					$_halim_metabox_options["halim_movie_status"] 			= $status;
					$_halim_metabox_options["fetch_info_url"] 					= $data['fetch_url'];
					$_halim_metabox_options["fetch_ophim_update_time"] 	= $data['fetch_ophim_update_time'];
					$_halim_metabox_options["halim_original_title"] 		= $data['org_title'];
					$_halim_metabox_options["halim_trailer_url"] 				= $data['trailer_url'];
					$_halim_metabox_options["halim_runtime"] 						= $data['duration'];
					$_halim_metabox_options["halim_episode"] 						= $data['episode'];
					$_halim_metabox_options["halim_total_episode"] 			= $data['total_episode'];
					$_halim_metabox_options["halim_quality"] 						= $data['lang'] . ' - ' . $data['quality'];
					$_halim_metabox_options["halim_showtime_movies"] 		= $data['showtime'];
					update_post_meta($post->ID, '_halim_metabox_options', $_halim_metabox_options);

					// Re-Update Episodes
					$list_episode = get_list_episode($sourcePage, $post->ID);
					$result = array(
						'status'				=> true,
						'post_id' 			=> $post->ID,
						'data'					=> $data,
						'list_episode' 	=> $list_episode,
						'wait'					=> true,
						'schedule_code' => SCHEDULE_CRAWLER_TYPE_UPDATE
					);
					wp_update_post($post);
					return json_encode($result);
				endwhile;
			endif;
		}

		$api_url 		= str_replace('ophim.tv', 'ophim1.com', $url);
		$sourcePage =  HALIMHelper::cURL($api_url);
		$sourcePage = json_decode($sourcePage, true);
		$data 			= create_data($sourcePage, $url, $ophim_id, $ophim_update_time, $filterType, $filterCategory, $filterCountry);
		if($data['crawl_filter']) {
			$result = array(
				'status'				=> false,
				'post_id' 			=> null,
				'data'					=> null,
				'list_episode' 	=> null,
				'msg' 					=> "Lọc bỏ qua",
				'wait'					=> false,
				'schedule_code' => SCHEDULE_CRAWLER_TYPE_FILTER
			);
			return json_encode($result);
		}

		$post_id 		= add_posts($data);
		$list_episode = get_list_episode($sourcePage, $post_id);
		$result = array(
			'status'				=> true,
			'post_id' 			=> $post_id,
			'data'					=> $data,
			'list_episode' 	=> $list_episode,
			'wait'					=> true,
			'schedule_code' => SCHEDULE_CRAWLER_TYPE_INSERT
		);
		return json_encode($result);
  } catch (Exception $e) {
		$result = array(
			'status'				=> false,
			'post_id' 			=> null,
			'data'					=> null,
			'list_episode' 	=> null,
			'msg' 					=> $e->getMessage(),
			'wait'					=> false,
			'schedule_code' => SCHEDULE_CRAWLER_TYPE_ERROR
		);
		return json_encode($result);
  }
}

// function create data nguonc ========================================================================================================
function create_data_nguonc($sourcePage, $url, $ophim_id, $ophim_update_time, $filterType = [], $filterCategory = [], $filterCountry = []) {
	// if(in_array($sourcePage["movie"]["type"], $filterType))  {
	// 	return array(
	// 		'crawl_filter' => true,
	// 	);
	// }

	if($sourcePage["movie"]["category"]["1"]["list"][0]["name"] == "Phim lẻ") {
		$type = "single_movies";
	} else {
		$type	= "tv_series";
	}

	$arrCat = [];
	foreach ($sourcePage["movie"]["category"]["2"]["list"] as $key => $value) {
		if(in_array($value["name"], $filterCategory))  {
			return array(
				'crawl_filter' => true,
			);
		}
		array_push($arrCat, $value["name"]);
	}
	if($sourcePage["movie"]["chieurap"] == true) {
		array_push($arrCat, "Chiếu Rạp");
	}
	if($sourcePage["movie"]["type"] == "hoathinh") {
		array_push($arrCat, "Hoạt Hình");
		$type = (count(reset($sourcePage["episodes"])['server_data'] ?? []) > 1 ? 'series' : 'single');
	}
	if($sourcePage["movie"]["type"] == "tvshows") {
		array_push($arrCat, "TV Shows");
	}

	$arrCountry 	= [];
	foreach ($sourcePage["movie"]["category"]["4"]["list"] as $key => $value) {
		if(in_array($value["name"], $filterCountry))  {
			return array(
				'crawl_filter' => true,
			);
		}
		array_push($arrCountry, $value["name"]);
	}

	$arrTags 			= [];
	array_push($arrTags, $sourcePage["movie"]["name"]);
	if($sourcePage["movie"]["name"] != $sourcePage["movie"]["original_name"]) array_push($arrTags, $sourcePage["movie"]["original_name"]);
	$status = getStatusNguonc($sourcePage["movie"]["current_episode"]);
	$content= sprintf('%s là một bộ phim %s  %s được sản xuất vào năm %s. %s',$sourcePage["movie"]["name"],$sourcePage["movie"]["category"]["2"]["list"][0]["name"],$sourcePage["movie"]["category"]["4"]["list"][0]["name"],$sourcePage["movie"]["category"]["3"]["list"][0]["name"],preg_replace('/\\r?\\n/s', '', $sourcePage["movie"]["description"]));
	$data = array(
		'crawl_filter'						=> false,
		'fetch_url' 							=> $url,
		'fetch_ophim_id' 					=> $ophim_id,
		'fetch_ophim_update_time' => $ophim_update_time,
		'title'     							=> $sourcePage["movie"]["name"],
		'org_title' 							=> $sourcePage["movie"]["original_name"],
		'thumbnail' 							=> $sourcePage["movie"]["thumb_url"],
		'poster'   		 						=> $sourcePage["movie"]["poster_url"],
		'trailer_url'   		 			=> $sourcePage["movie"]["trailer_url"],
		'episode'									=> $sourcePage["movie"]["current_episode"],
		'total_episode'						=> $sourcePage["movie"]["total_episodes"],
		'tags'      							=> $arrTags,
		// 'content'   							=> preg_replace('/\\r?\\n/s', '', $sourcePage["movie"]["description"]),
		'content'   							=> $content,
		'actor'										=> implode(',', $sourcePage["movie"]["casts"]),
		'director'								=> implode(',', $sourcePage["movie"]["director"]),
		'country'									=> $arrCountry,
		'cat'											=> $arrCat,
		'type'										=> $type,
		'lang'										=> $sourcePage["movie"]["language"],
		'showtime'								=> $sourcePage["movie"]["time"],
		'year'										=> $sourcePage["movie"]["category"]["3"]["list"][0]["name"],
		'status'									=> $status,
		'duration'								=> $sourcePage["movie"]["time"],
		'quality'									=> $sourcePage["movie"]["quality"]
	);
	return $data;
}
// end function create data nguonc ========================================================================================================
function create_data($sourcePage, $url, $ophim_id, $ophim_update_time, $filterType = [], $filterCategory = [], $filterCountry = []) {
	if(in_array($sourcePage["movie"]["type"], $filterType))  {
		return array(
			'crawl_filter' => true,
		);
	}

	if($sourcePage["movie"]["type"] == "single") {
		$type = "single_movies";
	} else {
		$type	= "tv_series";
	}

	$arrCat = [];
	foreach ($sourcePage["movie"]["category"] as $key => $value) {
		if(in_array($value["name"], $filterCategory))  {
			return array(
				'crawl_filter' => true,
			);
		}
		array_push($arrCat, $value["name"]);
	}
	if($sourcePage["movie"]["chieurap"] == true) {
		array_push($arrCat, "Chiếu Rạp");
	}
	if($sourcePage["movie"]["type"] == "hoathinh") {
		array_push($arrCat, "Hoạt Hình");
		$type = (count(reset($sourcePage["episodes"])['server_data'] ?? []) > 1 ? 'series' : 'single');
	}
	if($sourcePage["movie"]["type"] == "tvshows") {
		array_push($arrCat, "TV Shows");
	}

	$arrCountry 	= [];
	foreach ($sourcePage["movie"]["country"] as $key => $value) {
		if(in_array($value["name"], $filterCountry))  {
			return array(
				'crawl_filter' => true,
			);
		}
		array_push($arrCountry, $value["name"]);
	}

	$arrTags 			= [];
	array_push($arrTags, $sourcePage["movie"]["name"]);
	if($sourcePage["movie"]["name"] != $sourcePage["movie"]["origin_name"]) array_push($arrTags, $sourcePage["movie"]["origin_name"]);

	$data = array(
		'crawl_filter'						=> false,
		'fetch_url' 							=> $url,
		'fetch_ophim_id' 					=> $ophim_id,
		'fetch_ophim_update_time' => $ophim_update_time,
		'title'     							=> $sourcePage["movie"]["name"],
		'org_title' 							=> $sourcePage["movie"]["origin_name"],
		'thumbnail' 							=> $sourcePage["movie"]["thumb_url"],
		'poster'   		 						=> $sourcePage["movie"]["poster_url"],
		'trailer_url'   		 			=> $sourcePage["movie"]["trailer_url"],
		'episode'									=> $sourcePage["movie"]["episode_current"],
		'total_episode'						=> $sourcePage["movie"]["episode_total"],
		'tags'      							=> $arrTags,
		'content'   							=> preg_replace('/\\r?\\n/s', '', $sourcePage["movie"]["content"]),
		'actor'										=> implode(',', $sourcePage["movie"]["actor"]),
		'director'								=> implode(',', $sourcePage["movie"]["director"]),
		'country'									=> $arrCountry,
		'cat'											=> $arrCat,
		'type'										=> $type,
		'lang'										=> $sourcePage["movie"]["lang"],
		'showtime'								=> $sourcePage["movie"]["showtime"],
		'year'										=> $sourcePage["movie"]["year"],
		'status'									=> $sourcePage["movie"]["status"],
		'duration'								=> $sourcePage["movie"]["time"],
		'quality'									=> $sourcePage["movie"]["quality"]
	);

	return $data;
}

function add_posts($data)
{
	$director  = explode(',', sanitize_text_field($data['director']));
	$actor     = explode(',', sanitize_text_field($data['actor']));

	$cat_id = array();
	foreach ($data['cat'] as $cat) {
		if (!category_exists($cat) && $cat != '') {
			wp_create_category($cat);
		}
		$cat_id[] = get_cat_ID($cat);
	}
	foreach ($data['tags'] as $tag) {
		if (!term_exists($tag) && $tag != '') {
			wp_insert_term($tag, 'post_tag');
		}
	}
	$formality = ($data['type'] == 'tv_series') ? 'tv_series' : 'single_movies';
	$post_data = array(
		'post_title'   		=> $data['title'],
		'post_content' 		=> $data['content'],
		'post_status'  		=> 'publish',
		'comment_status' 	=> 'closed',
		'ping_status'  		=> 'closed',
		'post_author'  		=> get_current_user_id()
	);
	$post_id 						= wp_insert_post($post_data);

	if($data['poster'] && $data['poster'] != "") {
		$res 								= save_images($data['poster'], $post_id, $data['title']);
		$poster_image_url 	= str_replace(get_site_url(), '', $res['url']);
	}
	save_images($data['thumbnail'], $post_id, $data['title'], true);
	$thumb_image_url 		= get_the_post_thumbnail_url($post_id, 'movie-thumb');

	$status = getStatus($data['status']);
	wp_set_object_terms($post_id, $status, 'status', false);

	$post_format 				= halim_get_post_format_type($formality);
	set_post_format($post_id, $post_format);

	$post_meta_movies = array(
		'halim_movie_formality' 		=> $formality,
		'halim_movie_status'    		=> $status,
		'fetch_info_url'						=> $data['fetch_url'],
		'fetch_ophim_id'						=> $data['fetch_ophim_id'],
		'fetch_ophim_update_time'		=> $data['fetch_ophim_update_time'],
		'halim_poster_url'      		=> $poster_image_url,
		'halim_thumb_url'       		=> $thumb_image_url,
		'halim_original_title'			=> $data['org_title'],
		'halim_trailer_url' 				=> $data['trailer_url'],
		'halim_runtime'							=> $data['duration'],
		'halim_rating' 							=> '',
		'halim_votes' 							=> '',
		'halim_episode'         		=> $data['episode'],
		'halim_total_episode' 			=> $data['total_episode'],
		'halim_quality'         		=> $data['lang'] . ' - ' . $data['quality'],
		'halim_movie_notice' 				=> '',
		'halim_showtime_movies' 		=> $data['showtime'],
		'halim_add_to_widget' 			=> false,
		'save_poster_image' 				=> false,
		'set_reatured_image' 				=> false,
		'save_all_img' 							=> false,
		'is_adult' 									=> false,
		'is_copyright' 							=> false,
	);

	$default_episode     									= array();
	$ep_sv_add['halimmovies_server_name'] = "Server #1";
	$ep_sv_add['halimmovies_server_data'] = array();
	array_push($default_episode, $ep_sv_add);

	wp_set_object_terms($post_id, $director, 'director', false);
	wp_set_object_terms($post_id, $actor, 'actor', false);
	wp_set_object_terms($post_id, sanitize_text_field($data['year']), 'release', false);
	wp_set_object_terms($post_id, $data['country'], 'country', false);
	wp_set_post_terms($post_id, $data['tags']);
	wp_set_post_categories($post_id, $cat_id);
	update_post_meta($post_id, '_halim_metabox_options', $post_meta_movies);
	update_post_meta($post_id, '_halimmovies', json_encode($default_episode, JSON_UNESCAPED_UNICODE));
	update_post_meta($post_id, '_edit_last', 1);
	return $post_id;
}

function save_images($image_url, $post_id, $posttitle, $set_thumb = false)
{
	// $image_url = str_replace('img.ophim1.com', 'img.hiephanhthienha.com', $image_url);
	// Khởi tạo curl để tải về hình ảnh
	$ch = curl_init($image_url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36");
	$file = curl_exec($ch);
	curl_close($ch);

	$postname 		= sanitize_title($posttitle);
	$im_name 			= "$postname-$post_id.jpg";
	$res 					= wp_upload_bits($im_name, '', $file);
	insert_attachment($res['file'], $post_id, $set_thumb);
	return $res;
}

function insert_attachment($file, $post_id, $set_thumb)
{
	$dirs 							= wp_upload_dir();
	$filetype 					= wp_check_filetype($file);
	$attachment 				= array(
		'guid' 						=> $dirs['baseurl'] . '/' . _wp_relative_upload_path($file),
		'post_mime_type' 	=> $filetype['type'],
		'post_title' 			=> preg_replace('/\.[^.]+$/', '', basename($file)),
		'post_content' 		=> '',
		'post_status' 		=> 'inherit'
	);
	$attach_id 					= wp_insert_attachment($attachment, $file, $post_id);
	$attach_data 				= wp_generate_attachment_metadata($attach_id, $file);
	wp_update_attachment_metadata($attach_id, $attach_data);
	if ($set_thumb != false) set_post_thumbnail($post_id, $attach_id);
	return $attach_id;
}
// Function get list episode Nguonc =================================================================
function get_list_episode_nguonc($sourcePage, $post_id)
{
	# Xử lý episodes
	$server_add = array();
	if ($sourcePage["movie"]["episodes"][0]["items"][0]["m3u8"] !== "") {
		foreach ($sourcePage["movie"]["episodes"] as $key => $servers) {
			$server_info["halimmovies_server_name"] = $servers["server_name"];
			$server_info["halimmovies_server_data"] = array();

			foreach ($servers["items"] as $episode) {
				$slug_array 											= slugify($episode["name"], '_');
				$slug_ep 													= sanitize_title($episode["name"]);
				$episode["link_m3u8"]							= str_replace('http:', 'https:', $episode["m3u8"]);
				$episode["link_embed"]						= str_replace('http:', 'https:', $episode["embed"]);

				$ep_data['halimmovies_ep_name'] 	= $episode["name"];
				$ep_data['halimmovies_ep_slug'] 	= $slug_ep;
				$ep_data['halimmovies_ep_type'] 	= 'link';
				$ep_data['halimmovies_ep_link'] 	= $episode["m3u8"];
				$ep_data['halimmovies_ep_subs'] 	= array();
				$ep_data['halimmovies_ep_listsv'] = array();
				# Sử dụng link embed làm server dự phòng.
				$subServerData = array(
					"halimmovies_ep_listsv_link" => $episode["embed"],
					"halimmovies_ep_listsv_type" => "embed",
					"halimmovies_ep_listsv_name" => "#Dự Phòng"
				);
				array_push($ep_data['halimmovies_ep_listsv'], $subServerData);
				
				$server_info["halimmovies_server_data"][$slug_array] = $ep_data;
			}
			array_push($server_add, $server_info);
		}
		update_post_meta($post_id, '_halimmovies', json_encode($server_add, JSON_UNESCAPED_UNICODE));
	}
	return json_encode($server_add);
}
// End function get list episode Nguonc ===============================================================
function get_list_episode($sourcePage, $post_id)
{
	# Xử lý episodes
	$server_add = array();
	if ($sourcePage["episodes"][0]["server_data"][0]["link_m3u8"] !== "") {
		foreach ($sourcePage["episodes"] as $key => $servers) {
			$server_info["halimmovies_server_name"] = $servers["server_name"];
			$server_info["halimmovies_server_data"] = array();

			foreach ($servers["server_data"] as $episode) {
				$slug_array 											= slugify($episode["name"], '_');
				$slug_ep 													= sanitize_title($episode["name"]);
				$episode["link_m3u8"]							= str_replace('http:', 'https:', $episode["link_m3u8"]);
				$episode["link_embed"]						= str_replace('http:', 'https:', $episode["link_embed"]);

				$ep_data['halimmovies_ep_name'] 	= $episode["name"];
				$ep_data['halimmovies_ep_slug'] 	= $slug_ep;
				$ep_data['halimmovies_ep_type'] 	= 'link';
				$ep_data['halimmovies_ep_link'] 	= $episode["link_m3u8"];
				$ep_data['halimmovies_ep_subs'] 	= array();
				$ep_data['halimmovies_ep_listsv'] = array();
				# Sử dụng link embed làm server dự phòng.
				$subServerData = array(
					"halimmovies_ep_listsv_link" => $episode["link_embed"],
					"halimmovies_ep_listsv_type" => "embed",
					"halimmovies_ep_listsv_name" => "#Dự Phòng"
				);
				array_push($ep_data['halimmovies_ep_listsv'], $subServerData);
				
				$server_info["halimmovies_server_data"][$slug_array] = $ep_data;
			}
			array_push($server_add, $server_info);
		}
		update_post_meta($post_id, '_halimmovies', json_encode($server_add, JSON_UNESCAPED_UNICODE));
	}
	return json_encode($server_add);
}

function slugify($str, $divider = '-')
{
	$str = trim(mb_strtolower($str));
	$str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
	$str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
	$str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
	$str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
	$str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
	$str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
	$str = preg_replace('/(đ)/', 'd', $str);
	$str = preg_replace('/[^a-z0-9-\s]/', '', $str);
	$str = preg_replace('/([\s]+)/', $divider, $str);
	return $str;
}

function getStatus($status) {
	$hl_status = "completed";
	switch (strtolower($status)) {
		case 'ongoing':
			$hl_status = "ongoing";
			break;
		case 'completed':
			$hl_status = "completed";
			break;
		default:
			$hl_status = "is_trailer";
			break;
	}
	return $hl_status;
}
// functuon getStatus Nguonc =====================================================================
function getStatusNguonc($status) {
	$newStatus= slugify($status,'_');
	$hl_status = "completed";
	if (strpos($newStatus, 'tap')!==false) {
		$hl_status = "ongoing";
	} elseif (strpos($newStatus, 'hoan')!==false) {
		$hl_status = "completed";
	}
	elseif (strpos($newStatus, 'full')!==false) {
		$hl_status = "completed";
	}else{
		$hl_status = "is_trailer";
	}
	return $hl_status;
}
// End functuon getStatus Nguonc ===============================================================