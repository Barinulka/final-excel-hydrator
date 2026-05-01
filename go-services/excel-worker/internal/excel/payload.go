package excel

import (
	"encoding/json"
	"fmt"
)

type CalculationPayload struct {
	Tables   []CalculationTable `json:"tables"`
	Metrics  map[string]any     `json:"metrics"`
	Warnings []string           `json:"warnings"`
}

type CalculationTable struct {
	Code    string           `json:"code"`
	Title   string           `json:"title"`
	Periods []string         `json:"periods"`
	Rows    []CalculationRow `json:"rows"`
}

type CalculationRow struct {
	Code   string `json:"code"`
	Title  string `json:"title"`
	Values []any  `json:"values"`
}

func NewCalculationPayload(raw map[string]any) (CalculationPayload, error) {
	encodedPayload, err := json.Marshal(raw)
	if err != nil {
		return CalculationPayload{}, fmt.Errorf("encode calculation payload: %w", err)
	}

	var payload CalculationPayload
	if err := json.Unmarshal(encodedPayload, &payload); err != nil {
		return CalculationPayload{}, fmt.Errorf("decode calculation payload: %w", err)
	}

	return payload, nil
}
