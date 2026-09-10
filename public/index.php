<?php
// public/index.php

// تضمين ملف التحميل التلقائي الخاص بـ Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Auth;
use App\Schema;
use GraphQL\GraphQL;

// إعداد الترويسات (Headers) لنوع البيانات والسماح بطلبات من واجهات أخرى (CORS)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// إذا كان الطلب من نوع OPTIONS (للتحقق من الـ CORS من المتصفح)، نوقف التنفيذ هنا
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // 1. إنشاء اتصال بقاعدة البيانات
    $db = new Database();

    // 2. التحقق من المصادقة (التوكن)
    $token = Auth::getBearerToken();
    $user = null;
    if ($token) {
        // إذا وجدنا توكن، نحاول فك تشفيره للتأكد من صلاحيته
        $user = Auth::validateToken($token); 
    }

    // 3. تجهيز السياق (Context)
    // هذا السياق سيتم تمريره تلقائياً لكل دوال الـ resolve التي كتبناها في Schema.php
    $context = [
        'db' => $db,
        'user' => $user
    ];

    // 4. قراءة محتوى الطلب القادم من المستخدم
    // استعلامات GraphQL تأتي عادة على شكل JSON في الـ Body
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    $query = $input['query'] ?? null;
    $variables = $input['variables'] ?? null;

    if (empty($query)) {
        throw new Exception('لم يتم إرسال أي استعلام (Query is missing)');
    }

    // 5. بناء هيكل GraphQL
    $schema = Schema::build();

    // 6. تنفيذ الاستعلام
    $result = GraphQL::executeQuery($schema, $query, null, $context, $variables);
    $output = $result->toArray(\GraphQL\Error\DebugFlag::INCLUDE_DEBUG_MESSAGE | \GraphQL\Error\DebugFlag::INCLUDE_TRACE);

} catch (\Exception $e) {
    // في حال حدوث أي خطأ، نعيده بصيغة JSON تناسب GraphQL
    $output = [
        'errors' => [
            ['message' => $e->getMessage()]
        ]
    ];
}

// 7. طباعة النتيجة النهائية
echo json_encode($output);