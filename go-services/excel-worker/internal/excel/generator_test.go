package excel

import (
	"path/filepath"
	"testing"

	"excel-worker/internal/symfony"

	"github.com/xuri/excelize/v2"
)

func TestGenerateCreatesWorkbookInTimeParamsTemplateShape(t *testing.T) {
	storageRootDir := t.TempDir()
	generator := NewGenerator(storageRootDir, "excel-exports")

	filePath, err := generator.Generate(
		symfony.PendingExport{
			ID: 42,
			CalculationResultPayload: map[string]any{
				"tables": []any{
					map[string]any{
						"code":    "timeline",
						"title":   "Временная шкала",
						"periods": []any{"2026-01", "2026-02", "2026-03"},
						"rows": []any{
							map[string]any{
								"code":   "period_start_date",
								"title":  "Начало месяца",
								"values": []any{"2026-01-01", "2026-02-01", "2026-03-01"},
							},
							map[string]any{
								"code":   "period_end_date",
								"title":  "Окончание месяца",
								"values": []any{"2026-01-31", "2026-02-28", "2026-03-31"},
							},
							map[string]any{
								"code":   "investment_activity",
								"title":  "Инвестиционная деятельность",
								"values": []any{1, 1, 0},
							},
							map[string]any{
								"code":   "operating_activity",
								"title":  "Операционная деятельность",
								"values": []any{0, 0, 1},
							},
							map[string]any{
								"code":   "operating_start",
								"title":  "Старт операционной деятельности",
								"values": []any{0, 0, 1},
							},
						},
					},
				},
				"metrics": map[string]any{
					"period_count": 3,
				},
				"warnings": []any{},
			},
		},
		symfony.ProcessingExport{
			ID:     42,
			Status: "processing",
		},
	)

	if err != nil {
		t.Fatalf("expected file path to be generated, got error: %v", err)
	}

	expectedRelativeFilePath := "excel-exports/excel-export-42.xlsx"
	if filePath != expectedRelativeFilePath {
		t.Fatalf("expected file path %q, got %q", expectedRelativeFilePath, filePath)
	}

	absoluteFilePath := filepath.Join(storageRootDir, filepath.FromSlash(filePath))

	workbook, err := excelize.OpenFile(absoluteFilePath)
	if err != nil {
		t.Fatalf("expected generated workbook to open, got error: %v", err)
	}
	defer workbook.Close()

	assertCellValue(t, workbook, inputSheetName, "A4", "Дата начала инвестиций")
	assertCellValue(t, workbook, inputSheetName, "D5", "2")
	assertCellValue(t, workbook, inputSheetName, "D6", "1")
	assertCellValue(t, workbook, inputSheetName, "D7", "мес.")

	assertCellValue(t, workbook, timeParamsSheet, "A4", "Начало месяца")
	assertCellValue(t, workbook, timeParamsSheet, "A13", "Флаг инвестиционной деятельности")
	assertCellValue(t, workbook, timeParamsSheet, "E13", "1")
	assertCellValue(t, workbook, timeParamsSheet, "G13", "0")
	assertCellValue(t, workbook, timeParamsSheet, "G14", "1")
	assertCellValue(t, workbook, timeParamsSheet, "G15", "1")
}

func assertCellValue(t *testing.T, workbook *excelize.File, sheetName string, cell string, expectedValue string) {
	t.Helper()

	value, err := workbook.GetCellValue(sheetName, cell)
	if err != nil {
		t.Fatalf("expected cell %s!%s to be readable, got error: %v", sheetName, cell, err)
	}

	if value != expectedValue {
		t.Fatalf("expected cell %s!%s value %q, got %q", sheetName, cell, expectedValue, value)
	}
}
