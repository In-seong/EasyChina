<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Tip;
use App\Models\TipCategory;
use Illuminate\Database\Seeder;

class TipSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            '입국 준비' => [
                ['title' => '비자 확인', 'content' => "<p>한국 여권 소지자는 <strong>15일 이내 관광</strong> 시 무비자 입국이 가능합니다 (2024년부터 시행, 연장 여부 확인 필요).</p><p><strong>15일 초과 체류</strong> 시 사전에 중국 비자를 발급받아야 합니다.</p><ul><li>비자 신청: 주한중국대사관 또는 비자센터</li><li>소요 기간: 4~7 영업일</li><li>필요 서류: 여권, 사진, 호텔 예약 확인서, 항공권</li></ul>"],
                ['title' => '여권 유효기간 확인', 'content' => "<p>중국 입국 시 여권 잔여 유효기간이 <strong>6개월 이상</strong>이어야 합니다.</p><p>여권 빈 페이지도 최소 2페이지 이상 확보하세요.</p>"],
                ['title' => '항공편 & 공항 정보', 'content' => "<p><strong>상하이</strong>: 푸동국제공항(PVG) - 시내 약 50km, 자기부상열차 8분<br><strong>베이징</strong>: 서우두국제공항(PEK) / 다싱국제공항(PKX)</p><p>입국 시 <strong>건강 신고서</strong>를 작성해야 할 수 있습니다 (시기별 변동).</p>"],
                ['title' => '유심 / eSIM 준비', 'content' => "<p>중국에서는 구글, 카카오톡, 인스타그램 등이 <strong>차단</strong>됩니다.</p><p><strong>추천 방법</strong>:</p><ul><li>한국에서 중국용 eSIM 미리 구매 (VPN 포함 제품 선택)</li><li>공항에서 중국 유심 구매 (China Mobile/Unicom)</li><li>포켓와이파이 대여</li></ul><p>⚠️ VPN이 포함되지 않은 유심은 카카오톡 사용 불가!</p>"],
            ],
            '교통 가이드' => [
                ['title' => '지하철 이용법', 'content' => "<p>중국 대도시 지하철은 <strong>한국과 매우 유사</strong>합니다.</p><ol><li>역 입구에서 <strong>보안검색</strong> (짐 X선 검사)</li><li>자동판매기에서 1회용 토큰 구매 또는 교통카드</li><li>알리페이/위챗페이 QR로 탑승 가능 (도시별 앱 다름)</li></ol><p><strong>상하이</strong>: Metro大都会 앱<br><strong>베이징</strong>: 亿通行 앱</p><p>💡 러시아워(7:30-9:00, 17:30-19:00)는 피하세요</p>"],
                ['title' => '택시 & 디디추싱 사용법', 'content' => "<p><strong>디디추싱(滴滴出行)</strong>은 중국판 우버입니다.</p><ol><li>앱 설치 후 전화번호 인증</li><li>목적지 입력 (중국어 주소 복사해서 붙여넣기)</li><li>차량 호출 → 도착 후 탑승</li><li>결제는 자동 (알리페이/위챗페이 연동)</li></ol><p>💡 일반 택시도 탈 수 있지만, 기사가 한국어/영어 못하므로 <strong>목적지 중국어를 화면에 보여주세요</strong>.</p>"],
                ['title' => '고속철도 (가오티에) 예매', 'content' => "<p>중국 고속철도는 매우 빠르고 편리합니다.</p><ul><li>상하이 ↔ 베이징: 약 4.5시간 (G열차)</li><li>상하이 ↔ 항저우: 약 1시간</li></ul><p><strong>예매 방법</strong>: Trip.com 앱 (한국어 지원)으로 예매 가능</p><p>⚠️ 탑승 시 <strong>여권 원본 필수</strong> (자동개찰구에서 여권 스캔)</p>"],
            ],
            '필수 앱 설치' => [
                ['title' => '위챗 (WeChat/微信)', 'content' => "<p>중국 국민 메신저이자 만능 앱.</p><ul><li>메시지, 통화</li><li>위챗페이 (결제)</li><li>미니프로그램 (배달, 택시, 예매 등)</li></ul><p>한국에서 미리 설치하고 계정 만들어 가세요.</p>"],
                ['title' => '알리페이 (支付宝)', 'content' => "<p>중국 최대 결제 앱. 외국인도 해외 카드 연동 가능합니다.</p><ol><li>앱 설치 → Tour Pass 선택</li><li>해외 신용카드 등록</li><li>충전 후 QR 결제 사용</li></ol><p>💡 대부분의 가게가 알리페이를 받습니다. <strong>현금보다 필수!</strong></p>"],
                ['title' => '디디추싱 (滴滴出行)', 'content' => "<p>중국판 우버. 택시 호출 필수 앱.</p><p>영어 버전도 있어 비교적 사용이 편리합니다.</p>"],
                ['title' => 'VPN 앱', 'content' => "<p>중국에서 차단되는 서비스:</p><ul><li>❌ Google (검색, 지도, Gmail, YouTube)</li><li>❌ 카카오톡, 라인</li><li>❌ Instagram, Facebook, Twitter</li><li>❌ WhatsApp, Telegram</li></ul><p><strong>VPN 앱을 반드시 한국에서 미리 설치</strong>하세요. 중국 입국 후에는 앱 다운로드가 불가능합니다.</p><p>추천: ExpressVPN, NordVPN, Surfshark</p>"],
            ],
            '결제 설정' => [
                ['title' => '알리페이 외국인 설정법', 'content' => "<ol><li>알리페이 앱 설치</li><li>해외 전화번호로 가입</li><li>Tour Pass 메뉴 진입</li><li>해외 Visa/Master 카드 등록</li><li>최소 100위안 이상 충전</li><li>QR 코드로 결제!</li></ol><p>💡 충전 한도: 1회 2000위안, 연간 50000위안</p>"],
                ['title' => '현금은 거의 안 받습니다', 'content' => "<p>중국은 모바일 결제 비율이 <strong>95% 이상</strong>입니다.</p><p>편의점, 노점, 택시 모두 QR 결제가 기본이며, 현금을 거부하는 곳도 많습니다.</p><p><strong>반드시 알리페이 또는 위챗페이를 설정하고 가세요!</strong></p><p>💡 만약을 위해 소액 현금(500위안 정도)은 챙기세요.</p>"],
            ],
            'VPN & 인터넷' => [
                ['title' => '중국 인터넷 차단 목록', 'content' => "<p>중국에서 접속 불가능한 서비스:</p><ul><li>Google 전체 (검색, Gmail, YouTube, Play Store, Maps)</li><li>SNS: Instagram, Facebook, Twitter/X, TikTok(해외판)</li><li>메신저: 카카오톡, 라인, WhatsApp, Telegram</li><li>기타: Wikipedia, Dropbox, Slack</li></ul><p><strong>사용 가능한 서비스</strong>: 네이버, 다음, 한국 은행앱, Apple 서비스</p>"],
                ['title' => 'VPN 사용 팁', 'content' => "<ul><li>VPN은 <strong>반드시 한국에서 미리 설치</strong></li><li>중국 입국 후에는 앱스토어에서 VPN 다운 불가</li><li>VPN 연결이 불안정할 수 있음 - 여러 서버 시도</li><li>무료 VPN은 느리고 불안정 - 유료 추천</li><li>호텔 와이파이에서 VPN이 더 잘 됨</li></ul>"],
            ],
            '긴급 연락처' => [
                ['title' => '주요 긴급 번호', 'content' => "<table><tr><td>경찰</td><td><strong>110</strong></td></tr><tr><td>소방</td><td><strong>119</strong></td></tr><tr><td>응급의료</td><td><strong>120</strong></td></tr><tr><td>교통사고</td><td><strong>122</strong></td></tr></table>"],
                ['title' => '한국 대사관 & 영사관', 'content' => "<p><strong>주중한국대사관 (베이징)</strong><br>전화: +86-10-8531-0700<br>긴급: +86-10-6532-0290</p><p><strong>주상하이총영사관</strong><br>전화: +86-21-6295-5000<br>긴급: +86-139-1755-8269</p><p><strong>주광저우총영사관</strong><br>전화: +86-20-2919-2999</p><p><strong>주청두총영사관</strong><br>전화: +86-28-8616-5800</p><p>💡 영사콜센터 24시간: <strong>+82-2-3210-0404</strong></p>"],
            ],
            '주의사항 & 에티켓' => [
                ['title' => '여권 항상 지참', 'content' => "<p>중국에서는 <strong>여권 원본</strong>을 항상 가지고 다녀야 합니다.</p><ul><li>관광지 입장 시 신분증 검사</li><li>호텔 체크인 시 필수</li><li>기차역 탑승 시 필수</li><li>경찰 불심검문 가능성</li></ul><p>💡 여권 사본 + 사진도 휴대폰에 저장해두세요.</p>"],
                ['title' => '사진 촬영 주의', 'content' => "<ul><li>군사시설, 정부기관 근처 촬영 금지</li><li>사람 촬영 시 동의 구하기</li><li>사찰/사원 내부 촬영 금지인 곳 많음</li><li>박물관 플래시 촬영 금지</li></ul>"],
                ['title' => '식당 에티켓', 'content' => "<ul><li>팁 문화 없음 (팁 주면 오히려 당황)</li><li>식탁에 뼈나 껍질 놓는 건 정상</li><li>소리내며 먹는 것 OK</li><li>차를 따라줄 때 검지+중지로 테이블 톡톡 = 감사 표시</li><li>식사 중 코 풀기는 실례</li></ul>"],
                ['title' => '물 주의', 'content' => "<p>중국의 <strong>수돗물은 마시면 안 됩니다!</strong></p><ul><li>생수(瓶装水) 구매해서 드세요</li><li>식당에서 주는 뜨거운 물(热水)은 끓인 물이라 OK</li><li>얼음이 수돗물인 경우가 있으니 주의</li></ul>"],
            ],
            '생존 중국어' => [
                ['title' => '필수 단어 10개', 'content' => "<table><tr><td>안녕하세요</td><td>你好 (nǐ hǎo)</td></tr><tr><td>감사합니다</td><td>谢谢 (xièxie)</td></tr><tr><td>얼마예요?</td><td>多少钱？(duōshao qián?)</td></tr><tr><td>화장실</td><td>洗手间 (xǐshǒujiān)</td></tr><tr><td>지하철역</td><td>地铁站 (dìtiě zhàn)</td></tr><tr><td>너무 비싸요</td><td>太贵了 (tài guì le)</td></tr><tr><td>맵지 않게</td><td>不要辣 (bú yào là)</td></tr><tr><td>계산</td><td>买单 (mǎidān)</td></tr><tr><td>도와주세요</td><td>帮帮我 (bāngbang wǒ)</td></tr><tr><td>한국 사람</td><td>韩国人 (hánguó rén)</td></tr></table>"],
                ['title' => '숫자 읽기', 'content' => "<table><tr><td>1</td><td>一 yī</td><td>6</td><td>六 liù</td></tr><tr><td>2</td><td>二 èr</td><td>7</td><td>七 qī</td></tr><tr><td>3</td><td>三 sān</td><td>8</td><td>八 bā</td></tr><tr><td>4</td><td>四 sì</td><td>9</td><td>九 jiǔ</td></tr><tr><td>5</td><td>五 wǔ</td><td>10</td><td>十 shí</td></tr></table><p>23 = 二十三 (èr shí sān)<br>100 = 一百 (yì bǎi)<br>1000 = 一千 (yì qiān)</p>"],
            ],
        ];

        foreach ($data as $categoryName => $tips) {
            $category = TipCategory::where('name', $categoryName)->first();
            if (!$category) continue;

            foreach ($tips as $index => $tip) {
                Tip::create([
                    'tip_category_id' => $category->id,
                    'title' => $tip['title'],
                    'content' => $tip['content'],
                    'sort_order' => $index + 1,
                    'status' => 'PUBLIC',
                ]);
            }
        }

        // 실시간 배너
        $banners = [
            ['content' => '자금성·만리장성 등 주요 관광지 입장 시 여권 원본 필수!', 'type' => 'WARNING'],
            ['content' => '알리페이 Tour Pass로 외국인도 QR결제 가능 (해외카드 등록)', 'type' => 'INFO'],
            ['content' => 'VPN은 반드시 한국에서 미리 설치하세요. 중국 입국 후 다운 불가!', 'type' => 'URGENT'],
            ['content' => '중국 수돗물은 마시지 마세요. 생수(瓶装水) 구매 필수', 'type' => 'WARNING'],
            ['city_id' => 2, 'content' => '상하이 디즈니랜드 주말 대기시간 2-3시간. 평일 방문 추천', 'type' => 'INFO'],
        ];

        foreach ($banners as $b) {
            Banner::create(array_merge([
                'city_id' => $b['city_id'] ?? null,
                'content' => $b['content'],
                'type' => $b['type'],
                'is_active' => true,
                'sort_order' => 0,
            ]));
        }
    }
}
