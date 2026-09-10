<?php
namespace App;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema as GraphQLSchema;
use Exception;

class Schema {
    
    public static function build() {
        // 1. تعريف شكل البيانات (Types)
        
        $postType = new ObjectType([
            'name' => 'Post',
            'fields' => [
                'title' => Type::string()
            ]
        ]);

        $followerType = new ObjectType([
            'name' => 'Follower',
            'fields' => [
                'follower_name' => Type::string(),
                'created_at' => Type::string()
            ]
        ]);

        $hashtagType = new ObjectType([
            'name' => 'Hashtag',
            'fields' => [
                'name' => Type::string(),
                'usage_count' => Type::int()
            ]
        ]);

        // 2. تعريف الاستعلامات لجلب البيانات (Queries)
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                // جلب منشورات المستخدم (يتطلب تسجيل الدخول)
                'myPosts' => [
                    'type' => Type::listOf($postType),
                    'resolve' => function ($root, $args, $context) {
                        if (!$context['user']) throw new Exception('غير مصرح لك (Unauthorized)');
                        return $context['db']->getPostsByUserId($context['user']['user_id']);
                    }
                ],
                // جلب آخر 3 متابعين (يتطلب تسجيل الدخول)
                'myLastFollowers' => [
                    'type' => Type::listOf($followerType),
                    'resolve' => function ($root, $args, $context) {
                        if (!$context['user']) throw new Exception('غير مصرح لك (Unauthorized)');
                        return $context['db']->getLastThreeFollowers($context['user']['user_id']);
                    }
                ],
                // جلب أشهر 3 هاشتاجات (متاح للجميع)
                'popularHashtags' => [
                    'type' => Type::listOf($hashtagType),
                    'resolve' => function ($root, $args, $context) {
                        return $context['db']->getTopThreeHashtags();
                    }
                ]
            ]
        ]);

        // 3. تعريف الإجراءات (Mutations) - هنا لدينا تسجيل الدخول فقط
        $mutationType = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'login' => [
                    'type' => Type::string(), // سيعيد الـ Token كنص
                    'args' => [
                        'username' => Type::nonNull(Type::string()),
                        'password' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => function ($root, $args, $context) {
                        $token = Auth::login($args['username'], $args['password'], $context['db']);
                        if (!$token) {
                            throw new Exception('بيانات الدخول غير صحيحة');
                        }
                        return $token;
                    }
                ]
            ]
        ]);

        // تجميع كل شيء في Schema واحدة
        return new GraphQLSchema([
            'query' => $queryType,
            'mutation' => $mutationType
        ]);
    }
}