<?php
namespace LOOS\AfiExt;

function get_a8links() {

	$cache = get_transient('ls_afiext_data_cache');
	if ($cache) return $cache;


	$args = array(
		'post_type' => array('page', 'post'),
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	);
	$posts = get_posts($args);

	$query = new \WP_Query($args);
	if ( !$query->have_posts() ) return [];

	$link_list = array();
	foreach ($query->posts as $post) {
		$content = $post->post_content;
		if ( ! $content ) continue;
		
		// 再利用ブロックなども展開して検出できるように。
		$content = do_shortcode( do_blocks( $content ) );
		$pageUrl = get_permalink($post->ID);

		// html圧縮
		// $content = str_replace(array("\r\n", "\r", "\n"), '', $content);
		// $content = preg_replace('/\s+/', ' ', $content);

		if (strpos($content, 'a8.net/') !== false) {

			// 非推奨な mb_convert_encoding の代わりに mb_encode_numericentity を使ってhtmlエンコード
			$content = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');

			$old_libxml_error = libxml_use_internal_errors( true );
			$dom = new \DOMDocument();
			$dom->loadHTML( $content );
			libxml_use_internal_errors( $old_libxml_error );

			$links = $dom->getElementsByTagName('a');

			foreach ($links as $link) {
				$href = $link->getAttribute('href');
				if (strpos($href, 'a8.net/') !== false) {
					$linkUrl = $href;
					$programID = '';

					$childImg = $link->getElementsByTagName('img')->item(0);

					if ($childImg) {
						// 内部にimgがあれば src の mid からプログラムIDを取得
						$src = $childImg->getAttribute('src');
						if (preg_match('/&mid=(s\d{14})/', $src, $matches)) {
							$programID = $matches[1];
						}

					} else {
						// リンクのリダイレクト先を調べて末尾が "s"+14桁の数字ならプログラムIDを取得
						$redirect_url = get_redirect_url($linkUrl);
						if (preg_match('/(s\d{14})$/', $redirect_url, $matches)) {
							$programID = $matches[1];
						}
					}

					$link_list[] = array(
						'pageUrl' => $pageUrl,
						'programID' => $programID,
						'linkUrl' => $linkUrl,
					);
				}
			}
		}
	}
	wp_reset_postdata();

	set_transient( 'ls_afiext_data_cache', $link_list, 30 * MINUTE_IN_SECONDS );
	return $link_list;
}


// リダイレクト先を調べる
function get_redirect_url( $url, $check_final = false ) {

	$ch = curl_init( $url );
	curl_setopt_array( $ch, [
		CURLOPT_HEADER => 1,
		CURLOPT_NOBODY => 1,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => $check_final,
		CURLOPT_SSL_VERIFYPEER => 0
	] );
	$resp = curl_exec( $ch );
	
	if ($check_final) {
		// 最終的なURLを取得
		$redirect_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	} else {
		// 初回リダイレクト先を取得
		$redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
	}

	// cURLセッションを閉じる
	curl_close($ch);

	// // 最終的なURL
	return $redirect_url;
}
