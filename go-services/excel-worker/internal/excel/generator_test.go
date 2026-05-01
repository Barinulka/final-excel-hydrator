package excel

import (
	"testing"

	"excel-worker/internal/symfony"
)

func TestGenerateReturnsExpectedFilePath(t *testing.T) {
	generator := NewGenerator("storage/exports")

	filePath, err := generator.Generate(symfony.ProcessingExport{
		ID: 42,
	})

	if err != nil {
		t.Fatalf("expected file path to be generated, got error: %v", err)
	}

	expectedFilePath := "storage/exports/excel-export-42.xlsx"
	if filePath != expectedFilePath {
		t.Fatalf("expected file path %q, got %q", expectedFilePath, filePath)
	}
}
