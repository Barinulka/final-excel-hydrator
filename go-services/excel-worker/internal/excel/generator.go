package excel

import (
	"fmt"
	"os"
	"path/filepath"

	"excel-worker/internal/symfony"

	"github.com/xuri/excelize/v2"
)

const (
	inputSheetName    = "Входные данные"
	timeParamsSheet   = "Временные параметры"
	summarySheetName  = "Summary"
	firstPeriodColumn = 5
)

type Generator struct {
	storageRootDir  string
	excelExportsDir string
}

func NewGenerator(storageRootDir string, excelExportsDir string) *Generator {
	return &Generator{
		storageRootDir:  storageRootDir,
		excelExportsDir: excelExportsDir,
	}
}

func (g *Generator) Generate(pendingExport symfony.PendingExport, processingExport symfony.ProcessingExport) (string, error) {
	outputDir := filepath.Join(g.storageRootDir, g.excelExportsDir)
	if err := os.MkdirAll(outputDir, 0755); err != nil {
		return "", fmt.Errorf("create output dir: %w", err)
	}

	payload, err := NewCalculationPayload(pendingExport.CalculationResultPayload)
	if err != nil {
		return "", err
	}

	fileName := fmt.Sprintf("excel-export-%d.xlsx", processingExport.ID)
	absoluteFilePath := filepath.Join(outputDir, fileName)
	relativeFilePath := filepath.ToSlash(filepath.Join(g.excelExportsDir, fileName))

	file := excelize.NewFile()
	defer file.Close()

	styles, err := createWorkbookStyles(file)
	if err != nil {
		return "", err
	}

	defaultSheetName := file.GetSheetName(0)
	timelineTable, hasTimelineTable := findTableByCode(payload.Tables, "timeline")

	if hasTimelineTable {
		timelineData, err := buildTimelineExportData(timelineTable)
		if err != nil {
			return "", err
		}

		if err := file.SetSheetName(defaultSheetName, inputSheetName); err != nil {
			return "", fmt.Errorf("rename input sheet: %w", err)
		}

		if err := writeInputSheet(file, inputSheetName, styles, timelineData); err != nil {
			return "", err
		}

		if _, err := file.NewSheet(timeParamsSheet); err != nil {
			return "", fmt.Errorf("create time params sheet: %w", err)
		}

		if err := writeTimelineSheet(file, timeParamsSheet, styles, timelineData); err != nil {
			return "", err
		}
	} else {
		if err := file.SetSheetName(defaultSheetName, summarySheetName); err != nil {
			return "", fmt.Errorf("rename summary sheet: %w", err)
		}

		if err := writeSummarySheet(file, summarySheetName, styles, processingExport, payload); err != nil {
			return "", err
		}
	}

	for _, table := range payload.Tables {
		if table.Code == "timeline" {
			continue
		}

		sheetName := safeSheetName(table.Code)
		if sheetName == "" {
			sheetName = safeSheetName(table.Title)
		}
		if sheetName == "" {
			sheetName = "Table"
		}

		if _, err := file.NewSheet(sheetName); err != nil {
			return "", fmt.Errorf("create sheet %q: %w", sheetName, err)
		}

		if err := writeCalculationTableSheet(file, sheetName, styles, table); err != nil {
			return "", err
		}
	}

	if err := file.SaveAs(absoluteFilePath); err != nil {
		return "", fmt.Errorf("save xlsx file: %w", err)
	}

	return relativeFilePath, nil
}

func findTableByCode(tables []CalculationTable, tableCode string) (CalculationTable, bool) {
	for _, table := range tables {
		if table.Code == tableCode {
			return table, true
		}
	}

	return CalculationTable{}, false
}
