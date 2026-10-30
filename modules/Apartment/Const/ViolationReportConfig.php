<?php

namespace Modules\Apartment\Const;

/**
 * Ko'p kvartirali uylar bo'yicha 5 ta hisobot (ЖДВ) konfiguratsiyasi.
 *
 * Har bir guruh -> violation_types.id ro'yxati.
 * ID lar storage/excel/violation-type.xlsx (seed) dan olingan.
 * [] (bo'sh) bo'lganlari prod DB da tekshirib to'ldirilishi kerak (TODO):
 *   SELECT vt.id, vt.name, p.name FROM violation_types vt JOIN places p ON p.id = vt.place_id ORDER BY p.id, vt.id;
 */
class ViolationReportConfig
{
    public const TAYANCH = 1;
    public const TUTASH = 2;
    public const YERTOLA = 3;
    public const UMUMFOYDALANISH = 4;
    public const UMUMIY = 5;

    public const TYPES = [
        self::TAYANCH,
        self::TUTASH,
        self::YERTOLA,
        self::UMUMFOYDALANISH,
        self::UMUMIY,
    ];

    private const MONITORING_TYPE_YERTOLA = 1;
    private const MONITORING_TYPE_KOP_QAVATLI = 2;

    public static function get(int $type): array
    {
        return match ($type) {
            self::TAYANCH => self::tayanch(),
            self::TUTASH => self::tutash(),
            self::YERTOLA => self::yertola(),
            self::UMUMFOYDALANISH => self::umumfoydalanish(),
            self::UMUMIY => self::umumiy(),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }

    /** 1-ЖДВ. Таянч ва тўсиқ конструкциялар */
    private static function tayanch(): array
    {
        return [
            'title' => 'Кўп квартирали уй-жойлардаги таянч ва тўсиқ конструкциялар бутлиги юзасидан ўтказилган ўрганишлар тўғрисида маълумот',
            'monitoring_type_ids' => [self::MONITORING_TYPE_KOP_QAVATLI],
            'slug' => 'jdv-1-tayanch',
            'group_fines' => true,
            'total_fines' => false,
            'sections' => [
                'main' => [
                    'title' => null,
                    'groups' => [
                        'tayanch' => ['label' => 'Таянч конструкциялар бузилиш ҳолатлари', 'violation_type_ids' => [22]],
                        'tosiq' => ['label' => 'Тўсиқ конструкциялар бузилиш ҳолатлари', 'violation_type_ids' => [23]],
                    ],
                ],
            ],
        ];
    }

    /** 2-ЖДВ. Туташ ер участкалари */
    private static function tutash(): array
    {
        return [
            'title' => 'Кўп квартирали уйларнинг туташ ер участкаларида ўзбошимчалик билан қурилган қурилмалар ва уларга нисбатан қўлланилган чоралар тўғрисида маълумот',
            'monitoring_type_ids' => [self::MONITORING_TYPE_KOP_QAVATLI],
            'slug' => 'jdv-2-tutash',
            'group_fines' => true,
            'sections' => [
                'main' => [
                    'title' => null,
                    'groups' => [
                        'noturar_ogir' => ['label' => 'Нотурар жойга қўшиб оғир конструкциядан ноқонуний қурилган қурилмалар', 'violation_type_ids' => []], // TODO
                        'turar_ogir' => ['label' => 'Турар жойга қўшиб оғир конструкциядан ноқонуний қурилган қурилмалар', 'violation_type_ids' => []], // TODO
                        'noturar_yengil' => ['label' => 'Нотурар жойга қўшиб енгил конструкциядан ноқонуний қурилган қурилмалар', 'violation_type_ids' => []], // TODO
                        'turar_yengil' => ['label' => 'Турар жойга қўшиб енгил конструкциядан ноқонуний қурилган қурилмалар', 'violation_type_ids' => []], // TODO
                        'orab_olingan' => ['label' => 'Ҳудудни турли қурилиш материаллари билан ўралганлари', 'violation_type_ids' => []], // TODO
                        'balkon_osti' => ['label' => 'Туташ ер участкалари (балкон ости)нинг ковланганлиги', 'violation_type_ids' => []], // TODO
                        'ozboshimcha_qurilma' => ['label' => 'Туташ ер участкаларида ўзбошимча қурилма қурилган', 'violation_type_ids' => [24]],
                    ],
                ],
            ],
        ];
    }

    /** 3-ЖДВ. Ертўла */
    private static function yertola(): array
    {
        return [
            'title' => 'Кўп квартирали уйларнинг ертўла қисмида аниқланган ҳуқуқбузарликлар ва улар бўйича қўлланилган чоралар тўғрисида маълумот',
            'monitoring_type_ids' => [self::MONITORING_TYPE_YERTOLA],
            'slug' => 'jdv-3-yertola',
            'group_fines' => true,
            'studied_label' => 'Ўрганиб чиқилган ертўлалар сони',
            'sections' => [
                'main' => [
                    'title' => null,
                    'groups' => [
                        'qoshimcha_qurilma' => ['label' => 'Ертўлага қўшимча қурилмалар қурилган (ўрнатилган)', 'violation_type_ids' => [9]],
                        'muhandislik_ozgartirilgan' => ['label' => 'Ертўладаги муҳандислик-коммуникациялари тизими ўзгартирилган', 'violation_type_ids' => [6]],
                        'egallab_olingan' => ['label' => 'Ертўла ўзбошимча эгаллаб олинган (аҳоли розилигисиз)', 'violation_type_ids' => [11]],
                        'chiqindilar' => ['label' => 'Ертўлада қаттиқ маиший чиқиндилар тўпланган', 'violation_type_ids' => [12]],
                        'borish_cheklangan' => ['label' => 'Ертўлада муҳандислик-коммуникация тизимларига бориш имкониятини чеклаб қўйилган', 'violation_type_ids' => [8]],
                        'devor_shikast' => ['label' => 'Ертўладаги таянч ва таянч бўлмаган деворларга шикаст етказилган', 'violation_type_ids' => [1]],
                        'fasaddan_eshik' => ['label' => 'Ертўлага ташқи фасаддан эшик очилган', 'violation_type_ids' => [2]],
                        'kavlangan' => ['label' => 'Ертўланинг ички ва ташқи қисми кавланган', 'violation_type_ids' => [5]],
                        'xonadondan_eshik' => ['label' => 'Ертўлага хонадон ички қисмидан эшик очилган', 'violation_type_ids' => [3]],
                        'yonuvchan' => ['label' => 'Ертўлада ёнувчан ва портлаш хавфи мавжуд модда ва материаллар сақланган', 'violation_type_ids' => [13]],
                        'tirband' => ['label' => 'Ертўладаги умумий йўлаклар ва чиқиш жойлари турли буюмлар ва асбоб-ускуналар билан тирбанд қилинган', 'violation_type_ids' => [15]],
                        'isitish_qozon' => ['label' => 'Ертўлада ўзбошимчалик билан иситиш қозонлари ва радиаторлар ўрнатилган', 'violation_type_ids' => [7]],
                        'tadbirkorlik' => ['label' => 'Ертўладан мумкин бўлмаган тадбиркорлик фаолияти билан шуғулланилган', 'violation_type_ids' => [14]],
                        'fortochka' => ['label' => 'Фундамент қисмидан туйник (фортичка) очилган', 'violation_type_ids' => []], // TODO: seed da yo'q
                        'past_2_2' => ['label' => 'Баландлиги 2.2 метрдан паст бўлган ертўладан фойдаланилган', 'violation_type_ids' => [20]],
                        'shovqin' => ['label' => 'Ертўлада шовқин ва тебраниш ҳосил қилувчи ҳамда ҳавони ифлос қилувчи ишлар амалга оширилган', 'violation_type_ids' => [18]],
                        'framuga' => ['label' => 'Ертўлага фрамуга очилган', 'violation_type_ids' => [4]],
                        'boshqa' => ['label' => 'Бошқа ҳолатлар', 'violation_type_ids' => [10, 21]],
                    ],
                ],
            ],
        ];
    }

    /** 4-ЖДВ. Умумфойдаланишдаги жойлар (2 ta bo'lim) */
    private static function umumfoydalanish(): array
    {
        return [
            'title' => 'Кўп квартирали уйларнинг умумий фойдаланишдаги жойларида аниқланган қонун бузилиш ҳолатларини бартараф этиш юзасидан амалга оширилган ишлар тўғрисидаги маълумот',
            'monitoring_type_ids' => [self::MONITORING_TYPE_KOP_QAVATLI],
            'slug' => 'jdv-4-umumfoydalanish',
            'layout' => 'umumfoydalanish',
            'group_fines' => false,
            'show_studied' => false,
            'sections' => [
                'fasad' => [
                    'title' => 'Фасад, балкон, том',
                    'groups' => [
                        'fasad_reklama' => ['label' => 'Фасадда ўзбошимча рекламалар ва бошқа ҳолатлар мавжудлиги', 'violation_type_ids' => []], // TODO
                        'balkon_orab' => ['label' => 'Балкон қисми ўраб олинган', 'violation_type_ids' => []], // TODO
                        'shamollatish' => ['label' => 'Шамоллатиш каналлари', 'violation_type_ids' => []], // TODO
                        'ichki_kommunikatsiya' => ['label' => 'Ички коммуникация тизимларига ўзгартириш киритилган', 'violation_type_ids' => [26]],
                        'mansarda' => ['label' => 'Очиқ мансарда қисмларига қўшимча оғир (ғишт) турдаги қурилмалар билан ўраб олинган', 'violation_type_ids' => [28]],
                        'umumiy_joy_tosilgan' => ['label' => 'Умумий фойдаланишда бўлган жойлар тўсиб олинган', 'violation_type_ids' => [25]],
                        'tomga_qurilma' => ['label' => 'Томга оғир ва енгил турдаги конструкциялар қуриб олинганлик ҳолати', 'violation_type_ids' => []], // TODO
                    ],
                ],
                'egallab_olish' => [
                    'title' => 'Умумий фойдаланишдаги жойларни эгаллаб олиш',
                    'mib_unexecuted' => true,
                    'groups' => [
                        'kirish_yolagi' => ['label' => 'Кириш йўлакларини эгаллаб олинганлиги', 'violation_type_ids' => []], // TODO
                        'zinapoya_osti' => ['label' => 'Зинапоя ости қисми эгаллаб олинганлиги', 'violation_type_ids' => []], // TODO
                        'tom_qismi' => ['label' => 'Уйнинг том қисми эгаллаб олинганлиги', 'violation_type_ids' => []], // TODO
                        'texnik_qavat' => ['label' => 'Уйнинг техник қаватларини эгаллаб олинганлиги', 'violation_type_ids' => []], // TODO
                        'havo_shaxta' => ['label' => 'Ҳаво айланиш шахтаси беркитиб олинганлиги', 'violation_type_ids' => []], // TODO
                        'musoroprovod' => ['label' => 'Чиқинди ташлаш жойи (мусоропровод) эгаллаб олинганлиги', 'violation_type_ids' => []], // TODO
                        'kommunikatsiya' => ['label' => 'Уйнинг коммуникация тизимлари', 'violation_type_ids' => []], // TODO
                    ],
                ],
            ],
        ];
    }

    /** 5-ЖДВ. Умумий таҳрир = 1–4 hisobotlar yig'indisi */
    private static function umumiy(): array
    {
        $tayanch = self::tayanch()['sections']['main']['groups'];

        return [
            'title' => 'Кўп квартирали уй-жой фондларидан техник фойдаланиш қоидаларига риоя этилишини назорат қилиш йўналишида амалга оширилган ишлар юзасидан маълумот',
            'monitoring_type_ids' => [self::MONITORING_TYPE_YERTOLA, self::MONITORING_TYPE_KOP_QAVATLI],
            'slug' => 'jdv-5-umumiy',
            'group_fines' => false,
            'with_existing_houses' => true,
            'sections' => [
                'main' => [
                    'title' => null,
                    'groups' => [
                        'tayanch' => $tayanch['tayanch'],
                        'tosiq' => $tayanch['tosiq'],
                        'tutash' => ['label' => 'Туташ ер участкаларида қурилма қуриш', 'violation_type_ids' => self::allIds(self::tutash())],
                        'umumfoydalanish' => ['label' => 'Умумфойдаланишдаги жойларда аниқланган ҳуқуқбузарликлар', 'violation_type_ids' => self::allIds(self::umumfoydalanish())],
                        'yertola' => ['label' => 'Ертўлаларда аниқланган ҳуқуқбузарликлар', 'violation_type_ids' => self::allIds(self::yertola())],
                    ],
                ],
            ],
        ];
    }

    public static function allIds(array $config): array
    {
        $ids = [];
        foreach ($config['sections'] as $section) {
            foreach ($section['groups'] as $group) {
                array_push($ids, ...$group['violation_type_ids']);
            }
        }

        return array_values(array_unique($ids));
    }
}
