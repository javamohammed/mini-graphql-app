<?php
namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class Auth {
    // مفتاح سري لتشفير وفك تشفير التوكن (في المشاريع الحقيقية يجب وضعه في ملف .env)
    private static $secret_key = 'MySuperSecretKey123_ThisIsAtLeast32CharsLong!'; 

    /**
     * دالة لتسجيل الدخول وتوليد التوكن (Token)
     */
    public static function login($username, $password, Database $db) {
        $user = $db->getUserByUsername($username);

        // التحقق من وجود المستخدم ومطابقة كلمة المرور
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'iss' => 'mini-graphql-app', // مُصدر التوكن
                'iat' => time(), // وقت الإصدار
                'exp' => time() + (60 * 60), // وقت الانتهاء (بعد ساعة واحدة)
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['username']
                ]
            ];

            // تشفير التوكن وإرجاعه
            return JWT::encode($payload, self::$secret_key, 'HS256');
        }

        return null; // فشل تسجيل الدخول
    }

    /**
     * دالة للتحقق من صحة التوكن وفك تشفيره
     */
    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, 'HS256'));
            return (array) $decoded->data; // إرجاع بيانات المستخدم (user_id و username)
        } catch (Exception $e) {
            return null; // التوكن غير صالح أو انتهت صلاحيته
        }
    }

    /**
     * دالة مساعدة لاستخراج التوكن من الـ Headers الخاصة بالطلب (HTTP Request)
     */
    public static function getBearerToken() {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // سيرفرات مثل Nginx
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        // استخراج التوكن من كلمة Bearer
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}