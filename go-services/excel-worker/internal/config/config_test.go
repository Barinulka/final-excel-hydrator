package config

import "testing"

func TestLoadReturnsErrorWhenSymfonyInternalBaseURLIsMissing(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "")
	t.Setenv("EXCEL_OUTPUT_DIR", "")

	_, err := Load()

	if err == nil {
		t.Fatal("expected config error, got nil")
	}

	expectedError := "SYMFONY_INTERNAL_BASE_URL is required"
	if err.Error() != expectedError {
		t.Fatalf("expected error %q, got %q", expectedError, err.Error())
	}
}

func TestLoadUsesDefaultExcelOutputDir(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "http://127.0.0.1:7777")
	t.Setenv("EXCEL_OUTPUT_DIR", "")

	cfg, err := Load()

	if err != nil {
		t.Fatalf("expected config to load, got error: %v", err)
	}

	if cfg.SymfonyInternalBaseURL != "http://127.0.0.1:7777" {
		t.Fatalf("expected SymfonyInternalBaseURL to be %q, got %q", "http://127.0.0.1:7777", cfg.SymfonyInternalBaseURL)
	}

	if cfg.ExcelOutputDir != "exports" {
		t.Fatalf("expected ExcelOutputDir to be %q, got %q", "exports", cfg.ExcelOutputDir)
	}
}

func TestLoadUsesCustomExcelOutputDir(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "http://127.0.0.1:7777")
	t.Setenv("EXCEL_OUTPUT_DIR", "storage/exports")

	cfg, err := Load()

	if err != nil {
		t.Fatalf("expected config to load, got error: %v", err)
	}

	if cfg.ExcelOutputDir != "storage/exports" {
		t.Fatalf("expected ExcelOutputDir to be %q, got %q", "storage/exports", cfg.ExcelOutputDir)
	}
}
