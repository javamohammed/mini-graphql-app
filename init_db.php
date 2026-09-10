<?php
// init_db.php

try {
    // إنشاء أو الاتصال بملف قاعدة البيانات
    $pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "إنشاء الجداول...\n";

    // إنشاء جدول المستخدمين (Users)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )");

    // إنشاء جدول المنشورات (Posts) - نحتاج العناوين فقط كما طلبت
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // إنشاء جدول المتابعين (Followers)
    $pdo->exec("CREATE TABLE IF NOT EXISTS followers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        follower_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // إنشاء جدول الهاشتاجات (Hashtags) مع عداد لمعرفة الأكثر شيوعاً
    $pdo->exec("CREATE TABLE IF NOT EXISTS hashtags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        usage_count INTEGER DEFAULT 0
    )");

    echo "إدخال بيانات وهمية (Dummy Data)...\n";

    // إدخال مستخدم للتجربة (كلمة المرور: 123456 مشفرة)
    $password = password_hash('123456', PASSWORD_BCRYPT);
    $pdo->exec("INSERT INTO users (username, password) VALUES ('admin', '$password')");
    $userId = $pdo->lastInsertId();

    // إدخال منشورات
    $pdo->exec("INSERT INTO posts (user_id, title) VALUES ($userId, 'My First GraphQL Post')");
    $pdo->exec("INSERT INTO posts (user_id, title) VALUES ($userId, 'Learning PHP and SQLite')");

    // إدخال متابعين (سنحتاج آخر 3)
    $pdo->exec("INSERT INTO followers (user_id, follower_name) VALUES ($userId, 'Ahmed')");
    $pdo->exec("INSERT INTO followers (user_id, follower_name) VALUES ($userId, 'Sara')");
    $pdo->exec("INSERT INTO followers (user_id, follower_name) VALUES ($userId, 'Omar')");
    $pdo->exec("INSERT INTO followers (user_id, follower_name) VALUES ($userId, 'Khalid')"); // هذا الأحدث

    // إدخال هاشتاجات (بأرقام استخدام مختلفة لنجلب أعلى 3)
    $pdo->exec("INSERT INTO hashtags (name, usage_count) VALUES ('#php', 150)");
    $pdo->exec("INSERT INTO hashtags (name, usage_count) VALUES ('#graphql', 200)");
    $pdo->exec("INSERT INTO hashtags (name, usage_count) VALUES ('#sqlite', 50)");
    $pdo->exec("INSERT INTO hashtags (name, usage_count) VALUES ('#coding', 300)");

    echo "تم إعداد قاعدة البيانات بنجاح!\n";

} catch (PDOException $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}