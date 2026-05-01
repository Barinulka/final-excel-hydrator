package config

import "testing"

func TestLoadReturnsErrorWhenSymfonyInternalBaseURLIsMissing(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "")
	t.Setenv("STORAGE_ROOT_DIR", "")
	t.Setenv("EXCEL_EXPORTS_DIR", "")

	_, err := Load()

	if err == nil {
		t.Fatal("expected config error, got nil")
	}

	expectedError := "SYMFONY_INTERNAL_BASE_URL is required"
	if err.Error() != expectedError {
		t.Fatalf("expected error %q, got %q", expectedError, err.Error())
	}
}

func TestLoadUsesDefaultStorageConfig(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "http://127.0.0.1:7777")
	t.Setenv("STORAGE_ROOT_DIR", "")
	t.Setenv("EXCEL_EXPORTS_DIR", "")

	cfg, err := Load()

	if err != nil {
		t.Fatalf("expected config to load, got error: %v", err)
	}

	if cfg.SymfonyInternalBaseURL != "http://127.0.0.1:7777" {
		t.Fatalf("expected SymfonyInternalBaseURL to be %q, got %q", "http://127.0.0.1:7777", cfg.SymfonyInternalBaseURL)
	}

	if cfg.StorageRootDir != "var/storage" {
		t.Fatalf("expected StorageRootDir to be %q, got %q", "var/storage", cfg.StorageRootDir)
	}

	if cfg.ExcelExportsDir != "excel-exports" {
		t.Fatalf("expected ExcelExportsDir to be %q, got %q", "excel-exports", cfg.ExcelExportsDir)
	}
}

func TestLoadUsesCustomStorageConfig(t *testing.T) {
	t.Setenv("SYMFONY_INTERNAL_BASE_URL", "http://127.0.0.1:7777")
	t.Setenv("STORAGE_ROOT_DIR", "/app/var/storage")
	t.Setenv("EXCEL_EXPORTS_DIR", "tenant-a/excel-exports")

	cfg, err := Load()

	if err != nil {
		t.Fatalf("expected config to load, got error: %v", err)
	}

	if cfg.StorageRootDir != "/app/var/storage" {
		t.Fatalf("expected StorageRootDir to be %q, got %q", "/app/var/storage", cfg.StorageRootDir)
	}

	if cfg.ExcelExportsDir != "tenant-a/excel-exports" {
		t.Fatalf("expected ExcelExportsDir to be %q, got %q", "tenant-a/excel-exports", cfg.ExcelExportsDir)
	}
}
