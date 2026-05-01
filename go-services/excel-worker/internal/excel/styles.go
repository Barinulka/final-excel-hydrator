package excel

import (
	"fmt"

	"github.com/xuri/excelize/v2"
)

type workbookStyles struct {
	technical      int
	hint           int
	inputDate      int
	inputNumber    int
	inputText      int
	referenceDate  int
	calculatedDate int
	flag           int
	header         int
}

func createWorkbookStyles(file *excelize.File) (workbookStyles, error) {
	thinBorder := []excelize.Border{
		{Type: "left", Color: "A6A6A6", Style: 1},
		{Type: "right", Color: "A6A6A6", Style: 1},
		{Type: "top", Color: "A6A6A6", Style: 1},
		{Type: "bottom", Color: "A6A6A6", Style: 1},
	}
	dateFormat := "dd.mm.yyyy"

	technical, err := file.NewStyle(&excelize.Style{
		Fill: excelize.Fill{Type: "pattern", Pattern: 1, Color: []string{"DDEBF7"}},
		Font: &excelize.Font{Family: "Aptos Narrow", Size: 12},
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create technical style: %w", err)
	}

	hint, err := file.NewStyle(&excelize.Style{
		Font: &excelize.Font{Family: "Aptos Narrow", Size: 12, Italic: true},
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create hint style: %w", err)
	}

	inputDate, err := file.NewStyle(&excelize.Style{
		Border:       thinBorder,
		Fill:         excelize.Fill{Type: "pattern", Pattern: 1, Color: []string{"FEF9B6"}},
		Font:         &excelize.Font{Family: "Aptos Narrow", Size: 12},
		CustomNumFmt: &dateFormat,
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create input date style: %w", err)
	}

	inputNumber, err := file.NewStyle(&excelize.Style{
		Border: thinBorder,
		Fill:   excelize.Fill{Type: "pattern", Pattern: 1, Color: []string{"FEF9B6"}},
		Font:   &excelize.Font{Family: "Aptos Narrow", Size: 12},
		NumFmt: 1,
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create input number style: %w", err)
	}

	inputText, err := file.NewStyle(&excelize.Style{
		Border: thinBorder,
		Fill:   excelize.Fill{Type: "pattern", Pattern: 1, Color: []string{"FEF9B6"}},
		Font:   &excelize.Font{Family: "Aptos Narrow", Size: 12},
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create input text style: %w", err)
	}

	referenceDate, err := file.NewStyle(&excelize.Style{
		Border:       thinBorder,
		Font:         &excelize.Font{Family: "Aptos Narrow", Size: 12, Color: "0070C0"},
		CustomNumFmt: &dateFormat,
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create reference date style: %w", err)
	}

	calculatedDate, err := file.NewStyle(&excelize.Style{
		Border:       thinBorder,
		Font:         &excelize.Font{Family: "Aptos Narrow", Size: 12, Color: "31681F"},
		CustomNumFmt: &dateFormat,
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create calculated date style: %w", err)
	}

	flag, err := file.NewStyle(&excelize.Style{
		Border: thinBorder,
		Font:   &excelize.Font{Family: "Aptos Narrow", Size: 12, Color: "31681F"},
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create flag style: %w", err)
	}

	header, err := file.NewStyle(&excelize.Style{
		Border: thinBorder,
		Fill:   excelize.Fill{Type: "pattern", Pattern: 1, Color: []string{"FFF46A"}},
		Font:   &excelize.Font{Family: "Aptos Narrow", Size: 12},
	})
	if err != nil {
		return workbookStyles{}, fmt.Errorf("create header style: %w", err)
	}

	return workbookStyles{
		technical:      technical,
		hint:           hint,
		inputDate:      inputDate,
		inputNumber:    inputNumber,
		inputText:      inputText,
		referenceDate:  referenceDate,
		calculatedDate: calculatedDate,
		flag:           flag,
		header:         header,
	}, nil
}
