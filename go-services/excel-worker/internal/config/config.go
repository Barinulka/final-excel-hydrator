package config

import (
	"errors"
	"os"
)

type Config struct {
	SymfonyInternalBaseURL string
	StorageRootDir         string
	ExcelExportsDir        string
}

func Load() (Config, error) {
	baseURL := os.Getenv("SYMFONY_INTERNAL_BASE_URL")
	if baseURL == "" {
		return Config{}, errors.New("SYMFONY_INTERNAL_BASE_URL is required")
	}

	storageRootDir := os.Getenv("STORAGE_ROOT_DIR")
	if storageRootDir == "" {
		return Config{}, errors.New("STORAGE_ROOT_DIR is required")
	}

	excelExportsDir := os.Getenv("EXCEL_EXPORTS_DIR")
	if excelExportsDir == "" {
		excelExportsDir = "excel-exports"
	}

	return Config{
		SymfonyInternalBaseURL: baseURL,
		StorageRootDir:         storageRootDir,
		ExcelExportsDir:        excelExportsDir,
	}, nil
}
