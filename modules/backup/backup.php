<?php
$filename = 'backup_' . date('Ymd_His') . '.sql';
$backupPath = __DIR__ . '/../../storage/backups/' . $filename;

if (!is_dir(dirname($backupPath))) {
    mkdir(dirname($backupPath), 0777, true);
}

exec("mysqldump -u root sohoj_hishab > " . escapeshellarg($backupPath));

echo "Backup Complete";
