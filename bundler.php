<?php
// 1. Read .ini config
$config = parse_ini_file("testRabbitMQ.ini", true);
$files = $config['files'];       // Files to bundle
$processes = $config['processes']; 

// 2. Query DB for next version
$pdo = getDatabase(); // From mysqlconnect.php
$version = $pdo->query("SELECT MAX(version)+1 FROM bundles WHERE name='login'")->fetchColumn();

// 3. Create .tgz
$tmpDir = "/tmp/login-v{$version}";
mkdir($tmpDir);
foreach ($files as $file) copy($file, "$tmpDir/" . basename($file));
shell_exec("tar -czf /bundles/login-v{$version}.tgz -C $tmpDir .");

// 4. Notify Deployment System
$client = new rabbitMQClient("deploy.ini", "deploy");
$client->send_request([
    'type' => 'new_bundle',
    'name' => 'login',
    'version' => $version
]);
?>
