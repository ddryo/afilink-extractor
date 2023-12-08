<?php
namespace LOOS\AfiExt;


function get_update_data() {

	// GitHub APIを使って、Releaseの最新バージョン情報を取得する
	$response = wp_remote_get( 'https://raw.githubusercontent.com/ddryo/afilink-extractor/main/version.json' );

	// レスポンスエラー
	if( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}

	$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
	
	return array(
		'version' => $response_body['version'] ?? null,  // 最新のバージョン
		'package' => $response_body['package'] ?? null,  // zipファイルパッケージのURL
	);
}


function get_readme() {

	$cache = get_transient('ls_afiext_readme_cache');
	if ($cache) return $cache;
	$response = wp_remote_get( 'https://raw.githubusercontent.com/ddryo/afilink-extractor/main/README.md' );

	// レスポンスエラー
	if( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return null;
	}
	$response_body = wp_remote_retrieve_body( $response );

	set_transient( 'ls_afiext_readme_cache', $response_body, 24 * HOUR_IN_SECONDS );
	return $response_body;
}



/**
 * アップデートチェック
 */
add_filter( 'update_plugins_ls-afilink-extractor', function( $update, $plugin_data ) {
	if ( ! current_user_can( 'manage_options' ) ) return $update;
	$update_data = get_update_data();
	if ( ! $update_data ) return $update;

	return array(
		'slug'        => 'ls-afilink-extractor', // plugins_api に必要
		'version'     => $plugin_data['Version'], // 現在のバージョン
		'new_version' => $update_data['version'] ?? '', // 最新のバージョン
		'package'     => $update_data['package'] ?? '', // zipファイルパッケージのURL
	);
}, 10, 2 );


// 詳細表示のポップアップ情報をセット
add_filter( 'plugins_api', function( $res, $action, $args ) {

	if ( 'plugin_information' !== $action ) return $res;
	if ( $args->slug !== 'ls-afilink-extractor' ) return $res;

	// アップデート用のデータを取得
	$update_data = get_update_data();
	if ( ! $update_data ) return $res;

	$readme = get_readme();

	// 改行コードをbrタグに変換
	$readme = nl2br( $readme );

	
	$readme = preg_replace( '/<img([^>]+)>/', '<ul><li><img$1></li></ul>', $readme );

	return (object) array(
		'name' => 'Afilink Extractor',
		'slug' => 'ls-afilink-extractor',
		'path' => 'afilink-extractor/index.php',
		'version' => $update_data['version'] ?? '',
		'download_link' =>  $update_data['package'] ?? '',
		'sections' => array(
			// 'description' => $readme,
			'screenshots' => $readme, // img がはみ出さないようにするために screenshots タブで表示。
			
		),
		// 'banners' => [
		// 		'low'  => '',
		// 		'high' => '',
		// ],
	);
}, 20, 3 );
