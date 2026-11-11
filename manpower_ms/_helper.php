<?php

/**
 * 取得單筆資料
 * @return array<string, mixed>
 */
function getkeyvalue2(string $db, string $table, string $where, string $fields){}

/**
 * 自訂查詢
 * @return array<string, mixed>
 */
function mySelect(string $ch_caption, int $subcontracting_progress){}


/* ==============================
   舊版 mysql_* 函式 stub
   ============================== */

/**
 * @return resource|false
 */
function mysql_pconnect(string $host, string $user, string $password) {}

/**
 * @return string
 */
function mysql_real_escape_string(string $string){}

/**
 * @return resource|false
 */
function mysql_query(string $query, $link_identifier = null) {}

/**
 * @return string
 */
function mysql_error(){}

/**
 * @return array<string, mixed>|false
 */
function mysql_fetch_array($result) {}

/**
 * @return bool
 */
function mysql_select_db(string $database_name, $link_identifier = null){}


/* ==============================
   其他自訂函式
   ============================== */

/**
 * @return array<string, mixed>
 */
function getDataTable_de(){}

/**
 * @return string
 */
function getlang(string $key = ''){}

/**
 * @return string
 */
function utf8_substr(string $string, int $start, int $length){}

/**
 * 會員頭像
 * @return string
 */
function getmemberpict160(string $memberID){}

/**
 * 會員頭像
 * @return string
 */
function mywarning(string $memberID){}

