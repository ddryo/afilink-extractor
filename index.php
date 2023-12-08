<?php
/**
 * Plugin Name: Afilink Extractor
 * Description: アフィリエイトリンク(a8) を抽出するプラグイン
 * Version: 1.0.0
 * Author: Ryo
 * Author URI: https://twitter.com/ddryo_loos
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ls-afilist
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

/**
 * menu 追加
 */
add_action( 'admin_menu', function () {
	add_menu_page(
		'a8リンク抽出', // ページタイトルタグ
		'a8リンク抽出', // メニュータイトル
		'manage_options',  // 必要な権限
		'ls-afilink-extractor', // このメニューを参照するスラッグ名
		'ls_afiext__dashboard_site_status', // 表示内容
		'', // アイコン
		// 29 // 管理画面での表示位置
	);
} );

function ls_afiext__checknonce() {
	return wp_verify_nonce( $_POST['ls-afiext_nonce'], 'ls-afiext' );
}

function ls_afiext__dashboard_site_status() {
	require_once __DIR__ . '/inc/get_all_links.php';
	$cache = false;
	if ( isset( $_POST['ls-afiext-delete-cache'] ) && ls_afiext__checknonce() ) {
		delete_transient( 'ls_afiext_data_cache' );
	}

	?>
	<style>
		.wrap{padding-block:2rem}
		.toolBtns{margin-block:1.5em; display:flex; gap:1em;}
		.confArea{padding: 1.5em;background: #fff;border: solid 1px #ddd;}
		.confTable :is(td,th){padding: 2px 6px; border:solid 1px gray}
	</style>
	<?php
	echo '<div class="wrap">';

	$cache = get_transient('ls_afiext_data_cache');
	$has_cache = !!$cache;
	$ext_label = $has_cache ? 'リンクを再抽出' : 'リンクを抽出';
	$table_caption = $has_cache ? 'キャッシュ済みデータ (24時間保存されます。)' : '新規抽出データ';

	echo '<form method="post">';
	wp_nonce_field( 'ls-afiext', 'ls-afiext_nonce' );

	echo '<div class="toolBtns">';
	echo '<button type="submit" name="ls-afiext-check" class="button button-primary">'.$ext_label.'</button>';
	echo '<button type="submit" name="ls-afiext-download" class="button">CSVダウンロード</button>';
	echo '</div>';


	if($has_cache) {
		$link_list = $cache;
	}

	if ( isset( $_POST['ls-afiext-check'] ) && ls_afiext__checknonce() ) {
		delete_transient( 'ls_afiext_data_cache' );
		try {
			$link_list = \LOOS\CSV\get_a8links();
			$table_caption = '新規抽出データ';
		} catch (\Throwable $th) {
			echo '<p>エラーが発生しました</p>';
			echo '<p>' . $th->getMessage() . '</p>';
		}
	}


	if ( ! empty($link_list) ) {
		echo '<div class="confArea">';

		
		echo '<table class="confTable">';
		if ($table_caption) {
			echo '<caption>' . $table_caption . '</caption>';
		}
		echo '<tr><th>記事url</th><th>プログラムID</th><th>使用リンク</th></tr>';
		foreach ($link_list as $link) {
			echo '<tr>';
			foreach ($link as $value) {
				echo '<td>' . $value . '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';


		if( $has_cache ) {
			echo '<div class="toolBtns">';
				echo '<button type="submit" name="ls-afiext-delete-cache" class="button">キャッシュ削除</button>';
			echo '</div>';
		}
		echo '</form>';

	}
	

	echo '</div>';
}


add_action('admin_init', function() {
	if ( ! isset( $_POST['ls-afiext-download'] ) ) return;
	if ( ! ls_afiext__checknonce() ) return;

	try {
		require_once __DIR__ . '/inc/get_all_links.php';
		$link_list = \LOOS\CSV\get_a8links();
	} catch (\Throwable $th) {
		echo '<p>エラーが発生しました</p>';
		echo '<p>' . $th->getMessage() . '</p>';
	}

	require_once __DIR__ . '/inc/array_to_csv.php';
	$csv = \LOOS\CSV\array_to_csv($link_list, ['記事url', 'プログラムID', '使用リンク']);

	// 出力
	$filename = 'a8_links_' . wp_date('Ymd') . '.csv';
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment; filename="' . $filename . '";');
	echo $csv;
	exit;
});
