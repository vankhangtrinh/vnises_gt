<?php
// Dựng trang HTML tĩnh của module giới thiệu VNISES để quay footage (layer B).
// Dùng: php module_page.php > vnises_page.html
require __DIR__ . '/wp_stub.php';
$GLOBALS['__singular'] = true;
$GLOBALS['__post'] = (object) array( 'post_content' => '[vnises_gioithieu]' );
require __DIR__ . '/../../../vnises-gioithieu.php';
do_action( 'init' );
ob_start();
$GLOBALS['__doing'] = 'wp_head'; do_action( 'wp_enqueue_scripts' ); $GLOBALS['__actions_done']['wp_head'] = 1; $GLOBALS['__doing'] = null;
$head = ob_get_clean();
$body = do_shortcode_tag( 'vnises_gioithieu', array() );
echo "<!DOCTYPE html>\n<html lang=\"vi\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"><title>VNISES</title><style>html,body{margin:0;background:#0b0c0d}</style>$head</head><body>$body</body></html>";
