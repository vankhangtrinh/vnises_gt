<?php
// Minimal WordPress stubs for rendering tests.
error_reporting(E_ALL); ini_set('display_errors','1');
define('ABSPATH', __DIR__.'/');
$GLOBALS['__actions_done'] = array(); $GLOBALS['__doing'] = null; $GLOBALS['__shortcodes'] = array(); $GLOBALS['__hooks']=array();
function add_action($h,$cb){ $GLOBALS['__hooks'][$h][]=$cb; }
function do_action($h){ $GLOBALS['__doing']=$h; foreach(($GLOBALS['__hooks'][$h]??array()) as $cb) call_user_func($cb); $GLOBALS['__doing']=null; $GLOBALS['__actions_done'][$h]=1; }
function did_action($h){ return isset($GLOBALS['__actions_done'][$h]) ? 1 : 0; }
function doing_action($h){ return $GLOBALS['__doing']===$h; }
function apply_filters($h,$v){ return $v; }
function add_shortcode($t,$cb){ $GLOBALS['__shortcodes'][$t]=$cb; }
function shortcode_exists($t){ return isset($GLOBALS['__shortcodes'][$t]); }
function shortcode_atts($pairs,$atts,$sc=''){ $out=array(); foreach($pairs as $k=>$d){ $out[$k]=array_key_exists($k,$atts)?$atts[$k]:$d; } return $out; }
function do_shortcode_tag($t,$atts=array()){ return call_user_func($GLOBALS['__shortcodes'][$t],$atts,null,$t); }
function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function esc_url($u){ $u=(string)$u; if(preg_match('#^\s*javascript:#i',$u)) return ''; return htmlspecialchars($u, ENT_QUOTES, 'UTF-8'); }
function absint($v){ return abs((int)$v); }
function home_url($p=''){ return 'https://example.test'.$p; }
function wp_unique_id($p=''){ static $i=0; $i++; return $p.$i; }
function wp_kses($s,$allowed){ // approximate: strip disallowed tags and attributes
  return preg_replace_callback('#<(/?)([a-z0-9]+)([^>]*)>#i', function($m) use($allowed){ $tag=strtolower($m[2]); if(!isset($allowed[$tag])) return ''; if($m[1]) return '</'.$tag.'>'; $attrs=''; if(preg_match_all('#([a-z-]+)="([^"]*)"#i',$m[3],$mm,PREG_SET_ORDER)){ foreach($mm as $a){ if(!empty($allowed[$tag][strtolower($a[1])])) $attrs.=' '.strtolower($a[1]).'="'.$a[2].'"'; } } return '<'.$tag.$attrs.'>'; }, $s);
}
$GLOBALS['__styles']=array();
function is_singular(){ return !empty($GLOBALS['__singular']); }
function get_post(){ return isset($GLOBALS['__post']) ? $GLOBALS['__post'] : null; }
function has_shortcode($c,$t){ return strpos($c,'['.$t)!==false; }
function wp_register_style($h,$src,$deps=array(),$ver=false){ $GLOBALS['__styles'][$h]=''; }
function wp_enqueue_style($h){}
function wp_add_inline_style($h,$css){ $GLOBALS['__styles'][$h].=$css; echo '<style id="'.$h.'-inline-css">'.$css."</style>\n"; }
