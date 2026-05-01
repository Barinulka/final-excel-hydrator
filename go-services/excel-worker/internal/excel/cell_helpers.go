package excel

import (
	"fmt"
	"strings"
	"time"

	"github.com/xuri/excelize/v2"
)

func writeCell(file *excelize.File, sheetName string, cell string, value any, styleID int) error {
	if err := file.SetCellValue(sheetName, cell, value); err != nil {
		return fmt.Errorf("set cell %s!%s value: %w", sheetName, cell, err)
	}

	if styleID >= 0 {
		if err := file.SetCellStyle(sheetName, cell, cell, styleID); err != nil {
			return fmt.Errorf("set cell %s!%s style: %w", sheetName, cell, err)
		}
	}

	return nil
}

func writeDateCell(file *excelize.File, sheetName string, cell string, dateValue string, styleID int) error {
	parsedDate, err := time.Parse("2006-01-02", dateValue)
	if err != nil {
		return fmt.Errorf("parse date %q for cell %s!%s: %w", dateValue, sheetName, cell, err)
	}

	return writeCell(file, sheetName, cell, parsedDate, styleID)
}

func safeSheetName(value string) string {
	name := strings.TrimSpace(value)
	name = strings.NewReplacer(
		":", "_",
		"\\", "_",
		"/", "_",
		"?", "_",
		"*", "_",
		"[", "_",
		"]", "_",
	).Replace(name)

	runes := []rune(name)
	if len(runes) > 31 {
		runes = runes[:31]
	}

	return string(runes)
}
