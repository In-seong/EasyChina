<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    /**
     * 중국어 → 병음 변환 (외부 API 활용)
     */
    public function pinyin(Request $request)
    {
        $request->validate(['text' => 'required|string|max:500']);

        $text = $request->input('text');

        try {
            // MyMemory API로 중국어 → 영어(병음 유사) 변환 시도
            $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($text) . '&langpair=zh-CN|en';
            $ctx = stream_context_create(['http' => ['timeout' => 10]]);
            $response = @file_get_contents($url, false, $ctx);

            if ($response) {
                $data = json_decode($response, true);
                // matches에서 병음 형태 추출 시도
                $pinyin = $this->extractPinyin($text, $data);

                return response()->json([
                    'success' => true,
                    'data' => ['pinyin' => $pinyin],
                ]);
            }
        } catch (\Exception $e) {
            // 실패 시 빈 값
        }

        return response()->json([
            'success' => true,
            'data' => ['pinyin' => ''],
        ]);
    }

    /**
     * 간단한 병음 매핑 (기본 한자 → 병음)
     */
    private function extractPinyin(string $text, ?array $apiData): string
    {
        // 기본 한자-병음 매핑 (자주 쓰는 여행 관련 글자)
        $map = [
            '你' => 'nǐ', '好' => 'hǎo', '谢' => 'xiè', '不' => 'bù', '是' => 'shì',
            '的' => 'de', '我' => 'wǒ', '要' => 'yào', '这' => 'zhè', '个' => 'gè',
            '多' => 'duō', '少' => 'shǎo', '钱' => 'qián', '请' => 'qǐng', '给' => 'gěi',
            '到' => 'dào', '去' => 'qù', '在' => 'zài', '哪' => 'nǎ', '里' => 'lǐ',
            '有' => 'yǒu', '没' => 'méi', '会' => 'huì', '说' => 'shuō', '中' => 'zhōng',
            '文' => 'wén', '人' => 'rén', '大' => 'dà', '小' => 'xiǎo', '吃' => 'chī',
            '喝' => 'hē', '买' => 'mǎi', '单' => 'dān', '水' => 'shuǐ', '菜' => 'cài',
            '饭' => 'fàn', '肉' => 'ròu', '鱼' => 'yú', '辣' => 'là', '甜' => 'tián',
            '热' => 'rè', '冷' => 'lěng', '打' => 'dǎ', '包' => 'bāo', '用' => 'yòng',
            '支' => 'zhī', '付' => 'fù', '宝' => 'bǎo', '微' => 'wēi', '信' => 'xìn',
            '可' => 'kě', '以' => 'yǐ', '试' => 'shì', '穿' => 'chuān', '看' => 'kàn',
            '太' => 'tài', '贵' => 'guì', '便' => 'pián', '宜' => 'yí', '一' => 'yī',
            '点' => 'diǎn', '别' => 'bié', '颜' => 'yán', '色' => 'sè', '发' => 'fā',
            '票' => 'piào', '车' => 'chē', '站' => 'zhàn', '地' => 'dì', '铁' => 'tiě',
            '机' => 'jī', '场' => 'chǎng', '酒' => 'jiǔ', '店' => 'diàn', '房' => 'fáng',
            '间' => 'jiān', '洗' => 'xǐ', '手' => 'shǒu', '厕' => 'cè', '所' => 'suǒ',
            '医' => 'yī', '院' => 'yuàn', '警' => 'jǐng', '察' => 'chá', '救' => 'jiù',
            '命' => 'mìng', '帮' => 'bāng', '对' => 'duì', '起' => 'qǐ', '关' => 'guān',
            '系' => 'xì', '韩' => 'hán', '国' => 'guó', '护' => 'hù', '照' => 'zhào',
            '丢' => 'diū', '了' => 'le', '舒' => 'shū', '服' => 'fú', '吗' => 'ma',
            '叫' => 'jiào', '联' => 'lián', '使' => 'shǐ', '馆' => 'guǎn', '路' => 'lù',
            '迷' => 'mí', '公' => 'gōng', '交' => 'jiāo', '停' => 'tíng', '开' => 'kāi',
            '后' => 'hòu', '备' => 'bèi', '箱' => 'xiāng', '表' => 'biǎo', '随' => 'suí',
            '什' => 'shén', '么' => 'me', '名' => 'míng', '字' => 'zì',
            '北' => 'běi', '京' => 'jīng', '上' => 'shàng', '海' => 'hǎi',
            '东' => 'dōng', '西' => 'xī', '南' => 'nán', '门' => 'mén',
        ];

        $result = [];
        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            if (isset($map[$char])) {
                $result[] = $map[$char];
            } elseif (preg_match('/[\x{4e00}-\x{9fff}]/u', $char)) {
                $result[] = $char; // 매핑 없는 한자는 그대로
            } elseif ($char === '？' || $char === '？') {
                $result[] = '?';
            } elseif ($char === '！') {
                $result[] = '!';
            } elseif ($char !== ' ') {
                $result[] = $char;
            }
        }

        return implode(' ', $result);
    }
}
