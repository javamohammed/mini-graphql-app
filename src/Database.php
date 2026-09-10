<?php
namespace App;

use PDO;
use PDOException;

class Database {
    private $pdo;

    public function __construct() {
        try {
            // المسار إلى ملف قاعدة البيانات الذي أنشأناه في الخطوة السابقة
            $dbPath = __DIR__ . '/../database.sqlite';
            $this->pdo = new PDO('sqlite:' . $dbPath);
            
            // تفعيل التنبيهات في حال وجود أخطاء في SQL
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // جعل النتائج تعود دائماً كمصفوفة (Array)
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // 1. جلب المستخدم للتحقق منه عند تسجيل الدخول (Auth)
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    // 2. جلب عناوين المنشورات لمستخدم معين
    public function getPostsByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT title FROM posts WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // 3. جلب آخر 3 متابعين (مرتبة تنازلياً حسب رقم المعرف ID)
    public function getLastThreeFollowers($userId) {
        $stmt = $this->pdo->prepare("SELECT follower_name, created_at FROM followers WHERE user_id = :user_id ORDER BY id DESC LIMIT 3");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // 4. جلب أشهر 3 هاشتاجات (أعلى رقم في الاستخدام usage_count)
    public function getTopThreeHashtags() {
        $stmt = $this->pdo->query("SELECT name, usage_count FROM hashtags ORDER BY usage_count DESC LIMIT 3");
        return $stmt->fetchAll();
    }
}