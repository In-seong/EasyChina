<?php

namespace Database\Seeders;

use App\Models\Phrase;
use App\Models\PhraseCategory;
use Illuminate\Database\Seeder;

class PhraseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            '식당' => [
                ['text_ko' => '메뉴판 주세요', 'text_cn' => '请给我菜单', 'pinyin' => 'qǐng gěi wǒ càidān'],
                ['text_ko' => '이거 얼마예요?', 'text_cn' => '这个多少钱？', 'pinyin' => 'zhège duōshao qián?'],
                ['text_ko' => '이거 주세요', 'text_cn' => '我要这个', 'pinyin' => 'wǒ yào zhège'],
                ['text_ko' => '맵지 않게 해주세요', 'text_cn' => '不要辣的', 'pinyin' => 'bú yào là de'],
                ['text_ko' => '계산해 주세요', 'text_cn' => '买单', 'pinyin' => 'mǎidān'],
                ['text_ko' => '맛있어요', 'text_cn' => '好吃', 'pinyin' => 'hǎo chī'],
                ['text_ko' => '물 주세요', 'text_cn' => '请给我水', 'pinyin' => 'qǐng gěi wǒ shuǐ'],
                ['text_ko' => '포장해 주세요', 'text_cn' => '打包', 'pinyin' => 'dǎbāo'],
                ['text_ko' => '알리페이로 결제할게요', 'text_cn' => '用支付宝支付', 'pinyin' => 'yòng zhīfùbǎo zhīfù'],
                ['text_ko' => '위챗페이로 결제할게요', 'text_cn' => '用微信支付', 'pinyin' => 'yòng wēixìn zhīfù'],
            ],
            '택시' => [
                ['text_ko' => '여기로 가주세요', 'text_cn' => '请到这里', 'pinyin' => 'qǐng dào zhèlǐ'],
                ['text_ko' => '얼마예요?', 'text_cn' => '多少钱？', 'pinyin' => 'duōshao qián?'],
                ['text_ko' => '여기서 세워주세요', 'text_cn' => '请在这里停车', 'pinyin' => 'qǐng zài zhèlǐ tíng chē'],
                ['text_ko' => '미터기 켜주세요', 'text_cn' => '请打表', 'pinyin' => 'qǐng dǎ biǎo'],
                ['text_ko' => '트렁크 열어주세요', 'text_cn' => '请打开后备箱', 'pinyin' => 'qǐng dǎkāi hòubèixiāng'],
                ['text_ko' => '공항까지 가주세요', 'text_cn' => '请到机场', 'pinyin' => 'qǐng dào jīchǎng'],
                ['text_ko' => '호텔까지 가주세요', 'text_cn' => '请到酒店', 'pinyin' => 'qǐng dào jiǔdiàn'],
            ],
            '쇼핑' => [
                ['text_ko' => '좀 깎아주세요', 'text_cn' => '便宜一点', 'pinyin' => 'piányi yìdiǎn'],
                ['text_ko' => '다른 색 있어요?', 'text_cn' => '有别的颜色吗？', 'pinyin' => 'yǒu bié de yánsè ma?'],
                ['text_ko' => '이거 입어봐도 돼요?', 'text_cn' => '可以试穿吗？', 'pinyin' => 'kěyǐ shì chuān ma?'],
                ['text_ko' => '영수증 주세요', 'text_cn' => '请给我发票', 'pinyin' => 'qǐng gěi wǒ fāpiào'],
                ['text_ko' => '그냥 볼게요', 'text_cn' => '我随便看看', 'pinyin' => 'wǒ suíbiàn kànkan'],
            ],
            '긴급상황' => [
                ['text_ko' => '도와주세요!', 'text_cn' => '救命！', 'pinyin' => 'jiùmìng!'],
                ['text_ko' => '경찰을 불러주세요', 'text_cn' => '请叫警察', 'pinyin' => 'qǐng jiào jǐngchá'],
                ['text_ko' => '병원에 가야해요', 'text_cn' => '我要去医院', 'pinyin' => 'wǒ yào qù yīyuàn'],
                ['text_ko' => '한국 대사관에 연락해주세요', 'text_cn' => '请联系韩国大使馆', 'pinyin' => 'qǐng liánxì hánguó dàshǐguǎn'],
                ['text_ko' => '여권을 잃어버렸어요', 'text_cn' => '我的护照丢了', 'pinyin' => 'wǒ de hùzhào diū le'],
                ['text_ko' => '아파요', 'text_cn' => '我不舒服', 'pinyin' => 'wǒ bù shūfu'],
            ],
            '인사/기본' => [
                ['text_ko' => '안녕하세요', 'text_cn' => '你好', 'pinyin' => 'nǐ hǎo'],
                ['text_ko' => '감사합니다', 'text_cn' => '谢谢', 'pinyin' => 'xièxie'],
                ['text_ko' => '죄송합니다', 'text_cn' => '对不起', 'pinyin' => 'duìbuqǐ'],
                ['text_ko' => '괜찮아요', 'text_cn' => '没关系', 'pinyin' => 'méi guānxi'],
                ['text_ko' => '네 / 아니요', 'text_cn' => '是的 / 不是', 'pinyin' => 'shì de / bú shì'],
                ['text_ko' => '저는 한국 사람이에요', 'text_cn' => '我是韩国人', 'pinyin' => 'wǒ shì hánguó rén'],
                ['text_ko' => '중국어 못해요', 'text_cn' => '我不会说中文', 'pinyin' => 'wǒ bú huì shuō zhōngwén'],
            ],
            '교통' => [
                ['text_ko' => '지하철역이 어디예요?', 'text_cn' => '地铁站在哪里？', 'pinyin' => 'dìtiě zhàn zài nǎlǐ?'],
                ['text_ko' => '화장실이 어디예요?', 'text_cn' => '洗手间在哪里？', 'pinyin' => 'xǐshǒujiān zài nǎlǐ?'],
                ['text_ko' => '여기가 어디예요?', 'text_cn' => '这是哪里？', 'pinyin' => 'zhè shì nǎlǐ?'],
                ['text_ko' => '길을 잃었어요', 'text_cn' => '我迷路了', 'pinyin' => 'wǒ mílù le'],
                ['text_ko' => '이 버스가 어디로 가요?', 'text_cn' => '这路公交去哪里？', 'pinyin' => 'zhè lù gōngjiāo qù nǎlǐ?'],
            ],
        ];

        foreach ($data as $categoryName => $phrases) {
            $category = PhraseCategory::where('name', $categoryName)->first();
            if (!$category) continue;

            foreach ($phrases as $index => $phrase) {
                Phrase::create([
                    'phrase_category_id' => $category->id,
                    'text_ko' => $phrase['text_ko'],
                    'text_cn' => $phrase['text_cn'],
                    'pinyin' => $phrase['pinyin'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
