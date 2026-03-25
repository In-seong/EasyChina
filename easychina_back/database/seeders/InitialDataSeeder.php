<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use App\Models\PhraseCategory;
use App\Models\TipCategory;
use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 관리자 계정
        Admin::create([
            'name' => '관리자',
            'email' => 'admin@easychina.app',
            'password' => 'password',
            'role' => 'SUPER',
        ]);

        // 주요 도시
        $cities = [
            ['name_ko' => '베이징', 'name_cn' => '北京', 'name_en' => 'Beijing', 'pinyin' => 'Běijīng', 'latitude' => 39.9042000, 'longitude' => 116.4074000, 'sort_order' => 1],
            ['name_ko' => '상하이', 'name_cn' => '上海', 'name_en' => 'Shanghai', 'pinyin' => 'Shànghǎi', 'latitude' => 31.2304000, 'longitude' => 121.4737000, 'sort_order' => 2],
            ['name_ko' => '광저우', 'name_cn' => '广州', 'name_en' => 'Guangzhou', 'pinyin' => 'Guǎngzhōu', 'latitude' => 23.1291000, 'longitude' => 113.2644000, 'sort_order' => 3],
            ['name_ko' => '청두', 'name_cn' => '成都', 'name_en' => 'Chengdu', 'pinyin' => 'Chéngdū', 'latitude' => 30.5728000, 'longitude' => 104.0668000, 'sort_order' => 4],
            ['name_ko' => '시안', 'name_cn' => '西安', 'name_en' => "Xi'an", 'pinyin' => "Xī'ān", 'latitude' => 34.3416000, 'longitude' => 108.9398000, 'sort_order' => 5],
            ['name_ko' => '항저우', 'name_cn' => '杭州', 'name_en' => 'Hangzhou', 'pinyin' => 'Hángzhōu', 'latitude' => 30.2741000, 'longitude' => 120.1551000, 'sort_order' => 6],
            ['name_ko' => '충칭', 'name_cn' => '重庆', 'name_en' => 'Chongqing', 'pinyin' => 'Chóngqìng', 'latitude' => 29.4316000, 'longitude' => 106.9123000, 'sort_order' => 7],
            ['name_ko' => '선전', 'name_cn' => '深圳', 'name_en' => 'Shenzhen', 'pinyin' => 'Shēnzhèn', 'latitude' => 22.5431000, 'longitude' => 114.0579000, 'sort_order' => 8],
        ];
        foreach ($cities as $city) {
            City::create($city);
        }

        // 장소 카테고리
        $categories = [
            ['name_ko' => '관광지', 'name_cn' => '景点', 'icon' => 'landmark', 'color' => '#FF6B6B', 'sort_order' => 1],
            ['name_ko' => '맛집', 'name_cn' => '美食', 'icon' => 'utensils', 'color' => '#FFA94D', 'sort_order' => 2],
            ['name_ko' => '카페', 'name_cn' => '咖啡厅', 'icon' => 'coffee', 'color' => '#A0522D', 'sort_order' => 3],
            ['name_ko' => '쇼핑', 'name_cn' => '购物', 'icon' => 'shopping-bag', 'color' => '#CC5DE8', 'sort_order' => 4],
            ['name_ko' => '호텔', 'name_cn' => '酒店', 'icon' => 'bed', 'color' => '#339AF0', 'sort_order' => 5],
            ['name_ko' => '편의시설', 'name_cn' => '便利设施', 'icon' => 'store', 'color' => '#20C997', 'sort_order' => 6],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 팁 카테고리
        $tipCategories = [
            ['name' => '입국 준비', 'icon' => 'clipboard-check', 'sort_order' => 1],
            ['name' => '교통 가이드', 'icon' => 'train', 'sort_order' => 2],
            ['name' => '필수 앱 설치', 'icon' => 'smartphone', 'sort_order' => 3],
            ['name' => '결제 설정', 'icon' => 'credit-card', 'sort_order' => 4],
            ['name' => 'VPN & 인터넷', 'icon' => 'wifi', 'sort_order' => 5],
            ['name' => '환율 정보', 'icon' => 'dollar-sign', 'sort_order' => 6],
            ['name' => '긴급 연락처', 'icon' => 'phone', 'sort_order' => 7],
            ['name' => '주의사항 & 에티켓', 'icon' => 'alert-triangle', 'sort_order' => 8],
            ['name' => '생존 중국어', 'icon' => 'message-circle', 'sort_order' => 9],
        ];
        foreach ($tipCategories as $tc) {
            TipCategory::create($tc);
        }

        // 번역카드 카테고리
        $phraseCategories = [
            ['name' => '식당', 'icon' => 'utensils', 'sort_order' => 1],
            ['name' => '택시', 'icon' => 'car', 'sort_order' => 2],
            ['name' => '쇼핑', 'icon' => 'shopping-bag', 'sort_order' => 3],
            ['name' => '호텔', 'icon' => 'bed', 'sort_order' => 4],
            ['name' => '긴급상황', 'icon' => 'alert-circle', 'sort_order' => 5],
            ['name' => '인사/기본', 'icon' => 'hand', 'sort_order' => 6],
            ['name' => '교통', 'icon' => 'map', 'sort_order' => 7],
            ['name' => '관광', 'icon' => 'camera', 'sort_order' => 8],
        ];
        foreach ($phraseCategories as $pc) {
            PhraseCategory::create($pc);
        }
    }
}
