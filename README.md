# Afilink Extractor

## ダウンロード

[最新リリースページ](https://github.com/ddryo/afilink-extractor/releases/latest)からダウンロードしてください。

<img width="1338" alt="downloadlink" src="https://github.com/ddryo/afilink-extractor/assets/31400297/0d2b5b54-ee3c-41de-b8bc-aafddb9b6e5e">

## 使い方

管理メニューからボタンを押すだけです。

<img width="774" alt="adminmenu" src="https://github.com/ddryo/afilink-extractor/assets/31400297/2ab98ed2-6102-4e47-89da-a38c8ce95845">

## 注意点

再利用ブロックやショートコードでアフィリエイトリンクを管理していても正常に検出できるような仕組みになっています。  
また、a タグの中に img タグがあるものはそこからプログラム ID を抽出し、img タグが見つからない場合はアフィリエイトリンクのリダイレクト先を調べて、プログラム ID を抽出します。

これらの処理により、記事数・a8 リンク数が多い場合は処理が重たくなるのでご注意ください。もしかすると動作しないかもしれません。

## バグ報告

この github の issue へお願いします。
