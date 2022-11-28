<?php
    require_once 'dbh.inc.php';
    require_once 'vc.functions.php';
    if(!isset($_SESSION)){
        session_start();
     }
    // Nobody can directly go to Administrator.php. Login is necessary.
    if($_SESSION['usersposition'] != 'administrator' && $_SESSION['usersposition'] != 'executive'){
        header("location: ../login.php?error=adminerror");
        exit();
     }

    $year = $_GET['year'];
    
    getBackup($conn, $year);

    // *************** Download *************** //
    // download("test.txt");
     
    // ************* Back to Admin ************ //
    header("location: ./backup.php");


    // *************** Sub-Function of Download *************** //
    function download($pPath, $pMimeType = null)
    {
        //-- ファイルが読めない時はエラー(もっときちんと書いた方が良いが今回は割愛)
        if (!is_readable($pPath)) { die($pPath); }
    
        //-- Content-Typeとして送信するMIMEタイプ(第2引数を渡さない場合は自動判定) ※詳細は後述
        // $mimeType = (isset($pMimeType)) ? $pMimeType
        //                                : (new finfo(FILEINFO_MIME_TYPE))->file($pPath);
    
        //-- 適切なMIMEタイプが得られない時は、未知のファイルを示すapplication/octet-streamとする
        if (!preg_match('/\A\S+?\/\S+/', $mimeType)) {
            $mimeType = 'application/octet-stream';
        }
    
        //-- Content-Type
        header('Content-Type: ' . $mimeType);
    
        //-- ウェブブラウザが独自にMIMEタイプを判断する処理を抑止する
        header('X-Content-Type-Options: nosniff');
    
        //-- ダウンロードファイルのサイズ
        header('Content-Length: ' . filesize($pPath));
    
        //-- ダウンロード時のファイル名
        header('Content-Disposition: attachment; filename="' . basename($pPath) . '"');
    
        //-- keep-aliveを無効にする
        header('Connection: close');
    
        //-- readfile()の前に出力バッファリングを無効化する ※詳細は後述
        while (ob_get_level()) { ob_end_clean(); }
    
        //-- 出力
        readfile($pPath);
    
        //-- 最後に終了させるのを忘れない
        exit;
    }
?>


