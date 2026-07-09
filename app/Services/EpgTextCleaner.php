<?php

namespace App\Services;

class EpgTextCleaner
{
    protected array $replaceMap = [];
    protected array $chars = [
        'hdtv'=>['🅊','[HV]'],
        'sdtv'=>['🅌','[SD]'],
        'progressive'=>['🄿','[P]'],
        'wide'=>['🅆','[W]'],
        'multiview'=>['🅋','[MV]'],
        'sign_lang'=>['🈐','[手]'],
        'subtitled'=>['🈑','[字]'],
        'interactive'=>['🈒','[双]'],
        'data'=>['🈓','[デ]'],
        'stereo'=>['🅂','[S]'],
        'bilingual'=>['🈔','[二]'],
        'multilingual'=>['🈕','[多]'],
        'audio_description'=>['🈖','[解]'],
        'surround'=>['🅍','[SS]'],
        'b_mode_stereo'=>['🄱','[B]'],
        'news'=>['🄽','[N]'],
        'weather'=>['🈗','[天]'],
        'traffic'=>['🈘','[交]'],
        'cinema'=>['🈙','[映]'],
        'free'=>['🈚','[無]'],
        'paid'=>['🈛','[料]'],
        'parental_lock'=>['⚿','[鍵]'],
        'part1'=>['🈜','[前]'],
        'part2'=>['🈝','[後]'],
        'rerun'=>['🈞','[再]'],
        'new'=>['🈟','[新]'],
        'premiere'=>['🈠','[初]'],
        'finale'=>['🈡','[終]'],
        'live'=>['🈢','[生]'],
        'shopping'=>['🈣','[販]'],
        'voice'=>['🈤','[声]'],
        'dubbed'=>['🈥','[吹]'],
        'ppv'=>['🅎','[PPV]'],
        'secret'=>['㊙','(秘)'],
        'others'=>['🈀','ほか'],
    ];

    public function __construct()
    {
        $cookieValue = request()->cookie('epg_char_settings');
        $savedSettings = $cookieValue ? json_decode($cookieValue, true) : [];
        foreach ($this->chars as $key => $target) {
            $configKey = "chr_{$key}";
            if (isset($savedSettings[$configKey]) && $savedSettings[$configKey] === '1') {
                $this->replaceMap[$target[0]] = $target[1];
            }
        }
    }

    public function getDefinitions(): array
    {
        return $this->chars;
    }

    public function clean(?string $text): string
    {
        if (!$text) return '';
        if (empty($this->replaceMap)) return $text;

        return strtr($text, $this->replaceMap);
    }
}
