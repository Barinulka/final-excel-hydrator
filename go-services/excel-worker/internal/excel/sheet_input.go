package excel

import (
	"fmt"

	"github.com/xuri/excelize/v2"
)

func writeInputSheet(file *excelize.File, sheetName string, styles workbookStyles, timelineData timelineExportData) error {
	if err := file.SetColWidth(sheetName, "B", "B", 22.164); err != nil {
		return fmt.Errorf("set input sheet B width: %w", err)
	}
	if err := file.SetColWidth(sheetName, "D", "D", 22); err != nil {
		return fmt.Errorf("set input sheet D width: %w", err)
	}

	if err := writeCell(file, sheetName, "C2", "ед измерения", styles.technical); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A4", "Дата начала инвестиций", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C4", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D4", timelineData.investmentStartDate, styles.inputDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A5", "Длительность инвестиций", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C5", "мес.", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "D5", timelineData.investmentDurationMonths, styles.inputNumber); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A6", "Длительность коммерческой работы", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C6", "мес.", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "D6", timelineData.commercialOperationDurationMonths, styles.inputNumber); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A7", "Шаг прогнозирования", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C7", "выбор", styles.hint); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "D7", timelineData.forecastStepTitle, styles.inputText); err != nil {
		return err
	}
	if err := addForecastStepValidation(file, sheetName, "D7"); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A9", "Дата окончания инвестиционной фазы", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C9", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D9", timelineData.investmentEndDate, styles.calculatedDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A10", "Дата начала коммерческой эксплуатации", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C10", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D10", timelineData.commercialOperationStartDate, styles.calculatedDate); err != nil {
		return err
	}

	if err := writeCell(file, sheetName, "A11", "Дата окончания коммерческой фазы", -1); err != nil {
		return err
	}
	if err := writeCell(file, sheetName, "C11", "дата", -1); err != nil {
		return err
	}
	if err := writeDateCell(file, sheetName, "D11", timelineData.commercialOperationEndDate, styles.calculatedDate); err != nil {
		return err
	}

	return nil
}

func addForecastStepValidation(file *excelize.File, sheetName string, cell string) error {
	validation := excelize.NewDataValidation(true)
	validation.SetSqref(cell)
	if err := validation.SetDropList([]string{"мес.", "квартал", "год"}); err != nil {
		return fmt.Errorf("set forecast step validation list: %w", err)
	}

	if err := file.AddDataValidation(sheetName, validation); err != nil {
		return fmt.Errorf("add forecast step validation: %w", err)
	}

	return nil
}
