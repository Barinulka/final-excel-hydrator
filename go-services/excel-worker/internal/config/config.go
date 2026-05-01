package config

import (
	"errors"
	"os"
)

type Config struct {
	SymfonyInternalBaseURL string
	ExcelOutputDir         string
}

func Load() (Config, error) {
	baseURL := os.Getenv("SYMFONY_INTERNAL_BASE_URL")
	if baseURL == "" {
		return Config{}, errors.New("SYMFONY_INTERNAL_BASE_URL is required")
	}

	excelOutputDir := os.Getenv("EXCEL_OUTPUT_DIR")
	if excelOutputDir == "" {
		excelOutputDir = "exports"
	}

	return Config{
		SymfonyInternalBaseURL: baseURL,
		ExcelOutputDir:         excelOutputDir,
	}, nil
}
