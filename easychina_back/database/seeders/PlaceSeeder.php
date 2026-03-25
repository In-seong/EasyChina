<?php

namespace Database\Seeders;

use App\Models\Place;
use App\Models\City;
use App\Models\Category;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
        $shanghai = City::where('name_ko', '상하이')->first();
        $beijing = City::where('name_ko', '베이징')->first();
        $chengdu = City::where('name_ko', '청두')->first();
        $hangzhou = City::where('name_ko', '항저우')->first();
        $xian = City::where('name_ko', '시안')->first();
        $guangzhou = City::where('name_ko', '광저우')->first();

        $tourist = Category::where('name_ko', '관광지')->first();
        $food = Category::where('name_ko', '맛집')->first();
        $cafe = Category::where('name_ko', '카페')->first();
        $shopping = Category::where('name_ko', '쇼핑')->first();

        $places = [
            // ========== 상하이 관광지 ==========
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '와이탄 (외탄)', 'name_cn' => '外滩', 'pinyin' => 'Wàitān',
                'name_en' => 'The Bund',
                'address_ko' => '상하이시 황푸구 중산동1로', 'address_cn' => '上海市黄浦区中山东一路',
                'latitude' => 31.2400000, 'longitude' => 121.4900000,
                'business_hours' => '24시간 (야경 추천: 19:00-22:00)', 'closed_days' => '연중무휴',
                'price_min' => 0, 'price_max' => 0,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '상하이의 상징적인 강변 산책로. 1920~30년대 지어진 유럽풍 건축물 52채가 늘어서 있으며, 강 건너 루자쭈이의 현대적 스카이라인과 대비를 이룹니다. 야경이 특히 아름다워 저녁 방문을 추천합니다.',
                'tips' => "• 야경은 19시 이후가 베스트, 주말은 인파가 극심\n• 지하철 2호선/10호선 난징동루역 하차\n• 황푸강 유람선(浦江游览)도 추천 - 약 100위안\n• 와이탄 터널(외탄관광터널)은 가성비 별로",
                'recommendation_score' => 98, 'rating' => 4.8, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '동방명주 타워', 'name_cn' => '东方明珠广播电视塔', 'pinyin' => 'Dōngfāng Míngzhū',
                'name_en' => 'Oriental Pearl Tower',
                'address_ko' => '상하이시 푸동신구 스지다다오 1호', 'address_cn' => '上海市浦东新区世纪大道1号',
                'latitude' => 31.2397000, 'longitude' => 121.4998000,
                'business_hours' => '08:00-21:30', 'closed_days' => '연중무휴',
                'price_min' => 25000, 'price_max' => 45000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true,
                'description' => '상하이 푸동의 랜드마크. 468m 높이의 TV 타워로, 전망대에서 상하이 전경을 360도로 감상할 수 있습니다. 263m 높이의 유리 바닥 전망대가 스릴 만점.',
                'tips' => "• 온라인 사전 예매 필수 (현장 줄이 매우 김)\n• 맑은 날 오전이 전망 최고\n• 259m 전망대가 가성비 좋음\n• 지하철 2호선 루자쭈이역 1번 출구",
                'recommendation_score' => 90, 'rating' => 4.3, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '예원 (위위안)', 'name_cn' => '豫园', 'pinyin' => 'Yùyuán',
                'name_en' => 'Yu Garden',
                'address_ko' => '상하이시 황푸구 안런제 218호', 'address_cn' => '上海市黄浦区安仁街218号',
                'latitude' => 31.2272000, 'longitude' => 121.4922000,
                'business_hours' => '08:30-17:00', 'closed_days' => '연중무휴',
                'price_min' => 6000, 'price_max' => 6000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'description' => '400년 역사의 명나라 시대 정원. 전통 중국식 정원의 정수를 보여주며, 주변 예원상장(豫园商城)에서 쇼핑과 간식을 즐길 수 있습니다.',
                'tips' => "• 오전 일찍 가야 한적함 (09시 전 추천)\n• 주변 예원상장에서 소롱포(小笼包) 필수\n• 난상만터우뎬(南翔馒头店) 줄이 길지만 맛있음\n• 지하철 10호선 위위안역",
                'recommendation_score' => 88, 'rating' => 4.5, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '난징동루 보행거리', 'name_cn' => '南京东路步行街', 'pinyin' => 'Nánjīng Dōnglù',
                'name_en' => 'Nanjing Road',
                'address_ko' => '상하이시 황푸구 난징동루', 'address_cn' => '上海市黄浦区南京东路',
                'latitude' => 31.2352000, 'longitude' => 121.4747000,
                'business_hours' => '24시간 (매장은 10:00-22:00)', 'closed_days' => '연중무휴',
                'price_min' => 0, 'price_max' => 0,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '중국에서 가장 유명한 쇼핑 거리. 약 1.2km의 보행자 전용 거리로, 백화점, 브랜드숍, 맛집이 즐비합니다. 와이탄까지 걸어갈 수 있어 함께 방문하기 좋습니다.',
                'tips' => "• 와이탄과 연결되므로 저녁에 산책 코스로 추천\n• 주말 저녁은 인파 매우 많음\n• 소매치기 주의\n• 호객행위 무시하세요",
                'recommendation_score' => 85, 'rating' => 4.2, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '상하이 디즈니랜드', 'name_cn' => '上海迪士尼乐园', 'pinyin' => 'Shànghǎi Díshìní',
                'name_en' => 'Shanghai Disneyland',
                'address_ko' => '상하이시 푸동신구 촨사로 310번지', 'address_cn' => '上海市浦东新区川沙路310号',
                'latitude' => 31.1440000, 'longitude' => 121.6570000,
                'business_hours' => '08:30-20:30 (시즌별 변동)', 'closed_days' => '연중무휴',
                'price_min' => 60000, 'price_max' => 100000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true,
                'description' => '중국 본토 최초의 디즈니 테마파크. 세계 최대 규모의 디즈니 성이 있으며, 트론 라이트사이클, 캐리비안 해적 등 인기 어트랙션이 가득합니다.',
                'tips' => "• 평일 방문 강력 추천 (주말/공휴일 3시간 대기)\n• 디즈니 앱 설치 필수 (대기시간 확인)\n• 여권 원본 필수 (신분증 검사)\n• 지하철 11호선 디즈니역\n• 외부 음식 반입 가능 (물, 간식)",
                'recommendation_score' => 92, 'rating' => 4.6, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $tourist->id,
                'name_ko' => '쩐지아쓰 수향마을', 'name_cn' => '朱家角古镇', 'pinyin' => 'Zhūjiājiǎo',
                'name_en' => 'Zhujiajiao Water Town',
                'address_ko' => '상하이시 칭푸구 주자자오', 'address_cn' => '上海市青浦区朱家角镇',
                'latitude' => 31.1097000, 'longitude' => 121.0553000,
                'business_hours' => '08:30-16:30', 'closed_days' => '연중무휴',
                'price_min' => 0, 'price_max' => 12000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'description' => '1700년 역사의 수향 마을. 상하이에서 가장 가까운 수향으로, 고즈넉한 수로와 명청 시대 건축물을 감상할 수 있습니다. 나룻배 타기가 인기.',
                'tips' => "• 상하이 시내에서 버스로 약 1시간\n• 입장 무료, 개별 명소 유료\n• 좡좡러우(粽子) 꼭 드세요\n• 오전에 가야 한적함",
                'recommendation_score' => 80, 'rating' => 4.4, 'status' => 'PUBLIC',
            ],

            // ========== 상하이 맛집 ==========
            [
                'city_id' => $shanghai->id, 'category_id' => $food->id,
                'name_ko' => '딘타이펑 (상하이)', 'name_cn' => '鼎泰丰', 'pinyin' => 'Dǐngtàifēng',
                'name_en' => 'Din Tai Fung',
                'address_ko' => '상하이시 황푸구 난징시로 123호', 'address_cn' => '上海市黄浦区南京西路123号',
                'latitude' => 31.2312000, 'longitude' => 121.4745000,
                'phone' => '+86-21-6385-8378',
                'business_hours' => '10:00-22:00', 'closed_days' => '연중무휴',
                'price_min' => 15000, 'price_max' => 30000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true, 'restroom_rating' => 5,
                'description' => '대만 본점의 유명 딤섬 체인. 샤오롱바오(소롱포)가 대표 메뉴이며, 한국인 입맛에 잘 맞습니다. 깔끔한 매장과 친절한 서비스.',
                'tips' => "• 점심시간(11:30-13:00) 피하세요\n• 샤오롱바오 + 새우볶음밥 조합 추천\n• 영어 메뉴 있음\n• 위챗 미니프로그램으로 대기 등록 가능",
                'recommendation_score' => 95, 'rating' => 4.7, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $food->id,
                'name_ko' => '하이디라오 훠궈', 'name_cn' => '海底捞火锅', 'pinyin' => 'Hǎidǐlāo Huǒguō',
                'name_en' => 'Haidilao Hot Pot',
                'address_ko' => '상하이시 징안구 난징시로 1601호', 'address_cn' => '上海市静安区南京西路1601号',
                'latitude' => 31.2290000, 'longitude' => 121.4480000,
                'business_hours' => '24시간', 'closed_days' => '연중무휴',
                'price_min' => 25000, 'price_max' => 45000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true, 'restroom_rating' => 5,
                'description' => '중국 최고의 훠궈(샤브샤브) 체인. 대기 중 무료 네일아트, 간식 제공으로 유명합니다. 마라탕 육수와 토마토 육수의 반반이 인기.',
                'tips' => "• 대기가 길지만 간식/음료 무료 제공\n• 반반 냄비(鸳鸯锅) 주문 추천\n• 소스바에서 소스 직접 조합\n• 생일이면 케이크+노래 서비스\n• 앱으로 원격 대기 가능",
                'recommendation_score' => 93, 'rating' => 4.6, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $food->id,
                'name_ko' => '양스 볶음밥', 'name_cn' => '杨氏炒饭', 'pinyin' => 'Yáng Shì Chǎofàn',
                'address_ko' => '상하이시 황푸구 광동로 20호', 'address_cn' => '上海市黄浦区广东路20号',
                'latitude' => 31.2358000, 'longitude' => 121.4852000,
                'business_hours' => '11:00-14:00, 17:00-21:00', 'closed_days' => '일요일',
                'price_min' => 3000, 'price_max' => 8000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'has_english_menu' => false, 'restroom_rating' => 2,
                'description' => '현지인 맛집. 볶음밥 한 그릇이 15-30위안으로 저렴하면서도 맛있습니다. 새우볶음밥, 소고기볶음밥이 인기.',
                'tips' => "• 가게가 작아서 대기 있을 수 있음\n• 메뉴판 사진 찍어서 번역기 돌리세요\n• 현금도 받음\n• 양이 넉넉함",
                'recommendation_score' => 78, 'rating' => 4.3, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $food->id,
                'name_ko' => '르어자이 마라탕', 'name_cn' => '热尔寨麻辣烫', 'pinyin' => "Rè'ěrzhài Málàtàng",
                'address_ko' => '상하이시 쉬후이구 텐야오차오로 123호', 'address_cn' => '上海市徐汇区天钥桥路123号',
                'latitude' => 31.1930000, 'longitude' => 121.4510000,
                'business_hours' => '10:00-23:00', 'closed_days' => '연중무휴',
                'price_min' => 3000, 'price_max' => 10000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => false, 'restroom_rating' => 3,
                'description' => '재료를 직접 골라 무게로 계산하는 마라탕 맛집. 맵기 조절 가능하며, 한국인에게 인기 많은 로컬 체인입니다.',
                'tips' => "• 微辣(웨이라) = 약간 매운맛 추천\n• 채소 + 면 + 어묵 조합이 기본\n• 무게당 가격이라 양 조절 가능\n• 점심시간 직장인 많음",
                'recommendation_score' => 82, 'rating' => 4.2, 'status' => 'PUBLIC',
            ],

            // ========== 상하이 카페/쇼핑 ==========
            [
                'city_id' => $shanghai->id, 'category_id' => $cafe->id,
                'name_ko' => '스타벅스 리저브 로스터리', 'name_cn' => '星巴克烘焙工坊', 'pinyin' => 'Xīngbākè Hōngbèi Gōngfáng',
                'name_en' => 'Starbucks Reserve Roastery',
                'address_ko' => '상하이시 징안구 난징시로 789호', 'address_cn' => '上海市静安区南京西路789号',
                'latitude' => 31.2280000, 'longitude' => 121.4550000,
                'business_hours' => '07:00-23:00', 'closed_days' => '연중무휴',
                'price_min' => 6000, 'price_max' => 15000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true, 'restroom_rating' => 5,
                'description' => '세계에서 가장 큰 스타벅스. 2700㎡ 규모의 매장에서 원두 로스팅 과정을 직접 볼 수 있습니다. 중국 한정 메뉴도 다양.',
                'tips' => "• 2층에서 로스팅 과정 관람 가능\n• 중국 한정 차(茶) 메뉴 추천\n• 주말은 매우 혼잡\n• 굿즈 매장도 있음",
                'recommendation_score' => 85, 'rating' => 4.5, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $shanghai->id, 'category_id' => $shopping->id,
                'name_ko' => '티엔쯔팡', 'name_cn' => '田子坊', 'pinyin' => 'Tiánzǐfāng',
                'name_en' => 'Tianzifang',
                'address_ko' => '상하이시 황푸구 타이캉로 210번지', 'address_cn' => '上海市黄浦区泰康路210弄',
                'latitude' => 31.2075000, 'longitude' => 121.4678000,
                'business_hours' => '10:00-22:00', 'closed_days' => '연중무휴',
                'price_min' => 0, 'price_max' => 50000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'description' => '상하이의 예술 골목. 좁은 골목 안에 갤러리, 공방, 카페, 기념품 가게가 가득합니다. 독특한 수공예품과 디자인 제품을 구매하기 좋습니다.',
                'tips' => "• 골목이 미로처럼 복잡 - 그냥 헤매면서 즐기세요\n• 가격 흥정 가능 (30% 정도)\n• 지하철 9호선 다푸차오역\n• 오후 3-5시가 쾌적",
                'recommendation_score' => 83, 'rating' => 4.3, 'status' => 'PUBLIC',
            ],

            // ========== 베이징 ==========
            [
                'city_id' => $beijing->id, 'category_id' => $tourist->id,
                'name_ko' => '자금성 (고궁박물원)', 'name_cn' => '故宫博物院', 'pinyin' => 'Gùgōng Bówùyuàn',
                'name_en' => 'Forbidden City',
                'address_ko' => '베이징시 동청구 징산전가 4호', 'address_cn' => '北京市东城区景山前街4号',
                'latitude' => 39.9163000, 'longitude' => 116.3972000,
                'business_hours' => '08:30-17:00 (4-10월) / 08:30-16:30 (11-3월)',
                'price_min' => 10000, 'price_max' => 10000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '명·청 두 왕조의 황궁. 세계 최대 규모의 궁전 건축물로, 9999칸의 방이 있습니다. 유네스코 세계문화유산.',
                'tips' => "• 여권 원본 필수! (신분증 검사)\n• 온라인 사전 예매 필수 (당일 매진 빈번)\n• 월요일 휴관\n• 남쪽(오문)에서 입장 → 북쪽(신무문)으로 나옴\n• 최소 3시간 소요",
                'recommendation_score' => 97, 'rating' => 4.9, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $beijing->id, 'category_id' => $tourist->id,
                'name_ko' => '만리장성 (바다링)', 'name_cn' => '八达岭长城', 'pinyin' => 'Bādálǐng Chángchéng',
                'name_en' => 'Great Wall (Badaling)',
                'address_ko' => '베이징시 연경구 바다링', 'address_cn' => '北京市延庆区八达岭镇',
                'latitude' => 40.3541000, 'longitude' => 116.0140000,
                'business_hours' => '06:30-16:30',
                'price_min' => 7000, 'price_max' => 7000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '세계 7대 불가사의. 바다링 구간은 가장 잘 정비되어 접근성이 좋습니다. 케이블카 이용 가능.',
                'tips' => "• 베이징 시내에서 S2선 기차 추천 (6위안)\n• 여권 원본 필수\n• 편한 운동화 필수, 경사 급함\n• 여름에는 물 충분히 챙기세요\n• 케이블카 왕복 140위안",
                'recommendation_score' => 96, 'rating' => 4.8, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $beijing->id, 'category_id' => $food->id,
                'name_ko' => '취안쥐더 베이징덕', 'name_cn' => '全聚德烤鸭店', 'pinyin' => 'Quánjùdé Kǎoyā',
                'name_en' => 'Quanjude Roast Duck',
                'address_ko' => '베이징시 동청구 첸먼대가 30호', 'address_cn' => '北京市东城区前门大街30号',
                'latitude' => 39.8987000, 'longitude' => 116.3970000,
                'phone' => '+86-10-6701-1379',
                'business_hours' => '11:00-14:00, 17:00-21:00',
                'price_min' => 25000, 'price_max' => 50000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'has_english_menu' => true, 'restroom_rating' => 4,
                'description' => '1864년 창업, 160년 역사의 베이징 오리구이 원조. 바삭한 껍질과 육즙 가득한 고기가 일품입니다.',
                'tips' => "• 반마리(半只) 주문 가능 (2인 적당)\n• 껍질+살코기+전병+파+소스 조합으로 먹어요\n• 예약 추천\n• 본점(첸먼)이 분위기 최고",
                'recommendation_score' => 90, 'rating' => 4.4, 'status' => 'PUBLIC',
            ],

            // ========== 청두 ==========
            [
                'city_id' => $chengdu->id, 'category_id' => $tourist->id,
                'name_ko' => '청두 판다 기지', 'name_cn' => '成都大熊猫繁育研究基地', 'pinyin' => 'Chéngdū Dàxióngmāo Jīdì',
                'name_en' => 'Chengdu Panda Base',
                'address_ko' => '청두시 청화구 슝마오대도 1375호', 'address_cn' => '成都市成华区熊猫大道1375号',
                'latitude' => 30.7327000, 'longitude' => 104.1456000,
                'business_hours' => '07:30-18:00',
                'price_min' => 10000, 'price_max' => 10000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '자이언트 판다를 가장 가까이서 볼 수 있는 곳. 약 200마리의 판다가 있으며, 아기 판다를 볼 수 있는 유일한 장소입니다.',
                'tips' => "• 오전 7:30-10:00에 가야 판다가 활발함\n• 오후에는 판다가 잠만 잠\n• 전동차(观光车) 10위안 - 기지가 넓어서 추천\n• 여름엔 판다가 실내에 있을 수 있음",
                'recommendation_score' => 95, 'rating' => 4.7, 'status' => 'PUBLIC',
            ],
            [
                'city_id' => $chengdu->id, 'category_id' => $food->id,
                'name_ko' => '천마오뤄 마라훠궈', 'name_cn' => '陈麻婆豆腐', 'pinyin' => 'Chén Mápó Dòufu',
                'name_en' => 'Chen Mapo Tofu',
                'address_ko' => '청두시 칭양구 시위제 197호', 'address_cn' => '成都市青羊区西玉街197号',
                'latitude' => 30.6598000, 'longitude' => 104.0556000,
                'business_hours' => '11:00-21:00',
                'price_min' => 5000, 'price_max' => 15000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'has_english_menu' => false, 'restroom_rating' => 3,
                'description' => '마파두부의 원조 맛집. 1862년부터 이어져 온 정통 사천식 마파두부를 맛볼 수 있습니다.',
                'tips' => "• 微辣(약간 매움)도 한국인에게 꽤 매울 수 있음\n• 마파두부 + 흰쌀밥 조합이 정석\n• 현금도 받음\n• 점심시간 줄 있음",
                'recommendation_score' => 88, 'rating' => 4.5, 'status' => 'PUBLIC',
            ],

            // ========== 시안 ==========
            [
                'city_id' => $xian->id, 'category_id' => $tourist->id,
                'name_ko' => '진시황 병마용', 'name_cn' => '秦始皇兵马俑博物馆', 'pinyin' => 'Qínshǐhuáng Bīngmǎyǒng',
                'name_en' => 'Terracotta Warriors',
                'address_ko' => '시안시 린퉁구', 'address_cn' => '西安市临潼区秦始皇陵以东1.5千米处',
                'latitude' => 34.3842000, 'longitude' => 109.2785000,
                'business_hours' => '08:30-18:00 (3-11월) / 08:30-16:30 (12-2월)',
                'price_min' => 22000, 'price_max' => 22000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '진시황릉의 부장품으로 발굴된 8000여 점의 실물 크기 병마용. 세계 8대 불가사의로 불리며, 유네스코 세계문화유산입니다.',
                'tips' => "• 여권 원본 필수\n• 시안역에서 306번 버스 직통 (7위안)\n• 가이드 투어 추천 (영어/중국어)\n• 1호갱이 하이라이트\n• 최소 2-3시간 소요",
                'recommendation_score' => 96, 'rating' => 4.8, 'status' => 'PUBLIC',
            ],

            // ========== 항저우 ==========
            [
                'city_id' => $hangzhou->id, 'category_id' => $tourist->id,
                'name_ko' => '서호 (시후)', 'name_cn' => '西湖', 'pinyin' => 'Xīhú',
                'name_en' => 'West Lake',
                'address_ko' => '항저우시 시후구', 'address_cn' => '杭州市西湖区西湖',
                'latitude' => 30.2427000, 'longitude' => 120.1476000,
                'business_hours' => '24시간',
                'price_min' => 0, 'price_max' => 0,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => false,
                'description' => '중국 10대 명승지. 유네스코 세계문화유산으로, 호수 주변 산책과 유람선이 인기입니다. "하늘 위에 천당, 땅 위에 쑤항(上有天堂，下有苏杭)"이라는 말의 그 항저우.',
                'tips' => "• 입장 무료! 유람선만 유료 (45-150위안)\n• 자전거 대여로 호수 한바퀴 추천\n• 뤼허위안(西湖醋鱼) 맛집 주변에 많음\n• 봄(3-4월) 벚꽃, 가을(10-11월)이 최고 시즌",
                'recommendation_score' => 93, 'rating' => 4.7, 'status' => 'PUBLIC',
            ],

            // ========== 광저우 ==========
            [
                'city_id' => $guangzhou->id, 'category_id' => $food->id,
                'name_ko' => '광저우 딤섬 아침식사', 'name_cn' => '广州酒家', 'pinyin' => 'Guǎngzhōu Jiǔjiā',
                'name_en' => 'Guangzhou Restaurant',
                'address_ko' => '광저우시 리완구 원창난로 2호', 'address_cn' => '广州市荔湾区文昌南路2号',
                'latitude' => 23.1200000, 'longitude' => 113.2500000,
                'business_hours' => '07:00-15:00, 17:00-22:00',
                'price_min' => 10000, 'price_max' => 25000,
                'pay_alipay' => true, 'pay_wechat' => true, 'pay_cash' => true,
                'has_english_menu' => false, 'restroom_rating' => 4,
                'description' => '광둥식 딤섬(飲茶)의 정수. 하가우, 시우마이, 창펀 등 정통 광둥 딤섬을 저렴하게 즐길 수 있습니다. 아침 일찍 가야 합니다.',
                'tips' => "• '음차(飲茶/饮茶)' = 딤섬 식사\n• 아침 7-9시가 현지인 시간대\n• 차를 먼저 고르고 딤섬 주문\n• 찻주전자 뚜껑 열어놓으면 물 리필 신호",
                'recommendation_score' => 89, 'rating' => 4.5, 'status' => 'PUBLIC',
            ],
        ];

        foreach ($places as $place) {
            Place::create($place);
        }
    }
}
