package excel

import (
	"fmt"
	"strconv"
)

type timelineExportData struct {
	periodStartDates                  []string
	periodEndDates                    []string
	investmentActivityFlags           []int
	operatingActivityFlags            []int
	operatingStartFlags               []int
	investmentStartDate               string
	investmentEndDate                 string
	commercialOperationStartDate      string
	commercialOperationEndDate        string
	investmentDurationMonths          int
	commercialOperationDurationMonths int
	forecastStepTitle                 string
}

func buildTimelineExportData(table CalculationTable) (timelineExportData, error) {
	periodStartDates, err := requiredRowValues(table, "period_start_date")
	if err != nil {
		return timelineExportData{}, err
	}
	periodEndDates, err := requiredRowValues(table, "period_end_date")
	if err != nil {
		return timelineExportData{}, err
	}
	investmentActivityValues, err := requiredRowValues(table, "investment_activity")
	if err != nil {
		return timelineExportData{}, err
	}
	operatingActivityValues, err := requiredRowValues(table, "operating_activity")
	if err != nil {
		return timelineExportData{}, err
	}
	operatingStartValues, err := requiredRowValues(table, "operating_start")
	if err != nil {
		return timelineExportData{}, err
	}

	periodCount := len(periodStartDates)
	if periodCount == 0 {
		return timelineExportData{}, fmt.Errorf("timeline table must contain at least one period")
	}
	if len(periodEndDates) != periodCount {
		return timelineExportData{}, fmt.Errorf("timeline start/end rows length mismatch")
	}

	investmentActivityFlags := intSliceFromValues(investmentActivityValues)
	operatingActivityFlags := intSliceFromValues(operatingActivityValues)
	operatingStartFlags := intSliceFromValues(operatingStartValues)

	investmentEndIndex := lastFlagIndex(investmentActivityFlags)
	if investmentEndIndex < 0 {
		return timelineExportData{}, fmt.Errorf("timeline investment activity flags are empty")
	}

	commercialStartIndex := firstFlagIndex(operatingStartFlags)
	if commercialStartIndex < 0 {
		commercialStartIndex = firstFlagIndex(operatingActivityFlags)
	}
	if commercialStartIndex < 0 {
		return timelineExportData{}, fmt.Errorf("timeline operating activity flags are empty")
	}

	return timelineExportData{
		periodStartDates:                  stringSliceFromValues(periodStartDates),
		periodEndDates:                    stringSliceFromValues(periodEndDates),
		investmentActivityFlags:           investmentActivityFlags,
		operatingActivityFlags:            operatingActivityFlags,
		operatingStartFlags:               operatingStartFlags,
		investmentStartDate:               stringValue(periodStartDates[0]),
		investmentEndDate:                 stringValue(periodEndDates[investmentEndIndex]),
		commercialOperationStartDate:      stringValue(periodStartDates[commercialStartIndex]),
		commercialOperationEndDate:        stringValue(periodEndDates[len(periodEndDates)-1]),
		investmentDurationMonths:          countFlags(investmentActivityFlags),
		commercialOperationDurationMonths: countFlags(operatingActivityFlags),
		forecastStepTitle:                 "мес.",
	}, nil
}

func requiredRowValues(table CalculationTable, rowCode string) ([]any, error) {
	for _, row := range table.Rows {
		if row.Code == rowCode {
			return row.Values, nil
		}
	}

	return nil, fmt.Errorf("timeline table row %q not found", rowCode)
}

func stringSliceFromValues(values []any) []string {
	result := make([]string, 0, len(values))
	for _, value := range values {
		result = append(result, stringValue(value))
	}

	return result
}

func intSliceFromValues(values []any) []int {
	result := make([]int, 0, len(values))
	for _, value := range values {
		result = append(result, intValue(value))
	}

	return result
}

func stringValue(value any) string {
	switch typedValue := value.(type) {
	case string:
		return typedValue
	case int:
		return strconv.Itoa(typedValue)
	case int64:
		return strconv.FormatInt(typedValue, 10)
	case float64:
		if typedValue == float64(int64(typedValue)) {
			return strconv.FormatInt(int64(typedValue), 10)
		}
		return strconv.FormatFloat(typedValue, 'f', -1, 64)
	case bool:
		if typedValue {
			return "1"
		}
		return "0"
	default:
		return fmt.Sprintf("%v", typedValue)
	}
}

func intValue(value any) int {
	switch typedValue := value.(type) {
	case int:
		return typedValue
	case int64:
		return int(typedValue)
	case float64:
		return int(typedValue)
	case string:
		parsedValue, err := strconv.Atoi(typedValue)
		if err == nil {
			return parsedValue
		}
	case bool:
		if typedValue {
			return 1
		}
	}

	return 0
}

func countFlags(flags []int) int {
	count := 0
	for _, flag := range flags {
		if flag == 1 {
			count++
		}
	}

	return count
}

func firstFlagIndex(flags []int) int {
	for index, flag := range flags {
		if flag == 1 {
			return index
		}
	}

	return -1
}

func lastFlagIndex(flags []int) int {
	for index := len(flags) - 1; index >= 0; index-- {
		if flags[index] == 1 {
			return index
		}
	}

	return -1
}
