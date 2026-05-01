package excel

import (
	"fmt"

	"github.com/xuri/excelize/v2"
)

func writeTimelineSheet(file *excelize.File, sheetName string, styles workbookStyles, timelineData timelineExportData) error {
	if err := file.SetColWidth(sheetName, "B", "B", 23.832); err != nil {
		return fmt.Errorf("set time params sheet B width: %w", err)
	}
	if err := file.SetColWidth(sheetName, "C", "C", 27.332); err != nil {
		return fmt.Errorf("set time params sheet C width: %w", err)
	}

	if len(timelineData.periodStartDates) > 0 {
		lastPeriodColumn, err := excelize.ColumnNumberToName(firstPeriodColumn + len(timelineData.periodStartDates) - 1)
		if err != nil {
			return fmt.Errorf("build last period column name: %w", err)
		}

		if err := file.SetColWidth(sheetName, "E", lastPeriodColumn, 12); err != nil {
			return fmt.Errorf("set period columns width: %w", err)
		}
	}

	if err := writeCell(file, sheetName, "C2", "ед.измерения", styles.technical); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A4", "Начало месяца", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "A5", "Окончание месяца", -1); err != nil {
		return err
	}

	for index, startDate := range timelineData.periodStartDates {
		cell, err := excelize.CoordinatesToCellName(firstPeriodColumn+index, 4)
		if err != nil {
			return fmt.Errorf("build period start cell name: %w", err)
		}
		if err := writeDateCell(file, sheetName, cell, startDate, styles.calculatedDate); err != nil {
			return err
		}
	}

	for index, endDate := range timelineData.periodEndDates {
		cell, err := excelize.CoordinatesToCellName(firstPeriodColumn+index, 5)
		if err != nil {
			return fmt.Errorf("build period end cell name: %w", err)
		}
		if err := writeDateCell(file, sheetName, cell, endDate, styles.calculatedDate); err != nil {
			return err
		}
	}

	if err := writeCell(file, sheetName, "A7", "Дата начала инвестиций", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C7", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D7", timelineData.investmentStartDate, styles.referenceDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A8", "Дата окончания инвестиционной фазы", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C8", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D8", timelineData.investmentEndDate, styles.referenceDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A9", "Дата начала коммерческой эксплуатации", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C9", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D9", timelineData.commercialOperationStartDate, styles.referenceDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A10", "Дата окончания коммерческой фазы", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C10", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D10", timelineData.commercialOperationEndDate, styles.referenceDate); err != nil {
		return err
	}

	if err := writeFlagRow(file, sheetName, styles, 13, "Флаг инвестиционной деятельности", timelineData.investmentActivityFlags); err != nil {
		return err
	}
	if err := writeFlagRow(file, sheetName, styles, 14, "Флаг операционной деятельности", timelineData.operatingActivityFlags); err != nil {
		return err
	}
	if err := writeFlagRow(file, sheetName, styles, 15, "Флаг начала операционной деятельности", timelineData.operatingStartFlags); err != nil {
		return err
	}

	return nil
}

func writeFlagRow(file *excelize.File, sheetName string, styles workbookStyles, rowNumber int, title string, flags []int) error {
	titleCell := fmt.Sprintf("A%d", rowNumber)
	unitCell := fmt.Sprintf("C%d", rowNumber)

	if err := writeCell(file, sheetName, titleCell, title, -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, unitCell, "флаг", -1); err != nil {
		return err
	}

	for index, flag := range flags {
		cell, err := excelize.CoordinatesToCellName(firstPeriodColumn+index, rowNumber)
		if err != nil {
			return fmt.Errorf("build flag cell name: %w", err)
		}
		if err := writeCell(file, sheetName, cell, flag, styles.flag); err != nil {
			return err
		}
	}

	return nil
}
