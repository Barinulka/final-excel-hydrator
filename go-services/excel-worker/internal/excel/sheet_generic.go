package excel

import (
	"fmt"
	"time"

	"excel-worker/internal/symfony"

	"github.com/xuri/excelize/v2"
)

func writeSummarySheet(file *excelize.File, sheetName string, styles workbookStyles, export symfony.ProcessingExport, payload CalculationPayload) error {
	if err := writeCell(file, sheetName, "A1", "Excel export", styles.header); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A2", "Export ID", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "B2", export.ID, -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A3", "Status", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "B3", export.Status, -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A4", "Generated at", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "B4", time.Now().Format(time.RFC3339), -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A5", "Tables count", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "B5", len(payload.Tables), -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A6", "Metrics count", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "B6", len(payload.Metrics), -1); err != nil {
		return err
	}

	return nil
}

func writeCalculationTableSheet(file *excelize.File, sheetName string, styles workbookStyles, table CalculationTable) error {
	if err := writeCell(file, sheetName, "A1", table.Title, styles.header); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A3", "Показатель", styles.header); err != nil {
		return err
	}

	for index, period := range table.Periods {
		cell, err := excelize.CoordinatesToCellName(index+2, 3)
		if err != nil {
			return fmt.Errorf("build period cell name: %w", err)
		}

		if err := writeCell(file, sheetName, cell, period, styles.header); err != nil {
			return err
		}
	}

	for rowIndex, row := range table.Rows {
		excelRowNumber := rowIndex + 4

		titleCell, err := excelize.CoordinatesToCellName(1, excelRowNumber)
		if err != nil {
			return fmt.Errorf("build row title cell name: %w", err)
		}

		if err := writeCell(file, sheetName, titleCell, row.Title, -1); err != nil {
			return err
		}

		for valueIndex, value := range row.Values {
			valueCell, err := excelize.CoordinatesToCellName(valueIndex+2, excelRowNumber)
			if err != nil {
				return fmt.Errorf("build row value cell name: %w", err)
			}

			if err := writeCell(file, sheetName, valueCell, value, -1); err != nil {
				return err
			}
		}
	}

	return nil
}
