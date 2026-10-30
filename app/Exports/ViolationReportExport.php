<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Apartment\Const\ViolationReportConfig;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 1–5 ЖДВ hisobotlarini ViolationReportService natijasidan Excelga chiqaradi.
 *
 * Sarlavha ustunlar daraxti (tree) sifatida quriladi:
 *   bo'lim (bir nechta bo'lsa) → "шундан" → guruh → сони / бартараф этилган
 * Shuning uchun ViolationReportConfig'ga guruh qo'shilsa, Excel ham avtomatik o'zgaradi.
 */
class ViolationReportExport
{
    private const HEADER_ROW = 3;
    private const FONT = 'Times New Roman';

    private const INDEX_KEY = '__index';
    private const NAME_KEY = 'name';

    private array $config;

    /** @var array<int, string> ustun raqami => qatordagi qiymat yo'li (dot notation) */
    private array $leaves = [];

    /** @var array<int, bool> summa ustunlari (0.00 formatda) */
    private array $moneyColumns = [];

    public function __construct(private readonly array $report)
    {
        $this->config = ViolationReportConfig::get($report['type']);
    }

    public function fileName(): string
    {
        return sprintf(
            '%s_%s_%s.xlsx',
            $this->config['slug'],
            $this->report['period']['from'],
            $this->report['period']['to']
        );
    }

