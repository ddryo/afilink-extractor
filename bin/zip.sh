#!/bin/bash

version=$1
version=${version//-/.}

# DS_Store削除
find . -name '.DS_Store' -type f -ls -delete

#上の階層へ
cd ../

# 不要なファイルを除いてzip化
zip -r afilink-extractor-${1}.zip afilink-extractor -x "*/.*" "*/__*" "*/bin*" "*/node_modules*" "*/version.json" "*/package.json" "*/package-lock.json"

# src削除
# zip -r listup-afilink.zip listup-afilink -x "listup-afilink/src/img*" "listup-afilink/src/js*" "listup-afilink/src/scss*" "listup-afilink/src/gutenberg/*.js" "listup-afilink/src/gutenberg/*.js" "!listup-afilink/src/(dir)"

# zipから不要なファイルを削除
# zip --delete listup-afilink.zip  "listup-afilink/.*"
# zip --delete listup-afilink.zip  "*/src/gutenberg/components*" "*/src/gutenberg/extension*" "*/src/gutenberg/format*" "*/src/gutenberg/hoc*" "*/src/gutenberg/utils*"
