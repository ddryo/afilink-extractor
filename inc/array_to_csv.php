<?php
namespace LOOS\CSV;

function array_to_csv($array, $column_names = []) {
	$fp = fopen('php://temp', 'r+b');

	if ( ! empty($column_names) ) {
		fputcsv($fp, $column_names);
	}

	foreach ($array as $fields) {
		fputcsv($fp, $fields);
	}

	rewind($fp);

	$csv = stream_get_contents($fp);

	// 改行コードを強制的にCRLFに ( RFC 4180 準拠)
	// $csv = str_replace(PHP_EOL, "\r\n", $csv);

	// エンコードして返す
	$to_encoding = 'SJIS'; // SJIS-win の方が本当は良さそうだが今回は必要ない。
	$from_encoding = 'UTF-8'; // 'ASCII,JIS,UTF-8,EUC-JP,eucJP-win,SJIS,SJIS-win'
	return mb_convert_encoding($csv, $to_encoding, $from_encoding);
}