    public function download(): StreamedResponse
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $this->fileName(), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName(self::FONT)->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($this->sheetTitle(), 0, 31));

        $tree = $this->columnTree();
        $headerDepth = max(array_map(fn($node) => $this->depth($node), $tree));
        $lastHeaderRow = self::HEADER_ROW + $headerDepth - 1;

        // 1. Sarlavha ustunlari
        $col = 1;
        foreach ($tree as $node) {
            $col = $this->renderHeader($sheet, $node, $col, 0, $headerDepth);
        }
        $lastCol = $col - 1;
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

        // 2. Nom va davr
        $this->renderTitle($sheet, $lastColLetter);

        // 3. Ustun raqamlari (1, 2, 3 ...)
        $numberRow = $lastHeaderRow + 1;
        for ($c = 1; $c <= $lastCol; $c++) {
            $sheet->setCellValue([$c, $numberRow], $c);
        }

        // 4. Ma'lumotlar: avval jami, keyin hududlar
        $row = $numberRow + 1;
        $totalRow = $row;
        $this->renderDataRow($sheet, $row++, $this->report['total'], null);
        foreach ($this->report['rows'] as $i => $item) {
            $this->renderDataRow($sheet, $row++, $item, $i + 1);
        }
        $lastRow = $row - 1;

        // 5. Stil
        $this->styleSheet($sheet, $headerDepth, $lastHeaderRow, $numberRow, $totalRow, $lastRow, $lastCol);

        return $spreadsheet;
    }

    /* ------------------------------------------------------------------ */
    /*  Ustunlar daraxti                                                   */
    /* ------------------------------------------------------------------ */

    private function columnTree(): array
    {
        $tree = [
            $this->leaf('№', self::INDEX_KEY),
            $this->leaf($this->report['row_type'] === 'district' ? 'Туман (шаҳар)лар номи' : 'Ҳудудлар номи', self::NAME_KEY),
        ];

        if (!empty($this->config['with_existing_houses'])) {
            $tree[] = $this->leaf('Мавжуд уй-жойлар сони', 'existing_houses');
        }

        if ($this->config['show_studied'] ?? true) {
            // monitorings.apartment_id bo'yicha taxminiy hisob (tizimda alohida hisob yo'q)
            $tree[] = $this->leaf(
                ($this->config['studied_label'] ?? 'Ўрганиб чиқилган уй-жойлар сони') . ' (мониторинглар асосида)',
                'studied_houses'
            );
        }

        $multiSection = count($this->config['sections']) > 1;

        foreach ($this->config['sections'] as $sectionKey => $section) {
            $block = ($this->config['layout'] ?? 'default') === 'umumfoydalanish'
                ? $this->umumfoydalanishBlock("sections.{$sectionKey}", $section)
                : $this->sectionBlock("sections.{$sectionKey}", $section['groups']);

            if ($multiSection) {
                $tree[] = ['title' => $section['title'], 'children' => $block];
            } else {
                array_push($tree, ...$block);
            }
        }

        return $tree;
    }

    /** "шундан" → guruh → сони / бартараф этилган */
    private function groupsNode(string $prefix, array $groups, string $title = 'шундан'): array
    {
        return [
            'title' => $title,
            'children' => array_map(fn($key, $group) => [
                'title' => $group['label'],
                'children' => [
                    $this->leaf('сони', "{$prefix}.groups.{$key}.count"),
                    $this->leaf('бартараф этилган', "{$prefix}.groups.{$key}.fixed"),
                ],
            ], array_keys($groups), $groups),
        ];
    }

    /** 1, 2, 3, 5-ЖДВ ustunlari (asl Excel tartibida) */
    private function sectionBlock(string $prefix, array $groups): array
    {
        $total = "{$prefix}.total";

        $block = [
            $this->leaf('Аниқланган жами қонун бузилишлар сони', "{$total}.count"),
            $this->groupsNode($prefix, $groups),
        ];

        // 1-ЖДВ (Таянч) da umumiy jarima ustuni yo'q, faqat guruhlar bo'yicha
        if ($this->config['total_fines'] ?? true) {
            $block[] = [
                'title' => 'Белгиланган жарималар миқдори (млн. сўм)',
                'children' => [
                    $this->leaf('сони', "{$total}.fine_count"),
                    $this->leaf('суммаси', "{$total}.fine_sum", money: true),
                ],
            ];
        }

        if (!empty($this->config['group_fines'])) {
            $block[] = [
                'title' => ($this->config['total_fines'] ?? true) ? 'шундан жарималар (млн. сўм)' : 'Белгиланган жарималар миқдори (млн. сўм)',
                'children' => array_map(fn($key, $group) => [
                    'title' => $group['label'],
                    'children' => [
                        $this->leaf('сони', "{$prefix}.groups.{$key}.fine_count"),
                        $this->leaf('суммаси', "{$prefix}.groups.{$key}.fine_sum", money: true),
                    ],
                ], array_keys($groups), $groups),
            ];
        }

        return array_merge($block, [
            $this->leaf('Берилган мажбурий кўрсатмалар сони', "{$total}.regulations"),
            $this->leaf('Судга киритилган даъво аризалар сони', "{$total}.court"),
            $this->leaf('Суд томонидан қаноатлантирилган даъво аризалар сони', "{$total}.court_satisfied"),
            $this->leaf('Суд томонидан рад этилган даъво аризалар сони', "{$total}.court_rejected"),
            $this->leaf('МИБга чиқарилган хатлар сони', "{$total}.mib"),
            $this->leaf('Бартараф этилганлар сони', "{$total}.fixed"),
        ]);
    }

    /** 4-ЖДВ ustunlari: har bir bo'lim alohida blok (asl Excel tartibida) */
    private function umumfoydalanishBlock(string $prefix, array $section): array
    {
        $total = "{$prefix}.total";

        $court = [
            $this->leaf('Судга киритилган даъво аризалар', "{$total}.court"),
            $this->leaf('Қаноатлантирилган даъво аризалари', "{$total}.court_satisfied"),
            $this->leaf('Қаноатлантирилмаган даъво аризалари', "{$total}.court_rejected"),
            $this->leaf('Суд иши давом этаётган даъво аризалари', "{$total}.court_pending"),
            $this->leaf('МИБга чиқарилган хатлар', "{$total}.mib"),
        ];

        if (!empty($section['mib_unexecuted'])) {
            // tizimda yo'q – bo'sh ustun
            $court[] = $this->leaf('МИБлар томонидан ижросини таъминланмаган суд қарорлари', "{$total}.mib_unexecuted");
        }

        return [
            $this->leaf('Аниқланган жами қонун бузилишлар сони', "{$total}.count"),
            $this->groupsNode($prefix, $section['groups'], 'Аниқланган ҳолатлар'),
            [
                'title' => 'Инспекция томонидан кўрилган чоралар',
                'children' => [
                    $this->leaf('Берилган мажбурий кўрсатмалар', "{$total}.regulations"),
                    $this->leaf('Мажбурий кўрсатмага асосан бартараф этилганлар', "{$total}.fixed"),
                    [
                        'title' => 'Белгиланган жарималар миқдори (млн. сўм)',
                        'children' => [
                            $this->leaf('Сони', "{$total}.fine_count"),
                            $this->leaf('Қиймати', "{$total}.fine_sum", money: true),
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Судларга киритилган даъво аризаларининг ҳолати',
                'children' => $court,
            ],
        ];
    }

    private function leaf(string $title, string $key, bool $money = false): array
    {
        return ['title' => $title, 'key' => $key, 'money' => $money];
    }

    private function depth(array $node): int
    {
        if (empty($node['children'])) {
            return 1;
        }

        return 1 + max(array_map(fn($child) => $this->depth($child), $node['children']));
    }

    /**
     * Tugunni chizadi va keyingi bo'sh ustun raqamini qaytaradi.
     * Barg (leaf) pastki sarlavha qatorigacha vertikal birlashtiriladi.
     */
    private function renderHeader(Worksheet $sheet, array $node, int $col, int $level, int $depth): int
    {
        $row = self::HEADER_ROW + $level;

        if (empty($node['children'])) {
            $sheet->setCellValue([$col, $row], $node['title']);
            $bottom = self::HEADER_ROW + $depth - 1;
            if ($bottom > $row) {
                $sheet->mergeCells([$col, $row, $col, $bottom]);
            }
            $this->leaves[$col] = $node['key'];
            if ($node['money']) {
                $this->moneyColumns[$col] = true;
            }

            return $col + 1;
        }

        $start = $col;
        foreach ($node['children'] as $child) {
            $col = $this->renderHeader($sheet, $child, $col, $level + 1, $depth);
        }

        $sheet->setCellValue([$start, $row], $node['title']);
        if ($col - 1 > $start) {
            $sheet->mergeCells([$start, $row, $col - 1, $row]);
        }

        return $col;
    }

    /* ------------------------------------------------------------------ */
    /*  Qatorlar                                                           */
    /* ------------------------------------------------------------------ */

    private function renderTitle(Worksheet $sheet, string $lastColLetter): void
    {
        $sheet->setCellValue('A1', $this->config['title']);
        $sheet->mergeCells("A1:{$lastColLetter}1");

        $period = sprintf(
            '%s – %s',
            Carbon::parse($this->report['period']['from'])->format('d.m.Y'),
            Carbon::parse($this->report['period']['to'])->format('d.m.Y')
        );

        if ($regionName = $this->regionName()) {
            $period = "{$regionName}, {$period}";
        }

        $sheet->setCellValue('A2', $period);
        $sheet->mergeCells("A2:{$lastColLetter}2");
    }

    private function renderDataRow(Worksheet $sheet, int $row, array $item, ?int $index): void
    {
        foreach ($this->leaves as $col => $key) {
            $value = match ($key) {
                self::INDEX_KEY => $index,
                self::NAME_KEY => $index === null ? $this->totalLabel() : $item['name'],
                default => Arr::get($item, $key),
            };

            if ($value !== null) {
                $sheet->setCellValue([$col, $row], $value);
            }
        }
    }

    private function totalLabel(): string
    {
        return $this->report['row_type'] === 'district' ? 'Вилоят бўйича, жами' : 'Республика бўйича, жами';
    }

    private function sheetTitle(): string
    {
        return match ($this->report['type']) {
            ViolationReportConfig::TAYANCH => 'Таянч',
            ViolationReportConfig::TUTASH => 'Туташ',
            ViolationReportConfig::YERTOLA => 'Ертўла',
            ViolationReportConfig::UMUMFOYDALANISH => 'Умумфойдаланиш',
            ViolationReportConfig::UMUMIY => 'Умумий таҳрир',
        };
    }

    private function regionName(): ?string
    {
        if (empty($this->report['region_id'])) {
            return null;
        }

        return DB::table('regions')->where('id', $this->report['region_id'])->value('name_uz');
    }

    /* ------------------------------------------------------------------ */
    /*  Stil                                                               */
    /* ------------------------------------------------------------------ */

    private function styleSheet(
        Worksheet $sheet,
        int $headerDepth,
        int $lastHeaderRow,
        int $numberRow,
        int $totalRow,
        int $lastRow,
        int $lastCol
    ): void {
        $last = Coordinate::stringFromColumnIndex($lastCol);
        $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        // Nom
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(45);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        // Sarlavha
        $sheet->getStyle('A' . self::HEADER_ROW . ":{$last}{$lastHeaderRow}")->applyFromArray($thin + [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
        ]);
        for ($level = 0; $level < $headerDepth; $level++) {
            // guruh nomlari qatori (сони/бартараф dan bitta yuqori) eng baland
            $height = $level === $headerDepth - 2 ? 120 : 45;
            $sheet->getRowDimension(self::HEADER_ROW + $level)->setRowHeight($height);
        }

        // Ustun raqamlari
        $sheet->getStyle("A{$numberRow}:{$last}{$numberRow}")->applyFromArray($thin + [
            'font' => ['italic' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Ma'lumotlar
        $sheet->getStyle("A{$totalRow}:{$last}{$lastRow}")->applyFromArray($thin + [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("B{$totalRow}:B{$lastRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $sheet->getStyle("A{$totalRow}:{$last}{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
        ]);
        foreach (array_keys($this->moneyColumns) as $col) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$letter}{$totalRow}:{$letter}{$lastRow}")
                ->getNumberFormat()->setFormatCode('0.00');
        }

        // Ustun kengliklari
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(32);
        for ($c = 3; $c <= $lastCol; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(12);
        }

        $sheet->freezePane('C' . ($numberRow + 1));

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
    }
}
